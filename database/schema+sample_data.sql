-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2025 at 03:53 PM
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
-- Database: `origin_driving_school`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `note_id`, `file_path`, `uploaded_at`) VALUES
(1, 1, 'uploads/notes/puja.pdf', '2025-02-10 00:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `start_datetime` datetime GENERATED ALWAYS AS (`start_time`) STORED,
  `end_datetime` datetime GENERATED ALWAYS AS (`end_time`) STORED,
  `status` enum('booked','scheduled','completed','cancelled') DEFAULT 'booked',
  `reminder_sms_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `student_id`, `instructor_id`, `course_id`, `branch_id`, `vehicle_id`, `start_time`, `end_time`, `status`, `reminder_sms_sent`, `created_at`, `updated_at`) VALUES
(1, 5, 3, 1, 1, 1, '2025-02-10 09:00:00', '2025-02-10 11:00:00', 'completed', 0, '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 6, 4, 2, 2, 2, '2025-03-05 14:00:00', '2025-03-05 16:00:00', 'booked', 0, '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 7, 3, 3, 3, 3, '2025-04-01 10:00:00', '2025-04-01 12:00:00', 'cancelled', 0, '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(4, 8, 11, 2, 4, 3, '2025-09-27 14:56:00', '2025-09-27 16:56:00', 'booked', 0, '2025-09-27 02:56:45', '2025-09-27 02:56:45'),
(5, 8, 4, 1, 2, 2, '2025-10-12 13:42:00', '2025-10-12 19:42:00', 'booked', 0, '2025-09-28 03:42:20', '2025-09-28 03:42:20');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Sydney CBD Branch', '16 King St, Sydney NSW', '02-2918-4837', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 'Parramatta Branch', '45 Church St, Parramatta NSW', '02-4567-8392', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 'Manly Branch', '12 The Corso, Manly NSW', '02-9845-1203', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(4, 'Bondi Junction Branch', '8 Campbell Pde, Bondi NSW', '02-9371-4829', '2025-09-25 07:13:42', '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `branch_tours`
--

CREATE TABLE `branch_tours` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `preferred_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_tours`
--

INSERT INTO `branch_tours` (`id`, `branch_id`, `name`, `email`, `preferred_date`, `created_at`) VALUES
(1, 1, 'Olivia Chen', 'olivia@example.com', '2025-02-20', '2025-09-25 07:13:42'),
(2, 2, 'Michael Tan', 'michael@example.com', '2025-02-22', '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fee` decimal(10,2) DEFAULT NULL,
  `class_count` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `title`, `description`, `price`, `fee`, `class_count`, `created_at`, `updated_at`) VALUES
(1, 'Basic Driving', 'Basic Driving', 'Introduction to car driving basics.', 800.00, 800.00, 8, '2025-09-25 07:13:42', '2025-09-27 03:35:35'),
(2, 'Advanced Driving', 'Advanced Driving', 'Advanced maneuvers and highway driving.', 1400.00, 1400.00, 12, '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 'Motorcycle Riding', 'Motorcycle Riding', 'Motorcycle operation and safety essentials.', 1000.00, 1000.00, 6, '2025-09-25 07:13:42', '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `course_instructors`
--

CREATE TABLE `course_instructors` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_instructors`
--

INSERT INTO `course_instructors` (`id`, `course_id`, `instructor_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1),
(4, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `fleet`
--

CREATE TABLE `fleet` (
  `id` int(11) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `plate_no` varchar(20) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fleet`
--

INSERT INTO `fleet` (`id`, `vehicle_type`, `model`, `plate_no`, `branch_id`, `created_at`, `updated_at`) VALUES
(1, 'car', 'Toyota Corolla', 'SYD-FLT-01', 1, '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 'car', 'Hyundai Accent', 'PAR-FLT-02', 2, '2025-09-25 07:13:42', '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `experience` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `availability` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`availability`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `user_id`, `name`, `experience`, `address`, `phone`, `photo`, `availability`, `created_at`, `updated_at`) VALUES
(1, 3, 'Bikash Rai', '10 years teaching experience', '5 Barrack St, Sydney', '0434567890', NULL, '[\"Mon\", \"Wed\", \"Fri\"]', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 4, 'Manoj Gurung', 'Defensive driving specialist', '45 George St, Manly', '0445678901', NULL, '[\"Tue\", \"Thu\", \"Sat\"]', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(4, 11, 'Ramesh Khanal', '', '', '0492930202', NULL, NULL, '2025-09-27 02:48:47', '2025-09-27 02:48:47'),
(5, 12, 'Shyam Karki', '', '', '0492294042', NULL, NULL, '2025-09-27 02:53:14', '2025-09-27 02:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issued_date` date NOT NULL,
  `due_date` date NOT NULL,
  `paid_on` datetime DEFAULT NULL,
  `status` enum('unpaid','pending','partial','paid','overdue') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `student_id`, `course_id`, `amount`, `issued_date`, `due_date`, `paid_on`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 800.00, '2025-01-28', '2025-02-15', NULL, 'paid', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 6, 2, 1400.00, '2025-02-20', '2025-03-10', NULL, 'unpaid', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 7, 3, 1000.00, '2025-03-01', '2025-03-20', NULL, 'overdue', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(4, 8, 1, 1400.00, '0000-00-00', '2025-10-09', NULL, 'pending', '2025-09-25 11:39:52', '2025-09-25 11:39:52'),
(5, 8, 1, 800.00, '2025-09-25', '2026-03-26', NULL, 'pending', '2025-09-25 12:26:19', '2025-09-25 12:26:19'),
(6, 9, 1, 800.00, '2025-09-25', '2026-04-25', NULL, 'pending', '2025-09-25 13:01:36', '2025-09-25 13:01:36'),
(7, 8, 2, 1400.00, '2025-09-26', '2025-10-01', '2025-09-27 13:23:31', 'paid', '2025-09-26 03:54:19', '2025-09-27 03:23:31');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `name`, `email`, `phone`, `license_no`, `experience_years`, `resume_path`, `status`, `created_at`) VALUES
(1, 'Ashraya Acharya', 'ashraya@example.com', '0412345678', 'NSW-998877', 6, 'uploads/resumes/ashraya.pdf', 'pending', '2025-09-25 07:13:42'),
(2, 'Prajwal Khadka', 'clashprajwal@gmail.com', '0422774077', '79044-554', 3, NULL, 'pending', '2025-09-29 12:30:01');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `channel` enum('in_app','email','sms') DEFAULT 'in_app',
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `content`, `channel`, `sent_at`) VALUES
(1, NULL, 5, 'Welcome to Origin Driving School', 'Dear Puja, welcome aboard!', 'in_app', '2025-01-01 09:00:00'),
(2, 3, 6, 'Lesson Update', 'Your lesson on 2025-03-05 has been booked.', 'email', '2025-02-20 15:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `entity_type`, `entity_id`, `author_id`, `content`, `created_at`) VALUES
(1, 'student', 5, NULL, 'Puja shows good control at low speeds.', '2025-02-10 00:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `content`, `is_read`, `created_at`) VALUES
(1, 5, 'booking_reminder', 'Reminder: Your lesson on 2025-02-10 at 09:00 is coming up.', 0, '2025-09-25 07:13:42'),
(2, 6, 'payment_reminder', 'Reminder: Invoice #2 of $1400 is due on 2025-03-10.', 0, '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `amount`, `method`, `note`, `payment_date`, `paid_at`) VALUES
(1, 1, 800.00, 'cash', 'Paid in full at reception', '2025-02-12 10:30:00', '2025-09-25 07:13:42'),
(2, 3, 500.00, 'bank_transfer', 'Part payment received', '2025-03-25 09:45:00', '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('scheduled','booked','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `student_id`, `instructor_id`, `start_datetime`, `end_datetime`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 3, '2025-02-08 09:00:00', '2025-02-08 11:00:00', 'scheduled', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 6, 4, '2025-03-05 14:00:00', '2025-03-05 16:00:00', 'scheduled', '2025-09-25 07:13:42', '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `licence_type` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `licence_type`, `dob`, `created_at`, `updated_at`) VALUES
(1, 5, 'car', '2000-04-12', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 6, 'car', '1999-09-05', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 7, 'motorcycle', '2002-01-18', '2025-09-25 07:13:42', '2025-09-25 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_type` enum('car','motorcycle') NOT NULL,
  `course_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `preferred_days` varchar(50) DEFAULT NULL,
  `preferred_time` varchar(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `vehicle_type`, `course_id`, `branch_id`, `address`, `phone`, `preferred_days`, `preferred_time`, `start_date`, `created_at`, `updated_at`) VALUES
(1, 5, 'car', 1, 1, '16 King St, Sydney NSW', '0467890123', 'Mon,Wed,Fri', 'Morning', '2025-02-01', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 6, 'car', 2, 2, '45 Church St, Parramatta NSW', '0478901234', 'Tue,Thu', 'Afternoon', '2025-03-05', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 7, 'motorcycle', 3, 3, '12 The Corso, Manly NSW', '0456789012', 'Sat,Sun', 'Morning', '2025-03-15', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(4, 8, 'car', 2, 3, 'Toilet', '0492838202', 'Mon,Wed,Sun', 'Morning', '2025-10-01', '2025-09-25 09:25:21', '2025-09-26 03:54:19'),
(5, 9, 'car', 1, 4, NULL, NULL, 'Mon,Thu,Sat', 'Morning', '2026-04-25', '2025-09-25 13:01:36', '2025-09-25 13:01:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','staff','instructor','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `branch_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `role`, `created_at`, `updated_at`) VALUES
(1, 1, 'Prajwal', 'Khadka', 'admin@origin.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0299991111', 'admin', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 1, 'Bikash', 'Rai', 'bikash.rai@origin.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0434567890', 'instructor', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(4, 3, 'Manoj', 'Gurung', 'manoj.gurung@origin.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0445678901', 'instructor', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(5, 1, 'Puja', 'Rana', 'puja.rana@origin.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0467890123', 'student', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(6, 2, 'Sanjay', 'Magar', 'sanjay.magar@origin.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0478901234', 'student', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(7, 3, 'Kiran', 'Adhikari', 'kiran.adhikari@origin.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0456789012', 'student', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(8, 3, 'Randy', 'Rimal', 'imrandy@gmail.com', '$2y$10$YqsQBPMvz9i0aenpTZKSeu8pYFIU0KaX.9FBljsdZerhCCS/zVvxy', '0492838202', 'student', '2025-09-25 09:25:21', '2025-09-26 03:54:19'),
(9, 4, 'Sam', 'Altman', 'altman@gmail.com', '$2y$10$13RptJeW64XhlM.S4e9RC.TCeQqfg4uDOvD.KjTwjK2zlbWEJCV9e', '0492839328', 'student', '2025-09-25 12:58:56', '2025-09-25 13:01:36'),
(11, NULL, 'Ramesh', 'Khanal', 'khanalramesh@gmail.com', '', '0492930202', 'instructor', '2025-09-27 02:48:47', '2025-09-27 02:48:47'),
(12, NULL, 'Shyam', 'Karki', 'shyamkarki@gmail.com', '', '0492294042', 'instructor', '2025-09-27 02:53:14', '2025-09-27 02:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `registration_number` varchar(20) DEFAULT NULL,
  `make` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `status` enum('available','assigned','maintenance') DEFAULT 'available',
  `last_maintenance` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `branch_id`, `registration_number`, `make`, `model`, `status`, `last_maintenance`, `created_at`, `updated_at`) VALUES
(1, 1, 'SYD-1234', 'Toyota', 'Corolla', 'available', '2025-06-01', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(2, 2, 'PAR-5678', 'Hyundai', 'Accent', 'maintenance', '2025-05-15', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(3, 3, 'MAN-9101', 'Suzuki', 'Swift', 'available', '2025-07-20', '2025-09-25 07:13:42', '2025-09-25 07:13:42'),
(4, 4, 'K2302', '2019', 'Tesla X', 'available', '2025-09-27', '2025-09-27 03:02:03', '2025-09-27 03:02:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attachments_note` (`note_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bookings_course` (`course_id`),
  ADD KEY `fk_bookings_branch` (`branch_id`),
  ADD KEY `fk_bookings_vehicle` (`vehicle_id`),
  ADD KEY `idx_bookings_instructor` (`instructor_id`,`start_datetime`),
  ADD KEY `idx_bookings_student` (`student_id`,`start_datetime`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_tours`
--
ALTER TABLE `branch_tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tours_branch` (`branch_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ci_course` (`course_id`),
  ADD KEY `fk_ci_instructor` (`instructor_id`);

--
-- Indexes for table `fleet`
--
ALTER TABLE `fleet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_no` (`plate_no`),
  ADD KEY `fk_fleet_branch` (`branch_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_instructors_user` (`user_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoices_student` (`student_id`),
  ADD KEY `fk_invoices_course` (`course_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messages_sender` (`sender_id`),
  ADD KEY `fk_messages_receiver` (`receiver_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notes_author` (`author_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_invoice` (`invoice_id`,`paid_at`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_schedules_student` (`student_id`),
  ADD KEY `fk_schedules_instructor` (`instructor_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_students_user` (`user_id`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_profiles_user` (`user_id`),
  ADD KEY `fk_profiles_course` (`course_id`),
  ADD KEY `fk_profiles_branch` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_branch` (`branch_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `fk_vehicles_branch` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branch_tours`
--
ALTER TABLE `branch_tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_instructors`
--
ALTER TABLE `course_instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fleet`
--
ALTER TABLE `fleet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `fk_attachments_note` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bookings_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `branch_tours`
--
ALTER TABLE `branch_tours`
  ADD CONSTRAINT `fk_tours_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD CONSTRAINT `fk_ci_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fleet`
--
ALTER TABLE `fleet`
  ADD CONSTRAINT `fk_fleet_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `fk_instructors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoices_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_notes_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedules_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_profiles_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_profiles_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
