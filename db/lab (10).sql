-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 10:21 AM
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
-- Table structure for table `acceptance`
--

CREATE TABLE `acceptance` (
  `acceptance_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `accepted_by` varchar(200) DEFAULT NULL,
  `accepted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 'Alpex Pvt Ltd', 'Colombo 15', 'Colombo', '0111314321', 'Kavidu', '2025-11-03', 1, '2025-11-03 06:30:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `combination_items`
--

CREATE TABLE `combination_items` (
  `combo_item_id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `sequence_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `combination_pricing`
--

CREATE TABLE `combination_pricing` (
  `combo_pricing_id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `test_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2025, 3);

-- --------------------------------------------------------

--
-- Table structure for table `parameter_combinations`
--

CREATE TABLE `parameter_combinations` (
  `combo_id` int(11) NOT NULL,
  `combo_name` varchar(300) NOT NULL,
  `combo_code` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `applies_per_sample` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parameter_pricing`
--

CREATE TABLE `parameter_pricing` (
  `pricing_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `test_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, '37°C', '', 1, 0, '2025-11-06 04:59:57', '2025-11-10 04:04:35'),
(2, 1, '30°C', '', 1, 0, '2025-11-06 05:13:05', '2025-11-10 04:04:32'),
(3, 1, '22°C', '', 1, 0, '2025-11-06 05:28:22', '2025-11-10 04:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `samples`
--

CREATE TABLE `samples` (
  `sample_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `sample_code` varchar(50) NOT NULL,
  `received_date` date NOT NULL,
  `tentative_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sample_acknowledgement`
--

CREATE TABLE `sample_acknowledgement` (
  `ack_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `acknowledged_by` varchar(200) DEFAULT NULL,
  `acknowledged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sample_items`
--

CREATE TABLE `sample_items` (
  `sample_item_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sample_tests`
--

CREATE TABLE `sample_tests` (
  `sample_test_id` int(11) NOT NULL,
  `sample_item_id` int(11) NOT NULL,
  `test_method_id` int(11) NOT NULL,
  `charge` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 5000.00, 1, 0, '2025-11-10 04:42:54', '2025-11-10 04:42:54');

-- --------------------------------------------------------

--
-- Table structure for table `test_methods`
--

CREATE TABLE `test_methods` (
  `method_id` int(11) NOT NULL,
  `method_name` varchar(200) NOT NULL,
  `standard_body` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `default_method_id` int(11) DEFAULT NULL,
  `swab_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_parameters`
--

INSERT INTO `test_parameters` (`parameter_id`, `parameter_code`, `method_id`, `parameter_name`, `parameter_category`, `base_unit`, `has_variants`, `default_method_id`, `swab_enabled`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'A', NULL, 'Aerobic Plate Count', '', 'cfu/g', 0, NULL, 1, 1, 0, '2025-11-04 04:08:54', '2025-11-10 04:20:35'),
(2, 'B', NULL, 'Water and Ice – Coliforms', '', 'MPN/100ml', 0, NULL, 0, 1, 0, '2025-11-04 04:10:06', NULL),
(3, 'C', NULL, 'Water and Ice – Faecal coliforms', '', 'MPN/100ml', 0, NULL, 0, 1, 0, '2025-11-04 04:13:03', NULL),
(4, 'D', NULL, 'Water and Ice – E. coli', '', 'MPN/100ml', 0, NULL, 1, 1, 0, '2025-11-04 04:17:59', NULL),
(5, 'E', NULL, 'Food – Coliforms', '', 'MPN/g', 0, NULL, 1, 1, 0, '2025-11-04 04:18:30', NULL),
(6, 'F', NULL, 'Food – Faecal coliforms', '', 'MPN/g', 0, NULL, 1, 1, 0, '2025-11-04 04:19:04', NULL),
(7, 'G', NULL, 'Food – E. coli', '', 'MPN/g', 0, NULL, 1, 1, 0, '2025-11-04 04:23:57', NULL),
(8, 'H', NULL, 'Vibrio cholerae', '', '/25g /100ml', 0, NULL, 0, 1, 0, '2025-11-04 04:25:25', NULL),
(9, 'I', NULL, 'Vibrio parahaemolyticus', '', '/g', 0, NULL, 0, 1, 0, '2025-11-04 04:30:31', NULL),
(10, 'J', NULL, 'Salmonella spp.', '', '/25g /100ml', 0, NULL, 1, 1, 0, '2025-11-04 04:31:07', NULL),
(11, 'K', NULL, 'Staphylococcus aureus', '', '/g', 0, NULL, 1, 1, 0, '2025-11-04 04:31:28', NULL),
(12, 'L', NULL, 'Faecal Streptococci', '', 'MPN/ml', 0, NULL, 0, 1, 0, '2025-11-04 04:32:03', NULL),
(13, 'M', NULL, 'Listeria monocytogenes', '', '/25g', 0, NULL, 1, 1, 0, '2025-11-04 04:32:32', '2025-11-06 09:24:06'),
(14, 'N', NULL, 'Vibrio spp.', '', 'cfu/g', 0, NULL, 0, 1, 0, '2025-11-04 04:33:00', NULL),
(15, 'O', NULL, 'Sulphite reducing clostridia', '', 'MPN/100ml', 0, NULL, 0, 1, 0, '2025-11-04 04:33:36', NULL),
(16, 'P', NULL, 'Yeasts and Moulds', '', 'cfu/g', 0, NULL, 0, 1, 0, '2025-11-04 04:33:54', NULL);

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
(1, 'Kavidu Naveen', 'naveen_19', 'naveen.knj19@gmail.com', 'Admin', 'active', '$2y$10$d6K6LZ3Xtx15lsJ03PC.IuypkyvwUfO94D502K53MiXnbiuTP4R2W', '2025-11-10 05:19:45', '2025-11-10 05:20:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acceptance`
--
ALTER TABLE `acceptance`
  ADD PRIMARY KEY (`acceptance_id`),
  ADD KEY `sample_id` (`sample_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `combination_items`
--
ALTER TABLE `combination_items`
  ADD PRIMARY KEY (`combo_item_id`),
  ADD KEY `combo_id` (`combo_id`),
  ADD KEY `parameter_id` (`parameter_id`),
  ADD KEY `variant_id` (`variant_id`);

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
-- Indexes for table `parameter_pricing`
--
ALTER TABLE `parameter_pricing`
  ADD PRIMARY KEY (`pricing_id`),
  ADD KEY `parameter_id` (`parameter_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `parameter_variants`
--
ALTER TABLE `parameter_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `parameter_id` (`parameter_id`);

--
-- Indexes for table `samples`
--
ALTER TABLE `samples`
  ADD PRIMARY KEY (`sample_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `sample_acknowledgement`
--
ALTER TABLE `sample_acknowledgement`
  ADD PRIMARY KEY (`ack_id`),
  ADD KEY `sample_id` (`sample_id`);

--
-- Indexes for table `sample_items`
--
ALTER TABLE `sample_items`
  ADD PRIMARY KEY (`sample_item_id`),
  ADD KEY `sample_id` (`sample_id`),
  ADD KEY `parameter_id` (`parameter_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `sample_tests`
--
ALTER TABLE `sample_tests`
  ADD PRIMARY KEY (`sample_test_id`),
  ADD KEY `sample_item_id` (`sample_item_id`),
  ADD KEY `test_method_id` (`test_method_id`);

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
  ADD KEY `default_method_id` (`default_method_id`),
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
-- AUTO_INCREMENT for table `acceptance`
--
ALTER TABLE `acceptance`
  MODIFY `acceptance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `combination_items`
--
ALTER TABLE `combination_items`
  MODIFY `combo_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `combination_pricing`
--
ALTER TABLE `combination_pricing`
  MODIFY `combo_pricing_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parameter_combinations`
--
ALTER TABLE `parameter_combinations`
  MODIFY `combo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parameter_pricing`
--
ALTER TABLE `parameter_pricing`
  MODIFY `pricing_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parameter_variants`
--
ALTER TABLE `parameter_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `samples`
--
ALTER TABLE `samples`
  MODIFY `sample_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_acknowledgement`
--
ALTER TABLE `sample_acknowledgement`
  MODIFY `ack_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_items`
--
ALTER TABLE `sample_items`
  MODIFY `sample_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_tests`
--
ALTER TABLE `sample_tests`
  MODIFY `sample_test_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swab_param`
--
ALTER TABLE `swab_param`
  MODIFY `swab_param_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `test_methods`
--
ALTER TABLE `test_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `acceptance`
--
ALTER TABLE `acceptance`
  ADD CONSTRAINT `acceptance_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `combination_items`
--
ALTER TABLE `combination_items`
  ADD CONSTRAINT `combination_items_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `parameter_combinations` (`combo_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `combination_items_ibfk_2` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `combination_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `parameter_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `combination_pricing`
--
ALTER TABLE `combination_pricing`
  ADD CONSTRAINT `combination_pricing_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `parameter_combinations` (`combo_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `parameter_pricing`
--
ALTER TABLE `parameter_pricing`
  ADD CONSTRAINT `parameter_pricing_ibfk_1` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `parameter_pricing_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `parameter_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `parameter_variants`
--
ALTER TABLE `parameter_variants`
  ADD CONSTRAINT `parameter_variants_ibfk_1` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `samples`
--
ALTER TABLE `samples`
  ADD CONSTRAINT `samples_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON UPDATE CASCADE;

--
-- Constraints for table `sample_acknowledgement`
--
ALTER TABLE `sample_acknowledgement`
  ADD CONSTRAINT `sample_acknowledgement_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sample_items`
--
ALTER TABLE `sample_items`
  ADD CONSTRAINT `sample_items_ibfk_1` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`sample_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sample_items_ibfk_2` FOREIGN KEY (`parameter_id`) REFERENCES `test_parameters` (`parameter_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sample_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `parameter_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sample_tests`
--
ALTER TABLE `sample_tests`
  ADD CONSTRAINT `sample_tests_ibfk_1` FOREIGN KEY (`sample_item_id`) REFERENCES `sample_items` (`sample_item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sample_tests_ibfk_2` FOREIGN KEY (`test_method_id`) REFERENCES `test_methods` (`method_id`) ON UPDATE CASCADE;

--
-- Constraints for table `swab_param`
--
ALTER TABLE `swab_param`
  ADD CONSTRAINT `swab_param_ibfk_1` FOREIGN KEY (`param_id`) REFERENCES `test_parameters` (`parameter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `test_parameters`
--
ALTER TABLE `test_parameters`
  ADD CONSTRAINT `fk_method_id` FOREIGN KEY (`method_id`) REFERENCES `test_methods` (`method_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `test_parameters_ibfk_1` FOREIGN KEY (`default_method_id`) REFERENCES `test_methods` (`method_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
