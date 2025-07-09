-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2025 at 10:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddBooking` (IN `p_schedule_id` INT, IN `p_customer_name` VARCHAR(255), IN `p_customer_email` VARCHAR(255), IN `p_num_tickets` INT)   BEGIN
    DECLARE v_total_price DECIMAL(10,2);
    SET v_total_price = CalculateTotalPrice(p_schedule_id, p_num_tickets);
    INSERT INTO bookings (schedule_id, customer_name, customer_email, num_tickets, total_price)
    VALUES (p_schedule_id, p_customer_name, p_customer_email, p_num_tickets, v_total_price);
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateTotalPrice` (`p_schedule_id` INT, `p_num_tickets` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE v_price DECIMAL(10,2);
    SELECT price INTO v_price FROM schedules WHERE schedule_id = p_schedule_id;
    RETURN v_price * p_num_tickets;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
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

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `schedule_id`, `customer_name`, `customer_email`, `num_tickets`, `total_price`, `booking_date`, `user_id`) VALUES
(10, 7, 'biasa', '123456@123456.com', 1, 450000.00, '2025-07-10 00:39:51', 10),
(11, 7, 'biasa', '123456@123456.com', 1, 450000.00, '2025-07-10 00:49:17', 10),
(12, 8, 'biasa', '123456@123456.com', 1, 40000.00, '2025-07-10 00:53:22', 10),
(13, 8, 'biasa', '123456@123456.com', 150, 6000000.00, '2025-07-10 01:17:55', 10);

-- --------------------------------------------------------

--
-- Stand-in structure for view `daily_sales_summary`
-- (See below for the actual view)
--
CREATE TABLE `daily_sales_summary` (
`sales_date` date
,`total_revenue` decimal(32,2)
,`total_tickets_sold` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `movies`
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
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `genre`, `duration`, `description`, `poster_url`) VALUES
(4, 'KKN DI DESA PENARI', 'Horror, 18+', 132, 'Film ini berdasarkan kisah dongeng', 'https://upload.wikimedia.org/wikipedia/id/b/b7/KKN_di_Desa_Penari.jpg'),
(5, 'Superman', 'Adventure', 129, '\"Superman,\" film layar lebar pertama DC Studios, siap melesat ke bioskop di seluruh dunia Juli ini dari Warner Bros. Pictures. Dengan gaya khasnya, James Gunn menggarap pahlawan super orisinal ini di DC Universe yang baru, dengan perpaduan unik antara aksi epik, humor, dan hati, menghadirkan Superman yang didorong oleh kasih sayang dan keyakinan bawaan akan kebaikan umat manusia.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTyDiAf5rnHP0PzIBVsCny_aZSefrMsrUWwsQ&s');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `show_time` datetime NOT NULL,
  `cinema_hall` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `movie_id`, `show_time`, `cinema_hall`, `price`) VALUES
(7, 4, '2025-06-26 09:30:00', 'NENEK CGV', 450000.00),
(8, 5, '2025-07-22 00:52:00', 'CGV Nenek', 40000.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
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
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(7, 'arif', '$2y$10$G7ff3MORbpgFH0tIsW3E8OYOoAxyuEEbAP9qjMb1GVItUPVuPpOt2', 'arif@gmail.com', 'admin', '2025-06-26 08:46:49'),
(9, 'rizkie', '$2y$10$INbkNVl5q4yig7BgaMSq0ekguTw3jDC8CRNY471N9mWJYDaquq7v2', 'rizkie123@gmail.com', 'admin', '2025-06-26 08:50:38'),
(10, 'biasa', '$2y$10$HE8ChXukh8EQL7TM6c4vZ.uWCc9.xRH0yVjwJd3ULDbGajb8Pb2PS', '123456@123456.com', 'user', '2025-06-26 08:51:52'),
(11, 'adminrizkie', '$2y$10$pI8Q0NiXFUZ3eiVtviMbrOqWQ1gJPOfbRCCoN2zv5dv49qcN/.vkK', 'rizkieadmin@admin.com', 'admin', '2025-06-26 09:28:48');

-- --------------------------------------------------------

--
-- Structure for view `daily_sales_summary`
--
DROP TABLE IF EXISTS `daily_sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_sales_summary`  AS SELECT cast(`bookings`.`booking_date` as date) AS `sales_date`, sum(`bookings`.`total_price`) AS `total_revenue`, sum(`bookings`.`num_tickets`) AS `total_tickets_sold` FROM `bookings` GROUP BY cast(`bookings`.`booking_date` as date) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_booking_schedule_id` (`schedule_id`),
  ADD KEY `fk_user_booking` (`user_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_schedule_movie_id` (`movie_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_booking` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
