<?php
// index.php = titik masuk aplikasi: menyiapkan objek, memproses aksi form (POST),
// lalu menampilkan halaman yang dipilih lewat view.

// 1. Muat file config dan semua class
require_once 'config/db.php';
require_once 'class/Equipment.php';
require_once 'class/Borrower.php';
require_once 'class/Loan.php';

// 2. Buat koneksi, lalu objek tiap class (koneksi diberikan ke constructor)
$db = (new Database())->getConnection();
$equipment = new Equipment($db);
$borrower  = new Borrower($db);
$loan      = new Loan($db);

// 3. Tentukan halaman dari URL (?page=...). Pakai whitelist karena nilainya dipakai untuk include.
$page = $_GET['page'] ?? 'equipment';
if (!in_array($page, ['equipment', 'borrowers', 'loans'], true)) {
    $page = 'equipment';
}

$msg = $_GET['msg'] ?? ''; // pesan sukses (dikirim lewat URL setelah redirect)
$error = '';               // pesan error (ditampilkan di header.php)

// 4. Proses aksi dari form. Setiap form mengirim field tersembunyi "action".
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        switch ($action) {
            // ---- ALAT: tambah & update memakai validasi yang sama ----
            case 'create_equipment':
            case 'update_equipment':
                $name = trim($_POST['name'] ?? '');
                $category = trim($_POST['category'] ?? '');
                $stock = (int)($_POST['stock'] ?? 0);
                if ($name === '' || $category === '' || $stock < 0) {
                    throw new Exception("Data alat tidak valid.");
                }
                if ($action === 'create_equipment') {
                    $equipment->createEquipment($name, $category, $stock);
                    $msg = "Alat berhasil ditambahkan.";
                } else {
                    $equipment->updateEquipment($id, $name, $category, $stock);
                    $msg = "Alat berhasil diupdate.";
                }
                break;

            case 'delete_equipment':
                // deleteEquipment() mengembalikan false jika alat masih dipakai di loans
                if (!$equipment->deleteEquipment($id)) {
                    throw new Exception("Alat gagal dihapus, masih dipakai di data peminjaman.");
                }
                $msg = "Alat berhasil dihapus.";
                break;

            // ---- PEMINJAM ----
            case 'create_borrower':
            case 'update_borrower':
                $name = trim($_POST['name'] ?? '');
                $nim = trim($_POST['nim'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                if ($name === '' || $nim === '') {
                    throw new Exception("Nama dan NIM wajib diisi.");
                }
                if ($action === 'create_borrower') {
                    $borrower->createBorrower($name, $nim, $phone);
                    $msg = "Peminjam berhasil ditambahkan.";
                } else {
                    $borrower->updateBorrower($id, $name, $nim, $phone);
                    $msg = "Peminjam berhasil diupdate.";
                }
                break;

            case 'delete_borrower':
                if (!$borrower->deleteBorrower($id)) {
                    throw new Exception("Peminjam gagal dihapus, masih dipakai di data peminjaman.");
                }
                $msg = "Peminjam berhasil dihapus.";
                break;

            // ---- PEMINJAMAN ----
            case 'create_loan': // pinjam alat (stok berkurang)
                $loan->createLoan(
                    (int)($_POST['equipment_id'] ?? 0),
                    (int)($_POST['borrower_id'] ?? 0),
                    (int)($_POST['quantity'] ?? 0),
                    $_POST['loan_date'] ?? '',
                    $_POST['due_date'] ?? ''
                );
                $msg = "Peminjaman berhasil dicatat, stok berkurang.";
                break;

            case 'update_loan': // ubah peminjam / batas kembali
                $loan->updateLoan($id, (int)($_POST['borrower_id'] ?? 0), $_POST['due_date'] ?? '');
                $msg = "Data peminjaman berhasil diupdate.";
                break;

            case 'return_loan': // kembalikan alat (stok bertambah)
                $loan->returnLoan($id);
                $msg = "Alat dikembalikan, stok bertambah.";
                break;

            case 'delete_loan':
                if (!$loan->deleteLoan($id)) {
                    throw new Exception("Data peminjaman gagal dihapus.");
                }
                $msg = "Data peminjaman berhasil dihapus.";
                break;
        }

        // Post-Redirect-Get: setelah sukses, pindah ke halaman GET supaya F5 tidak mengirim ulang form
        if ($action !== '') {
            header("Location: index.php?page=" . $page . "&msg=" . urlencode($msg));
            exit;
        }
    } catch (PDOException $e) {
        // Kode 23000 = pelanggaran constraint (mis. NIM kembar)
        $error = ($e->getCode() == 23000) ? "Data duplikat atau melanggar relasi (NIM harus unik)." : "Error database: " . $e->getMessage();
    } catch (Exception $e) {
        // Pesan dari validasi dan logika stok
        $error = $e->getMessage();
    }
}

// 5. Mode edit: jika URL berisi ?edit=ID, ambil datanya untuk mengisi form
$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($page === 'equipment') $editData = $equipment->getEquipmentById($editId);
    if ($page === 'borrowers') $editData = $borrower->getBorrowerById($editId);
    if ($page === 'loans')     $editData = $loan->getLoanById($editId);
}

// 6. Tampilkan halaman: header + view sesuai $page + footer.
//    Semua variabel di atas ($equipment, $editData, $msg, dll.) bisa dipakai di view.
include 'view/header.php';
include 'view/' . $page . '.php';
include 'view/footer.php';
