SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `studenthub`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `studenthub`;

DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(10) NOT NULL,
  `course` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `last_login` datetime DEFAULT NULL,
  `remember_token_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Open',
  `poster_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`registration_id`),
  KEY `student_id` (`student_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`)
    REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`)
    REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(50) DEFAULT NULL,
  `entity_id` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `student_id` (`student_id`),
  KEY `action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `students`
  (`student_id`, `full_name`, `email`, `mobile`, `course`, `password`, `role`) VALUES
('ADMIN001', 'System Administrator', 'admin@studenthub.test', '9000000000',
 'Administration',
 '$2y$12$g8enUdLnGGB4ohD0L6dus.MimRSb.sXMzxr.vB21kzbZIsWrhRnUq', 'admin'),
('25CE015', 'Dhairy Chauhan', 'dhairy@studenthub.test', '9428280245',
 'B.Tech CSE',
 '$2y$12$./Uh9k4v4Vz87lTrI3kdJeMDILcfMt.tG.3Ct7P/2NEZnCmgmq6S2', 'student');

INSERT INTO `events`
  (`event_name`, `event_date`, `description`, `category`, `status`) VALUES
('Tech Fest 2026', '2026-11-14', 'Annual technology festival with workshops and competitions.', 'Technical', 'Open'),
('Cultural Night', '2026-12-20', 'Music, dance and drama performances by students.', 'Cultural', 'Open');

COMMIT;
