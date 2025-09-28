-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 12:52 PM
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
-- Database: `publication_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `edit_history`
--

CREATE TABLE `edit_history` (
  `edit_ID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `pub_ID` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_ID` int(11) NOT NULL,
  `sender_email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submit_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keyword`
--

CREATE TABLE `keyword` (
  `key_ID` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keyword`
--

INSERT INTO `keyword` (`key_ID`, `key_name`) VALUES
(1, 'Machine Learning'),
(2, 'Data Mining'),
(3, 'Web Development'),
(4, 'IoT'),
(5, 'Database System');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `login_ID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `pub_ID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `abstract` text NOT NULL,
  `publish_date` date NOT NULL,
  `status` varchar(50) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `type_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publication`
--

INSERT INTO `publication` (`pub_ID`, `title`, `abstract`, `publish_date`, `status`, `file`, `type_ID`) VALUES
(1, 'Deep Learning for Image Recognition in Thai Traffic', 'This paper explores the use of deep convolutional networks...', '2024-05-15', 'Approved', 'file_1.pdf', 1),
(2, 'A Survey on NoSQL Databases', 'A comprehensive survey of current NoSQL database architectures...', '2024-06-01', 'Pending', 'file_2.pdf', 2),
(3, 'Web Application Security using PHP', 'Protecting web applications from common vulnerabilities like XSS and SQL Injection.', '2023-11-20', 'Approved', 'file_3.pdf', 3);

-- --------------------------------------------------------

--
-- Table structure for table `publicationtype`
--

CREATE TABLE `publicationtype` (
  `type_ID` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publicationtype`
--

INSERT INTO `publicationtype` (`type_ID`, `type_name`) VALUES
(1, 'Journal Article'),
(2, 'Conference Proceeding'),
(3, 'Thesis/Dissertation'),
(4, 'Book Chapter');

-- --------------------------------------------------------

--
-- Table structure for table `pub_author`
--

CREATE TABLE `pub_author` (
  `pub_ID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `is_creator` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pub_author`
--

INSERT INTO `pub_author` (`pub_ID`, `UID`, `is_creator`) VALUES
(1, 2, 0),
(1, 3, 1),
(2, 3, 1),
(3, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pub_keyword`
--

CREATE TABLE `pub_keyword` (
  `pub_ID` int(11) NOT NULL,
  `key_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pub_keyword`
--

INSERT INTO `pub_keyword` (`pub_ID`, `key_ID`) VALUES
(1, 1),
(1, 4),
(3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UID`, `username`, `password`, `email`, `user_type_ID`) VALUES
(1, 'admin', '$2y$10$pf0lyD6CbVbFGz2xGqBuVu7bA7tIe1it33Amjh8JYNX40WU3FRTa2', 'admin1@test.com', 1),
(2, 'officer_user', '$2y$10$913eYxHMMcY6qN0scGSMuehbvTpaRs0K3jGl/AOpXI.zGxUkAA9Ju', 'officer1@test.com', 2),
(3, 'lecturer_user', '$2y$10$6pxC0ukTovkcoII1W5pj5uVGL/fKG6kXZ3OEtIwRRKY5.NUDbOrDW', 'lecturer1@test.com', 3);

-- --------------------------------------------------------

--
-- Table structure for table `user_type`
--

CREATE TABLE `user_type` (
  `user_type_ID` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_type`
--

INSERT INTO `user_type` (`user_type_ID`, `type_name`) VALUES
(1, 'Admin'),
(2, 'Officer'),
(3, 'Lecturer'),
(4, 'Student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `edit_history`
--
ALTER TABLE `edit_history`
  ADD PRIMARY KEY (`edit_ID`),
  ADD KEY `fk_edit_user` (`UID`),
  ADD KEY `fk_edit_pub` (`pub_ID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_ID`);

--
-- Indexes for table `keyword`
--
ALTER TABLE `keyword`
  ADD PRIMARY KEY (`key_ID`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_ID`),
  ADD KEY `fk_login_user` (`UID`);

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`pub_ID`),
  ADD KEY `fk_pub_type` (`type_ID`);

--
-- Indexes for table `publicationtype`
--
ALTER TABLE `publicationtype`
  ADD PRIMARY KEY (`type_ID`);

--
-- Indexes for table `pub_author`
--
ALTER TABLE `pub_author`
  ADD PRIMARY KEY (`pub_ID`,`UID`),
  ADD KEY `fk_pa_user` (`UID`);

--
-- Indexes for table `pub_keyword`
--
ALTER TABLE `pub_keyword`
  ADD PRIMARY KEY (`pub_ID`,`key_ID`),
  ADD KEY `fk_pk_key` (`key_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_type` (`user_type_ID`);

--
-- Indexes for table `user_type`
--
ALTER TABLE `user_type`
  ADD PRIMARY KEY (`user_type_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `edit_history`
--
ALTER TABLE `edit_history`
  MODIFY `edit_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keyword`
--
ALTER TABLE `keyword`
  MODIFY `key_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `pub_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `publicationtype`
--
ALTER TABLE `publicationtype`
  MODIFY `type_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_type`
--
ALTER TABLE `user_type`
  MODIFY `user_type_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `edit_history`
--
ALTER TABLE `edit_history`
  ADD CONSTRAINT `fk_edit_pub` FOREIGN KEY (`pub_ID`) REFERENCES `publication` (`pub_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_edit_user` FOREIGN KEY (`UID`) REFERENCES `user` (`UID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `fk_login_user` FOREIGN KEY (`UID`) REFERENCES `user` (`UID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `fk_pub_type` FOREIGN KEY (`type_ID`) REFERENCES `publicationtype` (`type_ID`) ON UPDATE CASCADE;

--
-- Constraints for table `pub_author`
--
ALTER TABLE `pub_author`
  ADD CONSTRAINT `fk_pa_pub` FOREIGN KEY (`pub_ID`) REFERENCES `publication` (`pub_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pa_user` FOREIGN KEY (`UID`) REFERENCES `user` (`UID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pub_keyword`
--
ALTER TABLE `pub_keyword`
  ADD CONSTRAINT `fk_pk_key` FOREIGN KEY (`key_ID`) REFERENCES `keyword` (`key_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pk_pub` FOREIGN KEY (`pub_ID`) REFERENCES `publication` (`pub_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_type` FOREIGN KEY (`user_type_ID`) REFERENCES `user_type` (`user_type_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
