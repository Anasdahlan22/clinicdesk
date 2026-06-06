-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2026 at 07:15 PM
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
-- Database: `clinicdesk_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(10) UNSIGNED NOT NULL,
  `patient_id` int(10) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `appt_date` date NOT NULL,
  `appt_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `reason` varchar(255) DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appt_date`, `appt_time`, `status`, `reason`, `doctor_notes`, `created_at`) VALUES
(3, 9, 4, '2026-06-12', '10:00:00', 'confirmed', 'ألام في الركب', NULL, '2026-06-06 15:30:01'),
(4, 10, 5, '2026-06-06', '13:30:00', 'confirmed', '', NULL, '2026-06-06 15:32:15'),
(5, 10, 2, '2026-06-07', '09:30:00', 'pending', '', NULL, '2026-06-06 15:32:26'),
(6, 11, 5, '2026-06-06', '11:30:00', 'pending', '', NULL, '2026-06-06 17:01:14'),
(7, 11, 3, '2026-06-18', '14:30:00', 'completed', '', NULL, '2026-06-06 17:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `specialization_id` int(10) UNSIGNED NOT NULL,
  `bio` text DEFAULT NULL,
  `consultation_fee` decimal(8,2) NOT NULL DEFAULT 0.00,
  `available_days` varchar(50) NOT NULL DEFAULT 'Sun,Mon,Tue,Wed,Thu',
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialization_id`, `bio`, `consultation_fee`, `available_days`, `photo`) VALUES
(2, 5, 3, '', 50.00, 'Mon,Tue,Wed,Thu', NULL),
(3, 6, 1, '', 50.00, 'Mon,Tue,Wed,Thu', NULL),
(4, 7, 5, '', 50.00, 'Mon,Tue,Wed,Thu', NULL),
(5, 8, 2, '', 50.00, 'Sun,Wed,Thu,Fri', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `appointment_id` int(10) UNSIGNED NOT NULL,
  `diagnosis` text NOT NULL,
  `medications` text NOT NULL,
  `notes` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `appointment_id`, `diagnosis`, `medications`, `notes`, `file_path`, `created_at`) VALUES
(1, 7, 'يرجى متابعة الوثفة الطبية ', 'يصرف الدواء من الصيدلية الخاصة بالعيادة ', '', NULL, '2026-06-06 17:02:47');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`) VALUES
(3, 'الأمراض الجلدية'),
(5, 'العظام والمفاصل'),
(1, 'طب الأسرة'),
(4, 'طب الأطفال'),
(2, 'طب القلب');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL DEFAULT 'patient',
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `avatar`, `is_active`, `created_at`) VALUES
(4, 'Administrator', 'admin@clinic.com', '$2y$10$k6Qy03bQRRBJff3zurbbxuBUh5mSfrDC.xrTIV0jqj2C2NtaFq29S', 'admin', NULL, NULL, 1, '2026-06-06 15:12:13'),
(5, 'د. محمود أبو علي', 'dr.mahmoud@clinic.com', '$2y$10$g3S1hIRGMd0WaqTqeXBq2.fmh/Ryn8KyFkNcTYPcfcBmo8MoWY4sK', 'doctor', '', NULL, 1, '2026-06-06 15:21:42'),
(6, 'د. ليلى المصري', 'r.laila@clinic.com', '$2y$10$sBiY.6xnOnIigBLKTbAuKO.VvFKZjGYltSfKlY9/gE4ku/3.6MxRe', 'doctor', '', NULL, 1, '2026-06-06 15:23:26'),
(7, 'د. ابراهيم ابو شقرة ', 'dr.ibrahim@clinic.com', '$2y$10$OPu6bK0jb.GQZkj0TPxep.XRHz21sKAlPPDsKKl7i6L0MV9FAMYni', 'doctor', '', NULL, 1, '2026-06-06 15:24:17'),
(8, 'د. سمير ابو كرش', 'dr.sameer@clinic.com', '$2y$10$LXFXICJ5vJKflnT3cRF5b.VwplDzZylTY7E3xkPUDaVwC3YY3/nmC', 'doctor', '', NULL, 1, '2026-06-06 15:25:18'),
(9, 'أنس دحلان', 'a@a.com', '$2y$10$oOTbJUWY/KqRRqKJdJShVu59eC7PYr.exWmQAzpz/IOY2iGj4Iv/y', 'patient', '', NULL, 1, '2026-06-06 15:26:26'),
(10, 'محمد ابو ربيع', 'moh@gmail.com', '$2y$10$KdSKxYC5xQ2qWrlpGvNkKeJ3t9dwBauWYt2IPY.rBb.HDrVSHvoLy', 'patient', '', NULL, 1, '2026-06-06 15:31:21'),
(11, 'أحمد الشنا', 'ahmed@gmail.com', '$2y$10$.luJzq3/a.24s1gfx9BWfOcBkmnG3q6Dm56uOUh2CjaKsr1yxA1OK', 'patient', '', NULL, 1, '2026-06-06 17:00:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_double_booking` (`doctor_id`,`appt_date`,`appt_time`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
