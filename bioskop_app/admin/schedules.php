<?php
include '../includes/auth_check.php'; // Proteksi untuk semua user login
include '../config/database.php';

// Pastikan hanya role 'admin' yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'admin') {
    header("Location: /bioskop_app/index.php"); // Redirect jika bukan admin
    exit();
}

include '../includes/header.php';

$message = '';

// Proses Tambah Jadwal
if (isset($_POST['add_schedule'])) {
    $movie_id = $_POST['movie_id'];
    $show_time = $_POST['show_time']; // Format YYYY-MM-DDTHH:MM
    $cinema_hall = $_POST['cinema_hall'];
    $price = $_POST['price'];

    try {
        $stmt = $pdo->prepare("INSERT INTO schedules (movie_id, show_time, cinema_hall, price) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$movie_id, $show_time, $cinema_hall, $price])) {
            $message = "<div class='alert alert-success'>Jadwal berhasil ditambahkan!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal menambahkan jadwal.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Proses Edit Jadwal
if (isset($_POST['edit_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    $movie_id = $_POST['movie_id'];
    $show_time = $_POST['show_time'];
    $cinema_hall = $_POST['cinema_hall'];
    $price = $_POST['price'];

    try {
        $stmt = $pdo->prepare("UPDATE schedules SET movie_id = ?, show_time = ?, cinema_hall = ?, price = ? WHERE schedule_id = ?");
        if ($stmt->execute([$movie_id, $show_time, $cinema_hall, $price, $schedule_id])) {
            $message = "<div class='alert alert-success'>Jadwal berhasil diperbarui!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal memperbarui jadwal.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Proses Hapus Jadwal
if (isset($_GET['delete_id'])) {
    $schedule_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE schedule_id = ?");
        if ($stmt->execute([$schedule_id])) {
            $message = "<div class='alert alert-success'>Jadwal berhasil dihapus!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal menghapus jadwal.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Ambil semua film untuk dropdown pilihan
$stmt_movies = $pdo->query("SELECT movie_id, title FROM movies ORDER BY title ASC");
$movies_list = $stmt_movies->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua jadwal tayang beserta judul filmnya
$stmt_schedules = $pdo->query("
    SELECT s.*, m.title AS movie_title
    FROM schedules s
    JOIN movies m ON s.movie_id = m.movie_id
    ORDER BY s.show_time DESC
");
$schedules = $stmt_schedules->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="mb-4">Manajemen Jadwal Tayang</h1>

<?php echo $message; ?>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Tambah Jadwal Baru</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="movie_id" class="form-label">Film</label>
                <select class="form-select" id="movie_id" name="movie_id" required>
                    <option value="">Pilih Film</option>
                    <?php foreach ($movies_list as $movie): ?>
                        <option value="<?php echo htmlspecialchars($movie['movie_id']); ?>">
                            <?php echo htmlspecialchars($movie['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="show_time" class="form-label">Waktu Tayang</label>
                <input type="datetime-local" class="form-control" id="show_time" name="show_time" required>
            </div>
            <div class="mb-3">
                <label for="cinema_hall" class="form-label">Studio Bioskop</label>
                <input type="text" class="form-control" id="cinema_hall" name="cinema_hall" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga Tiket (Rp)</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required min="0">
            </div>
            <button type="submit" name="add_schedule" class="btn btn-primary">Tambah Jadwal</button>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Daftar Jadwal Tayang</h5>
    </div>
    <div class="card-body">
        <?php if (empty($schedules)): ?>
            <div class="alert alert-info">Belum ada jadwal tayang yang terdaftar.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Film</th>
                            <th>Waktu Tayang</th>
                            <th>Studio</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($schedule['schedule_id']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['movie_title']); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($schedule['show_time'])); ?></td>
                                <td><?php echo htmlspecialchars($schedule['cinema_hall']); ?></td>
                                <td>Rp <?php echo number_format($schedule['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning edit-schedule-btn"
                                            data-bs-toggle="modal" data-bs-target="#editScheduleModal"
                                            data-schedule-id="<?php echo htmlspecialchars($schedule['schedule_id']); ?>"
                                            data-movie-id="<?php echo htmlspecialchars($schedule['movie_id']); ?>"
                                            data-show-time="<?php echo date('Y-m-d\TH:i', strtotime($schedule['show_time'])); ?>"
                                            data-cinema-hall="<?php echo htmlspecialchars($schedule['cinema_hall']); ?>"
                                            data-price="<?php echo htmlspecialchars($schedule['price']); ?>">
                                        Edit
                                    </button>
                                    <a href="schedules.php?delete_id=<?php echo $schedule['schedule_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus jadwal ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editScheduleModalLabel">Edit Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editScheduleForm" method="POST">
                    <input type="hidden" name="schedule_id" id="edit_schedule_id">
                    <div class="mb-3">
                        <label for="edit_movie_id" class="form-label">Film</label>
                        <select class="form-select" id="edit_movie_id" name="movie_id" required>
                            <?php foreach ($movies_list as $movie): ?>
                                <option value="<?php echo htmlspecialchars($movie['movie_id']); ?>">
                                    <?php echo htmlspecialchars($movie['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_show_time" class="form-label">Waktu Tayang</label>
                        <input type="datetime-local" class="form-control" id="edit_show_time" name="show_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_cinema_hall" class="form-label">Studio Bioskop</label>
                        <input type="text" class="form-control" id="edit_cinema_hall" name="cinema_hall" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Harga Tiket (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required min="0">
                    </div>
                    <button type="submit" name="edit_schedule" class="btn btn-warning">Update Jadwal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editScheduleModal = document.getElementById('editScheduleModal');
    editScheduleModal.addEventListener('show.bs.modal', function (event) {
        // Tombol yang memicu modal
        var button = event.relatedTarget;

        // Ekstrak informasi dari atribut data-*
        var scheduleId = button.getAttribute('data-schedule-id');
        var movieId = button.getAttribute('data-movie-id');
        var showTime = button.getAttribute('data-show-time');
        var cinemaHall = button.getAttribute('data-cinema-hall');
        var price = button.getAttribute('data-price');

        // Perbarui konten modal.
        var modalForm = editScheduleModal.querySelector('#editScheduleForm');
        modalForm.querySelector('#edit_schedule_id').value = scheduleId;
        modalForm.querySelector('#edit_movie_id').value = movieId;
        modalForm.querySelector('#edit_show_time').value = showTime;
        modalForm.querySelector('#edit_cinema_hall').value = cinemaHall;
        modalForm.querySelector('#edit_price').value = price;
    });
});
</script>
