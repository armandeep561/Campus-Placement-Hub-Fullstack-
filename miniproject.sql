-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2025 at 05:58 AM
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
-- Database: `miniproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `companyname` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`companyname`) VALUES
('Amazon'),
('Apple'),
('Capgemini'),
('Google'),
('Infosys'),
('Microsoft'),
('TCS');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `regdno` varchar(11) NOT NULL,
  `cgpa` float(10,2) DEFAULT 0.00,
  `backlogs` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`regdno`, `cgpa`, `backlogs`) VALUES
('19331a1201', 7.22, 0),
('19331a1202', 4.00, 2),
('19331a1203', 7.00, 0),
('19331a1204', 7.12, 2),
('19331a1208', 5.43, 0),
('19331a1264', 6.67, 0),
('19331a1299', 6.43, 3);

-- --------------------------------------------------------

--
-- Table structure for table `package`
--

CREATE TABLE `package` (
  `regdno` varchar(11) NOT NULL,
  `id` int(11) NOT NULL,
  `companyname` varchar(30) NOT NULL,
  `package` float NOT NULL,
  `file` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package`
--

INSERT INTO `package` (`regdno`, `id`, `companyname`, `package`, `file`) VALUES
('19331a1201', 987660, 'Infosys', 14, '4379-CV ARMANDEEP SINGH.pdf'),
('19331a1201', 987668, 'Google', 13, '5533-abvbc.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `role` varchar(20) NOT NULL,
  `staff_name` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`role`, `staff_name`, `password`) VALUES
('Admin', 'Admin', 'Admin_123'),
('Hod', 'Hod', 'Hod_123');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `regdno` varchar(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `contact` bigint(11) NOT NULL,
  `dob` date NOT NULL,
  `password` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`regdno`, `name`, `email`, `contact`, `dob`, `password`) VALUES
('19331a1201', 'Armandeep', 'armandeep0088@gmail.com', 7009833561, '2005-11-26', 'Arman_123'),
('19331a1202', 'Alice', 'alice@gmail.com', 9999955555, '2001-07-02', 'Alice_123'),
('19331a1203', 'Bharat', 'bharat@gmail.com', 4444455555, '2005-01-01', 'Bharat_123'),
('19331a1204', 'John', 'john@gmail.com', 3333344444, '2001-07-04', 'John_123'),
('19331a1208', 'Mike', 'mike@gmail.com', 4444477777, '2002-07-08', 'Mike_123'),
('19331a1264', 'Boyyd', 'boyyd@gmail.com', 6666699999, '2001-07-21', 'Boyyd_123'),
('19331a1299', 'Victor', 'victor@gmail.com', 5555588888, '2001-07-05', 'Victor_123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`companyname`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`regdno`),
  ADD KEY `regdno` (`regdno`);

--
-- Indexes for table `package`
--
ALTER TABLE `package`
  ADD PRIMARY KEY (`id`),
  ADD KEY `regdno` (`regdno`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`role`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`regdno`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `package`
--
ALTER TABLE `package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=987669;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks` FOREIGN KEY (`regdno`) REFERENCES `student` (`regdno`);

--
-- Constraints for table `package`
--
ALTER TABLE `package`
  ADD CONSTRAINT `regdno` FOREIGN KEY (`regdno`) REFERENCES `student` (`regdno`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
