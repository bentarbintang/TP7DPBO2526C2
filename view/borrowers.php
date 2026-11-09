<?php /* View halaman Peminjam: form tambah/edit + tabel daftar peminjam. */ ?>

<!-- Mode tambah / edit ditentukan oleh ada tidaknya $editData -->
<h2><?= $editData ? 'Edit Peminjam' : 'Tambah Peminjam' ?></h2>
<form method="post" action="index.php?page=borrowers">
    <input type="hidden" name="action" value="<?= $editData ? 'update_borrower' : 'create_borrower' ?>">
    <?php if ($editData): ?><input type="hidden" name="id" value="<?= e($editData['id']) ?>"><?php endif; ?>
    <input type="text" name="name" placeholder="Nama" required value="<?= e($editData['name'] ?? '') ?>">
    <input type="text" name="nim" placeholder="NIM" required value="<?= e($editData['nim'] ?? '') ?>">
    <input type="text" name="phone" placeholder="No. HP" value="<?= e($editData['phone'] ?? '') ?>">
    <button type="submit"><?= $editData ? 'Update' : 'Simpan' ?></button>
    <?php if ($editData): ?><a href="index.php?page=borrowers" class="btn-cancel">Batal</a><?php endif; ?>
</form>

<h2>Daftar Peminjam</h2>
<table>
    <tr><th>ID</th><th>Nama</th><th>NIM</th><th>No. HP</th><th>Aksi</th></tr>
    <?php foreach ($borrower->getAllBorrowers() as $row): ?>
    <tr>
        <td><?= e($row['id']) ?></td>
        <td><?= e($row['name']) ?></td>
        <td><?= e($row['nim']) ?></td>
        <td><?= e($row['phone']) ?></td>
        <td>
            <a href="index.php?page=borrowers&edit=<?= e($row['id']) ?>">Edit</a>
            <form method="post" action="index.php?page=borrowers" class="inline" onsubmit="return confirm('Hapus peminjam ini?')">
                <input type="hidden" name="action" value="delete_borrower">
                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                <button type="submit" class="danger">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
