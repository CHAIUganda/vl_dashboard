-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 06, 2016 at 08:15 PM
-- Server version: 5.5.53-0ubuntu0.14.04.1
-- PHP Version: 5.6.23-1+deprecated+dontuse+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `vl20160606`
--

-- --------------------------------------------------------

--
-- Table structure for table `vl_facility_printing`
--

CREATE TABLE IF NOT EXISTS `vl_facility_printing` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sample_id` bigint(20) unsigned NOT NULL,
  `printed` enum('YES','NO') NOT NULL DEFAULT 'NO',
  `qc_at` datetime NOT NULL,
  `qc_by` varchar(250) NOT NULL,
  `printed_at` datetime DEFAULT NULL,
  `printed_by` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;