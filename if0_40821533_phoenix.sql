-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql205.infinityfree.com
-- Generation Time: Jan 05, 2026 at 12:28 PM
-- Server version: 11.4.9-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40821533_phoenix`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `username`, `message`, `created_at`) VALUES
(1, 5, 'test', 'Hey I have a suggestion you can add a chatbot ', '2026-01-05 10:23:51'),
(2, 5, 'test', 'Hey I have a suggestion you can add a chatbot ', '2026-01-05 10:25:31'),
(3, 6, 'Hardik', 'Hello, this is good application , 4.5 pr for my think', '2026-01-05 14:34:22');

-- --------------------------------------------------------

--
-- Table structure for table `journal`
--

CREATE TABLE `journal` (
  `id` int(11) NOT NULL,
  `entry` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `journal`
--

INSERT INTO `journal` (`id`, `entry`, `created_at`, `user_id`) VALUES
(1, 'i feel an urge onn seeing yor photo in youtube video but insted of falling fantasy for dopamine i build this app', '2026-01-02 22:59:03', 1),
(2, 'i feel an urge onn seeing yor photo in youtube video but insted of falling fantasy for dopamine i build this app', '2026-01-02 22:59:11', 1),
(3, 'i lost the braintech compittion i was arogant i will this all grdes to prove i can change.', '2026-01-03 19:27:25', 1),
(4, '', '2026-01-04 04:01:21', 5),
(5, '', '2026-01-04 04:01:30', 5),
(6, 'mr ola ola', '2026-01-04 07:35:02', 10),
(7, 'today i have done many things updated the front work today i add the feedback and app purpose button in the app menu and working launching the report feature i tried to create a fake gf just pllaned didnt execute i know what happend in the 7th class i will not do that i will focus on improving my skills not copy pasting thankyou god for evrything', '2026-01-05 08:30:10', 5);

-- --------------------------------------------------------

--
-- Table structure for table `streak`
--

CREATE TABLE `streak` (
  `id` int(11) NOT NULL,
  `start_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'active',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `streak`
--

INSERT INTO `streak` (`id`, `start_date`, `status`, `user_id`) VALUES
(1, '2026-01-02 22:28:26', 'active', 1),
(2, '2026-01-04 01:18:19', 'active', 1),
(3, '2026-01-04 12:29:06', 'active', 1),
(4, '2026-01-04 01:39:36', 'active', 5),
(5, '2026-01-04 02:30:12', 'active', 6),
(6, '2026-01-04 03:06:15', 'active', 7),
(7, '2026-01-04 03:17:48', 'active', 8),
(8, '2026-01-04 03:44:07', 'active', 8),
(9, '2026-01-04 06:17:23', 'active', 9),
(10, '2026-01-04 07:33:48', 'active', 10),
(11, '2026-01-04 07:35:40', 'active', 10),
(12, '2026-01-04 08:33:55', 'active', 7),
(13, '2026-01-04 10:00:32', 'active', 6),
(14, '2026-01-04 21:03:05', 'active', 11),
(15, '2026-01-04 22:41:21', 'active', 12),
(16, '2026-01-04 22:46:37', 'active', 13),
(17, '2026-01-04 22:48:35', 'active', 14),
(18, '2026-01-05 06:59:25', 'active', 15),
(19, '2026-01-05 07:31:06', 'active', 16);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'Sandip', '$2y$10$R1.iMSzzgceZajz/iR31meSWNu1WO2QQ10q2SmOFMMlf4r4IZVtnu', '2026-01-04 01:10:04'),
(4, 'Admin', '$2y$10$5dwHKPg5rl6Q.WyK5IFELuamDNIrqS..WiIXIUH87WerqZ84ghsdi', '2026-01-04 01:12:56'),
(5, 'test', '$2y$10$TcPX9Z2vVC3XJKM4NmblK.t3MwOtZTqrekMMOlJ57VwKrcako1s0m', '2026-01-04 01:39:15'),
(6, 'Hardik', '$2y$10$qg5v.wCZqXfGclUBRrO34.ivK7ow6gEOARpzytDU66kfahd0Ukfne', '2026-01-04 02:30:02'),
(7, 'Mia khalifa ', '$2y$10$T3pQCSunX4qeNNycvN9IyOqB4kWaSL7cUS6u2Cu32T4m9PNrbCt2S', '2026-01-04 03:06:04'),
(8, 'Smile ', '$2y$10$AvoOWPs.xhgI09hksbMwJes9hC2aAq9hVBAFV7wTgXRENo7WzCWJi', '2026-01-04 03:16:36'),
(10, 'michael', '$2y$10$lm6.xmqnrtHFyTlRyElvz.SeyMfsoWMIM44dUk9e8Y51knDT71P8i', '2026-01-04 07:33:39'),
(11, 'tusharkumar', '$2y$10$NcKzGti/jVVA1OF0XMc61.mlv9VbeJgGthXXNmxwqzoB3Yn5qQdHy', '2026-01-04 21:02:54'),
(12, 'Kishan sodha01', '$2y$10$7K5f7OsUz9j2gFR..nluye/lqzqUF7tr.zH1ZoXrXC9JQHZfUB6w2', '2026-01-04 22:40:55'),
(13, 'Aamin', '$2y$10$71bIcdXr6nMpDIb7IWeNAu.sVeshoy9JVgK/XK4Ui73ESwRjb1EH.', '2026-01-04 22:46:22'),
(14, 'Ayan', '$2y$10$cLzqn3oInLatrJWs3fJT/OK661PXgGCu5.XjKhsJwExYa8oaHcZB.', '2026-01-04 22:48:24'),
(15, 'Jagdish Rathod ', '$2y$10$9wXDyRKf5owOpYGoitseGOAugZ0.uU.Fjsc.3ictJ61cH6KCgNmc2', '2026-01-05 06:57:48'),
(16, 'Rohitdas', '$2y$10$NOIxPY2lbpR1JTNR.vLUrO.104k3rLQ.BuJ7GKnpoXZM1bHJ7bmVy', '2026-01-05 07:30:51');

-- --------------------------------------------------------

--
-- Table structure for table `wisdom`
--

CREATE TABLE `wisdom` (
  `id` int(11) NOT NULL,
  `quote` text DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wisdom`
--

INSERT INTO `wisdom` (`id`, `quote`, `author`, `type`) VALUES
(1, 'we suffer more in imagination than in reality.', 'seneca', 'rational'),
(2, 'You do not rise to the level of your goals. You fall to the level of your systems.', 'James Clear', 'Discipline'),
(3, 'He who has a why to live for can bear almost any how.', 'Friedrich Nietzsche', 'Rational'),
(4, 'Discipline is doing what you hate to do, but doing it like you love it.', 'Mike Tyson', 'Motivation'),
(5, 'The only way out is through.', 'Robert Frost', 'Stoic'),
(6, 'Every action you take is a vote for the type of person you wish to become.', 'James Clear', 'Habit'),
(7, 'You do not rise to the level of your goals. You fall to the level of your systems.', 'James Clear', 'System'),
(8, 'The most effective way to change your habits is to focus not on what you want to achieve, but on who you wish to become.', 'James Clear', 'Identity'),
(9, '“In your actions, don’t procrastinate. In your conversations, don’t confuse. In your thoughts, don’t wander. In your soul, don’t be passive or aggressive. In your life, don’t be all about business.” ', '~ Marcus Aurelius', 'stoic'),
(10, '“It’s time you realized that you have something in you more powerful and miraculous than the things that affect you and make you dance like a puppet.”', '~:Marcus Aurelius', 'Stoic'),
(11, 'Listen more than you speak. You have two ears and one mouth for a reason.', 'Epictetus', 'Communication'),
(12, 'When talking to someone, maintain eye contact 70% of the time.', 'Psychology', 'Communication'),
(13, 'The sweetest sound to any person is their own name. Use it.', 'Dale Carnegie', 'Communication'),
(14, 'Pause before you reply. Silence is a power move.', 'Robert Greene', 'Communication'),
(15, 'Listen more than you speak. You have two ears and one mouth for a reason.', 'Epictetus', 'Communication'),
(16, 'When talking to someone, maintain eye contact 70% of the time.', 'Psychology', 'Communication'),
(17, 'The sweetest sound to any person is their own name. Use it.', 'Dale Carnegie', 'Communication'),
(18, 'Pause before you reply. Silence is a power move.', 'Robert Greene', 'Communication');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journal`
--
ALTER TABLE `journal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `streak`
--
ALTER TABLE `streak`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `wisdom`
--
ALTER TABLE `wisdom`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `journal`
--
ALTER TABLE `journal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `streak`
--
ALTER TABLE `streak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `wisdom`
--
ALTER TABLE `wisdom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
