<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user_id ada di session (tanda sudah login)
// Jika belum login, dan ini BUKAN halaman login.php atau register.php,
// maka redirect ke halaman login. index.php sudah ditangani di file itu sendiri.
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'register.php') {
    header("Location: /bioskop_app/login.php");
    exit();
}

// Cek role jika halaman hanya untuk admin (khusus folder admin/)
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { // Tambah !isset($_SESSION['role'])
        header("Location: /bioskop_app/index.php"); // Redirect ke halaman utama jika bukan admin atau role tidak set
        exit();
    }
}
?>