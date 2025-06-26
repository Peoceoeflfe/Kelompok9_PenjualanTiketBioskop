<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // 
}




include 'config/database.php';
include 'includes/header.php'; 


$stmt = $pdo->query("SELECT * FROM movies ORDER BY movie_id DESC");
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="mb-4">Film yang Sedang Tayang</h1>

<?php if (empty($movies)): ?>
    <div class="alert alert-info">Belum ada film yang tersedia saat ini.</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($movies as $movie): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                        <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($movie['genre']); ?> | <?php echo htmlspecialchars($movie['duration']); ?> menit</p>
                        <p class="card-text mb-auto"><?php echo htmlspecialchars(substr($movie['description'], 0, 100)); ?>...</p>
                        <a href="movie_detail.php?id=<?php echo htmlspecialchars($movie['movie_id']); ?>" class="btn btn-primary mt-3 align-self-start">Lihat Detail & Jadwal</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>