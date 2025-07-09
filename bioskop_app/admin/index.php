<?php
include '../includes/auth_check.php'; // Proteksi untuk semua user login
include '../config/database.php';

// Pastikan hanya role 'admin' yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'admin') {
    header("Location: /bioskop_app/index.php"); // Redirect jika bukan admin
    exit();
}

include '../includes/header.php';

// Agregat Operations
$stmt_total_revenue = $pdo->query("SELECT SUM(total_price) AS total FROM bookings");
$total_revenue = $stmt_total_revenue->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_total_tickets = $pdo->query("SELECT SUM(num_tickets) AS total FROM bookings");
$total_tickets = $stmt_total_tickets->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_most_sold_movie = $pdo->query("
    SELECT m.title, SUM(b.num_tickets) AS tickets_sold
    FROM bookings b
    JOIN schedules s ON b.schedule_id = s.schedule_id
    JOIN movies m ON s.movie_id = m.movie_id
    GROUP BY m.title
    ORDER BY tickets_sold DESC
    LIMIT 1
");
$most_sold_movie = $stmt_most_sold_movie->fetch(PDO::FETCH_ASSOC);

// Query untuk Film Paling Sedikit Ditonton
$stmt_least_sold_movie = $pdo->query("
    SELECT m.title, SUM(b.num_tickets) AS tickets_sold
    FROM bookings b
    JOIN schedules s ON b.schedule_id = s.schedule_id
    JOIN movies m ON s.movie_id = m.movie_id
    GROUP BY m.title
    ORDER BY tickets_sold ASC
    LIMIT 1
");
$least_sold_movie = $stmt_least_sold_movie->fetch(PDO::FETCH_ASSOC); // Perbaikan di sini

// Query untuk Rata-rata Tiket Terjual per Film
$stmt_avg_tickets_per_movie = $pdo->query("
    SELECT AVG(tickets_sold_per_movie) AS average_tickets
    FROM (
        SELECT SUM(b.num_tickets) AS tickets_sold_per_movie
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        GROUP BY s.movie_id
    ) AS movie_sales_summary
");
$avg_tickets_per_movie = $stmt_avg_tickets_per_movie->fetch(PDO::FETCH_ASSOC)['average_tickets'];


// Menggunakan VIEW
$stmt_daily_sales = $pdo->query("SELECT * FROM daily_sales_summary ORDER BY sales_date DESC LIMIT 5");
$daily_sales = $stmt_daily_sales->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="mb-4">Dashboard Admin</h1>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Pendapatan</h5>
                <p class="card-text fs-3">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Tiket Terjual</h5>
                <p class="card-text fs-3"><?php echo number_format($total_tickets, 0, ',', '.'); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Film Terlaris</h5>
                <p class="card-text fs-3">
                    <?php echo $most_sold_movie ? htmlspecialchars($most_sold_movie['title']) . " (" . $most_sold_movie['tickets_sold'] . " tiket)" : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-warning shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Film Paling Sedikit Ditonton</h5>
                <p class="card-text fs-3">
                    <?php echo $least_sold_movie ? htmlspecialchars($least_sold_movie['title']) . " (" . $least_sold_movie['tickets_sold'] . " tiket)" : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-danger shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Rata-rata Tiket Terjual per Film</h5>
                <p class="card-text fs-3">
                    <?php echo $avg_tickets_per_movie ? number_format($avg_tickets_per_movie, 2, ',', '.') : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Manajemen Konten</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="movies.php" class="list-group-item list-group-item-action">Kelola Film</a>
                <a href="schedules.php" class="list-group-item list-group-item-action">Kelola Jadwal Tayang</a>
                <a href="bookings.php" class="list-group-item list-group-item-action">Lihat Pemesanan</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Ringkasan Penjualan Harian (VIEW)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($daily_sales)): ?>
                    <p>Belum ada data penjualan.</p>
                <?php else: ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Total Pendapatan</th>
                                <th>Total Tiket Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daily_sales as $sale): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sale['sales_date']); ?></td>
                                    <td>Rp <?php echo number_format($sale['total_revenue'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($sale['total_tickets_sold']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
