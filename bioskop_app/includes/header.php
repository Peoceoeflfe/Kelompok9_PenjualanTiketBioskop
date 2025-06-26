<?php
// includes/header.php

// Mulai session di awal setiap halaman yang membutuhkan session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inilah tempat utama untuk menjalankan pengecekan autentikasi
// Pastikan ini di-include setelah session_start()
include 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bioskop App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/bioskop_app/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/bioskop_app/">Nenek CGV</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/bioskop_app/">Home</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/bioskop_app/admin/">Menu Admin</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                              <li><a class="dropdown-item" href="/bioskop_app/profil.php">Profil Saya</a></li> <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/bioskop_app/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: // Jika user belum login ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/bioskop_app/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary px-3 py-2 ms-lg-2" href="/bioskop_app/register.php">Daftar Akun</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
