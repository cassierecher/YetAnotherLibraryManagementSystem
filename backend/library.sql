-- phpMyAdmin SQL Dump
-- version 4.0.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 30, 2013 at 07:52 AM
-- Server version: 5.5.32-0ubuntu7
-- PHP Version: 5.5.3-1ubuntu2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `libraryadmin`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE IF NOT EXISTS `books` (
  `UID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` tinytext NOT NULL,
  `BookCover` text NOT NULL,
  `BookPDF` text NOT NULL,
  `Publisher` tinytext NOT NULL,
  `Issue_Date` date NOT NULL,
  `Return_Date` date NOT NULL,
  `Author` tinytext NOT NULL,
  `CID` int(11) DEFAULT NULL,
  PRIMARY KEY (`UID`),
  KEY `UID` (`UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE IF NOT EXISTS `customers` (
  `UID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `First_Name` tinytext NOT NULL,
  `Last_Name` tinytext NOT NULL,
  `Creation_Date` date NOT NULL,
  `Book_List` text NOT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `UID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `Username` tinytext CHARACTER SET utf16 COLLATE utf16_bin NOT NULL,
  `CID` int(11) DEFAULT NULL,
  `Password` text CHARACTER SET utf16 COLLATE utf16_bin NOT NULL,
  `Salt` int(11) NOT NULL,
  PRIMARY KEY (`UID`),
  KEY `UID` (`UID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UID`, `admin`, `Username`, `CID`, `Password`, `Salt`) VALUES
(1, 1, 'admin', NULL, 'c34bf18349b9e8dac4a73f7ba5d904ab7fdd13d63b3c07ab786ad58515a2f7f4c745e585b73f9b17b1da4d973a0f0d79a029520ee7a15a30bcde3f65de1fd8eb', 1385154326);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
