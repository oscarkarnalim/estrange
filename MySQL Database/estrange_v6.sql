-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 27, 2025 at 07:47 PM
-- Server version: 10.2.44-MariaDB
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `estrange_v6`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `assessment_id` int(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `submission_open_time` datetime NOT NULL,
  `submission_close_time` datetime NOT NULL,
  `submission_file_extension` varchar(30) NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `course_id` int(30) NOT NULL,
  `allow_late_submission` tinyint(1) NOT NULL DEFAULT 0,
  `public_assessment_id` varchar(30) NOT NULL,
  `similarity_report_path` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `code_clarity_suggestion`
--

CREATE TABLE `code_clarity_suggestion` (
  `suggestion_id` int(30) NOT NULL,
  `marked_code` longtext NOT NULL,
  `table_info` text NOT NULL,
  `explanation_info` text NOT NULL,
  `submission_id` int(30) NOT NULL,
  `public_suggestion_id` varchar(30) NOT NULL,
  `quality_point` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colecturer`
--

CREATE TABLE `colecturer` (
  `colecturer_id` int(30) NOT NULL,
  `user_id` int(30) NOT NULL,
  `course_id` int(30) NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` int(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `creator_id` int(30) NOT NULL,
  `enrollment_mode` int(1) NOT NULL DEFAULT 0,
  `course_password` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `enrollment_id` int(30) NOT NULL,
  `student_id` int(30) NOT NULL,
  `enrollment_time` datetime NOT NULL DEFAULT current_timestamp(),
  `course_id` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `game_access`
--

CREATE TABLE `game_access` (
  `game_access_id` int(50) NOT NULL,
  `student_id` int(30) NOT NULL,
  `access_time` datetime NOT NULL DEFAULT current_timestamp(),
  `type` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `game_assessment`
--

CREATE TABLE `game_assessment` (
  `ga_id` int(30) NOT NULL,
  `assessment_id` int(30) NOT NULL,
  `expected_collaboration_score` int(10) NOT NULL,
  `is_expectation_passed` tinyint(1) NOT NULL DEFAULT 0,
  `is_competitive_badges_distributed` tinyint(1) NOT NULL DEFAULT 0,
  `is_code_quality_competitive_badges_distributed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `game_course`
--

CREATE TABLE `game_course` (
  `gc_id` int(30) NOT NULL,
  `course_id` int(30) NOT NULL,
  `prize_text` text NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `game_student_course`
--

CREATE TABLE `game_student_course` (
  `gs_id` int(30) NOT NULL,
  `student_id` int(30) NOT NULL,
  `course_id` int(11) NOT NULL,
  `is_participating` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `game_unobserved_notif`
--

CREATE TABLE `game_unobserved_notif` (
  `notification_id` int(50) NOT NULL,
  `gs_id` int(30) NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `instant_quiz_bank`
--

CREATE TABLE `instant_quiz_bank` (
  `question_id` int(30) NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer_options` varchar(300) NOT NULL DEFAULT 'Ya,Tidak',
  `answers` varchar(300) NOT NULL DEFAULT 'Ya',
  `type` varchar(50) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `instant_quiz_response_history`
--

CREATE TABLE `instant_quiz_response_history` (
  `response_id` int(30) NOT NULL,
  `student_id` int(30) NOT NULL,
  `question_id` int(30) NOT NULL,
  `response_time` datetime NOT NULL DEFAULT current_timestamp(),
  `is_correct` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `password_request`
--

CREATE TABLE `password_request` (
  `request_id` int(30) NOT NULL,
  `user_id` int(30) NOT NULL,
  `access_key` varchar(30) NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `similarity_report_generation_queue`
--

CREATE TABLE `similarity_report_generation_queue` (
  `queue_id` int(30) NOT NULL,
  `assessment_id` int(30) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `student_registration`
--

CREATE TABLE `student_registration` (
  `registration_id` int(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `access_key` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `submission_id` int(30) NOT NULL,
  `description` text NOT NULL,
  `filename` varchar(100) NOT NULL,
  `file_path` varchar(150) NOT NULL,
  `attempt` int(11) NOT NULL,
  `submission_time` datetime NOT NULL DEFAULT current_timestamp(),
  `has_suspicion_report_created` tinyint(1) NOT NULL DEFAULT 0,
  `submitter_id` int(30) NOT NULL,
  `assessment_id` int(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `suggestion_access`
--

CREATE TABLE `suggestion_access` (
  `access_id` int(50) NOT NULL,
  `suggestion_id` int(30) NOT NULL,
  `access_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `suspicion`
--

CREATE TABLE `suspicion` (
  `suspicion_id` int(30) NOT NULL,
  `suspicion_type` varchar(10) NOT NULL,
  `marked_code` longtext DEFAULT NULL,
  `artificial_code` longtext DEFAULT NULL,
  `table_info` text DEFAULT NULL,
  `explanation_info` text DEFAULT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `submission_id` int(30) NOT NULL,
  `public_suspicion_id` varchar(30) NOT NULL,
  `originality_point` int(3) NOT NULL DEFAULT 0,
  `is_overly_unique` tinyint(1) NOT NULL DEFAULT 0,
  `efficiency_point` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `suspicion_access`
--

CREATE TABLE `suspicion_access` (
  `access_id` int(50) NOT NULL,
  `suspicion_id` int(30) NOT NULL,
  `access_time` datetime NOT NULL DEFAULT current_timestamp(),
  `accessor_id` int(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `suspicion_email_request`
--

CREATE TABLE `suspicion_email_request` (
  `request_id` int(30) NOT NULL,
  `suspicion_id` int(30) NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `name`, `password`, `email`, `role`) VALUES
(123759, 'adminacc', 'adminacc', '$2y$10$cTe/KXQoU1A0VLXvHEBn6OOOpTAvCXzQCv/SmxUjPGW6z0jKJSihS', 'admin@gmail.com', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `code_clarity_suggestion`
--
ALTER TABLE `code_clarity_suggestion`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `colecturer`
--
ALTER TABLE `colecturer`
  ADD PRIMARY KEY (`colecturer_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `game_access`
--
ALTER TABLE `game_access`
  ADD PRIMARY KEY (`game_access_id`),
  ADD KEY `game_access_ibfk_1` (`student_id`);

--
-- Indexes for table `game_assessment`
--
ALTER TABLE `game_assessment`
  ADD PRIMARY KEY (`ga_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `game_course`
--
ALTER TABLE `game_course`
  ADD PRIMARY KEY (`gc_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `game_student_course`
--
ALTER TABLE `game_student_course`
  ADD PRIMARY KEY (`gs_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `game_unobserved_notif`
--
ALTER TABLE `game_unobserved_notif`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `gs_id` (`gs_id`);

--
-- Indexes for table `instant_quiz_bank`
--
ALTER TABLE `instant_quiz_bank`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `instant_quiz_response_history`
--
ALTER TABLE `instant_quiz_response_history`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `password_request`
--
ALTER TABLE `password_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `similarity_report_generation_queue`
--
ALTER TABLE `similarity_report_generation_queue`
  ADD PRIMARY KEY (`queue_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `submitter_id` (`submitter_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `suggestion_access`
--
ALTER TABLE `suggestion_access`
  ADD PRIMARY KEY (`access_id`),
  ADD KEY `suggestion_id` (`suggestion_id`);

--
-- Indexes for table `suspicion`
--
ALTER TABLE `suspicion`
  ADD PRIMARY KEY (`suspicion_id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `suspicion_access`
--
ALTER TABLE `suspicion_access`
  ADD PRIMARY KEY (`access_id`),
  ADD KEY `suspicion_id` (`suspicion_id`),
  ADD KEY `accessor_id` (`accessor_id`);

--
-- Indexes for table `suspicion_email_request`
--
ALTER TABLE `suspicion_email_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `suspicion_id` (`suspicion_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `assessment_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `code_clarity_suggestion`
--
ALTER TABLE `code_clarity_suggestion`
  MODIFY `suggestion_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colecturer`
--
ALTER TABLE `colecturer`
  MODIFY `colecturer_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `course_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `enrollment_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_access`
--
ALTER TABLE `game_access`
  MODIFY `game_access_id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_assessment`
--
ALTER TABLE `game_assessment`
  MODIFY `ga_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_course`
--
ALTER TABLE `game_course`
  MODIFY `gc_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_student_course`
--
ALTER TABLE `game_student_course`
  MODIFY `gs_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_unobserved_notif`
--
ALTER TABLE `game_unobserved_notif`
  MODIFY `notification_id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instant_quiz_bank`
--
ALTER TABLE `instant_quiz_bank`
  MODIFY `question_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instant_quiz_response_history`
--
ALTER TABLE `instant_quiz_response_history`
  MODIFY `response_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_request`
--
ALTER TABLE `password_request`
  MODIFY `request_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `similarity_report_generation_queue`
--
ALTER TABLE `similarity_report_generation_queue`
  MODIFY `queue_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_registration`
--
ALTER TABLE `student_registration`
  MODIFY `registration_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `submission_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suggestion_access`
--
ALTER TABLE `suggestion_access`
  MODIFY `access_id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suspicion`
--
ALTER TABLE `suspicion`
  MODIFY `suspicion_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suspicion_access`
--
ALTER TABLE `suspicion_access`
  MODIFY `access_id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suspicion_email_request`
--
ALTER TABLE `suspicion_email_request`
  MODIFY `request_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(30) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessment`
--
ALTER TABLE `assessment`
  ADD CONSTRAINT `assessment_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`);

--
-- Constraints for table `code_clarity_suggestion`
--
ALTER TABLE `code_clarity_suggestion`
  ADD CONSTRAINT `code_clarity_suggestion_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`);

--
-- Constraints for table `colecturer`
--
ALTER TABLE `colecturer`
  ADD CONSTRAINT `colecturer_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `colecturer_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `enrollment_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `enrollment_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `game_unobserved_notif`
--
ALTER TABLE `game_unobserved_notif`
  ADD CONSTRAINT `game_unobserved_notif_ibfk_1` FOREIGN KEY (`gs_id`) REFERENCES `game_student_course` (`gs_id`);

--
-- Constraints for table `instant_quiz_response_history`
--
ALTER TABLE `instant_quiz_response_history`
  ADD CONSTRAINT `instant_quiz_response_history_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `instant_quiz_bank` (`question_id`),
  ADD CONSTRAINT `instant_quiz_response_history_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `password_request`
--
ALTER TABLE `password_request`
  ADD CONSTRAINT `password_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `similarity_report_generation_queue`
--
ALTER TABLE `similarity_report_generation_queue`
  ADD CONSTRAINT `similarity_report_generation_queue_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`);

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `submission_ibfk_1` FOREIGN KEY (`submitter_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `submission_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`);

--
-- Constraints for table `suggestion_access`
--
ALTER TABLE `suggestion_access`
  ADD CONSTRAINT `suggestion_access_ibfk_1` FOREIGN KEY (`suggestion_id`) REFERENCES `code_clarity_suggestion` (`suggestion_id`);

--
-- Constraints for table `suspicion`
--
ALTER TABLE `suspicion`
  ADD CONSTRAINT `suspicion_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`);

--
-- Constraints for table `suspicion_access`
--
ALTER TABLE `suspicion_access`
  ADD CONSTRAINT `suspicion_access_ibfk_1` FOREIGN KEY (`suspicion_id`) REFERENCES `suspicion` (`suspicion_id`),
  ADD CONSTRAINT `suspicion_access_ibfk_2` FOREIGN KEY (`accessor_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `suspicion_email_request`
--
ALTER TABLE `suspicion_email_request`
  ADD CONSTRAINT `suspicion_email_request_ibfk_1` FOREIGN KEY (`suspicion_id`) REFERENCES `suspicion` (`suspicion_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
