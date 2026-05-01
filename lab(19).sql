-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 09:56 PM
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
-- Database: `lab`
--

-- --------------------------------------------------------

--
-- Table structure for table `accreditation_certificates`
--

CREATE TABLE `accreditation_certificates` (
  `certificate_id` int(11) NOT NULL,
  `certificate_code` varchar(50) NOT NULL COMMENT 'e.g., TL 010-01',
  `certificate_name` varchar(200) NOT NULL COMMENT 'Descriptive name of certificate',
  `valid_from` date NOT NULL COMMENT 'Accreditation start date',
  `valid_to` date NOT NULL COMMENT 'Accreditation expiry date',
  `issued_date` date DEFAULT NULL COMMENT 'Date certificate was issued by SLAB',
  `is_current` tinyint(1) DEFAULT 0 COMMENT 'Only ONE certificate can be current at a time',
  `status` enum('active','expired','pending','superseded') DEFAULT 'active' COMMENT 'Certificate status',
  `scope_description` text DEFAULT NULL COMMENT 'What this certificate covers',
  `notes` text DEFAULT NULL COMMENT 'Additional notes',
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete flag',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SLAB accreditation certificates with validity tracking';

--
-- Dumping data for table `accreditation_certificates`
--

INSERT INTO `accreditation_certificates` (`certificate_id`, `certificate_code`, `certificate_name`, `valid_from`, `valid_to`, `issued_date`, `is_current`, `status`, `scope_description`, `notes`, `is_deleted`, `created_at`, `created_by`) VALUES
(1, 'TL 010-01', 'Microbiology Testing Accreditation 2024-2028', '2024-05-31', '2028-05-30', '2024-07-22', 1, 'active', 'ISO/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples', '', 0, '2026-02-22 07:09:34', 'system'),
(2, 'TL 010-01', 'Microbiology Testing Accreditation 2024-2028', '2024-05-31', '2028-05-30', '2024-07-22', 0, 'active', 'ISO/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples', '', 1, '2026-03-06 16:46:08', 'Kavidu Naveen'),
(3, 'TL 010-011', 'Microbiology Testing Accreditation 2024-2023', '2026-04-30', '2029-07-30', '2026-04-29', 0, 'active', '', '', 1, '2026-04-30 07:50:39', 'Kavidu Naveen');

-- --------------------------------------------------------

--
-- Table structure for table `base_units`
--

CREATE TABLE `base_units` (
  `base_unit_id` int(11) NOT NULL,
  `unit_name` varchar(100) DEFAULT NULL,
  `base_category_id` int(11) NOT NULL COMMENT 'Which category this unit belongs to',
  `unit_type` enum('count','presence','concentration','other') DEFAULT 'count' COMMENT 'Type of measurement',
  `is_common` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = show in dropdown by default, 0 = hide unless selected',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available units per category - pre-populated from SLAB certificate';

--
-- Dumping data for table `base_units`
--

INSERT INTO `base_units` (`base_unit_id`, `unit_name`, `base_category_id`, `unit_type`, `is_common`, `display_order`, `created_at`) VALUES
(1, 'cfu/mL', 1, 'count', 1, 1, '2026-02-27 16:58:57'),
(2, 'MPN/100 mL', 1, 'count', 1, 2, '2026-02-27 16:58:57'),
(3, 'MPN/mL', 1, 'count', 1, 3, '2026-02-27 16:58:57'),
(4, 'CFU/100mL', 1, 'count', 1, 4, '2026-02-27 16:58:57'),
(5, '/100 mL', 1, 'presence', 1, 5, '2026-02-27 16:58:57'),
(6, '/100-1000mL', 1, 'presence', 1, 6, '2026-02-27 16:58:57'),
(7, '/1000mL', 1, 'presence', 1, 7, '2026-02-27 16:58:57'),
(8, 'Present/Absent', 1, 'presence', 1, 8, '2026-02-27 16:58:57'),
(9, 'MPN/10mL', 1, 'count', 0, 9, '2026-02-27 16:58:57'),
(10, 'MPN/L', 1, 'count', 0, 10, '2026-02-27 16:58:57'),
(11, 'CFU/L', 1, 'count', 0, 11, '2026-02-27 16:58:57'),
(12, 'cfu/g', 2, 'count', 1, 1, '2026-02-27 16:58:58'),
(13, 'CFU/g', 2, 'count', 1, 2, '2026-02-27 16:58:58'),
(14, 'MPN/g', 2, 'count', 1, 3, '2026-02-27 16:58:58'),
(15, 'cfu/g (>1 x 10^1)', 2, 'count', 1, 4, '2026-02-27 16:58:58'),
(16, 'CFU/g (>1 x 10^1)', 2, 'count', 1, 5, '2026-02-27 16:58:58'),
(17, '/25g', 2, 'presence', 1, 6, '2026-02-27 16:58:58'),
(18, '/g', 2, 'presence', 1, 7, '2026-02-27 16:58:58'),
(19, 'Present/Absent', 2, 'presence', 1, 8, '2026-02-27 16:58:58'),
(20, 'MPN/25g', 2, 'count', 0, 9, '2026-02-27 16:58:58'),
(21, 'MPN/10g', 2, 'count', 0, 10, '2026-02-27 16:58:58'),
(22, 'cfu/10g', 2, 'count', 0, 11, '2026-02-27 16:58:58'),
(23, '/10g', 2, 'presence', 0, 12, '2026-02-27 16:58:58'),
(24, '/100g', 2, 'presence', 0, 13, '2026-02-27 16:58:58'),
(25, 'cfu/cm^2', 3, 'count', 1, 1, '2026-02-27 16:58:58'),
(26, 'CFU/cm^2', 3, 'count', 1, 2, '2026-02-27 16:58:58'),
(27, 'MPN/cm^2', 3, 'count', 1, 3, '2026-02-27 16:58:58'),
(28, 'cfu/cm^2 (>1 x 10^1)', 3, 'count', 1, 4, '2026-02-27 16:58:58'),
(29, 'CFU/cm^2 (>1 x 10^1)', 3, 'count', 1, 5, '2026-02-27 16:58:58'),
(30, '/cm^2', 3, 'presence', 1, 6, '2026-02-27 16:58:58'),
(31, 'Present/Absent', 3, 'presence', 1, 7, '2026-02-27 16:58:58'),
(32, 'cfu/swab', 3, 'count', 0, 8, '2026-02-27 16:58:58'),
(33, 'CFU/swab', 3, 'count', 0, 9, '2026-02-27 16:58:58'),
(34, 'MPN/swab', 3, 'count', 0, 10, '2026-02-27 16:58:58'),
(35, '/swab', 3, 'presence', 0, 11, '2026-02-27 16:58:58'),
(36, 'cfu/25cm^2', 3, 'count', 0, 12, '2026-02-27 16:58:58'),
(37, '/25cm^2', 3, 'presence', 0, 13, '2026-02-27 16:58:58');

-- --------------------------------------------------------

--
-- Table structure for table `base_unit_categories`
--

CREATE TABLE `base_unit_categories` (
  `base_category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL COMMENT 'Water and Ice, Food, Surface Swab',
  `category_code` varchar(20) NOT NULL COMMENT 'WATER, FOOD, SWAB',
  `description` text DEFAULT NULL COMMENT 'Description of this category',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Base unit categories for parameter configuration (Water, Food, Swab)';

--
-- Dumping data for table `base_unit_categories`
--

INSERT INTO `base_unit_categories` (`base_category_id`, `category_name`, `category_code`, `description`, `is_active`, `display_order`, `created_at`) VALUES
(1, 'Water and Ice', 'WATER', 'Water-based samples: Sea water, Coastal water, Potable water, Fresh water, Ice, Waste water', 1, 1, '2026-02-27 16:58:57'),
(2, 'Food', 'FOOD', 'Food samples: Fish and shellfish (chilled, frozen, canned)', 1, 2, '2026-02-27 16:58:57'),
(3, 'Surface Swab', 'SWAB', 'Surface sampling and swab testing', 1, 3, '2026-02-27 16:58:57');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `city_id` int(11) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `is_predefined` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = pre-loaded, 0 = user-added',
  `needs_review` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Flag for admin review',
  `usage_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Track popularity',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT 'system'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cities for client address standardization';

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`city_id`, `city_name`, `is_predefined`, `needs_review`, `usage_count`, `is_active`, `is_deleted`, `created_at`, `created_by`) VALUES
(1, 'Colombo', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(2, 'Colombo 1 - Fort', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(3, 'Fort', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(4, 'Colombo 2 - Slave Island', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(5, 'Slave Island', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(6, 'Colombo 3 - Kollupitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(7, 'Kollupitiya', 1, 0, 4, 1, 0, '2026-01-04 07:11:15', 'system'),
(8, 'Colombo 4 - Bambalapitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(9, 'Bambalapitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(10, 'Colombo 5 - Havelock Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(11, 'Havelock Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(12, 'Colombo 6 - Wellawatte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(13, 'Wellawatte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(14, 'Colombo 7 - Cinnamon Gardens', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(15, 'Cinnamon Gardens', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(16, 'Colombo 8 - Borella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(17, 'Borella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(18, 'Colombo 9 - Dematagoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(19, 'Dematagoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(20, 'Colombo  - Maradana', 1, 0, 3, 1, 0, '2026-01-04 07:11:15', 'system'),
(21, 'Maradana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(22, 'Colombo 11 - Pettah', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(23, 'Pettah', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(24, 'Colombo 12 - Hulftsdorp', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(25, 'Hulftsdorp', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(26, 'Colombo 13 - Kotahena', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(27, 'Kotahena', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(28, 'Colombo 14 - Grandpass', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(29, 'Grandpass', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(30, 'Colombo 15 - Mattakkuliya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(31, 'Mattakkuliya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(32, 'Modara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(33, 'Mutwal', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(34, 'Dehiwala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(35, 'Mount Lavinia', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(36, 'Ratmalana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(37, 'Moratuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(38, 'Piliyandala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(39, 'Kesbewa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(40, 'Kolonnawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(41, 'New Bazaar', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(42, 'Bloemendhal', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(43, 'Kochchikade', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(44, 'Aluthkade', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(45, 'Harbour', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(46, 'Kompanna Veediya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(47, 'Nawala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(48, 'Rajagiriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(49, 'Battaramulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(50, 'Kotte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(51, 'Sri Jayewardenepura Kotte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(52, 'Nugegoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(53, 'Maharagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(54, 'Kottawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(55, 'Pannipitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(56, 'Talawatugoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(57, 'Hokandara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(58, 'Athurugiriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(59, 'Malabe', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(60, 'Koswatta', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(61, 'Thalawathugoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(62, 'Kaduwela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(63, 'Avissawella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(64, 'Homagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(65, 'Padukka', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(66, 'Hanwella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(67, 'Boralesgamuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(68, 'Godagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(69, 'Pepiliyana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(70, 'Nedimala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(71, 'Kohuwala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(72, 'Gangodawila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(73, 'Thimbirigasyaya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(74, 'Narahenpita', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(75, 'Kirulapone', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(76, 'Pamankada', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(77, 'Wellampitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(78, 'Peliyagoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(79, 'Kelaniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(80, 'Angoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(81, 'Mulleriyawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(82, 'Thalangama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(83, 'Madiwela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(84, 'Pelawatte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(85, 'Thalahena', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(86, 'Pitakotte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(87, 'Udahamulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(88, 'Watareka', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(89, 'Ethul Kotte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(90, 'Welikada', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(91, 'Orugodawatta', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(92, 'Hunupitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(93, 'Maligawatte', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(94, 'Lunupokuna', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(95, 'Gothatuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(96, 'Angulana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(97, 'Rawathawatta', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(98, 'Kalubowila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(99, 'Wijerama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(100, 'Polhengoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(101, 'Werahera', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(102, 'Kosgama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(103, 'Meegoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(104, 'Polgasowita', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(105, 'Mattegoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(106, 'Makumbura', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(107, 'Nawinna', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(108, 'Gampaha', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(109, 'Negombo', 1, 0, 2, 1, 0, '2026-01-04 07:11:15', 'system'),
(110, 'Katunayake', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(111, 'Ja-Ela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(112, 'Seeduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(113, 'Wattala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(114, 'Mabole', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(115, 'Hendala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(116, 'Ragama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(117, 'Kadawatha', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(118, 'Kiribathgoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(119, 'Nittambuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(120, 'Veyangoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(121, 'Mirigama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(122, 'Minuwangoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(123, 'Divulapitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(124, 'Ganemulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(125, 'Biyagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(126, 'Delgoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(127, 'Kandana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(128, 'Pamunugama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(129, 'Dungalpitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(130, 'Katana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(131, 'Marawila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(132, 'Dankotuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(133, 'Wennappuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(134, 'Chilaw', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(135, 'Lunuwila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(136, 'Udugampola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(137, 'Yakkala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(138, 'Dompe', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(139, 'Pugoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(140, 'Attanagalla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(141, 'Kirindiwela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(142, 'Weliweriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(143, 'Radawana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(144, 'Meerigama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(145, 'Kalutara', 1, 0, 2, 1, 0, '2026-01-04 07:11:15', 'system'),
(146, 'Panadura', 1, 0, 4, 1, 0, '2026-01-04 07:11:15', 'system'),
(147, 'Wadduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(148, 'Bandaragama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(149, 'Horana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(150, 'Beruwala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(151, 'Aluthgama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(152, 'Matugama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(153, 'Ingiriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(154, 'Millaniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(155, 'Bulathsinhala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(156, 'Madurawala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(157, 'Dodangoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(158, 'Agalawatta', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(159, 'Palindanuwara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(160, 'Horawala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(161, 'Dharga Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(162, 'Kandy', 1, 0, 2, 1, 0, '2026-01-04 07:11:15', 'system'),
(163, 'Peradeniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(164, 'Katugastota', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(165, 'Gampola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(166, 'Nawalapitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(167, 'Kundasale', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(168, 'Akurana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(169, 'Kadugannawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(170, 'Pilimatalawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(171, 'Gelioya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(172, 'Pussellawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(173, 'Harispattuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(174, 'Galagedara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(175, 'Daulagala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(176, 'Teldeniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(177, 'Pilimathalawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(178, 'Wattegama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(179, 'Medawala Bazaar', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(180, 'Mulgampola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(181, 'Madawala Bazaar', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(182, 'Digana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(183, 'Pallekele', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(184, 'Mawanella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(185, 'Matale', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(186, 'Dambulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(187, 'Galewela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(188, 'Ukuwela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(189, 'Rattota', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(190, 'Yatawatta', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(191, 'Pallepola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(192, 'Naula', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(193, 'Laggala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(194, 'Sigiriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(195, 'Inamaluwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(196, 'Palapathwela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(197, 'Nuwara Eliya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(198, 'Hatton', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(199, 'Nanuoya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(200, 'Talawakele', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(201, 'Ginigathena', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(202, 'Rikillagaskada', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(203, 'Walapane', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(204, 'Ragala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(205, 'Kotagala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(206, 'Lindula', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(207, 'Labukele', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(208, 'Bogawantalawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(209, 'Maskeliya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(210, 'Norton Bridge', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(211, 'Pundaluoya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(212, 'Rozella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(213, 'Watawala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(214, 'Galle', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(215, 'Hikkaduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(216, 'Ambalangoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(217, 'Bentota', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(218, 'Balapitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(219, 'Elpitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(220, 'Baddegama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(221, 'Karapitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(222, 'Karandeniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(223, 'Ahangama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(224, 'Unawatuna', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(225, 'Koggala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(226, 'Habaraduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(227, 'Ginthota', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(228, 'Pitigala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(229, 'Neluwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(230, 'Nagoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(231, 'Imaduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(232, 'Batapola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(233, 'Dodanduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(234, 'Boossa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(235, 'Thalagaha', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(236, 'Matara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(237, 'Weligama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(238, 'Mirissa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(239, 'Akuressa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(240, 'Deniyaya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(241, 'Hakmana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(242, 'Kamburugamuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(243, 'Dikwella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(244, 'Devinuwara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(245, 'Gandara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(246, 'Kekanadurra', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(247, 'Kotapola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(248, 'Thihagoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(249, 'Pasgoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(250, 'Pitabeddara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(251, 'Kamburupitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(252, 'Urubokka', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(253, 'Beliatta', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(254, 'Hambantota', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(255, 'Tangalle', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(256, 'Tissamaharama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(257, 'Ambalantota', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(258, 'Kataragama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(259, 'Sooriyawewa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(260, 'Weeraketiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(261, 'Middeniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(262, 'Walasmulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(263, 'Angunakolapelessa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(264, 'Kirinda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(265, 'Bundala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(266, 'Wirawila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(267, 'Ranna', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(268, 'Jaffna', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(269, 'Nallur', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(270, 'Chavakachcheri', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(271, 'Point Pedro', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(272, 'Karainagar', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(273, 'Valvettithurai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(274, 'Chankanai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(275, 'Sandilipay', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(276, 'Kodikamam', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(277, 'Tellippalai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(278, 'Manipay', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(279, 'Kopay', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(280, 'Chunnakam', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(281, 'Karaveddy', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(282, 'Kayts', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(283, 'Velanai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(284, 'Delft', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(285, 'Kilinochchi', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(286, 'Pallai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(287, 'Paranthan', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(288, 'Poonakary', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(289, 'Karachchi', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(290, 'Mannar', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(291, 'Nanattan', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(292, 'Madhu', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(293, 'Pesalai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(294, 'Talaimannar', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(295, 'Vavuniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(296, 'Nedunkeni', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(297, 'Cheddikulam', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(298, 'Omanthai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(299, 'Mullaitivu', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(300, 'Puthukkudiyiruppu', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(301, 'Oddusuddan', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(302, 'Mankulam', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(303, 'Trincomalee', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(304, 'Kinniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(305, 'Kuchchaveli', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(306, 'Mutur', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(307, 'Kantale', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(308, 'Seruwila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(309, 'Nilaveli', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(310, 'Gomarankadawala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(311, 'China Bay', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(312, 'Batticaloa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(313, 'Kattankudy', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(314, 'Eravur', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(315, 'Kaluwanchikudy', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(316, 'Valachchenai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(317, 'Chenkalady', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(318, 'Oddamavadi', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(319, 'Kalkudah', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(320, 'Passikudah', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(321, 'Ampara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(322, 'Kalmunai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(323, 'Sammanthurai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(324, 'Pottuvil', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(325, 'Akkaraipattu', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(326, 'Nintavur', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(327, 'Sainthamaruthu', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(328, 'Addalachchenaii', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(329, 'Uhana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(330, 'Digamadulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(331, 'Arugam Bay', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(332, 'Kurunegala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(333, 'Kuliyapitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(334, 'Narammala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(335, 'Wariyapola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(336, 'Polgahawela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(337, 'Pannala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(338, 'Mawathagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(339, 'Melsiripura', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(340, 'Nikaweratiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(341, 'Galgamuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(342, 'Maho', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(343, 'Alawwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(344, 'Bingiriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(345, 'Giriulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(346, 'Hettipola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(347, 'Ibbagamuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(348, 'Ridigama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(349, 'Potuhera', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(350, 'Dambadeniya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(351, 'Puttalam', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(352, 'Anamaduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(353, 'Nattandiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(354, 'Madampe', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(355, 'Palavi', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(356, 'Mundel', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(357, 'Kalpitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(358, 'Norochcholai', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(359, 'Anuradhapura', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(360, 'Kekirawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(361, 'Medawachchiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(362, 'Habarana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(363, 'Mihintale', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(364, 'Tambuttegama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(365, 'Eppawala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(366, 'Talawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(367, 'Nochchiyagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(368, 'Rambewa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(369, 'Galenbindunuwewa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(370, 'Galnewa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(371, 'Kahatagasdigiliya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(372, 'Thirappane', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(373, 'Polonnaruwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(374, 'Kaduruwela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(375, 'Medirigiriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(376, 'Hingurakgoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(377, 'Minneriya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(378, 'Dimbulagala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(379, 'Welikanda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(380, 'Lankapura', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(381, 'Aralaganwila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(382, 'Badulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(383, 'Bandarawela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(384, 'Haputale', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(385, 'Welimada', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(386, 'Mahiyanganaya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(387, 'Ella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(388, 'Passara', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(389, 'Hali Ela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(390, 'Diyatalawa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(391, 'Kandaketiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(392, 'Lunugala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(393, 'Boralanda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(394, 'Koslanda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(395, 'Meegahakiula', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(396, 'Monaragala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(397, 'Wellawaya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(398, 'Bibile', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(399, 'Buttala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(400, 'Siyambalanduwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(401, 'Thanamalwila', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(402, 'Medagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(403, 'Okkampitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(404, 'Ratnapura', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(405, 'Embilipitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(406, 'Balangoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(407, 'Pelmadulla', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(408, 'Eheliyagoda', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(409, 'Kuruwita', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(410, 'Kahawatta', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(411, 'Kalawana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(412, 'Nivithigala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(413, 'Godakawela', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(414, 'Opanayaka', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(415, 'Weligepola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(416, 'Imbulpe', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(417, 'Ayagama', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(418, 'Elapatha', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(419, 'Kolonna', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(420, 'Rakwana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(421, 'Kegalle', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(422, 'Warakapola', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(423, 'Rambukkana', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(424, 'Galigamuwa', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(425, 'Ruwanwella', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(426, 'Dehiovita', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(427, 'Deraniyagala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(428, 'Yatiyantota', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(429, 'Aranayaka', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(430, 'Bulathkohupitiya', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(431, 'Kitulgala', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(432, 'Anuradhapura New Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(433, 'Kalmunai South', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(434, 'Trincomalee Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(435, 'Galle Fort', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(436, 'Kandy City', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(437, 'Jaffna Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(438, 'Moratuwa South', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(439, 'Kalutara South', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(440, 'Negombo Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(441, 'Kurunegala Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(442, 'Badulla Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(443, 'Ratnapura Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(444, 'Matara Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(445, 'Puttalam Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(446, 'Ampara Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(447, 'Batticaloa Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(448, 'Vavuniya Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(449, 'Mannar Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(450, 'Polonnaruwa New Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(451, 'Hambantota Town', 1, 0, 1, 1, 0, '2026-01-04 07:11:15', 'system'),
(453, 'Bombuwala', 1, 0, 0, 0, 1, '2026-01-04 07:29:18', 'navva_004'),
(454, 'Baduraliya', 1, 0, 0, 0, 1, '2026-01-04 09:46:54', 'Kavidu Naveen'),
(456, 'Colombo 10', 1, 0, 0, 1, 0, '2026-03-14 15:21:48', 'system'),
(457, 'Mankada', 1, 0, 0, 0, 1, '2026-03-14 15:30:27', 'Kavidu Naveen'),
(458, 'Addalachchenai', 1, 0, 0, 1, 0, '2026-04-30 15:23:12', 'Kavidu Naveen');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(200) NOT NULL,
  `address_line1` varchar(200) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `phone_primary` varchar(30) DEFAULT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `registration_date` date DEFAULT curdate(),
  `is_Active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `address_line1`, `city`, `city_id`, `phone_primary`, `contact_person`, `registration_date`, `is_Active`, `created_at`, `updated_at`) VALUES
(3, 'ABCD Pvt Ltd', '125/35/01, Akkara 29, Bombuwala, Kalutara', 'Colombo', 1, '0341212123', 'Naveen', '2025-11-03', 0, '2025-11-03 04:47:10', '2026-01-11 06:59:56'),
(4, 'ABC Pvt Ltd', '125/35/01, Akkara 29, Bombuwala, Kalutara South', 'Panadura', 145, '0111213244', 'Naveen', '2025-11-03', 1, '2025-11-03 06:07:48', '2026-01-11 13:23:22'),
(5, 'Jude san sea foods', 'No:41,Dungalpitiya,Thalahena', 'Negombo', 1, '0111314321', 'Jude', '2025-11-03', 1, '2025-11-03 06:30:18', '2026-01-14 07:07:10'),
(6, 'KNJ Lab', 'Kalutara', 'Panadura', 145, '0763740019', 'Kavidu Naveen', '2025-12-14', 1, '2025-12-14 09:05:58', '2026-01-11 13:31:43'),
(7, 'ABCD LAB Pvt Ltd', 'No 25, Nowhere 10', 'Colombo 10 - Maradana', NULL, '0763740110', 'Nobody Nobody', '2026-01-11', 1, '2026-01-11 07:54:41', '2026-01-11 13:01:19'),
(8, 'Ocean Fresh Seafoods Pvt Ltd', 'No 12, Harbour Road', 'Negombo', 109, '0312345001', 'Sunil Fernando', '2024-01-15', 1, '2024-01-15 03:30:00', '2026-01-14 04:30:00'),
(9, 'Marine Bio Lab', '45/A, Beach Road', 'Colombo', 1, '0112345002', 'Dr. Nimal Perera', '2024-01-20', 1, '2024-01-20 05:00:00', '2026-01-14 05:30:00'),
(10, 'Blue Wave Fisheries', '78 Coastal Drive', 'Galle', 214, '0912345003', 'Kamala Silva', '2024-02-01', 1, '2024-02-01 02:45:00', '2026-01-14 04:00:00'),
(11, 'Aqua Testing Laboratory', '234 Marine Avenue', 'Kalutara', 145, '0342345004', 'Rohan Jayasinghe', '2024-02-10', 1, '2024-02-10 08:50:00', '2026-01-13 10:30:00'),
(12, 'Golden Tuna Exports Ltd', '56 Port Access Road', 'Trincomalee', 303, '0262345005', 'Dilani Mendis', '2024-02-15', 1, '2024-02-15 05:30:00', '2026-01-12 09:30:00'),
(13, 'Seafood Quality Lab', '89/2, Industrial Zone', 'Panadura', 146, '0382345006', 'Asanka Wijewardena', '2024-03-01', 1, '2024-03-01 04:15:00', '2026-01-11 08:30:00'),
(14, 'Pearl Aquaculture Pvt Ltd', '123 Lagoon Road', 'Negombo', 109, '0312345007', 'Sanduni Ranasinghe', '2024-03-05', 1, '2024-03-05 04:30:00', '2026-01-10 07:30:00'),
(15, 'Lanka Water Testing Center', '67 Station Road', 'Colombo', 1, '0112345008', 'Prasad Kumar', '2024-03-10', 1, '2024-03-10 03:00:00', '2026-01-09 06:30:00'),
(16, 'Sea Harvest Processing', 'No 34, Fishery Lane', 'Chilaw', 134, '0322345009', 'Madhavi Gunasekara', '2024-03-15', 1, '2024-03-15 03:45:00', '2026-01-08 05:30:00'),
(17, 'Coral Reef Laboratories', '156 Ocean View', 'Galle', 214, '0912345010', 'Tharindu Dias', '2024-03-20', 1, '2024-03-20 05:15:00', '2026-01-07 04:30:00'),
(18, 'Fresh Catch Processors Ltd', '45 Market Street', 'Negombo', 109, '0312345011', 'Chaminda Perera', '2024-03-25', 1, '2024-03-25 05:50:00', '2026-01-06 03:30:00'),
(19, 'Oceanic Research Lab', '789 University Road', 'Colombo', 1, '0112345012', 'Dr. Sandya Fernando', '2024-04-01', 1, '2024-04-01 02:30:00', '2026-01-05 08:30:00'),
(20, 'Blue Lagoon Seafood Co', '23/A, Coastal Highway', 'Kalutara', 145, '0342345013', 'Nuwan Silva', '2024-04-05', 1, '2024-04-05 04:00:00', '2026-01-04 07:30:00'),
(21, 'Marine Food Processing', '90 Industrial Estate', 'Panadura', 146, '0382345014', 'Ruwan Jayawardena', '2024-04-10', 1, '2024-04-10 04:30:00', '2026-01-03 06:30:00'),
(22, 'Aqua Life Laboratory', '456 Research Center', 'Kandy', 162, '0812345015', 'Nilmini Wijesinghe', '2024-04-15', 1, '2024-04-15 03:15:00', '2026-01-02 05:30:00'),
(23, 'Deep Sea Fisheries Ltd', 'No 67, Harbour View', 'Trincomalee', 303, '0262345016', 'Kamal Rajapaksa', '2024-04-20', 1, '2024-04-20 03:30:00', '2026-01-01 04:30:00'),
(24, 'Crystal Waters Testing Lab', '123 Main Street', 'Colombo', 1, '0112345017', 'Sharmila Mendis', '2024-04-25', 1, '2024-04-25 05:00:00', '2025-12-31 09:30:00'),
(25, 'Seafood Excellence Pvt Ltd', '89 Export Zone', 'Negombo', 109, '0312345018', 'Ajith Fernando', '2024-05-01', 1, '2024-05-01 05:30:00', '2025-12-30 08:30:00'),
(26, 'Tidal Wave Processing', '234/B, Beach Front', 'Galle', 214, '0912345019', 'Priyanka Silva', '2024-05-05', 1, '2024-05-05 02:45:00', '2025-12-29 07:30:00'),
(27, 'Marine Quality Control Lab', '45 Science Park', 'Colombo', 1, '0112345020', 'Dr. Chamara Perera', '2024-05-10', 1, '2024-05-10 04:15:00', '2025-12-28 06:30:00'),
(28, 'Ocean Breeze Seafood', '678 Coastal Road', 'Chilaw', 134, '0322345021', 'Nadeeka Jayasinghe', '2024-05-15', 1, '2024-05-15 04:50:00', '2025-12-27 05:30:00'),
(29, 'Aquatic Food Solutions', '12/C, Industrial Park', 'Kalutara', 145, '0342345022', 'Sampath Wijewardena', '2024-05-20', 1, '2024-05-20 03:00:00', '2025-12-26 04:30:00'),
(30, 'Fisherman Pride Exports', '345 Port Road', 'Negombo', 109, '0312345023', 'Malini Ranasinghe', '2024-05-25', 1, '2024-05-25 03:30:00', '2025-12-25 03:30:00'),
(31, 'Water Quality Testing Center', '567 Hospital Road', 'Panadura', 146, '0382345024', 'Udara Kumar', '2024-06-01', 1, '2024-06-01 04:30:00', '2025-12-24 08:30:00'),
(32, 'Sea Star Processing Ltd', '890 Fishery Complex', 'Trincomalee', 303, '0262345025', 'Geetha Gunasekara', '2024-06-05', 1, '2024-06-05 06:00:00', '2025-12-23 07:30:00'),
(33, 'Blue Ocean Laboratory', '123/A, University Avenue', 'Colombo', 1, '0112345026', 'Dr. Roshan Dias', '2024-06-10', 1, '2024-06-10 02:30:00', '2025-12-22 06:30:00'),
(34, 'Pacific Seafood Traders', '456 Market Complex', 'Galle', 214, '0912345027', 'Sanduni Perera', '2024-06-15', 1, '2024-06-15 03:45:00', '2025-12-21 05:30:00'),
(35, 'Aqua Pure Testing Lab', '789/B, Industrial Zone', 'Matara', 236, '0412345028', 'Hasitha Fernando', '2024-06-20', 1, '2024-06-20 05:15:00', '2025-12-20 04:30:00'),
(36, 'Marina Food Processors', '234 Export Hub', 'Negombo', 109, '0312345029', 'Chandrika Silva', '2024-06-25', 1, '2024-06-25 03:00:00', '2025-12-19 03:30:00'),
(37, 'Coastal Waters Lab', '567/C, Beach Road', 'Colombo', 1, '0112345030', 'Anura Jayawardena', '2024-07-01', 1, '2024-07-01 03:30:00', '2025-12-18 08:30:00'),
(38, 'Sea Pearl Exports Pvt Ltd', '890 Harbour Area', 'Chilaw', 134, '0322345031', 'Kumari Wijesinghe', '2024-07-05', 1, '2024-07-05 05:00:00', '2025-12-17 07:30:00'),
(39, 'Marine Biotech Laboratory', '123 Research Institute', 'Kandy', 162, '0812345032', 'Dr. Malith Mendis', '2024-07-10', 1, '2024-07-10 05:30:00', '2025-12-16 06:30:00'),
(40, 'Ocean Harvest Trading Co', '456/A, Coastal Strip', 'Trincomalee', 303, '0262345033', 'Sajith Rajapaksa', '2024-07-15', 1, '2024-07-15 02:45:00', '2025-12-15 05:30:00'),
(41, 'Aquarium Testing Services', '789 Main Road', 'Moratuwa', 37, '0112345034', 'Nimali Fernando', '2024-07-20', 1, '2024-07-20 04:15:00', '2025-12-14 04:30:00'),
(42, 'Tuna Masters Processing', '234/B, Industrial Area', 'Galle', 214, '0912345035', 'Dinesh Silva', '2024-07-25', 1, '2024-07-25 04:50:00', '2025-12-13 03:30:00'),
(43, 'Sea Fresh Laboratories', '567 Science Complex', 'Panadura', 146, '0382345036', 'Yashodha Perera', '2024-08-01', 1, '2024-08-01 03:00:00', '2025-12-12 08:30:00'),
(44, 'Blue Fin Seafood Ltd', '890/C, Fishery Port', 'Negombo', 109, '0312345037', 'Ranjan Jayasinghe', '2024-08-05', 1, '2024-08-05 03:30:00', '2025-12-11 07:30:00'),
(45, 'Water Analysis Center', '123 Health Department', 'Colombo', 1, '0112345038', 'Dr. Thilini Wijewardena', '2024-08-10', 1, '2024-08-10 05:00:00', '2025-12-10 06:30:00'),
(46, 'Dolphin Seafood Processors', '456 Processing Plant', 'Kalutara', 145, '0342345039', 'Ashan Ranasinghe', '2024-08-15', 1, '2024-08-15 05:30:00', '2025-12-09 05:30:00'),
(47, 'Marine Life Testing Lab', '789/A, Research Wing', 'Kandy', 162, '0812345040', 'Rashmi Kumar', '2024-08-20', 1, '2024-08-20 02:30:00', '2025-12-08 04:30:00'),
(48, 'Sea Breeze Exports', '234 Harbour Road', 'Trincomalee', 303, '0262345041', 'Damith Gunasekara', '2024-08-25', 1, '2024-08-25 03:45:00', '2025-12-07 03:30:00'),
(49, 'Aqua Check Laboratory', '567/B, Medical Center', 'Colombo', 1, '0112345042', 'Samanthi Dias', '2024-09-01', 1, '2024-09-01 05:15:00', '2025-12-06 08:30:00'),
(50, 'Ocean King Processing', '890 Industrial Estate', 'Chilaw', 134, '0322345043', 'Pradeep Perera', '2024-09-05', 1, '2024-09-05 03:00:00', '2025-12-05 07:30:00'),
(51, 'Coral Sea Laboratory', '123/C, University Grounds', 'Galle', 214, '0912345044', 'Dr. Ishara Fernando', '2024-09-10', 1, '2024-09-10 03:30:00', '2025-12-04 06:30:00'),
(52, 'Marine Food Exports Ltd', '456 Port Complex', 'Negombo', 109, '0312345045', 'Sulochana Silva', '2024-09-15', 1, '2024-09-15 05:00:00', '2025-12-03 05:30:00'),
(53, 'Deep Blue Testing Services', '789 Laboratory Building', 'Colombo', 1, '0112345046', 'Mahinda Jayawardena', '2024-09-20', 1, '2024-09-20 05:30:00', '2025-12-02 04:30:00'),
(54, 'Fishery Products Pvt Ltd', '234/A, Export Zone', 'Panadura', 146, '0382345047', 'Rangika Wijesinghe', '2024-09-25', 1, '2024-09-25 02:45:00', '2026-03-06 06:56:36'),
(55, 'Aqua Science Laboratory', '567 Research Park', 'Kalutara', 145, '0342345048', 'Lakshan Mendis', '2024-10-01', 1, '2024-10-01 04:15:00', '2025-11-30 08:30:00'),
(56, 'Seagull Seafood Trading', '890/B, Market Street', 'Trincomalee', 303, '0262345049', 'Nirosha Rajapaksa', '2024-10-05', 1, '2024-10-05 04:50:00', '2025-11-29 07:30:00'),
(57, 'Marine Environmental Lab', '123 Environmental Center', 'Colombo', 1, '0112345050', 'Dr. Kasun Fernando', '2024-10-10', 1, '2024-10-10 03:00:00', '2025-11-28 06:30:00'),
(58, 'Ocean Pride Processing', '456/C, Industrial Park', 'Galle', 214, '0912345051', 'Ayesha Silva', '2024-10-15', 1, '2024-10-15 03:30:00', '2025-11-27 05:30:00'),
(59, 'Water Safety Testing Lab', '789 Health Complex', 'Negombo', 109, '0312345052', 'Bandula Perera', '2024-10-20', 1, '2024-10-20 05:00:00', '2025-11-26 04:30:00'),
(60, 'Sea Dragon Exports Ltd', '234 Shipping Terminal', 'Chilaw', 134, '0322345053', 'Vindya Jayasinghe', '2024-10-25', 1, '2024-10-25 05:30:00', '2025-11-25 03:30:00'),
(61, 'Aquatic Research Center', '567/A, Science Building', 'Kandy', 162, '0812345054', 'Arjuna Wijewardena', '2024-11-01', 1, '2024-11-01 02:30:00', '2025-11-24 08:30:00'),
(62, 'Blue Water Processors', '890 Processing Complex', 'Kalutara', 145, '0342345055', 'Sriyani Ranasinghe', '2024-11-05', 1, '2024-11-05 03:45:00', '2025-11-23 07:30:00'),
(63, 'Marine Quality Lab', '123/B, Medical Avenue', 'Colombo', 1, '0112345056', 'Dr. Janaka Kumar', '2024-11-10', 1, '2024-11-10 05:15:00', '2025-11-22 06:30:00'),
(64, 'Sea Crest Trading Co', '456 Harbour Front', 'Batticaloa', 312, '0652345057', 'Dilrukshi Gunasekara', '2024-11-15', 1, '2024-11-15 03:00:00', '2025-11-21 05:30:00'),
(65, 'Aqua Care Laboratory', '789/C, Hospital Road', 'Panadura', 146, '0382345058', 'Sumith Dias', '2024-11-20', 1, '2024-11-20 03:30:00', '2025-11-20 04:30:00'),
(66, 'Ocean Wave Processing', '234 Industrial Hub', 'Galle', 214, '0912345059', 'Miyuru Perera', '2024-11-25', 1, '2024-11-25 05:00:00', '2025-11-19 03:30:00'),
(67, 'Pearl Waters Testing Lab', '567 Laboratory Complex', 'Colombo', 1, '0112345060', 'Shalini Fernando', '2024-12-01', 1, '2024-12-01 05:30:00', '2025-11-18 08:30:00'),
(68, 'Marine Catch Exports', '890/A, Port Access', 'Negombo', 109, '0312345061', 'Gamini Silva', '2024-12-05', 1, '2024-12-05 02:45:00', '2025-11-17 07:30:00'),
(69, 'Deep Ocean Laboratory', '123 Research Facility', 'Kandy', 162, '0812345062', 'Dr. Anusha Jayawardena', '2024-12-10', 1, '2024-12-10 04:15:00', '2025-11-16 06:30:00'),
(70, 'Sea Food Masters Ltd', '456/B, Market Complex', 'Chilaw', 134, '0322345063', 'Wasantha Wijesinghe', '2024-12-15', 1, '2024-12-15 04:50:00', '2025-11-15 05:30:00'),
(71, 'Aqua Test Services', '789 Science Wing', 'Colombo', 1, '0112345064', 'Udeni Mendis', '2024-12-20', 1, '2024-12-20 03:00:00', '2025-11-14 04:30:00'),
(72, 'Ocean Blue Processors', '234/C, Processing Zone', 'Kalutara', 145, '0342345065', 'Nalin Rajapaksa', '2024-12-25', 1, '2024-12-25 03:30:00', '2025-11-13 03:30:00'),
(73, 'Marine Bio Testing Lab', '567 Medical Park', 'Trincomalee', 303, '0262345066', 'Chalani Fernando', '2025-01-01', 1, '2025-01-01 05:00:00', '2025-11-12 08:30:00'),
(74, 'Sea Shell Exports Pvt Ltd', '890 Fishery Zone', 'Galle', 214, '0912345067', 'Upul Silva', '2025-01-05', 1, '2025-01-05 05:30:00', '2025-11-11 07:30:00'),
(75, 'Water Quality Services', '123/A, Health Center', 'Panadura', 146, '0382345068', 'Dilini Perera', '2025-01-10', 1, '2025-01-10 02:30:00', '2025-11-10 06:30:00'),
(76, 'Aquatic Food Processing', '456 Industrial Area', 'Negombo', 109, '0312345069', 'Sanjeewa Jayasinghe', '2025-01-15', 1, '2025-01-15 03:45:00', '2025-11-09 05:30:00'),
(77, 'Blue Coral Laboratory', '789/B, Research Center', 'Colombo', 1, '0112345070', 'Dr. Samanthi Wijewardena', '2025-01-20', 1, '2025-01-20 05:15:00', '2025-11-08 04:30:00'),
(78, 'Sea Treasure Trading', '234 Export Terminal', 'Chilaw', 134, '0322345071', 'Chandana Ranasinghe', '2025-01-25', 1, '2025-01-25 03:00:00', '2025-11-07 03:30:00'),
(79, 'Marine Food Lab', '567/C, Science Complex', 'Kandy', 162, '0812345072', 'Nimanthi Kumar', '2025-02-01', 1, '2025-02-01 03:30:00', '2025-11-06 08:30:00'),
(80, 'Ocean Fresh Processing', '890 Processing Plant', 'Kalutara', 145, '0342345073', 'Tharanga Gunasekara', '2025-02-05', 1, '2025-02-05 05:00:00', '2025-11-05 07:30:00'),
(81, 'Aqua Marine Laboratory', '123 Testing Center', 'Colombo', 1, '0112345074', 'Dr. Rohan Dias', '2025-02-10', 1, '2025-02-10 05:30:00', '2025-11-04 06:30:00'),
(82, 'Prawn Processing Lanka', '456/A, Industrial Zone', 'Negombo', 109, '0312345075', 'Malini Perera', '2025-02-15', 1, '2025-02-15 02:45:00', '2025-11-03 05:30:00'),
(83, 'Coastal Lab Services', '789 Beach Road', 'Galle', 214, '0912345076', 'Darshana Silva', '2025-02-20', 1, '2025-02-20 04:15:00', '2025-11-02 04:30:00'),
(84, 'Sea Harvest Exports Ltd', '234/B, Port Area', 'Trincomalee', 303, '0262345077', 'Yasantha Jayawardena', '2025-02-25', 1, '2025-02-25 04:50:00', '2025-11-01 03:30:00'),
(85, 'Water Testing Institute', '567 Medical Complex', 'Panadura', 146, '0382345078', 'Kumari Wijesinghe', '2025-03-01', 1, '2025-03-01 03:00:00', '2025-10-31 08:30:00'),
(86, 'Lagoon Seafood Company', '890/C, Fishery Lane', 'Chilaw', 134, '0322345079', 'Prasanna Mendis', '2025-03-05', 1, '2025-03-05 03:30:00', '2025-10-30 07:30:00'),
(87, 'Marine Science Lab', '123 University Park', 'Kandy', 162, '0812345080', 'Dr. Nishantha Rajapaksa', '2025-03-10', 1, '2025-03-10 05:00:00', '2025-10-29 06:30:00'),
(88, 'Tuna Processing Lanka', '456 Export Hub', 'Galle', 214, '0912345081', 'Chandima Fernando', '2025-03-15', 1, '2025-03-15 05:30:00', '2025-10-28 05:30:00'),
(89, 'Aqua Tech Laboratory', '789/A, Research Park', 'Colombo', 1, '0112345082', 'Dilshan Silva', '2025-03-20', 1, '2025-03-20 02:30:00', '2025-10-27 04:30:00'),
(90, 'Ocean Products Lanka', '234 Industrial Estate', 'Negombo', 109, '0312345083', 'Sachini Perera', '2025-03-25', 1, '2025-03-25 03:45:00', '2025-10-26 03:30:00'),
(91, 'Marine Resources Lab', '567/B, Science Avenue', 'Kalutara', 145, '0342345084', 'Dr. Uditha Jayasinghe', '2025-04-01', 1, '2025-04-01 05:15:00', '2025-10-25 08:30:00'),
(92, 'Seafood Processing Centre', '890 Processing Complex', 'Panadura', 146, '0382345085', 'Buddhika Wijewardena', '2025-04-05', 1, '2025-04-05 03:00:00', '2025-10-24 07:30:00'),
(93, 'Blue Horizon Exports', '123/C, Harbour View', 'Trincomalee', 303, '0262345086', 'Shamila Ranasinghe', '2025-04-10', 1, '2025-04-10 03:30:00', '2025-10-23 06:30:00'),
(94, 'Aqua Diagnostics Lab', '456 Health Center', 'Colombo', 1, '0112345087', 'Dr. Gayan Kumar', '2025-04-15', 1, '2025-04-15 05:00:00', '2025-10-22 05:30:00'),
(95, 'Coastal Fisheries Ltd', '789 Market Street', 'Chilaw', 134, '0322345088', 'Niluka Gunasekara', '2025-04-20', 1, '2025-04-20 05:30:00', '2025-10-21 04:30:00'),
(96, 'Marine Processing Hub', '234/A, Industrial Park', 'Galle', 214, '0912345089', 'Sanath Dias', '2025-04-25', 1, '2025-04-25 02:45:00', '2025-10-20 03:30:00'),
(97, 'Water Bio Lab', '567 Laboratory Building', 'Kandy', 162, '0812345090', 'Dr. Thushara Perera', '2025-05-01', 1, '2025-05-01 04:15:00', '2025-10-19 08:30:00'),
(98, 'Ocean Wealth Seafoods', '890/B, Coastal Road', 'Negombo', 109, '0312345091', 'Janaki Fernando', '2025-05-05', 1, '2025-05-05 04:50:00', '2025-10-18 07:30:00'),
(99, 'Aqua Solutions Lab', '123 Science Complex', 'Colombo', 1, '0112345092', 'Hasitha Silva', '2025-05-10', 1, '2025-05-10 03:00:00', '2025-10-17 06:30:00'),
(100, 'Seafood Traders Lanka', '456/C, Port Terminal', 'Kalutara', 145, '0342345093', 'Dilrukshi Jayawardena', '2025-05-15', 1, '2025-05-15 03:30:00', '2025-10-16 05:30:00'),
(101, 'Marine Testing Centre', '789 Medical Park', 'Panadura', 146, '0382345094', 'Dr. Madush Wijesinghe', '2025-05-20', 1, '2025-05-20 05:00:00', '2025-10-15 04:30:00'),
(102, 'Blue Ocean Exports Ltd', '234 Shipping Complex', 'Trincomalee', 303, '0262345095', 'Rashmi Mendis', '2025-05-25', 1, '2025-05-25 05:30:00', '2025-10-14 03:30:00'),
(103, 'Aquatic Life Lab', '567/A, Research Institute', 'Kandy', 162, '0812345096', 'Dr. Chamara Rajapaksa', '2025-06-01', 1, '2025-06-01 02:30:00', '2025-10-13 08:30:00'),
(104, 'Coastal Processing Co', '890 Industrial Area', 'Galle', 214, '0912345097', 'Anushka Fernando', '2025-06-05', 1, '2025-06-05 03:45:00', '2025-10-12 07:30:00'),
(105, 'Water Research Lab', '123/B, University Road', 'Colombo', 1, '0112345098', 'Dr. Sampath Silva', '2025-06-10', 1, '2025-06-10 05:15:00', '2025-10-11 06:30:00'),
(106, 'Sea Gold Seafoods', '456 Fishery Complex', 'Negombo', 109, '0312345099', 'Niroshan Perera', '2025-06-15', 1, '2025-06-15 03:00:00', '2025-10-10 05:30:00'),
(107, 'Marine Analytics Lab', '789/C, Testing Center', 'Chilaw', 134, '0322345100', 'Dr. Lakshitha Jayasinghe', '2025-06-20', 1, '2025-06-20 03:30:00', '2025-10-09 04:30:00'),
(108, 'OLS Food (Pvt) Ltd', 'No:290 Thoduwawa North', 'Thoduwawa', NULL, '0769736972', 'QC Officer', '2026-02-03', 1, '2026-02-03 05:21:40', '2026-02-03 08:26:35'),
(109, 'KNJ Lab (PVT) Ltd', 'Akkara 29: Bombuwala', 'Kalutara', NULL, '0111787445', 'Naveen', '2026-03-13', 1, '2026-03-13 13:04:41', '2026-03-13 13:05:18');

-- --------------------------------------------------------

--
-- Table structure for table `combination_items`
--

CREATE TABLE `combination_items` (
  `combo_item_id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `sequence_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combination_items`
--

INSERT INTO `combination_items` (`combo_item_id`, `combo_id`, `parameter_id`, `sequence_order`, `created_at`) VALUES
(1, 1, 2, 0, '2025-11-13 06:18:36'),
(2, 1, 3, 1, '2025-11-13 06:18:36'),
(3, 2, 2, 0, '2025-11-17 09:45:38'),
(4, 2, 4, 1, '2025-11-17 09:45:38'),
(5, 2, 3, 2, '2025-11-17 09:45:38'),
(6, 3, 2, 0, '2025-11-17 09:46:42'),
(7, 3, 3, 1, '2025-11-17 09:46:42'),
(8, 4, 2, 0, '2025-11-17 09:47:09'),
(9, 4, 4, 1, '2025-11-17 09:47:09'),
(10, 4, 3, 2, '2025-11-17 09:47:09'),
(11, 5, 2, 0, '2026-03-08 07:08:14'),
(12, 5, 4, 1, '2026-03-08 07:08:14'),
(13, 6, 4, 0, '2026-03-08 07:08:35'),
(14, 6, 3, 1, '2026-03-08 07:08:35');

-- --------------------------------------------------------

--
-- Table structure for table `combination_pricing`
--

CREATE TABLE `combination_pricing` (
  `combo_pricing_id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `test_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combination_pricing`
--

INSERT INTO `combination_pricing` (`combo_pricing_id`, `combo_id`, `test_charge`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 1250.00, 1, 0, '2025-11-13 06:18:36', NULL),
(2, 2, 1375.00, 1, 0, '2025-11-17 09:45:39', NULL),
(3, 3, 1250.00, 1, 1, '2025-11-17 09:46:42', '2026-03-01 17:40:39'),
(4, 4, 1375.00, 1, 1, '2025-11-17 09:47:10', '2026-03-01 17:40:39'),
(5, 5, 1375.00, 1, 0, '2026-03-08 07:08:14', NULL),
(6, 6, 1375.00, 1, 0, '2026-03-08 07:08:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `extra_items`
--

CREATE TABLE `extra_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_value` decimal(10,2) NOT NULL COMMENT 'Numeric value (500, 1000, etc)',
  `item_unit` varchar(20) NOT NULL COMMENT 'Unit: ml, g, l, kg, piece',
  `item_price` decimal(10,2) NOT NULL COMMENT 'Price in Rs.',
  `item_description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT 'system',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Extra items for kiosk/sample submissions';

--
-- Dumping data for table `extra_items`
--

INSERT INTO `extra_items` (`item_id`, `item_name`, `item_value`, `item_unit`, `item_price`, `item_description`, `is_active`, `is_deleted`, `display_order`, `created_at`, `created_by`, `updated_at`) VALUES
(1, 'Water Bottle', 500.00, 'ml', 50.00, 'Sterile water sample collection bottle - 500ml', 1, 0, 1, '2026-01-04 16:59:51', 'system', NULL),
(2, 'Water Bottle', 1000.00, 'ml', 75.00, 'Sterile water sample collection bottle - 1L', 1, 0, 2, '2026-01-04 16:59:51', 'system', NULL),
(3, 'Sterile Bag', 250.00, 'mL', 60.00, 'ggghhgkkk', 1, 0, 0, '2026-03-14 16:02:33', 'nav019', '2026-04-30 15:36:26'),
(4, 'Sterile Bag', 1000.00, 'g', 100.00, '', 0, 1, 0, '2026-03-14 16:20:04', 'nav019', '2026-03-14 16:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `final_test_reports`
--

CREATE TABLE `final_test_reports` (
  `report_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL COMMENT 'FK to samples table',
  `report_number` varchar(50) NOT NULL COMMENT 'Same as sample report_ref e.g. QC/26/009/01',
  `report_type` enum('accredited','non_accredited') NOT NULL COMMENT 'Determines logo count and asterisks',
  `layout_type` enum('single','multi_column','non_accredited_single') NOT NULL DEFAULT 'single' COMMENT 'single=1 item/col, multi_column=2-5 items as columns, non_accredited_single=forced 1 item',
  `signatory_left_id` int(11) DEFAULT NULL COMMENT 'FK to report_signatories (scientist)',
  `signatory_right_id` int(11) DEFAULT NULL COMMENT 'FK to report_signatories (head)',
  `signatory_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Snapshot of signatory data at generation time' CHECK (json_valid(`signatory_snapshot`)),
  `report_data_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Full data snapshot for reprint' CHECK (json_valid(`report_data_snapshot`)),
  `generated_by` int(11) NOT NULL COMMENT 'FK to users',
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `print_count` int(11) DEFAULT 0,
  `last_printed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `final_test_reports`
--

INSERT INTO `final_test_reports` (`report_id`, `sample_id`, `report_number`, `report_type`, `layout_type`, `signatory_left_id`, `signatory_right_id`, `signatory_snapshot`, `report_data_snapshot`, `generated_by`, `generated_at`, `print_count`, `last_printed_at`, `notes`, `is_deleted`, `created_at`) VALUES
(47, 9, '26/009', 'accredited', 'single', 1, 2, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":9,\"sample_code\":\"26\\/009\\/001\",\"form_number\":\"26\\/009\\/001\",\"report_ref\":\"26\\/009\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-02\",\"received_time\":\"12:32:00\",\"tentative_date\":\"2026-03-12\",\"sample_collected_date\":\"2026-03-01\",\"sample_collected_time\":\"12:32:00\",\"analysis_start_date\":\"2026-02-11\",\"analysis_end_date\":\"2026-03-03\",\"is_drawn_by_nara\":0,\"client_name\":\"Aqua Care Laboratory\",\"client_address\":\"789\\/C, Hospital Road\",\"client_phone\":\"0382345058\",\"city_name\":\"Panadura\"},\"items\":[{\"sample_item_id\":12,\"sample_name\":\"Water\",\"sample_value\":\"100\",\"sample_unit\":\"mL\",\"client_sample_code\":\"\",\"sampling_location\":\"\",\"container_damage\":\"No\",\"temperature_condition\":\"Chilled\",\"temperature_value\":\"4.00\",\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":51,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"Aerobic Plate Count\",\"parameter_label\":\"Aerobic Plate Count (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":1,\"result_value\":\"4\",\"has_espc\":1,\"result_display\":\"4\",\"formatted\":\"4 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":52,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":5,\"result_value\":\"1800+\",\"has_espc\":0,\"result_display\":\"1800+\",\"formatted\":\"1800+\"}}]}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for Aerobic Plate Count (at 22°C) and Faecal Coliforms.\",\"sample_details\":{\"descriptions\":[\"Water sample (~ 100 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Water\",\"code\":null,\"location\":\"Water\"}],\"has_any_codes\":false,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-03-07 08:01:27', 0, NULL, NULL, 0, '2026-03-07 08:01:27'),
(57, 12, '26/012', 'accredited', '', 1, 2, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":12,\"sample_code\":\"26\\/012\\/001\",\"form_number\":\"26\\/012\\/001\",\"report_ref\":\"26\\/012\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-06\",\"received_time\":\"12:26:00\",\"tentative_date\":\"2026-03-16\",\"sample_collected_date\":\"2026-03-05\",\"sample_collected_time\":\"22:26:00\",\"analysis_start_date\":\"2026-03-06\",\"analysis_end_date\":\"2026-03-14\",\"is_drawn_by_nara\":0,\"client_name\":\"Fishery Products Pvt Ltd\",\"client_address\":\"234\\/A, Export Zone\",\"client_phone\":\"0382345047\",\"city_name\":\"Panadura\"},\"items\":[{\"sample_item_id\":15,\"sample_name\":\"Waste Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"WW-001\",\"sampling_location\":\"Waste Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":57,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"Aerobic Plate Count\",\"parameter_label\":\"Aerobic Plate Count (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":22,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":58,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"Aerobic Plate Count\",\"parameter_label\":\"Aerobic Plate Count (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":23,\"result_value\":\"9.2 x 10^3\",\"has_espc\":1,\"result_display\":\"9.2 x 10<sup>3<\\/sup>\",\"formatted\":\"9.2 x 10<sup>3<\\/sup> <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}}]}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for Aerobic Plate Count (at 22°C and at 30°C).\",\"sample_details\":{\"descriptions\":[\"Waste water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Waste Water\",\"code\":\"WW-001\",\"location\":\"Waste Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-03-07 10:24:04', 0, NULL, NULL, 0, '2026-03-07 10:24:04'),
(58, 7, '26/007', 'accredited', '', 1, 2, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":7,\"sample_code\":\"26\\/007\\/002\",\"form_number\":\"26\\/007\\/002\",\"report_ref\":\"26\\/007\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-01-29\",\"received_time\":\"09:00:00\",\"tentative_date\":\"2026-02-13\",\"sample_collected_date\":null,\"sample_collected_time\":null,\"analysis_start_date\":\"2026-03-06\",\"analysis_end_date\":\"2026-03-06\",\"is_drawn_by_nara\":0,\"client_name\":\"OLS Food (Pvt) Ltd\",\"client_address\":\"No:290 Thoduwawa North\",\"client_phone\":\"0769736972\",\"city_name\":null},\"items\":[{\"sample_item_id\":9,\"sample_name\":\"Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"\",\"sampling_location\":\"\",\"container_damage\":\"No\",\"temperature_condition\":\"Chilled\",\"temperature_value\":null,\"container_item_id\":null,\"container_name\":null,\"sequence_number\":1,\"sample_category_id\":null,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":35,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"Aerobic Plate Count\",\"parameter_label\":\"Aerobic Plate Count (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":24,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":36,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"Aerobic Plate Count\",\"parameter_label\":\"Aerobic Plate Count (cfu\\/mL) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":25,\"result_value\":\"4\",\"has_espc\":1,\"result_display\":\"4\",\"formatted\":\"4 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":37,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":26,\"result_value\":\"1800+\",\"has_espc\":0,\"result_display\":\"1800+\",\"formatted\":\"1800+\"}}]},{\"sample_item_id\":10,\"sample_name\":\"Ice\",\"sample_value\":\"500\",\"sample_unit\":\"g\",\"client_sample_code\":\"\",\"sampling_location\":\"\",\"container_damage\":\"No\",\"temperature_condition\":\"Frozen\",\"temperature_value\":null,\"container_item_id\":null,\"container_name\":null,\"sequence_number\":2,\"sample_category_id\":null,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":38,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"Aerobic Plate Count\",\"parameter_label\":\"Aerobic Plate Count (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":27,\"result_value\":\"10 x 10^8\",\"has_espc\":1,\"result_display\":\"10 x 10<sup>8<\\/sup>\",\"formatted\":\"10 x 10<sup>8<\\/sup> <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":39,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"Aerobic Plate Count\",\"parameter_label\":\"Aerobic Plate Count (cfu\\/mL) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":28,\"result_value\":\"5\",\"has_espc\":0,\"result_display\":\"5\",\"formatted\":\"5\"}},{\"sample_test_id\":40,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":29,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}}]}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for Aerobic Plate Count (at 22°C and at 37°C) and Faecal Coliforms.\",\"sample_details\":{\"descriptions\":[\"Water sample (~ 500 mL) in a container.\",\"Ice sample (~ 500 g) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Water\",\"code\":null,\"location\":\"Water\"},{\"index\":2,\"name\":\"Ice\",\"code\":null,\"location\":\"Ice\"}],\"has_any_codes\":false,\"is_multiple\":true,\"is_swab\":false}}', 1, '2026-03-07 10:24:20', 0, NULL, NULL, 0, '2026-03-07 10:24:20'),
(80, 14, '26/014', 'non_accredited', 'single', 1, 2, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":14,\"sample_code\":\"26\\/014\\/001\",\"form_number\":\"26\\/014\\/001\",\"report_ref\":\"26\\/014\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-08\",\"received_time\":\"14:39:00\",\"tentative_date\":\"2026-03-18\",\"sample_collected_date\":\"2026-03-08\",\"sample_collected_time\":\"09:41:00\",\"analysis_start_date\":\"2026-03-08\",\"analysis_end_date\":\"2026-03-08\",\"is_drawn_by_nara\":0,\"client_name\":\"Deep Sea Fisheries Ltd\",\"client_address\":\"No 67, Harbour View\",\"client_phone\":\"0262345016\",\"city_name\":\"Trincomalee\"},\"items\":[{\"sample_item_id\":23,\"sample_name\":\"Fruit Juice\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"FJ-001\",\"sampling_location\":\"Bottle\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":0,\"tests\":[{\"sample_test_id\":88,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":66,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":89,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":67,\"result_value\":\"10x10^5\",\"has_espc\":0,\"result_display\":\"10x10<sup>5<\\/sup>\",\"formatted\":\"10x10<sup>5<\\/sup>\"}},{\"sample_test_id\":90,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":68,\"result_value\":\"5\",\"has_espc\":0,\"result_display\":\"5\",\"formatted\":\"5\"}},{\"sample_test_id\":92,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":70,\"result_value\":\"500+\",\"has_espc\":0,\"result_display\":\"500+\",\"formatted\":\"500+\"}},{\"sample_test_id\":91,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":69,\"result_value\":\"4500\",\"has_espc\":0,\"result_display\":\"4500\",\"formatted\":\"4500\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C and at 30°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Fruit juice sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Fruit Juice\",\"code\":\"FJ-001\",\"location\":\"Fruit Juice\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"non_accredited\",\"logos\":[{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 22°C and at 30°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"sample_details\":{\"descriptions\":[\"Fruit juice sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Fruit Juice\",\"code\":\"FJ-001\",\"location\":\"Fruit Juice\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-03-07 20:21:36', 0, NULL, NULL, 0, '2026-03-07 20:21:36'),
(124, 11, '26/011/I', 'accredited', 'single', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":11,\"sample_code\":\"QC\\/26\\/011\\/001\",\"form_number\":\"26\\/011\\/001\",\"report_ref\":\"26\\/011\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-03\",\"received_time\":\"12:21:00\",\"tentative_date\":\"2026-03-13\",\"sample_collected_date\":\"2026-03-02\",\"sample_collected_time\":\"22:22:00\",\"analysis_start_date\":\"2026-03-03\",\"analysis_end_date\":\"2026-03-15\",\"is_drawn_by_nara\":0,\"client_name\":\"Aqua Care Laboratory\",\"client_address\":\"789\\/C, Hospital Road\",\"client_phone\":\"0382345058\",\"city_name\":\"Panadura\"},\"items\":[{\"sample_item_id\":14,\"sample_name\":\"Waste Water\",\"sample_value\":\"100\",\"sample_unit\":\"mL\",\"client_sample_code\":\"\",\"sampling_location\":\"\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":null,\"container_name\":null,\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":56,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":82,\"result_value\":\"10\",\"has_espc\":0,\"result_display\":\"10\",\"formatted\":\"10\"}}],\"isolated_customer_request\":\"To test samples for APC (at 37°C).\",\"isolated_sample_details\":{\"descriptions\":[\"Waste water sample (~ 100 mL) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Waste Water\",\"code\":null,\"location\":\"Waste Water\"}],\"has_any_codes\":false,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 37°C).\",\"sample_details\":{\"descriptions\":[\"Waste water sample (~ 100 mL) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Waste Water\",\"code\":null,\"location\":\"Waste Water\"}],\"has_any_codes\":false,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-03-15 06:37:36', 0, NULL, NULL, 0, '2026-03-15 06:37:36'),
(126, 16, '26/016/I', 'accredited', 'single', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":16,\"sample_code\":\"26\\/016\\/001\",\"form_number\":\"26\\/016\\/001\",\"report_ref\":\"26\\/016\",\"status\":\"Completed\",\"submission_type\":\"swab\",\"received_date\":\"2026-03-08\",\"received_time\":\"15:14:00\",\"tentative_date\":\"2026-03-18\",\"sample_collected_date\":\"2026-03-08\",\"sample_collected_time\":\"10:14:00\",\"analysis_start_date\":\"2026-03-08\",\"analysis_end_date\":\"2026-03-16\",\"is_drawn_by_nara\":0,\"client_name\":\"KNJ Lab\",\"client_address\":\"Kalutara\",\"client_phone\":\"0763740019\",\"city_name\":\"Kalutara\"},\"items\":[{\"sample_item_id\":26,\"sample_name\":\"Surface Swab\",\"sample_value\":\"50\",\"sample_unit\":\"cm²\",\"client_sample_code\":\"SS-001\",\"sampling_location\":\"Office Lab\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":null,\"container_name\":null,\"sequence_number\":1,\"sample_category_id\":3,\"category_name\":\"Surface Swab\",\"category_code\":\"SWB\",\"base_category_id\":3,\"base_category_name\":\"Surface Swab\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":99,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/cm<sup>2<\\/sup>) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/cm^2\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":1,\"result\":{\"result_id\":77,\"result_value\":\"5\",\"has_espc\":1,\"result_display\":\"5\",\"formatted\":\"5 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":100,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/cm<sup>2<\\/sup>) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/cm^2\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":1,\"result\":{\"result_id\":78,\"result_value\":\"10x10^7\",\"has_espc\":1,\"result_display\":\"10x10<sup>7<\\/sup>\",\"formatted\":\"10x10<sup>7<\\/sup> <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":101,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/cm<sup>2<\\/sup>)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/cm^2\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":1,\"result\":{\"result_id\":79,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":103,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/cm<sup>2<\\/sup>)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/cm^2\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":1,\"result\":{\"result_id\":81,\"result_value\":\"5\",\"has_espc\":0,\"result_display\":\"5\",\"formatted\":\"5\"}},{\"sample_test_id\":102,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/cm<sup>2<\\/sup>)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/cm^2\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":1,\"result\":{\"result_id\":80,\"result_value\":\"1500+\",\"has_espc\":0,\"result_display\":\"1500+\",\"formatted\":\"1500+\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C and at 30°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Swab sample from a office lab.\"],\"codes_table\":[{\"index\":1,\"name\":\"Surface Swab\",\"code\":\"SS-001\",\"location\":\"Surface Swab\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":true}}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 22°C and at 30°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"sample_details\":{\"descriptions\":[\"Swab sample from a office lab.\"],\"codes_table\":[{\"index\":1,\"name\":\"Surface Swab\",\"code\":\"SS-001\",\"location\":\"Surface Swab\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":true}}', 1, '2026-03-15 06:53:28', 0, NULL, NULL, 0, '2026-03-15 06:53:28'),
(129, 10, '26/010/I', 'accredited', 'single', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":10,\"sample_code\":\"QC\\/26\\/010\\/001\",\"form_number\":\"26\\/010\\/001\",\"report_ref\":\"26\\/010\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-03\",\"received_time\":\"02:05:00\",\"tentative_date\":\"2026-03-13\",\"sample_collected_date\":\"2026-03-02\",\"sample_collected_time\":\"02:05:00\",\"analysis_start_date\":\"2026-03-03\",\"analysis_end_date\":\"2026-03-15\",\"is_drawn_by_nara\":0,\"client_name\":\"Ocean Harvest Trading Co\",\"client_address\":\"456\\/A, Coastal Strip\",\"client_phone\":\"0262345033\",\"city_name\":\"Trincomalee\"},\"items\":[{\"sample_item_id\":13,\"sample_name\":\"Water\",\"sample_value\":\"100\",\"sample_unit\":\"mL\",\"client_sample_code\":\"W-001\",\"sampling_location\":\"tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Chilled\",\"temperature_value\":\"3.50\",\"container_item_id\":null,\"container_name\":null,\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":53,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":85,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":54,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":86,\"result_value\":\"10\",\"has_espc\":1,\"result_display\":\"10\",\"formatted\":\"10 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":55,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":87,\"result_value\":\"10 x 10^6\",\"has_espc\":0,\"result_display\":\"10 x 10<sup>6<\\/sup>\",\"formatted\":\"10 x 10<sup>6<\\/sup>\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C and at 30°C) and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Water sample (~ 100 mL) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Water\",\"code\":\"W-001\",\"location\":\"Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 22°C and at 30°C) and Faecal Coliforms.\",\"sample_details\":{\"descriptions\":[\"Water sample (~ 100 mL) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Water\",\"code\":\"W-001\",\"location\":\"Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-03-15 14:35:06', 0, NULL, NULL, 0, '2026-03-15 14:35:06');
INSERT INTO `final_test_reports` (`report_id`, `sample_id`, `report_number`, `report_type`, `layout_type`, `signatory_left_id`, `signatory_right_id`, `signatory_snapshot`, `report_data_snapshot`, `generated_by`, `generated_at`, `print_count`, `last_printed_at`, `notes`, `is_deleted`, `created_at`) VALUES
(132, 13, '26/013', 'accredited', '', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":13,\"sample_code\":\"QC\\/26\\/013\\/007\",\"form_number\":\"26\\/013\\/007\",\"report_ref\":\"26\\/013\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-07\",\"received_time\":\"15:05:00\",\"tentative_date\":\"2026-03-17\",\"sample_collected_date\":\"2026-03-06\",\"sample_collected_time\":\"11:06:00\",\"analysis_start_date\":\"2026-03-07\",\"analysis_end_date\":\"2026-03-12\",\"is_drawn_by_nara\":0,\"client_name\":\"Deep Blue Testing Services\",\"client_address\":\"789 Laboratory Building\",\"client_phone\":\"0112345046\",\"city_name\":\"Colombo\"},\"items\":[{\"sample_item_id\":16,\"sample_name\":\"Drinking Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"DW-01\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":59,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":37,\"result_value\":\"10\",\"has_espc\":1,\"result_display\":\"10\",\"formatted\":\"10 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":60,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":38,\"result_value\":\"10 x 10^5\",\"has_espc\":1,\"result_display\":\"10 x 10<sup>5<\\/sup>\",\"formatted\":\"10 x 10<sup>5<\\/sup> <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":61,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":39,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C and at 30°C) and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Drinking water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Drinking Water\",\"code\":\"DW-01\",\"location\":\"Drinking Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}},{\"sample_item_id\":20,\"sample_name\":\"Drinking Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"DW-002\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":5,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":71,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":49,\"result_value\":\"15\",\"has_espc\":0,\"result_display\":\"15\",\"formatted\":\"15\"}},{\"sample_test_id\":72,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":50,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":73,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":51,\"result_value\":\"10 x 10^6\",\"has_espc\":1,\"result_display\":\"10 x 10<sup>6<\\/sup>\",\"formatted\":\"10 x 10<sup>6<\\/sup> <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":74,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":52,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":76,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":54,\"result_value\":\"500+\",\"has_espc\":0,\"result_display\":\"500+\",\"formatted\":\"500+\"}},{\"sample_test_id\":75,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":53,\"result_value\":\"4500\",\"has_espc\":0,\"result_display\":\"4500\",\"formatted\":\"4500\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C, at 30°C and at 37°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Drinking water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Drinking Water\",\"code\":\"DW-002\",\"location\":\"Drinking Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}},{\"sample_item_id\":21,\"sample_name\":\"Drinking Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"Dw-003\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":6,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":77,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":55,\"result_value\":\"10\",\"has_espc\":1,\"result_display\":\"10\",\"formatted\":\"10 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":78,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":56,\"result_value\":\"<1\",\"has_espc\":0,\"result_display\":\"<1\",\"formatted\":\"<1\"}},{\"sample_test_id\":80,\"parameter_id\":12,\"parameter_code\":\"L\",\"parameter_name\":\"Faecal Streptococci\",\"parameter_label\":\"Faecal Streptococci (MPN\\/mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 516 Part 4: 1982\",\"unit_name\":\"MPN\\/mL\",\"is_accredited\":0,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":58,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":79,\"parameter_id\":11,\"parameter_code\":\"K\",\"parameter_name\":\"Staphylococcus aureus\",\"parameter_label\":\"Staphylococcus aureus (cfu\\/mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"ISO 6888-1:2021(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":0,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":57,\"result_value\":\"5\",\"has_espc\":0,\"result_display\":\"5\",\"formatted\":\"5\"}}],\"isolated_customer_request\":\"To test samples for APC (at 37°C), Escherichia coli, Faecal Streptococci and Staphylococcus aureus.\",\"isolated_sample_details\":{\"descriptions\":[\"Drinking water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Drinking Water\",\"code\":\"Dw-003\",\"location\":\"Drinking Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}},{\"sample_item_id\":18,\"sample_name\":\"Potable water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"PW-001\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":3,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":64,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":42,\"result_value\":\"9.2 x 10^6\",\"has_espc\":0,\"result_display\":\"9.2 x 10<sup>6<\\/sup>\",\"formatted\":\"9.2 x 10<sup>6<\\/sup>\"}},{\"sample_test_id\":65,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":43,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":66,\"parameter_id\":8,\"parameter_code\":\"H\",\"parameter_name\":\"Vibrio cholerae\",\"parameter_label\":\"Vibrio cholerae (\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"ISO\\/TS 21872-1:2017(E)\",\"unit_name\":\"\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"present_or_absent\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":44,\"result_value\":\"Absent\",\"has_espc\":0,\"result_display\":\"Absent\",\"formatted\":\"Absent\"}}],\"isolated_customer_request\":\"To test samples for Coliforms, Faecal Coliforms and Vibrio cholerae.\",\"isolated_sample_details\":{\"descriptions\":[\"Potable water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Potable water\",\"code\":\"PW-001\",\"location\":\"Potable water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}},{\"sample_item_id\":17,\"sample_name\":\"Waste Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"WW-001\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":2,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":62,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":40,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":63,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":41,\"result_value\":\"5\",\"has_espc\":0,\"result_display\":\"5\",\"formatted\":\"5\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C and at 30°C).\",\"isolated_sample_details\":{\"descriptions\":[\"Waste water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Waste Water\",\"code\":\"WW-001\",\"location\":\"Waste Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}},{\"sample_item_id\":22,\"sample_name\":\"Waste Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"WW-002\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":7,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":81,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":59,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":82,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":60,\"result_value\":\"10 x 10^7\",\"has_espc\":1,\"result_display\":\"10 x 10<sup>7<\\/sup>\",\"formatted\":\"10 x 10<sup>7<\\/sup> <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":83,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":61,\"result_value\":\"5\",\"has_espc\":1,\"result_display\":\"5\",\"formatted\":\"5 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}},{\"sample_test_id\":84,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":62,\"result_value\":\"1500\",\"has_espc\":0,\"result_display\":\"1500\",\"formatted\":\"1500\"}},{\"sample_test_id\":86,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":64,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":85,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":63,\"result_value\":\"<1\",\"has_espc\":0,\"result_display\":\"<1\",\"formatted\":\"<1\"}},{\"sample_test_id\":87,\"parameter_id\":14,\"parameter_code\":\"N\",\"parameter_name\":\"Vibrio spp.\",\"parameter_label\":\"Vibrio spp. (\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"APHA:2001\",\"unit_name\":\"\\/100 mL\",\"is_accredited\":0,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":65,\"result_value\":\"50\",\"has_espc\":0,\"result_display\":\"50\",\"formatted\":\"50\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C, at 30°C and at 37°C), Coliforms, Escherichia coli, Faecal Coliforms and Vibrio spp..\",\"isolated_sample_details\":{\"descriptions\":[\"Waste water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Waste Water\",\"code\":\"WW-002\",\"location\":\"Waste Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}},{\"sample_item_id\":19,\"sample_name\":\"Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"WW-001\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":4,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":67,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":45,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":68,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":46,\"result_value\":\"5\",\"has_espc\":0,\"result_display\":\"5\",\"formatted\":\"5\"}},{\"sample_test_id\":70,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":48,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":69,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":47,\"result_value\":\"1800+\",\"has_espc\":0,\"result_display\":\"1800+\",\"formatted\":\"1800+\"}}],\"isolated_customer_request\":\"To test samples for APC (at 30°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Water\",\"code\":\"WW-001\",\"location\":\"Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 22°C, at 30°C and at 37°C), Faecal Coliforms, Coliforms, Escherichia coli, Faecal Streptococci, Staphylococcus aureus, Vibrio cholerae and Vibrio spp..\",\"sample_details\":{\"descriptions\":[\"Three drinking water samples (~ 500 mL) in water bottles.\",\"Potable water sample (~ 500 mL) in a water bottle.\",\"Two waste water samples (~ 500 mL) in water bottles.\",\"Water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Drinking Water\",\"code\":\"DW-01\",\"location\":\"Drinking Water\"},{\"index\":2,\"name\":\"Drinking Water\",\"code\":\"DW-002\",\"location\":\"Drinking Water\"},{\"index\":3,\"name\":\"Drinking Water\",\"code\":\"Dw-003\",\"location\":\"Drinking Water\"},{\"index\":4,\"name\":\"Potable water\",\"code\":\"PW-001\",\"location\":\"Potable water\"},{\"index\":5,\"name\":\"Waste Water\",\"code\":\"WW-001\",\"location\":\"Waste Water\"},{\"index\":6,\"name\":\"Waste Water\",\"code\":\"WW-002\",\"location\":\"Waste Water\"},{\"index\":7,\"name\":\"Water\",\"code\":\"WW-001\",\"location\":\"Water\"}],\"has_any_codes\":true,\"is_multiple\":true,\"is_swab\":false}}', 1, '2026-03-17 15:48:39', 0, NULL, NULL, 0, '2026-03-17 15:48:39'),
(140, 15, '26/015-A', 'accredited', '', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":15,\"sample_code\":\"QC\\/26\\/015\\/002\",\"form_number\":\"26\\/015\\/002\",\"report_ref\":\"26\\/015\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-08\",\"received_time\":\"01:51:00\",\"tentative_date\":\"2026-03-18\",\"sample_collected_date\":\"2026-03-07\",\"sample_collected_time\":\"07:52:00\",\"analysis_start_date\":\"2026-03-08\",\"analysis_end_date\":\"2026-03-08\",\"is_drawn_by_nara\":0,\"client_name\":\"Aqua Care Laboratory\",\"client_address\":\"789\\/C, Hospital Road\",\"client_phone\":\"0382345058\",\"city_name\":\"Panadura\"},\"items\":[{\"sample_item_id\":24,\"sample_name\":\"Water\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"TW-001\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":93,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":71,\"result_value\":\"10x10^6\",\"has_espc\":0,\"result_display\":\"10x10<sup>6<\\/sup>\",\"formatted\":\"10x10<sup>6<\\/sup>\"}},{\"sample_test_id\":94,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":72,\"result_value\":\"5\",\"has_espc\":1,\"result_display\":\"5\",\"formatted\":\"5 <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C and at 30°C).\",\"isolated_sample_details\":{\"descriptions\":[\"Water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Water\",\"code\":\"TW-001\",\"location\":\"Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 22°C and at 30°C).\",\"sample_details\":{\"descriptions\":[\"Water sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Water\",\"code\":\"TW-001\",\"location\":\"Water\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-03-17 18:24:51', 0, NULL, NULL, 0, '2026-03-17 18:24:51'),
(141, 15, '26/015-NA', 'non_accredited', '', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":15,\"sample_code\":\"QC\\/26\\/015\\/002\",\"form_number\":\"26\\/015\\/002\",\"report_ref\":\"26\\/015\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-08\",\"received_time\":\"01:51:00\",\"tentative_date\":\"2026-03-18\",\"sample_collected_date\":\"2026-03-07\",\"sample_collected_time\":\"07:52:00\",\"analysis_start_date\":\"2026-03-08\",\"analysis_end_date\":\"2026-03-08\",\"is_drawn_by_nara\":0,\"client_name\":\"Aqua Care Laboratory\",\"client_address\":\"789\\/C, Hospital Road\",\"client_phone\":\"0382345058\",\"city_name\":\"Panadura\"},\"items\":[{\"sample_item_id\":25,\"sample_name\":\"Fruit Juice\",\"sample_value\":\"500\",\"sample_unit\":\"mL\",\"client_sample_code\":\"FJ-001\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":1,\"container_name\":\"Water Bottle\",\"sequence_number\":2,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":0,\"tests\":[{\"sample_test_id\":95,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":73,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":96,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":74,\"result_value\":\"1500+\",\"has_espc\":0,\"result_display\":\"1500+\",\"formatted\":\"1500+\"}},{\"sample_test_id\":98,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":76,\"result_value\":\"5\",\"has_espc\":0,\"result_display\":\"5\",\"formatted\":\"5\"}},{\"sample_test_id\":97,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/100 mL)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":75,\"result_value\":\"40\",\"has_espc\":0,\"result_display\":\"40\",\"formatted\":\"40\"}}],\"isolated_customer_request\":\"To test samples for APC (at 37°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Fruit juice sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Fruit Juice\",\"code\":\"FJ-001\",\"location\":\"Fruit Juice\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"non_accredited\",\"logos\":[{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 37°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"sample_details\":{\"descriptions\":[\"Fruit juice sample (~ 500 mL) in a water bottle.\"],\"codes_table\":[{\"index\":1,\"name\":\"Fruit Juice\",\"code\":\"FJ-001\",\"location\":\"Fruit Juice\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-03-17 18:24:51', 0, NULL, NULL, 0, '2026-03-17 18:24:51'),
(142, 5, '26/005', 'accredited', '', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":5,\"sample_code\":\"QC\\/26\\/005\\/02\",\"form_number\":\"26\\/005\\/02\",\"report_ref\":\"26\\/005\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-02-03\",\"received_time\":\"09:00:00\",\"tentative_date\":\"2026-02-10\",\"sample_collected_date\":null,\"sample_collected_time\":null,\"analysis_start_date\":\"2026-02-03\",\"analysis_end_date\":\"2026-02-10\",\"is_drawn_by_nara\":0,\"client_name\":\"Marine Catch Exports\",\"client_address\":\"890\\/A, Port Access\",\"client_phone\":\"0312345061\",\"city_name\":\"Negombo\"},\"items\":[{\"sample_item_id\":7,\"sample_name\":\"Fish\",\"sample_value\":\"400\",\"sample_unit\":\"g\",\"client_sample_code\":\"TW-002\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":null,\"container_name\":null,\"sequence_number\":2,\"sample_category_id\":null,\"category_name\":\"Fish and Shellfish\",\"category_code\":\"FSH\",\"base_category_id\":2,\"base_category_name\":\"Food\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":25,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/g) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/g\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":33,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":26,\"parameter_id\":2,\"parameter_code\":\"B\",\"parameter_name\":\"Coliforms\",\"parameter_label\":\"Coliforms (MPN\\/g)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"ISO 4831-1:2006(E)\",\"unit_name\":\"MPN\\/g\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":34,\"result_value\":\"100\",\"has_espc\":0,\"result_display\":\"100\",\"formatted\":\"100\"}},{\"sample_test_id\":28,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/g)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"ISO 7251:2005(E)\",\"unit_name\":\"MPN\\/g\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":36,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":27,\"parameter_id\":3,\"parameter_code\":\"C\",\"parameter_name\":\"Faecal Coliforms\",\"parameter_label\":\"Faecal Coliforms (MPN\\/g)\",\"display_format\":\"normal\",\"variant_name\":\"\",\"method_name\":\"APHA: 2015\",\"unit_name\":\"MPN\\/g\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":0,\"is_swab\":0,\"result\":{\"result_id\":35,\"result_value\":\"1800+\",\"has_espc\":0,\"result_display\":\"1800+\",\"formatted\":\"1800+\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"isolated_sample_details\":{\"descriptions\":[\"Ambient Fish sample (~ 400 g) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Fish\",\"code\":\"TW-002\",\"location\":\"Fish\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}},{\"sample_item_id\":6,\"sample_name\":\"Shrimp\",\"sample_value\":\"500\",\"sample_unit\":\"g\",\"client_sample_code\":\"TW-001\",\"sampling_location\":\"Tank\",\"container_damage\":\"No\",\"temperature_condition\":\"Ambient\",\"temperature_value\":null,\"container_item_id\":null,\"container_name\":null,\"sequence_number\":1,\"sample_category_id\":null,\"category_name\":\"Fish and Shellfish\",\"category_code\":\"FSH\",\"base_category_id\":2,\"base_category_name\":\"Food\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":22,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/g) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/g\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":30,\"result_value\":\"10\",\"has_espc\":0,\"result_display\":\"10\",\"formatted\":\"10\"}},{\"sample_test_id\":23,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/g) at 30°C\",\"display_format\":\"normal\",\"variant_name\":\"at 30°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/g\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":31,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}},{\"sample_test_id\":24,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/g) at 37°C\",\"display_format\":\"normal\",\"variant_name\":\"at 37°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/g\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":32,\"result_value\":\"10 x 10^8\",\"has_espc\":1,\"result_display\":\"10 x 10<sup>8<\\/sup>\",\"formatted\":\"10 x 10<sup>8<\\/sup> <sup class=\\\"espc-sup\\\">ESPC<\\/sup>\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C, at 30°C and at 37°C).\",\"isolated_sample_details\":{\"descriptions\":[\"Ambient Shrimp sample (~ 500 g) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Shrimp\",\"code\":\"TW-001\",\"location\":\"Shrimp\"}],\"has_any_codes\":true,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 22°C, at 30°C and at 37°C), Coliforms, Escherichia coli and Faecal Coliforms.\",\"sample_details\":{\"descriptions\":[\"Ambient Fish sample (~ 400 g) in a container.\",\"Ambient Shrimp sample (~ 500 g) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Fish\",\"code\":\"TW-002\",\"location\":\"Fish\"},{\"index\":2,\"name\":\"Shrimp\",\"code\":\"TW-001\",\"location\":\"Shrimp\"}],\"has_any_codes\":true,\"is_multiple\":true,\"is_swab\":false}}', 1, '2026-03-17 18:31:58', 0, NULL, NULL, 0, '2026-03-17 18:31:58'),
(146, 17, '26/017/I', 'accredited', 'single', 1, 4, '{\"left\":{\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"scientist\"},\"right\":{\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\",\"role_type\":\"head\"}}', '{\"sample\":{\"sample_id\":17,\"sample_code\":\"QC\\/26\\/017\\/001\",\"form_number\":\"26\\/017\\/001\",\"report_ref\":\"26\\/017\",\"status\":\"Completed\",\"submission_type\":\"regular\",\"received_date\":\"2026-03-15\",\"received_time\":\"11:27:00\",\"tentative_date\":\"2026-03-25\",\"sample_collected_date\":\"2026-03-15\",\"sample_collected_time\":\"11:27:00\",\"analysis_start_date\":\"2026-03-15\",\"analysis_end_date\":\"2026-03-15\",\"is_drawn_by_nara\":0,\"client_name\":\"Aqua Life Laboratory\",\"client_address\":\"456 Research Center\",\"client_phone\":\"0812345015\",\"city_name\":\"Kandy\"},\"items\":[{\"sample_item_id\":27,\"sample_name\":\"Ice\",\"sample_value\":\"500\",\"sample_unit\":\"g\",\"client_sample_code\":\"\",\"sampling_location\":\"\",\"container_damage\":\"No\",\"temperature_condition\":\"Frozen\",\"temperature_value\":null,\"container_item_id\":null,\"container_name\":null,\"sequence_number\":1,\"sample_category_id\":1,\"category_name\":\"Water and Ice\",\"category_code\":\"WAT\",\"base_category_id\":1,\"base_category_name\":\"Water and Ice\",\"is_slab_accredited\":1,\"tests\":[{\"sample_test_id\":104,\"parameter_id\":1,\"parameter_code\":\"A\",\"parameter_name\":\"APC\",\"parameter_label\":\"APC (cfu\\/mL) at 22°C\",\"display_format\":\"normal\",\"variant_name\":\"at 22°C\",\"method_name\":\"ISO 4833-1:2013(E)\",\"unit_name\":\"cfu\\/mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":83,\"result_value\":\"10\",\"has_espc\":0,\"result_display\":\"10\",\"formatted\":\"10\"}},{\"sample_test_id\":105,\"parameter_id\":4,\"parameter_code\":\"D\",\"parameter_name\":\"Escherichia coli\",\"parameter_label\":\"Escherichia coli (MPN\\/100 mL)\",\"display_format\":\"scientific\",\"variant_name\":\"\",\"method_name\":\"SLS 1461 Part 1\\/Sec 3:2013\",\"unit_name\":\"MPN\\/100 mL\",\"is_accredited\":1,\"result_mode\":\"numeric_or_ND\",\"espc_applicable\":1,\"is_swab\":0,\"result\":{\"result_id\":84,\"result_value\":\"ND\",\"has_espc\":0,\"result_display\":\"ND\",\"formatted\":\"ND\"}}],\"isolated_customer_request\":\"To test samples for APC (at 22°C) and Escherichia coli.\",\"isolated_sample_details\":{\"descriptions\":[\"Ice sample (~ 500 g) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Ice\",\"code\":null,\"location\":\"Ice\"}],\"has_any_codes\":false,\"is_multiple\":false,\"is_swab\":false}}],\"report_type\":\"accredited\",\"logos\":[{\"logo_id\":1,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":4,\"logo_name\":\"Government Seal\",\"logo_type\":\"institutional\",\"file_path\":\"assets\\/images\\/govt_seal.png\",\"display_order\":1},{\"logo_id\":2,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":5,\"logo_name\":\"NARA Logo\",\"logo_type\":\"primary\",\"file_path\":\"assets\\/images\\/nara_logo.png\",\"display_order\":2},{\"logo_id\":3,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3},{\"logo_id\":6,\"logo_name\":\"SLAB Accreditation Mark\",\"logo_type\":\"accreditation\",\"file_path\":\"assets\\/images\\/slab_logo.png\",\"display_order\":3}],\"certificate\":{\"certificate_id\":1,\"certificate_number\":\"TL 010-01\",\"scope_description\":\"ISO\\/IEC 17025:2017 accreditation for microbiological testing of water, food, and surface samples\",\"valid_from\":\"2024-05-31\",\"valid_until\":\"2028-05-30\"},\"customer_request\":\"To test samples for APC (at 22°C) and Escherichia coli.\",\"sample_details\":{\"descriptions\":[\"Ice sample (~ 500 g) in a container.\"],\"codes_table\":[{\"index\":1,\"name\":\"Ice\",\"code\":null,\"location\":\"Ice\"}],\"has_any_codes\":false,\"is_multiple\":false,\"is_swab\":false}}', 1, '2026-04-29 19:48:26', 0, NULL, NULL, 0, '2026-04-29 19:48:26');

-- --------------------------------------------------------

--
-- Table structure for table `form_sequence`
--

CREATE TABLE `form_sequence` (
  `year` int(11) NOT NULL,
  `current_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_sequence`
--

INSERT INTO `form_sequence` (`year`, `current_number`) VALUES
(2026, 22);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `report_number` varchar(50) NOT NULL,
  `invoice_data_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`invoice_data_snapshot`)),
  `signatory_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `sample_id`, `invoice_number`, `report_number`, `invoice_data_snapshot`, `signatory_id`, `created_by`, `created_at`) VALUES
(1, 15, 'QC/M/26/015', 'QC/26/015/002', '{\"sample_id\":15,\"invoice_number\":\"QC\\/M\\/26\\/015\",\"report_number\":\"QC\\/26\\/015\\/002\",\"date_of_request\":\"08 - 03 - 2026\",\"customer\":{\"name\":\"Aqua Care Laboratory\",\"address\":\"789\\/C, Hospital Road\",\"city\":\"Panadura\"},\"rows\":[{\"sample_type\":\"Fruit Juice\",\"parameters\":[{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"Coliforms, E. coli*, Faecal Coliforms\",\"fee\":1375}],\"unit_price\":2625,\"quantity\":1,\"sub_total\":2625},{\"sample_type\":\"Water\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250}],\"unit_price\":2500,\"quantity\":1,\"sub_total\":2500}],\"extra_items\":[{\"name\":\"Water Bottle\",\"quantity\":2,\"unit_price\":50,\"line_total\":100}],\"footnotes\":{\"APC\":\"Aerobic Plate Count\",\"E. coli\":\"Escherichia coli\"},\"total_payable\":5225,\"total_items_count\":2,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"2\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}}', 4, 1, '2026-03-08 10:46:13'),
(2, 14, 'QC/M/26/014', 'QC/26/014/001', '{\"sample_id\":14,\"invoice_number\":\"QC\\/M\\/26\\/014\",\"report_number\":\"QC\\/26\\/014\\/001\",\"date_of_request\":\"08 - 03 - 2026\",\"customer\":{\"name\":\"Deep Sea Fisheries Ltd\",\"address\":\"No 67, Harbour View\",\"city\":\"Trincomalee\"},\"rows\":[{\"sample_type\":\"Fruit Juice\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"Coliforms, E. coli*, Faecal Coliforms\",\"fee\":1375}],\"unit_price\":3875,\"quantity\":1,\"sub_total\":3875}],\"extra_items\":[{\"name\":\"Water Bottle\",\"quantity\":2,\"unit_price\":50,\"line_total\":100}],\"footnotes\":{\"APC\":\"Aerobic Plate Count\",\"E. coli\":\"Escherichia coli\"},\"total_payable\":3975,\"total_items_count\":1,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"2\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}}', 1, 1, '2026-03-08 12:17:10'),
(3, 13, 'QC/M/26/013', 'QC/26/013/007', '{\"sample_id\":13,\"invoice_number\":\"QC\\/M\\/26\\/013\",\"report_number\":\"QC\\/26\\/013\\/007\",\"date_of_request\":\"07 - 03 - 2026\",\"customer\":{\"name\":\"Deep Blue Testing Services\",\"address\":\"789 Laboratory Building\",\"city\":\"Colombo\"},\"rows\":[{\"sample_type\":\"Drinking Water\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"Coliforms, E. coli*, Faecal Coliforms\",\"fee\":1375},{\"name\":\"E. coli*\",\"fee\":1375},{\"name\":\"Faecal Coliforms\",\"fee\":1250},{\"name\":\"F.Streptococci*\",\"fee\":0},{\"name\":\"S. aureus*\",\"fee\":2625}],\"unit_price\":4708.33,\"quantity\":3,\"sub_total\":14125},{\"sample_type\":\"Potable water\",\"parameters\":[{\"name\":\"Coliforms, Faecal Coliforms\",\"fee\":1250},{\"name\":\"Vibrio cholerae\",\"fee\":2500}],\"unit_price\":3750,\"quantity\":1,\"sub_total\":3750},{\"sample_type\":\"Waste Water\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"Coliforms, E. coli*, Faecal Coliforms\",\"fee\":1375},{\"name\":\"Vibrio spp.*\",\"fee\":2125}],\"unit_price\":4875,\"quantity\":2,\"sub_total\":9750},{\"sample_type\":\"Water\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"Coliforms, E. coli*, Faecal Coliforms\",\"fee\":1375}],\"unit_price\":2625,\"quantity\":1,\"sub_total\":2625}],\"extra_items\":[{\"name\":\"Water Bottle\",\"quantity\":2,\"unit_price\":50,\"line_total\":100}],\"footnotes\":{\"APC\":\"Aerobic Plate Count\",\"E. coli\":\"Escherichia coli\",\"F.Streptococci\":\"Faecal Streptococci\",\"S. aureus\":\"Staphylococcus aureus\",\"Vibrio spp.\":\"Vibrio spp.\"},\"total_payable\":30350,\"total_items_count\":7,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"2\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}}', 3, 1, '2026-03-08 13:25:13'),
(4, 12, 'QC/M/26/012', 'QC/26/012/001', '{\"sample_id\":12,\"invoice_number\":\"QC\\/M\\/26\\/012\",\"report_number\":\"QC\\/26\\/012\\/001\",\"date_of_request\":\"06 - 03 - 2026\",\"customer\":{\"name\":\"Fishery Products Pvt Ltd\",\"address\":\"234\\/A, Export Zone\",\"city\":\"Panadura\"},\"rows\":[{\"sample_type\":\"Waste Water\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250}],\"unit_price\":2500,\"quantity\":1,\"sub_total\":2500}],\"extra_items\":[{\"name\":\"Water Bottle\",\"quantity\":1,\"unit_price\":50,\"line_total\":50}],\"footnotes\":{\"APC\":\"Aerobic Plate Count\"},\"total_payable\":2550,\"total_items_count\":1,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"2\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}}', 1, 1, '2026-03-08 13:28:23'),
(5, 11, 'QC/M/26/011', 'QC/26/011/001', '{\"sample_id\":11,\"invoice_number\":\"QC\\/M\\/26\\/011\",\"report_number\":\"QC\\/26\\/011\\/001\",\"date_of_request\":\"03 - 03 - 2026\",\"customer\":{\"name\":\"Aqua Care Laboratory\",\"address\":\"789\\/C, Hospital Road\",\"city\":\"Panadura\"},\"rows\":[{\"sample_type\":\"Waste Water\",\"parameters\":[{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250}],\"unit_price\":1250,\"quantity\":1,\"sub_total\":1250}],\"extra_items\":[{\"name\":\"Water Bottle\",\"quantity\":2,\"unit_price\":50,\"line_total\":100}],\"footnotes\":{\"APC\":\"Aerobic Plate Count\"},\"total_payable\":1350,\"total_items_count\":3,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"2\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}}', 3, 1, '2026-03-08 13:28:33'),
(6, 10, 'QC/M/26/010', 'QC/26/010/001', '{\"sample_id\":10,\"invoice_number\":\"QC\\/M\\/26\\/010\",\"report_number\":\"QC\\/26\\/010\\/001\",\"date_of_request\":\"03 - 03 - 2026\",\"customer\":{\"name\":\"Ocean Harvest Trading Co\",\"address\":\"456\\/A, Coastal Strip\",\"city\":\"Trincomalee\"},\"rows\":[{\"sample_type\":\"Water\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"Faecal Coliforms\",\"fee\":1250}],\"unit_price\":3750,\"quantity\":1,\"sub_total\":3750}],\"extra_items\":[{\"name\":\"Water Bottle\",\"quantity\":2,\"unit_price\":50,\"line_total\":100}],\"footnotes\":{\"APC\":\"Aerobic Plate Count\"},\"total_payable\":3850,\"total_items_count\":1,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"2\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}}', 3, 1, '2026-03-08 13:28:41'),
(7, 16, 'QC/M/26/016', 'QC/26/016/001', '{\"sample_id\":16,\"invoice_number\":\"QC\\/M\\/26\\/016\",\"report_number\":\"QC\\/26\\/016\\/001\",\"date_of_request\":\"08 - 03 - 2026\",\"customer\":{\"name\":\"KNJ Lab\",\"address\":\"Kalutara\",\"city\":\"Kalutara\"},\"rows\":[{\"sample_type\":\"Swab sample\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1625},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1625},{\"name\":\"Coliforms\",\"fee\":1500},{\"name\":\"E. coli*\",\"fee\":1750},{\"name\":\"Faecal Coliforms\",\"fee\":1625}],\"unit_price\":8125,\"quantity\":1,\"sub_total\":8125}],\"extra_items\":[],\"footnotes\":{\"APC\":\"Aerobic Plate Count\",\"E. coli\":\"Escherichia coli\"},\"total_payable\":8125,\"total_items_count\":1,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"2\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Head\\/Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"3\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior Scientist\",\"division\":\"Post Harvest Technology Division\"}}', 3, 1, '2026-03-08 15:15:30'),
(8, 19, 'QC/M/26/019', 'QC/26/019/015', '{\"sample_id\":19,\"invoice_number\":\"QC\\/M\\/26\\/019\",\"report_number\":\"QC\\/26\\/019\\/015\",\"date_of_request\":\"18 - 03 - 2026\",\"customer\":{\"name\":\"Ocean Wave Processing\",\"address\":\"234 Industrial Hub\",\"city\":\"Galle\"},\"rows\":[{\"sample_type\":\"Filtered Water\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"E. coli*\",\"fee\":1375}],\"unit_price\":3187.5,\"quantity\":2,\"sub_total\":6375},{\"sample_type\":\"Ice\",\"parameters\":[{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"E. coli*, Faecal Coliforms\",\"fee\":1375},{\"name\":\"Vibrio cholerae\",\"fee\":2500}],\"unit_price\":2125,\"quantity\":3,\"sub_total\":6375},{\"sample_type\":\"Ice Cubes\",\"parameters\":[{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"E. coli*, Faecal Coliforms\",\"fee\":1375}],\"unit_price\":2562.5,\"quantity\":2,\"sub_total\":5125},{\"sample_type\":\"Potable water\",\"parameters\":[{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"Coliforms\",\"fee\":1125}],\"unit_price\":3625,\"quantity\":2,\"sub_total\":7250},{\"sample_type\":\"Treated Water\",\"parameters\":[{\"name\":\"APC* at 37\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250}],\"unit_price\":2500,\"quantity\":1,\"sub_total\":2500},{\"sample_type\":\"Waste Water\",\"parameters\":[{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"APC* at 30\\u00b0C\",\"fee\":1250},{\"name\":\"E. coli*\",\"fee\":1375}],\"unit_price\":2562.5,\"quantity\":2,\"sub_total\":5125},{\"sample_type\":\"Water\",\"parameters\":[{\"name\":\"APC* at 22\\u00b0C\",\"fee\":1250},{\"name\":\"Coliforms, E. coli*, Faecal Coliforms\",\"fee\":1375},{\"name\":\"Coliforms\",\"fee\":1125},{\"name\":\"E. coli*, Faecal Coliforms\",\"fee\":1375}],\"unit_price\":2125,\"quantity\":3,\"sub_total\":6375}],\"extra_items\":[{\"name\":\"Sterile Bag\",\"quantity\":5,\"unit_price\":60,\"line_total\":300},{\"name\":\"Water Bottle\",\"quantity\":10,\"unit_price\":50,\"line_total\":500}],\"footnotes\":{\"APC\":\"Aerobic Plate Count\",\"E. coli\":\"Escherichia coli\"},\"total_payable\":39925,\"total_items_count\":15,\"signatories\":[{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\"},{\"signatory_id\":\"4\",\"full_name\":\"Suseema Ariyarathna\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\"}],\"selected_signatory\":{\"signatory_id\":\"1\",\"full_name\":\"P. Ginigaddarage\",\"title\":\"Senior scientist\",\"division\":\"Post Harvest Technology Division\"}}', 1, 1, '2026-04-29 13:01:53');

-- --------------------------------------------------------

--
-- Table structure for table `parameter_base_unit_config`
--

CREATE TABLE `parameter_base_unit_config` (
  `config_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL COMMENT 'FK: test_parameters',
  `base_category_id` int(11) NOT NULL COMMENT 'FK: base_unit_categories',
  `base_unit_id` int(11) NOT NULL COMMENT 'FK: base_units (selected unit for this combo)',
  `is_slab_accredited` tinyint(1) NOT NULL DEFAULT 0,
  `certificate_id` int(11) DEFAULT NULL,
  `detection_limit` varchar(100) DEFAULT NULL COMMENT '>0 MPN/100mL, >0.3 MPN/g, etc.',
  `sample_volume_requirement` varchar(50) DEFAULT NULL COMMENT '100mL, 25g, 25cm?, etc.',
  `temperature_options` varchar(100) DEFAULT NULL COMMENT 'For APC: 22?C, 30?C, 37?C',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Parameter configuration per base unit category (core mapping table)';

--
-- Dumping data for table `parameter_base_unit_config`
--

INSERT INTO `parameter_base_unit_config` (`config_id`, `parameter_id`, `base_category_id`, `base_unit_id`, `is_slab_accredited`, `certificate_id`, `detection_limit`, `sample_volume_requirement`, `temperature_options`, `is_active`, `created_at`, `updated_at`) VALUES
(226, 1, 1, 1, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:27:36'),
(227, 1, 2, 12, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:27:36'),
(228, 1, 3, 25, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:27:36'),
(229, 2, 1, 2, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(230, 2, 2, 14, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(231, 2, 3, 27, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(232, 3, 1, 2, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:21:09'),
(233, 3, 2, 14, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:21:09'),
(234, 3, 3, 27, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:21:09'),
(235, 4, 1, 2, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:20:58'),
(236, 4, 2, 14, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:20:58'),
(237, 4, 3, 27, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:20:58'),
(238, 8, 1, 5, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-23 16:15:06'),
(239, 8, 2, 17, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-23 16:15:06'),
(240, 8, 3, 31, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-23 16:15:06'),
(241, 9, 1, 5, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-01 17:15:42'),
(242, 9, 2, 17, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(243, 9, 3, 31, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(244, 10, 1, 6, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(245, 10, 2, 17, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(246, 10, 3, 31, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-06 19:25:43'),
(247, 11, 1, 1, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:30:40'),
(248, 11, 2, 12, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:30:40'),
(249, 11, 3, 25, 1, 1, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:30:40'),
(250, 12, 1, 3, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', NULL),
(251, 12, 2, 14, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', NULL),
(252, 12, 3, 27, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', NULL),
(253, 13, 1, 5, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', NULL),
(254, 13, 2, 17, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', NULL),
(255, 13, 3, 31, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', NULL),
(256, 14, 1, 5, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:31:11'),
(257, 14, 2, 12, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:31:11'),
(258, 14, 3, 31, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:31:11'),
(259, 15, 1, 2, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:36:42'),
(260, 15, 2, 14, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:36:42'),
(261, 15, 3, 27, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:36:42'),
(262, 16, 1, 1, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:36:12'),
(263, 16, 2, 12, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:36:12'),
(264, 16, 3, 25, 0, NULL, NULL, NULL, NULL, 1, '2026-03-01 14:02:18', '2026-03-20 16:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `parameter_category_methods`
--

CREATE TABLE `parameter_category_methods` (
  `pcm_id` int(11) NOT NULL,
  `config_id` int(11) NOT NULL COMMENT 'FK: parameter_base_unit_config',
  `method_id` int(11) NOT NULL COMMENT 'FK: test_methods',
  `sequence_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Methods per category config - allows different methods per sample type';

--
-- Dumping data for table `parameter_category_methods`
--

INSERT INTO `parameter_category_methods` (`pcm_id`, `config_id`, `method_id`, `sequence_order`, `is_primary`, `created_at`) VALUES
(136, 250, 9, 0, 1, '2026-03-01 14:02:18'),
(137, 251, 9, 0, 1, '2026-03-01 14:02:18'),
(138, 252, 9, 0, 1, '2026-03-01 14:02:18'),
(139, 253, 10, 0, 1, '2026-03-01 14:02:18'),
(140, 254, 10, 0, 1, '2026-03-01 14:02:18'),
(141, 255, 10, 0, 1, '2026-03-01 14:02:18'),
(151, 229, 2, 0, 1, '2026-03-01 15:19:17'),
(152, 230, 3, 0, 1, '2026-03-01 15:19:17'),
(153, 231, 2, 0, 1, '2026-03-01 15:19:17'),
(163, 241, 5, 0, 1, '2026-03-01 17:15:42'),
(164, 242, 5, 0, 1, '2026-03-01 17:15:42'),
(165, 243, 5, 0, 1, '2026-03-01 17:15:42'),
(166, 244, 7, 0, 1, '2026-03-01 17:16:06'),
(167, 245, 6, 0, 1, '2026-03-01 17:16:06'),
(168, 246, 6, 0, 1, '2026-03-01 17:16:06'),
(202, 235, 2, 0, 1, '2026-03-20 16:20:58'),
(203, 236, 4, 0, 1, '2026-03-20 16:20:58'),
(204, 237, 2, 0, 1, '2026-03-20 16:20:58'),
(205, 232, 2, 0, 1, '2026-03-20 16:21:09'),
(206, 233, 16, 0, 1, '2026-03-20 16:21:09'),
(207, 234, 2, 0, 1, '2026-03-20 16:21:09'),
(235, 226, 1, 0, 1, '2026-03-20 16:27:36'),
(236, 227, 1, 0, 1, '2026-03-20 16:27:36'),
(237, 228, 1, 0, 1, '2026-03-20 16:27:36'),
(241, 247, 8, 0, 1, '2026-03-20 16:30:40'),
(242, 248, 8, 0, 1, '2026-03-20 16:30:40'),
(243, 249, 8, 0, 1, '2026-03-20 16:30:40'),
(244, 256, 11, 0, 1, '2026-03-20 16:31:11'),
(245, 257, 11, 0, 1, '2026-03-20 16:31:11'),
(246, 258, 11, 0, 1, '2026-03-20 16:31:11'),
(253, 262, 13, 0, 1, '2026-03-20 16:36:12'),
(254, 263, 13, 0, 1, '2026-03-20 16:36:12'),
(255, 264, 13, 0, 1, '2026-03-20 16:36:12'),
(259, 259, 12, 0, 1, '2026-03-20 16:36:42'),
(260, 260, 12, 0, 1, '2026-03-20 16:36:42'),
(261, 261, 12, 0, 1, '2026-03-20 16:36:42'),
(265, 238, 5, 0, 1, '2026-03-23 16:15:06'),
(266, 239, 5, 0, 1, '2026-03-23 16:15:06'),
(267, 240, 5, 0, 1, '2026-03-23 16:15:06');

-- --------------------------------------------------------

--
-- Table structure for table `parameter_combinations`
--

CREATE TABLE `parameter_combinations` (
  `combo_id` int(11) NOT NULL,
  `combo_name` varchar(300) NOT NULL,
  `combo_code` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` int(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parameter_combinations`
--

INSERT INTO `parameter_combinations` (`combo_id`, `combo_name`, `combo_code`, `description`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Coliforms + Faecal Coliforms', 'COMBO-001', NULL, 1, 0, '2025-11-13 06:18:36', '2026-03-01 17:40:39'),
(2, 'Coliforms + Escherichia coli + Faecal Coliforms', 'COMBO-002', NULL, 1, 0, '2025-11-17 09:45:38', '2026-03-01 17:40:39'),
(3, 'Water and Ice – Coliforms + Water and Ice – Faecal coliforms', 'COMBO-003', NULL, 1, 1, '2025-11-17 09:46:42', '2026-03-01 17:40:39'),
(4, 'Water and Ice – Coliforms + Water and Ice – E. coli + Water and Ice – Faecal coliforms', 'COMBO-004', NULL, 1, 1, '2025-11-17 09:47:09', '2026-03-01 17:40:39'),
(5, 'Coliforms + Escherichia coli', 'COMBO-005', NULL, 1, 0, '2026-03-08 07:08:14', NULL),
(6, 'Escherichia coli + Faecal Coliforms', 'COMBO-006', NULL, 1, 0, '2026-03-08 07:08:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parameter_methods`
--

CREATE TABLE `parameter_methods` (
  `parameter_method_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `sequence_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parameter_methods`
--

INSERT INTO `parameter_methods` (`parameter_method_id`, `parameter_id`, `method_id`, `is_default`, `sequence_order`, `created_at`) VALUES
(17, 13, 10, 1, 0, '2025-11-12 08:47:46'),
(20, 12, 9, 1, 0, '2025-11-12 08:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `parameter_pricing`
--

CREATE TABLE `parameter_pricing` (
  `pricing_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `test_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parameter_pricing`
--

INSERT INTO `parameter_pricing` (`pricing_id`, `parameter_id`, `test_charge`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 1250.00, 1, 0, '2025-11-13 05:52:41', '2026-04-30 16:23:12'),
(2, 11, 2625.00, 1, 0, '2025-11-13 06:02:41', '2025-11-13 06:03:50'),
(3, 2, 1125.00, 1, 0, '2025-11-13 06:21:12', '2026-03-01 09:32:27'),
(4, 3, 1250.00, 1, 0, '2025-11-13 06:22:11', '2026-03-01 09:32:27'),
(5, 2, 1125.00, 1, 1, '2025-11-17 09:40:38', '2026-03-01 17:40:39'),
(6, 4, 1375.00, 1, 0, '2025-11-17 09:41:16', '2026-03-01 09:32:27'),
(7, 13, 2750.00, 1, 0, '2025-11-17 09:41:40', NULL),
(8, 10, 2800.00, 1, 0, '2025-11-17 09:42:13', NULL),
(9, 15, 2375.00, 1, 0, '2025-11-17 09:42:37', NULL),
(10, 8, 2500.00, 1, 0, '2025-11-17 09:42:56', NULL),
(11, 9, 2500.00, 1, 0, '2025-11-17 09:43:09', NULL),
(12, 14, 2125.00, 1, 0, '2025-11-17 09:43:19', NULL),
(13, 4, 1375.00, 1, 1, '2025-11-17 09:44:01', '2026-03-01 17:40:39'),
(14, 3, 1250.00, 1, 1, '2025-11-17 09:44:13', '2026-03-01 17:40:39'),
(15, 16, 1375.00, 1, 0, '2025-11-17 09:44:56', NULL),
(16, 12, 1375.00, 1, 0, '2026-03-08 09:00:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parameter_variants`
--

CREATE TABLE `parameter_variants` (
  `variant_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `variant_name` varchar(200) NOT NULL,
  `full_display_name` varchar(300) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parameter_variants`
--

INSERT INTO `parameter_variants` (`variant_id`, `parameter_id`, `variant_name`, `full_display_name`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 'at 37°C', '', 1, 0, '2025-11-06 04:59:57', '2026-03-06 15:07:59'),
(2, 1, 'at 30°C', '', 1, 0, '2025-11-06 05:13:05', '2026-03-06 15:07:54'),
(3, 1, 'at 22°C', '', 1, 0, '2025-11-06 05:28:22', '2026-03-14 06:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `printed_forms`
--

CREATE TABLE `printed_forms` (
  `print_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `form_type` enum('SIF','SAF','AIF','AF') NOT NULL,
  `printed_by` varchar(200) NOT NULL,
  `printed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `print_history`
--

CREATE TABLE `print_history` (
  `print_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `form_type` enum('SAF','ACKNOWLEDGEMENT','ANALYST') NOT NULL,
  `printed_by` varchar(100) NOT NULL COMMENT 'User fullname from session',
  `printed_by_user_id` int(11) DEFAULT NULL COMMENT 'User ID for FK',
  `print_format` enum('PDF','PRINT') DEFAULT 'PDF',
  `forms_included` varchar(50) DEFAULT NULL COMMENT 'Comma-separated',
  `printed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Silent print tracking - no UI display';

-- --------------------------------------------------------

--
-- Table structure for table `report_items`
--

CREATE TABLE `report_items` (
  `report_item_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL COMMENT 'FK to final_test_reports',
  `sample_item_id` int(11) NOT NULL COMMENT 'FK to sample_items',
  `page_number` int(11) NOT NULL DEFAULT 1 COMMENT 'Which page (1, 2, 3...)',
  `column_position` int(11) NOT NULL DEFAULT 1 COMMENT 'Which column on that page (1-5)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_items`
--

INSERT INTO `report_items` (`report_item_id`, `report_id`, `sample_item_id`, `page_number`, `column_position`, `created_at`) VALUES
(49, 47, 12, 1, 1, '2026-03-07 08:01:27'),
(68, 57, 15, 1, 1, '2026-03-07 10:24:04'),
(69, 58, 9, 1, 1, '2026-03-07 10:24:20'),
(70, 58, 10, 1, 2, '2026-03-07 10:24:20'),
(201, 80, 23, 1, 1, '2026-03-07 20:21:36'),
(257, 124, 14, 1, 1, '2026-03-15 06:37:36'),
(259, 126, 26, 1, 1, '2026-03-15 06:53:28'),
(262, 129, 13, 1, 1, '2026-03-15 14:35:06'),
(265, 132, 16, 1, 1, '2026-03-17 15:48:39'),
(266, 132, 20, 1, 2, '2026-03-17 15:48:39'),
(267, 132, 21, 1, 3, '2026-03-17 15:48:39'),
(268, 132, 18, 1, 4, '2026-03-17 15:48:39'),
(269, 132, 17, 1, 5, '2026-03-17 15:48:39'),
(270, 132, 22, 2, 1, '2026-03-17 15:48:39'),
(271, 132, 19, 2, 2, '2026-03-17 15:48:39'),
(280, 140, 24, 1, 1, '2026-03-17 18:24:51'),
(281, 141, 25, 1, 1, '2026-03-17 18:24:51'),
(282, 142, 7, 1, 1, '2026-03-17 18:31:58'),
(283, 142, 6, 1, 2, '2026-03-17 18:31:58'),
(287, 146, 27, 1, 1, '2026-04-29 19:48:26');

-- --------------------------------------------------------

--
-- Table structure for table `report_logos`
--

CREATE TABLE `report_logos` (
  `logo_id` int(11) NOT NULL,
  `logo_name` varchar(100) NOT NULL COMMENT 'Display name e.g. NARA Logo',
  `logo_type` enum('primary','accreditation','institutional') NOT NULL COMMENT 'primary=NARA, accreditation=SLAB, institutional=Govt seal',
  `file_path` varchar(500) NOT NULL COMMENT 'Path to logo image file',
  `is_for_accredited` tinyint(1) DEFAULT 1 COMMENT '1 = only show on accredited reports, 0 = show on all',
  `display_order` int(11) DEFAULT 1 COMMENT 'Position: 1=left, 2=center, 3=right',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_logos`
--

INSERT INTO `report_logos` (`logo_id`, `logo_name`, `logo_type`, `file_path`, `is_for_accredited`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Government Seal', 'institutional', 'assets/images/govt_seal.png', 1, 1, 1, '2026-03-03 11:01:52'),
(2, 'NARA Logo', 'primary', 'assets/images/nara_logo.png', 0, 2, 1, '2026-03-03 11:01:52'),
(3, 'SLAB Accreditation Mark', 'accreditation', 'assets/images/slab_logo.png', 1, 3, 1, '2026-03-03 11:01:52'),
(4, 'Government Seal', 'institutional', 'assets/images/govt_seal.png', 1, 1, 1, '2026-03-03 11:18:56'),
(5, 'NARA Logo', 'primary', 'assets/images/nara_logo.png', 0, 2, 1, '2026-03-03 11:18:56'),
(6, 'SLAB Accreditation Mark', 'accreditation', 'assets/images/slab_logo.png', 1, 3, 1, '2026-03-03 11:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `report_signatories`
--

CREATE TABLE `report_signatories` (
  `signatory_id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL COMMENT 'Full name e.g. P. Ginigaddarage',
  `title` varchar(150) NOT NULL COMMENT 'Job title e.g. Senior Scientist',
  `division` varchar(200) NOT NULL COMMENT 'Division e.g. Post Harvest Technology Division',
  `role_type` enum('scientist','head') NOT NULL COMMENT 'scientist = left block, head = right block',
  `is_default` tinyint(1) DEFAULT 1 COMMENT 'Auto-assign to new reports',
  `display_order` int(11) DEFAULT 0 COMMENT '1 = left position, 2 = right position',
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_signatories`
--

INSERT INTO `report_signatories` (`signatory_id`, `full_name`, `title`, `division`, `role_type`, `is_default`, `display_order`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'P. Ginigaddarage', 'Senior scientist', 'Post Harvest Technology Division', 'scientist', 1, 0, 1, 0, '2026-03-03 11:01:52', '2026-03-14 20:23:42'),
(2, 'Suseema Ariyarathna', 'Senior scientist', 'Post Harvest Technology Division', 'head', 1, 0, 0, 1, '2026-03-03 11:01:52', '2026-03-14 20:25:04'),
(3, 'P. Ginigaddarage', 'Senior scientist', 'Post Harvest Technology Division', 'scientist', 1, 0, 0, 1, '2026-03-03 11:18:56', '2026-03-14 20:25:02'),
(4, 'Suseema Ariyarathna', 'Senior scientist', 'Post Harvest Technology Division', 'head', 1, 0, 1, 0, '2026-03-03 11:18:56', '2026-04-30 16:02:36');

-- --------------------------------------------------------

--
-- Table structure for table `samples`
--

CREATE TABLE `samples` (
  `sample_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `sample_code` varchar(50) NOT NULL,
  `form_number` varchar(20) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `report_ref` varchar(20) DEFAULT NULL COMMENT 'Root form number (25/0001/03)',
  `submission_type` enum('regular','swab') NOT NULL,
  `received_date` date NOT NULL,
  `received_time` time DEFAULT '00:00:00',
  `sample_collected_date` date DEFAULT NULL,
  `sample_collected_time` time DEFAULT NULL,
  `tentative_date` date DEFAULT NULL,
  `submitted_by` varchar(200) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `additional_charges` decimal(12,2) DEFAULT 0.00,
  `test_charges_total` decimal(12,2) DEFAULT 0.00,
  `grand_total` decimal(12,2) DEFAULT 0.00,
  `payment_status` enum('Paid','Not Paid','Pending') DEFAULT 'Pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending' COMMENT 'Sample processing status',
  `status_updated_at` timestamp NULL DEFAULT NULL COMMENT 'When status was last updated',
  `status_updated_by` varchar(200) DEFAULT NULL COMMENT 'Who updated the status',
  `payment_date` timestamp NULL DEFAULT NULL,
  `payment_updated_by` varchar(100) DEFAULT NULL,
  `forms_generated` tinyint(1) DEFAULT 0,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `analysis_start_date` date DEFAULT NULL COMMENT 'Analysis period start date',
  `analysis_end_date` date DEFAULT NULL COMMENT 'Analysis period end date',
  `is_drawn_by_nara` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = NARA drew the sample, 0 = client submitted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `samples`
--

INSERT INTO `samples` (`sample_id`, `client_id`, `sample_code`, `form_number`, `city_id`, `report_ref`, `submission_type`, `received_date`, `received_time`, `sample_collected_date`, `sample_collected_time`, `tentative_date`, `submitted_by`, `additional_notes`, `additional_charges`, `test_charges_total`, `grand_total`, `payment_status`, `payment_reference`, `status`, `status_updated_at`, `status_updated_by`, `payment_date`, `payment_updated_by`, `forms_generated`, `generated_at`, `created_at`, `analysis_start_date`, `analysis_end_date`, `is_drawn_by_nara`) VALUES
(1, 49, 'QC/26/0001/02', '26/0001/02', NULL, '26/0001', 'regular', '2026-01-16', '09:00:00', NULL, NULL, '2026-01-23', 'Kavidu Naveen', 'dddf', 100.00, 6500.00, 6600.00, 'Paid', '7000-03', 'Pending', '2026-01-16 05:28:12', 'Kavidu Naveen', '2026-02-19 15:51:06', 'Kavidu Naveen', 0, NULL, '2026-01-16 05:28:12', NULL, NULL, 0),
(2, 49, 'QC/26/0002/01', '26/0002/01', NULL, '26/0002', 'regular', '2026-01-16', '09:00:00', NULL, NULL, '2026-01-23', 'Kavidu Naveen', 'ssss', 100.00, 2625.00, 2725.00, 'Not Paid', NULL, 'Pending', '2026-01-16 08:41:02', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-01-16 08:29:25', NULL, NULL, 0),
(3, 36, 'QC/26/0003/01', '26/0003/01', NULL, '26/0003', 'regular', '2026-01-16', '09:00:00', NULL, NULL, '2026-01-23', 'Kavidu Naveen', 'Test', 0.00, 2625.00, 2625.00, 'Not Paid', NULL, 'Pending', '2026-01-16 08:43:31', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-01-16 08:43:31', NULL, NULL, 0),
(4, 18, 'QC/26/0004/01', '26/0004/01', NULL, '26/0004', 'regular', '2026-01-23', '09:00:00', NULL, NULL, '2026-01-30', 'Kavidu Naveen', '', 0.00, 2625.00, 2625.00, 'Not Paid', NULL, 'Pending', '2026-02-02 05:08:24', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-01-23 09:35:38', NULL, NULL, 0),
(5, 68, 'QC/26/005/02', '26/005/02', NULL, '26/005', 'regular', '2026-02-03', '09:00:00', NULL, NULL, '2026-02-10', 'Kavidu Naveen', 'Test', 100.00, 6375.00, 6475.00, 'Not Paid', NULL, 'Completed', '2026-03-06 18:20:27', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-02-03 04:41:39', '2026-02-03', '2026-02-10', 0),
(6, 66, 'QC/26/006/001', '26/006/001', NULL, '26/006', 'regular', '2026-02-03', '09:00:00', NULL, NULL, '2026-02-10', 'Kavidu Naveen', 'Test', 50.00, 5125.00, 5175.00, 'Paid', '7000-002', 'Completed', '2026-03-15 14:37:26', 'Kavidu Naveen', '2026-02-26 15:49:57', 'Kavidu Naveen', 0, NULL, '2026-02-03 04:59:49', '2026-02-03', '2026-03-15', 0),
(7, 108, 'QC/26/007/002', '26/007/002', NULL, '26/007', 'regular', '2026-01-29', '09:00:00', NULL, NULL, '2026-02-13', 'E.M.M.Senavirathna', '', 50.00, 7500.00, 7550.00, 'Paid', '71600060', 'Completed', '2026-03-06 18:17:17', 'Kavidu Naveen', '2026-02-03 05:36:17', 'E.M.M.Senavirathna', 0, NULL, '2026-02-03 05:32:19', '2026-03-06', '2026-03-06', 0),
(8, 50, 'QC/26/008/001', '26/008/001', NULL, '26/008', 'regular', '2026-02-23', '00:00:00', NULL, NULL, '2026-03-05', 'Kavidu Naveen', '', 0.00, 9000.00, 9000.00, 'Paid', '5000-004', 'In Progress', '2026-03-15 07:06:46', 'Kavidu Naveen', '2026-02-11 15:46:39', 'Kavidu Naveen', 0, NULL, '2026-02-23 17:13:09', NULL, NULL, 0),
(9, 65, 'QC/26/009/001', '26/009/001', NULL, '26/009', 'regular', '2026-03-02', '12:32:00', '2026-03-01', '12:32:00', '2026-03-12', '0', NULL, 100.00, 2500.00, 2600.00, 'Paid', '7000-001', 'Completed', '2026-03-23 18:40:25', 'Kavidu Naveen', '2026-02-17 15:44:13', 'Kavidu Naveen', 0, NULL, '2026-03-02 07:02:42', '2026-02-11', '2026-03-03', 0),
(10, 40, 'QC/26/010/001', '26/010/001', 303, '26/010', 'regular', '2026-03-03', '02:05:00', '2026-03-02', '02:05:00', '2026-03-13', 'Kavidu Naveen', NULL, 100.00, 3750.00, 3850.00, 'Not Paid', NULL, 'Completed', '2026-03-15 14:34:53', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-03-02 20:40:59', '2026-03-03', '2026-03-15', 0),
(11, 65, 'QC/26/011/001', '26/011/001', 146, '26/011', 'regular', '2026-03-03', '12:21:00', '2026-03-02', '22:22:00', '2026-03-13', 'Kavidu Naveen', NULL, 100.00, 1250.00, 1350.00, 'Paid', '5000-005', 'Completed', '2026-03-15 06:37:19', 'Kavidu Naveen', '2026-03-14 10:08:56', 'Kavidu Naveen', 0, NULL, '2026-03-03 06:53:20', '2026-03-03', '2026-03-15', 0),
(12, 54, 'QC/26/012/001', '26/012/001', 146, '26/012', 'regular', '2026-03-06', '12:26:00', '2026-03-05', '22:26:00', '2026-03-16', 'Kavidu Naveen', NULL, 50.00, 2500.00, 2550.00, 'Paid', '5000-001', 'Completed', '2026-03-06 07:08:51', 'Kavidu Naveen', '2026-03-06 02:28:09', 'Kavidu Naveen', 0, NULL, '2026-03-06 06:57:47', '2026-03-06', '2026-03-14', 0),
(13, 53, 'QC/26/013/007', '26/013/007', 1, '26/013', 'regular', '2026-03-07', '15:05:00', '2026-03-06', '11:06:00', '2026-03-17', 'Kavidu Naveen', NULL, 100.00, 30250.00, 30350.00, 'Paid', '700-002', 'Completed', '2026-03-23 18:44:18', 'Kavidu Naveen', '2026-03-07 10:42:31', 'Kavidu Naveen', 0, NULL, '2026-03-07 10:42:10', '2026-03-07', '2026-03-12', 0),
(14, 23, 'QC/26/014/001', '26/014/001', 303, '26/014', 'regular', '2026-03-08', '14:39:00', '2026-03-08', '09:41:00', '2026-03-18', 'Kavidu Naveen', NULL, 100.00, 3875.00, 3975.00, 'Paid', '700-003', 'Completed', '2026-03-07 20:13:39', 'Kavidu Naveen', '2026-03-06 20:12:43', 'Kavidu Naveen', 0, NULL, '2026-03-07 20:12:23', '2026-03-08', '2026-03-08', 0),
(15, 65, 'QC/26/015/002', '26/015/002', 146, '26/015', 'regular', '2026-03-08', '01:51:00', '2026-03-07', '07:52:00', '2026-03-18', 'Kavidu Naveen', NULL, 100.00, 5125.00, 5225.00, 'Paid', '5000-002', 'Completed', '2026-03-07 20:25:15', 'Kavidu Naveen', '2026-03-06 20:24:19', 'Kavidu Naveen', 0, NULL, '2026-03-07 20:23:59', '2026-03-08', '2026-03-08', 0),
(16, 6, 'QC/26/016/001', '26/016/001', 146, '26/016', 'swab', '2026-03-08', '15:14:00', '2026-03-08', '10:14:00', '2026-03-18', 'Kavidu Naveen', NULL, 0.00, 8125.00, 8125.00, 'Paid', '5000-003', 'Completed', '2026-03-23 18:44:47', 'Kavidu Naveen', '2026-03-08 12:30:27', 'Kavidu Naveen', 0, NULL, '2026-03-08 09:45:22', '2026-03-08', '2026-03-16', 0),
(17, 22, 'QC/26/017/001', '26/017/001', 162, '26/017', 'regular', '2026-03-15', '11:27:00', '2026-03-15', '11:27:00', '2026-03-25', 'Kavidu Naveen', NULL, 60.00, 2625.00, 2685.00, 'Paid', '7000-005', 'Completed', '2026-04-29 19:48:12', 'Kavidu Naveen', '2026-03-15 16:44:59', 'Kavidu Naveen', 0, NULL, '2026-03-15 05:58:14', '2026-03-15', '2026-03-15', 0),
(18, 7, 'QC/26/018/001', '26/018/001', NULL, '26/018', 'regular', '2026-03-18', '12:35:00', '2026-03-18', '12:36:00', '2026-03-28', 'Kavidu Naveen', NULL, 0.00, 1375.00, 1375.00, 'Not Paid', NULL, 'Pending', '2026-03-18 07:06:28', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-03-18 07:06:28', '2026-03-18', NULL, 0),
(19, 66, 'QC/26/019/015', '26/019/015', 214, '26/019', 'regular', '2026-03-18', '11:49:00', '2026-03-18', '08:50:00', '2026-03-31', 'Kavidu Naveen', NULL, 800.00, 39125.00, 39925.00, 'Not Paid', NULL, 'Pending', '2026-03-18 15:26:38', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-03-18 15:26:38', '2026-03-18', NULL, 0),
(20, 29, 'QC/26/020/001', '26/020/001', 145, '26/020', 'swab', '2026-04-26', '13:03:00', '2026-04-26', '13:03:00', '2026-05-06', 'Kavidu Naveen', NULL, 60.00, 14300.00, 14360.00, 'Not Paid', NULL, 'Pending', '2026-04-26 07:35:57', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-04-26 07:35:57', '2026-04-26', NULL, 0),
(21, 69, 'QC/26/021/001', '26/021/001', 162, '26/021', 'regular', '2026-04-26', '13:11:00', '2026-04-26', '13:12:00', '2026-05-06', 'Kavidu Naveen', NULL, 0.00, 2625.00, 2625.00, 'Not Paid', NULL, 'Pending', '2026-04-26 07:43:31', 'Kavidu Naveen', NULL, NULL, 0, NULL, '2026-04-26 07:43:31', '2026-04-26', NULL, 0),
(22, 27, 'QC/26/022/002', '26/022/002', 1, '26/022', 'regular', '2026-04-26', '13:31:00', '2026-04-26', '13:31:00', '2026-05-06', 'Kavidu Naveen', NULL, 100.00, 4000.00, 4100.00, 'Paid', '700-0013', 'In Progress', '2026-04-30 08:32:58', 'Kavidu Naveen', '2026-04-30 08:32:48', 'Kavidu Naveen', 0, NULL, '2026-04-26 08:05:11', '2026-04-26', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sample_acceptance`
--

CREATE TABLE `sample_acceptance` (
  `id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `report_ref` varchar(20) NOT NULL,
  `received_by` varchar(150) NOT NULL,
  `received_time` time DEFAULT '00:00:00',
  `container_damage` enum('Yes','No') DEFAULT 'No',
  `temperature_condition` enum('Ambient','Chilled','Frozen') DEFAULT 'Ambient',
  `validity_ok` enum('OK','Not OK') DEFAULT 'OK',
  `tentative_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_acceptance`
--

INSERT INTO `sample_acceptance` (`id`, `sample_id`, `report_ref`, `received_by`, `received_time`, `container_damage`, `temperature_condition`, `validity_ok`, `tentative_date`, `remarks`, `created_at`) VALUES
(1, 1, 'AC/26/0001/02', 'Kavidu Naveen', '09:00:00', 'No', 'Ambient', 'OK', '2026-01-23', NULL, '2026-01-16 05:28:12'),
(2, 2, 'AC/26/0002/01', 'Kavidu Naveen', '09:00:00', 'No', 'Ambient', 'OK', '2026-01-23', NULL, '2026-01-16 08:29:25'),
(3, 3, 'AC/26/0003/01', 'Kavidu Naveen', '09:00:00', 'No', 'Ambient', 'OK', '2026-01-23', NULL, '2026-01-16 08:43:32'),
(4, 4, 'AC/26/0004/01', 'Kavidu Naveen', '09:00:00', 'No', 'Ambient', 'OK', '2026-01-30', NULL, '2026-01-23 09:35:38'),
(5, 5, 'QC/26/005/02', 'Kavidu Naveen', '09:00:00', 'No', 'Ambient', 'OK', '2026-02-10', NULL, '2026-02-03 04:41:40'),
(6, 6, 'QC/26/006/001', 'Kavidu Naveen', '09:00:00', 'No', 'Ambient', 'OK', '2026-02-10', NULL, '2026-02-03 04:59:49'),
(7, 7, 'QC/26/007/002', 'E.M.M.Senavirathna', '09:00:00', 'No', 'Chilled', 'OK', '2026-02-13', NULL, '2026-02-03 05:32:19'),
(8, 8, 'QC/26/008/001', 'Kavidu Naveen', '00:00:00', 'No', 'Ambient', 'OK', '2026-03-05', NULL, '2026-02-23 17:13:09'),
(9, 9, 'QC/26/009/001', 'Kavidu Naveen', '12:32:00', 'No', 'Chilled', 'OK', '2026-03-12', NULL, '2026-03-02 07:02:42'),
(10, 10, 'QC/26/010/001', 'Kavidu Naveen', '02:05:00', 'No', 'Chilled', 'OK', '2026-03-13', NULL, '2026-03-02 20:40:59'),
(11, 11, 'QC/26/011/001', 'Kavidu Naveen', '12:21:00', 'No', 'Ambient', 'OK', '2026-03-13', NULL, '2026-03-03 06:53:20'),
(12, 12, 'QC/26/012/001', 'Kavidu Naveen', '12:26:00', 'No', 'Ambient', 'OK', '2026-03-16', NULL, '2026-03-06 06:57:47'),
(13, 13, 'QC/26/013/007', 'Kavidu Naveen', '15:05:00', 'No', 'Ambient', 'OK', '2026-03-17', NULL, '2026-03-07 10:42:10'),
(14, 14, 'QC/26/014/001', 'Kavidu Naveen', '14:39:00', 'No', 'Ambient', 'OK', '2026-03-18', NULL, '2026-03-07 20:12:23'),
(15, 15, 'QC/26/015/002', 'Kavidu Naveen', '01:51:00', 'No', 'Ambient', 'OK', '2026-03-18', NULL, '2026-03-07 20:23:59'),
(16, 16, 'QC/26/016/001', 'Kavidu Naveen', '15:14:00', 'No', 'Ambient', 'OK', '2026-03-18', NULL, '2026-03-08 09:45:22'),
(17, 17, 'QC/26/017/001', 'Kavidu Naveen', '11:27:00', 'No', 'Frozen', 'OK', '2026-03-25', NULL, '2026-03-15 05:58:14'),
(18, 18, 'QC/26/018/001', 'Kavidu Naveen', '12:35:00', 'No', 'Ambient', 'OK', '2026-03-28', NULL, '2026-03-18 07:06:29'),
(19, 19, 'QC/26/019/015', 'Kavidu Naveen', '11:49:00', 'No', 'Ambient', 'OK', '2026-03-31', NULL, '2026-03-18 15:26:38'),
(20, 20, 'QC/26/020/001', 'Kavidu Naveen', '13:03:00', 'No', 'Ambient', 'OK', '2026-05-06', NULL, '2026-04-26 07:35:57'),
(21, 21, 'QC/26/021/001', 'Kavidu Naveen', '13:11:00', 'No', 'Ambient', 'OK', '2026-05-06', NULL, '2026-04-26 07:43:31'),
(22, 22, 'QC/26/022/002', 'Kavidu Naveen', '13:31:00', 'No', 'Ambient', 'OK', '2026-05-06', NULL, '2026-04-26 08:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `sample_acknowledgement`
--

CREATE TABLE `sample_acknowledgement` (
  `ack_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `report_ref` varchar(20) NOT NULL COMMENT 'AC/25/0001/03 format',
  `test_charges` decimal(12,2) DEFAULT 0.00,
  `additional_charges` decimal(12,2) DEFAULT 0.00,
  `total_charges` decimal(12,2) DEFAULT 0.00,
  `payment_status` enum('Paid','Not Paid','Pending') DEFAULT 'Pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `acknowledged_by` varchar(200) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_acknowledgement`
--

INSERT INTO `sample_acknowledgement` (`ack_id`, `sample_id`, `report_ref`, `test_charges`, `additional_charges`, `total_charges`, `payment_status`, `payment_reference`, `acknowledged_by`, `acknowledged_at`, `notes`, `created_at`) VALUES
(1, 1, 'AC/26/0001/02', 6500.00, 100.00, 6600.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-01-16 05:28:12', 'dddf', '2026-01-16 05:28:12'),
(2, 2, 'AC/26/0002/01', 2625.00, 100.00, 2725.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-01-16 08:29:25', 'ssss', '2026-01-16 08:29:25'),
(3, 3, 'AC/26/0003/01', 2625.00, 0.00, 2625.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-01-16 08:43:32', 'Test', '2026-01-16 08:43:32'),
(4, 4, 'AC/26/0004/01', 2625.00, 0.00, 2625.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-01-23 09:35:38', '', '2026-01-23 09:35:38'),
(5, 5, 'QC/26/005/02', 6375.00, 100.00, 6475.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-02-03 04:41:40', 'Test', '2026-02-03 04:41:40'),
(6, 6, 'QC/26/006/001', 5125.00, 50.00, 5175.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-02-03 04:59:49', 'Test', '2026-02-03 04:59:49'),
(7, 7, 'QC/26/007/002', 7500.00, 50.00, 7550.00, 'Not Paid', NULL, 'E.M.M.Senavirathna', '2026-02-03 05:32:19', '', '2026-02-03 05:32:19'),
(8, 8, 'QC/26/008/001', 9000.00, 0.00, 9000.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-02-23 17:13:09', '', '2026-02-23 17:13:09'),
(9, 9, 'QC/26/009/001', 2500.00, 100.00, 2600.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-02 07:02:42', '', '2026-03-02 07:02:42'),
(10, 10, 'QC/26/010/001', 3750.00, 100.00, 3850.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-02 20:40:59', '', '2026-03-02 20:40:59'),
(11, 11, 'QC/26/011/001', 1250.00, 100.00, 1350.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-03 06:53:20', '', '2026-03-03 06:53:20'),
(12, 12, 'QC/26/012/001', 2500.00, 50.00, 2550.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-06 06:57:47', '', '2026-03-06 06:57:47'),
(13, 13, 'QC/26/013/007', 30250.00, 100.00, 30350.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-07 10:42:10', '', '2026-03-07 10:42:10'),
(14, 14, 'QC/26/014/001', 3875.00, 100.00, 3975.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-07 20:12:23', '', '2026-03-07 20:12:23'),
(15, 15, 'QC/26/015/002', 5125.00, 100.00, 5225.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-07 20:23:59', '', '2026-03-07 20:23:59'),
(16, 16, 'QC/26/016/001', 8125.00, 0.00, 8125.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-08 09:45:22', '', '2026-03-08 09:45:22'),
(17, 17, 'QC/26/017/001', 2625.00, 60.00, 2685.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-15 05:58:14', '', '2026-03-15 05:58:14'),
(18, 18, 'QC/26/018/001', 1375.00, 0.00, 1375.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-18 07:06:29', '', '2026-03-18 07:06:29'),
(19, 19, 'QC/26/019/015', 39125.00, 800.00, 39925.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-03-18 15:26:38', '', '2026-03-18 15:26:38'),
(20, 20, 'QC/26/020/001', 14300.00, 60.00, 14360.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-04-26 07:35:57', '', '2026-04-26 07:35:57'),
(21, 21, 'QC/26/021/001', 2625.00, 0.00, 2625.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-04-26 07:43:31', '', '2026-04-26 07:43:31'),
(22, 22, 'QC/26/022/002', 4000.00, 100.00, 4100.00, 'Not Paid', NULL, 'Kavidu Naveen', '2026-04-26 08:05:11', '', '2026-04-26 08:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `sample_extra_items`
--

CREATE TABLE `sample_extra_items` (
  `id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Extra items purchased per submission';

--
-- Dumping data for table `sample_extra_items`
--

INSERT INTO `sample_extra_items` (`id`, `sample_id`, `item_id`, `quantity`, `unit_price`, `line_total`, `created_at`) VALUES
(1, 9, 1, 2, 50.00, 100.00, '2026-03-02 07:02:42'),
(2, 10, 1, 2, 50.00, 100.00, '2026-03-02 20:40:59'),
(3, 11, 1, 2, 50.00, 100.00, '2026-03-03 06:53:20'),
(4, 12, 1, 1, 50.00, 50.00, '2026-03-06 06:57:47'),
(5, 13, 1, 2, 50.00, 100.00, '2026-03-07 10:42:10'),
(6, 14, 1, 2, 50.00, 100.00, '2026-03-07 20:12:23'),
(7, 15, 1, 2, 50.00, 100.00, '2026-03-07 20:23:59'),
(8, 17, 3, 1, 60.00, 60.00, '2026-03-15 05:58:14'),
(9, 19, 3, 5, 60.00, 300.00, '2026-03-18 15:26:38'),
(10, 19, 1, 10, 50.00, 500.00, '2026-03-18 15:26:38'),
(11, 20, 3, 1, 60.00, 60.00, '2026-04-26 07:35:57'),
(12, 22, 1, 2, 50.00, 100.00, '2026-04-26 08:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `sample_items`
--

CREATE TABLE `sample_items` (
  `sample_item_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `sample_name` varchar(200) NOT NULL,
  `value` varchar(50) DEFAULT NULL,
  `unit` varchar(10) DEFAULT NULL,
  `client_sample_code` varchar(100) DEFAULT NULL,
  `sampling_location` varchar(200) DEFAULT NULL,
  `reason_for_analysis` text DEFAULT NULL,
  `container_damage` enum('Yes','No') DEFAULT 'No',
  `temperature_condition` enum('Ambient','Chilled','Frozen') DEFAULT 'Ambient',
  `temperature_value` decimal(4,2) DEFAULT NULL COMMENT 'Exact temp when chilled (2.0-6.0)',
  `container_item_id` int(11) DEFAULT NULL COMMENT 'FK to extra_items for container used',
  `sample_category_id` int(11) DEFAULT NULL COMMENT 'FK to sample_type_categories - locked at submission time',
  `validity_status` enum('OK','Damaged','Expired') DEFAULT 'OK',
  `sequence_number` int(11) NOT NULL,
  `item_total_charge` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_items`
--

INSERT INTO `sample_items` (`sample_item_id`, `sample_id`, `sample_name`, `value`, `unit`, `client_sample_code`, `sampling_location`, `reason_for_analysis`, `container_damage`, `temperature_condition`, `temperature_value`, `container_item_id`, `sample_category_id`, `validity_status`, `sequence_number`, `item_total_charge`, `created_at`) VALUES
(1, 1, 'Drinking Water', '100', 'ml', 'Tw-001', 'tap', 'dd', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-01-16 05:28:12'),
(2, 1, 'Fish', '500', 'g', 'Tf-001', 'tank', 'aaa', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 2, 0.00, '2026-01-16 05:28:12'),
(3, 2, 'Potable water', '100', 'mL', 'g', 'g', 'g', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-01-16 08:29:25'),
(4, 3, 'Fish', '500', 'g', 'TF-001', 'Tank', 'Export', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-01-16 08:43:32'),
(5, 4, 'Drinking Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-01-23 09:35:38'),
(6, 5, 'Shrimp', '500', 'g', 'TW-001', 'Tank', 'Export', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-02-03 04:41:39'),
(7, 5, 'Fish', '400', 'g', 'TW-002', 'Tank', 'Export', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 2, 0.00, '2026-02-03 04:41:39'),
(8, 6, 'Drinking Water', '300', 'mL', 'TW-001', 'Tank', 'Test', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-02-03 04:59:49'),
(9, 7, 'Water', '500', 'mL', '', '', '', 'No', 'Chilled', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-02-03 05:32:19'),
(10, 7, 'Ice', '500', 'g', '', '', '', 'No', 'Frozen', NULL, NULL, NULL, 'OK', 2, 0.00, '2026-02-03 05:32:19'),
(11, 8, 'Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, NULL, NULL, 'OK', 1, 0.00, '2026-02-23 17:13:09'),
(12, 9, 'Water', '100', 'mL', '', '', '', 'No', 'Chilled', 4.00, 1, 1, 'OK', 1, 0.00, '2026-03-02 07:02:42'),
(13, 10, 'Water', '100', 'mL', 'W-001', 'tank', 'test', 'No', 'Chilled', 3.50, NULL, 1, 'OK', 1, 0.00, '2026-03-02 20:40:59'),
(14, 11, 'Waste Water', '100', 'mL', '', '', '', 'No', 'Ambient', NULL, NULL, 1, 'OK', 1, 0.00, '2026-03-03 06:53:20'),
(15, 12, 'Waste Water', '500', 'mL', 'WW-001', 'Waste Tank', 'Test', 'No', 'Ambient', NULL, 1, 1, 'OK', 1, 0.00, '2026-03-06 06:57:47'),
(16, 13, 'Drinking Water', '500', 'mL', 'DW-01', 'Tank', 'Test', 'No', 'Ambient', NULL, 1, 1, 'OK', 1, 0.00, '2026-03-07 10:42:10'),
(17, 13, 'Waste Water', '500', 'mL', 'WW-001', 'Tank', 'Test', 'No', 'Ambient', NULL, 1, 1, 'OK', 2, 0.00, '2026-03-07 10:42:10'),
(18, 13, 'Potable water', '500', 'mL', 'PW-001', 'Tank', 'Dring', 'No', 'Ambient', NULL, 1, 1, 'OK', 3, 0.00, '2026-03-07 10:42:10'),
(19, 13, 'Water', '500', 'mL', 'WW-001', 'Tank', 'Drink', 'No', 'Ambient', NULL, 1, 1, 'OK', 4, 0.00, '2026-03-07 10:42:10'),
(20, 13, 'Drinking Water', '500', 'mL', 'DW-002', 'Tank', 'Who knows', 'No', 'Ambient', NULL, 1, 1, 'OK', 5, 0.00, '2026-03-07 10:42:10'),
(21, 13, 'Drinking Water', '500', 'mL', 'Dw-003', 'Tank', 'Test', 'No', 'Ambient', NULL, 1, 1, 'OK', 6, 0.00, '2026-03-07 10:42:10'),
(22, 13, 'Waste Water', '500', 'mL', 'WW-002', 'Tank', 'Sell', 'No', 'Ambient', NULL, 1, 1, 'OK', 7, 0.00, '2026-03-07 10:42:10'),
(23, 14, 'Fruit Juice', '500', 'mL', 'FJ-001', 'Bottle', 'Test', 'No', 'Ambient', NULL, 1, 1, 'OK', 1, 0.00, '2026-03-07 20:12:23'),
(24, 15, 'Water', '500', 'mL', 'TW-001', 'Tank', 'Test', 'No', 'Ambient', NULL, 1, 1, 'OK', 1, 0.00, '2026-03-07 20:23:59'),
(25, 15, 'Fruit Juice', '500', 'mL', 'FJ-001', 'Tank', 'Test', 'No', 'Ambient', NULL, 1, 1, 'OK', 2, 0.00, '2026-03-07 20:23:59'),
(26, 16, 'Surface Swab', '50', 'cm²', 'SS-001', 'Office Lab', 'Test', 'No', 'Ambient', NULL, NULL, 3, 'OK', 1, 0.00, '2026-03-08 09:45:22'),
(27, 17, 'Ice', '500', 'g', '', '', '', 'No', 'Frozen', NULL, NULL, 1, 'OK', 1, 0.00, '2026-03-15 05:58:14'),
(28, 18, 'Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 1, 0.00, '2026-03-18 07:06:29'),
(29, 19, 'Waste Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 1, 0.00, '2026-03-18 15:26:38'),
(30, 19, 'Water', '100', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 2, 0.00, '2026-03-18 15:26:38'),
(31, 19, 'Potable water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 3, 0.00, '2026-03-18 15:26:38'),
(32, 19, 'Waste Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 4, 0.00, '2026-03-18 15:26:38'),
(33, 19, 'Filtered Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 5, 0.00, '2026-03-18 15:26:38'),
(34, 19, 'Water', '400', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 6, 0.00, '2026-03-18 15:26:38'),
(35, 19, 'Treated Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 7, 0.00, '2026-03-18 15:26:38'),
(36, 19, 'Filtered Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 8, 0.00, '2026-03-18 15:26:38'),
(37, 19, 'Water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 9, 0.00, '2026-03-18 15:26:38'),
(38, 19, 'Potable water', '500', 'mL', '', '', '', 'No', 'Ambient', NULL, 1, 1, 'OK', 10, 0.00, '2026-03-18 15:26:38'),
(39, 19, 'Ice Cubes', '700', 'g', '', '', '', 'No', 'Frozen', NULL, 3, 1, 'OK', 11, 0.00, '2026-03-18 15:26:38'),
(40, 19, 'Ice', '500', 'g', '', '', '', 'No', 'Frozen', NULL, 3, 1, 'OK', 12, 0.00, '2026-03-18 15:26:38'),
(41, 19, 'Ice', '500', 'g', '', '', '', 'No', 'Frozen', NULL, 3, 1, 'OK', 13, 0.00, '2026-03-18 15:26:38'),
(42, 19, 'Ice Cubes', '500', 'g', '', '', '', 'No', 'Frozen', NULL, 3, 1, 'OK', 14, 0.00, '2026-03-18 15:26:38'),
(43, 19, 'Ice', '500', 'g', '', '', '', 'No', 'Frozen', NULL, 3, 1, 'OK', 15, 0.00, '2026-03-18 15:26:38'),
(44, 20, 'Surface Swab', '100', 'cm²', 'kkj', 'kjkj', 'kjkjkj', 'No', 'Ambient', NULL, 3, 3, 'OK', 1, 0.00, '2026-04-26 07:35:57'),
(45, 21, 'Waste Water', '133', 'mL', 'dfdffd', 'fffd', 'fdfdfd', 'No', 'Ambient', NULL, 3, 1, 'OK', 1, 0.00, '2026-04-26 07:43:31'),
(46, 22, 'Water', '111', 'mL', 'ddd', 'ddd', 'dddd', 'No', 'Ambient', NULL, 1, 1, 'OK', 1, 0.00, '2026-04-26 08:05:11'),
(47, 22, 'Waste Water', '222', 'mL', 'dsdd', 'dsdsds', 'dsdsds', 'No', 'Ambient', NULL, 1, 1, 'OK', 2, 0.00, '2026-04-26 08:05:11');

--
-- Triggers `sample_items`
--
DELIMITER $$
CREATE TRIGGER `after_sample_item_insert` AFTER INSERT ON `sample_items` FOR EACH ROW BEGIN
    DECLARE existing_name VARCHAR(200);
    DECLARE name_exists INT DEFAULT 0;
    
    
    
    SELECT sample_name, 1 
    INTO existing_name, name_exists
    FROM sample_names 
    WHERE LOWER(sample_name) = LOWER(NEW.sample_name)
    LIMIT 1;
    
    IF name_exists > 0 THEN
        
        
        UPDATE sample_names 
        SET usage_count = usage_count + 1,
            updated_at = NOW()
        WHERE LOWER(sample_name) = LOWER(NEW.sample_name);
    ELSE
        
        
        INSERT INTO sample_names (sample_name, usage_count, created_at)
        VALUES (NEW.sample_name, 1, NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sample_names`
--

CREATE TABLE `sample_names` (
  `sample_name_id` int(11) NOT NULL,
  `sample_name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_slab_accredited` tinyint(1) NOT NULL DEFAULT 0,
  `usage_count` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_names`
--

INSERT INTO `sample_names` (`sample_name_id`, `sample_name`, `category_id`, `is_slab_accredited`, `usage_count`, `created_at`, `updated_at`) VALUES
(1, 'Drinking Water', 1, 1, 30, '2025-11-20 10:05:02', '2026-03-14 18:58:48'),
(2, 'Ice Cubes', 1, 1, 6, '2025-12-07 17:07:24', '2026-03-18 15:26:38'),
(3, 'Surface Swab', 3, 1, 7, '2025-12-07 17:07:24', '2026-04-26 07:35:57'),
(4, 'Food Sample', 2, 1, 2, '2025-12-07 17:07:24', '2026-03-03 06:50:20'),
(5, 'Treated Water', 1, 1, 3, '2025-12-07 17:07:24', '2026-03-18 15:26:38'),
(19, 'Shrimp', 2, 1, 2, '2025-12-15 04:13:08', '2026-03-03 06:50:20'),
(20, 'Fish', 2, 1, 8, '2025-12-15 04:15:13', '2026-03-03 06:50:20'),
(33, 'Table', 3, 1, 1, '2026-01-12 09:14:01', '2026-03-03 06:50:20'),
(34, 'Potable water', 1, 1, 7, '2026-01-14 07:13:40', '2026-03-18 15:26:38'),
(35, 'Water', 1, 1, 11, '2026-02-03 05:32:19', '2026-04-26 08:05:11'),
(36, 'Ice', 1, 1, 5, '2026-02-03 05:32:19', '2026-03-18 15:26:38'),
(37, 'Fruit Juice', 1, 0, 2, '2026-03-03 06:51:10', '2026-03-07 20:23:59'),
(38, 'Waste Water', 1, 1, 8, '2026-03-03 06:52:58', '2026-04-26 08:05:11'),
(39, 'Filtered Water', 1, 0, 2, '2026-03-14 19:05:13', '2026-03-18 15:26:38');

-- --------------------------------------------------------

--
-- Table structure for table `sample_status_log`
--

CREATE TABLE `sample_status_log` (
  `log_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `old_status` enum('Pending','In Progress','Completed','Cancelled') NOT NULL,
  `new_status` enum('Pending','In Progress','Completed','Cancelled') NOT NULL,
  `updated_by` varchar(200) NOT NULL,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Audit log for sample status changes';

--
-- Dumping data for table `sample_status_log`
--

INSERT INTO `sample_status_log` (`log_id`, `sample_id`, `old_status`, `new_status`, `updated_by`, `notes`, `updated_at`) VALUES
(1, 2, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-01-16 08:40:52'),
(2, 2, 'In Progress', 'Completed', 'Kavidu Naveen', '', '2026-01-16 08:40:56'),
(3, 2, 'Completed', 'Pending', 'Kavidu Naveen', '', '2026-01-16 08:41:02'),
(4, 4, 'Pending', 'Completed', 'Kavidu Naveen', '', '2026-02-02 05:08:17'),
(5, 4, 'Completed', 'Pending', 'Kavidu Naveen', '', '2026-02-02 05:08:24'),
(6, 7, 'Pending', 'In Progress', 'E.M.M.Senavirathna', '', '2026-02-03 06:18:35'),
(7, 6, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-02-22 19:08:20'),
(8, 6, 'In Progress', 'Cancelled', 'Kavidu Naveen', '', '2026-02-22 19:08:24'),
(9, 6, 'Cancelled', 'Completed', 'Kavidu Naveen', '', '2026-02-22 19:08:33'),
(10, 6, 'Completed', 'Pending', 'Kavidu Naveen', '', '2026-02-22 19:08:36'),
(11, 9, 'Pending', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-02 19:07:24'),
(12, 12, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-03-06 06:57:55'),
(13, 12, 'In Progress', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-06 07:08:51'),
(14, 7, 'In Progress', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-06 18:17:17'),
(15, 5, 'Pending', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-06 18:20:27'),
(16, 13, 'Pending', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-07 10:45:49'),
(17, 14, 'Pending', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-07 20:13:39'),
(18, 15, 'Pending', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-07 20:25:15'),
(19, 16, 'Pending', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-08 12:31:23'),
(20, 11, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-03-14 10:08:23'),
(21, 11, 'In Progress', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-15 06:37:19'),
(22, 17, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-03-15 06:53:58'),
(23, 17, 'In Progress', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-15 06:54:44'),
(24, 10, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-03-15 07:06:41'),
(25, 8, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-03-15 07:06:46'),
(26, 10, 'In Progress', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-15 14:34:53'),
(27, 6, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-03-15 14:35:45'),
(28, 6, 'In Progress', 'Completed', 'Kavidu Naveen', 'Auto-completed: all results entered', '2026-03-15 14:37:26'),
(29, 22, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-04-28 18:48:58'),
(30, 22, 'In Progress', 'Pending', 'Kavidu Naveen', '', '2026-04-28 18:49:05'),
(31, 22, 'Pending', 'In Progress', 'Kavidu Naveen', '', '2026-04-30 08:32:58');

-- --------------------------------------------------------

--
-- Table structure for table `sample_tests`
--

CREATE TABLE `sample_tests` (
  `sample_test_id` int(11) NOT NULL,
  `combo_id` int(11) DEFAULT NULL,
  `sample_item_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `test_method_id` int(11) NOT NULL,
  `charge` decimal(12,2) NOT NULL,
  `is_swab` tinyint(1) DEFAULT 0,
  `is_combo_applied` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_tests`
--

INSERT INTO `sample_tests` (`sample_test_id`, `combo_id`, `sample_item_id`, `parameter_id`, `variant_id`, `test_method_id`, `charge`, `is_swab`, `is_combo_applied`, `created_at`) VALUES
(1, NULL, 1, 1, 3, 1, 1250.00, 0, 0, '2026-01-16 05:28:12'),
(2, NULL, 1, 1, 2, 1, 1250.00, 0, 0, '2026-01-16 05:28:12'),
(3, 2, 1, 2, NULL, 2, 458.00, 0, 1, '2026-01-16 05:28:12'),
(4, 2, 1, 3, NULL, 2, 458.00, 0, 1, '2026-01-16 05:28:12'),
(5, 2, 1, 4, NULL, 2, 458.00, 0, 1, '2026-01-16 05:28:12'),
(6, NULL, 2, 1, 3, 1, 1250.00, 0, 0, '2026-01-16 05:28:12'),
(7, 2, 2, 2, NULL, 3, 458.00, 0, 1, '2026-01-16 05:28:12'),
(8, 2, 2, 3, NULL, 4, 458.00, 0, 1, '2026-01-16 05:28:12'),
(9, 2, 2, 4, NULL, 4, 458.00, 0, 1, '2026-01-16 05:28:12'),
(10, NULL, 3, 1, 3, 1, 1250.00, 0, 0, '2026-01-16 08:29:25'),
(11, 2, 3, 2, NULL, 2, 458.00, 0, 1, '2026-01-16 08:29:25'),
(12, 2, 3, 3, NULL, 2, 458.00, 0, 1, '2026-01-16 08:29:25'),
(13, 2, 3, 4, NULL, 2, 458.00, 0, 1, '2026-01-16 08:29:25'),
(14, NULL, 4, 1, 3, 1, 1250.00, 0, 0, '2026-01-16 08:43:32'),
(15, 2, 4, 2, NULL, 3, 458.00, 0, 1, '2026-01-16 08:43:32'),
(16, 2, 4, 3, NULL, 4, 458.00, 0, 1, '2026-01-16 08:43:32'),
(17, 2, 4, 4, NULL, 4, 458.00, 0, 1, '2026-01-16 08:43:32'),
(18, NULL, 5, 1, 3, 1, 1250.00, 0, 0, '2026-01-23 09:35:38'),
(19, 2, 5, 2, NULL, 2, 458.00, 0, 1, '2026-01-23 09:35:38'),
(20, 2, 5, 3, NULL, 2, 458.00, 0, 1, '2026-01-23 09:35:38'),
(21, 2, 5, 4, NULL, 2, 458.00, 0, 1, '2026-01-23 09:35:38'),
(22, NULL, 6, 1, 3, 1, 1250.00, 0, 0, '2026-02-03 04:41:39'),
(23, NULL, 6, 1, 2, 1, 1250.00, 0, 0, '2026-02-03 04:41:39'),
(24, NULL, 6, 1, 1, 1, 1250.00, 0, 0, '2026-02-03 04:41:39'),
(25, NULL, 7, 1, 3, 1, 1250.00, 0, 0, '2026-02-03 04:41:39'),
(26, 2, 7, 2, NULL, 3, 458.00, 0, 1, '2026-02-03 04:41:39'),
(27, 2, 7, 3, NULL, 4, 458.00, 0, 1, '2026-02-03 04:41:40'),
(28, 2, 7, 4, NULL, 4, 458.00, 0, 1, '2026-02-03 04:41:40'),
(29, NULL, 8, 1, 3, 1, 1250.00, 0, 0, '2026-02-03 04:59:49'),
(30, NULL, 8, 1, 2, 1, 1250.00, 0, 0, '2026-02-03 04:59:49'),
(31, NULL, 8, 1, 1, 1, 1250.00, 0, 0, '2026-02-03 04:59:49'),
(32, 2, 8, 2, NULL, 2, 458.00, 0, 1, '2026-02-03 04:59:49'),
(33, 2, 8, 3, NULL, 2, 458.00, 0, 1, '2026-02-03 04:59:49'),
(34, 2, 8, 4, NULL, 2, 458.00, 0, 1, '2026-02-03 04:59:49'),
(35, NULL, 9, 1, 3, 1, 1250.00, 0, 0, '2026-02-03 05:32:19'),
(36, NULL, 9, 1, 1, 1, 1250.00, 0, 0, '2026-02-03 05:32:19'),
(37, NULL, 9, 3, NULL, 2, 1250.00, 0, 0, '2026-02-03 05:32:19'),
(38, NULL, 10, 1, 3, 1, 1250.00, 0, 0, '2026-02-03 05:32:19'),
(39, NULL, 10, 1, 1, 1, 1250.00, 0, 0, '2026-02-03 05:32:19'),
(40, NULL, 10, 3, NULL, 2, 1250.00, 0, 0, '2026-02-03 05:32:19'),
(41, NULL, 11, 1, 3, 1, 1250.00, 0, 0, '2026-02-23 17:13:09'),
(42, NULL, 11, 1, 2, 1, 1250.00, 0, 0, '2026-02-23 17:13:09'),
(43, NULL, 11, 1, 1, 1, 1250.00, 0, 0, '2026-02-23 17:13:09'),
(44, 2, 11, 2, NULL, 2, 458.00, 0, 1, '2026-02-23 17:13:09'),
(45, 2, 11, 3, NULL, 2, 458.00, 0, 1, '2026-02-23 17:13:09'),
(46, 2, 11, 4, NULL, 2, 458.00, 0, 1, '2026-02-23 17:13:09'),
(47, 2, 11, 2, NULL, 3, 458.00, 0, 1, '2026-02-23 17:13:09'),
(48, 2, 11, 3, NULL, 4, 458.00, 0, 1, '2026-02-23 17:13:09'),
(49, 2, 11, 4, NULL, 4, 458.00, 0, 1, '2026-02-23 17:13:09'),
(50, NULL, 11, 8, NULL, 5, 2500.00, 0, 0, '2026-02-23 17:13:09'),
(51, NULL, 12, 1, 3, 1, 1250.00, 0, 0, '2026-03-02 07:02:42'),
(52, NULL, 12, 3, NULL, 2, 1250.00, 0, 0, '2026-03-02 07:02:42'),
(53, NULL, 13, 1, 3, 1, 1250.00, 0, 0, '2026-03-02 20:40:59'),
(54, NULL, 13, 1, 2, 1, 1250.00, 0, 0, '2026-03-02 20:40:59'),
(55, NULL, 13, 3, NULL, 2, 1250.00, 0, 0, '2026-03-02 20:40:59'),
(56, NULL, 14, 1, 1, 1, 1250.00, 0, 0, '2026-03-03 06:53:20'),
(57, NULL, 15, 1, 3, 1, 1250.00, 0, 0, '2026-03-06 06:57:47'),
(58, NULL, 15, 1, 2, 1, 1250.00, 0, 0, '2026-03-06 06:57:47'),
(59, NULL, 16, 1, 3, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(60, NULL, 16, 1, 2, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(61, NULL, 16, 3, NULL, 2, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(62, NULL, 17, 1, 3, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(63, NULL, 17, 1, 2, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(64, 1, 18, 2, NULL, 2, 625.00, 0, 1, '2026-03-07 10:42:10'),
(65, 1, 18, 3, NULL, 2, 625.00, 0, 1, '2026-03-07 10:42:10'),
(66, NULL, 18, 8, NULL, 5, 2500.00, 0, 0, '2026-03-07 10:42:10'),
(67, NULL, 19, 1, 2, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(68, 2, 19, 2, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(69, 2, 19, 3, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(70, 2, 19, 4, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(71, NULL, 20, 1, 3, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(72, NULL, 20, 1, 2, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(73, NULL, 20, 1, 1, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(74, 2, 20, 2, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(75, 2, 20, 3, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(76, 2, 20, 4, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(77, NULL, 21, 1, 1, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(78, NULL, 21, 4, NULL, 2, 1375.00, 0, 0, '2026-03-07 10:42:10'),
(79, NULL, 21, 11, NULL, 8, 2625.00, 0, 0, '2026-03-07 10:42:10'),
(80, NULL, 21, 12, NULL, 9, 0.00, 0, 0, '2026-03-07 10:42:10'),
(81, NULL, 22, 1, 3, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(82, NULL, 22, 1, 2, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(83, NULL, 22, 1, 1, 1, 1250.00, 0, 0, '2026-03-07 10:42:10'),
(84, 2, 22, 2, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(85, 2, 22, 3, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(86, 2, 22, 4, NULL, 2, 458.00, 0, 1, '2026-03-07 10:42:10'),
(87, NULL, 22, 14, NULL, 11, 2125.00, 0, 0, '2026-03-07 10:42:10'),
(88, NULL, 23, 1, 3, 1, 1250.00, 0, 0, '2026-03-07 20:12:23'),
(89, NULL, 23, 1, 2, 1, 1250.00, 0, 0, '2026-03-07 20:12:23'),
(90, 2, 23, 2, NULL, 2, 458.00, 0, 1, '2026-03-07 20:12:23'),
(91, 2, 23, 3, NULL, 2, 458.00, 0, 1, '2026-03-07 20:12:23'),
(92, 2, 23, 4, NULL, 2, 458.00, 0, 1, '2026-03-07 20:12:23'),
(93, NULL, 24, 1, 3, 1, 1250.00, 0, 0, '2026-03-07 20:23:59'),
(94, NULL, 24, 1, 2, 1, 1250.00, 0, 0, '2026-03-07 20:23:59'),
(95, NULL, 25, 1, 1, 1, 1250.00, 0, 0, '2026-03-07 20:23:59'),
(96, 2, 25, 2, NULL, 2, 458.00, 0, 1, '2026-03-07 20:23:59'),
(97, 2, 25, 3, NULL, 2, 458.00, 0, 1, '2026-03-07 20:23:59'),
(98, 2, 25, 4, NULL, 2, 458.00, 0, 1, '2026-03-07 20:23:59'),
(99, NULL, 26, 1, 3, 1, 1625.00, 1, 0, '2026-03-08 09:45:22'),
(100, NULL, 26, 1, 2, 1, 1625.00, 1, 0, '2026-03-08 09:45:22'),
(101, NULL, 26, 2, NULL, 2, 1500.00, 1, 0, '2026-03-08 09:45:22'),
(102, NULL, 26, 3, NULL, 2, 1625.00, 1, 0, '2026-03-08 09:45:22'),
(103, NULL, 26, 4, NULL, 2, 1750.00, 1, 0, '2026-03-08 09:45:22'),
(104, NULL, 27, 1, 3, 1, 1250.00, 0, 0, '2026-03-15 05:58:14'),
(105, NULL, 27, 4, NULL, 2, 1375.00, 0, 0, '2026-03-15 05:58:14'),
(106, 6, 28, 3, NULL, 2, 687.00, 0, 1, '2026-03-18 07:06:29'),
(107, 6, 28, 4, NULL, 2, 687.00, 0, 1, '2026-03-18 07:06:29'),
(108, NULL, 29, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(109, NULL, 29, 1, 2, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(110, NULL, 30, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(111, NULL, 30, 2, NULL, 2, 1125.00, 0, 0, '2026-03-18 15:26:38'),
(112, NULL, 31, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(113, NULL, 31, 2, NULL, 2, 1125.00, 0, 0, '2026-03-18 15:26:38'),
(114, NULL, 32, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(115, NULL, 32, 4, NULL, 2, 1375.00, 0, 0, '2026-03-18 15:26:38'),
(116, NULL, 33, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(117, NULL, 33, 1, 2, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(118, NULL, 33, 1, 1, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(119, NULL, 34, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(120, 6, 34, 3, NULL, 2, 687.00, 0, 1, '2026-03-18 15:26:38'),
(121, 6, 34, 4, NULL, 2, 687.00, 0, 1, '2026-03-18 15:26:38'),
(122, NULL, 35, 1, 2, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(123, NULL, 35, 1, 1, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(124, NULL, 36, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(125, NULL, 36, 4, NULL, 2, 1375.00, 0, 0, '2026-03-18 15:26:38'),
(126, 2, 37, 2, NULL, 2, 458.00, 0, 1, '2026-03-18 15:26:38'),
(127, 2, 37, 3, NULL, 2, 458.00, 0, 1, '2026-03-18 15:26:38'),
(128, 2, 37, 4, NULL, 2, 458.00, 0, 1, '2026-03-18 15:26:38'),
(129, NULL, 38, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(130, NULL, 38, 1, 2, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(131, NULL, 38, 1, 1, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(132, NULL, 38, 2, NULL, 2, 1125.00, 0, 0, '2026-03-18 15:26:38'),
(133, NULL, 39, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(134, NULL, 39, 1, 1, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(135, NULL, 40, 1, 3, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(136, NULL, 41, 1, 1, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(137, 6, 41, 3, NULL, 2, 687.00, 0, 1, '2026-03-18 15:26:38'),
(138, 6, 41, 4, NULL, 2, 687.00, 0, 1, '2026-03-18 15:26:38'),
(139, NULL, 42, 1, 2, 1, 1250.00, 0, 0, '2026-03-18 15:26:38'),
(140, 6, 42, 3, NULL, 2, 687.00, 0, 1, '2026-03-18 15:26:38'),
(141, 6, 42, 4, NULL, 2, 687.00, 0, 1, '2026-03-18 15:26:38'),
(142, NULL, 43, 8, NULL, 5, 2500.00, 0, 0, '2026-03-18 15:26:38'),
(143, NULL, 44, 1, 3, 1, 1250.00, 1, 0, '2026-04-26 07:35:57'),
(144, NULL, 44, 1, 2, 1, 1250.00, 1, 0, '2026-04-26 07:35:57'),
(145, NULL, 44, 1, 1, 1, 1250.00, 1, 0, '2026-04-26 07:35:57'),
(146, 2, 44, 2, NULL, 2, 833.00, 1, 1, '2026-04-26 07:35:57'),
(147, 2, 44, 3, NULL, 2, 833.00, 1, 1, '2026-04-26 07:35:57'),
(148, 2, 44, 4, NULL, 2, 833.00, 1, 1, '2026-04-26 07:35:57'),
(149, NULL, 44, 10, NULL, 7, 2800.00, 1, 0, '2026-04-26 07:35:57'),
(150, NULL, 44, 11, NULL, 8, 2625.00, 1, 0, '2026-04-26 07:35:57'),
(151, NULL, 44, 13, NULL, 10, 2750.00, 1, 0, '2026-04-26 07:35:57'),
(152, NULL, 45, 1, 3, 1, 1250.00, 0, 0, '2026-04-26 07:43:31'),
(153, 2, 45, 2, NULL, 2, 458.00, 0, 1, '2026-04-26 07:43:31'),
(154, 2, 45, 3, NULL, 2, 458.00, 0, 1, '2026-04-26 07:43:31'),
(155, 2, 45, 4, NULL, 2, 458.00, 0, 1, '2026-04-26 07:43:31'),
(156, NULL, 46, 1, 3, 1, 1250.00, 0, 0, '2026-04-26 08:05:11'),
(157, 2, 46, 2, NULL, 2, 458.00, 0, 1, '2026-04-26 08:05:11'),
(158, 2, 46, 3, NULL, 2, 458.00, 0, 1, '2026-04-26 08:05:11'),
(159, 2, 46, 4, NULL, 2, 458.00, 0, 1, '2026-04-26 08:05:11'),
(160, 6, 47, 3, NULL, 2, 687.00, 0, 1, '2026-04-26 08:05:11'),
(161, 6, 47, 4, NULL, 2, 687.00, 0, 1, '2026-04-26 08:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `sample_test_results`
--

CREATE TABLE `sample_test_results` (
  `result_id` int(11) NOT NULL,
  `sample_test_id` int(11) NOT NULL,
  `sample_item_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `result_mode` enum('numeric_or_ND','present_or_absent') NOT NULL,
  `result_value` varchar(100) DEFAULT NULL,
  `has_espc` tinyint(1) NOT NULL DEFAULT 0,
  `result_display` varchar(255) DEFAULT NULL,
  `entered_by` int(11) DEFAULT NULL,
  `entered_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_test_results`
--

INSERT INTO `sample_test_results` (`result_id`, `sample_test_id`, `sample_item_id`, `parameter_id`, `variant_id`, `result_mode`, `result_value`, `has_espc`, `result_display`, `entered_by`, `entered_at`, `updated_at`) VALUES
(1, 51, 12, 1, 3, 'numeric_or_ND', '4', 1, '4', 1, '2026-03-23 18:40:25', '2026-03-23 18:40:25'),
(5, 52, 12, 3, NULL, 'numeric_or_ND', '1800+', 0, '1800+', 1, '2026-03-23 18:40:25', '2026-03-23 18:40:25'),
(22, 57, 15, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-06 07:08:51', '2026-03-06 07:08:51'),
(23, 58, 15, 1, 2, 'numeric_or_ND', '9.2 x 10^3', 1, '9.2 x 10<sup>3</sup>', 1, '2026-03-06 07:08:51', '2026-03-06 07:08:51'),
(24, 35, 9, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-06 18:17:16', '2026-03-06 18:17:16'),
(25, 36, 9, 1, 1, 'numeric_or_ND', '4', 1, '4', 1, '2026-03-06 18:17:16', '2026-03-06 18:17:16'),
(26, 37, 9, 3, NULL, 'numeric_or_ND', '1800+', 0, '1800+', 1, '2026-03-06 18:17:16', '2026-03-06 18:17:16'),
(27, 38, 10, 1, 3, 'numeric_or_ND', '10 x 10^8', 1, '10 x 10<sup>8</sup>', 1, '2026-03-06 18:17:17', '2026-03-06 18:17:17'),
(28, 39, 10, 1, 1, 'numeric_or_ND', '5', 0, '5', 1, '2026-03-06 18:17:17', '2026-03-06 18:17:17'),
(29, 40, 10, 3, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-06 18:17:17', '2026-03-06 18:17:17'),
(30, 22, 6, 1, 3, 'numeric_or_ND', '10', 0, '10', 1, '2026-03-06 18:20:27', '2026-03-06 18:20:27'),
(31, 23, 6, 1, 2, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-06 18:20:27', '2026-03-06 18:20:27'),
(32, 24, 6, 1, 1, 'numeric_or_ND', '10 x 10^8', 1, '10 x 10<sup>8</sup>', 1, '2026-03-06 18:20:27', '2026-03-06 18:20:27'),
(33, 25, 7, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-06 18:20:27', '2026-03-06 18:20:27'),
(34, 26, 7, 2, NULL, 'numeric_or_ND', '100', 0, '100', 1, '2026-03-06 18:20:27', '2026-03-06 18:20:27'),
(35, 27, 7, 3, NULL, 'numeric_or_ND', '1800+', 0, '1800+', 1, '2026-03-06 18:20:27', '2026-03-06 18:20:27'),
(36, 28, 7, 4, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-06 18:20:27', '2026-03-06 18:20:27'),
(37, 59, 16, 1, 3, 'numeric_or_ND', '10', 1, '10', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(38, 60, 16, 1, 2, 'numeric_or_ND', '10 x 10^5', 1, '10 x 10<sup>5</sup>', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(39, 61, 16, 3, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(40, 62, 17, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(41, 63, 17, 1, 2, 'numeric_or_ND', '5', 0, '5', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(42, 64, 18, 2, NULL, 'numeric_or_ND', '9.2 x 10^6', 0, '9.2 x 10<sup>6</sup>', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(43, 65, 18, 3, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(44, 66, 18, 8, NULL, 'present_or_absent', 'Absent', 0, 'Absent', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(45, 67, 19, 1, 2, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(46, 68, 19, 2, NULL, 'numeric_or_ND', '5', 0, '5', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(47, 69, 19, 3, NULL, 'numeric_or_ND', '1800+', 0, '1800+', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(48, 70, 19, 4, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(49, 71, 20, 1, 3, 'numeric_or_ND', '15', 0, '15', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(50, 72, 20, 1, 2, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(51, 73, 20, 1, 1, 'numeric_or_ND', '10 x 10^6', 1, '10 x 10<sup>6</sup>', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(52, 74, 20, 2, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(53, 75, 20, 3, NULL, 'numeric_or_ND', '4500', 0, '4500', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(54, 76, 20, 4, NULL, 'numeric_or_ND', '500+', 0, '500+', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(55, 77, 21, 1, 1, 'numeric_or_ND', '10', 1, '10', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(56, 78, 21, 4, NULL, 'numeric_or_ND', '<1', 0, '<1', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(57, 79, 21, 11, NULL, 'numeric_or_ND', '5', 0, '5', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(58, 80, 21, 12, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(59, 81, 22, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(60, 82, 22, 1, 2, 'numeric_or_ND', '10 x 10^7', 1, '10 x 10<sup>7</sup>', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(61, 83, 22, 1, 1, 'numeric_or_ND', '5', 1, '5', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(62, 84, 22, 2, NULL, 'numeric_or_ND', '1500', 0, '1500', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(63, 85, 22, 3, NULL, 'numeric_or_ND', '<1', 0, '<1', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(64, 86, 22, 4, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(65, 87, 22, 14, NULL, 'numeric_or_ND', '50', 0, '50', 1, '2026-03-23 18:44:18', '2026-03-23 18:44:18'),
(66, 88, 23, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-07 20:13:39', '2026-03-07 20:13:39'),
(67, 89, 23, 1, 2, 'numeric_or_ND', '10x10^5', 0, '10x10<sup>5</sup>', 1, '2026-03-07 20:13:39', '2026-03-07 20:13:39'),
(68, 90, 23, 2, NULL, 'numeric_or_ND', '5', 0, '5', 1, '2026-03-07 20:13:39', '2026-03-07 20:13:39'),
(69, 91, 23, 3, NULL, 'numeric_or_ND', '4500', 0, '4500', 1, '2026-03-07 20:13:39', '2026-03-07 20:13:39'),
(70, 92, 23, 4, NULL, 'numeric_or_ND', '500+', 0, '500+', 1, '2026-03-07 20:13:39', '2026-03-07 20:13:39'),
(71, 93, 24, 1, 3, 'numeric_or_ND', '10x10^6', 0, '10x10<sup>6</sup>', 1, '2026-03-07 20:25:15', '2026-03-07 20:25:15'),
(72, 94, 24, 1, 2, 'numeric_or_ND', '5', 1, '5', 1, '2026-03-07 20:25:15', '2026-03-07 20:25:15'),
(73, 95, 25, 1, 1, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-07 20:25:15', '2026-03-07 20:25:15'),
(74, 96, 25, 2, NULL, 'numeric_or_ND', '1500+', 0, '1500+', 1, '2026-03-07 20:25:15', '2026-03-07 20:25:15'),
(75, 97, 25, 3, NULL, 'numeric_or_ND', '40', 0, '40', 1, '2026-03-07 20:25:15', '2026-03-07 20:25:15'),
(76, 98, 25, 4, NULL, 'numeric_or_ND', '5', 0, '5', 1, '2026-03-07 20:25:15', '2026-03-07 20:25:15'),
(77, 99, 26, 1, 3, 'numeric_or_ND', '5', 1, '5', 1, '2026-03-23 18:44:47', '2026-03-23 18:44:47'),
(78, 100, 26, 1, 2, 'numeric_or_ND', '10x10^7', 1, '10x10<sup>7</sup>', 1, '2026-03-23 18:44:47', '2026-03-23 18:44:47'),
(79, 101, 26, 2, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-23 18:44:47', '2026-03-23 18:44:47'),
(80, 102, 26, 3, NULL, 'numeric_or_ND', '1500+', 0, '1500+', 1, '2026-03-23 18:44:47', '2026-03-23 18:44:47'),
(81, 103, 26, 4, NULL, 'numeric_or_ND', '5', 0, '5', 1, '2026-03-23 18:44:47', '2026-03-23 18:44:47'),
(82, 56, 14, 1, 1, 'numeric_or_ND', '10', 0, '10', 1, '2026-03-15 06:37:19', '2026-03-15 06:37:19'),
(83, 104, 27, 1, 3, 'numeric_or_ND', '10', 0, '10', 1, '2026-04-29 19:48:12', '2026-04-29 19:48:12'),
(84, 105, 27, 4, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-04-29 19:48:12', '2026-04-29 19:48:12'),
(85, 53, 13, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-15 14:34:53', '2026-03-15 14:34:53'),
(86, 54, 13, 1, 2, 'numeric_or_ND', '10', 1, '10', 1, '2026-03-15 14:34:53', '2026-03-15 14:34:53'),
(87, 55, 13, 3, NULL, 'numeric_or_ND', '10 x 10^6', 0, '10 x 10<sup>6</sup>', 1, '2026-03-15 14:34:53', '2026-03-15 14:34:53'),
(88, 29, 8, 1, 3, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-15 14:37:26', '2026-03-15 14:37:26'),
(89, 30, 8, 1, 2, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-15 14:37:26', '2026-03-15 14:37:26'),
(90, 31, 8, 1, 1, 'numeric_or_ND', '10', 1, '10', 1, '2026-03-15 14:37:26', '2026-03-15 14:37:26'),
(91, 32, 8, 2, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-15 14:37:26', '2026-03-15 14:37:26'),
(92, 33, 8, 3, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-15 14:37:26', '2026-03-15 14:37:26'),
(93, 34, 8, 4, NULL, 'numeric_or_ND', 'ND', 0, 'ND', 1, '2026-03-15 14:37:26', '2026-03-15 14:37:26');

-- --------------------------------------------------------

--
-- Table structure for table `sample_type_categories`
--

CREATE TABLE `sample_type_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(10) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `base_category_id` int(11) DEFAULT NULL COMMENT 'Links to parameter_base_unit_config.base_category_id for unit/method resolution',
  `is_slab_accredited` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sample type categories linked to SLAB accreditation';

--
-- Dumping data for table `sample_type_categories`
--

INSERT INTO `sample_type_categories` (`category_id`, `category_name`, `category_code`, `description`, `base_category_id`, `is_slab_accredited`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Water and Ice', 'WAT', 'Drinking water, treated water, ice cubes, potable water', 1, 1, 1, 1, '2026-03-01 18:32:27', NULL),
(2, 'Fish and Shellfish', 'FSH', 'Fish, shrimp, shellfish, and other seafood products', 2, 1, 2, 1, '2026-03-01 18:32:27', NULL),
(3, 'Surface Swab', 'SWB', 'Surface swabs from tables, equipment, and other surfaces', 3, 1, 3, 1, '2026-03-01 18:32:27', NULL),
(4, 'Other', 'OTH', 'Non-accredited samples (cosmetics, soil, general food, etc.)', NULL, 0, 4, 1, '2026-03-01 18:32:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `swab_combos`
--

CREATE TABLE `swab_combos` (
  `combo_id` int(11) NOT NULL,
  `combo_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `swab_combos`
--

INSERT INTO `swab_combos` (`combo_id`, `combo_name`, `price`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Escherichia coli + Coliforms + Aerobic Plate Count + Faecal Coliforms + Staphylococcus aureus', 375.00, 1, 0, '2026-03-23 18:27:04', '2026-04-26 13:49:08');

-- --------------------------------------------------------

--
-- Table structure for table `swab_combo_items`
--

CREATE TABLE `swab_combo_items` (
  `id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `param_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `swab_combo_items`
--

INSERT INTO `swab_combo_items` (`id`, `combo_id`, `param_id`) VALUES
(21, 1, 1),
(20, 1, 2),
(22, 1, 3),
(19, 1, 4),
(23, 1, 11);

-- --------------------------------------------------------

--
-- Table structure for table `swab_param`
--

CREATE TABLE `swab_param` (
  `swab_param_id` int(11) NOT NULL,
  `param_id` int(11) NOT NULL,
  `swab_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `swab_param`
--

INSERT INTO `swab_param` (`swab_param_id`, `param_id`, `swab_price`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 375.00, 1, 0, '2025-11-13 05:53:35', '2026-04-30 16:28:57'),
(2, 11, 375.00, 1, 0, '2025-11-13 06:03:33', '2026-03-20 16:30:40'),
(3, 2, 375.00, 1, 0, '2025-11-13 06:19:57', '2026-03-01 15:19:17'),
(6, 4, 375.00, 1, 0, '2025-12-14 07:23:57', '2026-03-20 16:20:58'),
(7, 13, 375.00, 1, 0, '2025-12-14 07:24:04', '2025-12-14 07:24:04'),
(8, 10, 375.00, 1, 0, '2025-12-14 07:24:12', '2026-03-01 17:16:06'),
(10, 3, 375.00, 1, 0, '2025-12-14 07:28:14', '2026-03-20 16:21:09'),
(11, 16, 0.00, 1, 1, '2026-01-16 09:17:53', '2026-03-01 15:55:17'),
(12, 9, 0.00, 1, 1, '2026-01-16 09:18:27', '2026-01-16 09:19:17'),
(13, 8, 375.00, 1, 1, '2026-01-20 06:12:18', '2026-03-23 16:15:06'),
(14, 8, 0.00, 1, 1, '2026-01-20 06:29:34', '2026-03-23 16:15:06'),
(15, 8, 0.00, 1, 1, '2026-01-20 06:30:08', '2026-03-23 16:15:06');

-- --------------------------------------------------------

--
-- Table structure for table `test_methods`
--

CREATE TABLE `test_methods` (
  `method_id` int(11) NOT NULL,
  `method_name` varchar(200) NOT NULL,
  `standard_body` varchar(100) DEFAULT 'Not Mentioned',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_methods`
--

INSERT INTO `test_methods` (`method_id`, `method_name`, `standard_body`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'ISO 4833-1:2013(E)', 'SLS', 1, 0, '2025-11-10 10:04:47', '2026-04-30 16:14:31'),
(2, 'SLS 1461 Part 1/Sec 3:2013', 'SLS', 1, 0, '2025-11-11 09:38:09', NULL),
(3, 'ISO 4831-1:2006(E)', 'SLS', 1, 0, '2025-11-11 09:38:46', '2026-03-03 11:18:56'),
(4, 'ISO 7251:2005(E)', 'SLS', 1, 0, '2025-11-11 09:39:18', '2026-03-03 11:18:56'),
(5, 'ISO/TS 21872-1:2017(E)', 'SLS', 1, 0, '2025-11-11 09:39:44', '2026-03-03 11:18:56'),
(6, 'ISO 11290-1:2017(E)', 'SLS', 1, 0, '2025-11-11 09:40:56', '2026-03-03 11:18:56'),
(7, 'ISO 6579-1:2017(E)', 'ISO', 1, 0, '2025-11-11 09:41:29', '2026-03-03 11:18:56'),
(8, 'ISO 6888-1:2021(E)', 'SLS', 1, 0, '2025-11-11 09:41:49', '2026-03-03 11:18:56'),
(9, 'SLS 516 Part 4: 1982', 'SLS', 1, 0, '2025-11-11 09:42:24', NULL),
(10, 'ISO:11920 - 1:1996', 'ISO', 1, 0, '2025-11-11 09:44:33', NULL),
(11, 'APHA:2001', 'APHA', 1, 0, '2025-11-11 09:46:38', NULL),
(12, 'ISO 6461:1986', 'ISO', 1, 0, '2025-11-11 09:47:20', NULL),
(13, 'SLS 516 Part 2/Sec 1:2013', 'SLS', 1, 0, '2025-11-11 09:47:34', '2025-11-11 09:53:45'),
(14, 'SLS 516 Part 2/Sec 1:2013', 'APHA', 1, 1, '2025-11-11 09:49:33', '2025-11-11 10:00:24'),
(15, 'SLS 516 Part 2/Sec 1:2013', '', 1, 1, '2025-11-11 09:59:58', '2025-11-11 10:00:18'),
(16, 'APHA: 2015', 'APHA', 1, 0, '2025-12-20 13:26:59', '2025-12-20 13:27:35'),
(17, 'ttt', 'ISO', 1, 1, '2026-03-14 07:07:31', '2026-03-14 07:07:35');

-- --------------------------------------------------------

--
-- Table structure for table `test_parameters`
--

CREATE TABLE `test_parameters` (
  `parameter_id` int(11) NOT NULL,
  `parameter_code` varchar(50) NOT NULL,
  `method_id` int(11) DEFAULT NULL,
  `parameter_name` varchar(200) NOT NULL,
  `parameter_category` varchar(100) DEFAULT NULL,
  `base_unit` varchar(50) DEFAULT NULL,
  `has_variants` tinyint(1) NOT NULL DEFAULT 0,
  `short_name` varchar(100) DEFAULT NULL COMMENT 'Short display name (e.g., APC, Coliforms)',
  `display_format` varchar(20) NOT NULL DEFAULT 'normal' COMMENT 'Display format: normal, scientific, superscript',
  `swab_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `result_mode` enum('numeric_or_ND','present_or_absent') NOT NULL DEFAULT 'numeric_or_ND' COMMENT 'Result rule: numeric+ND or Present/Absent',
  `espc_applicable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = ESPC notation can be used for this parameter'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_parameters`
--

INSERT INTO `test_parameters` (`parameter_id`, `parameter_code`, `method_id`, `parameter_name`, `parameter_category`, `base_unit`, `has_variants`, `short_name`, `display_format`, `swab_enabled`, `is_active`, `is_deleted`, `created_at`, `updated_at`, `result_mode`, `espc_applicable`) VALUES
(1, 'A', NULL, 'Aerobic Plate Count', 'Microbiology', NULL, 0, 'APC', 'normal', 1, 1, 0, '2025-11-04 04:08:54', '2026-03-20 16:27:36', 'numeric_or_ND', 1),
(2, 'B', NULL, 'Coliforms', '', 'MPN/100mL', 0, '', 'normal', 1, 1, 0, '2025-11-04 04:10:06', '2026-03-01 15:19:17', 'numeric_or_ND', 0),
(3, 'C', NULL, 'Faecal Coliforms', 'Microbiology', 'MPN/100mL', 0, '', 'normal', 1, 1, 0, '2025-11-04 04:13:03', '2026-03-20 16:21:09', 'numeric_or_ND', 1),
(4, 'D', NULL, 'Escherichia coli', 'Microbiology', 'MPN/100mL', 0, 'E. coli', 'scientific', 1, 1, 0, '2025-11-04 04:17:59', '2026-03-20 16:20:58', 'numeric_or_ND', 1),
(8, 'H', NULL, 'Vibrio cholerae', 'Microbiology', NULL, 0, '', 'scientific', 0, 1, 0, '2025-11-04 04:25:25', '2026-03-23 16:15:06', 'present_or_absent', 1),
(9, 'I', NULL, 'Vibrio parahaemolyticus', '', NULL, 0, '', 'scientific', 0, 1, 0, '2025-11-04 04:30:31', '2026-03-03 11:18:56', 'present_or_absent', 0),
(10, 'J', NULL, 'Salmonella spp.', '', NULL, 0, '', 'scientific', 1, 1, 0, '2025-11-04 04:31:07', '2026-03-03 11:18:56', 'present_or_absent', 0),
(11, 'K', NULL, 'Staphylococcus aureus', 'Microbiology', NULL, 0, 'S. aureus', 'scientific', 1, 1, 0, '2025-11-04 04:31:28', '2026-03-20 16:30:40', 'numeric_or_ND', 1),
(12, 'L', NULL, 'Faecal Streptococci', '', 'MPN/mL', 0, 'F.Streptococci', 'normal', 0, 1, 0, '2025-11-04 04:32:03', '2026-03-01 08:57:56', 'numeric_or_ND', 0),
(13, 'M', NULL, 'Listeria monocytogenes', '', '/25g', 0, 'Listeria', 'scientific', 1, 1, 0, '2025-11-04 04:32:32', '2026-03-03 11:18:56', 'present_or_absent', 0),
(14, 'N', NULL, 'Vibrio spp', 'Microbiology', 'cfu/g', 0, 'Vibrio spp.', 'scientific', 0, 1, 0, '2025-11-04 04:33:00', '2026-03-20 16:31:11', 'numeric_or_ND', 1),
(15, 'O', NULL, 'Sulphite reducing clostridia', 'Microbiology', 'MPN/100mL', 0, '', 'normal', 0, 1, 0, '2025-11-04 04:33:36', '2026-03-20 16:36:42', 'numeric_or_ND', 1),
(16, 'P', NULL, 'Yeasts and Moulds', 'Microbiology', 'cfu/g', 0, '', 'normal', 0, 1, 0, '2025-11-04 04:33:54', '2026-03-20 16:36:12', 'numeric_or_ND', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(200) NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('LabTechnician','Assistant','Admin') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_deleted` tinyint(1) DEFAULT 0,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `email`, `role`, `status`, `is_deleted`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'Kavidu Naveen', 'nav019', 'naveen.knj19@gmail.com', 'Admin', 'active', 0, '$2y$10$sFs7WRycpvbNBGv/0XZuku9jjGogu3pQO1g6GV6bQ/EdXCbPBB1KG', '2025-12-06 17:41:34', '2026-03-14 05:46:48'),
(2, 'Naveen KNJ', 'navva_004', 'naveen.kn19@gmail.com', 'LabTechnician', 'active', 0, '$2y$10$IBx5LyV.nrTdqOstJwP4m.2P9IRUQNVd.bdgF9AVmmy7e9B5UerwK', '2026-01-04 07:17:55', '2026-01-04 07:17:55'),
(3, 'Kavidu Naveen', 'navknj', 'naveen.thedev@gmail.com', 'LabTechnician', 'active', 0, '$2y$10$avjd/PGTLdjE8JqhystUout7cTwb04GlwQeu..RlsLLydfRZOruSm', '2026-01-10 14:32:13', '2026-01-10 14:32:13'),
(4, 'E.M.M.Senavirathna', 'Manoja', 'manojase1@gmail.com', 'Admin', 'active', 0, '$2y$10$2pNWIDRP0ansB98gKLYxcODGidXokbo5nN9H1.6Q0zQRC2WMsuGuG', '2026-02-03 05:15:27', '2026-03-19 09:23:16'),
(5, 'Root Administrator', 'rootAdmin', 'rootadmin@nara.gov.lk', 'Admin', 'active', 0, '$2y$10$RxgKvC5.w.ycX9glO7Z.lOnT3MPs4retjWUO3KAJ6nazSIGu9WGQG', '2026-03-10 10:10:44', '2026-04-30 16:18:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accreditation_certificates`
--
ALTER TABLE `accreditation_certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD UNIQUE KEY `uq_certificate_code` (`certificate_code`,`is_deleted`),
  ADD KEY `idx_is_current` (`is_current`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expiry` (`valid_to`),
  ADD KEY `idx_deleted` (`is_deleted`);

--
-- Indexes for table `base_units`
--
ALTER TABLE `base_units`
  ADD PRIMARY KEY (`base_unit_id`),
  ADD KEY `idx_base_category` (`base_category_id`),
  ADD KEY `idx_is_common` (`is_common`);

--
-- Indexes for table `base_unit_categories`
--
ALTER TABLE `base_unit_categories`
  ADD PRIMARY KEY (`base_category_id`),
  ADD UNIQUE KEY `category_code` (`category_code`),
  ADD KEY `idx_category_code` (`category_code`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`city_id`),
  ADD UNIQUE KEY `uq_city_name` (`city_name`,`is_deleted`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_review` (`needs_review`),
  ADD KEY `idx_usage` (`usage_count`),
  ADD KEY `idx_city_search` (`is_active`,`is_deleted`,`city_name`),
  ADD KEY `idx_city_usage` (`usage_count`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `idx_is_active` (`is_Active`),
  ADD KEY `idx_client_name` (`client_name`(50)),
  ADD KEY `idx_phone_search` (`phone_primary`),
  ADD KEY `idx_client_search` (`client_name`(100)),
  ADD KEY `idx_city_id` (`city_id`),
  ADD KEY `idx_client_city_id` (`city_id`);

--
-- Indexes for table `combination_items`
--
ALTER TABLE `combination_items`
  ADD PRIMARY KEY (`combo_item_id`),
  ADD KEY `combo_id` (`combo_id`),
  ADD KEY `parameter_id` (`parameter_id`);

--
-- Indexes for table `combination_pricing`
--
ALTER TABLE `combination_pricing`
  ADD PRIMARY KEY (`combo_pricing_id`),
  ADD KEY `combo_id` (`combo_id`);

--
-- Indexes for table `extra_items`
--
ALTER TABLE `extra_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_deleted` (`is_deleted`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `final_test_reports`
--
ALTER TABLE `final_test_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD KEY `sample_id` (`sample_id`),
  ADD KEY `signatory_left_id` (`signatory_left_id`),
  ADD KEY `signatory_right_id` (`signatory_right_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `form_sequence`
--
ALTER TABLE `form_sequence`
  ADD PRIMARY KEY (`year`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `sample_id` (`sample_id`);

--
-- Indexes for table `parameter_base_unit_config`
--
ALTER TABLE `parameter_base_unit_config`
  ADD PRIMARY KEY (`config_id`),
  ADD UNIQUE KEY `uq_param_base_category` (`parameter_id`,`base_category_id`),
  ADD KEY `idx_parameter` (`parameter_id`),
  ADD KEY `idx_category` (`base_category_id`),
  ADD KEY `fk_pbu_config_unit` (`base_unit_id`),
  ADD KEY `fk_pbc_certificate` (`certificate_id`);

--
-- Indexes for table `parameter_category_methods`
--
ALTER TABLE `parameter_category_methods`
  ADD PRIMARY KEY (`pcm_id`),
  ADD UNIQUE KEY `uq_config_method` (`config_id`,`method_id`),
  ADD KEY `idx_config` (`config_id`),
  ADD KEY `fk_pcm_method` (`method_id`);

--
-- Indexes for table `parameter_combinations`
--
ALTER TABLE `parameter_combinations`
  ADD PRIMARY KEY (`combo_id`),
  ADD UNIQUE KEY `combo_code` (`combo_code`);

--
-- Indexes for table `parameter_methods`
--
ALTER TABLE `parameter_methods`
  ADD PRIMARY KEY (`parameter_method_id`),
  ADD UNIQUE KEY `unique_param_method` (`parameter_id`,`method_id`),
  ADD KEY `parameter_id` (`parameter_id`),
  ADD KEY `method_id` (`method_id`),
  ADD KEY `idx_method_param` (`parameter_id`,`method_id`);

--
-- Indexes for table `parameter_pricing`
--
ALTER TABLE `parameter_pricing`
  ADD PRIMARY KEY (`pricing_id`),
  ADD KEY `parameter_id` (`parameter_id`);

--
-- Indexes for table `parameter_variants`
--
ALTER TABLE `parameter_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `parameter_id` (`parameter_id`),
  ADD KEY `idx_variant_param_active` (`parameter_id`,`is_active`,`is_deleted`);

--
-- Indexes for table `printed_forms`
--
ALTER TABLE `printed_forms`
  ADD PRIMARY KEY (`print_id`),
  ADD KEY `sample_id` (`sample_id`);

--
-- Indexes for table `print_history`
--
ALTER TABLE `print_history`
  ADD PRIMARY KEY (`print_id`),
  ADD KEY `idx_sample` (`sample_id`),
  ADD KEY `idx_user` (`printed_by_user_id`),
  ADD KEY `idx_date` (`printed_at`);

--
-- Indexes for table `report_items`
--
ALTER TABLE `report_items`
  ADD PRIMARY KEY (`report_item_id`),
  ADD UNIQUE KEY `uq_report_item` (`report_id`,`sample_item_id`),
  ADD KEY `sample_item_id` (`sample_item_id`),
  ADD KEY `idx_report_page` (`report_id`,`page_number`,`column_position`);

--
-- Indexes for table `report_logos`
--
ALTER TABLE `report_logos`
  ADD PRIMARY KEY (`logo_id`);

--
-- Indexes for table `report_signatories`
--
ALTER TABLE `report_signatories`
  ADD PRIMARY KEY (`signatory_id`);

--
-- Indexes for table `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`sample_id`),
  ADD UNIQUE KEY `sample_code` (`sample_code`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `idx_form_number` (`form_number`),
  ADD KEY `idx_submission_type` (`submission_type`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_received_date` (`received_date`),
  ADD KEY `idx_tentative_date` (`tentative_date`),
  ADD KEY `idx_report_ref` (`report_ref`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_status_client` (`status`,`client_id`),
  ADD KEY `idx_city_id` (`city_id`),
  ADD KEY `idx_received_datetime` (`received_date`,`received_time`);

--
-- Indexes for table `sample_acceptance`
--
ALTER TABLE `sample_acceptance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sample_id` (`sample_id`);

--
-- Indexes for table `sample_acknowledgement`
--
ALTER TABLE `sample_acknowledgement`
  ADD PRIMARY KEY (`ack_id`),
  ADD KEY `sample_id` (`sample_id`),
  ADD KEY `idx_report_ref` (`report_ref`);

--
-- Indexes for table `sample_extra_items`
--
ALTER TABLE `sample_extra_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sample_id` (`sample_id`),
  ADD KEY `idx_item_id` (`item_id`);

--
-- Indexes for table `sample_items`
--
ALTER TABLE `sample_items`
  ADD PRIMARY KEY (`sample_item_id`),
  ADD KEY `idx_sample_id` (`sample_id`),
  ADD KEY `idx_sample_category_id` (`sample_category_id`);

--
-- Indexes for table `sample_names`
--
ALTER TABLE `sample_names`
  ADD PRIMARY KEY (`sample_name_id`),
  ADD UNIQUE KEY `sample_name` (`sample_name`),
  ADD UNIQUE KEY `unique_sample_name` (`sample_name`),
  ADD KEY `idx_sample_name` (`sample_name`),
  ADD KEY `idx_sample_name_search` (`sample_name`(50)),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indexes for table `sample_status_log`
--
ALTER TABLE `sample_status_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_sample_id` (`sample_id`),
  ADD KEY `idx_updated_at` (`updated_at`),
  ADD KEY `idx_new_status` (`new_status`);

--
-- Indexes for table `sample_tests`
--
ALTER TABLE `sample_tests`
  ADD PRIMARY KEY (`sample_test_id`),
  ADD KEY `parameter_id` (`parameter_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `test_method_id` (`test_method_id`),
  ADD KEY `idx_sample_item_id` (`sample_item_id`),
  ADD KEY `idx_combo_id` (`combo_id`),
  ADD KEY `idx_parameter_id` (`parameter_id`);

--
-- Indexes for table `sample_test_results`
--
ALTER TABLE `sample_test_results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `uniq_sample_test` (`sample_test_id`),
  ADD KEY `idx_item` (`sample_item_id`),
  ADD KEY `idx_param` (`parameter_id`),
  ADD KEY `fk_str_pv` (`variant_id`);

--
-- Indexes for table `sample_type_categories`
--
ALTER TABLE `sample_type_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `unique_category_code` (`category_code`);

--
-- Indexes for table `swab_combos`
--
ALTER TABLE `swab_combos`
  ADD PRIMARY KEY (`combo_id`);

--
-- Indexes for table `swab_combo_items`
--
ALTER TABLE `swab_combo_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `combo_param_unique` (`combo_id`,`param_id`),
  ADD KEY `fk_swab_combo` (`combo_id`),
  ADD KEY `fk_swab_combo_param` (`param_id`);

--
-- Indexes for table `swab_param`
--
ALTER TABLE `swab_param`
  ADD PRIMARY KEY (`swab_param_id`),
  ADD KEY `param_id` (`param_id`);

--
-- Indexes for table `test_methods`
--
ALTER TABLE `test_methods`
  ADD PRIMARY KEY (`method_id`);

--
-- Indexes for table `test_parameters`
--
ALTER TABLE `test_parameters`
  ADD PRIMARY KEY (`parameter_id`),
  ADD UNIQUE KEY `parameter_code` (`parameter_code`),
  ADD KEY `fk_method_id` (`method_id`),
  ADD KEY `idx_param_name_pattern` (`parameter_name`(100),`is_active`,`is_deleted`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accreditation_certificates`
--
ALTER TABLE `accreditation_certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `base_units`
--
ALTER TABLE `base_units`
  MODIFY `base_unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `base_unit_categories`
--
ALTER TABLE `base_unit_categories`
  MODIFY `base_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `city_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=459;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `combination_items`
--
ALTER TABLE `combination_items`
  MODIFY `combo_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `combination_pricing`
--
ALTER TABLE `combination_pricing`
  MODIFY `combo_pricing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `extra_items`
--
ALTER TABLE `extra_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `final_test_reports`
--
ALTER TABLE `final_test_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `parameter_base_unit_config`
--
ALTER TABLE `parameter_base_unit_config`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=382;

--
-- AUTO_INCREMENT for table `parameter_category_methods`
--
ALTER TABLE `parameter_category_methods`
  MODIFY `pcm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=268;

--
-- AUTO_INCREMENT for table `parameter_combinations`
--
ALTER TABLE `parameter_combinations`
  MODIFY `combo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `parameter_methods`
--
ALTER TABLE `parameter_methods`
  MODIFY `parameter_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `parameter_pricing`
--
ALTER TABLE `parameter_pricing`
  MODIFY `pricing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `parameter_variants`
--
ALTER TABLE `parameter_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `printed_forms`
--
ALTER TABLE `printed_forms`
  MODIFY `print_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `print_history`
--
ALTER TABLE `print_history`
  MODIFY `print_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_items`
--
ALTER TABLE `report_items`
  MODIFY `report_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=288;

--
-- AUTO_INCREMENT for table `report_logos`
--
ALTER TABLE `report_logos`
  MODIFY `logo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `report_signatories`
--
ALTER TABLE `report_signatories`
  MODIFY `signatory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `samples`
--
ALTER TABLE `samples`
  MODIFY `sample_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sample_acceptance`
--
ALTER TABLE `sample_acceptance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sample_acknowledgement`
--
ALTER TABLE `sample_acknowledgement`
  MODIFY `ack_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sample_extra_items`
--
ALTER TABLE `sample_extra_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sample_items`
--
ALTER TABLE `sample_items`
  MODIFY `sample_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `sample_names`
--
ALTER TABLE `sample_names`
  MODIFY `sample_name_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `sample_status_log`
--
ALTER TABLE `sample_status_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `sample_tests`
--
ALTER TABLE `sample_tests`
  MODIFY `sample_test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `sample_test_results`
--
ALTER TABLE `sample_test_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `sample_type_categories`
--
ALTER TABLE `sample_type_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `swab_combos`
--
ALTER TABLE `swab_combos`
  MODIFY `combo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `swab_combo_items`
--
ALTER TABLE `swab_combo_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `swab_param`
--
ALTER TABLE `swab_param`
  MODIFY `swab_param_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `test_methods`
--
ALTER TABLE `test_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `test_parameters`
--
ALTER TABLE `test_parameters`
  MODIFY `parameter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `base_units`
--
ALTER TABLE `base_units`
  ADD CONSTRAINT `base_units_ibfk_1` FOREIGN KEY (`base_category_id`) REFERENCES `base_unit_categories` (`base_category_id`) ON UPDATE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `fk_client_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`city_id`) ON DELETE SET NULL;

--
-- Constraints for table `combination_items`
--
ALTER TABLE `combination_items`
  ADD CONSTRAINT `combination_items_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `parameter_combinations` (`combo_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `combination_items_ibfk_2` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON UPDATE CASCADE;

--
-- Constraints for table `combination_pricing`
--
ALTER TABLE `combination_pricing`
  ADD CONSTRAINT `combination_pricing_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `parameter_combinations` (`combo_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `final_test_reports`
--
ALTER TABLE `final_test_reports`
  ADD CONSTRAINT `final_test_reports_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`),
  ADD CONSTRAINT `final_test_reports_ibfk_2` FOREIGN KEY (`signatory_left_id`) REFERENCES `report_signatories` (`signatory_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `final_test_reports_ibfk_3` FOREIGN KEY (`signatory_right_id`) REFERENCES `report_signatories` (`signatory_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `final_test_reports_ibfk_4` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `parameter_base_unit_config`
--
ALTER TABLE `parameter_base_unit_config`
  ADD CONSTRAINT `fk_pbc_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `accreditation_certificates` (`certificate_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pbu_config_category` FOREIGN KEY (`base_category_id`) REFERENCES `base_unit_categories` (`base_category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pbu_config_param` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pbu_config_unit` FOREIGN KEY (`base_unit_id`) REFERENCES `base_units` (`base_unit_id`) ON UPDATE CASCADE;

--
-- Constraints for table `parameter_category_methods`
--
ALTER TABLE `parameter_category_methods`
  ADD CONSTRAINT `fk_pcm_config` FOREIGN KEY (`config_id`) REFERENCES `parameter_base_unit_config` (`config_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pcm_method` FOREIGN KEY (`method_id`) REFERENCES `test_methods` (`method_id`) ON UPDATE CASCADE;

--
-- Constraints for table `parameter_methods`
--
ALTER TABLE `parameter_methods`
  ADD CONSTRAINT `parameter_methods_ibfk_1` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parameter_methods_ibfk_2` FOREIGN KEY (`method_id`) REFERENCES `test_methods` (`method_id`) ON DELETE CASCADE;

--
-- Constraints for table `parameter_pricing`
--
ALTER TABLE `parameter_pricing`
  ADD CONSTRAINT `parameter_pricing_ibfk_1` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON UPDATE CASCADE;

--
-- Constraints for table `parameter_variants`
--
ALTER TABLE `parameter_variants`
  ADD CONSTRAINT `parameter_variants_ibfk_1` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `printed_forms`
--
ALTER TABLE `printed_forms`
  ADD CONSTRAINT `printed_forms_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE;

--
-- Constraints for table `print_history`
--
ALTER TABLE `print_history`
  ADD CONSTRAINT `print_history_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `print_history_ibfk_2` FOREIGN KEY (`printed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `report_items`
--
ALTER TABLE `report_items`
  ADD CONSTRAINT `report_items_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `final_test_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_items_ibfk_2` FOREIGN KEY (`sample_item_id`) REFERENCES `sample_items` (`sample_item_id`);

--
-- Constraints for table `samples`
--
ALTER TABLE `samples`
  ADD CONSTRAINT `fk_sample_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`city_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `samples_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Constraints for table `sample_acceptance`
--
ALTER TABLE `sample_acceptance`
  ADD CONSTRAINT `sample_acceptance_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE;

--
-- Constraints for table `sample_acknowledgement`
--
ALTER TABLE `sample_acknowledgement`
  ADD CONSTRAINT `sample_acknowledgement_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE;

--
-- Constraints for table `sample_items`
--
ALTER TABLE `sample_items`
  ADD CONSTRAINT `sample_items_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE;

--
-- Constraints for table `sample_status_log`
--
ALTER TABLE `sample_status_log`
  ADD CONSTRAINT `fk_status_log_sample` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE;

--
-- Constraints for table `sample_tests`
--
ALTER TABLE `sample_tests`
  ADD CONSTRAINT `fk_combo_id` FOREIGN KEY (`combo_id`) REFERENCES `parameter_combinations` (`combo_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sample_tests_combo` FOREIGN KEY (`combo_id`) REFERENCES `parameter_combinations` (`combo_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sample_tests_ibfk_1` FOREIGN KEY (`sample_item_id`) REFERENCES `sample_items` (`sample_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sample_tests_ibfk_2` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`),
  ADD CONSTRAINT `sample_tests_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `parameter_variants` (`variant_id`),
  ADD CONSTRAINT `sample_tests_ibfk_4` FOREIGN KEY (`test_method_id`) REFERENCES `test_methods` (`method_id`);

--
-- Constraints for table `sample_test_results`
--
ALTER TABLE `sample_test_results`
  ADD CONSTRAINT `fk_str_pv` FOREIGN KEY (`variant_id`) REFERENCES `parameter_variants` (`variant_id`),
  ADD CONSTRAINT `fk_str_si` FOREIGN KEY (`sample_item_id`) REFERENCES `sample_items` (`sample_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_str_st` FOREIGN KEY (`sample_test_id`) REFERENCES `sample_tests` (`sample_test_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_str_tp` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`);

--
-- Constraints for table `swab_combo_items`
--
ALTER TABLE `swab_combo_items`
  ADD CONSTRAINT `fk_swab_combo` FOREIGN KEY (`combo_id`) REFERENCES `swab_combos` (`combo_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_swab_combo_param` FOREIGN KEY (`param_id`) REFERENCES `test_parameters` (`parameter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `swab_param`
--
ALTER TABLE `swab_param`
  ADD CONSTRAINT `swab_param_ibfk_1` FOREIGN KEY (`param_id`) REFERENCES `test_parameters` (`parameter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `test_parameters`
--
ALTER TABLE `test_parameters`
  ADD CONSTRAINT `fk_method_id` FOREIGN KEY (`method_id`) REFERENCES `test_methods` (`method_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
