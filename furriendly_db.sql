-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2025 at 04:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `furriendly_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `pet_name` varchar(100) DEFAULT NULL,
  `pet_species` varchar(100) DEFAULT NULL,
  `pet_age` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `username`, `pet_name`, `pet_species`, `pet_age`) VALUES
(1, 'maru123', 'Maru', 'Dog', 4),
(2, 'kakai', 'Maru', 'Dog', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `name`, `email`, `bio`, `profile_pic`) VALUES
(1, 'admin', '$2y$10$SK5OAU5lU4tNYJTqQvuWP.sBHgnH9VMN3IKQ3qVhROiK7tIXztCOa', '2025-10-09 13:54:49', NULL, NULL, NULL, NULL),
(2, 'rikitu123', '$2y$10$V7oAinCqtgRfP4ZYTfhqye03r1MXvk2mGw5fWkygbfrK2dZ0lkIhy', '2025-10-09 14:02:13', NULL, NULL, NULL, NULL),
(3, 'baby kakai', '$2y$10$tjEvhlOx8AzWbV.xQzGW.OtAgSycA.UliDJFr8bF9P.E94Pw/yiki', '2025-10-09 14:18:44', NULL, NULL, NULL, NULL),
(4, 'maru123', '$2y$10$KwQ41sTSYox02R99Sm02wOvLBL68vEvPj.XZGIhliWq7vE1qi3/kO', '2025-10-09 14:22:38', 'Baby Kakai', 'rikitumangalindan@gmail.com', 'kyut', '../uploads/1760021544_Screenshot 2025-10-09 224157.png'),
(5, 'rikitukokiii', '$2y$10$GDhHHYkt80Z6wBXIP0NabOsBktfozJ4iXuuWFQOf694KQhjOzmyyC', '2025-10-10 02:26:36', NULL, NULL, NULL, NULL),
(6, 'kakai', '$2y$10$JBjNRuW1F3D7on6Q89wDk.947TlQMYFCcOjjlYnis3KbwAIEQuMp.', '2025-10-10 02:31:25', 'Baby ni Kakai', 'moymoymangalindan13@gmail.com', 'meowmeowmeow', NULL),
(7, 'richard', '$2y$10$XvHqe7krJlaZqfy67yXfMuy.7B1bkkWa1QgQBuuf8xdvd/kCPfLU.', '2025-10-10 02:40:58', 'Richard Ocana', 'meowmeowmeow@gmail.com', 'dadadadadadadada', '../uploads/1760064098_Screenshot 2025-10-09 190602.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
