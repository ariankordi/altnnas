-- phpMyAdmin SQL Dump
-- version 4.8.0-dev
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 09, 2017 at 12:17 PM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 7.0.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `nnas`
--
CREATE DATABASE IF NOT EXISTS `nnas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `nnas`;

-- --------------------------------------------------------

--
-- Table structure for table `agreements`
--

CREATE TABLE `agreements` (
  `id` bigint(20) NOT NULL,
  `country` varchar(2) NOT NULL DEFAULT 'JP',
  `language` varchar(2) NOT NULL DEFAULT 'ja',
  `languageName` text NOT NULL,
  `publishDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `agreeText` text NOT NULL,
  `mainTitle` text NOT NULL,
  `nonAgreeText` text NOT NULL,
  `mainText` longtext NOT NULL,
  `type` varchar(255) NOT NULL,
  `version` int(11) NOT NULL DEFAULT '0',
  `updatedBy` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` bigint(20) NOT NULL,
  `pid` int(10) NOT NULL,
  `deviceId` text NOT NULL,
  `platformId` int(1) NOT NULL,
  `language` varchar(2) NOT NULL DEFAULT 'ja',
  `region` int(9) NOT NULL,
  `systemVersion` int(4) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `serialNumber` varchar(12) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `emails`
--

CREATE TABLE `emails` (
  `pid` int(10) NOT NULL,
  `address` text NOT NULL,
  `id` bigint(20) NOT NULL,
  `isPrimary` tinyint(1) NOT NULL,
  `reachable` tinyint(1) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `updatedBy` text NOT NULL,
  `validated` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `miis`
--

CREATE TABLE `miis` (
  `id` bigint(20) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `data` text NOT NULL,
  `miiHash` text NOT NULL,
  `name` text NOT NULL,
  `pid` int(10) NOT NULL,
  `isPrimary` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mii_images`
--

CREATE TABLE `mii_images` (
  `master` bigint(20) NOT NULL,
  `id` bigint(20) NOT NULL,
  `url` text NOT NULL,
  `cachedUrl` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

CREATE TABLE `people` (
  `activeFlag` tinyint(1) NOT NULL DEFAULT '0',
  `birthDate` date NOT NULL,
  `country` varchar(2) NOT NULL,
  `createDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `isBirthdateFinal` tinyint(1) NOT NULL DEFAULT '0',
  `language` varchar(2) NOT NULL DEFAULT 'ja',
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `offDeviceFlag` tinyint(1) NOT NULL DEFAULT '0',
  `pid` int(10) NOT NULL,
  `region` int(9) NOT NULL DEFAULT '0',
  `timezone` text NOT NULL,
  `userId` varchar(16) NOT NULL,
  `utcOffset` int(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `person_agreements`
--

CREATE TABLE `person_agreements` (
  `id` bigint(20) NOT NULL,
  `pid` int(10) NOT NULL,
  `agreementDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `location` text,
  `agreement` text NOT NULL,
  `version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreements`
--
ALTER TABLE `agreements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `b` (`pid`);

--
-- Indexes for table `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `a` (`pid`);

--
-- Indexes for table `miis`
--
ALTER TABLE `miis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `c` (`pid`);

--
-- Indexes for table `mii_images`
--
ALTER TABLE `mii_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `d` (`master`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `person_agreements`
--
ALTER TABLE `person_agreements`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreements`
--
ALTER TABLE `agreements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `emails`
--
ALTER TABLE `emails`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `miis`
--
ALTER TABLE `miis`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mii_images`
--
ALTER TABLE `mii_images`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `person_agreements`
--
ALTER TABLE `person_agreements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `b` FOREIGN KEY (`pid`) REFERENCES `people` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `a` FOREIGN KEY (`pid`) REFERENCES `people` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `miis`
--
ALTER TABLE `miis`
  ADD CONSTRAINT `c` FOREIGN KEY (`pid`) REFERENCES `people` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mii_images`
--
ALTER TABLE `mii_images`
  ADD CONSTRAINT `d` FOREIGN KEY (`master`) REFERENCES `miis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

