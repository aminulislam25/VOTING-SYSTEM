-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2025 at 06:40 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `voting_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `college` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `admin_name`, `college`, `address`, `email`, `mobile`, `password`, `created_at`) VALUES
(1, 'Preceding Officer', 'Karimganj College', 'Karimganj', 'admin@example.com', 'admin123', 'admin@123', '2025-03-16 03:40:52'),
(2, 'Admin User', 'Demo College', 'Admin Address', 'admin@example.com', '1234567890', 'admin123', '2025-03-19 11:39:41');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL DEFAULT 0,
  `department` varchar(100) NOT NULL,
  `last_sem_percentage` decimal(5,2) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `manifesto` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidate_id`, `name`, `position_id`, `election_id`, `department`, `last_sem_percentage`, `photo`, `manifesto`, `created_at`, `phone`, `address`) VALUES
(7, 'testing', 2, 1, 'Computer Science & Application', 77.77, 'candidate_6847f6b09850d.jpg', 'xbf viwrgfnklweoihg', '2025-06-10 09:08:33', NULL, NULL),
(8, 'tesgygife', 2, 1, 'Bengali', 88.88, 'candidate_6847f6b09850d.jpg', 'wrgburehgljrn', '2025-06-10 09:11:12', NULL, NULL),
(9, 'testingA', 6, 1, 'English', 88.88, 'candidate_68484f249bcb1.jpg', 'hrfgehkbfdhkgf', '2025-06-10 15:28:36', NULL, NULL),
(10, 'Ytest', 6, 1, 'Bengali', 98.55, 'candidate_68484fb7298f5.jpg', 'testing the server', '2025-06-10 15:31:03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `hod_name` varchar(100) NOT NULL,
  `hod_email` varchar(100) NOT NULL,
  `hod_mobile` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department`, `hod_name`, `hod_email`, `hod_mobile`, `password`, `approved`, `created_at`) VALUES
(1, 'Bengali', 'Abhijit Chakraborty', 'example@example.com', '1234567890', '$2y$10$iruCrRPizzco0JBzBUgmG.viSg7yi/Lho8ciNJU8Tab4TZvtGY7Eq', 1, '2025-03-16 05:07:14'),
(3, 'English', 'Sudip Sinha', 'a@a.com', '1111111111', '$2y$10$zgSsqpMqE91tJfPar4ulBeN/k3SFeiZYjUv58NoxIeAlqYzIMrpR2', 1, '2025-03-19 09:56:07'),
(4, 'Computer Science & Application', 'Ananya Roy', 'ara@a.com', '12345612345', '$2y$10$Ak0isdjm62Si9dk.BiuJx.3t2uy6SbB2pV1Ure8otK8f0IUG7rmpm', 1, '2025-03-21 05:45:46');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `election_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('upcoming','active','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `results_visible` tinyint(1) NOT NULL DEFAULT 0,
  `results_last_updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`election_id`, `title`, `description`, `department`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `results_visible`, `results_last_updated`) VALUES
(1, 'testttt', 'ejbbfiue', NULL, '2025-06-10 15:05:31', '2025-06-12 15:05:31', 'active', '2025-06-10 09:35:50', '2025-06-10 09:59:47', 0, NULL),
(2, 'test', 'test', '', '2025-06-10 14:28:00', '2025-06-11 17:28:00', 'upcoming', '2025-06-10 08:58:50', '2025-06-10 08:58:50', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sender_type` enum('admin') NOT NULL,
  `recipient_type` enum('all','department','student') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `notification_id` int(11) NOT NULL,
  `user_type` enum('department','student') NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_name`, `description`, `created_at`) VALUES
(1, 'President', NULL, '2025-03-16 08:55:08'),
(2, 'Vice President', NULL, '2025-03-16 08:55:08'),
(3, 'Secretary', NULL, '2025-03-16 08:55:08'),
(4, 'Treasurer', NULL, '2025-03-16 08:55:08'),
(5, 'Cultural Secretary', NULL, '2025-03-16 08:55:08'),
(6, 'Sports Secretary', NULL, '2025-03-16 08:55:08');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `total_votes` int(11) NOT NULL DEFAULT 0,
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `rank_position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_name`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'election_status', 'inactive', '2025-03-16 06:34:44', '2025-03-16 06:34:44'),
(2, 'results_visibility', 'hidden', '2025-03-16 06:34:44', '2025-03-16 06:34:44'),
(3, 'election_status', 'inactive', '2025-03-16 06:36:11', '2025-03-16 06:36:11'),
(4, 'results_visibility', 'hidden', '2025-03-16 06:36:11', '2025-03-16 06:36:11'),
(5, 'election_status', 'inactive', '2025-03-16 06:37:10', '2025-03-16 06:37:10'),
(6, 'election_status', 'active', '2025-03-16 07:48:55', '2025-03-16 07:48:55'),
(7, 'results_visibility', 'visible', '2025-03-16 07:49:02', '2025-03-16 07:49:02'),
(8, 'election_status', 'active', '2025-06-10 06:23:19', '2025-06-10 06:23:19'),
(9, 'election_status', 'completed', '2025-06-10 06:57:10', '2025-06-10 06:57:10');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `registration_no` varchar(100) NOT NULL,
  `class_roll` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `session` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `registration_no`, `class_roll`, `department`, `session`, `email`, `mobile`, `password`, `status`, `approved`, `created_at`, `updated_at`) VALUES
(1, 'Rahul', 'Roy', '3333', '3333', 'Bengali', '2025-2026', 'c@c.com', '3333333333', '$2y$10$elZu.dUFNO8uC9247QJ7Ke5f1kLUGUaJJQ0CeIyQGTYnQNCSDEhl6', 'active', 1, '2025-03-19 11:03:14', '2025-06-10 13:31:12'),
(2, 'Rahul', 'Das', '4444', '4444', 'English', '2023-2024', 'd@d.com', '8888444477', '$2y$10$elZu.dUFNO8uC9247QJ7Ke5f1kLUGUaJJQ0CeIyQGTYnQNCSDEhl6', 'active', 1, '2025-03-19 11:12:31', '2025-06-10 10:51:24'),
(4, 'Anamika', 'Singha', '20220007341', '1232', 'Computer Science & Application', '2022-2023', 'princeaminul8822@gmail.com', '7002452374', '$2y$10$3Mw.YJXHikEzuHJP.9itretJkf2yumgEHaUTf7.N0alIuRNzzibn6', 'active', 1, '2025-03-21 05:51:53', '2025-03-24 08:14:11'),
(6, 'test', 'testt', 'KC8464', '868', 'English', '2021-2022', 'test@gmail.com', '9999999999', '$2y$10$LN2pdG80fQM5poudkGS2nOCogrfK0wdEb5DLD/u9pDvVvAJv/xcti', 'active', 1, '2025-06-10 15:25:38', '2025-06-10 15:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `registration_no` varchar(50) NOT NULL,
  `class_roll_no` varchar(50) NOT NULL,
  `session` enum('2021-22','2022-23','2023-24') NOT NULL,
  `stream` enum('Science','Arts','Commerce') NOT NULL,
  `department` enum('Computer Science and Application','Botany','Zoology','Economics') NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `has_voted` tinyint(1) DEFAULT 0,
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`vote_id`, `election_id`, `student_id`, `candidate_id`, `position_id`, `voted_at`) VALUES
(1, 1, 2, 7, 2, '2025-06-10 10:34:43'),
(2, 1, 1, 7, 2, '2025-06-10 13:33:56'),
(3, 1, 6, 8, 2, '2025-06-10 15:27:12'),
(4, 1, 6, 10, 6, '2025-06-10 15:32:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `fk_candidates_election_id` (`election_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`election_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`notification_id`,`user_type`,`user_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `unique_candidate_result` (`election_id`,`position_id`,`candidate_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `registration_no` (`registration_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_no` (`registration_no`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `unique_vote` (`student_id`,`position_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `position_id` (`position_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `election_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_election_fk` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `fk_candidates_election_id` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`notification_id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_3` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
