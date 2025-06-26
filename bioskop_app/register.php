<?php
include 'config/database.php';
include 'config/masuk_admin.php'; // Diperlukan untuk APP_ENV_DEVELOPMENT

// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$message = '';
// Default form type adalah register user biasa
$form_type = isset($_GET['action']) && $_GET['action'] === 'register_admin_dev' && defined('APP_ENV_DEVELOPMENT') && APP_ENV_DEVELOPMENT === true
             ? 'register_admin' : 'register_user';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logika Pendaftaran Pengguna Biasa
    if (isset($_POST['register_user'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $message = "<div class='alert alert-danger'>Semua kolom wajib diisi untuk pendaftaran pengguna biasa.</div>";
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
                // Default role adalah 'user'
                $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'user')");
                if ($stmt_insert->execute([$username, $hashed_password, $email])) {
                    $message = "<div class='alert alert-success'>Akun pengguna berhasil didaftarkan! Silakan login.</div>";
                    // Redirect ke login.php setelah daftar berhasil
                    header("Location: login.php?success=registered");
                    exit();
                } else {
                    $message = "<div class='alert alert-danger'>Gagal mendaftarkan akun pengguna.</div>";
                }
            }
        }
    }
    // Logika Pendaftaran Admin (HANYA UNTUK PENGEMBANGAN)
    elseif (isset($_POST['register_admin_dev'])) {
        if (defined('APP_ENV_DEVELOPMENT') && APP_ENV_DEVELOPMENT === true) {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $message = "<div class='alert alert-danger'>Semua kolom harus diisi untuk pendaftaran admin.</div>";
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
                        header("Location: login.php?success=admin_registered"); // Arahkan kembali ke form login setelah daftar
                        exit();
                    } else {
                        $message = "<div class='alert alert-danger'>Gagal mendaftarkan akun admin.</div>";
                    }
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>Pendaftaran admin tidak diizinkan di lingkungan ini.</div>";
        }
        $form_type = 'register_admin'; // Pastikan form admin tetap tampil jika ada error di POST admin
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0">Daftar Akun</h4>
            </div>
            <div class="card-body p-4">
                <?php echo $message; ?>

                <?php if ($form_type === 'register_user'): ?>
                    <h5 class="mb-3 text-center">Daftar Pengguna Baru</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username_user_register" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username_user_register" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email_user_register" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_user_register" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_user_register" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password_user_register" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password_user_register" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password_user_register" name="confirm_password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="register_user" class="btn btn-primary">Daftar</button>
                        </div>
                        <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login sekarang</a></p>
                    </form>
                    
                    <?php if (defined('APP_ENV_DEVELOPMENT') && APP_ENV_DEVELOPMENT === true): ?>
                        <p class="mt-4 text-center">
                            <a href="register.php?action=register_admin_dev">Daftar Akun Admin Baru (Dev Only)</a>
                        </p>
                    <?php endif; ?>

                <?php elseif ($form_type === 'register_admin'): ?>
                    <h5 class="mb-3 text-center">Daftar Akun Admin Baru (Dev Only)</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username_admin_register" class="form-label">Username Admin</label>
                            <input type="text" class="form-control" id="username_admin_register" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email_admin_register" class="form-label">Email Admin</label>
                            <input type="email" class="form-control" id="email_admin_register" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_admin_register" class="form-label">Password Admin</label>
                            <input type="password" class="form-control" id="password_admin_register" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password_admin_register" class="form-label">Konfirmasi Password Admin</label>
                            <input type="password" class="form-control" id="confirm_password_admin_register" name="confirm_password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="register_admin_dev" class="btn btn-success">Daftar Admin</button>
                        </div>
                        <p class="mt-3 text-center">
                            <a href="register.php">Kembali ke Daftar Pengguna Biasa</a>
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>