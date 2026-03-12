-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: 18 مايو 2025 الساعة 06:47
-- إصدار الخادم: 8.0.35
-- PHP Version: 8.1.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shmoolpt_atracking`
--

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `show_price` tinyint(1) DEFAULT '1',
  `notes` text,
  `show_notes` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT 'in_warehouse'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `tracking_number`, `customer_name`, `barcode`, `price`, `show_price`, `notes`, `show_notes`, `created_at`, `updated_at`, `status`) VALUES
(27, '97633673', 'Zakaria Mohammed ', 'TRK1747386219OCTF', 6955.00, 1, 'Zakjdjdjdjddydme5j', 1, '2025-05-16 09:03:39', '2025-05-16 18:57:34', 'in_warehouse'),
(28, '2002', 'Saeed', 'TRK1747397988XFIN', 69.00, 0, '', 0, '2025-05-16 12:19:48', '2025-05-16 18:57:34', 'in_delivery'),
(31, '7433', 'Ifd', 'TRK17473981474EBF', 39.00, 0, '', 1, '2025-05-16 12:22:27', '2025-05-16 18:57:34', 'in_warehouse'),
(34, '854', 'Hvc', 'TRK1747399280WVEZ', 699.00, 1, '', 1, '2025-05-16 12:41:20', '2025-05-16 18:57:34', 'in_warehouse'),
(35, '6727', 'Zakaria ', 'TRK1747399765N7W1', 69.00, 1, '', 1, '2025-05-16 12:49:25', '2025-05-16 18:57:34', 'in_warehouse'),
(36, '93939', 'Saeed', 'TRK1747401189E9VN', 39.00, 1, '', 1, '2025-05-16 13:13:09', '2025-05-16 18:57:34', 'in_warehouse'),
(37, '755790', 'Mihammed', 'TRK1747402681ZWMK', 3999.00, 1, '', 1, '2025-05-16 13:38:01', '2025-05-16 18:57:34', 'in_warehouse'),
(38, '838393', 'Zakaria ', 'TRK1747402900VO7S', 69.00, 1, 'لا توجد الان', 1, '2025-05-16 13:41:40', '2025-05-16 18:57:34', 'returned'),
(39, '3066', 'محمد خالد', 'TRK1747406586IS6V', 450.00, 0, '', 0, '2025-05-16 14:43:06', '2025-05-16 18:57:34', 'in_warehouse'),
(46, '6789', 'Zakaria ', 'TRK1747425282TMRE', 66.00, 1, '', 1, '2025-05-16 19:54:42', '2025-05-17 07:17:12', 'in_delivery'),
(47, '2005', 'Zakaria Mohammed ', 'TRK17474680265ZMJ', 69.00, 1, '', 1, '2025-05-17 07:47:06', '2025-05-17 09:55:28', 'returned'),
(48, '124', 'Zakaria Mohammed ', 'TRK1747477551W5VF', 250.00, 1, '', 1, '2025-05-17 10:25:51', '2025-05-17 12:34:52', 'returned'),
(49, '12393', 'Zakaria Mohammed ', 'TRK1747496967HNVW', 96.00, 0, '', 1, '2025-05-17 15:49:27', '2025-05-17 15:49:27', 'in_warehouse'),
(50, '121212', 'نقدي', 'TRK1747503281IEB2', 450.00, 1, '', 0, '2025-05-17 17:34:41', '2025-05-17 19:01:24', 'delivered'),
(51, '0976', 'Hvc', 'TRK174750436220PU', 36.00, 1, '', 1, '2025-05-17 17:52:42', '2025-05-17 17:52:42', 'in_warehouse'),
(52, '12345', 'Hvc', 'TRK17475044599QEN', 68.00, 1, '', 1, '2025-05-17 17:54:19', '2025-05-17 17:54:19', 'in_delivery'),
(53, '12', 'Zakaria Mohammed ', 'TRK1747504697X5YN', 698.00, 1, '', 1, '2025-05-17 17:58:17', '2025-05-17 17:58:17', 'in_warehouse'),
(54, '131', 'Zakaria Mohammed ', 'TRK1747506656GNH9', 39.00, 1, '', 1, '2025-05-17 18:30:56', '2025-05-17 18:30:56', 'in_warehouse'),
(55, '753', 'Zakaria Mohammed ', 'TRK1747507525LC98', 998.00, 1, '', 1, '2025-05-17 18:45:25', '2025-05-17 18:45:25', 'in_warehouse'),
(56, 'TRK20250517GW41YB', '', 'BC1747515389VLHE4A5U', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'in_warehouse'),
(57, 'TRK202505172BAWED', '', 'BC1747515389R18JDNAE', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'Array'),
(58, 'TRK20250517I7OA8P', '', 'BC17475153898E0BSFTW', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'Array'),
(59, 'TRK202505178REPDM', '', 'BC1747515389E4WRQSLP', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'Array'),
(60, 'TRK20250517FL1V4A', '', 'BC1747515389KQ8BYCTJ', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'Array'),
(61, 'TRK20250517VO8EX9', '', 'BC1747515389WB32QXHK', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'Array'),
(62, 'TRK20250517ILEO0K', '', 'BC1747515389ZU8H3P9B', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'Array'),
(63, 'TRK20250517ZO97L8', '', 'BC1747515389OWT8PVRG', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:56:29', 'Array'),
(65, 'TRK20250517HYQXG6', 'K', 'BC1747515389UPAJC5DH', 65.00, 1, '', 1, '2025-05-17 20:56:29', '2025-05-17 20:59:06', 'in_delivery'),
(66, 'TRK20250517MKHT2J', '', 'BC1747515593QUV5R8D2', 900.00, 1, '', 1, '2025-05-17 20:59:53', '2025-05-17 20:59:53', 'in_delivery'),
(67, 'TRK20250517K9IA16', '', 'BC1747515593I0JGCQUY', 900.00, 1, '', 1, '2025-05-17 20:59:53', '2025-05-17 20:59:53', 'Array'),
(69, 'TRK20250517M9R8WO', '', 'BC17475162064QKFIVGB', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(70, 'TRK20250517CVPM4X', '', 'BC1747516206QB4STP10', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(71, 'TRK20250517XGUMA8', '', 'BC17475162067OQ5Z1WM', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(72, 'TRK20250517POAKF0', '', 'BC17475162060XMQH538', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(73, 'TRK20250517Y6Z5HJ', '', 'BC1747516206S1AZEFN7', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(74, 'TRK20250517Q9NRK5', '', 'BC1747516206KCMAU6V3', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(75, 'TRK20250517HIMJ6D', '', 'BC1747516206ESZI1OX0', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(76, 'TRK20250517X6H4IV', '', 'BC1747516206AVFP8J74', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(77, 'TRK2025051751V4DQ', '', 'BC1747516206FD8BTGWS', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(78, 'TRK20250517FWI2Z6', '', 'BC1747516206N36OTKLV', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(79, 'TRK20250517OH3LQ4', '', 'BC1747516206K8O0RW25', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(80, 'TRK202505172UIOTC', '', 'BC1747516206NWO5CPAM', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(81, 'TRK20250517M24VF8', '', 'BC1747516206UK7ETQ65', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(82, 'TRK20250517W65K0S', '', 'BC1747516206MBCNAVHW', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(83, 'TRK20250517VX0W4M', '', 'BC1747516206NGW6TUZ2', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(84, 'TRK20250517VZOPI4', '', 'BC1747516206GESN61RU', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(85, 'TRK20250517HKEGC0', '', 'BC1747516206GU5K2MAR', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(86, 'TRK20250517Z01WUR', '', 'BC1747516206ECMNW2FH', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(87, 'TRK20250517OHMAGV', '', 'BC17475162069NRLZ7DX', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(88, 'TRK20250517C0I5KN', '', 'BC1747516206Z26O837S', 69.00, 1, '', 1, '2025-05-17 21:10:06', '2025-05-17 21:10:06', 'delivered'),
(89, 'TRK20250517GUZ2BQ', '', 'BC17475162520DH2TA3V', 95.00, 1, '', 1, '2025-05-17 21:10:52', '2025-05-17 21:10:52', 'in_warehouse'),
(90, 'TRK20250517HVC0BU', '', 'BC1747516253I3W0CKXA', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(91, 'TRK20250517X3WUB7', '', 'BC1747516253B2K519N0', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(92, 'TRK20250517QRSPDG', '', 'BC1747516253W5P1ZVLJ', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(93, 'TRK20250517948MTE', '', 'BC1747516253URXTHK3J', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(94, 'TRK2025051721GH97', '', 'BC17475162538I19WTS0', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(95, 'TRK20250517J169ZG', '', 'BC1747516253KMOT7CFH', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(96, 'TRK20250517U7SOBJ', '', 'BC1747516253W4UOQ870', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(97, 'TRK20250517JQA7G9', '', 'BC1747516253R067M5BJ', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(98, 'TRK202505178SNW32', '', 'BC1747516253P1YHOWTL', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(99, 'TRK20250517ZACHIG', '', 'BC17475162539HAB5T3M', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(100, 'TRK20250517H1UJED', '', 'BC1747516253XZNYE6JS', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(101, 'TRK20250517WJUN70', '', 'BC174751625318BIFNL9', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(102, 'TRK20250517DJUZB2', '', 'BC1747516253SBD4NWTO', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(103, 'TRK202505173DO0PB', '', 'BC17475162530UYP6SN2', 95.00, 1, '', 1, '2025-05-17 21:10:53', '2025-05-17 21:10:53', 'in_warehouse'),
(104, 'TRK20250517DMTH84', '', 'BC1747516281X2DPBQ1J', 665.00, 1, '', 1, '2025-05-17 21:11:21', '2025-05-17 21:11:21', 'returned'),
(105, 'TRK20250517R0DJIW', '', 'BC1747516281I0KVREYG', 665.00, 1, '', 1, '2025-05-17 21:11:21', '2025-05-17 21:11:21', 'returned'),
(106, 'TRK20250517HSFOPR', '', 'BC1747516281OCHUDMJP', 665.00, 1, '', 1, '2025-05-17 21:11:21', '2025-05-17 21:11:21', 'returned'),
(107, '1900', '', 'BC174751766202MV7QLN', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(108, '1901', '', 'BC1747517662AYZQCT2B', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(109, '1902', '', 'BC1747517662Y2D7WCBL', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(110, '1903', '', 'BC1747517662FSVOPLTE', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(111, '1904', '', 'BC1747517662WJZVE067', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(112, '1905', '', 'BC1747517662MQPUOF2R', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(113, '1906', '', 'BC1747517662JE4DIFAH', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(114, '1907', '', 'BC1747517662JHU75VF1', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(115, '1908', '', 'BC1747517662CQDARSNM', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:34:22', 'com'),
(116, '1909', 'Jj', 'BC1747517662BS2RT78E', 40.00, 0, '', 0, '2025-05-17 21:34:22', '2025-05-17 21:43:10', 'out_for_delivery'),
(117, '0000', 'Zakaria ', 'BC1747527965RJY6INHF', 680.00, 1, '', 1, '2025-05-18 00:26:05', '2025-05-18 00:36:35', 'com'),
(118, '00000', '0', 'BC1747528143GYXPE7ZV', 6808.00, 1, '', 1, '2025-05-18 00:29:03', '2025-05-18 00:29:03', 'in_warehouse'),
(119, '555', 'Jsj', 'BC1747528504WG4A7OIR', 69.00, 1, '', 1, '2025-05-18 00:35:04', '2025-05-18 00:39:02', 'delivered'),
(120, 'TRK20250518HW35UP', 'Zakaria Mohammed ', 'BC17475294553G0FCJ68', 9.00, 1, '', 1, '2025-05-18 00:50:55', '2025-05-18 00:55:37', 'delivered');

-- --------------------------------------------------------

--
-- بنية الجدول `order_history`
--

CREATE TABLE `order_history` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_history`
--

INSERT INTO `order_history` (`id`, `order_id`, `status`, `icon`, `created_at`) VALUES
(4, 27, 'في المخزن', 'bx-store', '2025-05-16 09:03:39'),
(5, 28, 'في المخزن', 'bx-store', '2025-05-16 12:19:48'),
(6, 31, 'في المخزن', 'bx-store', '2025-05-16 12:22:27'),
(7, 34, 'في المخزن', 'bx-store', '2025-05-16 12:41:20'),
(8, 35, 'في المخزن', 'bx-store', '2025-05-16 12:49:25'),
(9, 36, 'في المخزن', 'bx-store', '2025-05-16 13:13:09'),
(10, 37, 'في المخزن', 'bx-store', '2025-05-16 13:38:01'),
(11, 38, 'في المخزن', 'bx-store', '2025-05-16 13:41:40'),
(12, 39, 'في المخزن', 'bx-store', '2025-05-16 14:43:06'),
(17, 46, 'في المخزن', 'bx-store', '2025-05-16 19:54:42'),
(18, 47, 'Array', 'bx-store', '2025-05-17 07:47:06'),
(19, 48, 'Array', 'bx-store', '2025-05-17 10:25:51'),
(20, 49, 'Array', 'bx-store', '2025-05-17 15:49:27'),
(21, 50, 'Array', 'bx-store', '2025-05-17 17:34:42'),
(22, 51, 'Array', 'bx-store', '2025-05-17 17:52:42'),
(23, 52, 'Array', 'bx-store', '2025-05-17 17:54:19'),
(24, 53, 'Array', 'bx-store', '2025-05-17 17:58:17'),
(25, 54, 'Array', 'bx-store', '2025-05-17 18:30:56'),
(26, 55, 'Array', 'bx-store', '2025-05-17 18:45:25'),
(27, 69, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(28, 70, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(29, 71, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(30, 72, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(31, 73, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(32, 74, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(33, 75, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(34, 76, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(35, 77, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(36, 78, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(37, 79, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(38, 80, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(39, 81, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(40, 82, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(41, 83, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(42, 84, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(43, 85, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(44, 86, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(45, 87, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(46, 88, 'delivered', 'bx-check-circle', '2025-05-17 21:10:06'),
(47, 89, 'in_warehouse', 'bx-store', '2025-05-17 21:10:52'),
(48, 90, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(49, 91, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(50, 92, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(51, 93, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(52, 94, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(53, 95, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(54, 96, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(55, 97, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(56, 98, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(57, 99, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(58, 100, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(59, 101, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(60, 102, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(61, 103, 'in_warehouse', 'bx-store', '2025-05-17 21:10:53'),
(62, 104, 'returned', 'bx-x-circle', '2025-05-17 21:11:21'),
(63, 105, 'returned', 'bx-x-circle', '2025-05-17 21:11:21'),
(64, 106, 'returned', 'bx-x-circle', '2025-05-17 21:11:21'),
(65, 107, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(66, 108, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(67, 109, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(68, 110, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(69, 111, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(70, 112, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(71, 113, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(72, 114, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(73, 115, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(74, 116, 'com', 'bx-x-circle', '2025-05-17 21:34:22'),
(75, 117, 'in_warehouse', 'bx-store', '2025-05-18 00:26:05'),
(76, 118, 'in_warehouse', 'bx-store', '2025-05-18 00:29:03'),
(77, 119, 'in_warehouse', 'bx-store', '2025-05-18 00:35:04'),
(78, 117, 'com', 'bx-x-circle', '2025-05-18 00:36:35'),
(79, 119, 'in_delivery', 'bx-car', '2025-05-18 00:37:22'),
(80, 119, 'delivered', 'bx-check-circle', '2025-05-18 00:39:02'),
(81, 120, 'in_warehouse', 'bx-store', '2025-05-18 00:50:55'),
(82, 120, 'com', 'bx-x-circle', '2025-05-18 00:54:09'),
(83, 120, 'com', 'bx-x-circle', '2025-05-18 00:54:23'),
(84, 120, 'delivered', 'bx-check-circle', '2025-05-18 00:55:22'),
(85, 120, 'delivered', 'bx-check-circle', '2025-05-18 00:55:37');

-- --------------------------------------------------------

--
-- بنية الجدول `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int NOT NULL,
  `status_key` varchar(50) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `status_icon` varchar(50) DEFAULT NULL,
  `status_color` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `display_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_statuses`
--

INSERT INTO `order_statuses` (`id`, `status_key`, `status_name`, `status_icon`, `status_color`, `is_active`, `display_order`) VALUES
(1, 'in_warehouse', 'في المخزن z', 'bx-store', '#00ffff', 1, 1),
(2, 'in_delivery', 'في الطريق', 'bx-car', '#ffff00', 1, 2),
(3, 'delivered', 'وصلت الواجهة الرئيسية', 'bx-check-circle', '#ff00ff', 1, 3),
(4, 'returned', 'في البيت', 'bx-x-circle', '#0000ff', 1, 4),
(5, 'com', 'الان', 'bx-x-circle', '#000000', 1, 2),
(6, 'out_for_delivery', 'في البنزين', 'bx-x-circle', '#000000', 1, 5);

-- --------------------------------------------------------

--
-- بنية الجدول `order_status_dates`
--

CREATE TABLE `order_status_dates` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `actual_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_status_dates`
--

INSERT INTO `order_status_dates` (`id`, `order_id`, `status`, `scheduled_date`, `actual_date`) VALUES
(25, 27, 'في المخزن', '2025-05-16 12:03:00', NULL),
(26, 27, 'في التوصيل', '2025-05-19 12:03:00', NULL),
(27, 27, 'تم التسليم', '2025-05-21 12:07:00', NULL),
(28, 28, 'في المخزن', '2025-05-16 15:19:00', NULL),
(29, 28, 'في التوصيل', '2025-05-16 15:23:00', NULL),
(30, 28, 'تم التسليم', '2025-05-23 15:19:00', NULL),
(31, 31, 'في المخزن', '2025-05-16 15:22:00', NULL),
(32, 31, 'في التوصيل', '2025-05-20 15:24:00', NULL),
(33, 31, 'تم التسليم', '2025-05-25 15:22:00', NULL),
(34, 34, 'في المخزن', '2025-05-16 15:41:00', NULL),
(35, 34, 'في التوصيل', '2025-05-18 15:41:00', NULL),
(36, 34, 'تم التسليم', '2025-05-19 15:43:00', NULL),
(37, 35, 'في المخزن', '2025-05-16 15:49:00', NULL),
(38, 35, 'في التوصيل', '2025-05-19 15:54:00', NULL),
(39, 35, 'تم التسليم', '2025-05-21 15:49:00', NULL),
(40, 36, 'في المخزن', '2025-05-16 16:13:00', NULL),
(47, 38, 'في المخزن', '2025-05-16 17:39:00', NULL),
(48, 38, 'في التوصيل', '2025-05-17 18:39:00', NULL),
(49, 38, 'تم التسليم', '2025-05-18 21:39:00', NULL),
(86, 47, 'in_warehouse', '2025-05-17 10:46:00', NULL),
(87, 47, 'in_delivery', '2025-05-20 10:46:00', NULL),
(88, 47, 'delivered', '2025-05-23 10:46:00', NULL),
(89, 47, 'returned', '2025-05-29 10:47:00', NULL),
(121, 48, 'in_warehouse', '2025-05-17 13:25:00', NULL),
(122, 48, 'in_delivery', '2025-05-17 13:27:00', NULL),
(123, 48, 'delivered', '2025-05-17 15:10:00', NULL),
(124, 48, 'returned', '2025-05-17 15:11:00', NULL),
(125, 49, 'in_warehouse', '2025-05-17 18:49:00', NULL),
(127, 51, 'in_warehouse', '2025-05-17 20:48:00', NULL),
(128, 51, 'in_delivery', '2025-05-17 20:52:00', NULL),
(129, 51, 'delivered', '2025-05-17 20:54:00', NULL),
(130, 51, 'returned', '2025-05-17 20:55:00', NULL),
(131, 52, 'in_warehouse', '2025-05-17 20:55:00', NULL),
(132, 52, 'in_delivery', '2025-05-17 20:56:00', NULL),
(133, 52, 'delivered', '2025-05-17 20:57:00', NULL),
(134, 53, 'in_warehouse', '2025-05-17 20:59:00', NULL),
(135, 53, 'in_delivery', '2025-05-17 21:00:00', NULL),
(136, 53, 'delivered', '2025-05-17 21:02:00', NULL),
(137, 54, 'in_warehouse', '2025-05-17 21:32:00', NULL),
(138, 54, 'in_delivery', '2025-05-17 21:33:00', NULL),
(139, 54, 'delivered', '2025-05-17 21:35:00', NULL),
(140, 55, 'in_warehouse', '2025-05-17 21:47:00', NULL),
(141, 55, 'in_delivery', '2025-05-17 21:49:00', NULL),
(142, 55, 'delivered', '2025-05-17 21:53:00', NULL),
(145, 50, 'in_warehouse', '2025-05-17 21:34:00', NULL),
(146, 50, 'in_delivery', '2025-05-18 15:01:00', NULL),
(147, 50, 'delivered', '2025-05-20 00:03:00', NULL),
(148, 56, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(149, 57, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(150, 58, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(151, 59, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(152, 60, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(153, 61, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(154, 62, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(155, 63, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(158, 65, 'in_warehouse', '2025-05-17 23:56:00', NULL),
(159, 66, 'in_warehouse', '2025-05-17 23:59:00', NULL),
(160, 66, 'in_delivery', '2025-05-17 00:01:00', NULL),
(161, 66, 'delivered', '2025-05-17 00:04:00', NULL),
(162, 67, 'in_warehouse', '2025-05-17 23:59:00', NULL),
(163, 67, 'in_delivery', '2025-05-17 00:01:00', NULL),
(164, 67, 'delivered', '2025-05-17 00:04:00', NULL),
(168, 107, 'com', '2025-05-18 00:34:00', NULL),
(169, 108, 'com', '2025-05-18 00:34:00', NULL),
(170, 109, 'com', '2025-05-18 00:34:00', NULL),
(171, 110, 'com', '2025-05-18 00:34:00', NULL),
(172, 111, 'com', '2025-05-18 00:34:00', NULL),
(173, 112, 'com', '2025-05-18 00:34:00', NULL),
(174, 113, 'com', '2025-05-18 00:34:00', NULL),
(175, 114, 'com', '2025-05-18 00:34:00', NULL),
(176, 115, 'com', '2025-05-18 00:34:00', NULL),
(183, 116, 'in_warehouse', '2025-05-18 00:41:00', NULL),
(184, 116, 'in_delivery', '2025-05-18 00:42:00', NULL),
(185, 116, 'com', '2025-05-18 00:42:00', NULL),
(186, 116, 'delivered', '2025-05-18 00:49:00', NULL),
(187, 116, 'returned', '2025-05-18 00:50:00', NULL),
(188, 116, 'out_for_delivery', '2025-05-18 00:51:00', NULL),
(189, 117, 'in_warehouse', '2025-05-18 03:27:00', NULL),
(190, 117, 'in_delivery', '2025-05-18 03:28:00', NULL),
(191, 117, 'com', '2025-05-18 03:30:00', NULL),
(192, 118, 'in_warehouse', '2025-05-18 04:30:00', NULL),
(193, 118, 'in_delivery', '2025-05-18 04:31:00', NULL),
(194, 118, 'com', '2025-05-18 04:34:00', NULL),
(195, 118, 'delivered', '2025-05-18 04:42:00', NULL),
(203, 119, 'in_warehouse', '2025-05-18 03:36:00', NULL),
(204, 119, 'in_delivery', '2025-05-18 03:37:00', NULL),
(205, 119, 'com', '2025-05-18 03:38:00', NULL),
(206, 119, 'delivered', '2025-05-18 03:39:00', NULL),
(242, 120, 'in_warehouse', '2025-05-18 03:52:00', NULL),
(243, 120, 'in_delivery', '2025-05-18 03:53:00', NULL),
(244, 120, 'com', '2025-05-18 03:54:00', NULL),
(245, 120, 'delivered', '2025-05-18 03:55:00', NULL),
(246, 120, 'returned', '2025-05-18 03:56:00', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `about_text` text,
  `contact_info` text,
  `payment_methods` text,
  `home_hero_title` varchar(255) DEFAULT NULL,
  `home_hero_subtitle` text,
  `home_about_text` text,
  `home_features_title` varchar(255) DEFAULT NULL,
  `track_header_title` varchar(255) DEFAULT NULL,
  `track_header_subtitle` text,
  `track_instructions` text,
  `company_history` text,
  `mission_vision` text,
  `contact_address` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `feature1_title` varchar(255) DEFAULT NULL,
  `feature1_description` text,
  `feature2_title` varchar(255) DEFAULT NULL,
  `feature2_description` text,
  `feature3_title` varchar(255) DEFAULT NULL,
  `feature3_description` text,
  `about_image` varchar(255) DEFAULT NULL,
  `why_choose_title` varchar(255) DEFAULT NULL,
  `why_choose_description` text,
  `track_section_title` varchar(255) DEFAULT NULL,
  `track_section_description` text,
  `contact_hero_subtitle` text,
  `contact_phone_title` varchar(255) DEFAULT NULL,
  `contact_email_title` varchar(255) DEFAULT NULL,
  `contact_address_title` varchar(255) DEFAULT NULL,
  `contact_hours` text,
  `about_hero_text` text,
  `about_story_title` varchar(255) DEFAULT NULL,
  `about_story_text` text,
  `about_service_reliable_title` varchar(255) DEFAULT NULL,
  `about_service_reliable_text` text,
  `about_service_professional_title` varchar(255) DEFAULT NULL,
  `about_service_professional_text` text,
  `about_story_image` varchar(255) DEFAULT NULL,
  `about_stats_shipments` varchar(255) DEFAULT NULL,
  `about_stats_clients` varchar(255) DEFAULT NULL,
  `about_stats_cities` varchar(255) DEFAULT NULL,
  `about_stats_vehicles` varchar(255) DEFAULT NULL,
  `about_service1_title` varchar(255) DEFAULT NULL,
  `about_service1_text` text,
  `about_service2_title` varchar(255) DEFAULT NULL,
  `about_service2_text` text,
  `about_service3_title` varchar(255) DEFAULT NULL,
  `about_service3_text` text,
  `about_timeline_title` varchar(255) DEFAULT NULL,
  `about_timeline1_year` varchar(255) DEFAULT NULL,
  `about_timeline1_text` text,
  `about_timeline2_year` varchar(255) DEFAULT NULL,
  `about_timeline2_text` text,
  `about_timeline3_year` varchar(255) DEFAULT NULL,
  `about_timeline3_text` text,
  `site_logo` varchar(255) DEFAULT NULL,
  `footer_about_title` varchar(255) DEFAULT NULL,
  `footer_about_description` text,
  `footer_copyright_text` varchar(255) DEFAULT NULL,
  `payment_methods_images` text,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_facebook_icon` varchar(255) DEFAULT NULL,
  `social_twitter_icon` varchar(255) DEFAULT NULL,
  `social_instagram_icon` varchar(255) DEFAULT NULL,
  `track_status_icons` json DEFAULT NULL,
  `track_scheduled_date_text` varchar(255) DEFAULT 'التاريخ المجدول',
  `track_actual_date_text` varchar(255) DEFAULT 'التاريخ الفعلي',
  `track_status_scheduled_text` varchar(255) DEFAULT 'التاريخ المجدول:'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`id`, `logo_url`, `about_text`, `contact_info`, `payment_methods`, `home_hero_title`, `home_hero_subtitle`, `home_about_text`, `home_features_title`, `track_header_title`, `track_header_subtitle`, `track_instructions`, `company_history`, `mission_vision`, `contact_address`, `contact_email`, `contact_phone`, `hero_image`, `feature1_title`, `feature1_description`, `feature2_title`, `feature2_description`, `feature3_title`, `feature3_description`, `about_image`, `why_choose_title`, `why_choose_description`, `track_section_title`, `track_section_description`, `contact_hero_subtitle`, `contact_phone_title`, `contact_email_title`, `contact_address_title`, `contact_hours`, `about_hero_text`, `about_story_title`, `about_story_text`, `about_service_reliable_title`, `about_service_reliable_text`, `about_service_professional_title`, `about_service_professional_text`, `about_story_image`, `about_stats_shipments`, `about_stats_clients`, `about_stats_cities`, `about_stats_vehicles`, `about_service1_title`, `about_service1_text`, `about_service2_title`, `about_service2_text`, `about_service3_title`, `about_service3_text`, `about_timeline_title`, `about_timeline1_year`, `about_timeline1_text`, `about_timeline2_year`, `about_timeline2_text`, `about_timeline3_year`, `about_timeline3_text`, `site_logo`, `footer_about_title`, `footer_about_description`, `footer_copyright_text`, `payment_methods_images`, `social_facebook`, `social_twitter`, `social_instagram`, `social_facebook_icon`, `social_twitter_icon`, `social_instagram_icon`, `track_status_icons`, `track_scheduled_date_text`, `track_actual_date_text`, `track_status_scheduled_text`) VALUES
(1, '', 'نحن شركة رائدة في مجال خدمات الشحن والتوصيل ', 'اتصل بنا على مدار الساعة Zakaria ', 'نقبل جميع وسائل الدفع', 'تتبع شحنتك بسهولة وأمان Zakaria ', 'نظام متكامل لتتبع شحنتك أول بأول  ', 'نقدم خدمات شحن آمنة وموثوقة', 'مميزات خدماتنا', 'تتبع شحنتك  ', 'ادخل رقم التتبع للبحث عن شحنتك Zakaria ', 'يمكنك تتبع شحنتك عن طريق رقم التتبع أو رقم الباركود', 'تأسست الشركة عام 2020', 'رؤيتنا هي أن نكون الخيار الأول في خدمات الشحن', 'الرياض - المملكة العربية السعودية zero ', 'z422501809@gmail.com', '+970599747804', '68290d503b9ed.png', 'تتبع مباشر', 'تابع شحنتك خطوة بخطوة \r\nمع تحديثات فورية للموقع \r\nوالحالة', 'تحديثات فورية', 'أحصل على أشعارات فورية \r\nعن حالة شحنتك وموقعها \r\nالحالي', 'دعم متواصل', 'فريق دعم متخصص \r\nمتواجد على مدار الساعة لمساعدتك', '', 'تتبع شحنتك بكل سهولة', 'تاريخ بدء التوصيل المتوقع\r\n', 'تتبع الان طلبك', 'تتبع الان طلبك', 'نحن هنا لمساعدتك والإجابة على جميع استفساراتك Zakaria ', 'اتصل بنا Zakaria', 'راسلنا', 'موقعنا', 'الأحد - الخميس: 9:00 صباحاً - 6:00 مساءً\r\nالجمعة - السبت: مغلق', 'نحن نسعى لإرضاء عملائنا', 'قصتنا', ' • نحن نسعى لاسعاد العملاء \r\n • أولويتنا أرضاء العملاء ', 'خدمة موثوقة', 'ثقتنا بثقة العملاء ', 'فريق محترف', 'فريق يتبع الطلبات بشكل متتبع', '', '20', '100', '3', '10', 'تتبع الشحنة', 'تتبع شحنتك بكل سهولة', 'تتبع شحنتك من خلال الباركود.', 'سهولة التعامل مع العملاء', 'التواصل المباشر ', 'تواصل مباشر مع العملاء', 'مسيرتا', 'نسعى ارضاء العلماء', 'تتبع الان طلبك', 'تتبع الان طلبك', 'تتبع الان طلبك', 'تتبع الان طلبك', 'تتبع الان طلبك', 'logo_1747520767.png', 'تراكر', 'نظام متكامل لتتبع الشحنات والطلبات', 'جميع الحقوق محفوظة © 2025 ,hh', '[\"6826f3411cceb_1000039206.png\"]', 'https://shmoolptc.com/Atracking/admin/settings.php', '', '', '6828fdff2aa9f_icon4-1.png', '6828fdff2ab9b_icon7-2.png', '6828fdff2ac31_icon3-1.png', '[]', 'ZAKARIA BARBAKH', 'في الطلب', 'Zakaria ');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`) VALUES
(4, 'Afaf', '$2y$10$mtZl5UsaMoiL9.Bx/UHl4uCjqdIN48gKjggekJnm9hKx.Q0h4dAd6', 'admin@gmail.com', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`);

--
-- Indexes for table `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_key` (`status_key`);

--
-- Indexes for table `order_status_dates`
--
ALTER TABLE `order_status_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_status_dates`
--
ALTER TABLE `order_status_dates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- قيود الجداول `order_status_dates`
--
ALTER TABLE `order_status_dates`
  ADD CONSTRAINT `order_status_dates_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
