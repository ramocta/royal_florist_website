<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pelanggan - Royal Florist</title>
</head>
<body>
    <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pelanggan') ?></h1>
    <a href="../auth/logout.php">Logout</a>
</body>
</html>