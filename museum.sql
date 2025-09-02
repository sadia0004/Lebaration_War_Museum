-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 08:37 AM
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
(3, 'CN-1971-002', 'AN-2025', 'Military weapon', 'Weaponry', 'During War.', 'It is collected during the war from the Military troops when the war was happening.', 'Historical significance of the war of 1971.', 'Freedom Fighters', '1998-01-30', 'Dhaka', '10X12 inch', 'Carbon Steel', 'G-2', 'Excellent', 'Stored in a climate-controlled, UV-protected, acid-free enclosure.', '', 1, 'uploads/artifacts/1756579360_download (3).jpeg', 'On Display', 5, '2025-08-30 18:42:40'),
(4, 'CN-1971-003', 'AN-2025', 'News Paper of 1971', 'Documents', 'During War.', 'A newspaper of the 1971 war massacre.', 'Explain the Significance news.', 'Reporter', '0000-00-00', 'Bogura.', '', 'Paper', 'G-3', 'Fair', 'Stored in a glass protector.', '', 1, 'uploads/artifacts/1756724716_WhatsApp Image 2025-09-01 at 16.50.11_7a72ed69.jpg', 'On Display', 5, '2025-09-01 11:05:16'),
(5, 'CN-1971-004', 'AN-2022', 'A Crashed Plane.', 'Weaponry', 'During War.', 'Crashed In the War of 1971.', 'Tell the war Significance.', 'Bd Military', '2025-09-01', 'Bogura.', '', 'Paper', 'G-5', 'Good', 'Stored in a glass protector.', 'No correction', 1, 'uploads/artifacts/1756725024_images (1).jpeg', 'In Storage', 5, '2025-09-01 11:10:24');

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
-- Table structure for table `heroes`
--

CREATE TABLE `heroes` (
  `hero_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT 'e.g., Bir Sreshtho, Captain',
  `bio` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `heroes`
--

INSERT INTO `heroes` (`hero_id`, `full_name`, `title`, `bio`, `date_of_birth`, `date_of_death`, `image_url`, `is_featured`, `created_at`) VALUES
(1, 'Mohiuddin Jahangir', 'Bir Sreshtho, Captain', 'He was a captain in the Bangladesh Army during the 1971 Liberation War. He was posthumously awarded the Bir Sreshtho, Bangladesh\'s highest military gallantry award, for his valor and sacrifice.', '1949-03-07', '1971-12-14', 'images/heroes/mohiuddin_jahangir.jpg', 1, '2025-09-01 11:22:15'),
(2, 'Hamidur Rahman', 'Bir Sreshtho, Sepoy', 'A sepoy in the Bangladesh Army, he fought valiantly at the Dhalai border outpost and was killed during a fierce battle. He was posthumously awarded the Bir Sreshtho for his immense bravery.', '1953-02-02', '1971-10-28', 'images/heroes/hamidur_rahman.jpg', 1, '2025-09-01 11:22:15'),
(3, 'Mostafa Kamal', 'Bir Sreshtho, Sepoy', 'A sepoy in the East Pakistan Rifles, he held his post under heavy enemy fire to allow his comrades to retreat safely during the Battle of Daruin. He was posthumously awarded the Bir Sreshtho.', '1947-12-16', '1971-04-18', 'images/heroes/mostafa_kamal.jpg', 1, '2025-09-01 11:22:15'),
(4, 'Mohammad Ruhul Amin', 'Bir Sreshtho, Engine Room Artificer', 'An Engine Room Artificer in the Bangladesh Navy, he continued to command his gunboat even after it was hit by enemy aircraft, fighting until his death. He was posthumously awarded the Bir Sreshtho.', '1935-06-17', '1971-12-10', 'images/heroes/ruhul_amin.jpg', 1, '2025-09-01 11:22:15'),
(5, 'Motiur Rahman', 'Bir Sreshtho, Flight Lieutenant', 'A flight lieutenant in the Pakistan Air Force, he attempted to defect to Bangladesh with a T-33 jet trainer to join the Liberation War. The plane crashed, and he died a martyr, earning the Bir Sreshtho.', '1941-10-29', '1971-08-20', 'images/heroes/motiur_rahman.jpg', 1, '2025-09-01 11:22:15'),
(6, 'Munshi Abdur Rouf', 'Bir Sreshtho, Lance Nayak', 'A Lance Nayak in the East Pakistan Rifles, he single-handedly stalled a Pakistani advance with his machine gun, saving his company. He was killed by a mortar shell and awarded the Bir Sreshtho.', '1943-05-01', '1971-04-20', 'images/heroes/abdur_rouf.jpg', 1, '2025-09-01 11:22:15'),
(7, 'Nur Mohammad Sheikh', 'Bir Sreshtho, Lance Nayak', 'A Lance Nayak in the East Pakistan Rifles, he provided covering fire for his retreating comrades, continuing to fight even after being mortally wounded. He was posthumously awarded the Bir Sreshtho.', '1936-02-26', '1971-09-05', 'images/heroes/nur_mohammad.jpg', 1, '2025-09-01 11:22:15');

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
-- Table structure for table `timeline_events`
--

CREATE TABLE `timeline_events` (
  `event_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_significance` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `category` enum('Political','Military','Diplomatic','Social','Economic','Other') DEFAULT 'Other',
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `added_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeline_events`
--

INSERT INTO `timeline_events` (`event_id`, `event_date`, `event_title`, `event_description`, `event_significance`, `location`, `category`, `image_url`, `is_featured`, `added_by_user_id`, `created_at`) VALUES
(1, '1970-12-07', 'General Elections in East Pakistan', 'The Awami League under Sheikh Mujibur Rahman won a landslide victory in East Pakistan, securing 160 out of 162 seats allocated to East Pakistan in the National Assembly.', 'This election result gave the Awami League an absolute majority in the Pakistan National Assembly, setting the stage for the constitutional crisis that would lead to the Liberation War.', 'East Pakistan', 'Political', NULL, 1, NULL, '2025-08-30 22:58:43'),
(2, '1971-03-07', 'Historic Speech by Bangabandhu', 'Sheikh Mujibur Rahman delivered his historic speech at the Ramna Race Course (now Suhrawardy Udyan) where he declared \"This time the struggle is for our freedom. This time the struggle is for our independence.\"', 'This speech is considered the unofficial declaration of independence and galvanized the Bengali population for the upcoming struggle.', 'Dhaka', 'Political', NULL, 1, NULL, '2025-08-30 22:58:43'),
(3, '1971-03-25', 'Operation Searchlight Begins', 'The Pakistan Army launched Operation Searchlight, a systematic military operation to suppress the Bengali nationalist movement in East Pakistan.', 'This brutal crackdown marked the beginning of the genocide and the start of armed resistance by the Bengali people.', 'Dhaka', 'Military', NULL, 1, NULL, '2025-08-30 22:58:43'),
(4, '1971-03-26', 'Declaration of Independence', 'Sheikh Mujibur Rahman declared the independence of Bangladesh just before his arrest by Pakistani forces.', 'This declaration formally established Bangladesh as an independent nation and provided legal basis for the Liberation War.', 'Dhaka', 'Political', NULL, 1, NULL, '2025-08-30 22:58:43'),
(5, '1971-04-17', 'Formation of Mujibnagar Government', 'The Provisional Government of Bangladesh was formed at Baidyanathtala, Meherpur, which was later renamed Mujibnagar.', 'This government provided political legitimacy to the liberation struggle and organized the war effort.', 'Mujibnagar', 'Political', NULL, 1, NULL, '2025-08-30 22:58:43'),
(6, '1971-07-11', 'Formation of Mukti Bahini', 'The Bangladesh Forces (Mukti Bahini) was formally organized to conduct guerrilla warfare against Pakistani occupation forces.', 'The Mukti Bahini became the backbone of the armed resistance and played a crucial role in the liberation struggle.', 'Various locations', 'Military', NULL, 1, NULL, '2025-08-30 22:58:43'),
(7, '1971-12-03', 'India Enters the War', 'India officially entered the Bangladesh Liberation War following Pakistani air strikes on Indian airfields.', 'Indian military support proved decisive in the final phase of the war, leading to rapid Pakistani defeat.', 'India-East Pakistan Border', 'Military', NULL, 1, NULL, '2025-08-30 22:58:43'),
(8, '1971-12-16', 'Victory Day - Pakistan Surrenders', 'Lieutenant General A.A.K. Niazi signed the Instrument of Surrender in Dhaka, officially ending the Liberation War.', 'This day marks the birth of Bangladesh as an independent nation after nine months of bloody war.', 'Dhaka', 'Military', NULL, 1, NULL, '2025-08-30 22:58:43'),
(9, '1972-01-10', 'Bangabandhu Returns Home', 'Sheikh Mujibur Rahman returned to Bangladesh after being released from Pakistani prison.', 'The return of the Father of the Nation marked the beginning of nation-building in newly independent Bangladesh.', 'Dhaka', 'Political', NULL, 1, NULL, '2025-08-30 22:58:43'),
(10, '1972-03-26', 'First Independence Day Celebration', 'Bangladesh celebrated its first Independence Day as a free nation.', 'This celebration established the national commemorative tradition and reinforced the significance of the liberation struggle.', 'Dhaka', 'Social', NULL, 1, NULL, '2025-08-30 22:58:43'),
(11, '1971-01-08', 'The Concert for Bangladesh', 'A pair of benefit concerts organized by George Harrison and Ravi Shankar to raise international awareness and fund relief efforts for refugees from East Pakistan, following the Bangladesh genocide.', 'The first-ever major benefit concert of its kind, it raised global awareness of the humanitarian crisis and the struggle for Bangladesh\'s independence.', 'Madison Square Garden, New York, USA', 'Social', 'uploads/timeline/1756596077_$_12.jpeg', 1, 5, '2025-08-30 23:21:17');

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
(7, 'Sabiha Rassu', 'sabiha@gmail.com', '$2y$10$ixnyzj7CQAft6d6LTvxuyejpUJnIzeYmJR014bq7I3FPahLl/ob6K', 'visitor', 'active', 'uploads/profile_photos/1756590661_download (1).jpeg', '2025-08-30 21:51:01'),
(8, 'Tadique Ahmed', 'rafique@gmail.com', '$2y$10$NIZb.IY8Vdvq2eTry8vwXOYA0aMydJkKW8tpK7mDUVQi7eX/FucuO', 'visitor', 'active', NULL, '2025-09-01 11:19:12');

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
(3, 4, 3, '2025-08-30 21:45:01'),
(4, 4, 3, '2025-08-30 23:30:55'),
(5, 4, 3, '2025-09-01 04:01:33'),
(6, 4, 3, '2025-09-01 05:51:58'),
(7, 4, 3, '2025-09-01 05:52:31'),
(8, 4, 5, '2025-09-01 11:35:55');

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
-- Indexes for table `heroes`
--
ALTER TABLE `heroes`
  ADD PRIMARY KEY (`hero_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `uploaded_by_user_id` (`uploaded_by_user_id`);

--
-- Indexes for table `timeline_events`
--
ALTER TABLE `timeline_events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `event_date` (`event_date`),
  ADD KEY `category` (`category`),
  ADD KEY `added_by_user_id` (`added_by_user_id`);

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
  MODIFY `artifact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `heroes`
--
ALTER TABLE `heroes`
  MODIFY `hero_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `timeline_events`
--
ALTER TABLE `timeline_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_view_history`
--
ALTER TABLE `user_view_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Constraints for table `timeline_events`
--
ALTER TABLE `timeline_events`
  ADD CONSTRAINT `timeline_events_ibfk_1` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

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
