<?php
/* View halaman Peminjaman: form pinjam (atau form edit) + tabel riwayat peminjaman. */
$borrowerList = $borrower->getAllBorrowers(); // dipakai untuk dropdown peminjam
?>
<?php if ($editData): ?>
<!-- MODE EDIT: hanya peminjam dan batas kembali yang bisa diubah -->
<h2>Edit Peminjaman #<?= e($editData['id']) ?></h2>
<p class="note">Jumlah &amp; alat tidak bisa diubah agar stok tetap konsisten. Hapus lalu buat ulang jika salah.</p>
<form method="post" action="index.php?page=loans">
    <input type="hidden" name="action" value="update_loan">
    <input type="hidden" name="id" value="<?= e($editData['id']) ?>">
    <select name="borrower_id" required>
        <?php foreach ($borrowerList as $b): ?>
        <!-- Peminjam saat ini otomatis terpilih -->
        <option value="<?= e($b['id']) ?>" <?= $b['id'] == $editData['borrower_id'] ? 'selected' : '' ?>><?= e($b['name']) ?> (<?= e($b['nim']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <label>Batas kembali <input type="date" name="due_date" required value="<?= e($editData['due_date']) ?>"></label>
    <button type="submit">Update</button>
    <a href="index.php?page=loans" class="btn-cancel">Batal</a>
</form>
<?php else: ?>
<!-- MODE TAMBAH: form pinjam alat -->
<h2>Pinjam Alat</h2>
<form method="post" action="index.php?page=loans">
    <input type="hidden" name="action" value="create_loan">
    <!-- Dropdown hanya menampilkan alat yang stoknya masih ada -->
    <select name="equipment_id" required>
        <option value="">-- Pilih alat --</option>
        <?php foreach ($equipment->getAvailableEquipment() as $eq): ?>
        <option value="<?= e($eq['id']) ?>"><?= e($eq['name']) ?> (stok: <?= e($eq['stock']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <select name="borrower_id" required>
        <option value="">-- Pilih peminjam --</option>
        <?php foreach ($borrowerList as $b): ?>
        <option value="<?= e($b['id']) ?>"><?= e($b['name']) ?> (<?= e($b['nim']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="quantity" min="1" value="1" required>
    <!-- Default: pinjam hari ini, batas kembali 3 hari lagi -->
    <label>Tgl pinjam <input type="date" name="loan_date" required value="<?= date('Y-m-d') ?>"></label>
    <label>Batas kembali <input type="date" name="due_date" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>"></label>
    <button type="submit">Pinjam</button>
</form>
<?php endif; ?>

<h2>Daftar Peminjaman</h2>
<table>
    <tr><th>ID</th><th>Alat</th><th>Peminjam</th><th>Jml</th><th>Pinjam</th><th>Batas</th><th>Kembali</th><th>Status</th><th>Aksi</th></tr>
    <?php foreach ($loan->getAllLoans() as $row): ?>
    <tr>
        <td><?= e($row['id']) ?></td>
        <td><?= e($row['equipment_name']) ?></td>
        <td><?= e($row['borrower_name']) ?></td>
        <td><?= e($row['quantity']) ?></td>
        <td><?= e($row['loan_date']) ?></td>
        <td><?= e($row['due_date']) ?></td>
        <td><?= e($row['return_date'] ?? '-') ?></td>
        <td><span class="badge <?= e($row['status']) ?>"><?= $row['status'] === 'borrowed' ? 'Dipinjam' : 'Dikembalikan' ?></span></td>
        <td>
            <!-- Tombol Kembalikan hanya muncul jika alat masih dipinjam -->
            <?php if ($row['status'] === 'borrowed'): ?>
            <form method="post" action="index.php?page=loans" class="inline">
                <input type="hidden" name="action" value="return_loan">
                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                <button type="submit">Kembalikan</button>
            </form>
            <?php endif; ?>
            <a href="index.php?page=loans&edit=<?= e($row['id']) ?>">Edit</a>
            <form method="post" action="index.php?page=loans" class="inline" onsubmit="return confirm('Hapus data peminjaman ini?')">
                <input type="hidden" name="action" value="delete_loan">
                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                <button type="submit" class="danger">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
