<?php
// config/masuk_admin.php
// Definisikan konstanta lingkungan. TRUE untuk pengembangan (dev), FALSE untuk produksi (production).
define('APP_ENV_DEVELOPMENT', true); // UBAH KE FALSE SAAT DI PRODUCTION!

// Jika di mode pengembangan, kita bisa definisikan kredensial bypass/akses cepat admin
if (APP_ENV_DEVELOPMENT) {
    define('DEV_ADMIN_USERNAME', 'admin'); // Ganti dengan username admin Anda di database
    define('DEV_ADMIN_PASSWORD', 'admin123'); // Ganti dengan password teks biasa admin Anda (bukan hash!)
    define('DEV_ADMIN_USER_ID', 1); // Ganti dengan ID user admin Anda dari tabel 'users'
    define('DEV_ADMIN_EMAIL', 'admin@bioskop.com'); // Ganti dengan email admin Anda
}
?>