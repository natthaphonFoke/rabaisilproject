-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 07:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rabaisrin_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendar`
--

CREATE TABLE `calendar` (
  `id` int(11) NOT NULL,
  `app_id` int(11) DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar`
--

INSERT INTO `calendar` (`id`, `app_id`, `event_date`, `start_time`, `end_time`) VALUES
(345, 382, '2025-03-10', '17:00:00', '17:00:00'),
(346, 382, '2025-03-10', '17:00:00', '17:00:00'),
(349, NULL, '2025-03-12', '10:00:00', '13:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `consultation`
--

CREATE TABLE `consultation` (
  `hn_id` int(10) DEFAULT NULL,
  `app_id` int(10) UNSIGNED NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `channel_id` int(1) NOT NULL,
  `origin_id` int(1) DEFAULT NULL,
  `forward_from` varchar(50) DEFAULT NULL,
  `consult_case` text DEFAULT NULL,
  `consult_des` varchar(50) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `advice` text DEFAULT NULL,
  `test_results` text DEFAULT NULL,
  `follow_id` int(1) DEFAULT NULL,
  `follow_des` text DEFAULT NULL,
  `forward_id` int(1) DEFAULT NULL,
  `forward_des` varchar(100) DEFAULT NULL,
  `std_id` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `admin` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `consultation`
--

INSERT INTO `consultation` (`hn_id`, `app_id`, `event_date`, `event_time`, `channel_id`, `origin_id`, `forward_from`, `consult_case`, `consult_des`, `symptoms`, `advice`, `test_results`, `follow_id`, `follow_des`, `forward_id`, `forward_des`, `std_id`, `status`, `admin`) VALUES
(52, 382, '2025-03-10', '17:00:00', 3, 2, NULL, 'การเรียน', 'ไม่มี', 'sss', 'sss', 'sss', 4, 'ssss', 2, 'ไม่มี', 640710711, 4, 'พี่บอม บอม');

-- --------------------------------------------------------

--
-- Table structure for table `consultation_recipients`
--

CREATE TABLE `consultation_recipients` (
  `hn_id` int(11) NOT NULL,
  `std_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultation_recipients`
--

INSERT INTO `consultation_recipients` (`hn_id`, `std_id`) VALUES
(52, 640710711),
(55, 640710741),
(56, 640710095);

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `faculty_id` char(2) NOT NULL,
  `faculty_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`faculty_id`, `faculty_name`) VALUES
('01', 'จิตรกรรม ประติมากรรมและภาพพิมพ์'),
('02', 'สถาปัตยกรรมศาสตร์'),
('03', 'โบราณคดี'),
('04', 'มัณฑนศิลป์'),
('05', 'อักษรศาสตร์'),
('06', 'ศึกษาศาสตร์'),
('07', 'วิทยาศาสตร์'),
('08', 'เภสัชศาสตร์'),
('09', 'วิศวกรรมศาสตร์และเทคโนโลยีอุตสาหกรรม'),
('10', 'ดุริยางคศาสตร์'),
('11', 'สัตวศาสตร์'),
('12', 'วิทยาการจัดการ'),
('13', 'เทคโนโลยีสารสนเทศและการสื่อสาร');

-- --------------------------------------------------------

--
-- Table structure for table `form`
--

CREATE TABLE `form` (
  `ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form`
--

INSERT INTO `form` (`ID`, `name`, `img`) VALUES
(1, 'แบบทดสอบซึมเศร้า', 'https://www.nsm.or.th/nsm/sites/default/files/2023-10/Exdep.jpg'),
(2, 'แบบทดสอบวิตกกังวล', 'https://image.posttoday.com/uploads/images/contents/w1024/2024/01/E2horsFLGf0MOByHmlMf.webp?x-image-process=style/lg-webp'),
(3, 'แบบทดสอบความเครียด', 'https://storage.googleapis.com/techsauce-prod/ugc/uploads/2022/10/1666934812_12_ways_to_cope_with_stress_at_work.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `major`
--

CREATE TABLE `major` (
  `major_id` int(11) NOT NULL,
  `major_name` varchar(100) NOT NULL,
  `faculty_id` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `major`
--

INSERT INTO `major` (`major_id`, `major_name`, `faculty_id`) VALUES
(0, 'สาขาวิชาสถาปัตยกรรมไทย', '02'),
(1, 'สาขาวิชาทัศนศิลป์', '01'),
(2, 'สาขาวิชาทฤษฎีศิลป์', '01'),
(3, 'สาขาวิชาทัศนศิลปศึกษา', '01'),
(4, 'สาขาวิชาสถาปัตยกรรม', '02'),
(6, 'สาขาวิชาการออกแบบชุมชนเมือง', '02'),
(7, 'สาขาวิชาสถาปัตยกรรมพื้นถิ่น', '02'),
(8, 'สาขาวิชาประวัติศาสตร์สถาปัตยกรรม', '02'),
(9, 'สาขาวิชาการจัดการมรดกทางสถาปัตยกรรมและการท่องเที่ยว', '02'),
(10, 'สาขาวิชาการจัดการโครงการก่อสร้าง', '02'),
(11, 'สาขาวิชาคอมพิวเตอร์เพื่อการออกแบบทางสถาปัตยกรรม', '02'),
(12, 'สาขาวิชาโบราณคดี', '03'),
(13, 'สาขาวิชาประวัติศาสตร์ศิลปะ', '03'),
(14, 'สาขาวิชามานุษยวิทยา', '03'),
(15, 'สาขาวิชาภาษาไทย', '03'),
(16, 'สาขาวิชาภาษาอังกฤษ', '03'),
(17, 'สาขาวิชาภาษาฝรั่งเศส', '03'),
(18, 'สาขาประวัติศาสตร์ท้องถิ่น', '03'),
(19, 'สาขาวิชาจารึกศึกษา', '03'),
(20, 'สาขาวิชาภาษาสันสกฤต', '03'),
(21, 'สาขาวิชาการจัดการทรัพยากรวัฒนธรรม', '03'),
(22, 'สาขาวิชาการออกแบบภายใน', '04'),
(23, 'สาขาวิชาการออกแบบนิเทศศิลป์', '04'),
(24, 'สาขาวิชาการออกแบบผลิตภัณฑ์', '04'),
(25, 'สาขาวิชาประยุกตศิลปศึกษา', '04'),
(26, 'สาขาวิชาเครื่องเคลือบดินเผา', '04'),
(27, 'สาขาวิชาการออกแบบเครื่องประดับ', '04'),
(28, 'สาขาวิชาการออกแบบเครื่องแต่งกาย', '04'),
(29, 'สาขาวิชาศิลปะการออกแบบ', '04'),
(38, 'สาขาวิชาศิลปะการออกแบบ (หลักสูตรนานาชาติ)', '04'),
(39, 'สาขาวิชาศิลปะการออกแบบเชิงวัฒนธรรม', '04'),
(40, 'สาขาวิชาการออกแบบ', '04'),
(41, 'สาขาวิชาการแสดงศึกษา', '05'),
(42, 'สาขาวิชาประวัติศาสตร์', '05'),
(43, 'สาขาวิชาปรัชญา', '05'),
(44, 'สาขาวิชาภาษาไทย', '05'),
(45, 'สาขาวิชาภาษาเอเชียตะวันออก', '05'),
(46, 'สาขาวิชาภาษาฝรั่งเศส', '05'),
(47, 'สาขาวิชาภาษาเยอรมัน', '05'),
(48, 'สาขาวิชาภาษาอังกฤษ', '05'),
(49, 'สาขาวิชาภูมิศาสตร์', '05'),
(50, 'สาขาวิชาสังคมศาสตร์การพัฒนา', '05'),
(51, 'สาขาวิชาสารสนเทศศาสตร์และบรรณารักษศาสตร์', '05'),
(52, 'สาขาวิชาสังคีตศิลป์ไทย', '05'),
(53, 'สาขาวิชาเอเชียศึกษา', '05'),
(54, 'สาขาวิชาภาษาไทยเพื่อการพัฒนาอาชีพ', '05'),
(55, 'สาขาวิชาการศึกษาปฐมวัย', '06'),
(56, 'สาขาวิชาการประถมศึกษา', '06'),
(57, 'สาขาวิชาภาษาไทย', '06'),
(58, 'สาขาวิชาภาษาอังกฤษ', '06'),
(59, 'สาขาวิชาภาษาจีน', '06'),
(60, 'สาขาวิชาสังคมศึกษา', '06'),
(61, 'สาขาวิชาศิลปศึกษา', '06'),
(62, 'สาขาวิชาคณิตศาสตร์', '06'),
(63, 'สาขาวิชาฟิสิกส์', '06'),
(64, 'สาขาวิชาเทคโนโลยีการศึกษา', '06'),
(65, 'สาขาวิชาการศึกษาตลอดชีวิต', '06'),
(66, 'จิตวิทยา', '06'),
(67, 'สาขาวิชาวิทยาศาสตร์การกีฬา', '06'),
(68, 'สาขาวิชาการบริหารการศึกษา', '06'),
(69, 'สาขาวิชาหลักสูตรและการสอน', '06'),
(70, 'สาขาวิชาการสอนภาษาไทย', '06'),
(71, 'สาขาวิชาการสอนภาษาอังกฤษ', '06'),
(72, 'สาขาวิชาการสอนสังคมศึกษา', '06'),
(73, 'สาขาวิชาพัฒนศึกษา', '06'),
(74, 'สาขาวิชาวิธีวิจัยทางการศึกษา', '06'),
(75, 'สาขาวิชาหลักสูตรและการสอน', '06'),
(76, 'สาขาวิชาการจัดการนันทนาการ การท่องเที่ยวและกีฬา', '06'),
(77, 'สาขาวิชาคณิตศาสตร์', '07'),
(78, 'สาขาวิชาชีววิทยา', '07'),
(79, 'สาขาวิชาเคมี', '07'),
(80, 'สาขาวิชาฟิสิกส์', '07'),
(81, 'สาขาวิชาสถิติ', '07'),
(82, 'สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม', '07'),
(83, 'สาขาวิชาวิทยาการคอมพิวเตอร์', '07'),
(84, 'สาขาวิชาจุลชีววิทยา', '07'),
(85, 'สาขาวิชาคณิตศาสตร์ประยุกต์', '07'),
(86, 'สาขาวิชาเทคโนโลยีสารสนเทศ', '07'),
(87, 'สาขาวิชาวิทยาการข้อมูล', '07'),
(88, 'สาขาวิชาเคมีศึกษา', '07'),
(89, 'สาขาวิชาสถิติประยุกต์', '07'),
(90, 'สาขาวิชาคณิตศาสตร์ศึกษา', '07'),
(91, 'สาขาวิชาเทคโนโลยีสารสนเทศและนวัตกรรมดิจิทัล', '07'),
(92, 'สาขาวิชานิติวิทยาศาสตร์', '07'),
(93, 'สาขาวิชาคณิตศาสตร์ (หลักสูตรนานาชาติ)', '07'),
(94, 'สาขานิติวิทยาศาสตร์และงานยุติธรรม', '07'),
(95, 'สาขาวิชาเภสัชศาสตร์', '08'),
(96, 'สาขาวิชาเภสัชกรรมคลินิก\r\n', '08'),
(97, 'สาขาวิชาการคุ้มครองผู้บริโภคด้านสาธารณสุข', '08'),
(98, 'สาขาวิชาเภสัชศาสตร์สังคมและการบริหาร', '08'),
(99, 'สาขาวิชาสารสนเทศศาสตร์ทางสุขภาพ', '08'),
(100, 'สาขาวิชาวิทยาการทางเภสัชศาสตร์ (หลักสูตรนานาชาติ)', '08'),
(101, 'สาขาวิชาเทคโนโลยีเภสัชกรรม (หลักสูตรนานาชาติ)', '08'),
(102, 'สาขาวิชาวิศวเภสัชกรรม (หลักสูตรนานาชาติ)', '08'),
(103, 'สาขาเภสัชกรรมคลินิก', '08'),
(175, 'สาขาวิชาปิโตรเคมีและวัสดุพอลิเมอร์', '09'),
(176, 'สาขาวิชาวิศวกรรมอุตสาหการ', '09'),
(177, 'สาขาวิชาวิศวกรรมเครื่องกล', '09'),
(178, 'สาขาวิชาวิศวกรรมเคมี', '09'),
(179, 'สาขาวิชาวิศวกรรมอิเล็กทรอนิกส์และระบบคอมพิวเตอร์', '09'),
(180, 'สาขาวิชาวิศวกรรมการจัดการและโลจิสติกส์', '09'),
(181, 'สาขาวิชาวิศวกรรมวัสดุและนาโนเทคโนโลยี', '09'),
(182, 'สาขาวิชาวิศวกรรมกระบวนการชีวภาพ', '09'),
(183, 'สาขาวิชาเทคโนโลยีอาหาร', '09'),
(184, 'สาขาวิชาเทคโนโลยีชีวภาพ', '09'),
(185, 'สาขาวิชาธุรกิจวิศวกรรม', '09'),
(186, 'สาขาวิชาวิทยาการและวิศวกรรมพอลิเมอร์', '09'),
(187, 'สาขาวิชาการจัดการงานวิศวกรรม', '09'),
(188, 'สาขาวิชาวิศวกรรมพลังงาน', '09'),
(189, 'สาขาวิชาวิศวกรรมไฟฟ้าและคอมพิวเตอร์', '09'),
(190, 'สาขาวิชาธุรกิจวิศวกรรม', '09'),
(191, 'สาขาวิชาวิทยาการและวิศวกรรมพอลิเมอร์ (หลักสูตรนานาชาติ)', '09'),
(192, 'สาขาวิชาการแสดงดนตรี', '10'),
(193, 'สาขาวิชาดนตรีแจ๊ส', '10'),
(194, 'สาขาวิชาดนตรีเชิงพาณิชย์', '10'),
(195, 'หลักสูตรสัตวศาสตร์และเทคโนโลยีการเกษตร', '11'),
(196, 'หลักสูตรเทคโนโลยีการผลิตสัตว์น้ำ', '11'),
(197, 'หลักสูตรเทคโนโลยีการผลิตพืช', '11'),
(198, 'หลักสูตรธุรกิจเกษตร', '11'),
(199, 'หลักสูตรภาวะผู้นำและการสื่อสารทางการเกษตร', '11'),
(200, 'หลักสูตรสัตวศาสตร์', '11'),
(201, 'หลักสูตรชีววิทยาศาสตร์เพื่อเกษตรกรรมที่ยั่งยืน (หลักสูตรนานาชาติ)', '11'),
(202, 'สาขาวิชาการจัดการการท่องเที่ยว', '12'),
(203, 'สาขาวิชาการจัดการชุมชน', '12'),
(204, 'สาขาวิชาการจัดการนวัตกรรมทางธุรกิจ', '12'),
(205, 'สาขาวิชาการตลาด', '12'),
(206, 'สาขาวิชาการจัดการโรงแรม', '12'),
(207, 'สาขาวิชาการจัดการธุรกิจและภาษา', '12'),
(208, 'สาขาวิชาการจัดการงานนิทรรศการและงานอีเว้นท์)\r\n', '12'),
(209, 'สาขาวิชาการจัดการโลจิสติกส์ระหว่างประเทศ', '12'),
(210, 'สาขาวิชารัฐประศาสนศาสตร์', '12'),
(211, 'สาขาวิชาบัญชี', '12'),
(212, 'สาขาวิชาการจัดการภาครัฐและภาคเอกชน', '12'),
(213, 'สาขาวิชาการจัดการท่องเที่ยว โรงแรม และอีเวนต์)', '12'),
(214, 'สาขาวิชาบริหารธุรกิจ', '12'),
(215, 'สาขาวิชาการจัดการ', '12'),
(216, 'สาขาบริหารธุรกิจ', '12'),
(217, 'สาขาวิชานิเทศศาสตร์', '13'),
(218, 'สาขาวิชาเทคโนโลยีดิจิทัลเพื่อธุรกิจ', '13'),
(219, 'สาขาวิชาเทคโนโลยีสารสนเทศเพื่อการออกแบบ', '13');

-- --------------------------------------------------------

--
-- Table structure for table `psychologist`
--

CREATE TABLE `psychologist` (
  `user_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psychologist`
--

INSERT INTO `psychologist` (`user_id`, `first_name`, `last_name`, `email`) VALUES
('pro1', 'พี่บอม', 'บอม', 'napjune1234@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `question_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `survey_id`, `question_text`) VALUES
(31, 1, 'รู้สึกยาก ที่จะสงบจิตใจลงได้'),
(32, 1, 'รู้สึกปากแห้ง คอแห้ง'),
(33, 1, 'การหายใจผิดปกติ (หายใจเร็ว,หายใจไม่ทัน)'),
(34, 1, 'มีแนวโน้มที่จะตอบสนองเกินเหตุต่อสถานการณ์ต่างๆ'),
(35, 1, 'รู้สึกว่าร่างกายบางส่วนสั่นผิดปกติ (เช่น มือสั่น)'),
(36, 1, 'รู้สึกเสียพลังงานไปมากกับการคิดกังวล'),
(41, 1, 'ฉันรู้สึกกังวลกับเหตุการณ์นี้ อาจทำให้ฉันรู้สึกตื่นกลัวและกระทำบางสิ่งที่น่าอับอาย'),
(42, 1, 'รู้สึกกระวนกระวายใจ'),
(43, 1, 'รู้สึกยากที่จะผ่อนคลายตัวเอง '),
(44, 1, 'รู้สึกอดทนไม่ได้เวลามีอะไรมาขัดขวางสิ่งที่ฉันกำลังทำอยู่'),
(45, 1, 'รู้สึกคล้ายจะมีอาการตื่นตระหนก '),
(46, 1, 'รู้สึกค่อนข้างฉุนเฉียวง่าย '),
(47, 1, 'รับรู้ถึงการทำงานของหัวใจแม่ในตอนที่ฉันไม่ได้ออกแรง(เช่นรู้สึกว่าหัวใจเต้นเร็วขึ้นหรือเต้นไม่เป็นจังหวะ)'),
(48, 1, 'รู้สึกกลัวโดยไม่มีเหตุผล '),
(49, 2, 'รู้สึกยาก ที่จะสงบจิตใจลงได้'),
(50, 2, 'แทบไม่รู้สึกอะไรดี ๆ เลย '),
(51, 2, 'พบว่ามันยากที่จะคิดริเริ่มทำสิ่งใดสิ่งหนึ่ง'),
(52, 2, 'มีแนวโน้มที่จะตอบสอมงเกินเหตุต่อสถานการณ์ต่างๆ'),
(53, 2, 'รู้สึกเสียพลังงานไปมากกับการคิดกังวล '),
(54, 2, 'รู้สึกไม่มีเป้าหมายในชีวิต '),
(55, 2, 'รู้สึกกระวนกระวายใจ '),
(56, 2, 'รู้สึกยากที่จะผ่อนคลายตัวเอง'),
(57, 2, 'รู้สึกจิตใจเหงาหงอยเศร้าซีม '),
(58, 2, 'รู้สึกอดทนไม่ได้เวลามีอะไรมาขัดขวางสิ่งที่ฉันกำลังทำอยู่'),
(59, 2, 'รู้สึกไม่มีความกระตือรือร้นต่อสิ่งใด '),
(60, 2, 'รู้สึกเป็นคนไม่มีคุณค่า'),
(61, 2, 'รู้สึกค่อนข้างฉุนเฉียวง่าย'),
(62, 2, 'รู้สึกว่าชีวิตไม่มีความหมาย '),
(63, 3, 'รู้ปากแห้ง คอแห้ง'),
(64, 3, 'แทบไม่รู้สึกอะไรดี ๆ เลย'),
(65, 3, 'การหายใจผิดปกติ (หายใจเร็ว,หายใจไม่ทัน) '),
(66, 3, 'พบว่ามันยากที่จะคิดริเริ่มทำสิ่งใดสิ่งหนึ่ง'),
(67, 3, 'รู้สึกว่าร่างกายบางส่วนสั่นผิดปกติ (เช่น มือสั่น) '),
(68, 3, 'ฉันรู้สึกกังวลกับเหตุการณ์ อาจทำให้ฉันรู้สึกตื่น กลัวและกระทำบางสิ่งที่น่าอับอาย '),
(69, 3, 'รู้สึกไม่มีเป้าหมายในชีวิต '),
(70, 3, 'รู้สึกจิตใจเหงาหงอยเศร้าซีม '),
(71, 3, 'รู้สึกคล้ายจะมีอาการตื่นตระหนก '),
(72, 3, 'รู้สึกไม่มีความกระตือรือร้นต่อสิ่งใด '),
(73, 3, 'รู้สึกเป็นคนไม่มีคุณค่า '),
(74, 3, 'รับรู้ถึงการทำงานของหัวใจแม้ในตอนที่ฉันไม่ได้ออกแรง(เช่นรู้สึกว่าหัวใจเต้นเร็วขึ้นหรือเต้นไม่เป็นจังหวะ) '),
(75, 3, 'รู้สึกกลัวโดยไม่มีเหตุผล '),
(76, 3, 'รู้สึกว่าชีวิตไม่มีความหมาย ');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `std_id` int(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `nickname` varchar(10) NOT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `major` varchar(100) NOT NULL,
  `faculty_id` char(2) NOT NULL,
  `year` int(4) NOT NULL,
  `gender` int(1) NOT NULL,
  `phone` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`std_id`, `first_name`, `last_name`, `nickname`, `mail`, `major`, `faculty_id`, `year`, `gender`, `phone`) VALUES
(64071089, '213', '23', '56', '56d@gmail.com', 'สาขาวิชาทัศนศิลป์', '01', 2564, 1, '123'),
(630810778, 'โฟก', 'โฟก', 'โฟก', 'Foke@gmai..com', 'สาขาวิชาเภสัชศาสตร์สังคมและการบริหาร', '08', 2563, 1, '1234'),
(640710095, 'วันวิสาข์', 'สังวรกิจโสภณ', 'จูน', 'sungwornkitsopo_w@silpakorn.edu', 'สาขาวิชาเทคโนโลยีสารสนเทศ', '07', 2564, 2, '0828616739'),
(640710711, 'ณัฐพล', 'แก้วระวัง', 'โฟก', 'Foke0011@gmail.com', 'สาขาวิชาเทคโนโลยีสารสนเทศ', '07', 2564, 1, '123'),
(640710741, 'Pimchanok', 'Teerapongsakorn', 'Phia', 'pimchnokfia@gmail.com', 'สาขาวิชาเทคโนโลยีสารสนเทศ', '07', 2564, 2, '1356595'),
(660710778, 'pkl', 'pkl', 'pkl', 'f@gmail.com', 'สาขาประวัติศาสตร์ท้องถิ่น', '07', 2566, 1, '569');

-- --------------------------------------------------------

--
-- Table structure for table `test_results`
--

CREATE TABLE `test_results` (
  `result_id` int(11) NOT NULL,
  `std_id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `total_score` int(11) NOT NULL,
  `severity` enum('ปกติ','ต่ำ','ปานกลาง','รุนแรง','รุนแรงที่สุด') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_results`
--

INSERT INTO `test_results` (`result_id`, `std_id`, `survey_id`, `total_score`, `severity`) VALUES
(73, 640710711, 1, 14, 'ปานกลาง'),
(74, 640710711, 1, 35, 'รุนแรงที่สุด'),
(75, 640710711, 1, 37, 'รุนแรงที่สุด'),
(76, 640710711, 1, 0, 'ปกติ'),
(77, 640710711, 1, 20, 'รุนแรง'),
(78, 640710711, 2, 14, 'ปานกลาง'),
(79, 640710711, 3, 24, 'รุนแรงที่สุด'),
(80, 640710711, 1, 11, 'ปานกลาง'),
(81, 640710711, 1, 0, 'ปกติ'),
(82, 640710095, 1, 0, 'ปกติ'),
(83, 640710095, 1, 42, 'รุนแรงที่สุด'),
(84, 640710778, 1, 0, 'ปกติ'),
(85, 640710095, 2, 11, 'ปานกลาง'),
(86, 640710095, 2, 12, 'ปานกลาง');

-- --------------------------------------------------------

--
-- Table structure for table `userid`
--

CREATE TABLE `userid` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Student','Psychologist') NOT NULL DEFAULT 'Psychologist'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userid`
--

INSERT INTO `userid` (`id`, `username`, `password`, `user_type`) VALUES
(22, '640710711', '$2y$10$/RCsMVYN37qSttUR9Y6HMuzk9w06Q9d46/F0X3gxt4z46exX/TKPS', 'Student'),
(23, 'pro1', '$2y$10$7/OPi84lNfQ5FPZC2MTOsudWxLG6/Jz5HFOwPib4XR/hNFixsx47O', 'Psychologist'),
(24, '640710095', '$2y$10$xRkVATOsI1/VVWrvJJgo7OqZYGGZEvggRRjGm4/c0g4t.KyJeBQbm', 'Student'),
(25, '640710741', '$2y$10$WHtFmoJt5x77Wg16QfV9oOJoYJPkeaTkdnNJ3iOJUi3EjGkKye5RC', 'Student'),
(26, '640710095', '$2y$10$yhCs7oh7EClBTDEDOLmrAOD6jqnhuvtYCHn3joLI9c2Xa6cIiqJ82', 'Student'),
(27, '640710778', '$2y$10$gOmssvho4z8Q2H57ioXXk.U5.BYt77vyn..4hJB1vhrWhgRcPi7oC', 'Student'),
(28, '640810778', '$2y$10$LipFngXDUAfguTs8nJ0mzuTMNPVcg9Hqyp5liWt2SCHQiTaVSsMy6', 'Student'),
(29, '64071089', '$2y$10$k/p/W9gK9Z9mT5dEEJZDiu22tJQDar2GxTpFep7jjp8uvyaERvOh.', 'Student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar`
--
ALTER TABLE `calendar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`app_id`);

--
-- Indexes for table `consultation_recipients`
--
ALTER TABLE `consultation_recipients`
  ADD PRIMARY KEY (`hn_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`faculty_id`);

--
-- Indexes for table `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `major`
--
ALTER TABLE `major`
  ADD PRIMARY KEY (`major_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `psychologist`
--
ALTER TABLE `psychologist`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `survey_id` (`survey_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`std_id`),
  ADD KEY `foreign key faculty_id` (`faculty_id`);

--
-- Indexes for table `test_results`
--
ALTER TABLE `test_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `std_id` (`std_id`),
  ADD KEY `survey_id` (`survey_id`);

--
-- Indexes for table `userid`
--
ALTER TABLE `userid`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendar`
--
ALTER TABLE `calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

--
-- AUTO_INCREMENT for table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `app_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=384;

--
-- AUTO_INCREMENT for table `consultation_recipients`
--
ALTER TABLE `consultation_recipients`
  MODIFY `hn_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `form`
--
ALTER TABLE `form`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `major`
--
ALTER TABLE `major`
  MODIFY `major_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `test_results`
--
ALTER TABLE `test_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `userid`
--
ALTER TABLE `userid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `major`
--
ALTER TABLE `major`
  ADD CONSTRAINT `major_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `form` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `foreign key faculty_id` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
