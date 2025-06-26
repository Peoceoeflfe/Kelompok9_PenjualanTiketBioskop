<?php
include 'config/database.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$movie_id = $_GET['id'];

// Ambil data film
$stmt_movie = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ?");
$stmt_movie->execute([$movie_id]);
$movie = $stmt_movie->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    echo "<div class='alert alert-danger'>Film tidak ditemukan.</div>";
    include 'includes/auth_check.php'; // Proteksi halaman ini!
include 'config/database.php';
include 'includes/header.php'; 
    exit();
}

// Ambil jadwal tayang film ini
$stmt_schedules = $pdo->prepare("SELECT * FROM schedules WHERE movie_id = ? ORDER BY show_time ASC");
$stmt_schedules->execute([$movie_id]);
$schedules = $stmt_schedules->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-4">
        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($movie['title']); ?>">
    </div>
    <div class="col-md-8">
        <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> menit</p>
        <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>

        <h3 class="mt-4">Jadwal Tayang:</h3>
        <?php if (empty($schedules)): ?>
            <div class="alert alert-info">Belum ada jadwal tayang untuk film ini.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($schedules as $schedule): ?>
                    <a href="book_ticket.php?schedule_id=<?php echo $schedule['schedule_id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo date('d M Y H:i', strtotime($schedule['show_time'])); ?></strong>
                            <br>
                            <small>Studio: <?php echo htmlspecialchars($schedule['cinema_hall']); ?></small>
                        </div>
                        <span class="badge bg-primary rounded-pill">Rp <?php echo number_format($schedule['price'], 0, ',', '.'); ?></span>
                        <button class="btn btn-sm btn-success">Pesan Tiket</button>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>