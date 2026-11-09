<?php
// File konfigurasi: berisi class Database untuk membuat koneksi ke MySQL memakai PDO.
class Database
{
    // Kredensial koneksi (sesuaikan jika MySQL kamu memakai password / port lain)
    private $host = 'localhost';
    private $db_name = 'db_sports_loan';
    private $username = 'root';
    private $password = '';
    private $conn; // menyimpan objek PDO supaya koneksi tidak dibuat berulang

    // Mengembalikan objek PDO yang siap dipakai oleh class lain
    public function getConnection(): PDO
    {
        // Buat koneksi hanya jika belum ada
        if ($this->conn === null) {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    // Error SQL dilempar sebagai PDOException (bisa ditangkap try/catch)
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // Hasil query berupa array asosiatif, mis. $row['name']
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Prepared statement asli dari MySQL (lebih aman dari SQL injection)
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
        return $this->conn;
    }
}
