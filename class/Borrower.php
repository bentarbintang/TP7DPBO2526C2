<?php
// Class untuk mengontrol tabel `borrowers` (data peminjam).
class Borrower
{
    private $db; // koneksi PDO

    public function __construct($db)
    {
        $this->db = $db;
    }

    // READ: ambil semua peminjam
    public function getAllBorrowers() {
        $stmt = $this->db->prepare("SELECT * FROM borrowers ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // READ: ambil satu peminjam berdasarkan id
    public function getBorrowerById($id) {
        $stmt = $this->db->prepare("SELECT * FROM borrowers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // CREATE: tambah peminjam. Kolom nim bersifat UNIQUE, jadi NIM kembar akan error.
    public function createBorrower($name, $nim, $phone) {
        $stmt = $this->db->prepare("INSERT INTO borrowers (name, nim, phone) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $nim, $phone]);
    }

    // UPDATE: ubah data peminjam
    public function updateBorrower($id, $name, $nim, $phone) {
        $stmt = $this->db->prepare("UPDATE borrowers SET name = ?, nim = ?, phone = ? WHERE id = ?");
        return $stmt->execute([$name, $nim, $phone, $id]);
    }

    // DELETE: gagal (false) jika peminjam masih punya data di tabel loans
    public function deleteBorrower($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM borrowers WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
