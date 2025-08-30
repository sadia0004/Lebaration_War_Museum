-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2025 at 12:51 AM
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
-- Database: `museum`
--

-- --------------------------------------------------------

--
-- Table structure for table `artifacts`
--

CREATE TABLE `artifacts` (
  `artifact_id` int(11) NOT NULL,
  `collection_number` varchar(50) NOT NULL,
  `accession_number` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `object_type` varchar(100) DEFAULT NULL,
  `period` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `significance_comment` text DEFAULT NULL,
  `contributor_name` varchar(255) DEFAULT NULL,
  `collection_date` date DEFAULT NULL,
  `found_place` varchar(255) DEFAULT NULL,
  `measurements` varchar(100) DEFAULT NULL,
  `materials` varchar(255) DEFAULT NULL,
  `gallery_number` varchar(50) DEFAULT NULL,
  `condition` varchar(50) DEFAULT NULL,
  `preservation_notes` text DEFAULT NULL,
  `correction_notes` text DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `artifact_image_url` varchar(255) DEFAULT NULL,
  `status` enum('On Display','In Storage','Under Restoration','On Loan') NOT NULL DEFAULT 'In Storage',
  `added_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artifacts`
--

INSERT INTO `artifacts` (`artifact_id`, `collection_number`, `accession_number`, `title`, `object_type`, `period`, `description`, `significance_comment`, `contributor_name`, `collection_date`, `found_place`, `measurements`, `materials`, `gallery_number`, `condition`, `preservation_notes`, `correction_notes`, `is_featured`, `artifact_image_url`, `status`, `added_by_user_id`, `created_at`) VALUES
(3, 'CN-1971-002', 'AN-2025', 'Military weapon', 'Weaponry', 'During War.', 'It is collected during the war from the Military troops when the war was happening.', 'Historical significance of the war of 1971.', 'Freedom Fighters', '1998-01-30', 'Dhaka', '10X12 inch', 'Carbon Steel', 'G-2', 'Excellent', 'Stored in a climate-controlled, UV-protected, acid-free enclosure.', '', 1, 'uploads/artifacts/1756579360_download (3).jpeg', 'On Display', 5, '2025-08-30 18:42:40');

-- --------------------------------------------------------

--
-- Table structure for table `artifact_media`
--

CREATE TABLE `artifact_media` (
  `artifact_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `is_primary_display` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `media_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `media_type` enum('video','audio') NOT NULL,
  `category` enum('Documentary','War Footage','Post-War','Interview','Speech','Other') DEFAULT NULL,
  `file_url` varchar(255) NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `uploaded_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`media_id`, `title`, `description`, `media_type`, `category`, `file_url`, `thumbnail_url`, `uploaded_by_user_id`, `created_at`) VALUES
(3, 'Freedom Fighter Interview Clip', 'Audio interview with a veteran discussing the use of Sten guns.', 'audio', 'Interview', 'https://path.to/storage/sten_gun_interview.mp3', NULL, 2, '2025-08-30 15:34:26'),
(4, 'Some glimpse of the 1971 war!', 'A documentary detailing the events of 1971, when the Pakistan Army launched a brutal military crackdown on the civilian population of East Pakistan. Includes archival footage and survivor testimonies.', 'video', 'Documentary', 'uploads/videos/1756587899_gettyimages-1822348632-640_adpp.mp4', 'uploads/thumbnails/1756587899_thumb_download1.jpeg', 5, '2025-08-30 21:04:59'),
(5, '1971 War for the Liberation of Bangladesh.', 'Documentary Video About the 1971 war. The whole 9-month war Glimpse.', 'video', 'Documentary', 'uploads/videos/1756590463_Screen Recording 2025-08-31 034409.mp4', 'uploads/thumbnails/1756590463_thumb_Screenshot 2025-08-31 034235.png', 5, '2025-08-30 21:47:43'),
(6, 'Audio of young Bangladeshis about their knowledge of Bangladesh\'s liberation war.', 'Interview of some young Bangladeshis about their knowledge of Bangladesh\'s liberation war [Ehsan Ullah].', 'audio', 'Speech', 'uploads/audio/1756591235_17Jan05.mp3', NULL, 5, '2025-08-30 22:00:35'),
(7, 'Commemorating \'21 February - Bhasha Shaheed Dibash\' ', 'Commemorating \'21 February - Bhasha Shaheed Dibash\' - history, poetry and songs [Avijit Sarker]', 'audio', 'Speech', 'uploads/audio/1756591352_21Feb05.mp3', NULL, 5, '2025-08-30 22:02:32'),
(8, 'The Victory Day of Bangladesh on 16th December', 'Two graphic memoirs of the liberation war in commemorating the Victory Day of Bangladesh on 16th December [Ehsan Ullah]', 'audio', 'Speech', 'uploads/audio/1756591458_18Dec06.mp3', NULL, 5, '2025-08-30 22:04:18'),
(9, 'Words on 21st February \'bhasha shaheed dibosh\' and a short story \'prothom shikhok\'.', 'Words on 21st February \'bhasha shaheed dibosh\' and a short story \'prothom shikhok\' by Dr. Muhammed Zafar Iqbal [Murshed Haider]', 'audio', 'Speech', 'uploads/audio/1756591588_19Feb07.mp3', NULL, 5, '2025-08-30 22:06:28'),
(10, 'Tribute Audio to Maulana Abdul Hamid Khan Bhashani .', 'Tribute to Maulana Abdul Hamid Khan Bhashani - his role and contributions in Bangladesh\'s liberation [Badiuzzaman Khan]', 'audio', 'Interview', 'uploads/audio/1756591682_30Jul07.mp3', NULL, 5, '2025-08-30 22:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('visitor','manager','admin') NOT NULL DEFAULT 'visitor',
  `status` enum('active','pending','rejected') NOT NULL DEFAULT 'pending',
  `profile_photo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `role`, `status`, `profile_photo_url`, `created_at`) VALUES
(1, 'Admin User', 'admin@museum.com', 'admin123_hashed', 'admin', 'active', NULL, '2025-08-30 15:34:26'),
(2, 'Manager User', 'm@gmail.com', '123', 'manager', 'active', NULL, '2025-08-30 15:34:26'),
(3, 'Visitor User', 'visitor@museum.com', 'visitor123_hashed', 'visitor', 'active', NULL, '2025-08-30 15:34:26'),
(4, 'Sadia Ahmed', 'sadia@gmail.com', '$2y$10$M4xblbZRrm4yx4uYoxP39upsEumNWnyFs//32aK5INVtYLntf0gme', 'visitor', 'active', 'uploads/profile_photos/1756572547_download (1).jpeg', '2025-08-30 16:49:07'),
(5, 'Samia Ahmed', 'samia@gmail.com', '$2y$10$f1eyjvZDGiQhHwQ3cIk/0uSmIu14cogp9u9Inb9MFSMoM/4b.jSM6', 'manager', 'active', 'uploads/profile_photos/1756572848_images.jpeg', '2025-08-30 16:54:08'),
(6, 'Tadique Ahmed', 'rayu@gmail.com', '$2y$10$cYGcyZhIdqJfRPY4tbdkOuJrXQNCDaIDAWpBxDi9TFXWNrxQ2.mUi', 'manager', 'active', 'uploads/profile_photos/1756574093_images (2).jpeg', '2025-08-30 17:14:53'),
(7, 'Sabiha Rassu', 'sabiha@gmail.com', '$2y$10$ixnyzj7CQAft6d6LTvxuyejpUJnIzeYmJR014bq7I3FPahLl/ob6K', 'visitor', 'active', 'uploads/profile_photos/1756590661_download (1).jpeg', '2025-08-30 21:51:01');

-- --------------------------------------------------------

--
-- Table structure for table `user_collections`
--

CREATE TABLE `user_collections` (
  `user_id` int(11) NOT NULL,
  `artifact_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_view_history`
--

CREATE TABLE `user_view_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `artifact_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_view_history`
--

INSERT INTO `user_view_history` (`history_id`, `user_id`, `artifact_id`, `viewed_at`) VALUES
(1, 4, 3, '2025-08-30 21:17:44'),
(2, 4, 3, '2025-08-30 21:34:45'),
(3, 4, 3, '2025-08-30 21:45:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artifacts`
--
ALTER TABLE `artifacts`
  ADD PRIMARY KEY (`artifact_id`),
  ADD UNIQUE KEY `collection_number` (`collection_number`),
  ADD KEY `added_by_user_id` (`added_by_user_id`);

--
-- Indexes for table `artifact_media`
--
ALTER TABLE `artifact_media`
  ADD PRIMARY KEY (`artifact_id`,`media_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `uploaded_by_user_id` (`uploaded_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_collections`
--
ALTER TABLE `user_collections`
  ADD PRIMARY KEY (`user_id`,`artifact_id`),
  ADD KEY `artifact_id` (`artifact_id`);

--
-- Indexes for table `user_view_history`
--
ALTER TABLE `user_view_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `artifact_id` (`artifact_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artifacts`
--
ALTER TABLE `artifacts`
  MODIFY `artifact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_view_history`
--
ALTER TABLE `user_view_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `artifacts`
--
ALTER TABLE `artifacts`
  ADD CONSTRAINT `artifacts_ibfk_1` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `artifact_media`
--
ALTER TABLE `artifact_media`
  ADD CONSTRAINT `artifact_media_ibfk_1` FOREIGN KEY (`artifact_id`) REFERENCES `artifacts` (`artifact_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `artifact_media_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`media_id`) ON DELETE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_collections`
--
ALTER TABLE `user_collections`
  ADD CONSTRAINT `user_collections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_collections_ibfk_2` FOREIGN KEY (`artifact_id`) REFERENCES `artifacts` (`artifact_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_view_history`
--
ALTER TABLE `user_view_history`
  ADD CONSTRAINT `user_view_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_view_history_ibfk_2` FOREIGN KEY (`artifact_id`) REFERENCES `artifacts` (`artifact_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
