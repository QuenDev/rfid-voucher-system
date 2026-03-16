-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 08:35 AM
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
-- Database: `university_voucher_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminstafflogs`
--

CREATE TABLE `adminstafflogs` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminstafflogs`
--

INSERT INTO `adminstafflogs` (`id`, `username`, `fullname`, `office`, `password`, `role`, `timestamp`) VALUES
(22, 'Admin', 'Narcisso Domingo', 'Library', '$2y$10$Y7SKTWISZTxCXCAdQsr.9upQAZWBiLw8.NHmFVJQuzmlpBn7C0g3u', 'admin', '2025-05-13 20:06:30'),
(23, 'Quen', 'Quenedy Pabular', 'Library', '$2y$10$QOP.YqwpJUUf72F1vQqCFeFneJzvIqH4PUezSlO6E9KtJbqLQ5XuW', 'staff', '2025-05-15 00:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `student_vouchers`
--

CREATE TABLE `student_vouchers` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_vouchers`
--

INSERT INTO `student_vouchers` (`id`, `student_id`, `voucher_id`, `redeemed_at`) VALUES
(1, '20-10563', 819, '2025-05-14 08:00:37'),
(2, '21-11153', 818, '2025-05-14 08:12:19'),
(3, '20-10563', 817, '2025-05-14 08:12:24'),
(4, '21-11153', 816, '2025-05-14 08:12:28'),
(5, '20-10563', 815, '2025-05-14 08:12:46'),
(6, '20-10563', 814, '2025-05-14 08:12:48'),
(7, '20-10563', 813, '2025-05-14 08:12:50'),
(8, '21-11153', 812, '2025-05-14 08:12:57'),
(9, '21-11153', 811, '2025-05-14 08:21:40'),
(10, '20-10563', 810, '2025-05-14 08:31:01'),
(11, '20-10563', 809, '2025-05-14 08:31:02'),
(12, '20-10563', 808, '2025-05-14 08:31:06'),
(13, '20-10563', 807, '2025-05-14 08:31:08'),
(14, '20-10563', 806, '2025-05-14 08:31:10'),
(15, '20-10563', 805, '2025-05-14 08:31:11'),
(16, '21-11153', 804, '2025-05-14 08:31:14'),
(17, '20-10563', 803, '2025-05-14 08:31:35'),
(18, '20-10563', 801, '2025-05-14 08:31:39'),
(19, '20-10563', 800, '2025-05-14 08:57:49'),
(20, '23-10606', 799, '2025-05-15 01:01:45'),
(21, '23-10606', 798, '2025-05-15 01:02:13'),
(22, '21-11153', 797, '2025-05-15 01:35:17'),
(23, '21-11153', 796, '2025-05-15 01:35:21'),
(24, '21-11153', 795, '2025-05-15 01:35:23'),
(25, '21-11153', 794, '2025-05-15 01:35:25'),
(26, '21-11153', 793, '2025-05-15 01:35:29'),
(27, '21-11153', 792, '2025-05-15 01:35:31'),
(28, '21-11153', 791, '2025-05-15 01:35:35'),
(29, '21-11153', 779, '2025-05-15 01:36:04'),
(30, '21-11153', 778, '2025-05-15 01:36:07'),
(31, '21-11153', 737, '2025-05-15 06:12:27'),
(32, '21-11153', 738, '2025-05-15 06:12:42'),
(33, '21-11153', 739, '2025-05-15 06:12:45'),
(34, '21-11153', 740, '2025-05-15 06:13:22'),
(35, '21-11153', 741, '2025-05-15 06:13:34'),
(36, '21-11153', 742, '2025-05-15 06:13:40'),
(37, '21-11153', 743, '2025-05-15 06:21:07'),
(38, '21-11153', 744, '2025-05-15 06:21:10'),
(39, '21-11153', 745, '2025-05-15 06:21:22'),
(40, '21-11153', 746, '2025-05-15 06:21:34'),
(41, '21-11153', 747, '2025-05-15 06:30:34'),
(42, '20-10563', 748, '2025-05-15 06:30:39'),
(43, '20-10563', 749, '2025-05-15 06:30:47'),
(44, '20-10563', 750, '2025-05-15 06:30:50'),
(45, '20-10563', 751, '2025-05-15 06:30:53'),
(46, '20-10563', 752, '2025-05-15 06:30:56'),
(47, '20-10563', 753, '2025-05-15 06:30:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `rfid` varchar(50) DEFAULT NULL,
  `student_id` varchar(50) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `sex` enum('M','F') NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `rfid`, `student_id`, `last_name`, `first_name`, `middle_name`, `sex`, `course`, `year`, `section`, `picture`, `created_at`, `photo`) VALUES
(34721, 'a6762d79', '23-10606', 'Antiporda', 'Ashley', 'Datulayta', 'M', 'BA-ELS', 1, 'BA-ELS 1', '681c26744a770_22-10147.jpg', '2025-04-25 07:46:21', NULL),
(34724, NULL, '24-15846', 'Barayuga', 'Mark Lester', 'Ramos', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34725, NULL, '24-15951', 'Bautista', 'Jezmine Michaela', 'Bayubay', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34726, NULL, '24-16897', 'Bernardo', 'Jules Dhanielle', 'Cayetano', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34727, NULL, '24-15979', 'Bulacan', 'John Vergel', 'Valdez', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34728, NULL, '24-15937', 'Busto', 'Joyce', 'Sagadraca', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34729, NULL, '24-16055', 'Cadiz', 'Wendie', 'Melegrito', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34730, NULL, '24-15819', 'Camaddu', 'Estrella', 'Rosete', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34731, NULL, '24-16041', 'Carbonel', 'Jamaica', 'Guillermo', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34732, NULL, '23-11591', 'Casiano', 'Cris-Ann', '', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34733, NULL, '24-16044', 'Gazmin', 'Pia Bon Blessie', 'Taguba', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34734, NULL, '24-15827', 'Guillermo', 'Ladylmae', 'Rabutan', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34735, NULL, '24-16063', 'Jabonitalla', 'May Rose', 'Flores', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34736, NULL, '24-15966', 'Lauayan', 'Reilven', 'Dario', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34737, NULL, '24-15938', 'Laygo', 'Sweethel Joy', 'Delangsoy', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34738, NULL, '24-15940', 'Legaspi', 'Elmar', 'Manayan', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34739, NULL, '24-15935', 'Madriaga', 'Kimberly', 'Villanueva', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34740, NULL, '24-16036', 'Malabug', 'Katlyn', 'Aguilar', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34741, NULL, '24-16062', 'Malana', 'Dondie', 'Octaviano', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-04-25 07:46:21', NULL),
(34752, 'RFID12345', '20-10616', 'Pabular', 'Quenedy', 'Gelacio', 'M', 'BA-ELS', 4, 'BA-ELS 1', '', '2025-04-29 07:21:59', NULL),
(34754, '70978f23', '21-11153', 'Marcos', 'Princess', 'I', 'F', 'BSIT', 4, '4a Wmad', '681c24f3756da_23-13496.jpeg', '2025-05-08 01:55:20', NULL),
(34755, '20b0e023', '20-10613', 'Bucag', 'Mark Lester', 'Gabba', 'M', 'BSIT', 4, '4A WMAD', '68243f9c54f15.jpeg', '2025-05-14 07:00:44', NULL),
(34756, '109e1d24', '20-10563', 'Del Rosario', 'John Celson', 'Manuel', 'M', 'BSIT', 4, '4A WMAD', '682447e0ed099.jpg', '2025-05-14 07:36:00', NULL),
(34757, NULL, '24-15928', 'Abuan', 'Cristine', 'Jose', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34758, NULL, '24-16619', 'Adina', 'Ed kyler', 'Mangrubang', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34759, NULL, '24-15939', 'Agub', 'Lyka Nicole', 'Paña', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34760, NULL, '24-15963', 'Ancheta', 'Julia Rachelle', 'Gayap', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34761, NULL, '23-10735', 'Asuncion', 'Christian', 'Mauricio', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34762, NULL, '24-15962', 'Corpuz', 'Ismael', 'Gelacio', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34763, NULL, '24-16047', 'Cosilit', 'Paricia', 'Ramos', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34764, NULL, '24-15965', 'Del Moro', 'Angelo', 'Andres', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34765, NULL, '24-15985', 'Dela Cruz', 'Carlo Dave', 'Alterado', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34766, NULL, '24-16057', 'Dizon', 'Irah Jane', 'Materum', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34767, NULL, '24-17138', 'Drez', 'Reymark', '', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34768, NULL, '24-15845', 'Edra', 'Virginia', 'Jayme', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34769, NULL, '24-16496', 'Farrales', 'King Israel', 'Lata', 'M', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL),
(34770, NULL, '24-15933', 'Gallema', 'Princess Myla', 'Alcantara', 'F', 'BA-ELS', 1, 'BA-ELS 1', NULL, '2025-05-15 06:18:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `voucher_code` varchar(50) NOT NULL,
  `office_department` varchar(100) NOT NULL,
  `date_issued` timestamp NOT NULL DEFAULT current_timestamp(),
  `minutes_valid` int(11) NOT NULL,
  `status` enum('available','used') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucher_code`, `office_department`, `date_issued`, `minutes_valid`, `status`) VALUES
(735, 'VCHR-67FC7F6F596D7', 'MIS OFFICE', '2025-04-25 07:58:01', 58, 'used'),
(736, 'Gb8zbrfndxu', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(737, 'EqpTRGjSLLN', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(738, 'P32C3rxGcnd3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(739, 'QvKtHyEMSyn3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(740, '2MeCxHhhGrM', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(741, 'r54svaky85U', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(742, 'Hdk7kAftV6H', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(743, 'H83SGeZLqar3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(744, 'MZdYeaJz2tf3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(745, 'a6uJB7nSYyj', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(746, 'e7xmwhsuvTe', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(747, 'N8NYSpUePA33', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(748, 'yZMMmU63yyc', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(749, 'smpntEJj8Vf', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(750, '7PVZjMWebvm', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(751, 'JLbGfErxKuq', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(752, 'yB27QZULkZf3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(753, 'bLANjNwep5U', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(754, 'Ex6fa4xUzv63', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(755, 'rfwsNS3mnj83', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(756, 'F6NynNMjEDy', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(757, '7HxWaYPGGwq3', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(758, 'VeWbtvdpJLr3', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(759, 'jY35BynrSan', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(760, 'DV83sadUzE', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(761, 'KnqFqqpcUVJ', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(762, 'MQMXrk3fTr3', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(763, 'UAUfpB2VvF23', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(764, 'BVkAnfBxdT43', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(765, '4cu6Qdzpb3n3', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(766, 'vHdPLCfLC8B', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(767, 'tqJa52Cran53', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(768, 'whBw5XHjBxP', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(769, 'bDrsE8GvudB', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(770, 'cqSqYYz3E5U', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(771, 'ZB6yzur3CZh', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(772, '5rphj4a3Wu5', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(773, 'TGmZXxKh4KS', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(774, '6Y3r4HNQhYY', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(775, 'nUJS5HCmBVc', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(776, 'TmfNUbvcTKY', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(777, 'W5pYGzZ8HwR', 'Library', '2025-04-25 07:58:31', 60, 'available'),
(778, 'MZbXpCjhGUk', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(779, 'FqqxpZdSWzA', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(791, 'T2fppkmaTRJ', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(792, 'pNxruDEh23n3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(793, 'KJrdpBwvAmT', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(794, 'jFYTVCbzpkb', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(795, 'Zeu7AQyc7pj', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(796, 'ULZk85r5Lmq', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(797, 'CZK2QzdUkBn3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(798, '5F6QfFULvD3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(799, 'MYVDWmFwPte3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(800, 'xGkPeVLQKfT', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(801, '78edukXHCe7', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(803, 'GhyZMLwFVtf', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(804, 'qXPZYQ4h4ww', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(805, 'zvWbtJM6Tb73', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(806, 'yTDQNQtnEAf3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(807, 'tFsWEu4BcXd3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(808, '2EDYpBCJcte', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(809, 'CrjjDLXWcL3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(810, 'MrbqsZEuPfT', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(811, 'VMLeCFQz5PJ', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(812, 'UFRNKMe8sxW', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(813, 'qBC3Fnw5Yp8', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(814, 'nZxwxLZ7b3e', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(815, 'LxtV6KkPCyN', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(816, 'UJrXSPBZawu', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(817, 'kW87q2upKeM', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(818, 'D8AMfXkqjj7', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(819, '2KwBLhdZYyL', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(820, 'A65LNHqj5sD', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(821, 'xHen5SAcQc7', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(822, '6YkQhXtPe3p', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(823, 'D2KmDD4Kkf3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(824, 'BfhjqZkKwmd', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(825, 'naZrpVC2fRy', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(826, 'FbtBkCbA5NQ', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(827, 'fnb5dFLjQ5f3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(828, '85rQ3D43CcY', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(829, '3uUPp5ZsVtr3', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(830, 'Yb57eMEN64c', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(831, 'aysDuEwvmpX', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(832, 'hCW6v3Sbm2h', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(833, 'ymZTWjq5N5U', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(834, 'zseUFBVcr823', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(835, '5TZBBxXATVd', 'Library', '2025-04-25 07:58:31', 60, 'used'),
(881, 'W5nZbbnKRdY', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(882, 'ncKFj6VDVAe', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(883, 'FySMm2B7mrq3', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(884, 'jRLNRxJG58s', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(885, 'fNarXMxVpWZ', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(886, 'PxAqCYUCcAh3', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(887, 'm8rXamkRwUA', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(888, 'sRGXn5ym2de', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(889, 'KS2dQhbZrUa3', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(890, 'sVsFPzTqhr5', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(891, 'a7RpHHNhUaY', 'Library', '2025-05-15 02:02:12', 60, 'available'),
(903, 'uPQq3zPLCB83', 'Library', '2025-05-15 02:02:12', 60, 'available');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminstafflogs`
--
ALTER TABLE `adminstafflogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `student_vouchers`
--
ALTER TABLE `student_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`student_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `rfid` (`rfid`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_code` (`voucher_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminstafflogs`
--
ALTER TABLE `adminstafflogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `student_vouchers`
--
ALTER TABLE `student_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34771;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=937;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_vouchers`
--
ALTER TABLE `student_vouchers`
  ADD CONSTRAINT `student_vouchers_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`),
  ADD CONSTRAINT `student_vouchers_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
