<?php /* View halaman Alat: form tambah/edit + tabel daftar alat. Tidak ada SQL di sini, data diambil lewat class. */ ?>

<!-- Judul dan aksi form berubah otomatis: jika $editData ada = mode edit, jika tidak = mode tambah -->
<h2><?= $editData ? 'Edit Alat' : 'Tambah Alat' ?></h2>
<form method="post" action="index.php?page=equipment">
    <input type="hidden" name="action" value="<?= $editData ? 'update_equipment' : 'create_equipment' ?>">
    <?php if ($editData): ?><input type="hidden" name="id" value="<?= e($editData['id']) ?>"><?php endif; ?>
    <input type="text" name="name" placeholder="Nama alat" required value="<?= e($editData['name'] ?? '') ?>">
    <input type="text" name="category" placeholder="Kategori (mis. Futsal)" required value="<?= e($editData['category'] ?? '') ?>">
    <input type="number" name="stock" placeholder="Stok" min="0" required value="<?= e($editData['stock'] ?? '') ?>">
    <button type="submit"><?= $editData ? 'Update' : 'Simpan' ?></button>
    <?php if ($editData): ?><a href="index.php?page=equipment" class="btn-cancel">Batal</a><?php endif; ?>
</form>

<h2>Daftar Alat</h2>
<table>
    <tr><th>ID</th><th>Nama</th><th>Kategori</th><th>Stok Tersedia</th><th>Aksi</th></tr>
    <!-- Ulangi satu baris tabel untuk setiap alat dari database -->
    <?php foreach ($equipment->getAllEquipment() as $row): ?>
    <tr>
        <td><?= e($row['id']) ?></td>
        <td><?= e($row['name']) ?></td>
        <td><?= e($row['category']) ?></td>
        <td><?= e($row['stock']) ?></td>
        <td>
            <!-- Edit: kembali ke halaman ini dengan ?edit=ID -->
            <a href="index.php?page=equipment&edit=<?= e($row['id']) ?>">Edit</a>
            <!-- Hapus: pakai form POST + konfirmasi -->
            <form method="post" action="index.php?page=equipment" class="inline" onsubmit="return confirm('Hapus alat ini?')">
                <input type="hidden" name="action" value="delete_equipment">
                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                <button type="submit" class="danger">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
