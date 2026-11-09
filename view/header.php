<?php
// Fungsi pembantu: escape output HTML agar aman dari XSS
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peminjaman Alat Olahraga</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Sistem Peminjaman Alat Olahraga</h1>
    <!-- Menu navigasi; kelas "active" ditandai pada halaman yang sedang dibuka -->
    <nav>
        <a href="index.php?page=equipment" class="<?= $page === 'equipment' ? 'active' : '' ?>">Alat</a>
        <a href="index.php?page=borrowers" class="<?= $page === 'borrowers' ? 'active' : '' ?>">Peminjam</a>
        <a href="index.php?page=loans" class="<?= $page === 'loans' ? 'active' : '' ?>">Peminjaman</a>
    </nav>
</header>
<main>
<!-- Pesan sukses / error dari index.php -->
<?php if ($msg): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
