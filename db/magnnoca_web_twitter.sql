-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 04, 2021 at 09:12 AM
-- Server version: 10.3.27-MariaDB-log-cll-lve
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `magnnoca_web_twitter`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `user_fk` bigint(20) UNSIGNED NOT NULL,
  `comment_body` varchar(140) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tweet_fk` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `user_fk`, `comment_body`, `created_at`, `tweet_fk`) VALUES
(1, 25, 'Hello Guys', '2021-02-24 10:18:41', 4);

--
-- Triggers `comments`
--
DELIMITER $$
CREATE TRIGGER `decreaseCommentCount` AFTER DELETE ON `comments` FOR EACH ROW UPDATE tweets
SET tweets.num_comments = tweets.num_comments - 1
WHERE OLD.tweet_fk = tweets.tweet_id
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `increaseCommentCount` AFTER INSERT ON `comments` FOR EACH ROW UPDATE tweets
SET tweets.num_comments = tweets.num_comments + 1
WHERE tweets.tweet_id = NEW.tweet_fk
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `latest_message_fk` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `latest_message_fk`, `created_at`, `type`) VALUES
(1, 1, '2020-12-15 22:09:47', 0);

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `follower_fk` bigint(20) UNSIGNED NOT NULL,
  `followee_fk` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`follower_fk`, `followee_fk`, `created_at`) VALUES
(5, 23, '2021-01-08 19:53:31'),
(13, 1, '2020-12-15 22:09:13'),
(17, 22, '2020-12-15 22:58:34');

--
-- Triggers `follows`
--
DELIMITER $$
CREATE TRIGGER `decreaseFollowerCount` BEFORE DELETE ON `follows` FOR EACH ROW UPDATE users
SET users.follower_count = users.follower_count - 1
WHERE users.user_id = OLD.follower_fk
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `decreaseFollowingCount` BEFORE DELETE ON `follows` FOR EACH ROW UPDATE users
SET users.following_count = users.following_count - 1
WHERE users.user_id = OLD.followee_fk
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `increaseFollowerCount` AFTER INSERT ON `follows` FOR EACH ROW UPDATE users
SET	users.follower_count = users.follower_count + 1
WHERE user_id = NEW.follower_fk
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `increaseFollowingCount` AFTER INSERT ON `follows` FOR EACH ROW UPDATE users
SET	users.following_count = users.following_count + 1
WHERE user_id = NEW.followee_fk
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `tweet_fk` bigint(20) UNSIGNED NOT NULL,
  `user_fk` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`tweet_fk`, `user_fk`, `created_at`) VALUES
(5, 25, '2021-02-24 10:18:35');

--
-- Triggers `likes`
--
DELIMITER $$
CREATE TRIGGER `decreaseLikesCount` AFTER DELETE ON `likes` FOR EACH ROW UPDATE tweets
SET tweets.num_likes = tweets.num_likes - 1
WHERE tweets.tweet_id = OLD.tweet_fk
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `increaseLikesCount` AFTER INSERT ON `likes` FOR EACH ROW UPDATE tweets
SET tweets.num_likes = tweets.num_likes + 1
WHERE tweets.tweet_id = NEW.tweet_fk
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `body` varchar(240) NOT NULL,
  `sender_fk` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `conversation_fk` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `body`, `sender_fk`, `created_at`, `seen`, `conversation_fk`) VALUES
(1, 'hi', 1, '2020-12-15 22:09:47', 0, 1);

--
-- Triggers `messages`
--
DELIMITER $$
CREATE TRIGGER `updateLastMessageFk` AFTER INSERT ON `messages` FOR EACH ROW UPDATE conversations
SET conversations.latest_message_fk = NEW.message_id
WHERE conversations.conversation_id = NEW.conversation_fk
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `sender_fk` bigint(20) UNSIGNED NOT NULL,
  `receiver_fk` bigint(20) UNSIGNED NOT NULL,
  `notification_type_fk` bigint(20) UNSIGNED NOT NULL,
  `target_url_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `sender_fk`, `receiver_fk`, `notification_type_fk`, `target_url_id`) VALUES
(1, 9, 1, 2, 45);

-- --------------------------------------------------------

--
-- Table structure for table `notification_baseurls`
--

CREATE TABLE `notification_baseurls` (
  `notification_baseurl_id` bigint(20) UNSIGNED NOT NULL,
  `baseurl` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notification_baseurls`
--

INSERT INTO `notification_baseurls` (`notification_baseurl_id`, `baseurl`) VALUES
(1, 'profile'),
(2, 'status');

-- --------------------------------------------------------

--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `notification_type_id` bigint(20) UNSIGNED NOT NULL,
  `notification_message` varchar(50) NOT NULL,
  `notification_baseurl_fk` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notification_types`
--

INSERT INTO `notification_types` (`notification_type_id`, `notification_message`, `notification_baseurl_fk`) VALUES
(1, 'liked your tweet', 2),
(2, 'commented your tweet', 2),
(3, 'followed you', 1);

-- --------------------------------------------------------

--
-- Table structure for table `recent_search`
--

CREATE TABLE `recent_search` (
  `user_search_fk` bigint(20) UNSIGNED NOT NULL,
  `search_term` varchar(140) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recent_search`
--

INSERT INTO `recent_search` (`user_search_fk`, `search_term`, `created_at`) VALUES
(22, 'Sheila Kunde', '2020-12-15 22:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `tweets`
--

CREATE TABLE `tweets` (
  `tweet_id` bigint(20) UNSIGNED NOT NULL,
  `body` varchar(140) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_fk` bigint(20) UNSIGNED NOT NULL,
  `num_likes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `num_comments` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tweets`
--

INSERT INTO `tweets` (`tweet_id`, `body`, `created_at`, `user_fk`, `num_likes`, `num_comments`) VALUES
(1, 'www', '2020-12-15 22:03:38', 1, 0, 0),
(2, 'wwwxx', '2020-12-15 22:03:40', 1, 0, 0),
(4, 'asd', '2021-02-24 10:18:25', 25, 0, 1),
(5, 'monkaS', '2021-02-24 10:18:33', 25, 1, 0);

--
-- Triggers `tweets`
--
DELIMITER $$
CREATE TRIGGER `decreaseTweetCount` AFTER DELETE ON `tweets` FOR EACH ROW UPDATE users
SET users.tweet_count = users.tweet_count -1
WHERE users.user_id = OLD.user_fk
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `increaseTweetCount` AFTER INSERT ON `tweets` FOR EACH ROW UPDATE users
SET users.tweet_count = users.tweet_count + 1
WHERE users.user_id = NEW.user_fk
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `follower_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `following_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tweet_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `created_at`, `email`, `password`, `follower_count`, `following_count`, `tweet_count`, `first_name`, `last_name`, `active`) VALUES
(1, '2020-12-14 15:46:20', 'lswift@gmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 1, 2, 'Lauren', 'Miller', 1),
(2, '2020-12-14 15:46:20', 'ubaldo57@kuvalis.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Jakayla', 'Zieme', 1),
(3, '2020-12-14 15:46:20', 'yroberts@gmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Presley', 'DuBuque', 1),
(4, '2020-12-14 15:46:20', 'rafaela21@buckridge.biz', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Irving', 'Stamm', 1),
(5, '2020-12-14 15:46:20', 'edgar.senger@abernathy.org', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 1, 0, 0, 'Wilfred', 'Balistreri', 1),
(6, '2020-12-14 15:46:20', 'kwaelchi@howell.info', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Jackson', 'Brakus', 1),
(7, '2020-12-14 15:46:20', 'elizabeth61@hotmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Korey', 'Bednar', 1),
(8, '2020-12-14 15:46:20', 'lind.carmela@hotmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Kasandra', 'Nader', 1),
(9, '2020-12-14 15:46:20', 'austyn.altenwerth@olson.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Margarett', 'Mraz', 1),
(10, '2020-12-14 15:46:20', 'thagenes@hotmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Reva', 'Cassin', 1),
(11, '2020-12-14 15:46:20', 'bo.cummings@gmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Adonis', 'Klein', 1),
(12, '2020-12-14 15:46:20', 'breitenberg.dorian@considine.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Doyle', 'Dooley', 1),
(13, '2020-12-14 15:46:20', 'will.cronin@hotmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 1, 0, 0, 'Sheila', 'Kunde', 1),
(14, '2020-12-14 15:46:20', 'sallie.lakin@gmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Tom', 'Zieme', 1),
(15, '2020-12-14 15:46:20', 'lyda77@tremblay.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Kirk', 'Weimann', 1),
(16, '2020-12-14 15:46:20', 'huel.alvah@gmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Amos', 'Weimann', 1),
(17, '2020-12-14 15:46:20', 'kschaden@rutherford.info', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 1, 0, 0, 'Myles', 'Stracke', 1),
(18, '2020-12-14 15:46:20', 'kuphal.ramon@strosin.org', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Carlos', 'Rogahn', 1),
(19, '2020-12-14 15:46:20', 'arch16@hotmail.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Baron', 'Grimes', 1),
(20, '2020-12-14 15:46:20', 'zcasper@wilderman.com', '$2y$10$VYx6bJv0lmkEe2UauQ9uWeuclnJrNqbNB/JSw6uS/Vyaq6uc7l7vS', 0, 0, 0, 'Kasandra', 'Gleichner', 1),
(22, '2020-12-15 22:40:04', 'e@e.dk', '$2y$10$i80zYRB0UWzfRwQ3oB7NtuBOKM6QEL3IQvg96gmesOT1cR2YD3lfe', 0, 1, 0, 'ee', 'ee', 1),
(23, '2021-01-08 19:53:13', 'mag@mag.dk', '$2y$10$ilTk.iBjetRbm2F9XLuQGeTFKOs8DcaBi.zsvuG/BSAR2Mtea0tsG', 0, 1, 0, 'magnus', 'lundstrom', 1),
(25, '2021-02-24 10:16:59', 'b@b.dk', '$2y$10$dAq.hfqEiBBWkYkCTN.rfe4ir0NH/cq8aBHuMH3cbv5aUvNgENZjS', 0, 0, 2, 'Magnus', 'Lundstrøm', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users_in_conversations`
--

CREATE TABLE `users_in_conversations` (
  `conversation_fk` bigint(20) UNSIGNED NOT NULL,
  `user_fk` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users_in_conversations`
--

INSERT INTO `users_in_conversations` (`conversation_fk`, `user_fk`) VALUES
(1, 1),
(1, 6);

-- --------------------------------------------------------

--
-- Table structure for table `user_descriptions`
--

CREATE TABLE `user_descriptions` (
  `user_fk` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(140) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_descriptions`
--

INSERT INTO `user_descriptions` (`user_fk`, `description`) VALUES
(1, 'I\'m very nice :)');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `user_fk` (`user_fk`),
  ADD KEY `tweet_fk` (`tweet_fk`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD UNIQUE KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`follower_fk`,`followee_fk`),
  ADD KEY `cascade_followee_delete` (`followee_fk`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`tweet_fk`,`user_fk`),
  ADD KEY `cascade_like_user_delete` (`user_fk`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD UNIQUE KEY `message_id` (`message_id`),
  ADD KEY `cascade_conv_delete` (`conversation_fk`),
  ADD KEY `cascade_sender_delete` (`sender_fk`);
ALTER TABLE `messages` ADD FULLTEXT KEY `body` (`body`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD UNIQUE KEY `notification_id` (`notification_id`),
  ADD KEY `cascade_noti_sender_delete` (`sender_fk`),
  ADD KEY `cascade_noti_receiver_delete` (`receiver_fk`),
  ADD KEY `link_noti_type` (`notification_type_fk`);

--
-- Indexes for table `notification_baseurls`
--
ALTER TABLE `notification_baseurls`
  ADD PRIMARY KEY (`notification_baseurl_id`);

--
-- Indexes for table `notification_types`
--
ALTER TABLE `notification_types`
  ADD PRIMARY KEY (`notification_type_id`),
  ADD KEY `baseurl_link` (`notification_baseurl_fk`);

--
-- Indexes for table `recent_search`
--
ALTER TABLE `recent_search`
  ADD PRIMARY KEY (`user_search_fk`,`search_term`);

--
-- Indexes for table `tweets`
--
ALTER TABLE `tweets`
  ADD PRIMARY KEY (`tweet_id`),
  ADD KEY `cascade_tweets_user_delete` (`user_fk`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users_in_conversations`
--
ALTER TABLE `users_in_conversations`
  ADD PRIMARY KEY (`conversation_fk`,`user_fk`),
  ADD KEY `cascade_user_delete` (`user_fk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tweets`
--
ALTER TABLE `tweets`
  MODIFY `tweet_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
