<?php
include 'config/database.php';
include 'config/masuk_admin.php'; // Digunakan untuk DEV_ADMIN_USERNAME, dll.

// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, cek role. Jika admin, ke dashboard admin, jika bukan, ke index.
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$message = '';
$form_type = isset($_GET['action']) && $_GET['action'] === 'register' ? 'register' : 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login_admin'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $message = "<div class='alert alert-danger'>Username/Email dan password wajib diisi!</div>";
        } else {
            $stmt = $pdo->prepare("SELECT user_id, username, password, email, role FROM users WHERE (username = ? OR email = ?) AND role = 'admin'");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header("Location: admin/index.php");
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Username/Email atau Password admin salah.</div>";
            }
        }
    } elseif (isset($_POST['register_admin'])) {
        // Logika Pendaftaran Admin
        // Catatan: Pendaftaran admin langsung ini HANYA UNTUK PENGEMBANGAN.
        // Di produksi, admin harus dibuat secara manual di DB atau via fitur khusus.
        if (defined('APP_ENV_DEVELOPMENT') && APP_ENV_DEVELOPMENT === true) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $message = "<div class='alert alert-danger'>Semua kolom harus diisi.</div>";
            } elseif ($password !== $confirm_password) {
                $message = "<div class='alert alert-danger'>Konfirmasi password tidak cocok.</div>";
            } elseif (strlen($password) < 6) {
                $message = "<div class='alert alert-danger'>Password minimal 6 karakter.</div>";
            } else {
                // Cek apakah username atau email sudah terdaftar
                $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
                $stmt_check->execute([$username, $email]);
                if ($stmt_check->fetch()) {
                    $message = "<div class='alert alert-danger'>Username atau email sudah terdaftar.</div>";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')");
                    if ($stmt_insert->execute([$username, $hashed_password, $email])) {
                        $message = "<div class='alert alert-success'>Akun admin berhasil didaftarkan! Silakan login.</div>";
                        $form_type = 'login'; // Arahkan kembali ke form login setelah daftar
                    } else {
                        $message = "<div class='alert alert-danger'>Gagal mendaftarkan akun admin.</div>";
                    }
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>Pendaftaran admin tidak diizinkan di lingkungan ini.</div>";
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0">Akses Admin</h4>
            </div>
            <div class="card-body p-4">
                <?php echo $message; ?>

                <?php if ($form_type === 'login'): ?>
                    <h5 class="mb-3 text-center">Login Admin</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username_admin_login" class="form-label">Username atau Email</label>
                            <input type="text" class="form-control" id="username_admin_login" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_admin_login" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password_admin_login" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="login_admin" class="btn btn-primary">Login Admin</button>
                        </div>
                        <?php if (defined('APP_ENV_DEVELOPMENT') && APP_ENV_DEVELOPMENT === true): ?>
                            <p class="mt-3 text-center">Belum punya akun admin? <a href="admin_auth.php?action=register">Daftar Admin Baru (Dev Only)</a></p>
                        <?php endif; ?>
                    </form>
                <?php elseif ($form_type === 'register'): ?>
                    <h5 class="mb-3 text-center">Daftar Admin Baru (Dev Only)</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username_admin_register" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username_admin_register" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email_admin_register" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_admin_register" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_admin_register" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password_admin_register" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password_admin_register" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password_admin_register" name="confirm_password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="register_admin" class="btn btn-success">Daftar Admin</button>
                        </div>
                        <p class="mt-3 text-center">Sudah punya akun admin? <a href="admin_auth.php">Login Admin</a></p>
                    </form>
                <?php endif; ?>
                
                <p class="mt-4 text-center">
                    <a href="login.php">Kembali ke Login Pengguna</a>
                </p>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>