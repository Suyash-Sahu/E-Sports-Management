-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 07:25 PM
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
-- Database: `esports`
--

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `badge_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `level` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`badge_id`, `name`, `level`, `description`, `image_url`) VALUES
(1, 'Inter-college', 'Inter-college', 'Default level for new players', NULL),
(2, 'College', 'College', 'Awarded for winning an Inter-college tournament', NULL),
(3, 'State', 'State', 'Awarded for winning a College-level tournament', NULL),
(4, 'All India', 'All India', 'Awarded for winning a State-level tournament', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(11) NOT NULL,
  `game_name` varchar(100) NOT NULL,
  `domain` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `round_number` int(11) NOT NULL,
  `player1_id` int(11) NOT NULL,
  `player2_id` int(11) NOT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed') DEFAULT 'scheduled',
  `scheduled_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `match_history`
--

CREATE TABLE `match_history` (
  `history_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `result` enum('win','loss','draw') NOT NULL,
  `score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizer_profiles`
--

CREATE TABLE `organizer_profiles` (
  `organizer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `organization_name` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizer_profiles`
--

INSERT INTO `organizer_profiles` (`organizer_id`, `user_id`, `organization_name`, `contact_number`, `contact_email`, `contact_phone`) VALUES
(1, 5, 'Gamez', '1234567890', 'xyz@gmail.com', '1234567890');

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_domain` varchar(50) NOT NULL,
  `in_game_name` varchar(50) NOT NULL,
  `rank` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`player_id`, `user_id`, `game_domain`, `in_game_name`, `rank`, `bio`, `profile_picture`) VALUES
(2, 7, 'CS:GO', 'ya', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `player_badges`
--

CREATE TABLE `player_badges` (
  `player_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `awarded_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player_badges`
--

INSERT INTO `player_badges` (`player_id`, `badge_id`, `awarded_date`) VALUES
(7, 1, '2025-05-01 05:50:52');

-- --------------------------------------------------------

--
-- Table structure for table `player_participation`
--

CREATE TABLE `player_participation` (
  `participation_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `match_result` enum('Win','Loss','Pending') DEFAULT 'Pending',
  `badge_earned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player_profiles`
--

CREATE TABLE `player_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `college_name` varchar(150) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `selected_game_domain` varchar(100) NOT NULL,
  `skill_level` varchar(50) DEFAULT 'Beginner',
  `badge_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player_profiles`
--

INSERT INTO `player_profiles` (`profile_id`, `user_id`, `full_name`, `age`, `gender`, `college_name`, `state`, `city`, `selected_game_domain`, `skill_level`, `badge_count`) VALUES
(3, 7, 'Suyash Sahu', 19, 'Male', 'AMC', 'karnataka', 'Bengaluru', 'CS:GO', 'Beginner', 0);

-- --------------------------------------------------------

--
-- Table structure for table `player_rankings`
--

CREATE TABLE `player_rankings` (
  `player_id` int(11) NOT NULL,
  `game_domain` varchar(50) NOT NULL,
  `rank_points` int(11) DEFAULT 1000,
  `matches_played` int(11) DEFAULT 0,
  `matches_won` int(11) DEFAULT 0,
  `matches_lost` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `tournament_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `game_name` varchar(50) NOT NULL,
  `level` enum('Inter-college','College','State','All India','Custom/Open') DEFAULT 'Inter-college',
  `format` varchar(50) NOT NULL DEFAULT 'Single Elimination',
  `team_required` enum('solo','team','both') NOT NULL DEFAULT 'both',
  `tournament_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `registration_deadline` datetime NOT NULL,
  `max_players` int(11) NOT NULL,
  `entry_fee` decimal(10,2) NOT NULL,
  `prize_pool` decimal(10,2) NOT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`tournament_id`, `organizer_id`, `game_name`, `level`, `format`, `team_required`, `tournament_name`, `description`, `start_date`, `end_date`, `registration_deadline`, `max_players`, `entry_fee`, `prize_pool`, `status`, `created_at`) VALUES
(1, 5, 'PUBG', 'Inter-college', 'Single Elimination', 'both', 'Battleground Blitz', 'Get ready for the ultimate test of skill and survival in Battleground Blitz: BGMI Showdown! Join players from across the region in an adrenaline-pumping tournament where only the sharpest shooters and smartest strategists survive.', '2025-04-29 00:00:00', '2025-04-30 00:00:00', '2025-04-30 00:00:00', 20, 100.00, 10000.00, 'completed', '2025-04-29 11:50:19');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_players`
--

CREATE TABLE `tournament_players` (
  `tournament_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('registered','participated','disqualified') DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('player','organizer') NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `user_type`, `is_verified`, `verification_token`, `token_expiry`, `created_at`) VALUES
(5, 'gamer123', 'xyz@gmail.com', '$2y$10$6R5QkOqGE/vXGspoiamMQegj3X/w2WqDL6I5wZO/lK3jqVlB/UcYa', 'organizer', 0, NULL, NULL, '2025-04-29 11:45:31'),
(7, 'ya', 'ya@gmail.com', '$2y$10$7Un.yHXINdC4A/R05U17sefwtv4zeuwkSUJDzy4OARNiNnX/UvD3K', 'player', 0, NULL, NULL, '2025-05-01 05:50:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badge_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`),
  ADD UNIQUE KEY `game_name` (`game_name`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `player1_id` (`player1_id`),
  ADD KEY `player2_id` (`player2_id`),
  ADD KEY `winner_id` (`winner_id`);

--
-- Indexes for table `match_history`
--
ALTER TABLE `match_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `organizer_profiles`
--
ALTER TABLE `organizer_profiles`
  ADD PRIMARY KEY (`organizer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `player_badges`
--
ALTER TABLE `player_badges`
  ADD PRIMARY KEY (`player_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `player_participation`
--
ALTER TABLE `player_participation`
  ADD PRIMARY KEY (`participation_id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `player_profiles`
--
ALTER TABLE `player_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `player_rankings`
--
ALTER TABLE `player_rankings`
  ADD PRIMARY KEY (`player_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`tournament_id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `tournament_players`
--
ALTER TABLE `tournament_players`
  ADD PRIMARY KEY (`tournament_id`,`player_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `badge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_history`
--
ALTER TABLE `match_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizer_profiles`
--
ALTER TABLE `organizer_profiles`
  MODIFY `organizer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `player_participation`
--
ALTER TABLE `player_participation`
  MODIFY `participation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_profiles`
--
ALTER TABLE `player_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`player1_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `match_history`
--
ALTER TABLE `match_history`
  ADD CONSTRAINT `match_history_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_history_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `organizer_profiles`
--
ALTER TABLE `organizer_profiles`
  ADD CONSTRAINT `organizer_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `player_badges`
--
ALTER TABLE `player_badges`
  ADD CONSTRAINT `player_badges_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`badge_id`) ON DELETE CASCADE;

--
-- Constraints for table `player_participation`
--
ALTER TABLE `player_participation`
  ADD CONSTRAINT `player_participation_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player_profiles` (`profile_id`),
  ADD CONSTRAINT `player_participation_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`);

--
-- Constraints for table `player_profiles`
--
ALTER TABLE `player_profiles`
  ADD CONSTRAINT `player_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `player_rankings`
--
ALTER TABLE `player_rankings`
  ADD CONSTRAINT `player_rankings_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_players`
--
ALTER TABLE `tournament_players`
  ADD CONSTRAINT `tournament_players_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tournament_players_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
