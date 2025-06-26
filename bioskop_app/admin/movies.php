<?php
include '../includes/auth_check.php';
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /bioskop_app/index.php");
    exit();
}

include '../includes/header.php';

$message = '';

// Proses Tambah Film
if (isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    $poster_url = $_POST['poster_url'];

    try {
        $stmt = $pdo->prepare("INSERT INTO movies (title, genre, duration, description, poster_url) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $genre, $duration, $description, $poster_url])) {
            $message = "<div class='alert alert-success'>Film berhasil ditambahkan!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal menambahkan film.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Proses Edit Film
if (isset($_POST['edit_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    $poster_url = $_POST['poster_url'];

    try {
        $stmt = $pdo->prepare("UPDATE movies SET title = ?, genre = ?, duration = ?, description = ?, poster_url = ? WHERE movie_id = ?");
        if ($stmt->execute([$title, $genre, $duration, $description, $poster_url, $movie_id])) {
            $message = "<div class='alert alert-success'>Film berhasil diperbarui!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal memperbarui film.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Proses Hapus Film
if (isset($_GET['delete_id'])) {
    $movie_id = $_GET['delete_id'];
    try {
        // Hapus juga jadwal terkait film ini
        $pdo->beginTransaction();
        $stmt_schedules = $pdo->prepare("DELETE FROM schedules WHERE movie_id = ?");
        $stmt_schedules->execute([$movie_id]);

        $stmt_movie = $pdo->prepare("DELETE FROM movies WHERE movie_id = ?");
        if ($stmt_movie->execute([$movie_id])) {
            $pdo->commit();
            $message = "<div class='alert alert-success'>Film dan jadwal terkait berhasil dihapus!</div>";
        } else {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>Gagal menghapus film.</div>";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Ambil semua film
$stmt_movies = $pdo->query("SELECT * FROM movies ORDER BY movie_id DESC");
$movies = $stmt_movies->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="mb-4">Manajemen Film</h1>

<?php echo $message; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Tambah Film Baru</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Judul Film</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="genre" class="form-label">Genre</label>
                <input type="text" class="form-control" id="genre" name="genre" required>
            </div>
            <div class="mb-3">
                <label for="duration" class="form-label">Durasi (menit)</label>
                <input type="number" class="form-control" id="duration" name="duration" required min="1">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="poster_url" class="form-label">URL Poster</label>
                <input type="url" class="form-control" id="poster_url" name="poster_url" required>
            </div>
            <button type="submit" name="add_movie" class="btn btn-primary">Tambah Film</button>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Daftar Film</h5>
    </div>
    <div class="card-body">
        <?php if (empty($movies)): ?>
            <div class="alert alert-info">Belum ada film yang ditambahkan.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Genre</th>
                            <th>Durasi</th>
                            <th>Deskripsi</th>
                            <th>Poster</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($movie['movie_id']); ?></td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                                <td><?php echo htmlspecialchars($movie['duration']); ?></td>
                                <td><?php echo htmlspecialchars(substr($movie['description'], 0, 70)); ?>...</td>
                                <td><img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="width: 50px; height: auto;"></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm edit-movie-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editMovieModal"
                                            data-movie-id="<?php echo htmlspecialchars($movie['movie_id']); ?>"
                                            data-title="<?php echo htmlspecialchars($movie['title']); ?>"
                                            data-genre="<?php echo htmlspecialchars($movie['genre']); ?>"
                                            data-duration="<?php echo htmlspecialchars($movie['duration']); ?>"
                                            data-description="<?php echo htmlspecialchars($movie['description']); ?>"
                                            data-poster-url="<?php echo htmlspecialchars($movie['poster_url']); ?>">
                                        Edit
                                    </button>
                                    <a href="?delete_id=<?php echo htmlspecialchars($movie['movie_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus film ini dan semua jadwal terkait?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="editMovieModal" tabindex="-1" aria-labelledby="editMovieModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMovieModalLabel">Edit Film</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editMovieForm">
                    <input type="hidden" name="movie_id" id="edit_movie_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Judul Film</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_genre" class="form-label">Genre</label>
                        <input type="text" class="form-control" id="edit_genre" name="genre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_duration" class="form-label">Durasi (menit)</label>
                        <input type="number" class="form-control" id="edit_duration" name="duration" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_poster_url" class="form-label">URL Poster</label>
                        <input type="url" class="form-control" id="edit_poster_url" name="poster_url" required>
                    </div>
                    <button type="submit" name="edit_movie" class="btn btn-warning">Update Film</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editMovieModal = document.getElementById('editMovieModal');
    editMovieModal.addEventListener('show.bs.modal', function (event) {
        // Tombol yang memicu modal
        var button = event.relatedTarget;

        // Ekstrak info dari data-bs-* attributes
        var movieId = button.getAttribute('data-movie-id');
        var title = button.getAttribute('data-title');
        var genre = button.getAttribute('data-genre');
        var duration = button.getAttribute('data-duration');
        var description = button.getAttribute('data-description');
        var posterUrl = button.getAttribute('data-poster-url');

        // Update konten modal
        var modalForm = editMovieModal.querySelector('#editMovieForm');
        modalForm.querySelector('#edit_movie_id').value = movieId;
        modalForm.querySelector('#edit_title').value = title;
        modalForm.querySelector('#edit_genre').value = genre;
        modalForm.querySelector('#edit_duration').value = duration;
        modalForm.querySelector('#edit_description').value = description;
        modalForm.querySelector('#edit_poster_url').value = posterUrl;
    });
});
</script>