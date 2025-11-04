-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 11:11 AM
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
-- Database: `inventory_sys`
--

-- --------------------------------------------------------

--
-- Table structure for table `asset_replacement_log`
--

CREATE TABLE `asset_replacement_log` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `replaced_quantity` int(11) NOT NULL,
  `replaced_at` datetime DEFAULT current_timestamp(),
  `replaced` int(11) NOT NULL,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(40) NOT NULL,
  `department` varchar(30) NOT NULL,
  `floor` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_replacement_log`
--

INSERT INTO `asset_replacement_log` (`id`, `asset_id`, `replaced_quantity`, `replaced_at`, `replaced`, `reg_no`, `asset_name`, `department`, `floor`) VALUES
(31, 74, 1, '2025-11-04 10:04:22', 1, 'ISL913', 'HP 820 G3', 'Nursing1', 'First Floor');

-- --------------------------------------------------------

--
-- Table structure for table `asset_table`
--

CREATE TABLE `asset_table` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(200) NOT NULL,
  `description` varchar(250) NOT NULL,
  `quantity` varchar(20) NOT NULL,
  `category` varchar(30) NOT NULL,
  `dateofpurchase` varchar(30) NOT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_table`
--

INSERT INTO `asset_table` (`id`, `reg_no`, `asset_name`, `description`, `quantity`, `category`, `dateofpurchase`, `updated_at`) VALUES
(23, 'ISL158', 'HP 840 G3', 'All are bought with good conditions', '4', 'Laptops', '2025-09-09', '2025-11-03'),
(24, 'ISL913', 'HP 820 G3', 'Buy in good condition', '10', 'Laptops', '2025-09-11', '2025-11-03'),
(29, 'ISL227', 'MAC BOOK', 'sfsfffsffe', '15', 'Laptops', '2025-10-04', '2025-11-03'),
(30, 'ISL761', 'HP Laserjet Black Printer', 'dsgdgergrg', '24', 'Printers', '2025-10-04', '2025-11-03');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_table`
--

CREATE TABLE `borrow_table` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(100) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `quantity` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `borrow_date` varchar(100) NOT NULL,
  `borrow_by` varchar(100) NOT NULL,
  `admin_borrow_for` varchar(30) NOT NULL,
  `hod_status` int(11) NOT NULL,
  `pro_status` int(11) NOT NULL,
  `returned` int(11) NOT NULL,
  `returned_date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `category`) VALUES
(5, 'Laptops'),
(13, 'Printers'),
(16, 'Desktops'),
(21, 'AC');

-- --------------------------------------------------------

--
-- Table structure for table `completed_asset`
--

CREATE TABLE `completed_asset` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `floor` varchar(30) NOT NULL,
  `completed` int(11) NOT NULL,
  `completed_date` varchar(30) NOT NULL,
  `reg_no` varchar(20) NOT NULL,
  `asset_name` varchar(30) NOT NULL,
  `department` varchar(30) NOT NULL,
  `reported_by` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `completed_asset`
--

INSERT INTO `completed_asset` (`id`, `asset_id`, `quantity`, `status`, `floor`, `completed`, `completed_date`, `reg_no`, `asset_name`, `department`, `reported_by`) VALUES
(56, 74, 1, 'Repair Completed', 'First Floor', 1, '2025-11-04 10:00:19', 'ISL913', 'HP 820 G3', 'Nursing1', 'Tobestic'),
(57, 75, 1, 'Repair Completed', 'Admin building', 1, '2025-11-04 10:03:11', 'ISL761', 'HP Laserjet Black Printer', 'Procurement', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `department_borrow_table`
--

CREATE TABLE `department_borrow_table` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) DEFAULT NULL,
  `asset_name` varchar(100) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `quantity` varchar(10) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `borrow_by_dept` varchar(100) NOT NULL,
  `borrow_date` varchar(100) DEFAULT NULL,
  `hod_name` varchar(100) NOT NULL,
  `hod_status` int(11) NOT NULL,
  `returned` int(11) NOT NULL,
  `return_date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department_table`
--

CREATE TABLE `department_table` (
  `id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `floor` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_table`
--

INSERT INTO `department_table` (`id`, `department`, `floor`) VALUES
(15, 'Nursing1', 'First Floor'),
(16, 'Nursing2', 'Second Floor'),
(17, 'Nursing3', 'Third Floor'),
(18, 'Nursing4', 'Fourth Floor'),
(19, 'Nursing5', 'Fifth Floor'),
(20, 'Nursing6', 'Sixth Floor'),
(21, 'Facility', 'Admin building'),
(22, 'Audit', 'Admin building'),
(23, 'Procurement', 'Admin building'),
(24, 'Information Technology', 'IT Office'),
(25, 'Account', 'Admin building');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_table`
--

CREATE TABLE `maintenance_table` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `category` varchar(20) NOT NULL,
  `department` varchar(30) NOT NULL,
  `last_service` varchar(20) NOT NULL,
  `next_service` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_table`
--

INSERT INTO `maintenance_table` (`id`, `reg_no`, `asset_name`, `description`, `category`, `department`, `last_service`, `next_service`) VALUES
(21, 'ISL761', 'HP Laserjet Black Printer', 'Refill', 'Printers', 'Procurement', '2025-11-04', '2026-01-03');

-- --------------------------------------------------------

--
-- Table structure for table `repair_asset`
--

CREATE TABLE `repair_asset` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `reg_no` varchar(100) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `reported_by` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `quantity` int(11) DEFAULT 1,
  `report_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Under Repair',
  `floor` varchar(30) NOT NULL,
  `completed_date` varchar(40) NOT NULL,
  `completed` int(11) NOT NULL,
  `withdrawn_date` varchar(30) NOT NULL,
  `withdrawn` int(11) NOT NULL,
  `withdrawn_reason` text NOT NULL,
  `replaced_date` varchar(30) NOT NULL,
  `replaced` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_asset`
--

INSERT INTO `repair_asset` (`id`, `asset_id`, `reg_no`, `asset_name`, `department`, `reported_by`, `description`, `category`, `quantity`, `report_date`, `status`, `floor`, `completed_date`, `completed`, `withdrawn_date`, `withdrawn`, `withdrawn_reason`, `replaced_date`, `replaced`) VALUES
(175, 74, 'ISL913', 'HP 820 G3', 'Nursing1', 'Tobestic', 'Marked for repair', 'General', 0, '2025-11-04 09:59:33', NULL, 'First Floor', '2025-11-04 10:00:19', 1, '', 0, '', '', 0),
(176, 74, 'ISL913', 'HP 820 G3', 'Nursing1', 'Odeyemi Oluwatobi', 'Marked for repair', 'General', 1, '2025-11-04 10:01:53', NULL, 'First Floor', '', 0, '2025-11-04 10:02:56', 1, 'Bad', '2025-11-04 10:04:22', 1),
(177, 75, 'ISL761', 'HP Laserjet Black Printer', 'Procurement', 'admin', 'Marked for repair', 'General', 0, '2025-11-04 10:02:11', NULL, 'Admin building', '2025-11-04 10:03:11', 1, '', 0, '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `request_table`
--

CREATE TABLE `request_table` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `quantity` varchar(10) NOT NULL,
  `category` varchar(30) NOT NULL,
  `department` varchar(30) NOT NULL,
  `assigned_employee` varchar(30) NOT NULL,
  `requested_by` varchar(30) NOT NULL,
  `request_date` varchar(30) NOT NULL,
  `hod_approved` int(2) NOT NULL,
  `pro_approved` int(2) NOT NULL,
  `approval_date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `user_role` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `user_role`) VALUES
(1, 'admin'),
(3, 'audit'),
(7, 'procurement'),
(8, 'facility'),
(9, 'account');

-- --------------------------------------------------------

--
-- Table structure for table `staff_table`
--

CREATE TABLE `staff_table` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `reg_no` varchar(100) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `quantity` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `floor` varchar(100) NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `request_date` varchar(100) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `withdrawn` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_table`
--

INSERT INTO `staff_table` (`id`, `asset_id`, `reg_no`, `asset_name`, `description`, `quantity`, `category`, `department`, `floor`, `requested_by`, `request_date`, `status`, `withdrawn`) VALUES
(74, 24, 'ISL913', 'HP 820 G3', 'Everything is in good condition', '2', 'Laptops', 'Nursing1', 'First Floor', 'Tobestic', '2025-11-03 17:02:00', NULL, 0),
(75, 30, 'ISL761', 'HP Laserjet Black Printer', 'dsgdgergrg', '2', 'Printers', 'Procurement', 'Admin building', 'admin', '2025-11-04 10:00:00', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(250) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `role` varchar(30) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `department` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `role`, `phone`, `department`) VALUES
(7, 'Odeyemi', 'Oluwatobi', 'Tobestic', 'tobestic53@gmail.com', '12345', 'admin', '08143405243', 'Admin'),
(19, 'Odeyemi', 'Timothy', 'facility', 'olasunkanmioye17@gmail.com', '$2y$10$k9K.q6TUrwpL9NZ3ZzmQaOtSgDTLkIR4wRbpufTNjiOznypjKDFUa', 'facility', '08154883269', 'Facility'),
(20, 'Odeyemi', 'Oluwatobi', 'admin', 'odeyemioluwatobi60@gmail.com', '$2y$10$63jf2Bx.9hcQ5BG5C7AlbuGXLDsa2ZfnqDl0Ts0Qf1wVlM37hCdBy', 'admin', '08154883254', 'Information Technology'),
(21, 'Odeyemi', 'Olasunkanmi', 'audit', 'olavikessential24@gmail.com', '$2y$10$8Q6R5tOO6ijJ8Pf/X80BVuuLlhNMSzbQpBWHD7OWxkBkuyLLnujOS', 'audit', '08154883763', 'Audit'),
(22, 'Odeyemi', 'Oye', 'procurement', 'oluwakemiruth.olanipekun@gmail.com', '$2y$10$j1gK9hPo8TtBxiaOdW3EjewSktaTJf1ypRGu0a2.rmjNC/dmFXbbm', 'procurement', '0815488452', 'Procurement'),
(23, 'Odeyemi', 'Ola', 'account', 'ola@gmail.com', '$2y$10$OZqP.rFramygMw8Q5sSoaephGW1FEL1L81zfKQMwfGX6Nis09bFyW', 'account', '08154834567', 'Account');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawn_asset`
--

CREATE TABLE `withdrawn_asset` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `reg_no` varchar(30) NOT NULL,
  `asset_name` varchar(40) NOT NULL,
  `department` varchar(40) NOT NULL,
  `floor` varchar(30) NOT NULL,
  `withdrawn_date` varchar(40) NOT NULL,
  `withdrawn_by` varchar(40) NOT NULL,
  `withdrawn_reason` text NOT NULL,
  `qty` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `withdrawn_asset`
--

INSERT INTO `withdrawn_asset` (`id`, `asset_id`, `reg_no`, `asset_name`, `department`, `floor`, `withdrawn_date`, `withdrawn_by`, `withdrawn_reason`, `qty`, `status`) VALUES
(82, 74, 'ISL913', 'HP 820 G3', 'Nursing1', 'First Floor', '2025-11-04 10:02:56', 'admin', 'Bad', 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asset_replacement_log`
--
ALTER TABLE `asset_replacement_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `asset_table`
--
ALTER TABLE `asset_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrow_table`
--
ALTER TABLE `borrow_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `completed_asset`
--
ALTER TABLE `completed_asset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department_borrow_table`
--
ALTER TABLE `department_borrow_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department_table`
--
ALTER TABLE `department_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repair_asset`
--
ALTER TABLE `repair_asset`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unique_repair` (`asset_id`,`status`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_reg_no` (`reg_no`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `request_table`
--
ALTER TABLE `request_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_table`
--
ALTER TABLE `staff_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdrawn_asset`
--
ALTER TABLE `withdrawn_asset`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asset_replacement_log`
--
ALTER TABLE `asset_replacement_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `asset_table`
--
ALTER TABLE `asset_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `borrow_table`
--
ALTER TABLE `borrow_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `completed_asset`
--
ALTER TABLE `completed_asset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `department_borrow_table`
--
ALTER TABLE `department_borrow_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `department_table`
--
ALTER TABLE `department_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `repair_asset`
--
ALTER TABLE `repair_asset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `request_table`
--
ALTER TABLE `request_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `staff_table`
--
ALTER TABLE `staff_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `withdrawn_asset`
--
ALTER TABLE `withdrawn_asset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `repair_asset`
--
ALTER TABLE `repair_asset`
  ADD CONSTRAINT `repair_asset_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `staff_table` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
