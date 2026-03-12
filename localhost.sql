-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- مضيف: localhost:3306
-- وقت الجيل: 12 مارس 2026 الساعة 05:46
-- إصدار الخادم: 11.4.9-MariaDB-cll-lve
-- نسخة PHP: 8.4.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- قاعدة بيانات: `mawashi_atracking`
--
CREATE DATABASE IF NOT EXISTS `mawashi_atracking` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `mawashi_atracking`;

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `show_price` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `show_notes` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) DEFAULT 'in_warehouse'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `tracking_number`, `customer_name`, `barcode`, `price`, `show_price`, `notes`, `show_notes`, `created_at`, `updated_at`, `status`) VALUES
(122, '2026', 'خالد الزهراني', 'BC1761563797Z9AIC0HX', 350.00, 1, 'علي بركة الله موسم حج 1447 هجري', 1, '2025-10-27 11:16:37', '2025-10-27 11:16:37', 'out_for_delive'),
(123, '00101', 'احمد سيد عثمان محمد عثمان', 'BC1765753919RXCZOG8L', 450.00, 1, '', 1, '2025-12-14 23:11:59', '2025-12-14 23:13:21', 'out_for_delive'),
(124, '00102', 'زكي حسن زكي عامر', 'BC176575397949ZON68F', 450.00, 1, '', 1, '2025-12-14 23:12:59', '2025-12-14 23:12:59', 'out_for_delive'),
(125, '00103', 'عصمت عبدالحي عبدالحميد ابراهيم', 'BC1765754284GZS3E6OX', 450.00, 1, '', 1, '2025-12-14 23:18:04', '2025-12-14 23:18:04', 'out_for_delive'),
(126, '00104', 'صلاح حنفي عبدالعال', 'BC1765754286P8369K5A', 450.00, 1, '', 1, '2025-12-14 23:18:06', '2025-12-14 23:18:06', 'out_for_delive'),
(127, '00105', 'السيد عبدالقادر عبدالسلام', 'BC1765754290XHOPEK0U', 450.00, 1, '', 1, '2025-12-14 23:18:10', '2025-12-14 23:18:10', 'out_for_delive'),
(128, '00106', 'ناديه مسعد السيد السبع', 'BC1765754294G1NFL39Z', 450.00, 1, '', 1, '2025-12-14 23:18:14', '2025-12-14 23:18:14', 'out_for_delive'),
(129, '00107', 'السيد محمد ابوضيف عبدالرحمن', 'BC176575429826AIMYB3', 450.00, 1, '', 1, '2025-12-14 23:18:18', '2025-12-14 23:18:18', 'out_for_delive'),
(130, '00108', 'محمد عزت عبدالجليل احمد', 'BC1765754869FIL150XT', 450.00, 1, '', 1, '2025-12-14 23:27:49', '2025-12-14 23:27:49', 'out_for_delive'),
(131, '00109', 'رانا عبدالله عبدالله السيد بلال ', 'BC17657548717IGU2DY0', 450.00, 1, '', 1, '2025-12-14 23:27:51', '2025-12-14 23:27:51', 'out_for_delive'),
(132, '00110', 'انهار عبدالفتاح عبدالحميد الشافعي', 'BC17657548738PK2M9SB', 450.00, 1, '', 1, '2025-12-14 23:27:53', '2025-12-14 23:27:53', 'out_for_delive'),
(133, '00111', 'حسن عبدالحميد الشافعي', 'BC1765754876TZJK5C2V', 450.00, 1, '', 1, '2025-12-14 23:27:56', '2025-12-14 23:27:56', 'out_for_delive'),
(134, '00112', 'ياسر مجدي محمد لطفي احمد', 'BC1765754879JU480MZF', 450.00, 1, '', 1, '2025-12-14 23:27:59', '2025-12-14 23:27:59', 'out_for_delive'),
(135, '00113', 'هبة سيد احمد السيد', 'BC1765754882DKI9QMNU', 450.00, 1, '', 1, '2025-12-14 23:28:02', '2025-12-14 23:28:02', 'out_for_delive'),
(136, '00114', 'محمد عبدالمطلب محمد الدمرداش', 'BC1765754884J3UY5W1I', 450.00, 1, '', 1, '2025-12-14 23:28:04', '2025-12-14 23:28:04', 'out_for_delive'),
(137, '00115', 'بثينة سالم سالم البيومي', 'BC1765754887XT24FIV6', 450.00, 1, '', 1, '2025-12-14 23:28:07', '2025-12-14 23:28:07', 'out_for_delive'),
(138, '00116', 'محمد سامي احمد بدران رعب', 'BC17657548907B3DJMNX', 450.00, 1, '', 1, '2025-12-14 23:28:10', '2025-12-14 23:28:10', 'out_for_delive'),
(139, '00117', 'جمعة محمد حسين سليم', 'BC1765754940ZASK5481', 450.00, 1, '', 1, '2025-12-14 23:29:00', '2025-12-14 23:29:00', 'out_for_delive'),
(140, '00118', 'امال عامر عبدة اسماعيل ', 'BC1765754963IN9WQ84L', 450.00, 1, '', 1, '2025-12-14 23:29:23', '2025-12-14 23:29:23', 'out_for_delive'),
(141, '00119', 'تيسير عبدالمنعم اسماعيل مصطفي', 'BC1765755004AK4MB79J', 450.00, 1, '', 1, '2025-12-14 23:30:04', '2025-12-14 23:30:04', 'out_for_delive'),
(142, '00120', 'وليد محمود عبدالهادي عبدالعليم', 'BC1765755032LT8G3J6E', 450.00, 1, '', 1, '2025-12-14 23:30:32', '2025-12-14 23:30:32', 'out_for_delive'),
(143, '00121', 'داليا ابراهيم احمد مراد', 'BC1765755054A7U2EKCV', 450.00, 1, '', 1, '2025-12-14 23:30:54', '2025-12-14 23:30:54', 'out_for_delive');

-- --------------------------------------------------------

--
-- بنية الجدول `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_history`
--

INSERT INTO `order_history` (`id`, `order_id`, `status`, `icon`, `created_at`) VALUES
(87, 122, 'out_for_delive', 'bx-certification', '2025-10-27 11:16:37'),
(88, 123, 'out_for_deliv', 'bx-badge-check', '2025-12-14 23:12:00'),
(89, 124, 'out_for_delive', 'bx-certification', '2025-12-14 23:12:59'),
(90, 125, 'out_for_delive', 'bx-certification', '2025-12-14 23:18:04'),
(91, 126, 'out_for_delive', 'bx-certification', '2025-12-14 23:18:06'),
(92, 127, 'out_for_delive', 'bx-certification', '2025-12-14 23:18:10'),
(93, 128, 'out_for_delive', 'bx-certification', '2025-12-14 23:18:14'),
(94, 129, 'out_for_delive', 'bx-certification', '2025-12-14 23:18:18'),
(95, 130, 'out_for_delive', 'bx-certification', '2025-12-14 23:27:49'),
(96, 131, 'out_for_delive', 'bx-certification', '2025-12-14 23:27:51'),
(97, 132, 'out_for_delive', 'bx-certification', '2025-12-14 23:27:53'),
(98, 133, 'out_for_delive', 'bx-certification', '2025-12-14 23:27:56'),
(99, 134, 'out_for_delive', 'bx-certification', '2025-12-14 23:27:59'),
(100, 135, 'out_for_delive', 'bx-certification', '2025-12-14 23:28:02'),
(101, 136, 'out_for_delive', 'bx-certification', '2025-12-14 23:28:04'),
(102, 137, 'out_for_delive', 'bx-certification', '2025-12-14 23:28:07'),
(103, 138, 'out_for_delive', 'bx-certification', '2025-12-14 23:28:10'),
(104, 139, 'out_for_delive', 'bx-certification', '2025-12-14 23:29:00'),
(105, 140, 'out_for_delive', 'bx-certification', '2025-12-14 23:29:23'),
(106, 141, 'out_for_delive', 'bx-certification', '2025-12-14 23:30:04'),
(107, 142, 'out_for_delive', 'bx-certification', '2025-12-14 23:30:32'),
(108, 143, 'out_for_delive', 'bx-certification', '2025-12-14 23:30:54');

-- --------------------------------------------------------

--
-- بنية الجدول `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int(11) NOT NULL,
  `status_key` varchar(50) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `status_icon` varchar(50) DEFAULT NULL,
  `status_color` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_statuses`
--

INSERT INTO `order_statuses` (`id`, `status_key`, `status_name`, `status_icon`, `status_color`, `is_active`, `display_order`) VALUES
(1, 'in_warehouse', 'في المزرعة', 'bx-store', '#ff0000', 1, 3),
(2, 'in_delivery', 'في الطريق الي المخزن', 'bx-car', '#ffff00', 1, 4),
(3, 'delivered', 'في الطريق الي المسلخ', 'bx-navigation', '#ff00ff', 1, 6),
(4, 'returned', ' الوصول الي المسلخ', 'bx-refresh', '#0000ff', 1, 7),
(5, 'com', ' الوصول الي المخزن', 'bx-store-alt', '#05a820', 1, 5),
(6, 'out_for_delivery', 'تجهيز النسك للذبح', 'bx-loader', '#00fffb', 1, 8),
(7, 'out_for', ' الانتهاء من اداء النسك', 'bx-check-circle', '#ff0000', 1, 8),
(8, 'out_fo', 'تجهيز التقطيع و التغليف', 'bx-check-circle', '#d000fa', 1, 10),
(9, 'out_f', ' التوزيع علي المستحقين', 'bx-cube', '#00ffee', 1, 11),
(10, 'out_e', 'انتهاء النسك', 'bx-check-shield', '#ffee00', 1, 12),
(11, 'out_for_delive', 'اختيار النسك', 'bx-certification', '#eeff00', 1, 1),
(12, 'out_for_deliv', 'الدفع و الاصدار', 'bx-badge-check', '#57ff89', 1, 2);

-- --------------------------------------------------------

--
-- بنية الجدول `order_status_dates`
--

CREATE TABLE `order_status_dates` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `actual_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_status_dates`
--

INSERT INTO `order_status_dates` (`id`, `order_id`, `status`, `scheduled_date`, `actual_date`) VALUES
(261, 122, 'out_for_delive', '2025-10-27 14:16:00', NULL),
(263, 124, 'out_for_delive', '2025-12-15 01:12:00', NULL),
(264, 123, 'out_for_delive', '2025-12-15 01:11:00', NULL),
(265, 125, 'out_for_delive', '2025-12-15 01:17:00', NULL),
(266, 126, 'out_for_delive', '2025-12-15 01:17:00', NULL),
(267, 127, 'out_for_delive', '2025-12-15 01:17:00', NULL),
(268, 128, 'out_for_delive', '2025-12-15 01:17:00', NULL),
(269, 129, 'out_for_delive', '2025-12-15 01:17:00', NULL),
(270, 130, 'out_for_delive', '2025-12-15 01:26:00', NULL),
(271, 131, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(272, 132, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(273, 133, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(274, 134, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(275, 135, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(276, 136, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(277, 137, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(278, 138, 'out_for_delive', '2025-12-15 01:27:00', NULL),
(279, 139, 'out_for_delive', '2025-12-15 01:28:00', NULL),
(280, 140, 'out_for_delive', '2025-12-15 01:29:00', NULL),
(281, 141, 'out_for_delive', '2025-12-15 01:30:00', NULL),
(282, 142, 'out_for_delive', '2025-12-15 01:30:00', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `about_text` text DEFAULT NULL,
  `contact_info` text DEFAULT NULL,
  `payment_methods` text DEFAULT NULL,
  `home_hero_title` varchar(255) DEFAULT NULL,
  `home_hero_subtitle` text DEFAULT NULL,
  `home_about_text` text DEFAULT NULL,
  `home_features_title` varchar(255) DEFAULT NULL,
  `track_header_title` varchar(255) DEFAULT NULL,
  `track_header_subtitle` text DEFAULT NULL,
  `track_instructions` text DEFAULT NULL,
  `company_history` text DEFAULT NULL,
  `mission_vision` text DEFAULT NULL,
  `contact_address` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `feature1_title` varchar(255) DEFAULT NULL,
  `feature1_description` text DEFAULT NULL,
  `feature2_title` varchar(255) DEFAULT NULL,
  `feature2_description` text DEFAULT NULL,
  `feature3_title` varchar(255) DEFAULT NULL,
  `feature3_description` text DEFAULT NULL,
  `about_image` varchar(255) DEFAULT NULL,
  `why_choose_title` varchar(255) DEFAULT NULL,
  `why_choose_description` text DEFAULT NULL,
  `track_section_title` varchar(255) DEFAULT NULL,
  `track_section_description` text DEFAULT NULL,
  `contact_hero_subtitle` text DEFAULT NULL,
  `contact_phone_title` varchar(255) DEFAULT NULL,
  `contact_email_title` varchar(255) DEFAULT NULL,
  `contact_address_title` varchar(255) DEFAULT NULL,
  `contact_hours` text DEFAULT NULL,
  `about_hero_text` text DEFAULT NULL,
  `about_story_title` varchar(255) DEFAULT NULL,
  `about_story_text` text DEFAULT NULL,
  `about_service_reliable_title` varchar(255) DEFAULT NULL,
  `about_service_reliable_text` text DEFAULT NULL,
  `about_service_professional_title` varchar(255) DEFAULT NULL,
  `about_service_professional_text` text DEFAULT NULL,
  `about_story_image` varchar(255) DEFAULT NULL,
  `about_stats_shipments` varchar(255) DEFAULT NULL,
  `about_stats_clients` varchar(255) DEFAULT NULL,
  `about_stats_cities` varchar(255) DEFAULT NULL,
  `about_stats_vehicles` varchar(255) DEFAULT NULL,
  `about_service1_title` varchar(255) DEFAULT NULL,
  `about_service1_text` text DEFAULT NULL,
  `about_service2_title` varchar(255) DEFAULT NULL,
  `about_service2_text` text DEFAULT NULL,
  `about_service3_title` varchar(255) DEFAULT NULL,
  `about_service3_text` text DEFAULT NULL,
  `about_timeline_title` varchar(255) DEFAULT NULL,
  `about_timeline1_year` varchar(255) DEFAULT NULL,
  `about_timeline1_text` text DEFAULT NULL,
  `about_timeline2_year` varchar(255) DEFAULT NULL,
  `about_timeline2_text` text DEFAULT NULL,
  `about_timeline3_year` varchar(255) DEFAULT NULL,
  `about_timeline3_text` text DEFAULT NULL,
  `site_logo` varchar(255) DEFAULT NULL,
  `footer_about_title` varchar(255) DEFAULT NULL,
  `footer_about_description` text DEFAULT NULL,
  `footer_copyright_text` varchar(255) DEFAULT NULL,
  `payment_methods_images` text DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_facebook_icon` varchar(255) DEFAULT NULL,
  `social_twitter_icon` varchar(255) DEFAULT NULL,
  `social_instagram_icon` varchar(255) DEFAULT NULL,
  `track_status_icons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`track_status_icons`)),
  `track_scheduled_date_text` varchar(255) DEFAULT 'التاريخ المجدول',
  `track_actual_date_text` varchar(255) DEFAULT 'التاريخ الفعلي',
  `track_status_scheduled_text` varchar(255) DEFAULT 'التاريخ المجدول:'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`id`, `logo_url`, `about_text`, `contact_info`, `payment_methods`, `home_hero_title`, `home_hero_subtitle`, `home_about_text`, `home_features_title`, `track_header_title`, `track_header_subtitle`, `track_instructions`, `company_history`, `mission_vision`, `contact_address`, `contact_email`, `contact_phone`, `hero_image`, `feature1_title`, `feature1_description`, `feature2_title`, `feature2_description`, `feature3_title`, `feature3_description`, `about_image`, `why_choose_title`, `why_choose_description`, `track_section_title`, `track_section_description`, `contact_hero_subtitle`, `contact_phone_title`, `contact_email_title`, `contact_address_title`, `contact_hours`, `about_hero_text`, `about_story_title`, `about_story_text`, `about_service_reliable_title`, `about_service_reliable_text`, `about_service_professional_title`, `about_service_professional_text`, `about_story_image`, `about_stats_shipments`, `about_stats_clients`, `about_stats_cities`, `about_stats_vehicles`, `about_service1_title`, `about_service1_text`, `about_service2_title`, `about_service2_text`, `about_service3_title`, `about_service3_text`, `about_timeline_title`, `about_timeline1_year`, `about_timeline1_text`, `about_timeline2_year`, `about_timeline2_text`, `about_timeline3_year`, `about_timeline3_text`, `site_logo`, `footer_about_title`, `footer_about_description`, `footer_copyright_text`, `payment_methods_images`, `social_facebook`, `social_twitter`, `social_instagram`, `social_facebook_icon`, `social_twitter_icon`, `social_instagram_icon`, `track_status_icons`, `track_scheduled_date_text`, `track_actual_date_text`, `track_status_scheduled_text`) VALUES
(1, '', 'نحن شركة رائدة في مجال خدمات الشحن والتوصيل ', 'اتصل بنا على مدار الساعة ', 'نقبل جميع وسائل الدفع', 'تتبع اضحيتك بسهولة وأمان ', 'تشمل قيمة السند كافة تكاليف الكشف البيطري، والكشف الشرعي، والتنفيذ، والتجهيز، والتبريد، والتعبئة والتغليف، والنقل الداخلي، والخارجي والتوزيع.', 'نقدم خدمات شحن آمنة وموثوقة', 'مميزات خدماتنا', 'تتبع اضحيتك', 'ادخل  الاسم او رقم الصك', 'يمكنك تتبع اضحيتك عن طريق  رقم الصك او الطلب او الاسم ', 'تأسست الشركة عام 2020', 'رؤيتنا هي أن نكون الخيار الأول في خدمات الشحن', 'الرياض - المملكة العربية السعودية ', 'info@mawashi.org', '+966 59 648 6391', '6900002b2ce8d.png', 'تتبع مباشر', 'تابع اضحيتك خطوة بخطوة \r\nمع تحديثات فورية للموقع \r\nوالحالة', 'تحديثات فورية', 'أحصل على أشعارات فورية \r\nعن حالة اضحيتك وموقعها \r\nالحالي', 'دعم متواصل', 'فريق دعم متخصص \r\nمتواجد على مدار الساعة لمساعدتك', '6900002bbe20e.png', 'وكّلنا وتوكّل', 'هدي، أضحية، فدية، صدقة، عقيقة\r\nتحذر الجهات المختصة من التعامل مع الوسطاء غير الشرعيين، وتشجع بدلاً من ذلك جميع الحجاج والمواطنين الراغبين في أداء نسك الهدي والفدية والأضاحي أثناء موسم الحج، على الاستفادة من الخدمات المعتمدة والتي توفر ضماناً للتنفيذ وفق الشروط الشرعية والصحية وتجنب الاحتيال.\r\n', 'تتبع الان طلبك', 'تتبع الان طلبك بالاسم او رقم الصك', 'نحن هنا لمساعدتك والإجابة على جميع استفساراتك ', 'اتصل بنا ', 'راسلنا', 'موقعنا', 'الأحد - الخميس: 9:00 صباحاً - 6:00 مساءً\r\nالجمعة - السبت: مغلق', 'مواشي هي شركة  سعودية تجارية تتعامل في مجال اللحوم والمواشي منذ حوالي 40 سنة, نضمن أن جميع المنتجات التي نقدمها هي من الجودة العالية والمصادقة. نسعى دائمًا إلى تحقيق رضا العملاء من خلال توفير الخدمات الأمثل وبأفضل الأسعار.', 'تاسيس الشركة', 'تأسست شركة مواشي لتجارة اللحوم والمواشي  من قبل آبائنا في اربعانيات القرن الماضي, هدفهم هو التأكيد على تقديم الجودة العالية والأمان للعملاء. تم اعتماد اسم مواشي من قبل الأجيال الماضية بسبب الثقة المتبادلة التي تمت بين عملائنا وشركتنا.', 'الإلتزام بالمواعيد', 'نسعى دائماً بكل ما نملك إلى الإلتزام بالمواعيد حتى نكسب رضا عملائنا وثقتهم.', 'المصداقية والنزاهة', 'علاقاتنا مبنية على الشفافية وأعلى المعايير المهنية والأخلاقية.', '', '250', '70000', '13', '150', 'تتبع اضحيتك', 'تتبع اضحيتك بكل سهولة ', '​​عمليات التوزيع', 'يشرف المشروع على عمليات التوزيع بشكل مباشر وذلك بالتعاون مع سفارات خادم الحرمين الشريفين والجهات الحكومية وغير الحكومية المرخصة في الدول المستفيدة.', '​​توزيع اللحوم', 'ﻳﺘﻢ ﺗﻮزﻳﻊ اﻟﻠﺤﻮم على المستحقين في الحرم، كما يتم نقل وتوزيع الفائض عن الحاجة للمستحقين بالداخل وفي أكثر من 27 دولة في أنحاء العالم الاسلامي', 'خدماتنا', '​توريد الأنعام', 'يعقد المشروع اتفاقيات مع أبرز موردي الأغنام في المملكة العربية السعودية حيث تمكنه الأعداد الموسمية الضخمة من الأنعام المتعاقد عليها لأداء النسك، فضلاً عن ميزات الوزن والجودة.\r\n', '​​الأسعار', 'تشمل قيمة السند كافة تكاليف الكشف البيطري، والكشف الشرعي، والتنفيذ، والتجهيز، والتبريد، والتعبئة والتغليف، والنقل الداخلي، والخارجي والتوزيع.\r\n', '​​بعثات الحج', 'تعمل إدارة المشروع جاهدة على تقديم خدمات مميزة لبعثات الحج. تتضمن توفير الدعم اللوجستي والتنظيمي اللازم لتنفيذ نسك الهدي والأضاحي واتمامها بكل يسر وسهولة، وبأعلى معايير الجودة.', 'logo_1761491064.png', 'مواشي', 'نظام متكامل لتتبع اضحيتك', 'جميع الحقوق محفوظة لشركة مواشي لتجارة الماشية © 2025 ', '[\"6826f3411cceb_1000039206.png\"]', 'https://shmoolptc.com/Atracking/admin/settings.php', 'https://shmoolptc.com/Atracking/admin/settings.php', 'https://shmoolptc.com/Atracking/admin/settings.php', '6828fdff2aa9f_icon4-1.png', '6828fdff2ab9b_icon7-2.png', '6828fdff2ac31_icon3-1.png', '[]', 'تم التنفيذ', 'في الطلب', 'Zakaria ');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
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
-- فهارس للجدول `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`);

--
-- فهارس للجدول `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- فهارس للجدول `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_key` (`status_key`);

--
-- فهارس للجدول `order_status_dates`
--
ALTER TABLE `order_status_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- فهارس للجدول `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- فهارس للجدول `users`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_status_dates`
--
ALTER TABLE `order_status_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=283;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- القيود المفروضة على الجداول الملقاة
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
