-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 30, 2022 at 06:30 PM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.2.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vhive_v1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `a_id` int(11) NOT NULL,
  `a_un` varchar(50) NOT NULL COMMENT 'Admin Username',
  `a_pwd` varchar(50) NOT NULL COMMENT 'Admin Password',
  `a_typ` int(11) NOT NULL DEFAULT 0 COMMENT 'Admin type',
  `a_nm` varchar(50) NOT NULL COMMENT 'Admin Name',
  `a_dep` int(11) NOT NULL COMMENT 'Admin Department',
  `a_emp` varchar(50) NOT NULL COMMENT 'Admin Employee ID',
  `a_dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Current Date and time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`a_id`, `a_un`, `a_pwd`, `a_typ`, `a_nm`, `a_dep`, `a_emp`, `a_dt`) VALUES
(3, 'joebeckham', 'Joe123', 0, 'Joe Beckham', 1, '11199B176', '2022-03-21 14:01:40'),
(4, 'Rose12', 'rosemary234', 1, 'Rosemarie ', 4, '11189A156', '2022-03-21 14:01:55'),
(5, 'Henry_01', 'hwashington', 0, 'Henry Hunt', 5, '11199A555', '2022-03-21 14:01:47'),
(6, 'joebeckham', 'Joe123', 0, 'Joe Beckham', 2, '11199B176', '2022-03-21 14:01:58'),
(7, 'Rose12', 'rosemary234', 1, 'Rosemarie ', 2, '11189A156', '2022-03-21 14:02:02'),
(8, 'Henry_01', 'hwashington', 0, 'Henry Hunt', 3, '11199A555', '2022-03-21 14:02:06');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `d_id` int(11) NOT NULL,
  `d_nm` varchar(50) NOT NULL COMMENT 'Department Name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`d_id`, `d_nm`) VALUES
(1, 'cse'),
(2, 'ece'),
(3, 'eee'),
(4, 'civil'),
(5, 'mech'),
(6, 'bsc');

-- --------------------------------------------------------

--
-- Table structure for table `guest`
--

CREATE TABLE `guest` (
  `g_id` int(11) NOT NULL,
  `g_com/inst` varchar(50) NOT NULL COMMENT 'Guest Company/Institute',
  `g_aphn` varchar(50) NOT NULL COMMENT 'Guest Alternate Phone Number',
  `g_eid` varchar(50) NOT NULL COMMENT 'Guest Employee ID',
  `g_vid` int(11) NOT NULL COMMENT 'Guest Visitor ID',
  `g_des` varchar(50) NOT NULL COMMENT 'Guest Designation/role'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `l_id` int(11) NOT NULL,
  `l_nm` varchar(50) NOT NULL COMMENT 'Location Name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`l_id`, `l_nm`) VALUES
(1, 'Visakhapatnam '),
(2, 'Hyderabad'),
(3, 'Chennai'),
(4, 'Bangalore'),
(5, 'Mumbai');

-- --------------------------------------------------------

--
-- Table structure for table `parent`
--

CREATE TABLE `parent` (
  `p_id` int(11) NOT NULL,
  `p_snm` varchar(50) NOT NULL COMMENT 'Student Name(parent''s child)',
  `p_rel` int(11) NOT NULL COMMENT 'Relation with student',
  `p_dep` int(11) NOT NULL COMMENT 'Student Department',
  `p_srn` varchar(50) NOT NULL COMMENT 'Student Registration Number',
  `p_vid` int(11) NOT NULL COMMENT 'Parent Visitor ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `parent`
--

INSERT INTO `parent` (`p_id`, `p_snm`, `p_rel`, `p_dep`, `p_srn`, `p_vid`) VALUES
(1, 'Kamala Jones', 1, 2, '11199A456', 4),
(2, 'Kangana Joy', 1, 3, '11199B128', 2),
(3, 'Latha Bidon', 4, 6, '11199C155', 5),
(4, 'Kamala Jones', 1, 2, '11199A456', 4),
(5, 'Kangana Joy', 1, 3, '11199B128', 2),
(6, 'Latha Bidon', 4, 6, '11199C155', 5);

-- --------------------------------------------------------

--
-- Table structure for table `relation`
--

CREATE TABLE `relation` (
  `r_id` int(11) NOT NULL,
  `r_nm` varchar(50) NOT NULL COMMENT 'Relation Name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `relation`
--

INSERT INTO `relation` (`r_id`, `r_nm`) VALUES
(1, 'Mother'),
(2, 'Father'),
(3, 'Brother'),
(4, 'Sister'),
(5, 'Guardian');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_log`
--

CREATE TABLE `visitor_log` (
  `vl_id` int(11) NOT NULL,
  `vl_dep` int(11) NOT NULL COMMENT 'Visitor''s Host Department',
  `vl_hnm` int(11) NOT NULL COMMENT 'Visitor''s Host Name',
  `vl_hem` varchar(255) NOT NULL COMMENT 'Visitor''s Host Email ID ',
  `vl_hph` varchar(15) NOT NULL COMMENT 'Visitor''s Host Phone Number',
  `vl_pov` varchar(300) NOT NULL COMMENT 'Visitor Purpose of Visit',
  `vl_st` datetime NOT NULL COMMENT 'Visitor Scheduled Date and Time',
  `vl_dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Visitor Date and Time',
  `vl_stat` int(11) NOT NULL DEFAULT 0 COMMENT 'Visitor Status',
  `vl_sdt` datetime NOT NULL COMMENT 'Visitor status changed date and time',
  `vl_rid` varchar(30) NOT NULL COMMENT 'Visitor Request ID',
  `vl_snote` text NOT NULL COMMENT 'Visitor Status note ',
  `vl_vid` int(11) NOT NULL,
  `vl_hid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `visitor_log`
--

INSERT INTO `visitor_log` (`vl_id`, `vl_dep`, `vl_hnm`, `vl_hem`, `vl_hph`, `vl_pov`, `vl_st`, `vl_dt`, `vl_stat`, `vl_sdt`, `vl_rid`, `vl_snote`, `vl_vid`, `vl_hid`) VALUES
(1, 1, 0, 'asdcvasdvasd', '', 'sdvasdvasd', '2022-03-30 08:14:59', '2022-03-30 07:11:54', 0, '0000-00-00 00:00:00', '', '', 0, 0),
(3, 1, 0, '', '', 'test', '2022-03-30 08:14:59', '2022-03-30 07:25:53', 0, '0000-00-00 00:00:00', '', '', 0, 0),
(4, 1, 0, '', '', 'test', '2022-03-30 08:14:59', '2022-03-30 12:16:30', 1, '0000-00-00 00:00:00', '123453', '', 1, 0),
(5, 1, 3, '', '', 'test', '2022-03-30 08:14:59', '2022-03-30 12:16:28', 1, '0000-00-00 00:00:00', '635846', '', 1, 0),
(6, 1, 3, '', '', 'este1', '2022-03-31 02:12:00', '2022-03-30 12:16:34', 0, '0000-00-00 00:00:00', '232489', '', 1, 0),
(7, 1, 3, '', '', 'este1', '2022-03-31 02:12:00', '2022-03-30 12:16:38', 0, '0000-00-00 00:00:00', '235648', '', 1, 0),
(8, 1, 5, '', '', 'asdasdas', '2022-03-31 06:14:00', '2022-03-30 12:16:43', 0, '0000-00-00 00:00:00', '456893', '', 1, 0),
(9, 4, 5, '', '', 'uwvakaod xwiakxnd quOjd ehausodkebaya8onevwsudofmebszuo', '2022-03-31 08:29:00', '2022-03-30 15:14:13', 1, '0000-00-00 00:00:00', '', '', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vsitor`
--

CREATE TABLE `vsitor` (
  `v_id` int(11) NOT NULL,
  `v_nm` varchar(50) NOT NULL COMMENT 'Visitor Name',
  `v_phn` varchar(50) NOT NULL COMMENT 'Visitor Phone Number',
  `v_em` varchar(50) NOT NULL COMMENT 'Visitor Email ID',
  `v_loc` int(11) NOT NULL COMMENT 'Visitor Location',
  `v_img` text NOT NULL COMMENT 'Visitor Image',
  `v_gen` varchar(10) NOT NULL COMMENT 'Visitor Gender',
  `v_typ` int(11) NOT NULL DEFAULT 0 COMMENT 'Visitor Type',
  `v_dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Visitor Current Time and Date',
  `v_pwd` varchar(100) NOT NULL COMMENT 'Visitor Password',
  `v_vre` int(11) NOT NULL DEFAULT 0 COMMENT 'Visitor Number registered/not',
  `v_rvid` int(11) NOT NULL COMMENT 'Visitor Random ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vsitor`
--

INSERT INTO `vsitor` (`v_id`, `v_nm`, `v_phn`, `v_em`, `v_loc`, `v_img`, `v_gen`, `v_typ`, `v_dt`, `v_pwd`, `v_vre`, `v_rvid`) VALUES
(1, 'Subrahmanyam Iyer', '100', 'subbu123@gmail.com', 3, '', 'Male', 0, '2022-03-30 02:44:59', '$2y$10$h6nC74TFtgS.5x.i4FogWOadNVS1cpOIeGaOOaxC.yQKHOgNTTQKu', 0, 751),
(2, 'Neelam Joy', '9496348590', 'joy354@gmail.com', 4, '', 'Female', 1, '2022-03-30 02:42:44', '$2y$10$ZrIvNxymekIiTswQnvEfg.MAR3VEkIIJJgBL84GjjpLH7DV46JHAG', 1, 4591),
(3, 'John Bose', '+919025691598', 'bose234@yahoo.com', 1, '', 'Male', 0, '2022-03-24 15:28:49', 'subbu123', 0, 256),
(4, 'Neelima Jones', '+919489618590', 'neelu298@outlook.com', 2, '', 'Female', 1, '2022-03-24 14:14:58', 'Neelima123', 1, 124),
(5, 'Prema Bidon ', '+919866102598', 'bidonp09@gmail.com', 5, '', 'Female', 1, '2022-03-24 14:15:14', 'Prema125', 0, 15506);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`a_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`d_id`);

--
-- Indexes for table `guest`
--
ALTER TABLE `guest`
  ADD PRIMARY KEY (`g_id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`l_id`);

--
-- Indexes for table `parent`
--
ALTER TABLE `parent`
  ADD PRIMARY KEY (`p_id`);

--
-- Indexes for table `relation`
--
ALTER TABLE `relation`
  ADD PRIMARY KEY (`r_id`);

--
-- Indexes for table `visitor_log`
--
ALTER TABLE `visitor_log`
  ADD PRIMARY KEY (`vl_id`);

--
-- Indexes for table `vsitor`
--
ALTER TABLE `vsitor`
  ADD PRIMARY KEY (`v_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `a_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `d_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `guest`
--
ALTER TABLE `guest`
  MODIFY `g_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `l_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `parent`
--
ALTER TABLE `parent`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `relation`
--
ALTER TABLE `relation`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `visitor_log`
--
ALTER TABLE `visitor_log`
  MODIFY `vl_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vsitor`
--
ALTER TABLE `vsitor`
  MODIFY `v_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
