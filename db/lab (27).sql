-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 10:59 AM
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
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(200) NOT NULL,
  `address_line1` varchar(200) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
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

INSERT INTO `clients` (`client_id`, `client_name`, `address_line1`, `city`, `phone_primary`, `contact_person`, `registration_date`, `is_Active`, `created_at`, `updated_at`) VALUES
(3, 'ABCD Pvt Ltd', '125/35/01, Akkara 29, Bombuwala, Kalutara', 'Colombo', '0341212123', 'Naveen', '2025-11-03', 0, '2025-11-03 04:47:10', '2025-11-03 06:38:47'),
(4, 'ABC Pvt Ltd', '125/35/01, Akkara 29, Bombuwala, Kalutara South', 'Kaluthara', '0111213244', 'Naveen', '2025-11-03', 1, '2025-11-03 06:07:48', '2025-11-03 06:27:15'),
(5, 'Alpex Pvt Ltd', 'Colombo 15', 'Colombo', '0111314321', 'Kavidu Naveen', '2025-11-03', 1, '2025-11-03 06:30:18', '2025-12-14 13:10:22'),
(6, 'KNJ Lab', 'Kalutara', 'Kaluthara', '0763740019', 'Kavidu Naveen', '2025-12-14', 1, '2025-12-14 09:05:58', '2025-12-15 04:01:59');

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
(1, 1, 5, 0, '2025-11-13 06:18:36'),
(2, 1, 6, 1, '2025-11-13 06:18:36'),
(3, 2, 5, 0, '2025-11-17 09:45:38'),
(4, 2, 7, 1, '2025-11-17 09:45:38'),
(5, 2, 6, 2, '2025-11-17 09:45:38'),
(6, 3, 2, 0, '2025-11-17 09:46:42'),
(7, 3, 3, 1, '2025-11-17 09:46:42'),
(8, 4, 2, 0, '2025-11-17 09:47:09'),
(9, 4, 4, 1, '2025-11-17 09:47:09'),
(10, 4, 3, 2, '2025-11-17 09:47:09');

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
(3, 3, 1250.00, 1, 0, '2025-11-17 09:46:42', NULL),
(4, 4, 1375.00, 1, 0, '2025-11-17 09:47:10', NULL);

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
(2025, 7);

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
(1, 'Food – Coliforms + Food – Faecal coliforms', 'COMBO-001', NULL, 1, 0, '2025-11-13 06:18:36', NULL),
(2, 'Food – Coliforms + Food – E. coli + Food – Faecal coliforms', 'COMBO-002', NULL, 1, 0, '2025-11-17 09:45:38', NULL),
(3, 'Water and Ice – Coliforms + Water and Ice – Faecal coliforms', 'COMBO-003', NULL, 1, 0, '2025-11-17 09:46:42', NULL),
(4, 'Water and Ice – Coliforms + Water and Ice – E. coli + Water and Ice – Faecal coliforms', 'COMBO-004', NULL, 1, 0, '2025-11-17 09:47:09', NULL);

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
(1, 1, 1, 1, 0, '2025-11-12 08:35:09'),
(4, 4, 2, 1, 0, '2025-11-12 08:36:14'),
(6, 6, 4, 1, 0, '2025-11-12 08:37:04'),
(7, 8, 5, 1, 0, '2025-11-12 08:37:44'),
(8, 9, 5, 1, 0, '2025-11-12 08:37:50'),
(9, 10, 6, 1, 0, '2025-11-12 08:38:08'),
(10, 10, 7, 0, 1, '2025-11-12 08:38:09'),
(11, 11, 8, 1, 0, '2025-11-12 08:38:49'),
(14, 14, 11, 1, 0, '2025-11-12 08:40:54'),
(15, 15, 12, 1, 0, '2025-11-12 08:41:07'),
(16, 16, 13, 1, 0, '2025-11-12 08:41:20'),
(17, 13, 10, 1, 0, '2025-11-12 08:47:46'),
(18, 7, 4, 1, 0, '2025-11-12 08:48:58'),
(19, 5, 3, 1, 0, '2025-11-12 08:50:45'),
(20, 12, 9, 1, 0, '2025-11-12 08:54:20'),
(21, 2, 2, 1, 0, '2025-12-14 07:28:11'),
(22, 3, 2, 1, 0, '2025-12-14 07:28:14');

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
(1, 1, 1250.00, 1, 0, '2025-11-13 05:52:41', NULL),
(2, 11, 2625.00, 1, 0, '2025-11-13 06:02:41', '2025-11-13 06:03:50'),
(3, 5, 1125.00, 1, 0, '2025-11-13 06:21:12', NULL),
(4, 6, 1250.00, 1, 0, '2025-11-13 06:22:11', NULL),
(5, 2, 1125.00, 1, 0, '2025-11-17 09:40:38', NULL),
(6, 7, 1375.00, 1, 0, '2025-11-17 09:41:16', NULL),
(7, 13, 2750.00, 1, 0, '2025-11-17 09:41:40', NULL),
(8, 10, 2800.00, 1, 0, '2025-11-17 09:42:13', NULL),
(9, 15, 2375.00, 1, 0, '2025-11-17 09:42:37', NULL),
(10, 8, 2500.00, 1, 0, '2025-11-17 09:42:56', NULL),
(11, 9, 2500.00, 1, 0, '2025-11-17 09:43:09', NULL),
(12, 14, 2125.00, 1, 0, '2025-11-17 09:43:19', NULL),
(13, 4, 1375.00, 1, 0, '2025-11-17 09:44:01', NULL),
(14, 3, 1250.00, 1, 0, '2025-11-17 09:44:13', NULL),
(15, 16, 1375.00, 1, 0, '2025-11-17 09:44:56', NULL);

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
(1, 1, '37°C', '', 1, 0, '2025-11-06 04:59:57', '2025-11-17 09:15:48'),
(2, 1, '30°C', '', 1, 0, '2025-11-06 05:13:05', '2025-11-17 09:15:44'),
(3, 1, '22°C', '', 1, 0, '2025-11-06 05:28:22', '2025-11-18 05:08:52');

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
-- Table structure for table `samples`
--

CREATE TABLE `samples` (
  `sample_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `sample_code` varchar(50) NOT NULL,
  `form_number` varchar(20) NOT NULL,
  `report_ref` varchar(20) DEFAULT NULL COMMENT 'Root form number (25/0001/03)',
  `submission_type` enum('regular','swab') NOT NULL,
  `received_date` date NOT NULL,
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
  `forms_generated` tinyint(1) DEFAULT 0,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `samples`
--

INSERT INTO `samples` (`sample_id`, `client_id`, `sample_code`, `form_number`, `report_ref`, `submission_type`, `received_date`, `tentative_date`, `submitted_by`, `additional_notes`, `additional_charges`, `test_charges_total`, `grand_total`, `payment_status`, `payment_reference`, `status`, `status_updated_at`, `status_updated_by`, `payment_date`, `forms_generated`, `generated_at`, `created_at`) VALUES
(1, 6, '25/0001/01', '25/0001/01', '25/0001', 'regular', '2025-12-15', '2025-12-22', 'Kavidu Naveen', 'Do your best', 100.00, 2500.00, 2600.00, 'Not Paid', NULL, 'Pending', NULL, NULL, NULL, 0, NULL, '2025-12-15 04:10:45'),
(2, 6, '25/0002/01', '25/0002/01', '25/0002', 'regular', '2025-12-15', '2025-12-23', 'Kavidu Naveen', 'Test', 0.00, 2375.00, 2375.00, 'Not Paid', NULL, 'Pending', NULL, NULL, NULL, 0, NULL, '2025-12-15 04:13:08'),
(3, 6, '25/0003/01', '25/0003/01', '25/0003', 'regular', '2025-12-15', '2025-12-22', 'Kavidu Naveen', '', 0.00, 2500.00, 2500.00, 'Not Paid', NULL, 'Pending', NULL, NULL, NULL, 0, NULL, '2025-12-15 04:15:13'),
(4, 6, '25/0004/01', '25/0004/01', '25/0004', 'regular', '2025-12-15', '2025-12-22', 'Kavidu Naveen', 'Test', 0.00, 1250.00, 1250.00, 'Not Paid', NULL, 'Pending', '2025-12-15 08:28:28', 'Kavidu Naveen', NULL, 0, NULL, '2025-12-15 08:28:28'),
(5, 6, '25/0005/01', '25/0005/01', '25/0005', 'regular', '2025-12-15', '2025-12-22', 'Kavidu Naveen', 'Test', 100.00, 1250.00, 1350.00, 'Paid', 'TEST-003', 'Pending', '2025-12-15 08:56:39', 'Kavidu Naveen', NULL, 0, NULL, '2025-12-15 08:56:39'),
(6, 6, '25/0006/01', '25/0006/01', '25/0006', 'regular', '2025-12-15', '2025-12-22', 'Kavidu Naveen', '', 0.00, 2625.00, 2625.00, 'Not Paid', NULL, 'Pending', '2025-12-15 09:07:17', 'Kavidu Naveen', NULL, 0, NULL, '2025-12-15 09:07:17'),
(7, 5, '25/0007/01', '25/0007/01', '25/0007', 'regular', '2025-12-15', '2025-12-22', 'Kavidu Naveen', '', 0.00, 2625.00, 2625.00, 'Not Paid', NULL, 'Pending', '2025-12-15 09:53:08', 'Kavidu Naveen', NULL, 0, NULL, '2025-12-15 09:53:08');

-- --------------------------------------------------------

--
-- Table structure for table `sample_acceptance`
--

CREATE TABLE `sample_acceptance` (
  `id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `report_ref` varchar(20) NOT NULL,
  `received_by` varchar(150) NOT NULL,
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

INSERT INTO `sample_acceptance` (`id`, `sample_id`, `report_ref`, `received_by`, `container_damage`, `temperature_condition`, `validity_ok`, `tentative_date`, `remarks`, `created_at`) VALUES
(1, 1, 'AC/25/0001/01', 'Kavidu Naveen', 'No', 'Ambient', 'OK', '2025-12-22', NULL, '2025-12-15 04:10:45'),
(2, 2, 'AC/25/0002/01', 'Kavidu Naveen', 'No', 'Ambient', 'OK', '2025-12-23', NULL, '2025-12-15 04:13:08'),
(3, 3, 'AC/25/0003/01', 'Kavidu Naveen', 'No', 'Ambient', 'OK', '2025-12-22', NULL, '2025-12-15 04:15:13'),
(4, 4, 'AC/25/0004/01', 'Kavidu Naveen', 'No', 'Ambient', 'OK', '2025-12-22', NULL, '2025-12-15 08:28:28'),
(5, 5, 'AC/25/0005/01', 'Kavidu Naveen', 'No', 'Ambient', 'OK', '2025-12-22', NULL, '2025-12-15 08:56:39'),
(6, 6, 'AC/25/0006/01', 'Kavidu Naveen', 'No', 'Ambient', 'OK', '2025-12-22', NULL, '2025-12-15 09:07:18'),
(7, 7, 'AC/25/0007/01', 'Kavidu Naveen', 'No', 'Ambient', 'OK', '2025-12-22', NULL, '2025-12-15 09:53:09');

-- --------------------------------------------------------

--
-- Table structure for table `sample_acknowledgement`
--

CREATE TABLE `sample_acknowledgement` (
  `ack_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `report_ref` varchar(20) NOT NULL COMMENT 'AC/25/0001/03 format',
  `receipt_no` varchar(50) DEFAULT NULL,
  `test_charges` decimal(12,2) DEFAULT 0.00,
  `additional_charges` decimal(12,2) DEFAULT 0.00,
  `total_charges` decimal(12,2) DEFAULT 0.00,
  `payment_status` enum('Paid','Not Paid','Pending') DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `acknowledged_by` varchar(200) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_acknowledgement`
--

INSERT INTO `sample_acknowledgement` (`ack_id`, `sample_id`, `report_ref`, `receipt_no`, `test_charges`, `additional_charges`, `total_charges`, `payment_status`, `payment_method`, `payment_reference`, `acknowledged_by`, `acknowledged_at`, `notes`, `created_at`) VALUES
(1, 1, 'AC/25/0001/01', NULL, 2500.00, 100.00, 2600.00, 'Not Paid', NULL, NULL, 'Kavidu Naveen', '2025-12-15 04:10:46', 'Do your best', '2025-12-15 04:10:46'),
(2, 2, 'AC/25/0002/01', NULL, 2375.00, 0.00, 2375.00, 'Not Paid', NULL, NULL, 'Kavidu Naveen', '2025-12-15 04:13:08', 'Test', '2025-12-15 04:13:08'),
(3, 3, 'AC/25/0003/01', NULL, 2500.00, 0.00, 2500.00, 'Not Paid', NULL, NULL, 'Kavidu Naveen', '2025-12-15 04:15:13', '', '2025-12-15 04:15:13'),
(4, 4, 'AC/25/0004/01', NULL, 1250.00, 0.00, 1250.00, 'Not Paid', NULL, NULL, 'Kavidu Naveen', '2025-12-15 08:28:28', 'Test', '2025-12-15 08:28:28'),
(5, 5, 'AC/25/0005/01', NULL, 1250.00, 100.00, 1350.00, 'Paid', NULL, 'TEST-003', 'Kavidu Naveen', '2025-12-15 08:56:39', 'Test', '2025-12-15 08:56:39'),
(6, 6, 'AC/25/0006/01', NULL, 2625.00, 0.00, 2625.00, 'Not Paid', NULL, NULL, 'Kavidu Naveen', '2025-12-15 09:07:18', '', '2025-12-15 09:07:18'),
(7, 7, 'AC/25/0007/01', NULL, 2625.00, 0.00, 2625.00, 'Not Paid', NULL, NULL, 'Kavidu Naveen', '2025-12-15 09:53:09', '', '2025-12-15 09:53:09');

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
  `validity_status` enum('OK','Damaged','Expired') DEFAULT 'OK',
  `sequence_number` int(11) NOT NULL,
  `item_total_charge` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_items`
--

INSERT INTO `sample_items` (`sample_item_id`, `sample_id`, `sample_name`, `value`, `unit`, `client_sample_code`, `sampling_location`, `reason_for_analysis`, `container_damage`, `temperature_condition`, `validity_status`, `sequence_number`, `item_total_charge`, `created_at`) VALUES
(1, 1, 'Drinking Water', '100', 'ml', 'T-01', 'Office tap', 'Drink', 'No', 'Ambient', 'OK', 1, 0.00, '2025-12-15 04:10:45'),
(2, 2, 'Shrimp', '100', 'g', 'T-02', 'Tank', 'Export', 'No', 'Ambient', 'OK', 1, 0.00, '2025-12-15 04:13:08'),
(3, 3, 'Fish', '200', 'g', 'T-03', 'Tank', 'Sell', 'No', 'Ambient', 'OK', 1, 0.00, '2025-12-15 04:15:13'),
(4, 4, 'Drinking Water', '250', 'ml', 'TW-01', 'Tap', 'Sell', 'No', 'Ambient', 'OK', 1, 0.00, '2025-12-15 08:28:28'),
(5, 5, 'Drinking Water', '100', 'ml', 'TSW-01', 'Tap', 'Sell water', 'No', 'Ambient', 'OK', 1, 0.00, '2025-12-15 08:56:39'),
(6, 6, 'Drinking Water', '100', 'ml', 'T-002', '', '', 'No', 'Ambient', 'OK', 1, 0.00, '2025-12-15 09:07:18'),
(7, 7, 'Drinking Water', '490', 'ml', '', '', '', 'No', 'Ambient', 'OK', 1, 0.00, '2025-12-15 09:53:08');

--
-- Triggers `sample_items`
--
DELIMITER $$
CREATE TRIGGER `after_sample_item_insert` AFTER INSERT ON `sample_items` FOR EACH ROW BEGIN
    
    INSERT INTO sample_names (sample_name, usage_count, created_at)
    VALUES (NEW.sample_name, 1, NOW())
    ON DUPLICATE KEY UPDATE 
        usage_count = usage_count + 1,
        updated_at = NOW();
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
  `usage_count` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sample_names`
--

INSERT INTO `sample_names` (`sample_name_id`, `sample_name`, `usage_count`, `created_at`, `updated_at`) VALUES
(1, 'Drinking Water', 17, '2025-11-20 10:05:02', '2025-12-15 09:53:08'),
(2, 'Ice Cubes', 4, '2025-12-07 17:07:24', '2025-12-14 09:08:15'),
(3, 'Surface Swab', 4, '2025-12-07 17:07:24', NULL),
(4, 'Food Sample', 2, '2025-12-07 17:07:24', NULL),
(5, 'Treated Water', 2, '2025-12-07 17:07:24', '2025-12-15 03:53:36'),
(19, 'Shrimp', 1, '2025-12-15 04:13:08', NULL),
(20, 'Fish', 1, '2025-12-15 04:15:13', NULL);

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
(1, NULL, 1, 1, 3, 1, 1250.00, 0, 0, '2025-12-15 04:10:45'),
(2, NULL, 1, 1, 2, 1, 1250.00, 0, 0, '2025-12-15 04:10:45'),
(3, 1, 2, 5, NULL, 3, 625.00, 0, 1, '2025-12-15 04:13:08'),
(4, 1, 2, 6, NULL, 4, 625.00, 0, 1, '2025-12-15 04:13:08'),
(5, NULL, 3, 5, NULL, 3, 1125.00, 0, 0, '2025-12-15 04:15:13'),
(6, NULL, 3, 7, NULL, 4, 1375.00, 0, 0, '2025-12-15 04:15:13'),
(7, NULL, 4, 1, NULL, 1, 1250.00, 0, 0, '2025-12-15 08:28:28'),
(8, NULL, 5, 1, 3, 1, 1250.00, 0, 0, '2025-12-15 08:56:39'),
(9, 3, 6, 2, NULL, 2, 1250.00, 0, 1, '2025-12-15 09:07:18'),
(10, 3, 6, 3, NULL, 2, 1250.00, 0, 1, '2025-12-15 09:07:18'),
(11, 4, 6, 4, NULL, 2, 1375.00, 0, 1, '2025-12-15 09:07:18'),
(12, 3, 7, 2, NULL, 2, 1250.00, 0, 1, '2025-12-15 09:53:09'),
(13, 3, 7, 3, NULL, 2, 1250.00, 0, 1, '2025-12-15 09:53:09'),
(14, 4, 7, 4, NULL, 2, 1375.00, 0, 1, '2025-12-15 09:53:09');

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
(1, 1, 375.00, 1, 0, '2025-11-13 05:53:35', '2025-11-13 05:53:35'),
(2, 11, 375.00, 1, 0, '2025-11-13 06:03:33', '2025-11-13 06:03:33'),
(3, 5, 375.00, 1, 0, '2025-11-13 06:19:57', '2025-11-13 06:19:57'),
(4, 6, 375.00, 1, 0, '2025-11-13 06:22:36', '2025-11-13 06:22:36'),
(5, 7, 375.00, 1, 0, '2025-12-14 07:23:45', '2025-12-14 07:23:45'),
(6, 4, 375.00, 1, 0, '2025-12-14 07:23:57', '2025-12-14 07:23:57'),
(7, 13, 375.00, 1, 0, '2025-12-14 07:24:04', '2025-12-14 07:24:04'),
(8, 10, 375.00, 1, 0, '2025-12-14 07:24:12', '2025-12-14 07:24:12'),
(9, 2, 375.00, 1, 0, '2025-12-14 07:28:11', '2025-12-14 07:29:18'),
(10, 3, 375.00, 1, 0, '2025-12-14 07:28:14', '2025-12-14 07:29:05');

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
(1, 'SLS 516 Part 1/Sec 1:2013', 'SLS', 1, 0, '2025-11-10 10:04:47', '2025-11-10 10:11:44'),
(2, 'SLS 1461 Part 1/Sec 3:2013', 'SLS', 1, 0, '2025-11-11 09:38:09', NULL),
(3, 'SLS 516 Part 3/Sec 1:2013', 'SLS', 1, 0, '2025-11-11 09:38:46', '2025-11-12 08:52:09'),
(4, 'SLS 516 Part 12:2013', 'SLS', 1, 0, '2025-11-11 09:39:18', NULL),
(5, 'SLS 516 Part 7/Sec 1:2017', 'SLS', 1, 0, '2025-11-11 09:39:44', NULL),
(6, 'SLS 516: Part 5: 2017', 'SLS', 1, 0, '2025-11-11 09:40:56', NULL),
(7, 'ISO 19250:2010 (E)', 'ISO', 1, 0, '2025-11-11 09:41:29', NULL),
(8, 'SLS 516 Part 6/Sec 1:2013', 'SLS', 1, 0, '2025-11-11 09:41:49', NULL),
(9, 'SLS 516 Part 4: 1982', 'SLS', 1, 0, '2025-11-11 09:42:24', NULL),
(10, 'ISO:11920 - 1:1996', 'ISO', 1, 0, '2025-11-11 09:44:33', NULL),
(11, 'APHA:2001', 'APHA', 1, 0, '2025-11-11 09:46:38', NULL),
(12, 'ISO 6461:1986', 'ISO', 1, 0, '2025-11-11 09:47:20', NULL),
(13, 'SLS 516 Part 2/Sec 1:2013', 'SLS', 1, 0, '2025-11-11 09:47:34', '2025-11-11 09:53:45'),
(14, 'SLS 516 Part 2/Sec 1:2013', 'APHA', 1, 1, '2025-11-11 09:49:33', '2025-11-11 10:00:24'),
(15, 'SLS 516 Part 2/Sec 1:2013', '', 1, 1, '2025-11-11 09:59:58', '2025-11-11 10:00:18');

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
  `swab_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_parameters`
--

INSERT INTO `test_parameters` (`parameter_id`, `parameter_code`, `method_id`, `parameter_name`, `parameter_category`, `base_unit`, `has_variants`, `swab_enabled`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'A', NULL, 'Aerobic Plate Count', '', 'cfu/g', 0, 1, 1, 0, '2025-11-04 04:08:54', '2025-11-12 08:35:09'),
(2, 'B', NULL, 'Water and Ice – Coliforms', '', 'MPN/100ml', 0, 1, 1, 0, '2025-11-04 04:10:06', '2025-12-14 07:28:11'),
(3, 'C', NULL, 'Water and Ice – Faecal coliforms', '', 'MPN/100ml', 0, 1, 1, 0, '2025-11-04 04:13:03', '2025-12-14 07:28:14'),
(4, 'D', NULL, 'Water and Ice – E. coli', '', 'MPN/100ml', 0, 1, 1, 0, '2025-11-04 04:17:59', '2025-11-12 08:36:14'),
(5, 'E', NULL, 'Food – Coliforms', '', 'MPN/g', 0, 1, 1, 0, '2025-11-04 04:18:30', '2025-11-12 08:50:45'),
(6, 'F', NULL, 'Food – Faecal coliforms', '', 'MPN/g', 0, 1, 1, 0, '2025-11-04 04:19:04', '2025-11-12 08:37:04'),
(7, 'G', NULL, 'Food – E. coli', '', 'MPN/g', 0, 1, 1, 0, '2025-11-04 04:23:57', '2025-11-12 08:48:58'),
(8, 'H', NULL, 'Vibrio cholerae', '', '/25g /100ml', 0, 0, 1, 0, '2025-11-04 04:25:25', '2025-11-12 08:37:44'),
(9, 'I', NULL, 'Vibrio parahaemolyticus', '', '/g', 0, 0, 1, 0, '2025-11-04 04:30:31', '2025-11-12 08:37:50'),
(10, 'J', NULL, 'Salmonella spp.', '', '/25g /100ml', 0, 1, 1, 0, '2025-11-04 04:31:07', '2025-11-12 08:38:08'),
(11, 'K', NULL, 'Staphylococcus aureus', '', '/g', 0, 1, 1, 0, '2025-11-04 04:31:28', '2025-11-12 08:38:49'),
(12, 'L', NULL, 'Faecal Streptococci', '', 'MPN/ml', 0, 0, 1, 0, '2025-11-04 04:32:03', '2025-11-12 08:54:20'),
(13, 'M', NULL, 'Listeria monocytogenes', '', '/25g', 0, 1, 1, 0, '2025-11-04 04:32:32', '2025-11-12 08:47:46'),
(14, 'N', NULL, 'Vibrio spp.', '', 'cfu/g', 0, 0, 1, 0, '2025-11-04 04:33:00', '2025-11-12 08:40:54'),
(15, 'O', NULL, 'Sulphite reducing clostridia', '', 'MPN/100ml', 0, 0, 1, 0, '2025-11-04 04:33:36', '2025-11-12 08:41:07'),
(16, 'P', NULL, 'Yeasts and Moulds', '', 'cfu/g', 0, 0, 1, 0, '2025-11-04 04:33:54', '2025-11-12 08:41:20');

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
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `email`, `role`, `status`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'Kavidu Naveen', 'nav019', 'naveen.knj19@gmail.com', 'Admin', 'active', '$2y$10$U/GEQPTuqtsctunIU88/meJguH3wWys2TdYjvsakcPQq8iZR8idVu', '2025-12-06 17:41:34', '2025-12-06 17:41:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `idx_is_active` (`is_Active`),
  ADD KEY `idx_client_name` (`client_name`(50)),
  ADD KEY `idx_phone_search` (`phone_primary`),
  ADD KEY `idx_client_search` (`client_name`(100));

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
-- Indexes for table `form_sequence`
--
ALTER TABLE `form_sequence`
  ADD PRIMARY KEY (`year`);

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
  ADD KEY `method_id` (`method_id`);

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
  ADD KEY `parameter_id` (`parameter_id`);

--
-- Indexes for table `printed_forms`
--
ALTER TABLE `printed_forms`
  ADD PRIMARY KEY (`print_id`),
  ADD KEY `sample_id` (`sample_id`);

--
-- Indexes for table `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`sample_id`),
  ADD UNIQUE KEY `sample_code` (`sample_code`),
  ADD UNIQUE KEY `idx_unique_payment_ref` (`payment_reference`),
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
  ADD KEY `idx_status_client` (`status`,`client_id`);

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
-- Indexes for table `sample_items`
--
ALTER TABLE `sample_items`
  ADD PRIMARY KEY (`sample_item_id`),
  ADD KEY `idx_sample_id` (`sample_id`);

--
-- Indexes for table `sample_names`
--
ALTER TABLE `sample_names`
  ADD PRIMARY KEY (`sample_name_id`),
  ADD UNIQUE KEY `sample_name` (`sample_name`),
  ADD UNIQUE KEY `unique_sample_name` (`sample_name`),
  ADD KEY `idx_sample_name` (`sample_name`),
  ADD KEY `idx_sample_name_search` (`sample_name`(50));

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
  ADD KEY `fk_method_id` (`method_id`);

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
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `combination_items`
--
ALTER TABLE `combination_items`
  MODIFY `combo_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `combination_pricing`
--
ALTER TABLE `combination_pricing`
  MODIFY `combo_pricing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parameter_combinations`
--
ALTER TABLE `parameter_combinations`
  MODIFY `combo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parameter_methods`
--
ALTER TABLE `parameter_methods`
  MODIFY `parameter_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `parameter_pricing`
--
ALTER TABLE `parameter_pricing`
  MODIFY `pricing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- AUTO_INCREMENT for table `samples`
--
ALTER TABLE `samples`
  MODIFY `sample_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sample_acceptance`
--
ALTER TABLE `sample_acceptance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sample_acknowledgement`
--
ALTER TABLE `sample_acknowledgement`
  MODIFY `ack_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sample_items`
--
ALTER TABLE `sample_items`
  MODIFY `sample_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sample_names`
--
ALTER TABLE `sample_names`
  MODIFY `sample_name_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sample_status_log`
--
ALTER TABLE `sample_status_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_tests`
--
ALTER TABLE `sample_tests`
  MODIFY `sample_test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `swab_param`
--
ALTER TABLE `swab_param`
  MODIFY `swab_param_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `test_methods`
--
ALTER TABLE `test_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `test_parameters`
--
ALTER TABLE `test_parameters`
  MODIFY `parameter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `samples`
--
ALTER TABLE `samples`
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
