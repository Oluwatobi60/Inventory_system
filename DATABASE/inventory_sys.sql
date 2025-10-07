-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 05, 2025 at 07:06 PM
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
(22, 72, 2, '2025-10-05 15:03:33', 1, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'First Floor'),
(23, 71, 2, '2025-10-05 15:04:12', 1, 'ISL227', 'MAC BOOK', 'Nursing1', 'First Floor'),
(24, 72, 2, '2025-10-05 17:48:17', 1, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'First Floor');

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
  `dateofpurchase` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_table`
--

INSERT INTO `asset_table` (`id`, `reg_no`, `asset_name`, `description`, `quantity`, `category`, `dateofpurchase`) VALUES
(23, 'ISL158', 'HP 840 G3', 'All are bought with good condition', '3', 'Laptops', '2025-09-09'),
(24, 'ISL913', 'HP 820 G3', 'Everything is in good condition', '4', 'Laptops', '2025-09-11'),
(29, 'ISL227', 'MAC BOOK', 'dfsdfsdfss', '7', 'Laptops', '2025-10-04'),
(30, 'ISL761', 'HP Laserjet Black Printer', 'ddcdd', '9', 'Printers', '2025-10-04');

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
(43, 71, 1, 'Repair Completed', 'First Floor', 1, '2025-10-05 13:59:03', 'ISL227', 'MAC BOOK', 'Nursing1', 'Tobestic'),
(44, 73, 1, 'Repair Completed', 'Admin building', 1, '2025-10-05 15:04:30', 'ISL913', 'HP 820 G3', 'Facility', 'Tobestic'),
(45, 73, 1, 'Repair Completed', 'Admin building', 1, '2025-10-05 17:46:55', 'ISL913', 'HP 820 G3', 'Facility', 'Tobestic');

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
(21, 'Facility', 'Admin building');

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
(18, 'ISL707', 'HP Laserjet Black Printer', 'Good', 'Printers', 'Nursing1', '2025-09-16', '2025-11-15'),
(19, 'ISL707', 'HP Laserjet Black Printer', 'Good', 'Printers', 'Nursing2', '2025-09-16', '2025-11-15');

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
(153, 72, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'Odeyemi Oluwatobi', 'Marked for repair', 'General', 1, '2025-10-05 14:58:15', NULL, 'First Floor', '', 0, '2025-10-05 14:58:42', 1, 'rde', '2025-10-05 14:59:08', 1),
(154, 71, 'ISL227', 'MAC BOOK', 'Nursing1', 'Odeyemi Oluwatobi', 'Marked for repair', 'General', 1, '2025-10-05 15:01:41', NULL, 'First Floor', '', 0, '2025-10-05 15:01:51', 1, 'as', '2025-10-05 15:01:56', 1),
(155, 72, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'Odeyemi Oluwatobi', 'Marked for repair', 'General', 2, '2025-10-05 15:03:17', NULL, 'First Floor', '', 0, '2025-10-05 15:03:27', 1, 'df', '2025-10-05 15:03:33', 1),
(156, 71, 'ISL227', 'MAC BOOK', 'Nursing1', 'Odeyemi Oluwatobi', 'Marked for repair', 'General', 2, '2025-10-05 15:03:55', NULL, 'First Floor', '', 0, '2025-10-05 15:04:07', 1, 'dfg', '2025-10-05 15:04:12', 1),
(157, 73, 'ISL913', 'HP 820 G3', 'Facility', 'Tobestic', 'Marked for repair', 'General', 0, '2025-10-05 15:04:22', NULL, 'Admin building', '2025-10-05 15:04:30', 1, '', 0, '', '', 0),
(158, 73, 'ISL913', 'HP 820 G3', 'Facility', 'Tobestic', 'Marked for repair', 'General', 0, '2025-10-05 17:45:16', NULL, 'Admin building', '2025-10-05 17:46:55', 1, '', 0, '', '', 0),
(159, 72, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'Daramola Damola', 'Marked for repair', 'General', 2, '2025-10-05 17:47:04', NULL, 'First Floor', '', 0, '2025-10-05 17:47:43', 1, 'wer', '2025-10-05 17:48:17', 1);

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
(3, 'hod'),
(7, 'procurement');

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
(71, 29, 'ISL227', 'MAC BOOK', 'dfsdfsdfss', '7', 'Laptops', 'Nursing1', 'First Floor', 'Tobestic', '2025-10-04 22:10:00', NULL, 0),
(72, 30, 'ISL761', 'HP Laserjet Black Printer', 'ddcdd', '9', 'Printers', 'Nursing1', 'First Floor', 'Tobestic', '2025-10-04 22:12:00', NULL, 0),
(73, 24, 'ISL913', 'HP 820 G3', 'Everything is in good condition', '4', 'Laptops', 'Facility', 'Admin building', 'Tobestic', '2025-10-05 08:52:00', NULL, 0);

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
(3, 'Odeyemi', 'Timothy', 'Tobestics', 'odeyemioluwatobi60@gmail.com', '12345', 'hod', '08154883262', 'Computer Science'),
(6, 'Daramola', 'Damola', 'Daraminds', 'daramolaadewunmi@gmail.com', '12345', 'procurement', '08143405244', 'Procurement/Maintenance'),
(7, 'Odeyemi', 'Oluwatobi', 'Tobestic', 'tobestic53@gmail.com', '12345', 'admin', '08143405243', 'Admin'),
(16, 'Odeyemi', 'Admin', 'admin', 'admin@gmail.com', '$2y$10$oGOPN8Ah8t0k1X6wk27YnO9PJnboS1O36ISmGZGZ1dt6vP2asHgQ.', 'admin', '08154883267', 'Facility'),
(17, 'Ola', 'Facility', 'facility', 'fas@gmail.com', '$2y$10$SbeccdeGSAcNkGoiFfINdOGL4Q4S/g08js3hYlMicWAVAkVMtfGUS', 'procurement', '08154883267', 'Facility'),
(18, 'Tim', 'user', 'user', 'user@gmail.com', '$2y$10$/ia23/q1H5qsIR8gO7gsLODObp6LKPQK3hieEzHwN2Kr8i4TkDef.', 'hod', '08154883278', 'Facility');

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
(70, 72, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'First Floor', '2025-10-05 14:58:42', 'admin', 'rde', 1, 1),
(71, 71, 'ISL227', 'MAC BOOK', 'Nursing1', 'First Floor', '2025-10-05 15:01:51', 'admin', 'as', 1, 1),
(72, 72, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'First Floor', '2025-10-05 15:03:28', 'admin', 'df', 1, 0),
(73, 71, 'ISL227', 'MAC BOOK', 'Nursing1', 'First Floor', '2025-10-05 15:04:07', 'admin', 'dfg', 1, 0),
(74, 72, 'ISL761', 'HP Laserjet Black Printer', 'Nursing1', 'First Floor', '2025-10-05 17:47:43', 'admin', 'wer', 1, 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `department_borrow_table`
--
ALTER TABLE `department_borrow_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `department_table`
--
ALTER TABLE `department_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `maintenance_table`
--
ALTER TABLE `maintenance_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `repair_asset`
--
ALTER TABLE `repair_asset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `request_table`
--
ALTER TABLE `request_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff_table`
--
ALTER TABLE `staff_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `withdrawn_asset`
--
ALTER TABLE `withdrawn_asset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

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
