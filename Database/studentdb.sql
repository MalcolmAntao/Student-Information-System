-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2024 at 04:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studentdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `Announcement_ID` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Content` varchar(500) NOT NULL,
  `Posting_Date` date NOT NULL,
  `Author_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`Announcement_ID`, `Title`, `Content`, `Posting_Date`, `Author_ID`) VALUES
(1, 'Mid-term Exam', 'Mid-term exam will be held on 25th Oct.', '2023-10-10', 1),
(2, 'Assignment Submission', 'Submit assignment 2 by 15th Nov.', '2023-11-05', 2),
(3, 'Guest Lecture', 'A guest lecture on AI will be held.', '2023-09-15', 3),
(4, 'Practical Exam', 'Practical exam starts next week.', '2023-12-01', 4),
(5, 'Holiday Notice', 'College will remain closed on 2nd Dec.', '2023-12-02', 5),
(6, 'Lab Sessions', 'Lab sessions rescheduled to Fridays.', '2023-11-10', 6),
(7, 'Project Submission', 'Submit project report by 5th Dec.', '2023-12-01', 7),
(8, 'Seminar Announcement', 'Seminar on cloud computing.', '2023-11-15', 8),
(9, 'Exam Schedule', 'Final exam schedule released.', '2023-11-20', 9),
(10, 'Internship Opportunity', 'Internship openings available.', '2023-11-25', 10),
(11, 'Workshop', 'Workshop on web development.', '2023-12-05', 11),
(12, 'Alumni Meet', 'Annual alumni meet on 15th Dec.', '2023-12-10', 12),
(13, 'Placement Drive', 'Placement drive starts in Jan.', '2023-12-20', 13),
(14, 'Research Paper Submission', 'Submit research paper by 10th Jan.', '2023-12-15', 14),
(15, 'Sports Meet', 'Annual sports meet on 22nd Dec.', '2023-12-22', 15);

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `Assessment_ID` int(11) NOT NULL,
  `IT1` decimal(5,2) NOT NULL,
  `IT2` decimal(5,2) NOT NULL,
  `IT3` decimal(5,2) NOT NULL,
  `Internal_Assessment` decimal(4,2) NOT NULL,
  `Sem` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment`
--

INSERT INTO `assessment` (`Assessment_ID`, `IT1`, `IT2`, `IT3`, `Internal_Assessment`, `Sem`) VALUES
(1, 18.50, 19.00, 20.00, 9.00, 75.50),
(2, 17.00, 18.00, 19.00, 8.00, 72.00),
(3, 20.00, 20.00, 19.00, 9.50, 78.50),
(4, 16.50, 17.50, 18.00, 7.00, 71.00),
(5, 19.00, 19.50, 20.00, 8.50, 75.00),
(6, 15.50, 16.00, 17.00, 6.50, 69.00),
(7, 18.00, 18.50, 19.50, 9.00, 74.00),
(8, 19.50, 20.00, 20.00, 9.50, 78.00),
(9, 17.50, 18.00, 18.50, 8.00, 72.50),
(10, 16.00, 17.00, 17.50, 7.50, 70.00),
(11, 18.00, 19.00, 19.50, 8.50, 73.50),
(12, 19.00, 20.00, 20.00, 9.00, 76.00),
(13, 15.50, 16.50, 17.50, 7.00, 68.50),
(14, 16.00, 17.00, 18.00, 7.50, 69.50),
(15, 19.50, 20.00, 20.00, 9.50, 78.50);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `Course_ID` int(11) NOT NULL,
  `CourseName` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Credits` int(11) NOT NULL,
  `Department_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`Course_ID`, `CourseName`, `Description`, `Credits`, `Department_ID`) VALUES
(1, 'Data Structures', 'Introduction to data structures, covering arrays, linked lists, trees, and graphs.', 4, 1),
(2, 'Operating Systems', 'Study of process management, memory management, and I/O systems.', 4, 1),
(3, 'Database Systems', 'Foundations of database management systems, SQL, and query optimization.', 3, 1),
(4, 'Computer Networks', 'Principles of networking, covering TCP/IP, routing, and security.', 4, 1),
(5, 'Web Technologies', 'Web development with HTML, CSS, JavaScript, and backend systems.', 3, 2),
(6, 'Artificial Intelligence', 'Introduction to AI concepts like search, learning, and reasoning.', 4, 2),
(7, 'Machine Learning', 'Supervised, unsupervised, and reinforcement learning methods.', 3, 2),
(8, 'Thermodynamics', 'Fundamentals of thermodynamics including laws, cycles, and applications.', 4, 3),
(9, 'Mechanics of Materials', 'Study of stress, strain, and deformation in materials.', 3, 3),
(10, 'Fluid Mechanics', 'Concepts of fluid flow, pressure, and fluid dynamics.', 4, 3),
(11, 'Structural Analysis', 'Analysis of structures and forces in beams, columns, and frames.', 4, 4),
(12, 'Construction Materials', 'Properties and uses of materials in construction.', 3, 4),
(13, 'Geotechnical Engineering', 'Soil mechanics and foundation design principles.', 3, 4),
(14, 'Embedded Systems', 'Study of embedded systems, microcontrollers, and real-time operating systems.', 4, 5),
(15, 'Digital Signal Processing', 'Introduction to signal processing and applications in communication.', 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `Department_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`Department_ID`, `Name`, `Description`) VALUES
(1, 'Computer Engineering', 'Department of Computer Engineering'),
(2, 'Information Technology', 'Department of Information Technology'),
(3, 'Mechancical Engineering', 'Department of Mechanical Engineering'),
(4, 'Civil Engineering', 'Department of Civil Engineering'),
(5, 'Electronics and Computer Science', 'Department of Electronics and Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_types`
--

CREATE TABLE `enrollment_types` (
  `Enrollment_Type_ID` int(11) NOT NULL,
  `Enrollment_Type_Name` enum('Major','Minor','Professional Elective','Open Elective') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_types`
--

INSERT INTO `enrollment_types` (`Enrollment_Type_ID`, `Enrollment_Type_Name`) VALUES
(1, 'Major'),
(2, 'Minor'),
(3, 'Professional Elective'),
(4, 'Open Elective');

-- --------------------------------------------------------

--
-- Table structure for table `enrolls_in`
--

CREATE TABLE `enrolls_in` (
  `Enrollment_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Course_ID` int(11) NOT NULL,
  `Year` int(11) NOT NULL,
  `Enrollment_Type_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrolls_in`
--

INSERT INTO `enrolls_in` (`Enrollment_ID`, `Student_ID`, `Course_ID`, `Year`, `Enrollment_Type_ID`) VALUES
(102, 1, 1, 2023, 1),
(103, 1, 2, 2023, 3),
(104, 1, 3, 2023, 1),
(105, 2, 4, 2023, 1),
(106, 2, 5, 2023, 2),
(107, 2, 6, 2023, 1),
(108, 3, 7, 2023, 1),
(109, 3, 8, 2023, 3),
(110, 3, 9, 2023, 1),
(111, 4, 10, 2023, 1),
(112, 4, 11, 2023, 4),
(113, 4, 12, 2023, 1),
(114, 5, 13, 2023, 1),
(115, 5, 14, 2023, 2),
(116, 5, 15, 2023, 3),
(117, 6, 1, 2023, 2),
(118, 6, 2, 2023, 4),
(119, 7, 3, 2023, 1),
(120, 7, 4, 2023, 3),
(121, 8, 5, 2023, 1),
(122, 8, 6, 2023, 2),
(123, 9, 7, 2023, 3),
(124, 9, 8, 2023, 4),
(125, 10, 9, 2023, 1),
(126, 10, 10, 2023, 2),
(127, 11, 11, 2023, 3),
(128, 11, 12, 2023, 1),
(129, 12, 13, 2023, 1),
(130, 12, 14, 2023, 4),
(131, 13, 15, 2023, 1);

-- --------------------------------------------------------

--
-- Table structure for table `gender_definitions`
--

CREATE TABLE `gender_definitions` (
  `Gender_Code` enum('M','F','X') NOT NULL,
  `Gender_Description` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gender_definitions`
--

INSERT INTO `gender_definitions` (`Gender_Code`, `Gender_Description`) VALUES
('M', 'Male'),
('F', 'Female'),
('X', 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `Grade_ID` int(11) NOT NULL,
  `Student_ID` int(11) NOT NULL,
  `Course_ID` int(11) NOT NULL,
  `Instructor_ID` int(11) NOT NULL,
  `Semester` varchar(20) NOT NULL,
  `Year` int(11) NOT NULL,
  `Assessment_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`Grade_ID`, `Student_ID`, `Course_ID`, `Instructor_ID`, `Semester`, `Year`, `Assessment_ID`) VALUES
(1, 1, 1, 1, 'V', 2023, 1),
(2, 2, 2, 2, 'V', 2023, 2),
(3, 3, 3, 3, 'V', 2023, 3),
(4, 4, 4, 4, 'V', 2023, 4),
(5, 5, 5, 5, 'V', 2023, 5),
(6, 6, 6, 6, 'V', 2023, 6),
(7, 7, 7, 7, 'V', 2023, 7),
(8, 8, 8, 8, 'V', 2023, 8),
(9, 9, 9, 9, 'V', 2023, 9),
(10, 10, 10, 10, 'V', 2023, 10),
(11, 11, 11, 11, 'V', 2023, 11),
(12, 12, 12, 12, 'V', 2023, 12),
(13, 13, 13, 13, 'V', 2023, 13),
(14, 14, 14, 14, 'V', 2023, 14),
(15, 15, 15, 15, 'V', 2023, 15);

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `Instructor_ID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Gender` enum('M','F','X') NOT NULL,
  `Contact_Info` varchar(150) DEFAULT NULL,
  `Profile_Picture` longblob DEFAULT NULL,
  `Department_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`Instructor_ID`, `First_Name`, `Middle_Name`, `Last_Name`, `Gender`, `Contact_Info`, `Profile_Picture`, `Department_ID`) VALUES
(1, 'Alan', 'John', 'Smith', 'M', 'alansmith@dbcegoa.ac.in', NULL, 1),
(2, 'Betty', 'Marie', 'Johnson', 'F', 'bettyjohnson@dbcegoa.ac.in', NULL, 1),
(3, 'Charles', 'Michael', 'Davis', 'M', 'charlesdavis@dbcegoa.ac.in', NULL, 1),
(4, 'Dorothy', 'Anne', 'Martinez', 'F', 'dorothymartinez@dbcegoa.ac.in', NULL, 1),
(5, 'Edward', 'Richard', 'Garcia', 'M', 'edwardgarcia@dbcegoa.ac.in', NULL, 2),
(6, 'Fiona', 'Sophia', 'Clark', 'F', 'fionaclark@dbcegoa.ac.in', NULL, 2),
(7, 'George', 'Matthew', 'Rodriguez', 'M', 'georgerodriguez@dbcegoa.ac.in', NULL, 2),
(8, 'Hannah', 'Elizabeth', 'Lewis', 'F', 'hannahlewis@dbcegoa.ac.in', NULL, 2),
(9, 'Isaac', 'Anthony', 'Lee', 'M', 'isaaclee@dbcegoa.ac.in', NULL, 1),
(10, 'Jack', 'Amelia', 'Walker', 'M', 'jackwalker@dbcegoa.ac.in', NULL, 1),
(11, 'Kathy', 'Emily', 'Robinson', 'F', 'kathyrobinson@dbcegoa.ac.in', NULL, 2),
(12, 'Louis', 'Charles', 'Young', 'M', 'louischarles@dbcegoa.ac.in', NULL, 2),
(13, 'Molly', 'Grace', 'King', 'F', 'mollyking@dbcegoa.ac.in', NULL, 2),
(14, 'Nathan', 'David', 'Scott', 'M', 'nathanscott@dbcegoa.ac.in', NULL, 1),
(15, 'Olivia', 'Helen', 'Green', 'F', 'oliviagreen@dbcegoa.ac.in', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `Department_ID` int(11) NOT NULL,
  `Course_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`Department_ID`, `Course_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 11),
(1, 13),
(1, 15),
(2, 8),
(2, 9),
(2, 10),
(2, 12),
(2, 14);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `Instructor_ID` int(11) NOT NULL,
  `Announcement_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`Instructor_ID`, `Announcement_ID`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10),
(11, 11),
(12, 12),
(13, 13),
(14, 14),
(15, 15);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `Role_ID` int(11) NOT NULL,
  `Role_Name` enum('student','teacher','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`Role_ID`, `Role_Name`) VALUES
(1, 'student'),
(2, 'teacher'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `role_associations`
--

CREATE TABLE `role_associations` (
  `User_ID` int(11) NOT NULL,
  `Student_ID` int(11) DEFAULT NULL,
  `Instructor_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_associations`
--

INSERT INTO `role_associations` (`User_ID`, `Student_ID`, `Instructor_ID`) VALUES
(1, 1, NULL),
(2, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `Student_ID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Gender` enum('M','F','X') NOT NULL,
  `Roll_No` varchar(50) NOT NULL,
  `University_No` varchar(50) NOT NULL,
  `Date_Of_Birth` date DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `PhoneNo` varchar(15) DEFAULT NULL,
  `Current_Semester` varchar(20) NOT NULL,
  `Profile_Picture` longblob DEFAULT NULL,
  `Bio` text DEFAULT NULL,
  `Enrollment_Status` varchar(50) DEFAULT NULL,
  `Major` varchar(50) DEFAULT NULL,
  `Department_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`Student_ID`, `First_Name`, `Middle_Name`, `Last_Name`, `Gender`, `Roll_No`, `University_No`, `Date_Of_Birth`, `Email`, `PhoneNo`, `Current_Semester`, `Profile_Picture`, `Bio`, `Enrollment_Status`, `Major`, `Department_ID`) VALUES
(1, 'John', 'Michael', 'Doe', 'M', 'CSE1001', 'U12345601', '2000-01-15', '1@dbcegoa.ac.in', '9876543210', 'V', NULL, 'Computer Engineering student.', 'Enrolled', NULL, 1),
(2, 'Alice', 'Marie', 'Smith', 'F', 'CSE1002', 'U12345602', '1999-05-23', '2@dbcegoa.ac.in', '9876543211', 'V', NULL, 'Loves AI research.', 'Enrolled', NULL, 1),
(3, 'Bob', 'Robert', 'Johnson', 'M', 'CSE2001', 'U22345601', '1998-11-30', '3@dbcegoa.ac.in', '9876543212', 'V', NULL, 'Expert in web development.', 'Enrolled', NULL, 2),
(4, 'Carol', 'Anne', 'Williams', 'F', 'CSE2002', 'U22345602', '2000-03-12', '4@dbcegoa.ac.in', '9876543213', 'V', NULL, 'Focuses on networking.', 'Enrolled', NULL, 2),
(5, 'Dave', 'Matthew', 'Brown', 'M', 'ME1001', 'U32345601', '2001-07-18', '5@dbcegoa.ac.in', '9876543214', 'V', NULL, 'Mechanical Engineering student.', 'Enrolled', NULL, 3),
(6, 'Eve', 'Sophia', 'Davis', 'F', 'ME1002', 'U32345602', '1999-12-22', '6@dbcegoa.ac.in', '9876543215', 'V', NULL, 'Interested in thermodynamics.', 'Enrolled', NULL, 3),
(7, 'Frank', 'Richard', 'Miller', 'M', 'CE1001', 'U42345601', '2001-02-05', '7@dbcegoa.ac.in', '9876543216', 'V', NULL, 'Specializes in structural analysis.', 'Enrolled', NULL, 4),
(8, 'Grace', 'Elizabeth', 'Wilson', 'F', 'CE1002', 'U42345602', '2000-09-17', '8@dbcegoa.ac.in', '9876543217', 'V', NULL, 'Focuses on geotechnical engineering.', 'Enrolled', NULL, 4),
(9, 'Hank', 'Anthony', 'Moore', 'M', 'ECS1001', 'U52345601', '1999-04-25', '9@dbcegoa.ac.in', '9876543218', 'V', NULL, 'Embedded systems enthusiast.', 'Enrolled', NULL, 5),
(10, 'Ivy', 'Amelia', 'Taylor', 'F', 'ECS1002', 'U52345602', '2001-06-10', '10@dbcegoa.ac.in', '9876543219', 'V', NULL, 'Loves digital signal processing.', 'Enrolled', NULL, 5),
(11, 'Jack', 'George', 'Anderson', 'M', 'CSE3001', 'U12345611', '1999-11-14', '11@dbcegoa.ac.in', '9876543220', 'V', NULL, 'Focuses on software engineering.', 'Enrolled', NULL, 1),
(12, 'Kim', 'Sarah', 'Thomas', 'F', 'CSE3002', 'U12345612', '2000-08-19', '12@dbcegoa.ac.in', '9876543221', 'V', NULL, 'Blockchain technology expert.', 'Enrolled', NULL, 1),
(13, 'Leo', 'Charles', 'Jackson', 'M', 'ME2001', 'U32345613', '1998-12-03', '13@dbcegoa.ac.in', '9876543222', 'V', NULL, 'Mechanical design and fluid mechanics.', 'Enrolled', NULL, 3),
(14, 'Mia', 'Emily', 'White', 'F', 'IT1003', 'U22345614', '2001-09-28', '14@dbcegoa.ac.in', '9876543223', 'V', NULL, 'Focuses on cloud computing.', 'Enrolled', NULL, 2),
(15, 'Nate', 'David', 'Harris', 'M', 'CE2001', 'U42345615', '1999-07-04', '15@dbcegoa.ac.in', '9876543224', 'V', NULL, 'Civil Engineering, construction materials.', 'Enrolled', NULL, 4);

-- --------------------------------------------------------

--
-- Table structure for table `teaches`
--

CREATE TABLE `teaches` (
  `Instructor_ID` int(11) NOT NULL,
  `Course_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teaches`
--

INSERT INTO `teaches` (`Instructor_ID`, `Course_ID`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10),
(11, 11),
(12, 12),
(13, 13),
(14, 14),
(15, 15);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_ID`, `Email`, `Password`, `Role_ID`) VALUES
(1, '1@dbcegoa.ac.in', '$2a$12$iLX8IrL0gSt0i15/UevSr.Y84diJJw31zMsKgXKt84d/SIY6Ph0Fu', 1),
(2, 'john.smith@dbcegoa.ac.in', '$2a$12$O7/iOmAO0MAKAK/6adBLXO/KQW43sLTFPlb4aVRAJIZh2LEeopZG.', 2),
(3, 'admin@dbcegoa.ac.in', '$2a$12$6HWNitANOhSwGc45g8koOuT/kMfG68dN.O/U/Vf5wEhg/yhpnn.u.', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`Announcement_ID`),
  ADD KEY `Author_ID` (`Author_ID`);

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`Assessment_ID`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`Course_ID`),
  ADD KEY `Department_ID` (`Department_ID`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`Department_ID`);

--
-- Indexes for table `enrollment_types`
--
ALTER TABLE `enrollment_types`
  ADD PRIMARY KEY (`Enrollment_Type_ID`);

--
-- Indexes for table `enrolls_in`
--
ALTER TABLE `enrolls_in`
  ADD PRIMARY KEY (`Enrollment_ID`),
  ADD KEY `Student_ID` (`Student_ID`),
  ADD KEY `Course_ID` (`Course_ID`),
  ADD KEY `FK_Enrollment_Type` (`Enrollment_Type_ID`);

--
-- Indexes for table `gender_definitions`
--
ALTER TABLE `gender_definitions`
  ADD PRIMARY KEY (`Gender_Code`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`Grade_ID`),
  ADD KEY `Student_ID` (`Student_ID`),
  ADD KEY `Course_ID` (`Course_ID`),
  ADD KEY `Instructor_ID` (`Instructor_ID`),
  ADD KEY `Assessment_ID` (`Assessment_ID`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`Instructor_ID`),
  ADD KEY `Department_ID` (`Department_ID`),
  ADD KEY `FK_Instructor_Gender` (`Gender`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`Department_ID`,`Course_ID`),
  ADD KEY `Course_ID` (`Course_ID`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`Instructor_ID`,`Announcement_ID`),
  ADD KEY `Announcement_ID` (`Announcement_ID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`Role_ID`);

--
-- Indexes for table `role_associations`
--
ALTER TABLE `role_associations`
  ADD PRIMARY KEY (`User_ID`),
  ADD KEY `Student_ID` (`Student_ID`),
  ADD KEY `Instructor_ID` (`Instructor_ID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Student_ID`),
  ADD KEY `FK_Student_Department` (`Department_ID`),
  ADD KEY `FK_Student_Gender` (`Gender`);

--
-- Indexes for table `teaches`
--
ALTER TABLE `teaches`
  ADD PRIMARY KEY (`Instructor_ID`,`Course_ID`),
  ADD KEY `Course_ID` (`Course_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `Announcement_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `Assessment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `Course_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `Department_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollment_types`
--
ALTER TABLE `enrollment_types`
  MODIFY `Enrollment_Type_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrolls_in`
--
ALTER TABLE `enrolls_in`
  MODIFY `Enrollment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `Grade_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `Instructor_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `Role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `Student_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`Author_ID`) REFERENCES `instructors` (`Instructor_ID`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`Department_ID`) REFERENCES `departments` (`Department_ID`);

--
-- Constraints for table `enrolls_in`
--
ALTER TABLE `enrolls_in`
  ADD CONSTRAINT `FK_Enrollment_Type` FOREIGN KEY (`Enrollment_Type_ID`) REFERENCES `enrollment_types` (`Enrollment_Type_ID`),
  ADD CONSTRAINT `enrolls_in_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `students` (`Student_ID`),
  ADD CONSTRAINT `enrolls_in_ibfk_2` FOREIGN KEY (`Course_ID`) REFERENCES `courses` (`Course_ID`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`Student_ID`) REFERENCES `students` (`Student_ID`),
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`Course_ID`) REFERENCES `courses` (`Course_ID`),
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructors` (`Instructor_ID`),
  ADD CONSTRAINT `grades_ibfk_4` FOREIGN KEY (`Assessment_ID`) REFERENCES `assessment` (`Assessment_ID`);

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `FK_Instructor_Gender` FOREIGN KEY (`Gender`) REFERENCES `gender_definitions` (`Gender_Code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `instructors_ibfk_1` FOREIGN KEY (`Department_ID`) REFERENCES `departments` (`Department_ID`);

--
-- Constraints for table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`Department_ID`) REFERENCES `departments` (`Department_ID`),
  ADD CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`Course_ID`) REFERENCES `courses` (`Course_ID`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructors` (`Instructor_ID`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`Announcement_ID`) REFERENCES `announcements` (`Announcement_ID`);

--
-- Constraints for table `role_associations`
--
ALTER TABLE `role_associations`
  ADD CONSTRAINT `role_associations_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_associations_ibfk_2` FOREIGN KEY (`Student_ID`) REFERENCES `students` (`Student_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_associations_ibfk_3` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructors` (`Instructor_ID`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `FK_Student_Department` FOREIGN KEY (`Department_ID`) REFERENCES `departments` (`Department_ID`),
  ADD CONSTRAINT `FK_Student_Gender` FOREIGN KEY (`Gender`) REFERENCES `gender_definitions` (`Gender_Code`) ON UPDATE CASCADE;

--
-- Constraints for table `teaches`
--
ALTER TABLE `teaches`
  ADD CONSTRAINT `teaches_ibfk_1` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructors` (`Instructor_ID`),
  ADD CONSTRAINT `teaches_ibfk_2` FOREIGN KEY (`Course_ID`) REFERENCES `courses` (`Course_ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `roles` (`Role_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;