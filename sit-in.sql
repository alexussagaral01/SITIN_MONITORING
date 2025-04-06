-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2025 at 02:52 PM
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
-- Database: `sit-in`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ADMIN_ID` int(11) NOT NULL,
  `USER_NAME` varchar(30) NOT NULL DEFAULT 'admin',
  `PASSWORD_HASH` varchar(30) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `ID` int(11) NOT NULL,
  `TITLE` varchar(255) NOT NULL,
  `CONTENT` text NOT NULL,
  `CREATED_DATE` date NOT NULL,
  `CREATED_BY` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement`
--

INSERT INTO `announcement` (`ID`, `TITLE`, `CONTENT`, `CREATED_DATE`, `CREATED_BY`) VALUES
(1, '', 'GOOD DAY CCS', '2025-03-24', 'ADMIN'),
(2, '', 'ATTENTION CCS', '2025-03-24', 'ADMIN');

-- --------------------------------------------------------

--
-- Table structure for table `curr_sitin`
--

CREATE TABLE `curr_sitin` (
  `SITIN_ID` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `FULL_NAME` varchar(30) NOT NULL,
  `PURPOSE` enum('C Programming','C++ Programming','C# Programming','Java Programming','Python Programming','Other') NOT NULL,
  `LABORATORY` enum('Lab 524','Lab 526','Lab 528','Lab 530','Lab 542','Lab 544') NOT NULL,
  `TIME_IN` time NOT NULL,
  `TIME_OUT` time DEFAULT NULL,
  `DATE` date NOT NULL,
  `STATUS` varchar(10) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curr_sitin`
--
-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FEEDBACK_ID` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `LABORATORY` enum('Lab 524','Lab 526','Lab 528','Lab 530','Lab 542','Lab 544') NOT NULL,
  `DATE` date NOT NULL,
  `FEEDBACK` varchar(255) NOT NULL,
  `RATING` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `STUD_NUM` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `LAST_NAME` varchar(30) NOT NULL,
  `FIRST_NAME` varchar(30) NOT NULL,
  `MID_NAME` varchar(30) NOT NULL,
  `COURSE` enum('BS IN ACCOUNTANCY','BS IN BUSINESS ADMINISTRATION','BS IN CRIMINOLOGY','BS IN CUSTOMS ADMINISTRATION','BS IN INFORMATION TECHNOLOGY','BS IN COMPUTER SCIENCE','BS IN OFFICE ADMINISTRATION','BS IN SOCIAL WORK','BACHELOR OF SECONDARY EDUCATION','BACHELOR OF ELEMENTARY EDUCATION') NOT NULL,
  `YEAR_LEVEL` enum('1st Year','2nd Year','3rd Year','4th Year') NOT NULL,
  `USER_NAME` varchar(30) NOT NULL,
  `PASSWORD_HASH` varchar(255) NOT NULL,
  `UPLOAD_IMAGE` longblob DEFAULT NULL,
  `EMAIL` varchar(30) NOT NULL,
  `ADDRESS` varchar(255) NOT NULL,
  `SESSION` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`STUD_NUM`, `IDNO`, `LAST_NAME`, `FIRST_NAME`, `MID_NAME`, `COURSE`, `YEAR_LEVEL`, `USER_NAME`, `PASSWORD_HASH`, `UPLOAD_IMAGE`, `EMAIL`, `ADDRESS`, `SESSION`) VALUES
(2, 22680649, 'Sagaral', 'Alexus Sundae', 'Jamilo', 'BS IN INFORMATION TECHNOLOGY', '3rd Year', 'alexus123', '$2y$10$9Vx2MQN1h.BjEAgqVZll1u/FvjkI3dKO5dNR.X47IviQcCdduEz92', 0x363764663861393632366239325f363764643861353763303132365f6d656f772e6a7067, 'alexussagaral3@gmail.com', 'Cebu City', 30),
(3, 12312312, 'Cabunilas', 'Vince Bryant', 'N', 'BS IN INFORMATION TECHNOLOGY', '3rd Year', 'Vince123', '$2y$10$UHWV1XqZt8gOPw36Ioski.Nh3gIF8WTXCp9IF94qu1iI9RUwCq.Ku', 0x696d6167652e6a7067, '', '', 30);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ADMIN_ID`);

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `curr_sitin`
--
ALTER TABLE `curr_sitin`
  ADD PRIMARY KEY (`SITIN_ID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FEEDBACK_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`STUD_NUM`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `ADMIN_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `curr_sitin`
--
ALTER TABLE `curr_sitin`
  MODIFY `SITIN_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FEEDBACK_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `STUD_NUM` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
