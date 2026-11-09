-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 08, 2025 at 04:13 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `repairsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `archive_records`
--

CREATE TABLE `archive_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `deleted_data` longtext NOT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_name` (`table_name`),
  KEY `record_id` (`record_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `idx_table_record` (`table_name`, `record_id`),
  KEY `idx_deleted_by` (`deleted_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appliances`
--

CREATE TABLE `appliances` (
  `appliance_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `brand` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `model_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `serial_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_in` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appliances`
--

INSERT INTO `appliances` (`appliance_id`, `customer_id`, `brand`, `product`, `model_no`, `serial_no`, `date_in`, `warranty_end`, `category`, `status`) VALUES
(3, 8, 'oke na ba?', 'TV', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-06-27', '2025-07-11', 'Television', 'Active'),
(5, 10, 'OKI??', 'Washing Machine', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-06-27', '2025-07-11', 'Air Conditioner', 'Active'),
(8, 11, 'Samsung', 'Washing Machine', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-06-28', '2025-06-27', 'Oven', 'Active'),
(10, 13, 'Samsung', 'Oven', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-07-09', '2025-06-28', 'Oven', 'Active'),
(12, 14, 'Samsung', 'Washing Machine', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-06-27', '2025-06-27', 'Oven', 'Active'),
(13, 15, 'OKEEEEEEEEEE', 'Washing Machine', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-06-27', '2025-06-27', 'Washing Machine', 'Active'),
(15, 17, 'Samsung', 'Washing Machine', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-07-17', '2025-07-18', 'Washing Machine', 'Active'),
(16, 6, 'acer', 'laptop', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-08-13', '2025-08-16', 'Other', 'Active'),
(17, 6, 'lenovo', 'laptop', '0G3R5CZNA04756T', 'UN65TU7000FXZA', '2025-08-13', '2025-08-16', 'Other', 'Active'),
(19, 18, 'Nokia', 'iphone-nokia', '23532453456', '12324353464', '2025-10-04', '2025-10-11', 'Other', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `phone_no` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `address`, `phone_no`) VALUES
(6, 'Jun Antoni Reus', 'Indino', 'Maya', '09690540108'),
(7, 'ren', 'oke', 'ambot', '09690540108'),
(8, 'oke', 'oke', 'oke', '1234564587869'),
(10, 'OKOKOKOK', 'Indino', 'sdsgdsf', '1234564587869'),
(11, 'oke', 'Indin0', 'efSFW', '09690540108'),
(13, 'Ren ren', 'Indino', 'Maya mayA', '09690540108'),
(14, 'lj', 'indino', 'minta\'l', '1234564587869'),
(15, 'Louise Juliet', 'henson', 'mintal hospital', '09690540108'),
(17, 'Earl', 'Pangit', 'mars', '1234567899'),
(18, 'new ', 'customer ', 'spm', '09691134567');

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `part_id` int NOT NULL,
  `part_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity_stock` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parts`
--

INSERT INTO `parts` (`part_id`, `part_no`, `description`, `price`, `quantity_stock`) VALUES
(1, 'blah', 'very gamit kaayo sure oi', '1000.00', 14),
(3, 'blahnhhh', 'sagdfsgvxcvd', '124.00', 200),
(4, 'okokoe', 'nice ni sya ', '50.00', 27);

-- --------------------------------------------------------

--
-- Table structure for table `parts_used`
--

CREATE TABLE `parts_used` (
  `part_used_id` int NOT NULL,
  `report_id` int NOT NULL,
  `part_name` varchar(50) NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `parts_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parts_used`
--

INSERT INTO `parts_used` (`part_used_id`, `report_id`, `part_name`, `quantity`, `unit_price`, `parts_total`) VALUES
(9, 7, 'blahnhhh', 2, '124.00', '248.00'),
(17, 9, 'blah', 1, '1000.00', '1000.00'),
(18, 9, 'blahnhhh', 1, '124.00', '124.00'),
(19, 9, 'okokoe', 1, '50.00', '50.00'),
(29, 6, 'okokoe', 3, '50.00', '150.00'),
(30, 6, 'blahnhhh', 3, '124.00', '372.00'),
(46, 11, 'blahnhhh', 1, '124.00', '124.00'),
(50, 10, 'blahnhhh', 2, '124.00', '248.00'),
(51, 10, 'blahnhhh', 3, '124.00', '372.00'),
(52, 10, 'okokoe', 3, '50.00', '150.00');

-- --------------------------------------------------------

--
-- Table structure for table `service_details`
--

CREATE TABLE `service_details` (
  `detail_id` int NOT NULL,
  `report_id` int NOT NULL,
  `service_types` json NOT NULL,
  `service_charge` decimal(10,2) DEFAULT '0.00',
  `date_repaired` date DEFAULT NULL,
  `date_delivered` date DEFAULT NULL,
  `complaint` text,
  `labor` decimal(10,2) DEFAULT '0.00',
  `pullout_delivery` decimal(10,2) DEFAULT '0.00',
  `parts_total_charge` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `receptionist` varchar(50) DEFAULT NULL,
  `manager` varchar(50) DEFAULT NULL,
  `technician` varchar(50) DEFAULT NULL,
  `released_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_details`
--

INSERT INTO `service_details` (`detail_id`, `report_id`, `service_types`, `service_charge`, `date_repaired`, `date_delivered`, `complaint`, `labor`, `pullout_delivery`, `parts_total_charge`, `total_amount`, `receptionist`, `manager`, `technician`, `released_by`) VALUES
(9, 7, '[\"installation\"]', '500.00', NULL, NULL, NULL, NULL, NULL, '248.00', '748.00', 'ok (Technician)', 'ok (Technician)', 'ok (Technician)', 'okokoeoeoke (Technician)'),
(14, 9, '[\"installation\", \"repair\", \"cleaning\"]', '1000.00', '2025-08-19', '2025-08-20', NULL, '3.00', '3.00', '1174.00', '2180.00', 'adminactivee (Secretary)', 'admin123 (Manager)', 'oklizz (Technician)', 'adminactivee (Secretary)'),
(18, 6, '[\"installation\"]', '500.00', NULL, NULL, NULL, NULL, NULL, '522.00', '1022.00', 'ok (Technician)', 'ok (Technician)', 'rennotoke (Technician)', 'ok (Technician)'),
(25, 11, '[\"installation\"]', '500.00', NULL, NULL, NULL, '1.00', '1.00', '124.00', '626.00', 'staff (Secretary)', 'ren (Manager)', 'oklizz (Technician)', 'staff (Secretary)'),
(27, 10, '[\"installation\", \"repair\"]', '800.00', '2025-08-22', '2025-08-25', 'okeoke', '7.00', '4.00', '770.00', '1581.00', 'renadmin (Secretary)', 'admin123 (Manager)', 'oklizz (Technician)', 'renadmin (Secretary)');

-- --------------------------------------------------------

--
-- Table structure for table `service_prices`
--

CREATE TABLE `service_prices` (
  `service_id` int NOT NULL,
  `service_name` varchar(50) NOT NULL,
  `service_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_prices`
--

INSERT INTO `service_prices` (`service_id`, `service_name`, `service_price`, `created_at`) VALUES
(1, 'installation', '500.00', '2025-10-04 17:02:18'),
(2, 'repair', '300.00', '2025-10-04 17:02:18'),
(3, 'cleaning', '200.00', '2025-10-04 17:02:18'),
(4, 'checkup', '250.00', '2025-10-04 17:02:18');

-- --------------------------------------------------------

--
-- Table structure for table `service_reports`
--

CREATE TABLE `service_reports` (
  `report_id` int NOT NULL,
  `customer_name` varchar(50) NOT NULL,
  `appliance_name` varchar(50) NOT NULL,
  `date_in` date NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `dealer` varchar(50) DEFAULT NULL,
  `dop` date DEFAULT NULL,
  `date_pulled_out` date DEFAULT NULL,
  `findings` text,
  `remarks` text,
  `location` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_reports`
--

INSERT INTO `service_reports` (`report_id`, `customer_name`, `appliance_name`, `date_in`, `status`, `dealer`, `dop`, `date_pulled_out`, `findings`, `remarks`, `location`) VALUES
(6, 'Jun Antoni Reus Indino', 'OKEEEEEEEEEE (Washing Machine)', '2025-08-06', 'Completed', NULL, '2025-08-06', NULL, 'unta mo gana?????', NULL, '[\"shop\"]'),
(7, 'OKOKOKOK Indino', 'OKI?? (Air Conditioner)', '2025-08-06', 'Pending', NULL, '2025-08-06', NULL, NULL, NULL, '[\"shop\"]'),
(9, 'Jun Antoni Reus Indino', 'lenovo (Other)', '2025-08-15', 'Completed', 'ELJAY', '2025-08-15', '2025-08-22', 'unta mo gana', 'oke', '[\"shop\"]'),
(10, 'Jun Antoni Reus Indino', 'acer (Other)', '2025-08-18', 'Completed', 'ELJAY', '2025-10-04', '2025-08-25', 'okay na???', 'oke', '[\"shop\"]'),
(11, 'oke oke', 'oke na ba? (Television)', '2025-10-06', 'Pending', 'ELJAYy', '2025-10-06', '2025-10-09', NULL, NULL, '[\"shop\"]');

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `staff_id` int NOT NULL,
  `full_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`staff_id`, `full_name`, `username`, `email`, `password`, `role`, `status`, `date_created`, `last_login`, `reset_token`, `reset_token_expiry`) VALUES
(14, 'adminadmin', 'admin123', 'admin@email.com', '$2y$10$zAz2tZh5z94WpJbAAseVK.F65Pp4x6TE0M1O8HZkwhn8w9.vCVLTq', 'Manager', 'Active', '2025-07-03 00:00:00', '2025-10-07 18:01:15', NULL, NULL),
(16, 'blah okee', 'rennotoke', 'oke@email.com', '$2y$10$HrdFqD1NxZEX5YSQM8aVJOZskmbOiaLkh.Vsmq3bZm.x.Q6QDbMx6', 'Technician', 'Active', '2025-07-03 04:11:52', '2025-07-03 04:11:52', NULL, NULL),
(17, 'Admin Active', 'adminactivee', 'admin@email.com', '$2y$10$nANVu5BnGPM0v4eTHgE3rucUDe8uKLdhskZswutDdjSouwKf/IL1K', 'Secretary', 'Inactive', '2025-07-04 00:42:27', '2025-07-04 00:42:37', NULL, NULL),
(18, 'admin', 'renadmin', 'admin@email.com', '$2y$10$akx4372XiPsoB320taPKCeRUoCEnCW0EI4UzqHs2e8ui2M20vJNWS', 'Secretary', 'Active', '2025-07-04 12:20:52', '2025-07-04 12:20:52', NULL, NULL),
(19, 'okokoeoeoke', 'rennotokee', 'admin@email.com', '$2y$10$dEEu92vvdh6VzkqXxKv6Y.zqExJuUU2ZqHJ7dDTs5VERd27CNVPPO', 'Technician', 'Active', '2025-07-04 12:22:47', '2025-07-04 12:22:47', NULL, NULL),
(20, 'ok', 'oklizz', 'oke@email.com', '$2y$10$adAYFk9mDN/Df5TyPiHjpObVxuWZkFQnBm2biS46YPlwYa9Tk1YnC', 'Technician', 'Active', '2025-07-04 12:25:07', '2025-07-04 12:25:07', NULL, NULL),
(21, 'lj', 'ljjjj', 'oke@email.com', '$2y$10$9mUAtcb2Fgnyztl2dmSzaOHJeu9W7layJZYDMBu6HhWaYAqVp4lNO', 'Secretary', 'Active', '2025-07-04 12:36:09', '2025-07-04 12:36:09', NULL, NULL),
(22, 'blah blah oke', 'okegana', 'oke@email.com', '$2y$10$Y9x2Vlxwypr8112zUVLzVu9Cy7LyA5X1a3954IsXx9iY5UfXDiiii', 'Technician', 'Inactive', '2025-07-04 22:26:31', '2025-07-04 22:26:31', NULL, NULL),
(23, 'era dumangcas', 'era123', 'eradumangcas7@gmail.com', '$2y$10$cp8ih5L.eI.psQieboJCh.HMuwTz3t9J6KU0LOBLq.dlzNH8efSOu', 'Manager', 'Active', '2025-10-02 23:35:41', '2025-10-03 00:03:10', NULL, NULL),
(24, 'Ren Admin', 'ren', 'renindino02@gmail.com', '$2y$10$3Uw1mlyF/KieGw/Nxka1Cezdcif5l0VeAcw2Hr/rUXItf7wwEhqau', 'Manager', 'Active', '2025-10-03 00:57:16', '2025-10-03 17:04:04', NULL, NULL),
(25, 'Staff User', 'staff', 'Staff@example.com', '$2y$10$/8phNgC6N2VhMqfIlpdf7.z5MWOd4VdEjpS29WUsbrAOEFDF.qdSW', 'Secretary', 'Active', '2025-10-03 17:07:10', '2025-10-05 18:46:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int NOT NULL,
  `report_id` int DEFAULT NULL,
  `parts_total` decimal(10,2) DEFAULT NULL,
  `labor_total` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `received_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `report_id`, `parts_total`, `labor_total`, `total_amount`, `payment_status`, `payment_date`, `received_by`) VALUES
(1, 10, NULL, NULL, '1581.00', 'Pending', NULL, '14'),
(2, 9, NULL, NULL, '2180.00', 'Pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `staff_id`, `session_token`, `created_at`, `expires_at`, `is_active`, `ip_address`, `user_agent`) VALUES
(83, 14, '00c769251b249f2129a63a96bda0cda882a9a5d593ac28661d642a5f4435a34c', '2025-10-02 03:10:22', '2025-10-02 11:10:22', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(84, 14, '48c6ca3c4043b1d103c18185e13480091d365f2d52264a09a42f53a82cc081af', '2025-10-02 05:55:46', '2025-10-02 13:55:46', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(85, 14, '5c5a5fcc0ed1177c838844179e0294a186e0bf6e7dfa22c595acb1b2a611c417', '2025-10-02 05:58:46', '2025-10-02 13:58:46', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(86, 14, 'a8db64bda3859b91f0025cbb642423c89b555b9afcff7ac2aaf983006bc9121a', '2025-10-02 06:00:51', '2025-10-02 14:00:51', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(87, 14, '569a8e1c08ded42c6bd81c92fc21afd164fe61294c1363038e8996c8e1ef1968', '2025-10-02 06:05:41', '2025-10-02 14:05:41', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(88, 14, '509ce7520b9b7d7219c4b7d536ea1fca6eee3f4521ba4d556eda74a058126595', '2025-10-02 06:11:29', '2025-10-02 14:11:29', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(89, 14, 'f7612638ba6a38d686c2b6d6ef8435430bd5f74c97617b9dc7ff3e1ee77ea727', '2025-10-02 15:08:39', '2025-10-02 23:08:39', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(90, 14, '27b92724f29120e0d186ad619285b163481ac46b5d514673b7b41fbcad5f7054', '2025-10-02 15:35:06', '2025-10-02 23:36:14', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(91, 14, 'b1c4c5d3406ffa80e5c9ae74524c4e53e8f2a048e9bc83dfc62d52862b04fd5e', '2025-10-02 15:48:48', '2025-10-02 23:48:48', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(92, 23, 'd1560e48cdfa47d83f16206922c74cde5b7f4e556373e1573c08a6a36d678a81', '2025-10-02 15:57:17', '2025-10-02 23:57:17', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(93, 23, 'bb6370674c34bb63b327c1d5f6b6828459a83299c980f22ae91d98c2738cd94d', '2025-10-02 16:03:10', '2025-10-03 00:03:10', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(94, 14, '9eb4ed85583c5512e09cf30258593fdebb6b750ce606a4435b1fca1dc31e4961', '2025-10-02 16:42:42', '2025-10-03 00:42:42', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(95, 14, '19e5a7bf7938be096acf916cd04c83ea6a8052908dbae22e8e41d9332480d25e', '2025-10-02 16:55:15', '2025-10-03 00:55:18', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(96, 14, '6549565a4e8bf366a3f6abe66e4512a7b65972b548df6ebfcbcabc60206cf8a4', '2025-10-02 17:26:49', '2025-10-03 01:26:49', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(97, 24, '9344b14fa9ffbbb9108a00db01577b9ab026b158e4e2d2d367f7a01096f7b27c', '2025-10-02 17:30:44', '2025-10-03 01:30:44', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(98, 14, '9762769fe494ccaecf918c93082b1c4626a7706e34c3131206dd65a2f7130c40', '2025-10-03 03:47:26', '2025-10-03 15:51:24', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(99, 14, '216f05cc23f4bffd69cac56cad269600dcdc2ab0057a9e392e2478646f56bb06', '2025-10-03 08:55:17', '2025-10-03 16:55:17', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(100, 24, 'e674763c0d03162dad35c0d77f6bf8844bedea29fa173dc2e2b25c0d2dd952f0', '2025-10-03 08:55:31', '2025-10-03 16:55:31', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(101, 14, '7c672702d1346f080614d448c0ae89c67ef230e7ab57faad9a75306997e4ea3c', '2025-10-03 09:00:05', '2025-10-03 17:00:05', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(102, 24, 'a59c192dafd41c660abcd6a71aebfefe00b313a6dd791f7fe172db2edccafe12', '2025-10-03 09:00:23', '2025-10-03 17:00:23', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(103, 24, '0e52acad7c6549d5218f80505ff4bbefbcf11cfb57b384b612a4a44c6923185e', '2025-10-03 09:04:04', '2025-10-03 17:04:59', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(104, 14, '57cfe70e4bf9a3f76711f9304d4884670cdf9271178e8d5599d390b736f91777', '2025-10-03 09:06:25', '2025-10-03 17:06:28', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(105, 25, '0178555bf8c3eaa69150acd77b50aeb3853942d70420d337d79a5087801f5a85', '2025-10-03 09:07:25', '2025-10-03 17:07:41', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(106, 14, '91e045d938173f1f9f01d60b6e3b606662547aa831f26891a4ef6ca39ca1224b', '2025-10-03 09:07:49', '2025-10-03 17:07:50', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(107, 25, '153d1839a64a4c3ceb52ee850577a12b0b774f81c8c760d1cf030e2fd3ce6901', '2025-10-03 09:10:09', '2025-10-03 17:10:09', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(108, 14, '9de00750aa75077ff82360104537dd3ebdc9be5f8aacbb3ff4bfae0e66cbe7d0', '2025-10-03 09:11:14', '2025-10-03 17:34:32', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(109, 14, '23c7d95806a47908370ab3b655fe02b03af9b6f08a09aaafb440634d0418ce1e', '2025-10-03 09:34:43', '2025-10-03 18:26:55', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(110, 25, '2b47db86f97973e11dd464e3f7e2227e8c605fd7e108e3453f00f4f339455ecb', '2025-10-03 10:28:45', '2025-10-03 18:28:45', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(111, 14, 'f03ba163acb524549747033ecd325773846096f643543ff64dd1efd58019581b', '2025-10-03 10:29:02', '2025-10-03 18:29:02', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(112, 25, '251ddc0cf0b9b7595415ae5587529fdb62e1f10dc9841eabb85c2d1419a75bdd', '2025-10-03 10:29:21', '2025-10-03 18:30:18', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(113, 14, 'f9bc46913143337dcd6a45183e5725a29296c21d4f710797e23ca586f9ac3604', '2025-10-03 10:30:49', '2025-10-03 18:39:01', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(114, 25, 'b412a6cdd821a79e926346312e3d3d60d01559c4963b0d759b6656b3061398fd', '2025-10-03 10:51:07', '2025-10-03 19:01:21', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(115, 14, '0c0aa4a028ac921c58edaac7cc6e9d1210aeac752a40cb476198aa064ffc3c1d', '2025-10-03 11:01:42', '2025-10-03 19:09:20', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(116, 25, '49b72a8e89ee9ade65ec26409e7cd9d0383aa43c68b238981626f831bf6de638', '2025-10-03 11:15:04', '2025-10-03 19:16:25', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(117, 14, 'fe7b63a1b53c29c87412dea210fc8d4c0fd77fefd7d0d2b1dca8c9ea08ce4713', '2025-10-03 11:16:59', '2025-10-03 19:21:58', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(118, 25, '4d02a7207aabdf6780a8096a489fbf472019674c3bc4ea1bc569b994a0c16c1a', '2025-10-03 11:22:11', '2025-10-03 19:22:36', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(119, 14, 'd352f743e2ac7de2050f3eea9b1f2a62c6ea58174bc6f6a5ae0b7870f731816f', '2025-10-03 11:24:32', '2025-10-03 19:24:32', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(120, 14, '502a56117ed29849a67c03742dcc3d4fef60c98ce8c5994425ad7d5adc346121', '2025-10-04 13:52:45', '2025-10-05 09:03:26', 0, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36'),
(121, 14, '3fed2901ed584ed3be92b0304cd6a71821eac319cea6619d19ca3a6bb2f74b79', '2025-10-05 09:03:26', '2025-10-06 03:22:01', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(122, 25, '747ceb991ae573c88e079a4ab6300c036db0970dbbfd0b9ea24fd03a0e713268', '2025-10-05 10:46:36', '2025-10-05 10:46:38', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(123, 14, '798c4fe3223cacfd904e599aa2a5be17f39788424bbb60285d78925fc6320713', '2025-10-05 10:46:54', '2025-10-06 03:22:01', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(124, 14, '31e7d7351cb4f4440d60aa7eda3c5d5295808da977db48fe2a243eb5df9967cc', '2025-10-06 03:22:01', '2025-10-06 20:24:55', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(125, 14, '6b20a27a3c1115354ec5d1b61b60d882d8b40c667ca487e019dc3f8f009b3714', '2025-10-06 11:20:50', '2025-10-07 07:23:23', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(126, 14, '9fada335b201079fb80ae4076b8b32a519c12ca40c1d118714e2185f65489c01', '2025-10-06 20:24:55', '2025-10-07 07:23:23', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(127, 14, 'bcab34831b3be95cf15783b1f3229d47b5fe1743e0c1ef1e60989091d810f64a', '2025-10-07 07:23:23', '2025-10-07 15:23:23', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(128, 14, '23e9b8bbfef74ade7a4c18fd4fb3327ea5709df2d671a9957cd4ebd82f7561d0', '2025-10-07 07:24:02', '2025-10-07 15:24:02', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(129, 14, 'd43de3f0ce319fd60ddc49e74b4c96bc72f048c955b7608485503aad5571a8e3', '2025-10-07 07:26:47', '2025-10-07 15:27:50', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(130, 14, 'a405a596af0f3812658d054d7163b0e2d8a52f072576812ac5367661d43690c9', '2025-10-07 07:28:25', '2025-10-07 15:28:31', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(131, 14, '0346d2d66f402ed2cb380256f1eba7b9230301bfc596af0fb6986dbc4eac93a0', '2025-10-07 07:35:15', '2025-10-07 15:35:42', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'),
(132, 14, '1aa0f30001c3ef4dd7a906cb42cbfe93ab4d71d556e6048b4ba411897d32b1eb', '2025-10-07 10:00:10', '2025-10-07 18:00:47', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0'),
(133, 14, '4ca595107cb26a94fe184fc1dd66e14734b4b6ca6bdd1333f00b84439e8991c2', '2025-10-07 10:01:15', '2025-10-07 20:46:12', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appliances`
--
ALTER TABLE `appliances`
  ADD PRIMARY KEY (`appliance_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`part_id`);

--
-- Indexes for table `parts_used`
--
ALTER TABLE `parts_used`
  ADD PRIMARY KEY (`part_used_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `service_details`
--
ALTER TABLE `service_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `service_prices`
--
ALTER TABLE `service_prices`
  ADD PRIMARY KEY (`service_id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `service_reports`
--
ALTER TABLE `service_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unq_reset_token` (`reset_token`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_report_id` (`report_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `SessionToken` (`session_token`),
  ADD KEY `fk_user_sessions_staff` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appliances`
--
ALTER TABLE `appliances`
  MODIFY `appliance_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `part_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parts_used`
--
ALTER TABLE `parts_used`
  MODIFY `part_used_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `service_details`
--
ALTER TABLE `service_details`
  MODIFY `detail_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `service_prices`
--
ALTER TABLE `service_prices`
  MODIFY `service_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_reports`
--
ALTER TABLE `service_reports`
  MODIFY `report_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `staff_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appliances`
--
ALTER TABLE `appliances`
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `parts_used`
--
ALTER TABLE `parts_used`
  ADD CONSTRAINT `parts_used_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `service_reports` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_details`
--
ALTER TABLE `service_details`
  ADD CONSTRAINT `service_details_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `service_reports` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_report_id` FOREIGN KEY (`report_id`) REFERENCES `service_reports` (`report_id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_staff` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `service_progress_comments`
--

CREATE TABLE `service_progress_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `report_id` int NOT NULL,
  `progress_key` varchar(50) NOT NULL,
  `comment_text` longtext NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_by_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_report_id` (`report_id`),
  KEY `idx_progress_key` (`progress_key`),
  KEY `idx_report_progress` (`report_id`, `progress_key`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_progress_comments_report` FOREIGN KEY (`report_id`) REFERENCES `service_reports` (`report_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_comments`
--

CREATE TABLE `staff_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `comment_text` longtext NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `fk_staff_comments_staff` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_staff_comments_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
