<?php
include 'config/database.php';

// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger'>Semua field wajib diisi!</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Format email tidak valid.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Konfirmasi password tidak cocok.</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='alert alert-danger'>Password minimal 6 karakter.</div>";
    } else {
        // Cek apakah username atau email sudah terdaftar
        $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt_check->execute([$username, $email]);
        if ($stmt_check->fetch()) {
            $message = "<div class='alert alert-danger'>Username atau Email sudah terdaftar.</div>";
        } else {
            // Hash password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            if ($stmt_insert->execute([$username, $hashed_password, $email])) {
                $message = "<div class='alert alert-success'>Pendaftaran berhasil! Silakan <a href='login.php'>Login</a>.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Terjadi kesalahan saat pendaftaran.</div>";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0">Daftar Akun Baru</h4>
            </div>
            <div class="card-body p-4">
                <?php echo $message; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Daftar</button>
                    </div>
                    <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>