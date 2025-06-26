<?php
// profile.php
include 'includes/auth_check.php';
include 'config/database.php';
include 'includes/header.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil informasi pengguna
try {
    $stmt_user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<div class='alert alert-danger'>Informasi pengguna tidak ditemukan.</div>";
        include 'includes/footer.php';
        exit();
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Terjadi kesalahan database: " . $e->getMessage() . "</div>";
    include 'includes/footer.php';
    exit();
}

// Ambil daftar tiket yang dipesan oleh pengguna
try {
    $stmt_bookings = $pdo->prepare("
        SELECT b.*, s.show_time, s.cinema_hall, s.price, m.title, m.poster_url
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        JOIN movies m ON s.movie_id = m.movie_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt_bookings->execute([$user_id]);
    $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Terjadi kesalahan database saat mengambil daftar tiket: " . $e->getMessage() . "</div>";
    include 'includes/footer.php';
    exit();
}
?>

<h1>Profil Saya</h1>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Informasi Pengguna</h5>
    </div>
    <div class="card-body">
        <p><strong>Nama:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Tiket Saya</h5>
    </div>
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">Anda belum memesan tiket apa pun.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID Pemesanan</th>
                            <th>Film</th>
                            <th>Waktu Tayang</th>
                            <th>Studio</th>
                            <th>Jumlah Tiket</th>
                            <th>Total Harga</th>
                            <th>Tanggal Pemesanan</th>
                            <th>Aksi</th> </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['title']); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($booking['show_time'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['cinema_hall']); ?></td>
                                <td><?php echo htmlspecialchars($booking['num_tickets']); ?></td>
                                <td>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <a href="view.ticket.php?id=<?php echo htmlspecialchars($booking['booking_id']); ?>" class="btn btn-sm btn-info">Detail</a>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>