<?php
// book_ticket.php
include 'includes/auth_check.php'; // Proteksi halaman ini!
include 'config/database.php';
include 'includes/header.php';

// Validasi schedule_id dari URL
if (!isset($_GET['schedule_id']) || !is_numeric($_GET['schedule_id'])) {
    // Log error jika schedule_id tidak valid
    error_log("Invalid or missing schedule_id in book_ticket.php. GET data: " . print_r($_GET, true));
    header("Location: index.php");
    exit();
}

$schedule_id = $_GET['schedule_id'];

// Ambil detail jadwal dan film
try {
    $stmt_schedule = $pdo->prepare("
        SELECT s.*, m.title, m.poster_url
        FROM schedules s
        JOIN movies m ON s.movie_id = m.movie_id
        WHERE s.schedule_id = ?
    ");
    $stmt_schedule->execute([$schedule_id]);
    $schedule = $stmt_schedule->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        // Log error jika jadwal tidak ditemukan di database
        error_log("Schedule not found for schedule_id: " . $schedule_id . " in book_ticket.php");
        echo "<div class='alert alert-danger'>Jadwal tidak ditemukan. Mungkin jadwal sudah dihapus atau tidak valid.</div>";
        include 'includes/footer.php';
        exit();
    }
} catch (PDOException $e) {
    // Tangani error database saat mengambil jadwal
    error_log("Database error fetching schedule in book_ticket.php: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Terjadi kesalahan saat mengambil data jadwal. Silakan coba lagi nanti.</div>";
    include 'includes/footer.php';
    exit();
}


$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $num_tickets = filter_var($_POST['num_tickets'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (empty($customer_name) || empty($customer_email) || $num_tickets === false) {
        $error_message = "Semua field harus diisi dengan benar (Nama, Email, dan Jumlah Tiket).";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } else {
        // Lanjutkan proses booking
        $price_per_ticket = $schedule['price'];
        $total_price = $price_per_ticket * $num_tickets;
        $user_id = $_SESSION['user_id'] ?? null; // Dapatkan user_id dari session

        try {
            $pdo->beginTransaction(); // Mulai transaksi

            $stmt_booking = $pdo->prepare("
                INSERT INTO bookings (schedule_id, user_id, customer_name, customer_email, num_tickets, total_price, booking_date)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            if ($stmt_booking->execute([$schedule_id, $user_id, $customer_name, $customer_email, $num_tickets, $total_price])) {
                $booking_id = $pdo->lastInsertId();
                $pdo->commit(); // Commit transaksi

                // Debugging: Log booking_id yang berhasil
                error_log("Pemesanan berhasil! Booking ID: " . $booking_id . " untuk schedule_id: " . $schedule_id);

                // Redirect ke halaman konfirmasi dengan booking_id
                header("Location: confirm_booking.php?id=" . $booking_id);
                exit();
            } else {
                $pdo->rollBack(); // Rollback transaksi jika gagal
                $error_message = "Gagal menyimpan pemesanan ke database.";
                // Log error PDO yang lebih detail
                error_log("PDO Error during booking insertion: " . json_encode($stmt_booking->errorInfo()));
            }
        } catch (PDOException $e) {
            $pdo->rollBack(); // Pastikan rollback jika ada exception
            $error_message = "Terjadi kesalahan database: " . $e->getMessage();
            error_log("Exception during booking process: " . $e->getMessage());
        }
    }
}
?>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Pesan Tiket untuk Film: <?php echo htmlspecialchars($schedule['title']); ?></h4>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <img src="<?php echo htmlspecialchars($schedule['poster_url']); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($schedule['title']); ?>">
            </div>
            <div class="col-md-8">
                <h5>Detail Jadwal:</h5>
                <p><strong>Film:</strong> <?php echo htmlspecialchars($schedule['title']); ?></p>
                <p><strong>Waktu Tayang:</strong> <?php echo date('d M Y H:i', strtotime($schedule['show_time'])); ?></p>
                <p><strong>Studio:</strong> <?php echo htmlspecialchars($schedule['cinema_hall']); ?></p>
                <p><strong>Harga per Tiket:</strong> Rp <?php echo number_format($schedule['price'], 0, ',', '.'); ?></p>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? $_SESSION['username'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="customer_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($_POST['customer_email'] ?? $_SESSION['email'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="num_tickets" class="form-label">Jumlah Tiket</label>
                <input type="number" class="form-control" id="num_tickets" name="num_tickets" min="1" value="<?php echo htmlspecialchars($_POST['num_tickets'] ?? 1); ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Konfirmasi Pemesanan</button>
            <a href="movie_detail.php?id=<?php echo $schedule['movie_id']; ?>" class="btn btn-secondary ms-2">Batal</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>