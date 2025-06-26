<?php
// view_ticket.php
include 'includes/auth_check.php';
include 'config/database.php';
include 'includes/header.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validasi booking_id dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID Pemesanan tidak valid.</div>";
    include 'includes/footer.php';
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil detail pemesanan
try {
    $stmt = $pdo->prepare("
        SELECT b.*, s.show_time, s.cinema_hall, s.price, m.title, m.poster_url
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        JOIN movies m ON s.movie_id = m.movie_id
        WHERE b.booking_id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo "<div class='alert alert-danger'>Pemesanan tidak ditemukan atau Anda tidak memiliki izin untuk melihat detailnya.</div>";
        include 'includes/footer.php';
        exit();
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Terjadi kesalahan database: " . $e->getMessage() . "</div>";
    include 'includes/footer.php';
    exit();
}
?>

<h1>Detail Tiket</h1>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Detail Pemesanan</h5>
    </div>
    <div class="card-body">
        <p><strong>ID Pemesanan:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?></p>
        <p><strong>Film:</strong> <?php echo htmlspecialchars($booking['title']); ?></p>
        <p><strong>Waktu Tayang:</strong> <?php echo date('d M Y H:i', strtotime($booking['show_time'])); ?></p>
        <p><strong>Studio:</strong> <?php echo htmlspecialchars($booking['cinema_hall']); ?></p>
        <p><strong>Jumlah Tiket:</strong> <?php echo htmlspecialchars($booking['num_tickets']); ?></p>
        <p><strong>Harga Per Tiket:</strong> Rp <?php echo number_format($booking['price'], 0, ',', '.'); ?></p>
        <p><strong>Total Harga:</strong> Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></p>
         <p><strong>Tanggal Pemesanan:</strong> <?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?></p>
        </div>
</div>

<a href="profil.php" class="btn btn-secondary">Kembali ke Profil</a>

<?php include 'includes/footer.php'; ?>