<?php
// Class untuk mengontrol tabel `equipment` (data alat olahraga dan stoknya).
class Equipment
{
    private $db; // koneksi PDO

    // Koneksi diberikan dari luar (index.php) saat objek dibuat
    public function __construct($db)
    {
        $this->db = $db;
    }

    // READ: ambil semua alat, yang terbaru di atas
    public function getAllEquipment() {
        $stmt = $this->db->prepare("SELECT * FROM equipment ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(); // array berisi banyak baris
    }

    // READ: ambil alat yang stoknya masih ada (untuk dropdown form pinjam)
    public function getAvailableEquipment() {
        $stmt = $this->db->prepare("SELECT * FROM equipment WHERE stock > 0 ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // READ: ambil satu alat berdasarkan id (dipakai saat mode edit)
    public function getEquipmentById($id) {
        $stmt = $this->db->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->execute([$id]); // tanda ? diganti nilai dari array
        return $stmt->fetch(); // satu baris saja
    }

    // CREATE: tambah alat baru
    public function createEquipment($name, $category, $stock) {
        $stmt = $this->db->prepare("INSERT INTO equipment (name, category, stock) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $category, $stock]);
    }

    // UPDATE: ubah data alat berdasarkan id
    public function updateEquipment($id, $name, $category, $stock) {
        $stmt = $this->db->prepare("UPDATE equipment SET name = ?, category = ?, stock = ? WHERE id = ?");
        return $stmt->execute([$name, $category, $stock, $id]);
    }

    // DELETE: hapus alat. Jika alat masih dipakai di tabel loans, MySQL menolak
    // (foreign key) -> PDOException ditangkap dan method mengembalikan false.
    public function deleteEquipment($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM equipment WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
