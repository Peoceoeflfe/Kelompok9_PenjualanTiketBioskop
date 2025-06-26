-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Jun 2025 pada 04.35
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bioskop`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddBooking` (IN `p_schedule_id` INT, IN `p_customer_name` VARCHAR(255), IN `p_customer_email` VARCHAR(255), IN `p_num_tickets` INT)   BEGIN
    DECLARE v_total_price DECIMAL(10,2);
    SET v_total_price = CalculateTotalPrice(p_schedule_id, p_num_tickets);
    INSERT INTO bookings (schedule_id, customer_name, customer_email, num_tickets, total_price)
    VALUES (p_schedule_id, p_customer_name, p_customer_email, p_num_tickets, v_total_price);
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateTotalPrice` (`p_schedule_id` INT, `p_num_tickets` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE v_price DECIMAL(10,2);
    SELECT price INTO v_price FROM schedules WHERE schedule_id = p_schedule_id;
    RETURN v_price * p_num_tickets;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `num_tickets` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_date` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `daily_sales_summary`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `daily_sales_summary` (
`sales_date` date
,`total_revenue` decimal(32,2)
,`total_tickets_sold` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `movies`
--

CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `poster_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `genre`, `duration`, `description`, `poster_url`) VALUES
(4, 'KKN DI DESA PENARI', 'Horror, 18+', 132, 'Film ini berdasarkan kisah dongeng', 'https://cdn.antaranews.com/cache/1200x800/2022/05/12/IMG_20220512_135230_013.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `show_time` datetime NOT NULL,
  `cinema_hall` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `movie_id`, `show_time`, `cinema_hall`, `price`) VALUES
(7, 4, '2025-06-26 09:30:00', 'NENEK CGV', 450000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(7, 'kiee123', '$2y$10$G7ff3MORbpgFH0tIsW3E8OYOoAxyuEEbAP9qjMb1GVItUPVuPpOt2', 'kiee@gmail.com', 'admin', '2025-06-26 08:46:49'),
(8, 'admin_master', '$2y$10$29h/chwVfo70UarZR6Z4JO/Y/4CMGqcM/Uo9/hxA7R9m6CLsdsYTe', '123@123.com', 'user', '2025-06-26 08:47:29'),
(9, 'rizkie', '$2y$10$INbkNVl5q4yig7BgaMSq0ekguTw3jDC8CRNY471N9mWJYDaquq7v2', 'rizkie123@gmail.com', 'admin', '2025-06-26 08:50:38'),
(10, 'biasa', '$2y$10$HE8ChXukh8EQL7TM6c4vZ.uWCc9.xRH0yVjwJd3ULDbGajb8Pb2PS', '123456@123456.com', 'user', '2025-06-26 08:51:52'),
(11, 'adminrizkie', '$2y$10$pI8Q0NiXFUZ3eiVtviMbrOqWQ1gJPOfbRCCoN2zv5dv49qcN/.vkK', 'rizkieadmin@admin.com', 'admin', '2025-06-26 09:28:48');

-- --------------------------------------------------------

--
-- Struktur untuk view `daily_sales_summary`
--
DROP TABLE IF EXISTS `daily_sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_sales_summary`  AS SELECT cast(`bookings`.`booking_date` as date) AS `sales_date`, sum(`bookings`.`total_price`) AS `total_revenue`, sum(`bookings`.`num_tickets`) AS `total_tickets_sold` FROM `bookings` GROUP BY cast(`bookings`.`booking_date` as date) ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_booking_schedule_id` (`schedule_id`),
  ADD KEY `fk_user_booking` (`user_id`);

--
-- Indeks untuk tabel `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_schedule_movie_id` (`movie_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_booking` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
