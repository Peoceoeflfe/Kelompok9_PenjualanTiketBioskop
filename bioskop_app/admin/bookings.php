<?php
include '../includes/auth_check.php'; // Proteksi untuk semua user login
include '../config/database.php';

// Pastikan hanya role 'admin' yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'admin') {
    header("Location: /bioskop_app/index.php"); // Redirect jika bukan admin
    exit();
}

include '../includes/header.php';

// Ambil semua pemesanan beserta detail film dan jadwalnya
$stmt_bookings = $pdo->query("
    SELECT
        b.*,
        s.show_time,
        s.cinema_hall,
        s.price AS schedule_price,
        m.title AS movie_title
    FROM bookings b
    JOIN schedules s ON b.schedule_id = s.schedule_id
    JOIN movies m ON s.movie_id = m.movie_id
    ORDER BY b.booking_date DESC
");
$bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="mb-4">Daftar Pemesanan Tiket</h1>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Pemesanan Pelanggan</h5>
    </div>
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">Belum ada pemesanan tiket.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID Pemesanan</th>
                            <th>Film</th>
                            <th>Waktu Tayang</th>
                            <th>Studio</th>
                            <th>Pelanggan</th>
                            <th>Email</th>
                            <th>Jumlah Tiket</th>
                            <th>Total Harga</th>
                            <th>Tanggal Pemesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($booking['show_time'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['cinema_hall']); ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_email']); ?></td>
                                <td><?php echo htmlspecialchars($booking['num_tickets']); ?></td>
                                <td>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>