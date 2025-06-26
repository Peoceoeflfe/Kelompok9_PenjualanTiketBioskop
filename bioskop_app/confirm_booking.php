<?php
// confirm_booking.php
include 'includes/auth_check.php'; // Proteksi halaman ini!
include 'config/database.php';
include 'includes/header.php';

// Validasi booking_id dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Log error jika booking_id tidak valid
    error_log("Invalid or missing booking_id in confirm_booking.php. GET data: " . print_r($_GET, true));
    header("Location: index.php");
    exit();
}

$booking_id = $_GET['id'];

// Ambil detail pemesanan
try {
    $stmt = $pdo->prepare("
        SELECT b.*, s.show_time, s.cinema_hall, s.price, m.title, m.poster_url
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        JOIN movies m ON s.movie_id = m.movie_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        // Log error jika pemesanan tidak ditemukan di database
        error_log("Booking not found for booking_id: " . $booking_id . " in confirm_booking.php");
        echo "<div class='alert alert-danger'>Pemesanan tidak ditemukan. Mungkin ID pemesanan tidak valid atau telah dihapus.</div>";
        include 'includes/footer.php';
        exit();
    }
} catch (PDOException $e) {
    // Tangani error database saat mengambil pemesanan
    error_log("Database error fetching booking in confirm_booking.php: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Terjadi kesalahan saat mengambil data pemesanan. Silakan coba lagi nanti.</div>";
    include 'includes/footer.php';
    exit();
}
?>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">Pemesanan Berhasil Dikonfirmasi!</h4>
    </div>
    <div class="card-body">
        <h2 class="card-title">Detail Pemesanan Anda</h2>
        <div class="row">
            <div class="col-md-4">
                <img src="<?php echo htmlspecialchars($booking['poster_url']); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($booking['title']); ?>">
            </div>
            <div class="col-md-8">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID Pemesanan:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?></li>
                    <li class="list-group-item"><strong>Film:</strong> <?php echo htmlspecialchars($booking['title']); ?></li>
                    <li class="list-group-item"><strong>Waktu Tayang:</strong> <?php echo date('d M Y H:i', strtotime($booking['show_time'])); ?></li>
                    <li class="list-group-item"><strong>Studio:</strong> <?php echo htmlspecialchars($booking['cinema_hall']); ?></li>
                    <li class="list-group-item"><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($booking['customer_email']); ?></li>
                    <li class="list-group-item"><strong>Jumlah Tiket:</strong> <?php echo htmlspecialchars($booking['num_tickets']); ?></li>
                    <li class="list-group-item"><strong>Harga Per Tiket:</strong> Rp <?php echo number_format($booking['price'], 0, ',', '.'); ?></li>
                    <li class="list-group-item"><strong>Total Harga:</strong> <span class="h5 text-primary">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span></li>
                    <li class="list-group-item"><strong>Tanggal Pemesanan:</strong> <?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?></li>
                </ul>
            </div>
        </div>
        <div class="mt-4 text-center">
            <p>Terima kasih telah melakukan pemesanan!</p>
            <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>