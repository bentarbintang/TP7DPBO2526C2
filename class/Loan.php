<?php
// Class untuk mengontrol tabel `loans` (transaksi peminjaman).
// Tabel ini punya foreign key ke `equipment` dan `borrowers`,
// serta mengatur perubahan stok alat saat pinjam / kembali / hapus.

class Loan {
    private $db; // koneksi PDO

    public function __construct($db)
    {
        $this->db = $db;
    }

    // READ: semua peminjaman. JOIN dipakai supaya yang tampil nama alat & nama peminjam,
    // bukan hanya angka id.
    public function getAllLoans() {
        $stmt = $this->db->prepare(
            "SELECT l.*, e.name AS equipment_name, b.name AS borrower_name
            FROM loans l
            JOIN equipment e ON l.equipment_id = e.id
            JOIN borrowers b ON l.borrower_id = b.id
            ORDER BY l.id ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // READ: satu data peminjaman berdasarkan id
    public function getLoanById($id) {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // CREATE (pinjam alat): cek stok, simpan peminjaman, kurangi stok.
    // Semua langkah dijalankan dalam satu transaksi: kalau satu gagal, semua dibatalkan.
    public function createLoan($equipment_id, $borrower_id, $quantity, $loan_date, $due_date) {
        // Validasi dasar sebelum menyentuh database
        if ($quantity < 1) {
            throw new Exception("Jumlah minimal 1.");
        }
        if ($due_date < $loan_date) {
            throw new Exception("Tanggal kembali tidak boleh sebelum tanggal pinjam.");
        }

        $this->db->beginTransaction(); // mulai transaksi
        try {
            // Ambil stok sekarang. FOR UPDATE mengunci baris agar tidak ada
            // peminjaman lain yang mengubah stok di saat yang sama.
            $stmt = $this->db->prepare("SELECT stock FROM equipment WHERE id = ? FOR UPDATE");
            $stmt->execute([$equipment_id]);
            $row = $stmt->fetch();
            if (!$row) {
                throw new Exception("Alat tidak ditemukan.");
            }
            if ($row['stock'] < $quantity) {
                throw new Exception("Stok tidak cukup. Tersedia: " . $row['stock']);
            }

            // Catat peminjaman (status default 'borrowed' diatur di database)
            $stmt = $this->db->prepare("INSERT INTO loans (equipment_id, borrower_id, quantity, loan_date, due_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$equipment_id, $borrower_id, $quantity, $loan_date, $due_date]);

            // Kurangi stok alat sebanyak jumlah yang dipinjam
            $stmt = $this->db->prepare("UPDATE equipment SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$quantity, $equipment_id]);

            $this->db->commit(); // semua berhasil -> simpan permanen
            return true;
        } catch (Exception $e) {
            $this->db->rollBack(); // ada yang gagal -> batalkan semua perubahan
            throw $e;              // lempar lagi agar pesan error sampai ke index.php
        }
    }

    // UPDATE: hanya peminjam dan batas kembali yang boleh diubah.
    // Alat & jumlah tidak diubah agar stok tetap konsisten.
    public function updateLoan($id, $borrower_id, $due_date) {
        $loan = $this->getLoanById($id);
        if (!$loan) {
            throw new Exception("Data peminjaman tidak ditemukan.");
        }
        if ($due_date < $loan['loan_date']) {
            throw new Exception("Tanggal kembali tidak boleh sebelum tanggal pinjam.");
        }
        $stmt = $this->db->prepare("UPDATE loans SET borrower_id = ?, due_date = ? WHERE id = ?");
        return $stmt->execute([$borrower_id, $due_date, $id]);
    }

    // Pengembalian alat: ubah status jadi 'returned', isi return_date, tambah stok kembali.
    public function returnLoan($id) {
        $this->db->beginTransaction();
        try {
            // Ambil data peminjaman dan kunci barisnya
            $stmt = $this->db->prepare("SELECT equipment_id, quantity, status FROM loans WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $loan = $stmt->fetch();
            if (!$loan) {
                throw new Exception("Data peminjaman tidak ditemukan.");
            }
            // Cegah stok bertambah dua kali jika tombol ditekan ulang
            if ($loan['status'] === 'returned') {
                throw new Exception("Alat sudah dikembalikan.");
            }

            // Tandai sudah dikembalikan hari ini
            $stmt = $this->db->prepare("UPDATE loans SET status = 'returned', return_date = CURDATE() WHERE id = ?");
            $stmt->execute([$id]);

            // Tambah stok sebanyak jumlah yang dulu dipinjam
            $stmt = $this->db->prepare("UPDATE equipment SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$loan['quantity'], $loan['equipment_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // DELETE: hapus data peminjaman. Jika statusnya masih 'borrowed',
    // stok dikembalikan dulu supaya stok alat tidak hilang.
    public function deleteLoan($id) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT equipment_id, quantity, status FROM loans WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $loan = $stmt->fetch();
            if (!$loan) {
                $this->db->rollBack();
                return false; // data tidak ada
            }
            if ($loan['status'] === 'borrowed') {
                $stmt = $this->db->prepare("UPDATE equipment SET stock = stock + ? WHERE id = ?");
                $stmt->execute([$loan['quantity'], $loan['equipment_id']]);
            }

            $stmt = $this->db->prepare("DELETE FROM loans WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
