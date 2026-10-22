-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 09:21 PM
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
-- Database: `gcst_rms`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `target`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-20 17:56:44'),
(2, 1, 'UPDATE_EQUIPMENT', 'equip:1', 'Updated Projector Epson -X41', '::1', '2026-04-21 10:36:55'),
(3, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-21 10:50:39'),
(4, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-21 11:06:58'),
(5, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-21 11:24:25'),
(6, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-21 11:31:17'),
(7, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-21 11:44:48'),
(8, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-21 11:46:46'),
(9, 1, 'DELETE_ROOM', 'room:5', NULL, '::1', '2026-04-21 11:47:31'),
(10, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-21 11:48:41'),
(11, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-21 11:49:16'),
(12, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-21 11:49:48'),
(13, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-22 16:32:36'),
(14, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-22 16:55:05'),
(15, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-04-22 16:55:47'),
(16, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-22 16:56:11'),
(17, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-22 19:19:24'),
(18, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-22 19:19:44'),
(19, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-22 19:29:03'),
(20, 1, 'UPDATE_PROFILE', 'user:1', NULL, '::1', '2026-04-22 19:34:59'),
(21, 1, 'UPDATE_USER', 'user:3', 'Updated: full_name=Juan  Reyes role=faculty', '::1', '2026-04-22 19:35:47'),
(22, 1, 'UPDATE_USER', 'user:2', 'Updated: full_name=Maria Santos role=faculty', '::1', '2026-04-22 19:36:00'),
(23, 1, 'UPDATE_ROOM', 'room:1', 'Updated: AVR', '::1', '2026-04-22 19:37:48'),
(24, 1, 'UPDATE_ROOM', 'room:2', 'Updated: Classroom', '::1', '2026-04-22 19:38:48'),
(25, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-22 05:16:13'),
(26, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-22 05:55:19'),
(27, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-22 23:32:59'),
(28, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-23 18:24:14'),
(29, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-23 18:28:16'),
(30, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-23 18:29:18'),
(31, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-23 18:46:13'),
(32, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-04-23 18:47:00'),
(33, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-23 18:56:08'),
(34, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-23 18:59:34'),
(35, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-23 19:00:23'),
(36, 1, 'UPDATE_ROOM', 'room:6', 'Updated: AVR Room', '::1', '2026-04-23 19:01:41'),
(37, 1, 'UPDATE_ROOM', 'room:6', 'Updated: AVR Room', '::1', '2026-04-23 20:09:40'),
(38, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-23 20:10:41'),
(39, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-23 21:22:50'),
(40, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-23 21:25:49'),
(41, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-23 21:30:47'),
(42, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-23 21:34:16'),
(43, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-04-23 21:35:33'),
(44, 3, 'SUBMIT_RESERVATION', 'res:5', 'RES-005 by Juan  Reyes', '::1', '2026-04-23 21:36:38'),
(45, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 01:17:38'),
(46, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-29 11:42:56'),
(47, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 11:43:26'),
(48, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-29 11:44:28'),
(49, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-04-29 11:45:00'),
(50, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 12:02:34'),
(51, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-29 12:04:14'),
(52, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-29 12:04:43'),
(53, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-29 12:18:03'),
(54, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 12:18:27'),
(55, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-29 12:18:49'),
(56, 2, 'UPDATE_PROFILE', 'user:2', NULL, '::1', '2026-04-29 12:19:25'),
(57, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-29 12:19:43'),
(58, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 12:46:40'),
(59, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-29 12:50:09'),
(60, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-29 12:54:31'),
(61, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 12:54:54'),
(62, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-29 12:58:33'),
(63, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 12:59:29'),
(64, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-04-29 12:59:52'),
(65, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-29 13:12:26'),
(66, 1, 'UPDATE_EQUIPMENT', 'equip:2', 'Updated: Laptop Dell Inspiron → Available', '::1', '2026-04-29 13:13:24'),
(67, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-04-29 13:14:16'),
(68, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 13:15:03'),
(69, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-29 12:43:26'),
(70, 4, 'RETURN_EQUIPMENT', 'borrow:6', 'Returned equipment: EQ-006', '::1', '2026-04-30 18:55:24'),
(71, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-04-30 19:04:37'),
(72, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-04-30 19:05:11'),
(73, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-02 09:15:17'),
(74, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-05 21:10:24'),
(75, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-05 21:22:28'),
(76, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-05 21:25:41'),
(77, 4, 'UPDATE_EQUIP_STATUS', 'equip:3', 'EQ-003 → In Use', '::1', '2026-05-05 21:26:16'),
(78, 4, 'UPDATE_EQUIP_STATUS', 'equip:1', 'EQ-001 → In Use', '::1', '2026-05-05 21:26:22'),
(79, 4, 'UPDATE_EQUIP_STATUS', 'equip:1', 'EQ-001 → Available', '::1', '2026-05-05 21:26:26'),
(80, 4, 'UPDATE_EQUIP_STATUS', 'equip:3', 'EQ-003 → Available', '::1', '2026-05-05 21:26:29'),
(81, 4, 'UPDATE_EQUIP_STATUS', 'equip:3', 'EQ-003 → In Use', '::1', '2026-05-05 21:26:41'),
(82, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-05 21:28:07'),
(83, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-05 21:30:19'),
(84, 4, 'RETURN_EQUIPMENT', 'borrow:5', 'Returned equipment: EQ-005', '::1', '2026-05-05 21:30:32'),
(85, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-05 21:47:46'),
(86, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-05 22:14:08'),
(87, 1, 'CREATE_EQUIPMENT', 'equip:7', 'EQ-007 — Test Laptop', '::1', '2026-05-05 22:15:00'),
(88, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-05 22:17:49'),
(89, 4, 'CREATE_BORROWING', 'borrow:3', 'Borrower: John Doe, Equipment: EQ-007', '::1', '2026-05-05 22:18:35'),
(90, 4, 'RETURN_EQUIPMENT', 'borrow:3', 'Returned equipment ID: 7', '::1', '2026-05-05 22:25:32'),
(91, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-05 22:28:09'),
(92, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-05 22:54:21'),
(93, 1, 'RES_APPROVE', 'res:3', 'RES-003 → Approved', '::1', '2026-05-05 23:54:46'),
(94, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-05 23:55:18'),
(95, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-05 23:56:15'),
(96, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 00:01:58'),
(97, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 00:53:16'),
(98, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 00:59:00'),
(99, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 01:02:27'),
(100, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 10:31:33'),
(101, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 10:32:40'),
(102, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 10:33:17'),
(103, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 10:33:34'),
(104, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 10:45:05'),
(105, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 10:46:24'),
(106, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 10:46:44'),
(107, 3, 'SUBMIT_RESERVATION', 'res:6', 'RES-006 by Juan  Reyes', '::1', '2026-05-06 10:47:49'),
(108, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 10:52:02'),
(109, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 11:09:19'),
(110, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 11:09:36'),
(111, 2, 'SUBMIT_RESERVATION', 'res:7', 'RES-007 by Hakdog Santos', '::1', '2026-05-06 11:10:41'),
(112, 2, 'SUBMIT_RESERVATION', 'res:8', 'RES-008 by Hakdog Santos', '::1', '2026-05-06 11:19:57'),
(113, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 00:40:34'),
(114, 1, 'RES_APPROVE', 'res:8', 'RES-008 → Approved', '::1', '2026-05-06 00:41:05'),
(115, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 00:41:32'),
(116, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 00:42:33'),
(117, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 00:44:10'),
(118, 3, 'SUBMIT_RESERVATION', 'res:9', 'RES-009 by Juan  Reyes', '::1', '2026-05-06 00:45:04'),
(119, 3, 'SUBMIT_RESERVATION', 'res:10', 'RES-010 by Juan  Reyes', '::1', '2026-05-06 00:45:21'),
(120, 3, 'SUBMIT_RESERVATION', 'res:11', 'RES-011 by Juan  Reyes', '::1', '2026-05-06 00:47:04'),
(121, 3, 'SUBMIT_RESERVATION', 'res:12', 'RES-012 by Juan  Reyes', '::1', '2026-05-06 00:54:41'),
(122, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 01:49:22'),
(123, 1, 'RES_REJECT', 'res:9', 'RES-009 → Rejected', '::1', '2026-05-06 01:50:09'),
(124, 1, 'RES_REJECT', 'res:10', 'RES-010 → Rejected', '::1', '2026-05-06 01:50:13'),
(125, 1, 'RES_REJECT', 'res:11', 'RES-011 → Rejected', '::1', '2026-05-06 01:50:15'),
(126, 1, 'RES_REJECT', 'res:12', 'RES-012 → Rejected', '::1', '2026-05-06 01:50:18'),
(127, 1, 'RES_REJECT', 'res:7', 'RES-007 → Rejected', '::1', '2026-05-06 01:50:21'),
(128, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 01:50:50'),
(129, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 01:51:26'),
(130, 1, 'DELETE_RESERVATION', 'res:12', NULL, '::1', '2026-05-06 01:51:41'),
(131, 1, 'DELETE_RESERVATION', 'res:11', NULL, '::1', '2026-05-06 01:51:44'),
(132, 1, 'DELETE_RESERVATION', 'res:10', NULL, '::1', '2026-05-06 01:51:48'),
(133, 1, 'DELETE_RESERVATION', 'res:9', NULL, '::1', '2026-05-06 01:51:54'),
(134, 1, 'DELETE_RESERVATION', 'res:17', NULL, '::1', '2026-05-06 01:52:08'),
(135, 1, 'RES_REJECT', 'res:6', 'RES-006 → Rejected', '::1', '2026-05-06 01:52:28'),
(136, 1, 'DELETE_RESERVATION', 'res:6', NULL, '::1', '2026-05-06 01:52:33'),
(137, 1, 'RES_REJECT', 'res:13', 'RES-013 → Rejected', '::1', '2026-05-06 01:52:36'),
(138, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 01:53:17'),
(139, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 01:58:40'),
(140, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 02:00:11'),
(141, 1, 'RES_APPROVE', 'res:21', 'RES-021 → Approved', '::1', '2026-05-06 02:00:21'),
(142, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 02:00:40'),
(143, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 02:14:18'),
(144, 3, 'CANCEL_RESERVATION', 'res:5', 'Cancelled by Juan  Reyes', '::1', '2026-05-06 03:23:19'),
(145, 3, 'CANCEL_RESERVATION', 'res:22', 'Cancelled by Juan  Reyes', '::1', '2026-05-06 03:23:26'),
(146, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 03:24:34'),
(147, 1, 'RES_REJECT', 'res:20', 'RES-020 → Rejected', '::1', '2026-05-06 03:26:25'),
(148, 1, 'DELETE_RESERVATION', 'res:20', NULL, '::1', '2026-05-06 03:26:34'),
(149, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 03:26:53'),
(150, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 03:27:43'),
(151, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 03:38:23'),
(152, 1, 'UPDATE_EQUIPMENT', 'equip:1', 'Updated: Epson Projector V1 → Available', '::1', '2026-05-06 05:19:58'),
(153, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 06:09:11'),
(154, 3, 'SUBMIT_RESERVATION', 'res:24', 'Reservation by Juan  Reyes', '::1', '2026-05-06 06:25:40'),
(155, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 06:26:02'),
(156, 1, 'CREATE_EQUIPMENT', 'equip:8', 'EQ-008 — desk fan', '::1', '2026-05-06 06:46:34'),
(157, 1, 'UPDATE_EQUIPMENT', 'equip:7', 'Updated: Test Laptop → Available', '::1', '2026-05-06 06:47:45'),
(158, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 06:49:46'),
(159, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 06:50:40'),
(160, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 07:02:40'),
(161, 1, 'CREATE_EQUIPMENT', 'equip:9', 'EQ-009 — hatdog na sunog', '::1', '2026-05-06 07:03:51'),
(162, 1, 'RES_APPROVE', 'res:14', 'RES-014 → Approved', '::1', '2026-05-06 07:04:15'),
(163, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 07:06:03'),
(164, 1, 'CREATE_EQUIPMENT', 'equip:10', 'EQ-010 — hatdog na hilaw', '::1', '2026-05-06 07:07:09'),
(165, 1, 'RES_APPROVE', 'res:23', 'RES-023 → Approved', '::1', '2026-05-06 07:07:35'),
(166, 1, 'DELETE_RESERVATION', 'res:22', NULL, '::1', '2026-05-06 07:07:41'),
(167, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 07:08:31'),
(168, 3, 'SUBMIT_RESERVATION', 'res:25', 'Reservation by Juan  Reyes', '::1', '2026-05-06 07:09:25'),
(169, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 07:10:26'),
(170, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 07:27:18'),
(171, 3, 'SUBMIT_RESERVATION', 'res:26', 'Reservation by Juan  Reyes', '::1', '2026-05-06 07:27:49'),
(172, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 07:28:49'),
(173, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 08:22:45'),
(174, 1, 'UPDATE_USER', 'user:2', 'Updated: username=prof.santos role=faculty', '::1', '2026-05-06 08:23:24'),
(175, 1, 'CREATE_ROOM', 'room:7', 'GA-310 — Computer Laboratory', '::1', '2026-05-06 08:26:15'),
(176, 1, 'CREATE_ROOM', 'room:8', 'GA-309 — Classroom', '::1', '2026-05-06 08:35:51'),
(177, 1, 'CREATE_ROOM', 'room:9', 'GA-401 — Classroom', '::1', '2026-05-06 08:36:28'),
(178, 1, 'CREATE_ROOM', 'room:10', 'GA-402 — Classroom', '::1', '2026-05-06 08:36:43'),
(179, 1, 'CREATE_ROOM', 'room:11', 'GA-403 — Classroom', '::1', '2026-05-06 08:36:55'),
(180, 1, 'CREATE_ROOM', 'room:12', 'GA-404 — Classroom', '::1', '2026-05-06 08:37:08'),
(181, 1, 'CREATE_ROOM', 'room:13', 'GD-101 — Classroom', '::1', '2026-05-06 08:37:50'),
(182, 1, 'CREATE_ROOM', 'room:14', 'GD-102 — Classroom', '::1', '2026-05-06 08:38:03'),
(183, 1, 'CREATE_ROOM', 'room:15', 'GD-210 — Criminology Department', '::1', '2026-05-06 08:39:16'),
(184, 1, 'CREATE_ROOM', 'room:16', 'GC-203 — Classroom', '::1', '2026-05-06 08:40:12'),
(185, 1, 'CREATE_EQUIPMENT', 'equip:11', 'EQ-001 — Epson Projector V1', '::1', '2026-05-06 08:41:17'),
(186, 1, 'CREATE_EQUIPMENT', 'equip:12', 'EQ-002 — Epson Projector V1', '::1', '2026-05-06 08:42:47'),
(187, 1, 'CREATE_EQUIPMENT', 'equip:13', 'EQ-003 — Wireless Microphone A', '::1', '2026-05-06 08:52:52'),
(188, 1, 'CREATE_EQUIPMENT', 'equip:14', 'EQ-004 — Portable Speaker Z4', '::1', '2026-05-06 08:54:19'),
(189, 1, 'CREATE_EQUIPMENT', 'equip:15', 'EQ-005 — HDMI Cable 10m', '::1', '2026-05-06 08:55:09'),
(190, 1, 'CREATE_EQUIPMENT', 'equip:16', 'EQ-006 — Lenovo ThinkPad X1', '::1', '2026-05-06 08:56:23'),
(191, 1, 'CREATE_EQUIPMENT', 'equip:17', 'EQ-007 — Test Laptop', '::1', '2026-05-06 08:57:59'),
(192, 1, 'UPDATE_EQUIPMENT', 'equip:17', 'Updated: Test Laptop → Under Repair', '::1', '2026-05-06 08:58:15'),
(193, 1, 'UPDATE_EQUIPMENT', 'equip:17', 'Updated: Test Laptop → Damaged', '::1', '2026-05-06 08:58:25'),
(194, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 08:58:52'),
(195, 3, 'SUBMIT_RESERVATION', 'res:27', 'Reservation by Juan  Reyes', '::1', '2026-05-06 09:00:07'),
(196, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 09:00:36'),
(197, 1, 'RES_APPROVE', 'res:27', 'RES-001 → Approved', '::1', '2026-05-06 09:00:50'),
(198, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 09:01:33'),
(199, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 09:03:18'),
(200, 4, 'CREATE_BORROWING', 'borrow:4', 'Borrower: Julie Oripaypay, Equipment: EQ-003', '::1', '2026-05-06 09:06:58'),
(201, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 09:24:39'),
(202, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 09:28:23'),
(203, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 09:37:14'),
(204, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 09:47:53'),
(205, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 09:55:27'),
(206, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 10:07:29'),
(207, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 10:08:04'),
(208, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-06 10:08:39'),
(209, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 10:19:57'),
(210, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 10:21:38'),
(211, 3, 'SUBMIT_RESERVATION', 'res:28', 'Reservation by Juan  Reyes', '::1', '2026-05-06 10:22:31'),
(212, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 10:22:57'),
(213, 1, 'RES_REJECT', 'res:28', 'RES-002 → Rejected', '::1', '2026-05-06 10:23:15'),
(214, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 10:23:32'),
(215, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 10:24:21'),
(216, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 10:26:38'),
(217, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-06 22:09:56'),
(218, 1, 'UPDATE_EQUIPMENT', 'equip:11', 'Updated: Epson Projector V2 → Available', '::1', '2026-05-06 22:10:40'),
(219, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-06 22:11:23'),
(220, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-06 22:12:07'),
(222, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 01:34:52'),
(223, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 02:23:22'),
(224, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-07 02:24:11'),
(225, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 02:27:25'),
(226, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 02:54:43'),
(227, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 03:37:53'),
(228, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-07 03:39:32'),
(229, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 03:41:42'),
(230, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 03:43:04'),
(231, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-07 03:43:19'),
(232, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 03:46:33'),
(233, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 04:44:28'),
(234, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 06:46:08'),
(235, 1, 'CREATE_ROOM', 'room:17', 'GD-201 — Computer Laboratory', '::1', '2026-05-07 10:52:36'),
(236, 1, 'CREATE_ROOM', 'room:18', 'GD-202 — Classroom', '::1', '2026-05-07 10:53:11'),
(237, 1, 'UPDATE_ROOM', 'room:7', 'Updated: Computer Laboratory', '::1', '2026-05-07 10:53:27'),
(238, 1, 'CREATE_ROOM', 'room:19', 'GD-203 — Classroom', '::1', '2026-05-07 10:54:31'),
(239, 1, 'UPDATE_ROOM', 'room:17', 'Updated: Computer Laboratory', '::1', '2026-05-07 10:55:12'),
(240, 1, 'UPDATE_ROOM', 'room:7', 'Updated: Computer Laboratory', '::1', '2026-05-07 10:55:31'),
(241, 1, 'CREATE_ROOM', 'room:20', 'GD-309 — Classroom', '::1', '2026-05-07 11:04:43'),
(242, 1, 'CREATE_ROOM', 'room:21', 'GC-309 — Classroom', '::1', '2026-05-07 11:05:06'),
(243, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-07 11:38:17'),
(244, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 11:38:42'),
(245, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 11:39:03'),
(246, 1, 'UPDATE_ROOM', 'room:14', 'Updated: Classroom', '::1', '2026-05-07 11:58:57'),
(247, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 12:50:00'),
(248, 3, 'SUBMIT_RESERVATION', 'res:29', 'Reservation by Juan  Reyes', '::1', '2026-05-07 12:51:58'),
(249, 3, 'SUBMIT_RESERVATION', 'res:30', 'Reservation by Juan  Reyes', '::1', '2026-05-07 12:52:40'),
(250, 3, 'SUBMIT_RESERVATION', 'res:31', 'Reservation by Juan  Reyes', '::1', '2026-05-07 12:53:05'),
(251, 3, 'SUBMIT_RESERVATION', 'res:32', 'Reservation by Juan  Reyes', '::1', '2026-05-07 12:53:39'),
(252, 3, 'SUBMIT_RESERVATION', 'res:33', 'Reservation by Juan  Reyes', '::1', '2026-05-07 12:54:03'),
(253, 3, 'SUBMIT_RESERVATION', 'res:34', 'Reservation by Juan  Reyes', '::1', '2026-05-07 12:54:54'),
(254, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 12:55:41'),
(255, 1, 'RES_APPROVE', 'res:34', 'RES-008 → Approved', '::1', '2026-05-07 12:56:08'),
(256, 1, 'RES_REJECT', 'res:33', 'RES-007 → Rejected', '::1', '2026-05-07 12:56:12'),
(257, 1, 'RES_APPROVE', 'res:32', 'RES-006 → Approved', '::1', '2026-05-07 12:56:19'),
(258, 1, 'RES_APPROVE', 'res:31', 'RES-005 → Approved', '::1', '2026-05-07 12:56:20'),
(259, 1, 'RES_REJECT', 'res:30', 'RES-004 → Rejected', '::1', '2026-05-07 12:56:24'),
(260, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 12:59:09'),
(261, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 13:34:07'),
(262, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 14:04:23'),
(263, 1, 'CREATE_EQUIPMENT', 'equip:18', 'EQ-008 — HP- Laptop', '::1', '2026-05-07 14:10:09'),
(264, 1, 'UPDATE_EQUIPMENT', 'equip:12', 'Updated: Epson Projector V1 → Available', '::1', '2026-05-07 14:13:03'),
(265, 1, 'UPDATE_EQUIPMENT', 'equip:11', 'Updated: Epson Projector V2 → In Use', '::1', '2026-05-07 14:15:33'),
(266, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 18:29:31'),
(267, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 18:33:18'),
(268, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 18:40:29'),
(269, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 19:30:13'),
(270, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 19:52:21'),
(271, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 19:52:55'),
(272, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-07 20:21:52'),
(273, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 21:03:57'),
(274, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 21:07:00'),
(275, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-07 21:08:11'),
(276, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 21:12:02'),
(277, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 21:16:04'),
(278, 3, 'SUBMIT_RESERVATION', 'res:35', 'Reservation by Juan  Reyes', '::1', '2026-05-07 21:17:43'),
(279, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 21:18:03'),
(280, 1, 'RES_APPROVE', 'res:35', 'RES-009 → Approved', '::1', '2026-05-07 21:19:06'),
(281, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 21:19:36'),
(282, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-07 21:21:41'),
(283, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-07 21:51:11'),
(284, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-07 21:56:01'),
(285, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-08 06:18:46'),
(286, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-08 06:34:58'),
(287, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-12 21:34:23'),
(288, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 12:26:46'),
(289, 1, 'UPDATE_EQUIPMENT', 'equip:11', 'Updated: Epson Projector V1 → In Use', '::1', '2026-05-13 12:32:33'),
(290, 1, 'UPDATE_EQUIPMENT', 'equip:12', 'Updated: Epson Projector V2 → Available', '::1', '2026-05-13 12:32:54'),
(291, 1, 'CREATE_EQUIPMENT', 'equip:19', 'EQ-011 — Hatdog na sunog', '::1', '2026-05-13 12:42:15'),
(292, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 13:54:45'),
(293, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 02:41:25'),
(294, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 03:55:34'),
(295, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-13 06:13:36'),
(296, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 06:14:04'),
(297, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-13 06:24:25'),
(298, 3, 'SUBMIT_RESERVATION', 'res:36', 'Reservation by Juan  Reyes', '::1', '2026-05-13 07:15:39'),
(299, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 07:16:04'),
(300, 1, 'RES_APPROVE', 'res:36', 'RES-010 → Approved', '::1', '2026-05-13 07:16:21'),
(301, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-13 07:16:34'),
(302, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-13 07:27:41'),
(303, 4, 'CREATE_BORROWING', 'borrow:5', 'Borrower: Hakdog Poklong, Equipment: EQ-004', '::1', '2026-05-13 08:45:03'),
(304, 4, 'RETURN_EQUIPMENT', 'borrow:5', 'Returned equipment ID: 14 (Returned (Damaged))', '::1', '2026-05-13 08:45:10'),
(305, 4, 'RETURN_EQUIPMENT', 'borrow:4', 'Returned equipment ID: 13 (Returned)', '::1', '2026-05-13 08:46:31'),
(306, 4, 'RETURN_EQUIPMENT', 'borrow:5', 'Returned equipment ID: 14 (Returned (Damaged))', '::1', '2026-05-13 08:51:46'),
(307, 4, 'CREATE_BORROWING', 'borrow:6', 'Borrower: Ernest Santos, Equipment: EQ-003', '::1', '2026-05-13 09:00:40'),
(308, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 09:23:55'),
(309, 1, 'CREATE_ROOM', 'room:22', 'GA-303 — General Science Room', '::1', '2026-05-13 09:35:18'),
(310, 1, 'CREATE_ROOM', 'room:23', 'GA-407 — Classroom', '::1', '2026-05-13 09:38:08'),
(311, 1, 'CREATE_ROOM', 'room:24', 'GA-408 — Classroom', '::1', '2026-05-13 09:38:27'),
(312, 1, 'CREATE_ROOM', 'room:25', 'Canteen — School Canteen', '::1', '2026-05-13 09:40:06'),
(313, 1, 'CREATE_ROOM', 'room:26', 'GB-201 — Classroom', '::1', '2026-05-13 09:40:47'),
(314, 1, 'UPDATE_ROOM', 'room:25', 'Updated: School Canteen', '::1', '2026-05-13 09:41:34'),
(315, 1, 'CREATE_ROOM', 'room:27', 'GB-202 — Classroom', '::1', '2026-05-13 09:42:26'),
(316, 1, 'CREATE_ROOM', 'room:28', 'GB-301 — Classroom', '::1', '2026-05-13 09:42:51'),
(317, 1, 'CREATE_ROOM', 'room:29', 'GB-302 — Classroom', '::1', '2026-05-13 09:43:04'),
(318, 1, 'CREATE_ROOM', 'room:30', 'GB-303 — Classroom', '::1', '2026-05-13 09:43:15'),
(319, 1, 'CREATE_ROOM', 'room:31', 'GB-401 — Classroom', '::1', '2026-05-13 09:44:31'),
(320, 1, 'CREATE_ROOM', 'room:32', 'GB-402 — Classroom', '::1', '2026-05-13 09:44:52'),
(321, 1, 'CREATE_ROOM', 'room:33', 'GC-101 — Classroom', '::1', '2026-05-13 09:45:35'),
(322, 1, 'CREATE_ROOM', 'room:34', 'GC-102 — Classroom', '::1', '2026-05-13 09:46:03'),
(323, 1, 'CREATE_ROOM', 'room:35', 'GC-103 — Classroom', '::1', '2026-05-13 09:46:23'),
(324, 1, 'CREATE_ROOM', 'room:36', 'GC-106 — Classroom', '::1', '2026-05-13 09:46:45'),
(325, 1, 'CREATE_ROOM', 'room:37', 'GC-107 — Classroom', '::1', '2026-05-13 09:46:55'),
(326, 1, 'UPDATE_ROOM', 'room:25', 'Updated: School Canteen', '::1', '2026-05-13 09:52:25'),
(327, 1, 'CREATE_ROOM', 'room:38', 'GC-104 — Female', '::1', '2026-05-13 09:54:01'),
(328, 1, 'CREATE_ROOM', 'room:39', 'GC-105 — Male', '::1', '2026-05-13 09:54:19'),
(329, 1, 'CREATE_ROOM', 'room:40', 'GC-201 — Classroom', '::1', '2026-05-13 10:00:15'),
(330, 1, 'CREATE_ROOM', 'room:41', 'GC-202 — Classroom', '::1', '2026-05-13 10:00:39'),
(331, 1, 'CREATE_ROOM', 'room:42', 'GC-207 — Classroom', '::1', '2026-05-13 10:01:06'),
(332, 1, 'CREATE_ROOM', 'room:43', 'GC-204 — Female', '::1', '2026-05-13 10:02:21'),
(333, 1, 'UPDATE_ROOM', 'room:39', 'Updated: Male', '::1', '2026-05-13 10:02:42'),
(334, 1, 'UPDATE_ROOM', 'room:38', 'Updated: Female', '::1', '2026-05-13 10:02:49'),
(335, 1, 'UPDATE_ROOM', 'room:25', 'Updated: School Canteen', '::1', '2026-05-13 10:02:59'),
(336, 1, 'CREATE_ROOM', 'room:44', 'GC-205 — Male', '::1', '2026-05-13 10:03:34'),
(337, 1, 'CREATE_ROOM', 'room:45', 'GC-206 — Classroom', '::1', '2026-05-13 10:04:19'),
(338, 1, 'CREATE_ROOM', 'room:46', 'GC-301 — Classroom', '::1', '2026-05-13 10:05:18'),
(339, 1, 'CREATE_ROOM', 'room:47', 'GC-302 — Classroom', '::1', '2026-05-13 10:05:37'),
(340, 1, 'CREATE_ROOM', 'room:48', 'GC-303 — Classroom', '::1', '2026-05-13 10:05:49'),
(341, 1, 'CREATE_ROOM', 'room:49', 'GC-306 — Classroom', '::1', '2026-05-13 10:06:12'),
(342, 1, 'CREATE_ROOM', 'room:50', 'GC-307 — Classroom', '::1', '2026-05-13 10:06:27'),
(343, 1, 'CREATE_ROOM', 'room:51', 'GC-304 — Female', '::1', '2026-05-13 10:08:25'),
(344, 1, 'CREATE_ROOM', 'room:52', 'GC-305 — Male', '::1', '2026-05-13 10:08:42'),
(345, 1, 'CREATE_ROOM', 'room:53', 'GD-302 — Classroom', '::1', '2026-05-13 11:52:29'),
(346, 1, 'CREATE_ROOM', 'room:54', 'AVR — Classroom', '::1', '2026-05-13 11:53:48'),
(347, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'IT Project Management in GD-302', '::1', '2026-05-13 11:55:07'),
(348, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'Capstone Project and Research  1 in GA-407', '::1', '2026-05-13 11:56:09'),
(349, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'IT Project Management in AVR', '::1', '2026-05-13 11:57:27'),
(350, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'Application Development and Emerging Technologies in GA-309', '::1', '2026-05-13 11:59:00'),
(351, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'Information Assurance and Security 1 in GA-407', '::1', '2026-05-13 12:00:01'),
(352, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'Application Development and Emerging Technologies in GA-309', '::1', '2026-05-13 12:01:07'),
(353, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'GE Elective 2 (The Entrepreneurial Mind) in GA-407', '::1', '2026-05-13 12:02:20'),
(354, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'Information Assurance and Security 1 in GA-407', '::1', '2026-05-13 12:03:57'),
(355, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'GE Elective 2 (The Entrepreneurial Mind) in GA-407', '::1', '2026-05-13 12:05:05'),
(356, 1, 'ADD_CLASS_SCHEDULE', 'schedule:BSIT-3B', 'IT Elective 2 (System Integration and Architecture 2) in GA-403', '::1', '2026-05-13 12:06:38'),
(357, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-13 12:07:29'),
(358, 2, 'SUBMIT_RESERVATION', 'res:37', 'Reservation by Maria Santos', '::1', '2026-05-13 12:15:15'),
(359, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 12:15:31'),
(360, 1, 'RES_REJECT', 'res:37', 'RES-011 → Rejected', '::1', '2026-05-13 12:15:59'),
(361, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-13 12:16:23'),
(362, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-13 12:16:57'),
(363, 3, 'SUBMIT_RESERVATION', 'res:38', 'Reservation by Juan  Reyes', '::1', '2026-05-13 12:18:09'),
(364, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 12:18:32'),
(365, 1, 'CREATE_ROOM', 'room:55', 'GC-401 — Classroom', '::1', '2026-05-13 12:21:44'),
(366, 1, 'CREATE_ROOM', 'room:56', 'GLS C-101 — Classroom', '::1', '2026-05-13 12:33:01'),
(367, 1, 'CREATE_ROOM', 'room:57', 'GLS C-102 — Classroom', '::1', '2026-05-13 12:33:09'),
(368, 1, 'CREATE_ROOM', 'room:58', 'Annex B Lab — Science Laboratory', '::1', '2026-05-13 12:33:53'),
(369, 1, 'CREATE_ROOM', 'room:59', 'GLS C-201 — Classroom', '::1', '2026-05-13 12:34:29'),
(370, 1, 'CREATE_ROOM', 'room:60', 'GLS C-202 — Classroom', '::1', '2026-05-13 12:34:41'),
(371, 1, 'CREATE_ROOM', 'room:61', 'GLS C-203 — Classroom', '::1', '2026-05-13 12:34:53'),
(372, 1, 'CREATE_ROOM', 'room:62', 'GLS B-201 — Classroom', '::1', '2026-05-13 12:35:29'),
(373, 1, 'CREATE_ROOM', 'room:63', 'GLS B-202 — Classroom', '::1', '2026-05-13 12:35:37'),
(374, 1, 'CREATE_ROOM', 'room:64', 'Annex B Canteen — Canteen', '::1', '2026-05-13 12:36:21'),
(375, 1, 'ADD_SECTION', 'section:BSIT-2D', NULL, '::1', '2026-05-13 12:52:40'),
(376, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-13 12:59:25'),
(377, 2, 'SUBMIT_RESERVATION', 'res:39', 'Reservation by Maria Santos', '::1', '2026-05-13 13:00:34'),
(378, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 14:38:02'),
(379, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-13 14:54:26'),
(380, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 15:17:11'),
(381, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-13 15:40:55'),
(382, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 15:43:41'),
(383, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-13 15:44:15'),
(384, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 15:58:29'),
(385, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-13 15:59:23'),
(386, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 16:00:07'),
(387, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-13 16:02:06'),
(388, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-13 16:05:35'),
(389, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-13 16:06:29'),
(390, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-14 00:14:27'),
(391, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-14 00:19:18'),
(392, 4, 'RETURN_EQUIPMENT', 'borrow:6', 'Returned equipment ID: 13 (Returned (Damaged))', '::1', '2026-05-14 00:19:57'),
(393, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-14 00:20:36'),
(394, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-14 00:22:02'),
(395, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-14 08:31:06'),
(396, 1, 'RES_APPROVE', 'res:38', 'RES-012 → Approved', '::1', '2026-05-14 08:37:49'),
(397, 1, 'RES_APPROVE', 'res:39', 'RES-013 → Approved', '::1', '2026-05-14 08:37:59'),
(398, 1, 'RES_REJECT', 'res:29', 'RES-003 → Rejected', '::1', '2026-05-14 08:39:39'),
(399, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-18 01:44:29'),
(400, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-18 01:46:30'),
(401, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-18 01:47:34'),
(402, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-18 05:30:21'),
(403, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-18 06:15:05'),
(404, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-18 06:15:36'),
(405, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-18 06:17:58'),
(406, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-18 06:19:11'),
(407, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-18 06:20:37'),
(408, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-18 06:20:37'),
(409, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-18 06:21:14'),
(410, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-18 06:35:01'),
(411, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-18 06:37:07'),
(412, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-18 07:38:52'),
(413, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-18 07:40:52'),
(414, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-18 07:42:39'),
(415, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-19 22:50:32'),
(416, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-19 22:51:23'),
(417, 4, 'CREATE_BORROWING', 'borrow:7', 'Borrower: jjermenlou, Equipment: EQ-005', '::1', '2026-05-19 22:52:56'),
(418, 4, 'RETURN_EQUIPMENT', 'borrow:7', 'Returned equipment ID: 15 (Returned (Damaged))', '::1', '2026-05-19 22:53:28'),
(419, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-19 22:54:11'),
(420, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-19 22:54:51'),
(421, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-19 23:46:40'),
(422, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-20 01:27:21'),
(423, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-20 02:04:52'),
(424, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-20 02:06:04'),
(425, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-20 02:07:00'),
(426, 2, 'RESERVATION_CREATE', 'reservation:HRES26022601', 'Historical analytics seed: reservation request for Annex B Lab', '127.0.0.1', '2026-02-23 09:16:00'),
(427, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-002', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-03-02 07:40:00'),
(428, 3, 'RESERVATION_CREATE', 'reservation:HRES26030501', 'Historical analytics seed: reservation request for GLS C-201', '127.0.0.1', '2026-02-28 09:23:00'),
(429, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-004', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-03-10 13:10:00'),
(430, 2, 'RESERVATION_CREATE', 'reservation:HRES26031102', 'Historical analytics seed: reservation request for GLS C-201', '127.0.0.1', '2026-03-09 10:29:00'),
(431, 3, 'RESERVATION_CREATE', 'reservation:HRES26031703', 'Historical analytics seed: reservation request for GLS C-201', '127.0.0.1', '2026-03-13 11:35:00'),
(432, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-001', 'Historical analytics seed: borrowed by Maria Santos', '127.0.0.1', '2026-03-18 10:40:00'),
(433, 2, 'RESERVATION_CREATE', 'reservation:HRES26032001', 'Historical analytics seed: reservation request for GA-403', '127.0.0.1', '2026-03-15 09:38:00'),
(434, 2, 'RESERVATION_CREATE', 'reservation:HRES26032502', 'Historical analytics seed: reservation request for GA-310', '127.0.0.1', '2026-03-19 10:43:00'),
(435, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-002', 'Historical analytics seed: borrowed by Maria Santos', '127.0.0.1', '2026-03-25 07:40:00'),
(436, 3, 'RESERVATION_CREATE', 'reservation:HRES26033002', 'Historical analytics seed: reservation request for AVR', '127.0.0.1', '2026-03-24 10:48:00'),
(437, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-008', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-04-01 07:40:00'),
(438, 2, 'RESERVATION_CREATE', 'reservation:HRES26040202', 'Historical analytics seed: reservation request for GD-201', '127.0.0.1', '2026-03-29 10:11:00'),
(439, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-002', 'Historical analytics seed: borrowed by Maria Santos', '127.0.0.1', '2026-04-06 10:40:00'),
(440, 2, 'RESERVATION_CREATE', 'reservation:HRES26040701', 'Historical analytics seed: reservation request for Annex B Lab', '127.0.0.1', '2026-04-04 09:16:00'),
(441, 2, 'RESERVATION_CREATE', 'reservation:HRES26040903', 'Historical analytics seed: reservation request for GA-407', '127.0.0.1', '2026-04-07 11:18:00'),
(442, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-008', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-04-13 10:40:00'),
(443, 2, 'RESERVATION_CREATE', 'reservation:HRES26041501', 'Historical analytics seed: reservation request for GA-309', '127.0.0.1', '2026-04-09 09:24:00'),
(444, 2, 'RESERVATION_CREATE', 'reservation:HRES26041701', 'Historical analytics seed: reservation request for GA-407', '127.0.0.1', '2026-04-14 09:26:00'),
(445, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-004', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-04-17 09:10:00'),
(446, 2, 'RESERVATION_CREATE', 'reservation:HRES26042103', 'Historical analytics seed: reservation request for GA-403', '127.0.0.1', '2026-04-17 11:30:00'),
(447, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-008', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-04-22 10:40:00'),
(448, 2, 'RESERVATION_CREATE', 'reservation:HRES26042303', 'Historical analytics seed: reservation request for GB-202', '127.0.0.1', '2026-04-17 11:32:00'),
(449, 2, 'RESERVATION_CREATE', 'reservation:HRES26042703', 'Historical analytics seed: reservation request for GA-309', '127.0.0.1', '2026-04-22 11:36:00'),
(450, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-007', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-04-27 09:10:00'),
(451, 3, 'RESERVATION_CREATE', 'reservation:HRES26042904', 'Historical analytics seed: reservation request for GB-202', '127.0.0.1', '2026-04-26 12:38:00'),
(452, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-001', 'Historical analytics seed: borrowed by Maria Santos', '127.0.0.1', '2026-04-29 09:10:00'),
(453, 3, 'RESERVATION_CREATE', 'reservation:HRES26050401', 'Historical analytics seed: reservation request for GLS B-201', '127.0.0.1', '2026-04-29 09:43:00'),
(454, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-001', 'Historical analytics seed: borrowed by Maria Santos', '127.0.0.1', '2026-05-04 07:40:00'),
(455, 2, 'RESERVATION_CREATE', 'reservation:HRES26050602', 'Historical analytics seed: reservation request for GD-309', '127.0.0.1', '2026-05-03 10:45:00'),
(456, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-002', 'Historical analytics seed: borrowed by Maria Santos', '127.0.0.1', '2026-05-07 10:40:00'),
(457, 2, 'RESERVATION_CREATE', 'reservation:HRES26050802', 'Historical analytics seed: reservation request for GC-301', '127.0.0.1', '2026-05-03 10:47:00'),
(458, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-003', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-05-11 14:40:00'),
(459, 2, 'RESERVATION_CREATE', 'reservation:HRES26051204', 'Historical analytics seed: reservation request for GA-408', '127.0.0.1', '2026-05-06 12:11:00'),
(460, 3, 'RESERVATION_CREATE', 'reservation:HRES26051403', 'Historical analytics seed: reservation request for GC-301', '127.0.0.1', '2026-05-12 11:13:00'),
(461, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-003', 'Historical analytics seed: borrowed by Maria Santos', '127.0.0.1', '2026-05-14 07:40:00'),
(462, 2, 'RESERVATION_CREATE', 'reservation:HRES26051901', 'Historical analytics seed: reservation request for GA-404', '127.0.0.1', '2026-05-14 09:18:00'),
(463, 4, 'EQUIPMENT_BORROW', 'equipment:EQ-003', 'Historical analytics seed: borrowed by Juan  Reyes', '127.0.0.1', '2026-05-19 07:40:00'),
(464, 1, 'SEED_PREDICTIVE_HISTORY', 'predictive_analytics', 'Historical analytics seed: inserted 179 reservations, 160 borrowings, 7 condition reports, and updated equipment predictions.', '127.0.0.1', '2026-05-20 08:05:00'),
(465, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-20 02:19:08'),
(466, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-20 02:21:32'),
(467, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-20 02:22:39'),
(468, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-20 02:28:22'),
(469, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-20 02:30:04'),
(470, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-20 02:31:32'),
(471, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-20 02:38:58'),
(472, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-20 02:41:20'),
(473, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-20 03:07:35'),
(474, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-20 03:28:33'),
(475, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-20 03:39:44'),
(476, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-20 04:09:34'),
(477, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-20 04:10:23'),
(478, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-20 04:11:30'),
(479, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-22 06:03:32'),
(480, 1, 'CREATE_USER', 'user:5', 'Created user: Marian (maintenance)', '::1', '2026-05-22 06:09:20'),
(481, 1, 'RES_APPROVE', 'res:212', 'HRES26051801 → Approved', '::1', '2026-05-22 06:16:13'),
(482, 1, 'RES_APPROVE', 'res:137', 'HRES26041703 → Approved', '::1', '2026-05-22 06:16:21'),
(483, 1, 'RES_APPROVE', 'res:134', 'HRES26041604 → Approved', '::1', '2026-05-22 06:16:26'),
(484, 1, 'RES_REJECT', 'res:80', 'HRES26032002 → Rejected', '::1', '2026-05-22 06:16:35'),
(485, 1, 'RES_REJECT', 'res:77', 'HRES26031903 → Rejected', '::1', '2026-05-22 06:16:42'),
(486, 1, 'UPDATE_EQUIPMENT', 'equip:19', 'Updated: Hatdog na sunog → Available', '::1', '2026-05-22 06:19:17'),
(487, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-22 06:20:14'),
(488, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-22 06:25:32'),
(489, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-22 06:26:28'),
(490, 3, 'SUBMIT_RESERVATION', 'res:219', 'Reservation by Juan  Reyes', '::1', '2026-05-22 06:27:43'),
(491, 5, 'LOGIN', 'user:5', 'Login: Marian', '::1', '2026-05-22 06:28:25'),
(492, 5, 'CREATE_BORROWING', 'borrow:168', 'Borrower: prof  reyes, Equipment: eq-011', '::1', '2026-05-22 06:31:30'),
(493, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-22 06:37:29'),
(494, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-22 06:40:39'),
(495, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-22 20:00:37'),
(496, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-22 20:05:13'),
(497, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-22 20:08:05'),
(498, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-05-22 20:52:26'),
(499, 3, 'SUBMIT_RESERVATION', 'res:220', 'Reservation by Juan  Reyes', '::1', '2026-05-22 20:53:03'),
(500, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-05-22 20:53:18'),
(501, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-05-22 20:56:15'),
(502, 4, 'RETURN_EQUIPMENT', 'borrow:162', 'Returned equipment ID: 18 (Returned)', '::1', '2026-05-22 20:57:11'),
(503, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-05-22 20:59:46'),
(504, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-21 21:50:16'),
(505, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-21 21:51:49'),
(506, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-10-21 21:52:38'),
(507, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-10-21 21:54:39'),
(508, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-10-21 23:10:50'),
(509, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-21 23:40:15'),
(510, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-10-22 01:19:23'),
(511, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-10-22 03:49:22'),
(512, 4, 'LINK_BORROWER', 'borrow:165', 'Registered borrower ID: 001', '::1', '2026-10-22 04:49:04'),
(513, 4, 'RETURN_EQUIPMENT', 'borrow:165', 'Returned equipment ID: 11 (Returned)', '::1', '2026-10-22 04:50:16'),
(514, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 05:23:15'),
(515, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-10-22 05:33:39'),
(516, 4, 'RETURN_EQUIPMENT', 'borrow:168', 'Returned (Damaged); damage fee: 10.00', '::1', '2026-10-22 06:09:45'),
(517, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-10-22 06:10:46'),
(518, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-10-22 06:13:17'),
(519, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 06:20:44'),
(520, 2, 'LOGIN', 'user:2', 'Login: prof.santos', '::1', '2026-10-22 11:07:13'),
(521, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 11:27:12'),
(522, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-10-22 11:28:23'),
(523, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-10-22 12:15:00'),
(524, 3, 'SUBMIT_RESERVATION', 'res:221', 'Pending', '::1', '2026-10-22 12:16:09'),
(525, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 12:16:32'),
(526, 1, 'RES_REJECT', 'res:221', 'Rejected', '::1', '2026-10-22 12:17:19'),
(527, 1, 'RES_REJECT', 'res:220', 'Rejected', '::1', '2026-10-22 12:17:28'),
(528, 1, 'RES_REJECT', 'res:219', 'Rejected', '::1', '2026-10-22 12:17:31'),
(529, 3, 'LOGIN', 'user:3', 'Login: prof.reyes', '::1', '2026-10-22 12:17:59'),
(530, 3, 'SUBMIT_RESERVATION', 'res:222', 'Pending', '::1', '2026-10-22 12:18:45'),
(531, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 12:19:17'),
(532, 1, 'RES_APPROVE', 'res:222', 'Approved', '::1', '2026-10-22 12:19:26'),
(533, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 12:51:52'),
(534, 1, 'CREATE_USER', 'user:6', 'Created user: Dr.Villanueva (faculty)', '::1', '2026-10-22 12:53:47'),
(535, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 12:54:48'),
(536, 1, 'UPDATE_USER', 'user:6', 'Updated: username=Dr.Villanueva role=vip', '::1', '2026-10-22 12:55:08'),
(537, 1, 'RESET_PASSWORD', 'user:6', NULL, '::1', '2026-10-22 12:55:33'),
(538, 6, 'LOGIN', 'user:6', 'Login: Dr.Villanueva', '::1', '2026-10-22 12:55:51'),
(539, 6, 'SUBMIT_RESERVATION', 'res:223', 'Pending', '::1', '2026-10-22 12:57:13'),
(540, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 12:57:29'),
(541, 1, 'RES_APPROVE', 'res:223', 'Approved', '::1', '2026-10-22 12:58:15'),
(542, 6, 'LOGIN', 'user:6', 'Login: Dr.Villanueva', '::1', '2026-10-22 12:59:27'),
(543, 6, 'SUBMIT_RESERVATION', 'res:224', 'Waiting List', '::1', '2026-10-22 13:00:31'),
(544, 1, 'LOGIN', 'user:1', 'Login: admin', '::1', '2026-10-22 13:00:44'),
(545, 1, 'RES_APPROVE', 'res:224', 'Approved', '::1', '2026-10-22 13:01:06'),
(546, 4, 'LOGIN', 'user:4', 'Login: maint.staff', '::1', '2026-10-22 13:03:39');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `link_ref` varchar(100) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `user_id`, `type`, `title`, `body`, `link_ref`, `is_read`, `created_at`) VALUES
(17, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on May 08, 2026 (22:00).', 'reservations', 1, '2026-05-06 09:00:07'),
(18, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-001 for Classroom has been Approved.', 'reservations', 1, '2026-05-06 09:00:50'),
(19, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for Test Laptop', 'condition_reports', 1, '2026-05-06 09:23:56'),
(20, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on May 06, 2026 (13:00).', 'reservations', 1, '2026-05-06 10:22:31'),
(21, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-002 for Classroom has been Rejected.', 'reservations', 1, '2026-05-06 10:23:15'),
(22, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on Sep 05, 2026 (09:00).', 'reservations', 1, '2026-05-07 12:51:58'),
(23, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on Sep 05, 2026 (10:00).', 'reservations', 1, '2026-05-07 12:52:40'),
(24, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on Sep 05, 2026 (13:00).', 'reservations', 1, '2026-05-07 12:53:05'),
(25, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on Oct 05, 2026 (11:00).', 'reservations', 1, '2026-05-07 12:53:39'),
(26, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on Oct 05, 2026 (15:30).', 'reservations', 1, '2026-05-07 12:54:03'),
(27, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on Nov 05, 2026 (14:00).', 'reservations', 1, '2026-05-07 12:54:54'),
(28, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-008 for Classroom has been Approved.', 'reservations', 1, '2026-05-07 12:56:08'),
(29, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-007 for Classroom has been Rejected.', 'reservations', 1, '2026-05-07 12:56:12'),
(30, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-006 for Classroom has been Approved.', 'reservations', 1, '2026-05-07 12:56:19'),
(31, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-005 for Classroom has been Approved.', 'reservations', 1, '2026-05-07 12:56:20'),
(32, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-004 for Classroom has been Rejected.', 'reservations', 1, '2026-05-07 12:56:24'),
(33, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on May 08, 2026 (09:30).', 'reservations', 1, '2026-05-07 21:17:43'),
(34, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-009 for Classroom has been Approved.', 'reservations', 1, '2026-05-07 21:19:06'),
(35, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on May 14, 2026 (10:00).', 'reservations', 1, '2026-05-13 07:15:39'),
(36, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-010 for Classroom has been Approved.', 'reservations', 1, '2026-05-13 07:16:21'),
(37, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for Portable Speaker Z4', 'condition_reports', 1, '2026-05-13 08:51:51'),
(38, NULL, 'reservation_new', 'New Reservation Request', 'Maria Santos requested Classroom on May 15, 2026 (08:30).', 'reservations', 1, '2026-05-13 12:15:15'),
(39, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-011 for Classroom has been Rejected. Note: Conflict with BSIT-3B Regular Class', 'reservations', 1, '2026-05-13 12:15:59'),
(40, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on May 15, 2026 (11:00).', 'reservations', 1, '2026-05-13 12:18:09'),
(41, NULL, 'reservation_new', 'New Reservation Request', 'Maria Santos requested Computer Laboratory on Jun 01, 2026 (11:00).', 'reservations', 1, '2026-05-13 13:00:34'),
(42, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-004 - Portable Speaker Z4.', 'equipment', 1, '2026-05-13 15:57:14'),
(43, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-003 - Wireless Microphone A.', 'equipment', 1, '2026-05-14 00:19:57'),
(44, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for Wireless Microphone A', 'condition_reports', 1, '2026-05-14 00:20:08'),
(45, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-012 for Classroom has been Approved.', 'reservations', 1, '2026-05-14 08:37:49'),
(46, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation RES-013 for Computer Laboratory has been Approved.', 'reservations', 1, '2026-05-14 08:37:59'),
(47, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-003 for Classroom has been Rejected.', 'reservations', 1, '2026-05-14 08:39:39'),
(48, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-005 - HDMI Cable 10m.', 'equipment', 1, '2026-05-19 22:53:38'),
(49, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for HDMI Cable 10m', 'condition_reports', 1, '2026-05-19 22:53:38'),
(50, 1, 'analytics_seed', 'Historical analytics data loaded', 'Predictive analytics now has 90 days of realistic reservation and equipment history.', 'visual-dashboard', 1, '2026-05-20 08:00:00'),
(51, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-001 - Epson Projector V1.', 'equipment', 1, '2026-05-20 02:19:08'),
(52, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation HRES26051801 for Classroom has been Approved.', 'reservations', 1, '2026-05-22 06:16:13'),
(53, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation HRES26041703 for Classroom has been Approved.', 'reservations', 1, '2026-05-22 06:16:21'),
(54, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation HRES26041604 for Classroom has been Approved.', 'reservations', 1, '2026-05-22 06:16:26'),
(55, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation HRES26032002 for Computer Laboratory has been Rejected.', 'reservations', 1, '2026-05-22 06:16:35'),
(56, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation HRES26031903 for Computer Laboratory has been Rejected.', 'reservations', 1, '2026-05-22 06:16:42'),
(57, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on May 23, 2026 (11:00).', 'reservations', 1, '2026-05-22 06:27:43'),
(58, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes requested Classroom on May 23, 2026 (12:00).', 'reservations', 1, '2026-05-22 20:53:03'),
(59, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-001 - Epson Projector V1.', 'equipment', 1, '2026-10-21 21:50:18'),
(60, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-003 - Wireless Microphone A.', 'equipment', 1, '2026-10-21 21:50:18'),
(61, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-004 - Portable Speaker Z4.', 'equipment', 1, '2026-10-21 21:50:18'),
(62, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-005 - HDMI Cable 10m.', 'equipment', 1, '2026-10-21 21:50:18'),
(63, NULL, 'overdue', 'Overdue equipment', 'Juan  Reyes has overdue equipment: EQ-001.', 'borrow:165', 1, '2026-10-22 04:35:32'),
(64, NULL, 'overdue', 'Overdue equipment', 'prof  reyes has overdue equipment: EQ-011.', 'borrow:168', 1, '2026-10-22 04:35:33'),
(65, NULL, 'disposal', 'Disposal approval required', 'EQ-011 was assessed as Critical and is For Disposal.', 'disposal', 1, '2026-10-22 06:09:45'),
(66, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-011 - Hatdog na sunog.', 'equipment', 1, '2026-10-22 06:09:45'),
(67, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes submitted a request: Pending', 'reservations', 1, '2026-10-22 12:16:09'),
(68, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation for Classroom has been Rejected. Note: expired reservation', 'reservations', 1, '2026-10-22 12:17:19'),
(69, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation for Classroom has been Rejected', 'reservations', 1, '2026-10-22 12:17:28'),
(70, NULL, 'reservation_reject', 'Reservation Rejected', 'Reservation for Classroom has been Rejected', 'reservations', 1, '2026-10-22 12:17:31'),
(71, NULL, 'reservation_new', 'New Reservation Request', 'Juan  Reyes submitted a request: Pending', 'reservations', 1, '2026-10-22 12:18:45'),
(72, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation for Classroom has been Approved', 'reservations', 1, '2026-10-22 12:19:26'),
(73, NULL, 'reservation_new', 'New Reservation Request', 'Mercurio G. Villanueva submitted a request. Priority: VIP. Status: Pending', 'reservations', 1, '2026-10-22 12:57:13'),
(74, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation for Classroom has been Approved', 'reservations', 1, '2026-10-22 12:58:15'),
(75, NULL, 'reservation_new', 'New Reservation Request', 'Mercurio G. Villanueva submitted a request. Priority: VIP. Status: Waiting List', 'reservations', 1, '2026-10-22 13:00:31'),
(76, NULL, 'reservation_approve', 'Reservation Approved', 'Reservation for Classroom has been Approved', 'reservations', 1, '2026-10-22 13:01:06'),
(77, NULL, 'reservation_conflict', 'Reservation Waiting List', 'Classroom: Moved to Waiting List: an overlapping VIP priority request was approved by the admin. Status: Waiting List.', 'reservations', 1, '2026-10-22 13:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed`
--

CREATE TABLE `borrowed` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `borrower_name` varchar(201) NOT NULL,
  `student_faculty_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `time_out` time NOT NULL,
  `expected_return_date` date NOT NULL,
  `due_date` date NOT NULL,
  `expected_return_time` time NOT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('Borrowed','Returned','Returned (Damaged)','Overdue') NOT NULL DEFAULT 'Borrowed',
  `actual_time_in` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `penalty_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `damage_level` enum('None','Not Critical','Critical') NOT NULL DEFAULT 'None',
  `damage_penalty_amount` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `borrowed`
--

INSERT INTO `borrowed` (`id`, `equipment_id`, `borrower_name`, `student_faculty_id`, `department`, `borrow_date`, `time_out`, `expected_return_date`, `due_date`, `expected_return_time`, `purpose`, `status`, `actual_time_in`, `created_at`, `penalty_amount`, `damage_level`, `damage_penalty_amount`) VALUES
(4, 13, 'Julie Oripaypay', '001', 'IT Dept', '2026-05-06', '21:00:00', '2026-05-06', '2026-05-06', '22:00:00', 'Lecture', 'Returned', '2026-05-13 08:46:31', '2026-05-06 09:06:58', 0.00, 'None', 0.00),
(5, 14, 'Hakdog Poklong', NULL, 'Crim Dept', '2026-05-13', '07:44:00', '2026-05-13', '2026-05-13', '08:44:00', NULL, 'Returned (Damaged)', '2026-05-13 08:51:46', '2026-05-13 08:45:03', 0.00, 'None', 0.00),
(6, 13, 'Ernest Santos', NULL, 'IT Dept', '2026-05-13', '08:59:00', '2026-05-14', '2026-05-14', '20:00:00', NULL, 'Returned (Damaged)', '2026-05-14 00:19:57', '2026-05-13 09:00:40', 0.00, 'None', 0.00),
(7, 15, 'jjermenlou', NULL, NULL, '2026-05-19', '22:51:00', '2026-05-19', '2026-05-19', '23:00:00', NULL, 'Returned (Damaged)', '2026-05-19 22:53:28', '2026-05-19 22:52:56', 0.00, 'None', 0.00),
(8, 11, 'Juan  Reyes', NULL, 'IT Department', '2026-02-20', '08:00:00', '2026-02-20', '2026-02-20', '12:00:00', 'Historical analytics seed: Class presentation', 'Returned (Damaged)', '2026-02-20 12:00:00', '2026-02-20 07:40:00', 0.00, 'None', 0.00),
(9, 11, 'Maria Santos', NULL, 'Education Department', '2026-02-20', '09:30:00', '2026-02-20', '2026-02-20', '12:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-02-20 12:31:00', '2026-02-20 09:10:00', 0.00, 'None', 0.00),
(10, 12, 'Maria Santos', NULL, 'General Education', '2026-02-23', '13:30:00', '2026-02-23', '2026-02-23', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-02-23 17:33:00', '2026-02-23 13:10:00', 0.00, 'None', 0.00),
(11, 13, 'Juan  Reyes', NULL, 'Research Office', '2026-02-23', '15:00:00', '2026-02-23', '2026-02-23', '18:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-02-23 18:04:00', '2026-02-23 14:40:00', 0.00, 'None', 0.00),
(12, 15, 'Juan  Reyes', NULL, 'Research Office', '2026-02-24', '15:00:00', '2026-02-24', '2026-02-24', '18:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-02-24 18:04:00', '2026-02-24 14:40:00', 0.00, 'None', 0.00),
(13, 11, 'Maria Santos', NULL, 'IT Department', '2026-02-25', '08:00:00', '2026-02-25', '2026-02-25', '12:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-02-25 12:05:00', '2026-02-25 07:40:00', 0.00, 'None', 0.00),
(14, 12, 'Juan  Reyes', NULL, 'Education Department', '2026-02-25', '09:30:00', '2026-02-25', '2026-02-25', '12:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-02-25 12:36:00', '2026-02-25 09:10:00', 0.00, 'None', 0.00),
(15, 14, 'Juan  Reyes', NULL, 'Education Department', '2026-02-26', '09:30:00', '2026-02-26', '2026-02-26', '12:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-02-26 12:36:00', '2026-02-26 09:10:00', 0.00, 'None', 0.00),
(16, 18, 'Maria Santos', NULL, 'Criminology Department', '2026-02-27', '11:00:00', '2026-02-27', '2026-02-27', '16:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-02-27 16:07:00', '2026-02-27 10:40:00', 0.00, 'None', 0.00),
(17, 12, 'Juan  Reyes', NULL, 'IT Department', '2026-03-02', '08:00:00', '2026-03-02', '2026-03-02', '12:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-03-02 12:10:00', '2026-03-02 07:40:00', 0.00, 'None', 0.00),
(18, 13, 'Maria Santos', NULL, 'Education Department', '2026-03-02', '09:30:00', '2026-03-02', '2026-03-02', '12:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-03-02 12:41:00', '2026-03-02 09:10:00', 0.00, 'None', 0.00),
(19, 14, 'Juan  Reyes', NULL, 'Criminology Department', '2026-03-02', '11:00:00', '2026-03-02', '2026-03-02', '16:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-03-02 16:12:00', '2026-03-02 10:40:00', 0.00, 'None', 0.00),
(20, 14, 'Maria Santos', NULL, 'Education Department', '2026-03-03', '09:30:00', '2026-03-03', '2026-03-03', '12:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-03-03 12:41:00', '2026-03-03 09:10:00', 0.00, 'None', 0.00),
(21, 11, 'Juan  Reyes', NULL, 'Criminology Department', '2026-03-04', '11:00:00', '2026-03-04', '2026-03-04', '16:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-03-04 16:12:00', '2026-03-04 10:40:00', 0.00, 'None', 0.00),
(22, 12, 'Maria Santos', NULL, 'General Education', '2026-03-04', '13:30:00', '2026-03-04', '2026-03-04', '17:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-03-04 17:43:00', '2026-03-04 13:10:00', 0.00, 'None', 0.00),
(23, 13, 'Maria Santos', NULL, 'General Education', '2026-03-05', '13:30:00', '2026-03-05', '2026-03-05', '17:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-03-05 17:43:00', '2026-03-05 13:10:00', 0.00, 'None', 0.00),
(24, 18, 'Juan  Reyes', NULL, 'Research Office', '2026-03-06', '15:00:00', '2026-03-06', '2026-03-06', '18:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-03-06 18:14:00', '2026-03-06 14:40:00', 0.00, 'None', 0.00),
(25, 11, 'Maria Santos', NULL, 'Criminology Department', '2026-03-09', '11:00:00', '2026-03-09', '2026-03-09', '16:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-03-09 16:17:00', '2026-03-09 10:40:00', 0.00, 'None', 0.00),
(26, 12, 'Juan  Reyes', NULL, 'General Education', '2026-03-09', '13:30:00', '2026-03-09', '2026-03-09', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-03-09 17:48:00', '2026-03-09 13:10:00', 0.00, 'None', 0.00),
(27, 14, 'Juan  Reyes', NULL, 'General Education', '2026-03-10', '13:30:00', '2026-03-10', '2026-03-10', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-03-10 17:48:00', '2026-03-10 13:10:00', 0.00, 'None', 0.00),
(28, 11, 'Maria Santos', NULL, 'Research Office', '2026-03-11', '15:00:00', '2026-03-11', '2026-03-11', '18:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-03-11 18:19:00', '2026-03-11 14:40:00', 0.00, 'None', 0.00),
(29, 11, 'Juan  Reyes', NULL, 'IT Department', '2026-03-11', '08:00:00', '2026-03-11', '2026-03-11', '12:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-03-11 12:20:00', '2026-03-11 07:40:00', 0.00, 'None', 0.00),
(30, 13, 'Juan  Reyes', NULL, 'IT Department', '2026-03-12', '08:00:00', '2026-03-12', '2026-03-12', '12:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-03-12 12:20:00', '2026-03-12 07:40:00', 0.00, 'None', 0.00),
(31, 14, 'Maria Santos', NULL, 'Education Department', '2026-03-12', '09:30:00', '2026-03-12', '2026-03-12', '12:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-12 12:51:00', '2026-03-12 09:10:00', 0.00, 'None', 0.00),
(32, 17, 'Maria Santos', NULL, 'Education Department', '2026-03-13', '09:30:00', '2026-03-13', '2026-03-13', '12:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-13 12:51:00', '2026-03-13 09:10:00', 0.00, 'None', 0.00),
(33, 11, 'Juan  Reyes', NULL, 'Research Office', '2026-03-16', '15:00:00', '2026-03-16', '2026-03-16', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-03-16 18:24:00', '2026-03-16 14:40:00', 0.00, 'None', 0.00),
(34, 12, 'Maria Santos', NULL, 'IT Department', '2026-03-16', '08:00:00', '2026-03-16', '2026-03-16', '12:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-03-16 12:25:00', '2026-03-16 07:40:00', 0.00, 'None', 0.00),
(35, 14, 'Maria Santos', NULL, 'IT Department', '2026-03-17', '08:00:00', '2026-03-17', '2026-03-17', '12:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-03-17 12:25:00', '2026-03-17 07:40:00', 0.00, 'None', 0.00),
(36, 19, 'Juan  Reyes', NULL, 'Education Department', '2026-03-18', '09:30:00', '2026-03-18', '2026-03-18', '12:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-03-18 12:56:00', '2026-03-18 09:10:00', 0.00, 'None', 0.00),
(37, 11, 'Maria Santos', NULL, 'Criminology Department', '2026-03-18', '11:00:00', '2026-03-18', '2026-03-18', '16:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-18 16:27:00', '2026-03-18 10:40:00', 0.00, 'None', 0.00),
(38, 13, 'Maria Santos', NULL, 'Criminology Department', '2026-03-19', '11:00:00', '2026-03-19', '2026-03-19', '16:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-19 16:27:00', '2026-03-19 10:40:00', 0.00, 'None', 0.00),
(39, 16, 'Juan  Reyes', NULL, 'General Education', '2026-03-20', '13:30:00', '2026-03-20', '2026-03-20', '17:30:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-03-20 17:58:00', '2026-03-20 13:10:00', 0.00, 'None', 0.00),
(40, 11, 'Maria Santos', NULL, 'Education Department', '2026-03-23', '09:30:00', '2026-03-23', '2026-03-23', '12:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-03-23 13:01:00', '2026-03-23 09:10:00', 0.00, 'None', 0.00),
(41, 12, 'Juan  Reyes', NULL, 'Criminology Department', '2026-03-23', '11:00:00', '2026-03-23', '2026-03-23', '16:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-03-23 16:32:00', '2026-03-23 10:40:00', 0.00, 'None', 0.00),
(42, 13, 'Maria Santos', NULL, 'General Education', '2026-03-23', '13:30:00', '2026-03-23', '2026-03-23', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-23 18:03:00', '2026-03-23 13:10:00', 0.00, 'None', 0.00),
(43, 13, 'Juan  Reyes', NULL, 'Criminology Department', '2026-03-24', '11:00:00', '2026-03-24', '2026-03-24', '16:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-03-24 16:32:00', '2026-03-24 10:40:00', 0.00, 'None', 0.00),
(44, 15, 'Maria Santos', NULL, 'General Education', '2026-03-24', '13:30:00', '2026-03-24', '2026-03-24', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-24 18:03:00', '2026-03-24 13:10:00', 0.00, 'None', 0.00),
(45, 18, 'Maria Santos', NULL, 'General Education', '2026-03-25', '13:30:00', '2026-03-25', '2026-03-25', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-25 18:03:00', '2026-03-25 13:10:00', 0.00, 'None', 0.00),
(46, 11, 'Juan  Reyes', NULL, 'Research Office', '2026-03-25', '15:00:00', '2026-03-25', '2026-03-25', '18:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-03-25 18:34:00', '2026-03-25 14:40:00', 0.00, 'None', 0.00),
(47, 12, 'Maria Santos', NULL, 'IT Department', '2026-03-25', '08:00:00', '2026-03-25', '2026-03-25', '12:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-03-25 12:00:00', '2026-03-25 07:40:00', 0.00, 'None', 0.00),
(48, 13, 'Juan  Reyes', NULL, 'Research Office', '2026-03-26', '15:00:00', '2026-03-26', '2026-03-26', '18:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-03-26 18:34:00', '2026-03-26 14:40:00', 0.00, 'None', 0.00),
(49, 13, 'Maria Santos', NULL, 'IT Department', '2026-03-26', '08:00:00', '2026-03-26', '2026-03-26', '12:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-03-26 12:00:00', '2026-03-26 07:40:00', 0.00, 'None', 0.00),
(50, 16, 'Maria Santos', NULL, 'IT Department', '2026-03-27', '08:00:00', '2026-03-27', '2026-03-27', '12:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-03-27 12:00:00', '2026-03-27 07:40:00', 0.00, 'None', 0.00),
(51, 18, 'Juan  Reyes', NULL, 'Education Department', '2026-03-27', '09:30:00', '2026-03-27', '2026-03-27', '12:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-03-27 12:31:00', '2026-03-27 09:10:00', 0.00, 'None', 0.00),
(52, 11, 'Juan  Reyes', NULL, 'General Education', '2026-03-30', '13:30:00', '2026-03-30', '2026-03-30', '17:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-03-30 17:33:00', '2026-03-30 13:10:00', 0.00, 'None', 0.00),
(53, 12, 'Maria Santos', NULL, 'Research Office', '2026-03-30', '15:00:00', '2026-03-30', '2026-03-30', '18:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-30 18:04:00', '2026-03-30 14:40:00', 0.00, 'None', 0.00),
(54, 13, 'Juan  Reyes', NULL, 'IT Department', '2026-03-30', '08:00:00', '2026-03-30', '2026-03-30', '12:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-03-30 12:05:00', '2026-03-30 07:40:00', 0.00, 'None', 0.00),
(55, 13, 'Maria Santos', NULL, 'Research Office', '2026-03-31', '15:00:00', '2026-03-31', '2026-03-31', '18:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-03-31 18:04:00', '2026-03-31 14:40:00', 0.00, 'None', 0.00),
(56, 14, 'Juan  Reyes', NULL, 'IT Department', '2026-03-31', '08:00:00', '2026-03-31', '2026-03-31', '12:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-03-31 12:05:00', '2026-03-31 07:40:00', 0.00, 'None', 0.00),
(57, 18, 'Juan  Reyes', NULL, 'IT Department', '2026-04-01', '08:00:00', '2026-04-01', '2026-04-01', '12:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-04-01 12:05:00', '2026-04-01 07:40:00', 0.00, 'None', 0.00),
(58, 11, 'Maria Santos', NULL, 'Education Department', '2026-04-01', '09:30:00', '2026-04-01', '2026-04-01', '12:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-01 12:36:00', '2026-04-01 09:10:00', 0.00, 'None', 0.00),
(59, 12, 'Juan  Reyes', NULL, 'Criminology Department', '2026-04-01', '11:00:00', '2026-04-01', '2026-04-01', '16:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-01 16:07:00', '2026-04-01 10:40:00', 0.00, 'None', 0.00),
(60, 13, 'Maria Santos', NULL, 'General Education', '2026-04-01', '13:30:00', '2026-04-01', '2026-04-01', '17:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-01 17:38:00', '2026-04-01 13:10:00', 0.00, 'None', 0.00),
(61, 12, 'Maria Santos', NULL, 'Education Department', '2026-04-02', '09:30:00', '2026-04-02', '2026-04-02', '12:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-02 12:36:00', '2026-04-02 09:10:00', 0.00, 'None', 0.00),
(62, 13, 'Juan  Reyes', NULL, 'Criminology Department', '2026-04-02', '11:00:00', '2026-04-02', '2026-04-02', '16:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-02 16:07:00', '2026-04-02 10:40:00', 0.00, 'None', 0.00),
(63, 15, 'Juan  Reyes', NULL, 'Criminology Department', '2026-04-03', '11:00:00', '2026-04-03', '2026-04-03', '16:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-03 16:07:00', '2026-04-03 10:40:00', 0.00, 'None', 0.00),
(64, 18, 'Maria Santos', NULL, 'General Education', '2026-04-03', '13:30:00', '2026-04-03', '2026-04-03', '17:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-03 17:38:00', '2026-04-03 13:10:00', 0.00, 'None', 0.00),
(65, 11, 'Maria Santos', NULL, 'IT Department', '2026-04-06', '08:00:00', '2026-04-06', '2026-04-06', '12:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-04-06 12:10:00', '2026-04-06 07:40:00', 0.00, 'None', 0.00),
(66, 11, 'Juan  Reyes', NULL, 'Education Department', '2026-04-06', '09:30:00', '2026-04-06', '2026-04-06', '12:30:00', 'Historical analytics seed: Student activity documentation', 'Returned (Damaged)', '2026-04-06 12:41:00', '2026-04-06 09:10:00', 0.00, 'None', 0.00),
(67, 12, 'Maria Santos', NULL, 'Criminology Department', '2026-04-06', '11:00:00', '2026-04-06', '2026-04-06', '16:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-06 16:12:00', '2026-04-06 10:40:00', 0.00, 'None', 0.00),
(68, 13, 'Juan  Reyes', NULL, 'Education Department', '2026-04-07', '09:30:00', '2026-04-07', '2026-04-07', '12:30:00', 'Historical analytics seed: Student activity documentation', 'Returned (Damaged)', '2026-04-07 12:41:00', '2026-04-07 09:10:00', 0.00, 'None', 0.00),
(69, 14, 'Maria Santos', NULL, 'Criminology Department', '2026-04-07', '11:00:00', '2026-04-07', '2026-04-07', '16:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-07 16:12:00', '2026-04-07 10:40:00', 0.00, 'None', 0.00),
(70, 16, 'Maria Santos', NULL, 'Criminology Department', '2026-04-08', '11:00:00', '2026-04-08', '2026-04-08', '16:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-08 16:12:00', '2026-04-08 10:40:00', 0.00, 'None', 0.00),
(71, 11, 'Juan  Reyes', NULL, 'General Education', '2026-04-08', '13:30:00', '2026-04-08', '2026-04-08', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-08 17:43:00', '2026-04-08 13:10:00', 0.00, 'None', 0.00),
(72, 11, 'Maria Santos', NULL, 'Research Office', '2026-04-08', '15:00:00', '2026-04-08', '2026-04-08', '18:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-08 18:14:00', '2026-04-08 14:40:00', 0.00, 'None', 0.00),
(73, 12, 'Juan  Reyes', NULL, 'General Education', '2026-04-09', '13:30:00', '2026-04-09', '2026-04-09', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-09 17:43:00', '2026-04-09 13:10:00', 0.00, 'None', 0.00),
(74, 13, 'Maria Santos', NULL, 'Research Office', '2026-04-09', '15:00:00', '2026-04-09', '2026-04-09', '18:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-09 18:14:00', '2026-04-09 14:40:00', 0.00, 'None', 0.00),
(75, 15, 'Maria Santos', NULL, 'Research Office', '2026-04-10', '15:00:00', '2026-04-10', '2026-04-10', '18:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-10 18:14:00', '2026-04-10 14:40:00', 0.00, 'None', 0.00),
(76, 16, 'Juan  Reyes', NULL, 'IT Department', '2026-04-10', '08:00:00', '2026-04-10', '2026-04-10', '12:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-10 12:15:00', '2026-04-10 07:40:00', 0.00, 'None', 0.00),
(77, 18, 'Juan  Reyes', NULL, 'Criminology Department', '2026-04-13', '11:00:00', '2026-04-13', '2026-04-13', '16:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-04-13 16:17:00', '2026-04-13 10:40:00', 0.00, 'None', 0.00),
(78, 11, 'Maria Santos', NULL, 'General Education', '2026-04-13', '13:30:00', '2026-04-13', '2026-04-13', '17:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-13 17:48:00', '2026-04-13 13:10:00', 0.00, 'None', 0.00),
(79, 12, 'Juan  Reyes', NULL, 'Research Office', '2026-04-13', '15:00:00', '2026-04-13', '2026-04-13', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-13 18:19:00', '2026-04-13 14:40:00', 0.00, 'None', 0.00),
(80, 13, 'Maria Santos', NULL, 'General Education', '2026-04-14', '13:30:00', '2026-04-14', '2026-04-14', '17:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-14 17:48:00', '2026-04-14 13:10:00', 0.00, 'None', 0.00),
(81, 14, 'Juan  Reyes', NULL, 'Research Office', '2026-04-14', '15:00:00', '2026-04-14', '2026-04-14', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-14 18:19:00', '2026-04-14 14:40:00', 0.00, 'None', 0.00),
(82, 16, 'Juan  Reyes', NULL, 'Research Office', '2026-04-15', '15:00:00', '2026-04-15', '2026-04-15', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-15 18:19:00', '2026-04-15 14:40:00', 0.00, 'None', 0.00),
(83, 18, 'Maria Santos', NULL, 'IT Department', '2026-04-15', '08:00:00', '2026-04-15', '2026-04-15', '12:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-15 12:20:00', '2026-04-15 07:40:00', 0.00, 'None', 0.00),
(84, 11, 'Juan  Reyes', NULL, 'Education Department', '2026-04-15', '09:30:00', '2026-04-15', '2026-04-15', '12:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-15 12:51:00', '2026-04-15 09:10:00', 0.00, 'None', 0.00),
(85, 12, 'Maria Santos', NULL, 'IT Department', '2026-04-16', '08:00:00', '2026-04-16', '2026-04-16', '12:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-16 12:20:00', '2026-04-16 07:40:00', 0.00, 'None', 0.00),
(86, 13, 'Juan  Reyes', NULL, 'Education Department', '2026-04-16', '09:30:00', '2026-04-16', '2026-04-16', '12:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-16 12:51:00', '2026-04-16 09:10:00', 0.00, 'None', 0.00),
(87, 14, 'Juan  Reyes', NULL, 'Education Department', '2026-04-17', '09:30:00', '2026-04-17', '2026-04-17', '12:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-17 12:51:00', '2026-04-17 09:10:00', 0.00, 'None', 0.00),
(88, 16, 'Maria Santos', NULL, 'Criminology Department', '2026-04-17', '11:00:00', '2026-04-17', '2026-04-17', '16:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-04-17 16:22:00', '2026-04-17 10:40:00', 0.00, 'None', 0.00),
(89, 18, 'Maria Santos', NULL, 'Research Office', '2026-04-20', '15:00:00', '2026-04-20', '2026-04-20', '18:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-20 18:24:00', '2026-04-20 14:40:00', 0.00, 'None', 0.00),
(90, 11, 'Juan  Reyes', NULL, 'IT Department', '2026-04-20', '08:00:00', '2026-04-20', '2026-04-20', '12:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-20 12:25:00', '2026-04-20 07:40:00', 0.00, 'None', 0.00),
(91, 12, 'Maria Santos', NULL, 'Education Department', '2026-04-20', '09:30:00', '2026-04-20', '2026-04-20', '12:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-20 12:56:00', '2026-04-20 09:10:00', 0.00, 'None', 0.00),
(92, 12, 'Juan  Reyes', NULL, 'IT Department', '2026-04-21', '08:00:00', '2026-04-21', '2026-04-21', '12:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-21 12:25:00', '2026-04-21 07:40:00', 0.00, 'None', 0.00),
(93, 13, 'Maria Santos', NULL, 'Education Department', '2026-04-21', '09:30:00', '2026-04-21', '2026-04-21', '12:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-21 12:56:00', '2026-04-21 09:10:00', 0.00, 'None', 0.00),
(94, 14, 'Juan  Reyes', NULL, 'Criminology Department', '2026-04-21', '11:00:00', '2026-04-21', '2026-04-21', '16:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-21 16:27:00', '2026-04-21 10:40:00', 0.00, 'None', 0.00),
(95, 16, 'Maria Santos', NULL, 'General Education', '2026-04-21', '13:30:00', '2026-04-21', '2026-04-21', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-04-21 17:58:00', '2026-04-21 13:10:00', 0.00, 'None', 0.00),
(96, 15, 'Maria Santos', NULL, 'Education Department', '2026-04-22', '09:30:00', '2026-04-22', '2026-04-22', '12:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-22 12:56:00', '2026-04-22 09:10:00', 0.00, 'None', 0.00),
(97, 18, 'Juan  Reyes', NULL, 'Criminology Department', '2026-04-22', '11:00:00', '2026-04-22', '2026-04-22', '16:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-22 16:27:00', '2026-04-22 10:40:00', 0.00, 'None', 0.00),
(98, 11, 'Maria Santos', NULL, 'General Education', '2026-04-22', '13:30:00', '2026-04-22', '2026-04-22', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-04-22 17:58:00', '2026-04-22 13:10:00', 0.00, 'None', 0.00),
(99, 12, 'Juan  Reyes', NULL, 'Research Office', '2026-04-22', '15:00:00', '2026-04-22', '2026-04-22', '18:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-04-22 18:29:00', '2026-04-22 14:40:00', 0.00, 'None', 0.00),
(100, 11, 'Juan  Reyes', NULL, 'Criminology Department', '2026-04-23', '11:00:00', '2026-04-23', '2026-04-23', '16:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-23 16:27:00', '2026-04-23 10:40:00', 0.00, 'None', 0.00),
(101, 12, 'Maria Santos', NULL, 'General Education', '2026-04-23', '13:30:00', '2026-04-23', '2026-04-23', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-04-23 17:58:00', '2026-04-23 13:10:00', 0.00, 'None', 0.00),
(102, 13, 'Juan  Reyes', NULL, 'Research Office', '2026-04-23', '15:00:00', '2026-04-23', '2026-04-23', '18:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-04-23 18:29:00', '2026-04-23 14:40:00', 0.00, 'None', 0.00),
(103, 14, 'Maria Santos', NULL, 'General Education', '2026-04-24', '13:30:00', '2026-04-24', '2026-04-24', '17:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-04-24 17:58:00', '2026-04-24 13:10:00', 0.00, 'None', 0.00),
(104, 15, 'Juan  Reyes', NULL, 'Research Office', '2026-04-24', '15:00:00', '2026-04-24', '2026-04-24', '18:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-04-24 18:29:00', '2026-04-24 14:40:00', 0.00, 'None', 0.00),
(105, 18, 'Maria Santos', NULL, 'IT Department', '2026-04-24', '08:00:00', '2026-04-24', '2026-04-24', '12:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-24 12:30:00', '2026-04-24 07:40:00', 0.00, 'None', 0.00),
(106, 13, 'Maria Santos', NULL, 'IT Department', '2026-04-26', '08:00:00', '2026-04-26', '2026-04-26', '12:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-26 12:30:00', '2026-04-26 07:40:00', 0.00, 'None', 0.00),
(107, 17, 'Juan  Reyes', NULL, 'Education Department', '2026-04-27', '09:30:00', '2026-04-27', '2026-04-27', '12:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-04-27 13:01:00', '2026-04-27 09:10:00', 0.00, 'None', 0.00),
(108, 11, 'Maria Santos', NULL, 'Criminology Department', '2026-04-27', '11:00:00', '2026-04-27', '2026-04-27', '16:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-27 16:32:00', '2026-04-27 10:40:00', 0.00, 'None', 0.00),
(109, 11, 'Juan  Reyes', NULL, 'General Education', '2026-04-27', '13:30:00', '2026-04-27', '2026-04-27', '17:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-27 18:03:00', '2026-04-27 13:10:00', 0.00, 'None', 0.00),
(110, 12, 'Maria Santos', NULL, 'Research Office', '2026-04-27', '15:00:00', '2026-04-27', '2026-04-27', '18:00:00', 'Historical analytics seed: Faculty workshop', 'Returned (Damaged)', '2026-04-27 18:34:00', '2026-04-27 14:40:00', 0.00, 'None', 0.00),
(111, 12, 'Maria Santos', NULL, 'Criminology Department', '2026-04-28', '11:00:00', '2026-04-28', '2026-04-28', '16:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-04-28 16:32:00', '2026-04-28 10:40:00', 0.00, 'None', 0.00),
(112, 13, 'Juan  Reyes', NULL, 'General Education', '2026-04-28', '13:30:00', '2026-04-28', '2026-04-28', '17:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-28 18:03:00', '2026-04-28 13:10:00', 0.00, 'None', 0.00),
(113, 14, 'Maria Santos', NULL, 'Research Office', '2026-04-28', '15:00:00', '2026-04-28', '2026-04-28', '18:00:00', 'Historical analytics seed: Faculty workshop', 'Returned (Damaged)', '2026-04-28 18:34:00', '2026-04-28 14:40:00', 0.00, 'None', 0.00),
(114, 15, 'Juan  Reyes', NULL, 'General Education', '2026-04-29', '13:30:00', '2026-04-29', '2026-04-29', '17:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-04-29 18:03:00', '2026-04-29 13:10:00', 0.00, 'None', 0.00),
(115, 17, 'Maria Santos', NULL, 'Research Office', '2026-04-29', '15:00:00', '2026-04-29', '2026-04-29', '18:00:00', 'Historical analytics seed: Faculty workshop', 'Returned (Damaged)', '2026-04-29 18:34:00', '2026-04-29 14:40:00', 0.00, 'None', 0.00),
(116, 11, 'Juan  Reyes', NULL, 'IT Department', '2026-04-29', '08:00:00', '2026-04-29', '2026-04-29', '12:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-04-29 12:00:00', '2026-04-29 07:40:00', 0.00, 'None', 0.00),
(117, 11, 'Maria Santos', NULL, 'Education Department', '2026-04-29', '09:30:00', '2026-04-29', '2026-04-29', '12:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-29 12:31:00', '2026-04-29 09:10:00', 0.00, 'None', 0.00),
(118, 11, 'Maria Santos', NULL, 'Research Office', '2026-04-30', '15:00:00', '2026-04-30', '2026-04-30', '18:00:00', 'Historical analytics seed: Faculty workshop', 'Returned (Damaged)', '2026-04-30 18:34:00', '2026-04-30 14:40:00', 0.00, 'None', 0.00),
(119, 12, 'Juan  Reyes', NULL, 'IT Department', '2026-04-30', '08:00:00', '2026-04-30', '2026-04-30', '12:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-04-30 12:00:00', '2026-04-30 07:40:00', 0.00, 'None', 0.00),
(120, 13, 'Maria Santos', NULL, 'Education Department', '2026-04-30', '09:30:00', '2026-04-30', '2026-04-30', '12:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-04-30 12:31:00', '2026-04-30 09:10:00', 0.00, 'None', 0.00),
(121, 14, 'Juan  Reyes', NULL, 'IT Department', '2026-05-01', '08:00:00', '2026-05-01', '2026-05-01', '12:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-01 12:00:00', '2026-05-01 07:40:00', 0.00, 'None', 0.00),
(122, 15, 'Maria Santos', NULL, 'Education Department', '2026-05-01', '09:30:00', '2026-05-01', '2026-05-01', '12:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-01 12:31:00', '2026-05-01 09:10:00', 0.00, 'None', 0.00),
(123, 17, 'Juan  Reyes', NULL, 'Criminology Department', '2026-05-01', '11:00:00', '2026-05-01', '2026-05-01', '16:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-01 16:02:00', '2026-05-01 10:40:00', 0.00, 'None', 0.00),
(124, 11, 'Maria Santos', NULL, 'General Education', '2026-05-01', '13:30:00', '2026-05-01', '2026-05-01', '17:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-05-01 17:33:00', '2026-05-01 13:10:00', 0.00, 'None', 0.00),
(125, 16, 'Maria Santos', NULL, 'General Education', '2026-05-04', '13:30:00', '2026-05-04', '2026-05-04', '17:30:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-05-04 17:33:00', '2026-05-04 13:10:00', 0.00, 'None', 0.00),
(126, 19, 'Juan  Reyes', NULL, 'Research Office', '2026-05-04', '15:00:00', '2026-05-04', '2026-05-04', '18:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-05-04 18:04:00', '2026-05-04 14:40:00', 0.00, 'None', 0.00),
(127, 11, 'Maria Santos', NULL, 'IT Department', '2026-05-04', '08:00:00', '2026-05-04', '2026-05-04', '12:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-05-04 12:05:00', '2026-05-04 07:40:00', 0.00, 'None', 0.00),
(128, 12, 'Juan  Reyes', NULL, 'Education Department', '2026-05-04', '09:30:00', '2026-05-04', '2026-05-04', '12:30:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-04 12:36:00', '2026-05-04 09:10:00', 0.00, 'None', 0.00),
(129, 12, 'Juan  Reyes', NULL, 'Research Office', '2026-05-05', '15:00:00', '2026-05-05', '2026-05-05', '18:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-05-05 18:04:00', '2026-05-05 14:40:00', 0.00, 'None', 0.00),
(130, 13, 'Maria Santos', NULL, 'IT Department', '2026-05-05', '08:00:00', '2026-05-05', '2026-05-05', '12:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-05-05 12:05:00', '2026-05-05 07:40:00', 0.00, 'None', 0.00),
(131, 14, 'Juan  Reyes', NULL, 'Education Department', '2026-05-05', '09:30:00', '2026-05-05', '2026-05-05', '12:30:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-05 12:36:00', '2026-05-05 09:10:00', 0.00, 'None', 0.00),
(132, 15, 'Maria Santos', NULL, 'IT Department', '2026-05-06', '08:00:00', '2026-05-06', '2026-05-06', '12:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-05-06 12:05:00', '2026-05-06 07:40:00', 0.00, 'None', 0.00),
(133, 16, 'Juan  Reyes', NULL, 'Education Department', '2026-05-06', '09:30:00', '2026-05-06', '2026-05-06', '12:30:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-06 12:36:00', '2026-05-06 09:10:00', 0.00, 'None', 0.00),
(134, 19, 'Maria Santos', NULL, 'Criminology Department', '2026-05-06', '11:00:00', '2026-05-06', '2026-05-06', '16:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-06 16:07:00', '2026-05-06 10:40:00', 0.00, 'None', 0.00),
(135, 11, 'Juan  Reyes', NULL, 'General Education', '2026-05-06', '13:30:00', '2026-05-06', '2026-05-06', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-06 17:38:00', '2026-05-06 13:10:00', 0.00, 'None', 0.00),
(136, 11, 'Juan  Reyes', NULL, 'Education Department', '2026-05-07', '09:30:00', '2026-05-07', '2026-05-07', '12:30:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-07 12:36:00', '2026-05-07 09:10:00', 0.00, 'None', 0.00),
(137, 12, 'Maria Santos', NULL, 'Criminology Department', '2026-05-07', '11:00:00', '2026-05-07', '2026-05-07', '16:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-07 16:07:00', '2026-05-07 10:40:00', 0.00, 'None', 0.00),
(138, 13, 'Juan  Reyes', NULL, 'General Education', '2026-05-07', '13:30:00', '2026-05-07', '2026-05-07', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-07 17:38:00', '2026-05-07 13:10:00', 0.00, 'None', 0.00),
(139, 13, 'Maria Santos', NULL, 'Criminology Department', '2026-05-08', '11:00:00', '2026-05-08', '2026-05-08', '16:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-08 16:07:00', '2026-05-08 10:40:00', 0.00, 'None', 0.00),
(140, 15, 'Juan  Reyes', NULL, 'General Education', '2026-05-08', '13:30:00', '2026-05-08', '2026-05-08', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-08 17:38:00', '2026-05-08 13:10:00', 0.00, 'None', 0.00),
(141, 16, 'Maria Santos', NULL, 'Research Office', '2026-05-08', '15:00:00', '2026-05-08', '2026-05-08', '18:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-05-08 18:09:00', '2026-05-08 14:40:00', 0.00, 'None', 0.00),
(142, 18, 'Juan  Reyes', NULL, 'General Education', '2026-05-09', '13:30:00', '2026-05-09', '2026-05-09', '17:30:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-09 17:38:00', '2026-05-09 13:10:00', 0.00, 'None', 0.00),
(143, 16, 'Juan  Reyes', NULL, 'IT Department', '2026-05-11', '08:00:00', '2026-05-11', '2026-05-11', '12:00:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-05-11 12:10:00', '2026-05-11 07:40:00', 0.00, 'None', 0.00),
(144, 18, 'Maria Santos', NULL, 'Education Department', '2026-05-11', '09:30:00', '2026-05-11', '2026-05-11', '12:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-05-11 12:41:00', '2026-05-11 09:10:00', 0.00, 'None', 0.00),
(145, 11, 'Juan  Reyes', NULL, 'Criminology Department', '2026-05-11', '11:00:00', '2026-05-11', '2026-05-11', '16:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-11 16:12:00', '2026-05-11 10:40:00', 0.00, 'None', 0.00),
(146, 12, 'Maria Santos', NULL, 'General Education', '2026-05-11', '13:30:00', '2026-05-11', '2026-05-11', '17:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-11 17:43:00', '2026-05-11 13:10:00', 0.00, 'None', 0.00),
(147, 13, 'Juan  Reyes', NULL, 'Research Office', '2026-05-11', '15:00:00', '2026-05-11', '2026-05-11', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-11 18:14:00', '2026-05-11 14:40:00', 0.00, 'None', 0.00),
(148, 12, 'Maria Santos', NULL, 'Education Department', '2026-05-12', '09:30:00', '2026-05-12', '2026-05-12', '12:30:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-05-12 12:41:00', '2026-05-12 09:10:00', 0.00, 'None', 0.00),
(149, 13, 'Juan  Reyes', NULL, 'Criminology Department', '2026-05-12', '11:00:00', '2026-05-12', '2026-05-12', '16:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-12 16:12:00', '2026-05-12 10:40:00', 0.00, 'None', 0.00),
(150, 13, 'Maria Santos', NULL, 'General Education', '2026-05-12', '13:30:00', '2026-05-12', '2026-05-12', '17:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-12 17:43:00', '2026-05-12 13:10:00', 0.00, 'None', 0.00),
(151, 14, 'Juan  Reyes', NULL, 'Criminology Department', '2026-05-13', '11:00:00', '2026-05-13', '2026-05-13', '16:00:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-13 16:12:00', '2026-05-13 10:40:00', 0.00, 'None', 0.00),
(152, 16, 'Maria Santos', NULL, 'General Education', '2026-05-13', '13:30:00', '2026-05-13', '2026-05-13', '17:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-13 17:43:00', '2026-05-13 13:10:00', 0.00, 'None', 0.00),
(153, 18, 'Juan  Reyes', NULL, 'Research Office', '2026-05-13', '15:00:00', '2026-05-13', '2026-05-13', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-13 18:14:00', '2026-05-13 14:40:00', 0.00, 'None', 0.00),
(154, 11, 'Maria Santos', NULL, 'IT Department', '2026-05-13', '08:00:00', '2026-05-13', '2026-05-13', '12:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-05-13 12:15:00', '2026-05-13 07:40:00', 0.00, 'None', 0.00),
(155, 11, 'Maria Santos', NULL, 'General Education', '2026-05-14', '13:30:00', '2026-05-14', '2026-05-14', '17:30:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-14 17:43:00', '2026-05-14 13:10:00', 0.00, 'None', 0.00),
(156, 12, 'Juan  Reyes', NULL, 'Research Office', '2026-05-14', '15:00:00', '2026-05-14', '2026-05-14', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-14 18:14:00', '2026-05-14 14:40:00', 0.00, 'None', 0.00),
(157, 13, 'Maria Santos', NULL, 'IT Department', '2026-05-14', '08:00:00', '2026-05-14', '2026-05-14', '12:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-05-14 12:15:00', '2026-05-14 07:40:00', 0.00, 'None', 0.00),
(158, 13, 'Juan  Reyes', NULL, 'Research Office', '2026-05-15', '15:00:00', '2026-05-15', '2026-05-15', '18:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-15 18:14:00', '2026-05-15 14:40:00', 0.00, 'None', 0.00),
(159, 14, 'Maria Santos', NULL, 'IT Department', '2026-05-15', '08:00:00', '2026-05-15', '2026-05-15', '12:00:00', 'Historical analytics seed: Laboratory demonstration', 'Returned', '2026-05-15 12:15:00', '2026-05-15 07:40:00', 0.00, 'None', 0.00),
(160, 16, 'Juan  Reyes', NULL, 'Education Department', '2026-05-15', '09:30:00', '2026-05-15', '2026-05-15', '12:30:00', 'Historical analytics seed: Seminar audio support', 'Returned', '2026-05-15 12:46:00', '2026-05-15 09:10:00', 0.00, 'None', 0.00),
(161, 15, 'Maria Santos', NULL, 'Criminology Department', '2026-05-18', '11:00:00', '2026-05-18', '2026-05-18', '16:00:00', 'Historical analytics seed: Faculty workshop', 'Returned', '2026-05-18 16:17:00', '2026-05-18 10:40:00', 0.00, 'None', 0.00),
(162, 18, 'Juan  Reyes', NULL, 'General Education', '2026-05-18', '13:30:00', '2026-05-18', '2026-05-18', '17:30:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-05-22 20:57:11', '2026-05-18 13:10:00', 0.00, 'None', 0.00),
(163, 11, 'Maria Santos', NULL, 'Research Office', '2026-05-18', '15:00:00', '2026-05-18', '2026-05-18', '18:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-18 18:19:00', '2026-05-18 14:40:00', 0.00, 'None', 0.00),
(164, 12, 'Juan  Reyes', NULL, 'IT Department', '2026-05-18', '08:00:00', '2026-05-18', '2026-05-18', '12:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-18 12:20:00', '2026-05-18 07:40:00', 0.00, 'None', 0.00),
(165, 11, 'Julie Oripaypay', '001', 'IT', '2026-05-19', '13:30:00', '2026-05-19', '2026-05-19', '17:30:00', 'Historical analytics seed: Student activity documentation', 'Returned', '2026-10-22 04:50:16', '2026-05-19 13:10:00', 0.00, 'None', 0.00),
(166, 12, 'Maria Santos', NULL, 'Research Office', '2026-05-19', '15:00:00', '2026-05-19', '2026-05-19', '18:00:00', 'Historical analytics seed: Department meeting setup', 'Returned', '2026-05-19 18:19:00', '2026-05-19 14:40:00', 0.00, 'None', 0.00),
(167, 13, 'Juan  Reyes', NULL, 'IT Department', '2026-05-19', '08:00:00', '2026-05-19', '2026-05-19', '12:00:00', 'Historical analytics seed: Class presentation', 'Returned', '2026-05-19 12:20:00', '2026-05-19 07:40:00', 0.00, 'None', 0.00),
(168, 19, 'Julie Oripaypay', '001', 'IT', '2026-05-22', '06:30:00', '2026-05-23', '2026-05-23', '23:30:00', NULL, 'Returned (Damaged)', '2026-10-22 06:09:45', '2026-05-22 06:31:29', 0.00, 'Critical', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `borrowers`
--

CREATE TABLE `borrowers` (
  `id` int(11) NOT NULL,
  `student_faculty_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `department_course` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowers`
--

INSERT INTO `borrowers` (`id`, `student_faculty_id`, `first_name`, `last_name`, `department_course`, `email`, `contact_number`, `created_at`) VALUES
(1, '001', 'Julie', 'Oripaypay', 'IT', 'julieneutronmalik04@gmail.com', '09931254656', '2026-10-22 07:18:23');

-- --------------------------------------------------------

--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `id` int(11) NOT NULL,
  `year_section` varchar(80) NOT NULL,
  `subject_name` varchar(160) NOT NULL,
  `day_of_week` varchar(3) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_code` varchar(50) NOT NULL,
  `room_name` varchar(160) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_schedules`
--

INSERT INTO `class_schedules` (`id`, `year_section`, `subject_name`, `day_of_week`, `start_time`, `end_time`, `room_code`, `room_name`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BSIT-3B', 'IT Project Management', 'W', '08:30:00', '10:00:00', 'GD-302', 'Classroom', 1, '2026-05-13 17:55:07', '2026-05-13 17:55:07'),
(2, 'BSIT-3B', 'Capstone Project and Research  1', 'W', '11:30:00', '13:00:00', 'GA-407', 'Classroom', 1, '2026-05-13 17:56:09', '2026-05-13 17:56:09'),
(3, 'BSIT-3B', 'IT Project Management', 'Th', '08:30:00', '10:00:00', 'AVR', 'Classroom', 1, '2026-05-13 17:57:27', '2026-05-13 17:57:27'),
(4, 'BSIT-3B', 'Application Development and Emerging Technologies', 'Th', '11:30:00', '13:00:00', 'GA-309', 'Classroom', 1, '2026-05-13 17:59:00', '2026-05-13 17:59:00'),
(5, 'BSIT-3B', 'Information Assurance and Security 1', 'F', '07:00:00', '08:30:00', 'GA-407', 'Classroom', 1, '2026-05-13 18:00:01', '2026-05-13 18:00:01'),
(6, 'BSIT-3B', 'Application Development and Emerging Technologies', 'F', '08:30:00', '10:00:00', 'GA-309', 'Classroom', 1, '2026-05-13 18:01:07', '2026-05-13 18:01:07'),
(7, 'BSIT-3B', 'GE Elective 2 (The Entrepreneurial Mind)', 'F', '10:00:00', '11:30:00', 'GA-407', 'Classroom', 1, '2026-05-13 18:02:20', '2026-05-13 18:02:20'),
(8, 'BSIT-3B', 'Information Assurance and Security 1', 'F', '13:00:00', '14:30:00', 'GA-407', 'Classroom', 1, '2026-05-13 18:03:57', '2026-05-13 18:03:57'),
(9, 'BSIT-3B', 'GE Elective 2 (The Entrepreneurial Mind)', 'F', '14:30:00', '16:00:00', 'GA-407', 'Classroom', 1, '2026-05-13 18:05:05', '2026-05-13 18:05:05'),
(10, 'BSIT-3B', 'IT Elective 2 (System Integration and Architecture 2)', 'S', '11:00:00', '14:00:00', 'GA-403', 'Classroom', 1, '2026-05-13 18:06:38', '2026-05-13 18:06:38');

-- --------------------------------------------------------

--
-- Table structure for table `condition_reports`
--

CREATE TABLE `condition_reports` (
  `id` int(11) NOT NULL,
  `report_code` varchar(20) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `issue` text NOT NULL,
  `severity` enum('Low','Medium','High') NOT NULL DEFAULT 'Low',
  `action_needed` varchar(100) DEFAULT 'Monitor',
  `reported_by` int(11) NOT NULL,
  `status` enum('Open','In Progress','Resolved') NOT NULL DEFAULT 'Open',
  `resolution_note` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `damage_level` enum('None','Not Critical','Critical') NOT NULL DEFAULT 'None',
  `assessed_cost` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `condition_reports`
--

INSERT INTO `condition_reports` (`id`, `report_code`, `equipment_id`, `issue`, `severity`, `action_needed`, `reported_by`, `status`, `resolution_note`, `resolved_at`, `created_at`, `damage_level`, `assessed_cost`) VALUES
(1, 'REP-2026-0001', 17, 'Damaged LCD', 'High', 'Replace', 4, 'Resolved', NULL, NULL, '2026-05-06 09:23:56', 'None', 0.00),
(4, 'REP-2026-0002', 14, 'Returned equipment requires inspection/repair: EQ-004 - Portable Speaker Z4.', 'High', 'Repair', 4, 'Open', NULL, NULL, '2026-05-13 08:51:51', 'None', 0.00),
(5, 'REP-2026-0003', 13, 'Returned equipment requires inspection/repair: EQ-003 - Wireless Microphone A.', 'High', 'Repair', 4, 'Open', NULL, NULL, '2026-05-14 00:20:08', 'None', 0.00),
(6, 'REP-2026-0004', 15, 'Returned equipment requires inspection/repair: EQ-005 - HDMI Cable 10m.', 'High', 'Repair', 4, 'Open', NULL, NULL, '2026-05-19 22:53:38', 'None', 0.00),
(7, 'HCR26022001', 11, 'Historical analytics seed: wear observed after frequent classroom use.', 'High', 'Repair', 4, 'In Progress', NULL, NULL, '2026-02-20 16:00:00', 'None', 0.00),
(8, 'HCR26040602', 11, 'Historical analytics seed: wear observed after frequent classroom use.', 'Medium', 'Monitor', 4, 'Resolved', 'Checked during historical maintenance cycle.', '2026-04-06 16:30:00', '2026-04-06 16:00:00', 'None', 0.00),
(9, 'HCR26040701', 13, 'Historical analytics seed: wear observed after frequent classroom use.', 'High', 'Repair', 4, 'In Progress', NULL, NULL, '2026-04-07 16:00:00', 'None', 0.00),
(10, 'HCR26042704', 12, 'Historical analytics seed: wear observed after frequent classroom use.', 'Medium', 'Monitor', 4, 'Resolved', 'Checked during historical maintenance cycle.', '2026-04-27 16:30:00', '2026-04-27 16:00:00', 'None', 0.00),
(11, 'HCR26042803', 14, 'Historical analytics seed: wear observed after frequent classroom use.', 'Medium', 'Monitor', 4, 'Resolved', 'Checked during historical maintenance cycle.', '2026-04-28 16:30:00', '2026-04-28 16:00:00', 'None', 0.00),
(12, 'HCR26042902', 17, 'Historical analytics seed: wear observed after frequent classroom use.', 'Medium', 'Monitor', 4, 'Resolved', 'Checked during historical maintenance cycle.', '2026-04-29 16:30:00', '2026-04-29 16:00:00', 'None', 0.00),
(13, 'HCR26043001', 11, 'Historical analytics seed: wear observed after frequent classroom use.', 'Medium', 'Monitor', 4, 'Resolved', 'Checked during historical maintenance cycle.', '2026-04-30 16:30:00', '2026-04-30 16:00:00', 'None', 0.00),
(14, 'RET-2026-5D8844A1', 19, 'over sa sunog', 'High', 'Decommission', 4, 'Open', NULL, NULL, '2026-10-22 06:09:45', 'Critical', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `damage_assessments`
--

CREATE TABLE `damage_assessments` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `borrowed_id` int(11) DEFAULT NULL,
  `condition_report_id` int(11) NOT NULL,
  `student_faculty_id` varchar(100) DEFAULT NULL,
  `damage_level` enum('Not Critical','Critical') NOT NULL,
  `reason` text NOT NULL,
  `assessed_cost` decimal(12,2) NOT NULL,
  `assessed_by` int(11) DEFAULT NULL,
  `assessed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `damage_assessments`
--

INSERT INTO `damage_assessments` (`id`, `equipment_id`, `borrowed_id`, `condition_report_id`, `student_faculty_id`, `damage_level`, `reason`, `assessed_cost`, `assessed_by`, `assessed_at`) VALUES
(1, 19, 168, 14, '001', 'Critical', 'over sa sunog', 10.00, 4, '2026-10-22 06:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `disposal_requests`
--

CREATE TABLE `disposal_requests` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `borrowed_id` int(11) DEFAULT NULL,
  `reason` text NOT NULL,
  `assessed_cost` decimal(12,2) NOT NULL,
  `disposal_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `requested_by` int(11) DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disposal_requests`
--

INSERT INTO `disposal_requests` (`id`, `equipment_id`, `assessment_id`, `borrowed_id`, `reason`, `assessed_cost`, `disposal_status`, `requested_by`, `requested_at`, `reviewed_by`, `reviewed_at`, `review_note`) VALUES
(1, 19, 1, 168, 'over sa sunog', 10.00, 'Pending', 4, '2026-10-22 06:09:45', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `equip_code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `qr_string` varchar(100) NOT NULL,
  `qr_image_path` varchar(255) DEFAULT NULL,
  `status` enum('Available','In Use','Damaged','Under Repair','For Disposal','Disposed') NOT NULL DEFAULT 'Available',
  `assigned_room` varchar(20) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchased_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `damage_level` enum('None','Not Critical','Critical') NOT NULL DEFAULT 'None'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `equip_code`, `name`, `description`, `qr_string`, `qr_image_path`, `status`, `assigned_room`, `serial_number`, `purchased_date`, `created_at`, `updated_at`, `damage_level`) VALUES
(11, 'EQ-001', 'Epson Projector V1', 'High-definition projector for classrooms', 'EQ-001', 'qrcodes/EQ-001.png', 'Available', 'SBO Office', 'SN-EPS-001', '2026-04-26', '2026-05-06 08:41:17', '2026-10-22 04:50:16', 'None'),
(12, 'EQ-002', 'Epson Projector V2', 'High-definition projector for classrooms', 'EQ-002', 'qrcodes/EQ-002.png', 'Available', 'GA-309', 'SN-EPS-002', '2026-04-26', '2026-05-06 08:42:47', '2026-05-13 12:32:54', 'None'),
(13, 'EQ-003', 'Wireless Microphone A', NULL, 'EQ-003', 'qrcodes/EQ-003.png', 'Damaged', 'SBO Office', 'SN-MIC-003', '2026-04-26', '2026-05-06 08:52:52', '2026-05-14 00:19:57', 'None'),
(14, 'EQ-004', 'Portable Speaker Z4', NULL, 'EQ-004', 'qrcodes/EQ-004.png', 'Damaged', 'SBO Office', 'SN-SPK-004', '2026-04-26', '2026-05-06 08:54:19', '2026-05-13 08:45:10', 'None'),
(15, 'EQ-005', 'HDMI Cable 10m', NULL, 'EQ-005', 'qrcodes/EQ-005.png', 'Damaged', 'SBO Office', 'SN-CAB-005', '2026-04-26', '2026-05-06 08:55:09', '2026-05-19 22:53:28', 'None'),
(16, 'EQ-006', 'Lenovo ThinkPad X1', NULL, 'EQ-006', 'qrcodes/EQ-006.png', 'In Use', 'GD-210', 'SN-LP-006', '2024-12-05', '2026-05-06 08:56:23', '2026-05-06 08:56:23', 'None'),
(17, 'EQ-007', 'Test Laptop', NULL, 'EQ-007', 'qrcodes/EQ-007.png', 'Available', 'SBO Office', 'SN-LP-007', '2026-05-05', '2026-05-06 08:57:59', '2026-05-13 09:14:34', 'None'),
(18, 'EQ-008', 'HP- Laptop', NULL, 'EQ-008', 'qrcodes/EQ-008.png', 'Available', 'GD-201', 'SN-LAPTOP-008', '2026-05-07', '2026-05-07 14:10:09', '2026-05-22 20:57:11', 'None'),
(19, 'EQ-011', 'Hatdog na sunog', 'consumable', 'EQ-011', 'qrcodes/EQ-011.png', 'For Disposal', 'SBO Office', 'SN-HATDOG-011', '2026-05-13', '2026-05-13 12:42:15', '2026-10-22 06:09:45', 'Critical');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_predictions`
--

CREATE TABLE `equipment_predictions` (
  `equipment_id` int(11) NOT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `usage_hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `condition_reports` int(11) NOT NULL DEFAULT 0,
  `severe_reports` int(11) NOT NULL DEFAULT 0,
  `health_score` int(11) NOT NULL DEFAULT 100,
  `health_status` varchar(40) NOT NULL DEFAULT 'Healthy',
  `lifecycle_percent` int(11) NOT NULL DEFAULT 100,
  `maintenance_prediction` varchar(80) DEFAULT NULL,
  `is_consumable` tinyint(1) NOT NULL DEFAULT 0,
  `burn_rate_days` decimal(10,2) DEFAULT NULL,
  `reorder_within_days` int(11) DEFAULT NULL,
  `replenishment_message` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_predictions`
--

INSERT INTO `equipment_predictions` (`equipment_id`, `usage_count`, `usage_hours`, `condition_reports`, `severe_reports`, `health_score`, `health_status`, `lifecycle_percent`, `maintenance_prediction`, `is_consumable`, `burn_rate_days`, `reorder_within_days`, `replenishment_message`, `updated_at`) VALUES
(11, 36, 3877.45, 3, 1, 31, 'Critical', 41, 'Maintenance Predicted: Next Month', 0, NULL, NULL, NULL, '2026-10-22 13:03:39'),
(12, 30, 120.60, 1, 0, 74, 'Watch', 84, NULL, 0, NULL, NULL, NULL, '2026-10-22 13:03:39'),
(13, 33, 296.25, 2, 2, 10, 'Critical', 20, 'Maintenance Predicted: Next Month', 0, NULL, NULL, NULL, '2026-10-22 13:03:39'),
(14, 18, 70.65, 2, 1, 44, 'Critical', 54, 'Maintenance Predicted: Next Month', 0, NULL, NULL, NULL, '2026-10-22 13:03:39'),
(15, 12, 43.98, 1, 1, 56, 'Maintenance Predicted', 66, 'Maintenance Predicted: Next Month', 1, 1.00, 1, 'HDMI Cable 10m should be reordered within 1 days.', '2026-10-22 13:03:39'),
(16, 13, 53.02, 0, 0, 93, 'Healthy', 63, NULL, 0, NULL, NULL, NULL, '2026-10-22 13:03:39'),
(17, 4, 15.47, 2, 1, 72, 'Watch', 82, NULL, 0, NULL, NULL, NULL, '2026-10-22 13:03:39'),
(18, 15, 161.10, 0, 0, 76, 'Watch', 86, NULL, 0, NULL, NULL, NULL, '2026-10-22 13:03:39'),
(19, 4, 3683.27, 1, 1, 47, 'Maintenance Predicted', 57, 'Maintenance Predicted: Next Month', 1, 152.00, 15, 'Inventory Alert: Hatdog na sunog are depleting 100% faster than last month. Consider increasing stock.', '2026-10-22 13:03:39');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_notifications`
--

CREATE TABLE `faculty_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `link_ref` varchar(100) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculty_notifications`
--

INSERT INTO `faculty_notifications` (`id`, `user_id`, `type`, `title`, `body`, `link_ref`, `is_read`, `created_at`) VALUES
(1, 2, 'reservation_approved', 'Reservation Approved', 'Your request for Science Laboratory on today was approved.', 'my-reservations', 1, '2026-04-29 11:33:27'),
(2, 2, 'reservation_rejected', 'Reservation Rejected', 'Your request for Computer Room A was not approved.', 'my-reservations', 1, '2026-04-29 11:33:27'),
(3, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-020 for Classroom has been Rejected.', 'my-reservations', 1, '2026-05-06 03:26:25'),
(4, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-014 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-06 07:04:15'),
(5, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-023 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-06 07:07:35'),
(6, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-001 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-06 09:00:50'),
(7, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-002 for Classroom has been Rejected.', 'my-reservations', 1, '2026-05-06 10:23:15'),
(8, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-008 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-07 12:56:08'),
(9, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-007 for Classroom has been Rejected.', 'my-reservations', 1, '2026-05-07 12:56:12'),
(10, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-006 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-07 12:56:19'),
(11, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-005 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-07 12:56:20'),
(12, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-004 for Classroom has been Rejected.', 'my-reservations', 1, '2026-05-07 12:56:24'),
(13, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-009 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-07 21:19:06'),
(14, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-010 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-13 07:16:21'),
(15, 2, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-011 for Classroom has been Rejected. Note: Conflict with BSIT-3B Regular Class', 'my-reservations', 1, '2026-05-13 12:15:59'),
(16, 3, 'reservation_approve', 'Reservation Approved', 'Reservation RES-012 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-14 08:37:49'),
(17, 2, 'reservation_approve', 'Reservation Approved', 'Reservation RES-013 for Computer Laboratory has been Approved.', 'my-reservations', 0, '2026-05-14 08:37:59'),
(18, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation RES-003 for Classroom has been Rejected.', 'my-reservations', 1, '2026-05-14 08:39:39'),
(19, 3, 'reservation_approve', 'Reservation Approved', 'Reservation HRES26051801 for Classroom has been Approved.', 'my-reservations', 1, '2026-05-22 06:16:13'),
(20, 2, 'reservation_approve', 'Reservation Approved', 'Reservation HRES26041703 for Classroom has been Approved.', 'my-reservations', 0, '2026-05-22 06:16:21'),
(21, 2, 'reservation_approve', 'Reservation Approved', 'Reservation HRES26041604 for Classroom has been Approved.', 'my-reservations', 0, '2026-05-22 06:16:26'),
(22, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation HRES26032002 for Computer Laboratory has been Rejected.', 'my-reservations', 1, '2026-05-22 06:16:35'),
(23, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation HRES26031903 for Computer Laboratory has been Rejected.', 'my-reservations', 1, '2026-05-22 06:16:42'),
(24, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation for Classroom has been Rejected. Note: expired reservation', 'my-reservations', 0, '2026-10-22 12:17:19'),
(25, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation for Classroom has been Rejected', 'my-reservations', 0, '2026-10-22 12:17:28'),
(26, 3, 'reservation_reject', 'Reservation Rejected', 'Reservation for Classroom has been Rejected', 'my-reservations', 0, '2026-10-22 12:17:31'),
(27, 3, 'reservation_approve', 'Reservation Approved', 'Reservation for Classroom has been Approved', 'my-reservations', 0, '2026-10-22 12:19:26'),
(28, 6, 'reservation_approve', 'Reservation Approved', 'Reservation for Classroom has been Approved', 'my-reservations', 0, '2026-10-22 12:58:15'),
(29, 6, 'reservation_approve', 'Reservation Approved', 'Reservation for Classroom has been Approved', 'my-reservations', 0, '2026-10-22 13:01:06'),
(30, 3, 'reservation_conflict', 'Reservation Waiting List', 'Classroom: Moved to Waiting List: an overlapping VIP priority request was approved by the admin. Status: Waiting List.', 'my-reservations', 0, '2026-10-22 13:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `location_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `location_name`) VALUES
(16, 'Annex B, Building A'),
(14, 'Annex B, Building B, 1st Floor'),
(15, 'Annex B, Building B, 2nd Floor'),
(17, 'Annex B, Building C, 1st Floor'),
(18, 'Annex B, Building C, 2nd Floor'),
(1, 'Building A, 3rd Floor'),
(2, 'Building A, 4th Floor'),
(8, 'Building B, 1st Floor'),
(9, 'Building B, 2nd Floor'),
(10, 'Building B, 3rd Floor'),
(19, 'Building B, 4th Floor'),
(11, 'Building C, 1st Floor'),
(5, 'Building C, 2nd Floor'),
(7, 'Building C, 3rd Floor'),
(12, 'Building C, 4th Floor'),
(3, 'Building D, 1st Floor'),
(4, 'Building D, 2nd Floor'),
(6, 'Building D, 3rd Floor'),
(13, 'Building D, 4th Floor');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_notifications`
--

CREATE TABLE `maintenance_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `link_ref` varchar(100) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_notifications`
--

INSERT INTO `maintenance_notifications` (`id`, `user_id`, `type`, `title`, `body`, `link_ref`, `is_read`, `created_at`) VALUES
(4, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for Test Laptop', 'condition_reports', 1, '2026-05-06 09:23:56'),
(5, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for Portable Speaker Z4', 'condition_reports', 1, '2026-05-13 08:51:51'),
(6, 4, 'report', 'Report Resolved', 'Your condition report for Test Laptop has been resolved and the equipment is now available.', 'condition_reports', 1, '2026-05-13 09:14:34'),
(7, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-004 - Portable Speaker Z4.', 'equipment', 1, '2026-05-13 15:57:14'),
(8, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-003 - Wireless Microphone A.', 'equipment', 1, '2026-05-14 00:19:57'),
(9, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for Wireless Microphone A', 'condition_reports', 1, '2026-05-14 00:20:08'),
(10, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-005 - HDMI Cable 10m.', 'equipment', 1, '2026-05-19 22:53:38'),
(11, NULL, 'report', 'New Condition Report', 'New condition report (High severity) filed for HDMI Cable 10m', 'condition_reports', 1, '2026-05-19 22:53:38'),
(12, 4, 'condition_report', 'Equipment condition follow-up', 'EQ-001 needs review after frequent historical use.', 'equipment', 1, '2026-02-20 16:05:00'),
(13, 4, 'condition_report', 'Equipment condition follow-up', 'EQ-001 needs review after frequent historical use.', 'equipment', 1, '2026-04-06 16:05:00'),
(14, 4, 'condition_report', 'Equipment condition follow-up', 'EQ-003 needs review after frequent historical use.', 'equipment', 1, '2026-04-07 16:05:00'),
(15, 4, 'condition_report', 'Equipment condition follow-up', 'EQ-002 needs review after frequent historical use.', 'equipment', 1, '2026-04-27 16:05:00'),
(16, 4, 'condition_report', 'Equipment condition follow-up', 'EQ-004 needs review after frequent historical use.', 'equipment', 1, '2026-04-28 16:05:00'),
(17, 4, 'condition_report', 'Equipment condition follow-up', 'EQ-007 needs review after frequent historical use.', 'equipment', 1, '2026-04-29 16:05:00'),
(18, 4, 'condition_report', 'Equipment condition follow-up', 'EQ-001 needs review after frequent historical use.', 'equipment', 1, '2026-04-30 16:05:00'),
(19, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-001 - Epson Projector V1.', 'equipment', 1, '2026-05-20 02:19:08'),
(20, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-001 - Epson Projector V1.', 'equipment', 1, '2026-10-21 21:50:18'),
(21, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-003 - Wireless Microphone A.', 'equipment', 1, '2026-10-21 21:50:18'),
(22, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-004 - Portable Speaker Z4.', 'equipment', 1, '2026-10-21 21:50:18'),
(23, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-005 - HDMI Cable 10m.', 'equipment', 1, '2026-10-21 21:50:18'),
(24, NULL, 'overdue', 'Overdue equipment', 'Juan  Reyes has overdue equipment: EQ-001.', 'borrow:165', 1, '2026-10-22 04:35:32'),
(25, NULL, 'overdue', 'Overdue equipment', 'prof  reyes has overdue equipment: EQ-011.', 'borrow:168', 1, '2026-10-22 04:35:33'),
(26, NULL, 'prediction', 'Maintenance Predicted', 'Maintenance Predicted: Next Month for EQ-011 - Hatdog na sunog.', 'equipment', 1, '2026-10-22 06:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `overdue_sms_log`
--

CREATE TABLE `overdue_sms_log` (
  `id` int(11) NOT NULL,
  `borrowed_id` int(11) NOT NULL,
  `notification_date` date NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('Pending','Processing','Simulated','Accepted','Failed','Skipped','Unknown') NOT NULL DEFAULT 'Pending',
  `provider_reference` varchar(100) DEFAULT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `overdue_sms_log`
--

INSERT INTO `overdue_sms_log` (`id`, `borrowed_id`, `notification_date`, `contact_number`, `message`, `status`, `provider_reference`, `error_message`, `created_at`, `processed_at`) VALUES
(1, 165, '2026-10-22', '09931254656', 'Hello Julie Oripaypay, your borrowed equipment EQ-001 is overdue. Please return it immediately to avoid penalties.', 'Simulated', NULL, NULL, '2026-10-22 04:49:09', '2026-10-22 04:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `res_id` varchar(20) DEFAULT NULL,
  `res_code` varchar(20) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `room_id` int(11) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `res_date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `purpose` text DEFAULT NULL,
  `attendees` int(11) DEFAULT 1,
  `status` enum('Pending','Approved','Rejected','Cancelled','Waiting List') NOT NULL DEFAULT 'Pending',
  `priority` enum('Regular','VIP') NOT NULL DEFAULT 'Regular',
  `admin_note` varchar(255) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `res_id`, `res_code`, `faculty_id`, `faculty_name`, `room_id`, `room_code`, `room_name`, `res_date`, `time_start`, `time_end`, `purpose`, `attendees`, `status`, `priority`, `admin_note`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(27, NULL, 'RES-001', 3, 'Juan  Reyes', 0, 'GA-309', 'Classroom', '2026-05-08', '10:00:00', '22:00:00', 'Class Lesson', 43, 'Approved', 'Regular', NULL, 1, '2026-05-06 09:00:50', '2026-05-06 09:00:07', '2026-05-06 09:00:50'),
(28, NULL, 'RES-002', 3, 'Juan  Reyes', 0, 'GA-404', 'Classroom', '2026-05-06', '10:00:00', '13:00:00', NULL, 35, 'Rejected', 'Regular', NULL, 1, '2026-05-06 10:23:15', '2026-05-06 10:22:31', '2026-05-06 10:23:15'),
(29, NULL, 'RES-003', 3, 'Juan  Reyes', 0, 'GD-202', 'Classroom', '2026-09-05', '08:00:00', '09:00:00', 'class lesson', 30, 'Rejected', 'Regular', NULL, 1, '2026-05-14 08:39:39', '2026-05-07 12:51:58', '2026-05-14 08:39:39'),
(30, NULL, 'RES-004', 3, 'Juan  Reyes', 0, 'GD-202', 'Classroom', '2026-09-05', '09:00:00', '10:00:00', 'Class Lesson', 30, 'Rejected', 'Regular', NULL, 1, '2026-05-07 12:56:24', '2026-05-07 12:52:40', '2026-05-07 12:56:24'),
(31, NULL, 'RES-005', 3, 'Juan  Reyes', 0, 'GD-202', 'Classroom', '2026-09-05', '10:30:00', '13:00:00', NULL, 30, 'Approved', 'Regular', NULL, 1, '2026-05-07 12:56:20', '2026-05-07 12:53:05', '2026-05-07 12:56:20'),
(32, NULL, 'RES-006', 3, 'Juan  Reyes', 0, 'GA-404', 'Classroom', '2026-10-05', '10:00:00', '11:00:00', NULL, 30, 'Approved', 'Regular', NULL, 1, '2026-05-07 12:56:19', '2026-05-07 12:53:39', '2026-05-07 12:56:19'),
(33, NULL, 'RES-007', 3, 'Juan  Reyes', 0, 'GA-404', 'Classroom', '2026-10-05', '14:00:00', '15:30:00', NULL, 30, 'Rejected', 'Regular', NULL, 1, '2026-05-07 12:56:12', '2026-05-07 12:54:03', '2026-05-07 12:56:12'),
(34, NULL, 'RES-008', 3, 'Juan  Reyes', 0, 'GD-309', 'Classroom', '2026-11-05', '10:30:00', '14:00:00', 'ITPM Class', 30, 'Approved', 'Regular', NULL, 1, '2026-05-07 12:56:08', '2026-05-07 12:54:54', '2026-05-07 12:56:08'),
(35, NULL, 'RES-009', 3, 'Juan  Reyes', 0, 'GA-404', 'Classroom', '2026-05-08', '08:00:00', '09:30:00', NULL, 40, 'Approved', 'Regular', NULL, 1, '2026-05-07 21:19:06', '2026-05-07 21:17:43', '2026-05-07 21:19:06'),
(36, NULL, 'RES-010', 3, 'Juan  Reyes', 0, 'GA-403', 'Classroom', '2026-05-14', '08:00:00', '10:00:00', NULL, 39, 'Approved', 'Regular', NULL, 1, '2026-05-13 07:16:21', '2026-05-13 07:15:39', '2026-05-13 07:16:21'),
(37, NULL, 'RES-011', 2, 'Maria Santos', 0, 'GA-407', 'Classroom', '2026-05-15', '07:00:00', '08:30:00', NULL, 44, 'Rejected', 'Regular', 'Conflict with BSIT-3B Regular Class', 1, '2026-05-13 12:15:59', '2026-05-13 12:15:15', '2026-05-13 12:15:59'),
(38, NULL, 'RES-012', 3, 'Juan  Reyes', 0, 'GA-407', 'Classroom', '2026-05-15', '10:00:00', '11:00:00', NULL, 39, 'Approved', 'Regular', NULL, 1, '2026-05-14 08:37:49', '2026-05-13 12:18:09', '2026-05-14 08:37:49'),
(39, NULL, 'RES-013', 2, 'Maria Santos', 0, 'GA-310', 'Computer Laboratory', '2026-06-01', '08:30:00', '11:00:00', 'Senior Hign Orientation', 50, 'Approved', 'Regular', NULL, 1, '2026-05-14 08:37:59', '2026-05-13 13:00:34', '2026-05-14 08:37:59'),
(40, 'HRES26022001', 'HRES26022001', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-02-20', '07:30:00', '09:00:00', 'Lecture session', 18, 'Rejected', 'Regular', 'alternate room suggested.', 1, '2026-02-18 11:10:00', '2026-02-18 09:10:00', '2026-05-22 06:33:28'),
(41, 'HRES26022002', 'HRES26022002', 3, 'Juan  Reyes', 8, 'GA-309', 'Classroom', '2026-02-20', '09:30:00', '11:00:00', ' Laboratory activity', 25, 'Approved', 'Regular', NULL, 1, '2026-02-17 13:10:00', '2026-02-17 10:10:00', '2026-05-22 06:36:58'),
(42, 'HRES26022301', 'HRES26022301', 3, 'Juan  Reyes', 23, 'GA-407', 'Classroom', '2026-02-23', '13:00:00', '15:00:00', 'Practical demonstration', 27, 'Approved', 'Regular', NULL, 1, '2026-02-18 11:13:00', '2026-02-18 09:13:00', '2026-05-22 06:36:41'),
(43, 'HRES26022401', 'HRES26022401', 2, 'Maria Santos', 33, 'GC-101', 'Classroom', '2026-02-24', '15:15:00', '16:45:00', 'Student consultation', 30, 'Approved', 'Regular', NULL, 1, '2026-02-18 11:14:00', '2026-02-18 09:14:00', '2026-05-22 06:36:26'),
(44, 'HRES26022402', 'HRES26022402', 3, 'Juan  Reyes', 46, 'GC-301', 'Classroom', '2026-02-24', '17:00:00', '18:30:00', 'Capstone review', 37, 'Approved', 'Regular', NULL, 1, '2026-02-22 13:14:00', '2026-02-22 10:14:00', '2026-05-22 06:36:02'),
(45, 'HRES26022501', 'HRES26022501', 3, 'Juan  Reyes', 49, 'GC-306', 'Classroom', '2026-02-25', '17:00:00', '18:30:00', 'Capstone review', 33, 'Approved', 'Regular', NULL, 1, '2026-02-23 11:15:00', '2026-02-23 09:15:00', '2026-05-22 06:35:48'),
(46, 'HRES26022502', 'HRES26022502', 2, 'Maria Santos', 54, 'AVR', 'Classroom', '2026-02-25', '07:30:00', '09:00:00', 'Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-02-22 13:15:00', '2026-02-22 10:15:00', '2026-05-22 06:35:26'),
(47, 'HRES26022601', 'HRES26022601', 2, 'Maria Santos', 58, 'Annex B Lab', 'Science Laboratory', '2026-02-26', '07:30:00', '09:00:00', 'Skills assessment', 36, 'Approved', 'Regular', NULL, 1, '2026-02-23 11:16:00', '2026-02-23 09:16:00', '2026-05-22 06:35:07'),
(48, 'HRES26022602', 'HRES26022602', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-02-26', '09:30:00', '11:00:00', 'Lecture session', 43, 'Approved', 'Regular', NULL, 1, '2026-02-22 13:16:00', '2026-02-22 10:16:00', '2026-05-22 06:34:50'),
(49, 'HRES26022701', 'HRES26022701', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-02-27', '09:30:00', '11:00:00', 'Lecture session', 39, 'Approved', 'Regular', NULL, 1, '2026-02-23 11:17:00', '2026-02-23 09:17:00', '2026-05-22 06:34:33'),
(50, 'HRES26030201', 'HRES26030201', 2, 'Maria Santos', 26, 'GB-201', 'Classroom', '2026-03-02', '15:15:00', '16:45:00', 'Practical demonstration', 48, 'Approved', 'Regular', NULL, 1, '2026-02-28 11:20:00', '2026-02-28 09:20:00', '2026-05-22 06:34:11'),
(51, 'HRES26030301', 'HRES26030301', 3, 'Juan  Reyes', 41, 'GC-202', 'Classroom', '2026-03-03', '17:00:00', '18:30:00', 'Student consultation', 45, 'Approved', 'Regular', NULL, 1, '2026-02-28 11:21:00', '2026-02-28 09:21:00', '2026-05-22 06:33:53'),
(52, 'HRES26030302', 'HRES26030302', 2, 'Maria Santos', 49, 'GC-306', 'Classroom', '2026-03-03', '07:30:00', '09:00:00', 'Historical analytics seed: Capstone review', 50, 'Approved', 'Regular', NULL, 1, '2026-02-27 13:21:00', '2026-02-27 10:21:00', '2026-02-27 13:21:00'),
(53, 'HRES26030401', 'HRES26030401', 2, 'Maria Santos', 54, 'AVR', 'Classroom', '2026-03-04', '07:30:00', '09:00:00', 'Historical analytics seed: Capstone review', 50, 'Approved', 'Regular', NULL, 1, '2026-02-28 11:22:00', '2026-02-28 09:22:00', '2026-02-28 11:22:00'),
(54, 'HRES26030402', 'HRES26030402', 3, 'Juan  Reyes', 58, 'Annex B Lab', 'Science Laboratory', '2026-03-04', '09:30:00', '11:00:00', 'Historical analytics seed: Skills assessment', 21, 'Approved', 'Regular', NULL, 1, '2026-02-27 13:22:00', '2026-02-27 10:22:00', '2026-02-27 13:22:00'),
(55, 'HRES26030501', 'HRES26030501', 3, 'Juan  Reyes', 59, 'GLS C-201', 'Classroom', '2026-03-05', '09:30:00', '11:00:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-02-28 11:23:00', '2026-02-28 09:23:00', '2026-02-28 11:23:00'),
(56, 'HRES26030502', 'HRES26030502', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-03-05', '11:00:00', '12:30:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-02-27 13:23:00', '2026-02-27 10:23:00', '2026-02-27 13:23:00'),
(57, 'HRES26030601', 'HRES26030601', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-03-06', '11:00:00', '12:30:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-02-28 11:24:00', '2026-02-28 09:24:00', '2026-02-28 11:24:00'),
(58, 'HRES26030901', 'HRES26030901', 3, 'Juan  Reyes', 28, 'GB-301', 'Classroom', '2026-03-09', '17:00:00', '18:30:00', 'Historical analytics seed: Practical demonstration', 24, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-03-05 11:27:00', '2026-03-05 09:27:00', '2026-03-05 11:27:00'),
(59, 'HRES26031001', 'HRES26031001', 2, 'Maria Santos', 47, 'GC-302', 'Classroom', '2026-03-10', '07:30:00', '09:00:00', 'Historical analytics seed: Student consultation', 22, 'Approved', 'Regular', NULL, 1, '2026-03-05 11:28:00', '2026-03-05 09:28:00', '2026-03-05 11:28:00'),
(60, 'HRES26031002', 'HRES26031002', 3, 'Juan  Reyes', 54, 'AVR', 'Classroom', '2026-03-10', '09:30:00', '11:00:00', 'Historical analytics seed: Capstone review', 29, 'Approved', 'Regular', NULL, 1, '2026-03-04 13:28:00', '2026-03-04 10:28:00', '2026-03-04 13:28:00'),
(61, 'HRES26031003', 'HRES26031003', 2, 'Maria Santos', 58, 'Annex B Lab', 'Science Laboratory', '2026-03-10', '11:00:00', '12:30:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-03-08 15:28:00', '2026-03-08 11:28:00', '2026-03-08 15:28:00'),
(62, 'HRES26031101', 'HRES26031101', 3, 'Juan  Reyes', 55, 'GC-401', 'Classroom', '2026-03-11', '09:30:00', '11:00:00', 'Historical analytics seed: Capstone review', 30, 'Approved', 'Regular', NULL, 1, '2026-03-05 11:29:00', '2026-03-05 09:29:00', '2026-03-05 11:29:00'),
(63, 'HRES26031102', 'HRES26031102', 2, 'Maria Santos', 59, 'GLS C-201', 'Classroom', '2026-03-11', '11:00:00', '12:30:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-03-09 13:29:00', '2026-03-09 10:29:00', '2026-03-09 13:29:00'),
(64, 'HRES26031201', 'HRES26031201', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-03-12', '11:00:00', '12:30:00', 'Historical analytics seed: Skills assessment', 28, 'Approved', 'Regular', NULL, 1, '2026-03-10 11:30:00', '2026-03-10 09:30:00', '2026-03-10 11:30:00'),
(65, 'HRES26031202', 'HRES26031202', 3, 'Juan  Reyes', 8, 'GA-309', 'Classroom', '2026-03-12', '13:00:00', '15:00:00', 'Historical analytics seed: Lecture session', 35, 'Approved', 'Regular', NULL, 1, '2026-03-09 13:30:00', '2026-03-09 10:30:00', '2026-03-09 13:30:00'),
(66, 'HRES26031301', 'HRES26031301', 3, 'Juan  Reyes', 9, 'GA-401', 'Classroom', '2026-03-13', '13:00:00', '15:00:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-03-10 11:31:00', '2026-03-10 09:31:00', '2026-03-10 11:31:00'),
(67, 'HRES26031401', 'HRES26031401', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-03-14', '15:15:00', '16:45:00', 'Historical analytics seed: Laboratory activity', 40, 'Approved', 'Regular', NULL, 1, '2026-03-10 11:32:00', '2026-03-10 09:32:00', '2026-03-10 11:32:00'),
(68, 'HRES26031601', 'HRES26031601', 2, 'Maria Santos', 34, 'GC-102', 'Classroom', '2026-03-16', '07:30:00', '09:00:00', 'Historical analytics seed: Practical demonstration', 45, 'Approved', 'Regular', NULL, 1, '2026-03-10 11:34:00', '2026-03-10 09:34:00', '2026-03-10 11:34:00'),
(69, 'HRES26031701', 'HRES26031701', 3, 'Juan  Reyes', 50, 'GC-307', 'Classroom', '2026-03-17', '09:30:00', '11:00:00', 'Historical analytics seed: Student consultation', 43, 'Approved', 'Regular', NULL, 1, '2026-03-15 11:35:00', '2026-03-15 09:35:00', '2026-03-15 11:35:00'),
(70, 'HRES26031702', 'HRES26031702', 2, 'Maria Santos', 55, 'GC-401', 'Classroom', '2026-03-17', '11:00:00', '12:30:00', 'Historical analytics seed: Capstone review', 45, 'Approved', 'Regular', NULL, 1, '2026-03-14 13:35:00', '2026-03-14 10:35:00', '2026-03-14 13:35:00'),
(71, 'HRES26031703', 'HRES26031703', 3, 'Juan  Reyes', 59, 'GLS C-201', 'Classroom', '2026-03-17', '13:00:00', '15:00:00', 'Historical analytics seed: Skills assessment', 27, 'Approved', 'Regular', NULL, 1, '2026-03-13 15:35:00', '2026-03-13 11:35:00', '2026-03-13 15:35:00'),
(72, 'HRES26031801', 'HRES26031801', 2, 'Maria Santos', 58, 'Annex B Lab', 'Science Laboratory', '2026-03-18', '11:00:00', '12:30:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-03-15 11:36:00', '2026-03-15 09:36:00', '2026-03-15 11:36:00'),
(73, 'HRES26031802', 'HRES26031802', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-03-18', '13:00:00', '15:00:00', 'Historical analytics seed: Skills assessment', 50, 'Approved', 'Regular', NULL, 1, '2026-03-14 13:36:00', '2026-03-14 10:36:00', '2026-03-14 13:36:00'),
(74, 'HRES26031803', 'HRES26031803', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-03-18', '15:15:00', '16:45:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-03-13 15:36:00', '2026-03-13 11:36:00', '2026-03-13 15:36:00'),
(75, 'HRES26031901', 'HRES26031901', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-03-19', '13:00:00', '15:00:00', 'Historical analytics seed: Skills assessment', 49, 'Approved', 'Regular', NULL, 1, '2026-03-15 11:37:00', '2026-03-15 09:37:00', '2026-03-15 11:37:00'),
(76, 'HRES26031902', 'HRES26031902', 2, 'Maria Santos', 9, 'GA-401', 'Classroom', '2026-03-19', '15:15:00', '16:45:00', 'Historical analytics seed: Lecture session', 26, 'Approved', 'Regular', NULL, 1, '2026-03-14 13:37:00', '2026-03-14 10:37:00', '2026-03-14 13:37:00'),
(77, 'HRES26031903', 'HRES26031903', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-03-19', '17:00:00', '18:30:00', 'Historical analytics seed: Laboratory activity', 33, 'Rejected', 'Regular', NULL, 1, '2026-05-22 06:16:41', '2026-03-13 11:37:00', '2026-05-22 06:16:41'),
(78, 'HRES26031904', 'HRES26031904', 2, 'Maria Santos', 19, 'GD-203', 'Classroom', '2026-03-19', '07:30:00', '09:00:00', 'Historical analytics seed: Department meeting', 20, 'Approved', 'Regular', NULL, 1, '2026-03-17 17:37:00', '2026-03-17 12:37:00', '2026-03-17 17:37:00'),
(79, 'HRES26032001', 'HRES26032001', 2, 'Maria Santos', 11, 'GA-403', 'Classroom', '2026-03-20', '15:15:00', '16:45:00', 'Historical analytics seed: Lecture session', 22, 'Approved', 'Regular', NULL, 1, '2026-03-15 11:38:00', '2026-03-15 09:38:00', '2026-03-15 11:38:00'),
(80, 'HRES26032002', 'HRES26032002', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-03-20', '17:00:00', '18:30:00', 'Historical analytics seed: Laboratory activity', 29, 'Rejected', 'Regular', NULL, 1, '2026-05-22 06:16:35', '2026-03-14 10:38:00', '2026-05-22 06:16:35'),
(81, 'HRES26032301', 'HRES26032301', 3, 'Juan  Reyes', 42, 'GC-207', 'Classroom', '2026-03-23', '09:30:00', '11:00:00', 'Historical analytics seed: Practical demonstration', 21, 'Approved', 'Regular', NULL, 1, '2026-03-20 11:41:00', '2026-03-20 09:41:00', '2026-03-20 11:41:00'),
(82, 'HRES26032302', 'HRES26032302', 2, 'Maria Santos', 50, 'GC-307', 'Classroom', '2026-03-23', '11:00:00', '12:30:00', 'Historical analytics seed: Student consultation', 18, 'Approved', 'Regular', NULL, 1, '2026-03-19 13:41:00', '2026-03-19 10:41:00', '2026-03-19 13:41:00'),
(83, 'HRES26032401', 'HRES26032401', 2, 'Maria Santos', 54, 'AVR', 'Classroom', '2026-03-24', '11:00:00', '12:30:00', 'Historical analytics seed: Student consultation', 50, 'Approved', 'Regular', NULL, 1, '2026-03-20 11:42:00', '2026-03-20 09:42:00', '2026-03-20 11:42:00'),
(84, 'HRES26032402', 'HRES26032402', 3, 'Juan  Reyes', 58, 'Annex B Lab', 'Science Laboratory', '2026-03-24', '13:00:00', '15:00:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-03-19 13:42:00', '2026-03-19 10:42:00', '2026-03-19 13:42:00'),
(85, 'HRES26032403', 'HRES26032403', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-03-24', '15:15:00', '16:45:00', 'Historical analytics seed: Skills assessment', 28, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-03-18 15:42:00', '2026-03-18 11:42:00', '2026-03-18 15:42:00'),
(86, 'HRES26032501', 'HRES26032501', 3, 'Juan  Reyes', 60, 'GLS C-202', 'Classroom', '2026-03-25', '13:00:00', '15:00:00', 'Historical analytics seed: Capstone review', 27, 'Approved', 'Regular', NULL, 1, '2026-03-20 11:43:00', '2026-03-20 09:43:00', '2026-03-20 11:43:00'),
(87, 'HRES26032502', 'HRES26032502', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-03-25', '15:15:00', '16:45:00', 'Historical analytics seed: Skills assessment', 24, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-03-19 13:43:00', '2026-03-19 10:43:00', '2026-03-19 13:43:00'),
(88, 'HRES26032503', 'HRES26032503', 3, 'Juan  Reyes', 9, 'GA-401', 'Classroom', '2026-03-25', '17:00:00', '18:30:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-03-23 15:43:00', '2026-03-23 11:43:00', '2026-03-23 15:43:00'),
(89, 'HRES26032601', 'HRES26032601', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-03-26', '15:15:00', '16:45:00', 'Historical analytics seed: Skills assessment', 20, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-03-20 11:44:00', '2026-03-20 09:44:00', '2026-03-20 11:44:00'),
(90, 'HRES26032602', 'HRES26032602', 3, 'Juan  Reyes', 11, 'GA-403', 'Classroom', '2026-03-26', '17:00:00', '18:30:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-03-24 13:44:00', '2026-03-24 10:44:00', '2026-03-24 13:44:00'),
(91, 'HRES26032603', 'HRES26032603', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-03-26', '07:30:00', '09:00:00', 'Historical analytics seed: Laboratory activity', 40, 'Approved', 'Regular', NULL, 1, '2026-03-23 15:44:00', '2026-03-23 11:44:00', '2026-03-23 15:44:00'),
(92, 'HRES26032701', 'HRES26032701', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-03-27', '17:00:00', '18:30:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-03-25 11:45:00', '2026-03-25 09:45:00', '2026-03-25 11:45:00'),
(93, 'HRES26032702', 'HRES26032702', 2, 'Maria Santos', 19, 'GD-203', 'Classroom', '2026-03-27', '07:30:00', '09:00:00', 'Historical analytics seed: Laboratory activity', 30, 'Approved', 'Regular', NULL, 1, '2026-03-24 13:45:00', '2026-03-24 10:45:00', '2026-03-24 13:45:00'),
(94, 'HRES26033001', 'HRES26033001', 2, 'Maria Santos', 47, 'GC-302', 'Classroom', '2026-03-30', '11:00:00', '12:30:00', 'Historical analytics seed: Practical demonstration', 32, 'Approved', 'Regular', NULL, 1, '2026-03-25 11:48:00', '2026-03-25 09:48:00', '2026-03-25 11:48:00'),
(95, 'HRES26033002', 'HRES26033002', 3, 'Juan  Reyes', 54, 'AVR', 'Classroom', '2026-03-30', '13:00:00', '15:00:00', 'Historical analytics seed: Student consultation', 39, 'Approved', 'Regular', NULL, 1, '2026-03-24 13:48:00', '2026-03-24 10:48:00', '2026-03-24 13:48:00'),
(96, 'HRES26033101', 'HRES26033101', 3, 'Juan  Reyes', 56, 'GLS C-101', 'Classroom', '2026-03-31', '13:00:00', '15:00:00', 'Historical analytics seed: Student consultation', 40, 'Approved', 'Regular', NULL, 1, '2026-03-25 11:49:00', '2026-03-25 09:49:00', '2026-03-25 11:49:00'),
(97, 'HRES26033102', 'HRES26033102', 2, 'Maria Santos', 60, 'GLS C-202', 'Classroom', '2026-03-31', '15:15:00', '16:45:00', 'Historical analytics seed: Capstone review', 45, 'Approved', 'Regular', NULL, 1, '2026-03-29 13:49:00', '2026-03-29 10:49:00', '2026-03-29 13:49:00'),
(98, 'HRES26033103', 'HRES26033103', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-03-31', '17:00:00', '18:30:00', 'Historical analytics seed: Skills assessment', 49, 'Approved', 'Regular', NULL, 1, '2026-03-28 15:49:00', '2026-03-28 11:49:00', '2026-03-28 15:49:00'),
(99, 'HRES26040101', 'HRES26040101', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-04-01', '15:15:00', '16:45:00', 'Historical analytics seed: Capstone review', 38, 'Approved', 'Regular', NULL, 1, '2026-03-30 11:10:00', '2026-03-30 09:10:00', '2026-03-30 11:10:00'),
(100, 'HRES26040102', 'HRES26040102', 3, 'Juan  Reyes', 8, 'GA-309', 'Classroom', '2026-04-01', '17:00:00', '18:30:00', 'Historical analytics seed: Skills assessment', 45, 'Approved', 'Regular', NULL, 1, '2026-03-29 13:10:00', '2026-03-29 10:10:00', '2026-03-29 13:10:00'),
(101, 'HRES26040103', 'HRES26040103', 2, 'Maria Santos', 11, 'GA-403', 'Classroom', '2026-04-01', '07:30:00', '09:00:00', 'Historical analytics seed: Lecture session', 32, 'Approved', 'Regular', NULL, 1, '2026-03-28 15:10:00', '2026-03-28 11:10:00', '2026-03-28 15:10:00'),
(102, 'HRES26040201', 'HRES26040201', 3, 'Juan  Reyes', 9, 'GA-401', 'Classroom', '2026-04-02', '17:00:00', '18:30:00', 'Historical analytics seed: Skills assessment', 21, 'Approved', 'Regular', NULL, 1, '2026-03-30 11:11:00', '2026-03-30 09:11:00', '2026-03-30 11:11:00'),
(103, 'HRES26040202', 'HRES26040202', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-04-02', '07:30:00', '09:00:00', 'Historical analytics seed: Lecture session', 28, 'Approved', 'Regular', NULL, 1, '2026-03-29 13:11:00', '2026-03-29 10:11:00', '2026-03-29 13:11:00'),
(104, 'HRES26040203', 'HRES26040203', 3, 'Juan  Reyes', 19, 'GD-203', 'Classroom', '2026-04-02', '09:30:00', '11:00:00', 'Historical analytics seed: Laboratory activity', 50, 'Approved', 'Regular', NULL, 1, '2026-03-28 15:11:00', '2026-03-28 11:11:00', '2026-03-28 15:11:00'),
(105, 'HRES26040301', 'HRES26040301', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-04-03', '07:30:00', '09:00:00', 'Historical analytics seed: Lecture session', 24, 'Approved', 'Regular', NULL, 1, '2026-03-30 11:12:00', '2026-03-30 09:12:00', '2026-03-30 11:12:00'),
(106, 'HRES26040302', 'HRES26040302', 3, 'Juan  Reyes', 23, 'GA-407', 'Classroom', '2026-04-03', '09:30:00', '11:00:00', 'Historical analytics seed: Laboratory activity', 50, 'Approved', 'Regular', NULL, 1, '2026-03-29 13:12:00', '2026-03-29 10:12:00', '2026-03-29 13:12:00'),
(107, 'HRES26040501', 'HRES26040501', 2, 'Maria Santos', 35, 'GC-103', 'Classroom', '2026-04-05', '11:00:00', '12:30:00', 'Historical analytics seed: Department meeting', 45, 'Approved', 'Regular', NULL, 1, '2026-03-30 11:14:00', '2026-03-30 09:14:00', '2026-03-30 11:14:00'),
(108, 'HRES26040601', 'HRES26040601', 3, 'Juan  Reyes', 53, 'GD-302', 'Classroom', '2026-04-06', '13:00:00', '15:00:00', 'Historical analytics seed: Practical demonstration', 50, 'Approved', 'Regular', NULL, 1, '2026-04-04 11:15:00', '2026-04-04 09:15:00', '2026-04-04 11:15:00'),
(109, 'HRES26040602', 'HRES26040602', 2, 'Maria Santos', 56, 'GLS C-101', 'Classroom', '2026-04-06', '15:15:00', '16:45:00', 'Historical analytics seed: Student consultation', 40, 'Approved', 'Regular', NULL, 1, '2026-04-03 13:15:00', '2026-04-03 10:15:00', '2026-04-03 13:15:00'),
(110, 'HRES26040603', 'HRES26040603', 3, 'Juan  Reyes', 60, 'GLS C-202', 'Classroom', '2026-04-06', '17:00:00', '18:30:00', 'Historical analytics seed: Capstone review', 32, 'Approved', 'Regular', NULL, 1, '2026-04-02 15:15:00', '2026-04-02 11:15:00', '2026-04-02 15:15:00'),
(111, 'HRES26040701', 'HRES26040701', 2, 'Maria Santos', 58, 'Annex B Lab', 'Science Laboratory', '2026-04-07', '15:15:00', '16:45:00', 'Historical analytics seed: Student consultation', 36, 'Approved', 'Regular', NULL, 1, '2026-04-04 11:16:00', '2026-04-04 09:16:00', '2026-04-04 11:16:00'),
(112, 'HRES26040702', 'HRES26040702', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-04-07', '17:00:00', '18:30:00', 'Historical analytics seed: Capstone review', 50, 'Approved', 'Regular', NULL, 1, '2026-04-03 13:16:00', '2026-04-03 10:16:00', '2026-04-03 13:16:00'),
(113, 'HRES26040703', 'HRES26040703', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-04-07', '07:30:00', '09:00:00', 'Historical analytics seed: Skills assessment', 20, 'Approved', 'Regular', NULL, 1, '2026-04-02 15:16:00', '2026-04-02 11:16:00', '2026-04-02 15:16:00'),
(114, 'HRES26040801', 'HRES26040801', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-04-08', '17:00:00', '18:30:00', 'Historical analytics seed: Capstone review', 50, 'Approved', 'Regular', NULL, 1, '2026-04-04 11:17:00', '2026-04-04 09:17:00', '2026-04-04 11:17:00'),
(115, 'HRES26040802', 'HRES26040802', 2, 'Maria Santos', 9, 'GA-401', 'Classroom', '2026-04-08', '07:30:00', '09:00:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-04-03 13:17:00', '2026-04-03 10:17:00', '2026-04-03 13:17:00'),
(116, 'HRES26040803', 'HRES26040803', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-04-08', '09:30:00', '11:00:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-04-02 15:17:00', '2026-04-02 11:17:00', '2026-04-02 15:17:00'),
(117, 'HRES26040901', 'HRES26040901', 2, 'Maria Santos', 11, 'GA-403', 'Classroom', '2026-04-09', '07:30:00', '09:00:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-04-04 11:18:00', '2026-04-04 09:18:00', '2026-04-04 11:18:00'),
(118, 'HRES26040902', 'HRES26040902', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-04-09', '09:30:00', '11:00:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-04-03 13:18:00', '2026-04-03 10:18:00', '2026-04-03 13:18:00'),
(119, 'HRES26040903', 'HRES26040903', 2, 'Maria Santos', 23, 'GA-407', 'Classroom', '2026-04-09', '11:00:00', '12:30:00', 'Historical analytics seed: Laboratory activity', 26, 'Approved', 'Regular', NULL, 1, '2026-04-07 15:18:00', '2026-04-07 11:18:00', '2026-04-07 15:18:00'),
(120, 'HRES26041001', 'HRES26041001', 3, 'Juan  Reyes', 19, 'GD-203', 'Classroom', '2026-04-10', '09:30:00', '11:00:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-04-04 11:19:00', '2026-04-04 09:19:00', '2026-04-04 11:19:00'),
(121, 'HRES26041002', 'HRES26041002', 2, 'Maria Santos', 23, 'GA-407', 'Classroom', '2026-04-10', '11:00:00', '12:30:00', 'Historical analytics seed: Laboratory activity', 22, 'Approved', 'Regular', NULL, 1, '2026-04-08 13:19:00', '2026-04-08 10:19:00', '2026-04-08 13:19:00'),
(122, 'HRES26041301', 'HRES26041301', 2, 'Maria Santos', 54, 'AVR', 'Classroom', '2026-04-13', '15:15:00', '16:45:00', 'Historical analytics seed: Practical demonstration', 24, 'Approved', 'Regular', NULL, 1, '2026-04-09 11:22:00', '2026-04-09 09:22:00', '2026-04-09 11:22:00'),
(123, 'HRES26041302', 'HRES26041302', 3, 'Juan  Reyes', 58, 'Annex B Lab', 'Science Laboratory', '2026-04-13', '17:00:00', '18:30:00', 'Historical analytics seed: Student consultation', 21, 'Approved', 'Regular', NULL, 1, '2026-04-08 13:22:00', '2026-04-08 10:22:00', '2026-04-08 13:22:00'),
(124, 'HRES26041401', 'HRES26041401', 3, 'Juan  Reyes', 61, 'GLS C-203', 'Classroom', '2026-04-14', '17:00:00', '18:30:00', 'Historical analytics seed: Student consultation', 27, 'Approved', 'Regular', NULL, 1, '2026-04-09 11:23:00', '2026-04-09 09:23:00', '2026-04-09 11:23:00'),
(125, 'HRES26041402', 'HRES26041402', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-04-14', '07:30:00', '09:00:00', 'Historical analytics seed: Capstone review', 34, 'Approved', 'Regular', NULL, 1, '2026-04-08 13:23:00', '2026-04-08 10:23:00', '2026-04-08 13:23:00'),
(126, 'HRES26041403', 'HRES26041403', 3, 'Juan  Reyes', 9, 'GA-401', 'Classroom', '2026-04-14', '09:30:00', '11:00:00', 'Historical analytics seed: Skills assessment', 31, 'Approved', 'Regular', NULL, 1, '2026-04-12 15:23:00', '2026-04-12 11:23:00', '2026-04-12 15:23:00'),
(127, 'HRES26041501', 'HRES26041501', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-04-15', '07:30:00', '09:00:00', 'Historical analytics seed: Capstone review', 30, 'Approved', 'Regular', NULL, 1, '2026-04-09 11:24:00', '2026-04-09 09:24:00', '2026-04-09 11:24:00'),
(128, 'HRES26041502', 'HRES26041502', 3, 'Juan  Reyes', 11, 'GA-403', 'Classroom', '2026-04-15', '09:30:00', '11:00:00', 'Historical analytics seed: Skills assessment', 27, 'Approved', 'Regular', NULL, 1, '2026-04-13 13:24:00', '2026-04-13 10:24:00', '2026-04-13 13:24:00'),
(129, 'HRES26041503', 'HRES26041503', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-04-15', '11:00:00', '12:30:00', 'Historical analytics seed: Lecture session', 34, 'Approved', 'Regular', NULL, 1, '2026-04-12 15:24:00', '2026-04-12 11:24:00', '2026-04-12 15:24:00'),
(130, 'HRES26041504', 'HRES26041504', 3, 'Juan  Reyes', 23, 'GA-407', 'Classroom', '2026-04-15', '13:00:00', '15:00:00', 'Historical analytics seed: Laboratory activity', 50, 'Approved', 'Regular', NULL, 1, '2026-04-11 17:24:00', '2026-04-11 12:24:00', '2026-04-11 17:24:00'),
(131, 'HRES26041601', 'HRES26041601', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-04-16', '09:30:00', '11:00:00', 'Historical analytics seed: Skills assessment', 23, 'Approved', 'Regular', NULL, 1, '2026-04-14 11:25:00', '2026-04-14 09:25:00', '2026-04-14 11:25:00'),
(132, 'HRES26041602', 'HRES26041602', 2, 'Maria Santos', 19, 'GD-203', 'Classroom', '2026-04-16', '11:00:00', '12:30:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-04-13 13:25:00', '2026-04-13 10:25:00', '2026-04-13 13:25:00'),
(133, 'HRES26041603', 'HRES26041603', 3, 'Juan  Reyes', 23, 'GA-407', 'Classroom', '2026-04-16', '13:00:00', '15:00:00', 'Historical analytics seed: Laboratory activity', 47, 'Approved', 'Regular', NULL, 1, '2026-04-12 15:25:00', '2026-04-12 11:25:00', '2026-04-12 15:25:00'),
(134, 'HRES26041604', 'HRES26041604', 2, 'Maria Santos', 29, 'GB-302', 'Classroom', '2026-04-16', '15:15:00', '16:45:00', 'Historical analytics seed: Department meeting', 50, 'Approved', 'Regular', NULL, 1, '2026-05-22 06:16:26', '2026-04-11 12:25:00', '2026-05-22 06:16:26'),
(135, 'HRES26041701', 'HRES26041701', 2, 'Maria Santos', 23, 'GA-407', 'Classroom', '2026-04-17', '11:00:00', '12:30:00', 'Historical analytics seed: Lecture session', 36, 'Approved', 'Regular', NULL, 1, '2026-04-14 11:26:00', '2026-04-14 09:26:00', '2026-04-14 11:26:00'),
(136, 'HRES26041702', 'HRES26041702', 3, 'Juan  Reyes', 27, 'GB-202', 'Classroom', '2026-04-17', '13:00:00', '15:00:00', 'Historical analytics seed: Laboratory activity', 45, 'Approved', 'Regular', NULL, 1, '2026-04-13 13:26:00', '2026-04-13 10:26:00', '2026-04-13 13:26:00'),
(137, 'HRES26041703', 'HRES26041703', 2, 'Maria Santos', 35, 'GC-103', 'Classroom', '2026-04-17', '15:15:00', '16:45:00', 'Historical analytics seed: Department meeting', 20, 'Approved', 'Regular', NULL, 1, '2026-05-22 06:16:21', '2026-04-12 11:26:00', '2026-05-22 06:16:21'),
(138, 'HRES26042001', 'HRES26042001', 3, 'Juan  Reyes', 57, 'GLS C-102', 'Classroom', '2026-04-20', '17:00:00', '18:30:00', 'Historical analytics seed: Practical demonstration', 35, 'Approved', 'Regular', NULL, 1, '2026-04-14 11:29:00', '2026-04-14 09:29:00', '2026-04-14 11:29:00'),
(139, 'HRES26042002', 'HRES26042002', 2, 'Maria Santos', 61, 'GLS C-203', 'Classroom', '2026-04-20', '07:30:00', '09:00:00', 'Historical analytics seed: Student consultation', 50, 'Approved', 'Regular', NULL, 1, '2026-04-18 13:29:00', '2026-04-18 10:29:00', '2026-04-18 13:29:00'),
(140, 'HRES26042003', 'HRES26042003', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-04-20', '09:30:00', '11:00:00', 'Historical analytics seed: Capstone review', 50, 'Approved', 'Regular', NULL, 1, '2026-04-17 15:29:00', '2026-04-17 11:29:00', '2026-04-17 15:29:00'),
(141, 'HRES26042101', 'HRES26042101', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-04-21', '07:30:00', '09:00:00', 'Historical analytics seed: Student consultation', 48, 'Approved', 'Regular', NULL, 1, '2026-04-19 11:30:00', '2026-04-19 09:30:00', '2026-04-19 11:30:00'),
(142, 'HRES26042102', 'HRES26042102', 3, 'Juan  Reyes', 8, 'GA-309', 'Classroom', '2026-04-21', '09:30:00', '11:00:00', 'Historical analytics seed: Capstone review', 50, 'Approved', 'Regular', NULL, 1, '2026-04-18 13:30:00', '2026-04-18 10:30:00', '2026-04-18 13:30:00'),
(143, 'HRES26042103', 'HRES26042103', 2, 'Maria Santos', 11, 'GA-403', 'Classroom', '2026-04-21', '11:00:00', '12:30:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-04-17 15:30:00', '2026-04-17 11:30:00', '2026-04-17 15:30:00'),
(144, 'HRES26042104', 'HRES26042104', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-04-21', '13:00:00', '15:00:00', 'Historical analytics seed: Lecture session', 19, 'Approved', 'Regular', NULL, 1, '2026-04-16 17:30:00', '2026-04-16 12:30:00', '2026-04-16 17:30:00'),
(145, 'HRES26042201', 'HRES26042201', 3, 'Juan  Reyes', 9, 'GA-401', 'Classroom', '2026-04-22', '09:30:00', '11:00:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-04-19 11:31:00', '2026-04-19 09:31:00', '2026-04-19 11:31:00'),
(146, 'HRES26042202', 'HRES26042202', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-04-22', '11:00:00', '12:30:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-04-18 13:31:00', '2026-04-18 10:31:00', '2026-04-18 13:31:00'),
(147, 'HRES26042203', 'HRES26042203', 3, 'Juan  Reyes', 19, 'GD-203', 'Classroom', '2026-04-22', '13:00:00', '15:00:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-04-17 15:31:00', '2026-04-17 11:31:00', '2026-04-17 15:31:00'),
(148, 'HRES26042204', 'HRES26042204', 2, 'Maria Santos', 23, 'GA-407', 'Classroom', '2026-04-22', '15:15:00', '16:45:00', 'Historical analytics seed: Laboratory activity', 22, 'Approved', 'Regular', NULL, 1, '2026-04-16 17:31:00', '2026-04-16 12:31:00', '2026-04-16 17:31:00'),
(149, 'HRES26042301', 'HRES26042301', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-04-23', '11:00:00', '12:30:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-04-19 11:32:00', '2026-04-19 09:32:00', '2026-04-19 11:32:00'),
(150, 'HRES26042302', 'HRES26042302', 3, 'Juan  Reyes', 23, 'GA-407', 'Classroom', '2026-04-23', '13:00:00', '15:00:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-04-18 13:32:00', '2026-04-18 10:32:00', '2026-04-18 13:32:00'),
(151, 'HRES26042303', 'HRES26042303', 2, 'Maria Santos', 27, 'GB-202', 'Classroom', '2026-04-23', '15:15:00', '16:45:00', 'Historical analytics seed: Laboratory activity', 38, 'Approved', 'Regular', NULL, 1, '2026-04-17 15:32:00', '2026-04-17 11:32:00', '2026-04-17 15:32:00'),
(152, 'HRES26042304', 'HRES26042304', 3, 'Juan  Reyes', 35, 'GC-103', 'Classroom', '2026-04-23', '17:00:00', '18:30:00', 'Historical analytics seed: Department meeting', 45, 'Approved', 'Regular', NULL, 1, '2026-04-21 17:32:00', '2026-04-21 12:32:00', '2026-04-21 17:32:00'),
(153, 'HRES26042401', 'HRES26042401', 3, 'Juan  Reyes', 24, 'GA-408', 'Classroom', '2026-04-24', '13:00:00', '15:00:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-04-19 11:33:00', '2026-04-19 09:33:00', '2026-04-19 11:33:00'),
(154, 'HRES26042402', 'HRES26042402', 2, 'Maria Santos', 30, 'GB-303', 'Classroom', '2026-04-24', '15:15:00', '16:45:00', 'Historical analytics seed: Laboratory activity', 50, 'Approved', 'Regular', NULL, 1, '2026-04-18 13:33:00', '2026-04-18 10:33:00', '2026-04-18 13:33:00'),
(155, 'HRES26042403', 'HRES26042403', 3, 'Juan  Reyes', 46, 'GC-301', 'Classroom', '2026-04-24', '17:00:00', '18:30:00', 'Historical analytics seed: Department meeting', 21, 'Approved', 'Regular', NULL, 1, '2026-04-22 15:33:00', '2026-04-22 11:33:00', '2026-04-22 15:33:00'),
(156, 'HRES26042404', 'HRES26042404', 2, 'Maria Santos', 53, 'GD-302', 'Classroom', '2026-04-24', '07:30:00', '09:00:00', 'Historical analytics seed: Practical demonstration', 28, 'Approved', 'Regular', NULL, 1, '2026-04-21 17:33:00', '2026-04-21 12:33:00', '2026-04-21 17:33:00'),
(157, 'HRES26042701', 'HRES26042701', 2, 'Maria Santos', 59, 'GLS C-201', 'Classroom', '2026-04-27', '07:30:00', '09:00:00', 'Historical analytics seed: Practical demonstration', 40, 'Approved', 'Regular', NULL, 1, '2026-04-24 11:36:00', '2026-04-24 09:36:00', '2026-04-24 11:36:00'),
(158, 'HRES26042702', 'HRES26042702', 3, 'Juan  Reyes', 7, 'GA-310', 'Computer Laboratory', '2026-04-27', '09:30:00', '11:00:00', 'Historical analytics seed: Student consultation', 23, 'Approved', 'Regular', NULL, 1, '2026-04-23 13:36:00', '2026-04-23 10:36:00', '2026-04-23 13:36:00'),
(159, 'HRES26042703', 'HRES26042703', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-04-27', '11:00:00', '12:30:00', 'Historical analytics seed: Capstone review', 30, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-04-22 15:36:00', '2026-04-22 11:36:00', '2026-04-22 15:36:00'),
(160, 'HRES26042801', 'HRES26042801', 3, 'Juan  Reyes', 8, 'GA-309', 'Classroom', '2026-04-28', '09:30:00', '11:00:00', 'Historical analytics seed: Student consultation', 19, 'Approved', 'Regular', NULL, 1, '2026-04-24 11:37:00', '2026-04-24 09:37:00', '2026-04-24 11:37:00'),
(161, 'HRES26042802', 'HRES26042802', 2, 'Maria Santos', 9, 'GA-401', 'Classroom', '2026-04-28', '11:00:00', '12:30:00', 'Historical analytics seed: Capstone review', 26, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-04-23 13:37:00', '2026-04-23 10:37:00', '2026-04-23 13:37:00'),
(162, 'HRES26042803', 'HRES26042803', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-04-28', '13:00:00', '15:00:00', 'Historical analytics seed: Skills assessment', 33, 'Approved', 'Regular', NULL, 1, '2026-04-22 15:37:00', '2026-04-22 11:37:00', '2026-04-22 15:37:00'),
(163, 'HRES26042804', 'HRES26042804', 2, 'Maria Santos', 19, 'GD-203', 'Classroom', '2026-04-28', '15:15:00', '16:45:00', 'Historical analytics seed: Lecture session', 40, 'Approved', 'Regular', NULL, 1, '2026-04-26 17:37:00', '2026-04-26 12:37:00', '2026-04-26 17:37:00'),
(164, 'HRES26042901', 'HRES26042901', 2, 'Maria Santos', 11, 'GA-403', 'Classroom', '2026-04-29', '11:00:00', '12:30:00', 'Historical analytics seed: Capstone review', 22, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-04-24 11:38:00', '2026-04-24 09:38:00', '2026-04-24 11:38:00'),
(165, 'HRES26042902', 'HRES26042902', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-04-29', '13:00:00', '15:00:00', 'Historical analytics seed: Skills assessment', 29, 'Approved', 'Regular', NULL, 1, '2026-04-23 13:38:00', '2026-04-23 10:38:00', '2026-04-23 13:38:00'),
(166, 'HRES26042903', 'HRES26042903', 2, 'Maria Santos', 23, 'GA-407', 'Classroom', '2026-04-29', '15:15:00', '16:45:00', 'Historical analytics seed: Lecture session', 36, 'Approved', 'Regular', NULL, 1, '2026-04-27 15:38:00', '2026-04-27 11:38:00', '2026-04-27 15:38:00'),
(167, 'HRES26042904', 'HRES26042904', 3, 'Juan  Reyes', 27, 'GB-202', 'Classroom', '2026-04-29', '17:00:00', '18:30:00', 'Historical analytics seed: Laboratory activity', 18, 'Approved', 'Regular', NULL, 1, '2026-04-26 17:38:00', '2026-04-26 12:38:00', '2026-04-26 17:38:00'),
(168, 'HRES26043001', 'HRES26043001', 3, 'Juan  Reyes', 20, 'GD-309', 'Classroom', '2026-04-30', '13:00:00', '15:00:00', 'Historical analytics seed: Skills assessment', 25, 'Approved', 'Regular', NULL, 1, '2026-04-24 11:39:00', '2026-04-24 09:39:00', '2026-04-24 11:39:00'),
(169, 'HRES26043002', 'HRES26043002', 2, 'Maria Santos', 24, 'GA-408', 'Classroom', '2026-04-30', '15:15:00', '16:45:00', 'Historical analytics seed: Lecture session', 32, 'Approved', 'Regular', NULL, 1, '2026-04-28 13:39:00', '2026-04-28 10:39:00', '2026-04-28 13:39:00'),
(170, 'HRES26043003', 'HRES26043003', 3, 'Juan  Reyes', 30, 'GB-303', 'Classroom', '2026-04-30', '17:00:00', '18:30:00', 'Historical analytics seed: Laboratory activity', 39, 'Approved', 'Regular', NULL, 1, '2026-04-27 15:39:00', '2026-04-27 11:39:00', '2026-04-27 15:39:00'),
(171, 'HRES26043004', 'HRES26043004', 2, 'Maria Santos', 46, 'GC-301', 'Classroom', '2026-04-30', '07:30:00', '09:00:00', 'Historical analytics seed: Department meeting', 46, 'Approved', 'Regular', NULL, 1, '2026-04-26 17:39:00', '2026-04-26 12:39:00', '2026-04-26 17:39:00'),
(172, 'HRES26050101', 'HRES26050101', 2, 'Maria Santos', 28, 'GB-301', 'Classroom', '2026-05-01', '15:15:00', '16:45:00', 'Historical analytics seed: Lecture session', 45, 'Approved', 'Regular', NULL, 1, '2026-04-29 11:40:00', '2026-04-29 09:40:00', '2026-04-29 11:40:00'),
(173, 'HRES26050102', 'HRES26050102', 3, 'Juan  Reyes', 36, 'GC-106', 'Classroom', '2026-05-01', '17:00:00', '18:30:00', 'Historical analytics seed: Laboratory activity', 45, 'Approved', 'Regular', NULL, 1, '2026-04-28 13:40:00', '2026-04-28 10:40:00', '2026-04-28 13:40:00'),
(174, 'HRES26050103', 'HRES26050103', 2, 'Maria Santos', 47, 'GC-302', 'Classroom', '2026-05-01', '07:30:00', '09:00:00', 'Historical analytics seed: Department meeting', 42, 'Approved', 'Regular', NULL, 1, '2026-04-27 15:40:00', '2026-04-27 11:40:00', '2026-04-27 15:40:00'),
(175, 'HRES26050401', 'HRES26050401', 3, 'Juan  Reyes', 62, 'GLS B-201', 'Classroom', '2026-05-04', '09:30:00', '11:00:00', 'Historical analytics seed: Practical demonstration', 37, 'Approved', 'Regular', NULL, 1, '2026-04-29 11:43:00', '2026-04-29 09:43:00', '2026-04-29 11:43:00'),
(176, 'HRES26050402', 'HRES26050402', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-05-04', '11:00:00', '12:30:00', 'Historical analytics seed: Student consultation', 44, 'Approved', 'Regular', NULL, 1, '2026-04-28 13:43:00', '2026-04-28 10:43:00', '2026-04-28 13:43:00'),
(177, 'HRES26050403', 'HRES26050403', 3, 'Juan  Reyes', 9, 'GA-401', 'Classroom', '2026-05-04', '13:00:00', '15:00:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-05-02 15:43:00', '2026-05-02 11:43:00', '2026-05-02 15:43:00'),
(178, 'HRES26050501', 'HRES26050501', 2, 'Maria Santos', 8, 'GA-309', 'Classroom', '2026-05-05', '11:00:00', '12:30:00', 'Historical analytics seed: Student consultation', 40, 'Approved', 'Regular', NULL, 1, '2026-04-29 11:44:00', '2026-04-29 09:44:00', '2026-04-29 11:44:00'),
(179, 'HRES26050502', 'HRES26050502', 3, 'Juan  Reyes', 11, 'GA-403', 'Classroom', '2026-05-05', '13:00:00', '15:00:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-05-03 13:44:00', '2026-05-03 10:44:00', '2026-05-03 13:44:00'),
(180, 'HRES26050503', 'HRES26050503', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-05-05', '15:15:00', '16:45:00', 'Historical analytics seed: Skills assessment', 40, 'Approved', 'Regular', NULL, 1, '2026-05-02 15:44:00', '2026-05-02 11:44:00', '2026-05-02 15:44:00'),
(181, 'HRES26050504', 'HRES26050504', 3, 'Juan  Reyes', 23, 'GA-407', 'Classroom', '2026-05-05', '17:00:00', '18:30:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-05-01 17:44:00', '2026-05-01 12:44:00', '2026-05-01 17:44:00'),
(182, 'HRES26050601', 'HRES26050601', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-05-06', '13:00:00', '15:00:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-05-04 11:45:00', '2026-05-04 09:45:00', '2026-05-04 11:45:00'),
(183, 'HRES26050602', 'HRES26050602', 2, 'Maria Santos', 20, 'GD-309', 'Classroom', '2026-05-06', '15:15:00', '16:45:00', 'Historical analytics seed: Skills assessment', 50, 'Approved', 'Regular', NULL, 1, '2026-05-03 13:45:00', '2026-05-03 10:45:00', '2026-05-03 13:45:00'),
(184, 'HRES26050603', 'HRES26050603', 3, 'Juan  Reyes', 24, 'GA-408', 'Classroom', '2026-05-06', '17:00:00', '18:30:00', 'Historical analytics seed: Lecture session', 50, 'Approved', 'Regular', NULL, 1, '2026-05-02 15:45:00', '2026-05-02 11:45:00', '2026-05-02 15:45:00'),
(185, 'HRES26050604', 'HRES26050604', 2, 'Maria Santos', 30, 'GB-303', 'Classroom', '2026-05-06', '07:30:00', '09:00:00', 'Historical analytics seed: Laboratory activity', 50, 'Approved', 'Regular', NULL, 1, '2026-05-01 17:45:00', '2026-05-01 12:45:00', '2026-05-01 17:45:00'),
(186, 'HRES26050701', 'HRES26050701', 2, 'Maria Santos', 23, 'GA-407', 'Classroom', '2026-05-07', '15:15:00', '16:45:00', 'Historical analytics seed: Skills assessment', 46, 'Approved', 'Regular', NULL, 1, '2026-05-04 11:46:00', '2026-05-04 09:46:00', '2026-05-04 11:46:00'),
(187, 'HRES26050702', 'HRES26050702', 3, 'Juan  Reyes', 28, 'GB-301', 'Classroom', '2026-05-07', '17:00:00', '18:30:00', 'Historical analytics seed: Lecture session', 28, 'Approved', 'Regular', NULL, 1, '2026-05-03 13:46:00', '2026-05-03 10:46:00', '2026-05-03 13:46:00'),
(188, 'HRES26050703', 'HRES26050703', 2, 'Maria Santos', 36, 'GC-106', 'Classroom', '2026-05-07', '07:30:00', '09:00:00', 'Historical analytics seed: Laboratory activity', 35, 'Approved', 'Regular', NULL, 1, '2026-05-02 15:46:00', '2026-05-02 11:46:00', '2026-05-02 15:46:00'),
(189, 'HRES26050704', 'HRES26050704', 3, 'Juan  Reyes', 47, 'GC-302', 'Classroom', '2026-05-07', '09:30:00', '11:00:00', 'Historical analytics seed: Department meeting', 50, 'Approved', 'Regular', NULL, 1, '2026-05-01 17:46:00', '2026-05-01 12:46:00', '2026-05-01 17:46:00'),
(190, 'HRES26050801', 'HRES26050801', 3, 'Juan  Reyes', 31, 'GB-401', 'Classroom', '2026-05-08', '17:00:00', '18:30:00', 'Historical analytics seed: Lecture session', 49, 'Approved', 'Regular', NULL, 1, '2026-05-04 11:47:00', '2026-05-04 09:47:00', '2026-05-04 11:47:00'),
(191, 'HRES26050802', 'HRES26050802', 2, 'Maria Santos', 46, 'GC-301', 'Classroom', '2026-05-08', '07:30:00', '09:00:00', 'Historical analytics seed: Laboratory activity', 50, 'Approved', 'Regular', NULL, 1, '2026-05-03 13:47:00', '2026-05-03 10:47:00', '2026-05-03 13:47:00'),
(192, 'HRES26050803', 'HRES26050803', 3, 'Juan  Reyes', 54, 'AVR', 'Classroom', '2026-05-08', '09:30:00', '11:00:00', 'Historical analytics seed: Department meeting', 50, 'Approved', 'Regular', NULL, 1, '2026-05-02 15:47:00', '2026-05-02 11:47:00', '2026-05-02 15:47:00'),
(193, 'HRES26051101', 'HRES26051101', 2, 'Maria Santos', 7, 'GA-310', 'Computer Laboratory', '2026-05-11', '11:00:00', '12:30:00', 'Historical analytics seed: Practical demonstration', 50, 'Approved', 'Regular', NULL, 1, '2026-05-09 11:10:00', '2026-05-09 09:10:00', '2026-05-09 11:10:00'),
(194, 'HRES26051102', 'HRES26051102', 3, 'Juan  Reyes', 8, 'GA-309', 'Classroom', '2026-05-11', '13:00:00', '15:00:00', 'Historical analytics seed: Student consultation', 50, 'Approved', 'Regular', NULL, 1, '2026-05-08 13:10:00', '2026-05-08 10:10:00', '2026-05-08 13:10:00'),
(195, 'HRES26051103', 'HRES26051103', 2, 'Maria Santos', 11, 'GA-403', 'Classroom', '2026-05-11', '15:15:00', '16:45:00', 'Historical analytics seed: Capstone review', 32, 'Approved', 'Regular', NULL, 1, '2026-05-07 15:10:00', '2026-05-07 11:10:00', '2026-05-07 15:10:00'),
(196, 'HRES26051201', 'HRES26051201', 3, 'Juan  Reyes', 9, 'GA-401', 'Classroom', '2026-05-12', '13:00:00', '15:00:00', 'Historical analytics seed: Student consultation', 21, 'Approved', 'Regular', NULL, 1, '2026-05-09 11:11:00', '2026-05-09 09:11:00', '2026-05-09 11:11:00'),
(197, 'HRES26051202', 'HRES26051202', 2, 'Maria Santos', 17, 'GD-201', 'Computer Laboratory', '2026-05-12', '15:15:00', '16:45:00', 'Historical analytics seed: Capstone review', 28, 'Approved', 'Regular', NULL, 1, '2026-05-08 13:11:00', '2026-05-08 10:11:00', '2026-05-08 13:11:00'),
(198, 'HRES26051203', 'HRES26051203', 3, 'Juan  Reyes', 20, 'GD-309', 'Classroom', '2026-05-12', '17:00:00', '18:30:00', 'Historical analytics seed: Skills assessment', 25, 'Approved', 'Regular', NULL, 1, '2026-05-07 15:11:00', '2026-05-07 11:11:00', '2026-05-07 15:11:00'),
(199, 'HRES26051204', 'HRES26051204', 2, 'Maria Santos', 24, 'GA-408', 'Classroom', '2026-05-12', '07:30:00', '09:00:00', 'Historical analytics seed: Lecture session', 32, 'Approved', 'Regular', NULL, 1, '2026-05-06 17:11:00', '2026-05-06 12:11:00', '2026-05-06 17:11:00'),
(200, 'HRES26051205', 'HRES26051205', 3, 'Juan  Reyes', 30, 'GB-303', 'Classroom', '2026-05-12', '09:30:00', '11:00:00', 'Historical analytics seed: Laboratory activity', 39, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-05-10 15:11:00', '2026-05-10 09:11:00', '2026-05-10 15:11:00'),
(201, 'HRES26051301', 'HRES26051301', 2, 'Maria Santos', 18, 'GD-202', 'Classroom', '2026-05-13', '15:15:00', '16:45:00', 'Historical analytics seed: Capstone review', 24, 'Approved', 'Regular', NULL, 1, '2026-05-09 11:12:00', '2026-05-09 09:12:00', '2026-05-09 11:12:00'),
(202, 'HRES26051302', 'HRES26051302', 3, 'Juan  Reyes', 23, 'GA-407', 'Classroom', '2026-05-13', '17:00:00', '18:30:00', 'Historical analytics seed: Skills assessment', 21, 'Approved', 'Regular', NULL, 1, '2026-05-08 13:12:00', '2026-05-08 10:12:00', '2026-05-08 13:12:00'),
(203, 'HRES26051303', 'HRES26051303', 2, 'Maria Santos', 28, 'GB-301', 'Classroom', '2026-05-13', '07:30:00', '09:00:00', 'Historical analytics seed: Lecture session', 45, 'Approved', 'Regular', NULL, 1, '2026-05-07 15:12:00', '2026-05-07 11:12:00', '2026-05-07 15:12:00'),
(204, 'HRES26051304', 'HRES26051304', 3, 'Juan  Reyes', 36, 'GC-106', 'Classroom', '2026-05-13', '09:30:00', '11:00:00', 'Historical analytics seed: Laboratory activity', 45, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-05-11 17:12:00', '2026-05-11 12:12:00', '2026-05-11 17:12:00'),
(205, 'HRES26051401', 'HRES26051401', 3, 'Juan  Reyes', 26, 'GB-201', 'Classroom', '2026-05-14', '17:00:00', '18:30:00', 'Historical analytics seed: Skills assessment', 50, 'Approved', 'Regular', NULL, 1, '2026-05-09 11:13:00', '2026-05-09 09:13:00', '2026-05-09 11:13:00'),
(206, 'HRES26051402', 'HRES26051402', 2, 'Maria Santos', 31, 'GB-401', 'Classroom', '2026-05-14', '07:30:00', '09:00:00', 'Historical analytics seed: Lecture session', 24, 'Approved', 'Regular', NULL, 1, '2026-05-08 13:13:00', '2026-05-08 10:13:00', '2026-05-08 13:13:00'),
(207, 'HRES26051403', 'HRES26051403', 3, 'Juan  Reyes', 46, 'GC-301', 'Classroom', '2026-05-14', '09:30:00', '11:00:00', 'Historical analytics seed: Laboratory activity', 31, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-05-12 15:13:00', '2026-05-12 11:13:00', '2026-05-12 15:13:00');
INSERT INTO `reservations` (`id`, `res_id`, `res_code`, `faculty_id`, `faculty_name`, `room_id`, `room_code`, `room_name`, `res_date`, `time_start`, `time_end`, `purpose`, `attendees`, `status`, `priority`, `admin_note`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(208, 'HRES26051404', 'HRES26051404', 2, 'Maria Santos', 54, 'AVR', 'Classroom', '2026-05-14', '11:00:00', '12:30:00', 'Historical analytics seed: Department meeting', 38, 'Approved', 'Regular', NULL, 1, '2026-05-11 17:13:00', '2026-05-11 12:13:00', '2026-05-11 17:13:00'),
(209, 'HRES26051501', 'HRES26051501', 2, 'Maria Santos', 37, 'GC-107', 'Classroom', '2026-05-15', '07:30:00', '09:00:00', 'Historical analytics seed: Lecture session', 45, 'Approved', 'Regular', NULL, 1, '2026-05-09 11:14:00', '2026-05-09 09:14:00', '2026-05-09 11:14:00'),
(210, 'HRES26051502', 'HRES26051502', 3, 'Juan  Reyes', 47, 'GC-302', 'Classroom', '2026-05-15', '09:30:00', '11:00:00', 'Historical analytics seed: Laboratory activity', 27, 'Rejected', 'Regular', 'Historical test record: alternate room suggested.', 1, '2026-05-13 13:14:00', '2026-05-13 10:14:00', '2026-05-13 13:14:00'),
(211, 'HRES26051503', 'HRES26051503', 2, 'Maria Santos', 54, 'AVR', 'Classroom', '2026-05-15', '11:00:00', '12:30:00', 'Historical analytics seed: Department meeting', 34, 'Approved', 'Regular', NULL, 1, '2026-05-12 15:14:00', '2026-05-12 11:14:00', '2026-05-12 15:14:00'),
(212, 'HRES26051801', 'HRES26051801', 3, 'Juan  Reyes', 8, 'GA-309', 'Classroom', '2026-05-18', '13:00:00', '15:00:00', 'Historical analytics seed: Practical demonstration', 29, 'Approved', 'Regular', NULL, 1, '2026-05-22 06:16:13', '2026-05-14 09:17:00', '2026-05-22 06:16:13'),
(213, 'HRES26051802', 'HRES26051802', 2, 'Maria Santos', 9, 'GA-401', 'Classroom', '2026-05-18', '15:15:00', '16:45:00', 'Historical analytics seed: Student consultation', 40, 'Approved', 'Regular', NULL, 1, '2026-05-13 13:17:00', '2026-05-13 10:17:00', '2026-05-13 13:17:00'),
(214, 'HRES26051803', 'HRES26051803', 3, 'Juan  Reyes', 17, 'GD-201', 'Computer Laboratory', '2026-05-18', '17:00:00', '18:30:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-05-12 15:17:00', '2026-05-12 11:17:00', '2026-05-12 15:17:00'),
(215, 'HRES26051901', 'HRES26051901', 2, 'Maria Santos', 12, 'GA-404', 'Classroom', '2026-05-19', '15:15:00', '16:45:00', 'Historical analytics seed: Student consultation', 40, 'Approved', 'Regular', NULL, 1, '2026-05-14 11:18:00', '2026-05-14 09:18:00', '2026-05-14 11:18:00'),
(216, 'HRES26051902', 'HRES26051902', 3, 'Juan  Reyes', 18, 'GD-202', 'Classroom', '2026-05-19', '17:00:00', '18:30:00', 'Historical analytics seed: Capstone review', 40, 'Approved', 'Regular', NULL, 1, '2026-05-13 13:18:00', '2026-05-13 10:18:00', '2026-05-13 13:18:00'),
(217, 'HRES26051903', 'HRES26051903', 2, 'Maria Santos', 23, 'GA-407', 'Classroom', '2026-05-19', '07:30:00', '09:00:00', 'Historical analytics seed: Skills assessment', 46, 'Approved', 'Regular', NULL, 1, '2026-05-17 15:18:00', '2026-05-17 11:18:00', '2026-05-17 15:18:00'),
(218, 'HRES26051904', 'HRES26051904', 3, 'Juan  Reyes', 28, 'GB-301', 'Classroom', '2026-05-19', '09:30:00', '11:00:00', 'Historical analytics seed: Lecture session', 33, 'Approved', 'Regular', NULL, 1, '2026-05-16 17:18:00', '2026-05-16 12:18:00', '2026-05-16 17:18:00'),
(219, NULL, 'RES-26051905', 3, 'Juan  Reyes', 0, 'GLS C-101', 'Classroom', '2026-05-23', '09:00:00', '11:00:00', NULL, 40, 'Rejected', 'Regular', NULL, 1, '2026-10-22 12:17:31', '2026-05-22 06:27:43', '2026-10-22 12:17:31'),
(220, NULL, 'RES-26051906', 3, 'Juan  Reyes', 0, 'GA-403', 'Classroom', '2026-05-23', '10:00:00', '12:00:00', NULL, 1, 'Rejected', 'Regular', NULL, 1, '2026-10-22 12:17:28', '2026-05-22 20:53:03', '2026-10-22 12:17:28'),
(221, NULL, 'RES-221', 3, 'Juan  Reyes', 12, 'GA-404', 'Classroom', '2026-10-22', '08:30:00', '12:00:00', NULL, 40, 'Rejected', 'Regular', 'expired reservation', 1, '2026-10-22 12:17:18', '2026-10-22 12:16:09', '2026-10-22 12:17:18'),
(222, NULL, 'RES-222', 3, 'Juan  Reyes', 28, 'GB-301', 'Classroom', '2026-10-22', '13:00:00', '15:00:00', NULL, 40, 'Waiting List', 'Regular', 'Moved to Waiting List: an overlapping VIP priority request was approved by the admin.', 1, '2026-10-22 13:01:06', '2026-10-22 12:18:45', '2026-10-22 13:01:06'),
(223, NULL, 'RES-223', 6, 'Mercurio G. Villanueva', 27, 'GB-202', 'Classroom', '2026-10-22', '13:00:00', '15:00:00', NULL, 45, 'Approved', 'VIP', NULL, 1, '2026-10-22 12:58:15', '2026-10-22 12:57:13', '2026-10-22 12:58:15'),
(224, NULL, 'RES-224', 6, 'Mercurio G. Villanueva', 28, 'GB-301', 'Classroom', '2026-10-22', '13:00:00', '15:00:00', NULL, 40, 'Approved', 'VIP', NULL, 1, '2026-10-22 13:01:06', '2026-10-22 13:00:31', '2026-10-22 13:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_id` varchar(20) DEFAULT NULL,
  `room_code` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `type` enum('Classroom','Laboratory','Conference Room','Lecture Hall','Office','Comfort Room','Canteen') NOT NULL DEFAULT 'Classroom',
  `capacity` int(11) NOT NULL DEFAULT 1,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('Available','Occupied','Under Maintenance') NOT NULL DEFAULT 'Available',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `qr_code_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_id`, `room_code`, `room_name`, `type`, `capacity`, `location`, `status`, `created_at`, `updated_at`, `qr_code_path`) VALUES
(7, NULL, 'GA-310', 'Computer Laboratory', 'Laboratory', 50, 'Building A, 3rd Floor', 'Available', '2026-05-06 08:26:15', '2026-10-22 06:30:32', 'qrcodes/room-7-3e34c8da1daca8a4d36b.png'),
(8, NULL, 'GA-309', 'Classroom', 'Classroom', 50, 'Building A, 3rd Floor', 'Available', '2026-05-06 08:35:51', '2026-10-22 06:30:32', 'qrcodes/room-8-0f6b5130c6d9e2a10b95.png'),
(9, NULL, 'GA-401', 'Classroom', 'Classroom', 40, 'Building A, 4th Floor', 'Available', '2026-05-06 08:36:28', '2026-10-22 06:30:32', 'qrcodes/room-9-f3ee69a88eb50f7ebda4.png'),
(10, NULL, 'GA-402', 'Classroom', 'Classroom', 40, 'Building A, 4th Floor', 'Available', '2026-05-06 08:36:43', '2026-10-22 06:30:32', 'qrcodes/room-10-24972c3d86710c811de0.png'),
(11, NULL, 'GA-403', 'Classroom', 'Classroom', 40, 'Building A, 4th Floor', 'Available', '2026-05-06 08:36:55', '2026-10-22 06:30:32', 'qrcodes/room-11-c881675ac8938c15ee38.png'),
(12, NULL, 'GA-404', 'Classroom', 'Classroom', 40, 'Building A, 4th Floor', 'Available', '2026-05-06 08:37:08', '2026-10-22 06:30:32', 'qrcodes/room-12-5ba9c0049d6ee803d42f.png'),
(13, NULL, 'GD-101', 'Classroom', 'Classroom', 50, 'Building D, 1st Floor', 'Available', '2026-05-06 08:37:50', '2026-10-22 06:30:33', 'qrcodes/room-13-c4c8ed0e004b52920400.png'),
(14, NULL, 'GD-102', 'Classroom', 'Classroom', 50, 'Building D, 1st Floor', 'Under Maintenance', '2026-05-06 08:38:03', '2026-10-22 06:30:33', 'qrcodes/room-14-9cc14fa5fb7ddaf84193.png'),
(15, NULL, 'GD-210', 'Criminology Department', 'Office', 15, 'Building D, 2nd Floor', 'Occupied', '2026-05-06 08:39:15', '2026-10-22 06:30:33', 'qrcodes/room-15-c05a11d834924c88d2ea.png'),
(16, NULL, 'GC-203', 'Classroom', 'Office', 45, 'Building C, 2nd Floor', 'Available', '2026-05-06 08:40:12', '2026-10-22 06:30:33', 'qrcodes/room-16-ceffb4cf66d98a96bd71.png'),
(17, NULL, 'GD-201', 'Computer Laboratory', 'Laboratory', 40, 'Building D, 2nd Floor', 'Available', '2026-05-07 10:52:36', '2026-10-22 06:30:33', 'qrcodes/room-17-44f4f33d0c1a0186f69f.png'),
(18, NULL, 'GD-202', 'Classroom', 'Classroom', 40, 'Building D, 2nd Floor', 'Available', '2026-05-07 10:53:11', '2026-10-22 06:30:33', 'qrcodes/room-18-b57b03ac03a1d3273226.png'),
(19, NULL, 'GD-203', 'Classroom', 'Classroom', 50, 'Building D, 2nd Floor', 'Available', '2026-05-07 10:54:31', '2026-10-22 06:30:33', 'qrcodes/room-19-f504198e226ba1a99a7c.png'),
(20, NULL, 'GD-309', 'Classroom', 'Classroom', 50, 'Building D, 3rd Floor', 'Available', '2026-05-07 11:04:43', '2026-10-22 06:30:34', 'qrcodes/room-20-a65e320488db6fa1ffef.png'),
(21, NULL, 'GC-309', 'Classroom', 'Classroom', 50, 'Building C, 3rd Floor', 'Available', '2026-05-07 11:05:06', '2026-10-22 06:30:33', 'qrcodes/room-21-6fedaff11064b801d76a.png'),
(22, NULL, 'GA-303', 'General Science Room', 'Laboratory', 15, 'Building A, 3rd Floor', 'Under Maintenance', '2026-05-13 09:35:18', '2026-10-22 06:30:32', 'qrcodes/room-22-f6bd5112b5b5ca3f56b1.png'),
(23, NULL, 'GA-407', 'Classroom', 'Classroom', 50, 'Building A, 4th Floor', 'Available', '2026-05-13 09:38:08', '2026-10-22 06:30:32', 'qrcodes/room-23-75547c29c70a503c6c94.png'),
(24, NULL, 'GA-408', 'Classroom', 'Classroom', 50, 'Building A, 4th Floor', 'Available', '2026-05-13 09:38:27', '2026-10-22 06:30:32', 'qrcodes/room-24-3112e59e834c8691adcd.png'),
(25, NULL, 'Bldg B Canteen', 'School Canteen', 'Canteen', 75, 'Building B, 1st Floor', 'Occupied', '2026-05-13 09:40:06', '2026-10-22 06:30:32', 'qrcodes/room-25-f7587eef4d84b273c29a.png'),
(26, NULL, 'GB-201', 'Classroom', 'Classroom', 50, 'Building B, 2nd Floor', 'Available', '2026-05-13 09:40:47', '2026-10-22 06:30:32', 'qrcodes/room-26-ce3686e8d1223e73d617.png'),
(27, NULL, 'GB-202', 'Classroom', 'Classroom', 45, 'Building B, 2nd Floor', 'Available', '2026-05-13 09:42:26', '2026-10-22 06:30:32', 'qrcodes/room-27-7da00b0137ae729ed9e8.png'),
(28, NULL, 'GB-301', 'Classroom', 'Classroom', 45, 'Building B, 3rd Floor', 'Available', '2026-05-13 09:42:51', '2026-10-22 06:30:32', 'qrcodes/room-28-95234328dd36d5f8890d.png'),
(29, NULL, 'GB-302', 'Classroom', 'Classroom', 50, 'Building B, 3rd Floor', 'Available', '2026-05-13 09:43:04', '2026-10-22 06:30:33', 'qrcodes/room-29-fe96d63046ccb507e87a.png'),
(30, NULL, 'GB-303', 'Classroom', 'Classroom', 50, 'Building B, 3rd Floor', 'Available', '2026-05-13 09:43:15', '2026-10-22 06:30:33', 'qrcodes/room-30-7ce516420e636733f305.png'),
(31, NULL, 'GB-401', 'Classroom', 'Classroom', 50, 'Building B, 4th Floor', 'Available', '2026-05-13 09:44:31', '2026-10-22 06:30:33', 'qrcodes/room-31-a0d4e1afb70313d4d58c.png'),
(32, NULL, 'GB-402', 'Classroom', 'Classroom', 45, 'Building B, 4th Floor', 'Available', '2026-05-13 09:44:52', '2026-10-22 06:30:33', 'qrcodes/room-32-076f980f6b397caf8cb7.png'),
(33, NULL, 'GC-101', 'Classroom', 'Classroom', 45, 'Building C, 1st Floor', 'Available', '2026-05-13 09:45:35', '2026-10-22 06:30:33', 'qrcodes/room-33-5f9e407ca3667ade3006.png'),
(34, NULL, 'GC-102', 'Classroom', 'Classroom', 45, 'Building C, 1st Floor', 'Available', '2026-05-13 09:46:03', '2026-10-22 06:30:33', 'qrcodes/room-34-addbeb88e4ae2b9530d3.png'),
(35, NULL, 'GC-103', 'Classroom', 'Classroom', 45, 'Building C, 1st Floor', 'Available', '2026-05-13 09:46:23', '2026-10-22 06:30:33', 'qrcodes/room-35-327eebb485283ea2fa81.png'),
(36, NULL, 'GC-106', 'Classroom', 'Classroom', 45, 'Building C, 1st Floor', 'Available', '2026-05-13 09:46:45', '2026-10-22 06:30:33', 'qrcodes/room-36-155efd1ca98151854aea.png'),
(37, NULL, 'GC-107', 'Classroom', 'Classroom', 45, 'Building C, 1st Floor', 'Available', '2026-05-13 09:46:55', '2026-10-22 06:30:33', 'qrcodes/room-37-d8f67dd3104f8bc5642e.png'),
(38, NULL, 'GC-104', 'Female', 'Comfort Room', 1, 'Building C, 1st Floor', 'Occupied', '2026-05-13 09:54:01', '2026-10-22 06:30:33', 'qrcodes/room-38-f81b5b7734ed5fd97057.png'),
(39, NULL, 'GC-105', 'Male', 'Comfort Room', 1, 'Building C, 1st Floor', 'Occupied', '2026-05-13 09:54:19', '2026-10-22 06:30:33', 'qrcodes/room-39-12abc65587a0c04d9f65.png'),
(40, NULL, 'GC-201', 'Classroom', 'Classroom', 40, 'Building C, 2nd Floor', 'Available', '2026-05-13 10:00:15', '2026-10-22 06:30:33', 'qrcodes/room-40-e42e54c09127eeb1afed.png'),
(41, NULL, 'GC-202', 'Classroom', 'Classroom', 45, 'Building C, 2nd Floor', 'Available', '2026-05-13 10:00:39', '2026-10-22 06:30:33', 'qrcodes/room-41-ca3061f79e03b088276f.png'),
(42, NULL, 'GC-207', 'Classroom', 'Classroom', 45, 'Building C, 2nd Floor', 'Available', '2026-05-13 10:01:06', '2026-10-22 06:30:33', 'qrcodes/room-42-ce0647552c741c5f13f5.png'),
(43, NULL, 'GC-204', 'Female', 'Comfort Room', 1, 'Building C, 2nd Floor', 'Occupied', '2026-05-13 10:02:21', '2026-10-22 06:30:33', 'qrcodes/room-43-aecb7cc4fb925a09afa8.png'),
(44, NULL, 'GC-205', 'Male', 'Comfort Room', 1, 'Building C, 2nd Floor', 'Occupied', '2026-05-13 10:03:34', '2026-10-22 06:30:33', 'qrcodes/room-44-eaf484f36180fca54da2.png'),
(45, NULL, 'GC-206', 'Classroom', 'Classroom', 50, 'Building C, 2nd Floor', 'Under Maintenance', '2026-05-13 10:04:19', '2026-10-22 06:30:33', 'qrcodes/room-45-2d54b7e2248c1edb33ff.png'),
(46, NULL, 'GC-301', 'Classroom', 'Classroom', 50, 'Building C, 3rd Floor', 'Available', '2026-05-13 10:05:18', '2026-10-22 06:30:33', 'qrcodes/room-46-e01465cb03265d1da6b0.png'),
(47, NULL, 'GC-302', 'Classroom', 'Classroom', 50, 'Building C, 3rd Floor', 'Available', '2026-05-13 10:05:37', '2026-10-22 06:30:33', 'qrcodes/room-47-60ab4d7fac74b23740b4.png'),
(48, NULL, 'GC-303', 'Classroom', 'Classroom', 50, 'Building C, 3rd Floor', 'Available', '2026-05-13 10:05:49', '2026-10-22 06:30:33', 'qrcodes/room-48-6102a2686848e548c5ff.png'),
(49, NULL, 'GC-306', 'Classroom', 'Classroom', 50, 'Building C, 3rd Floor', 'Available', '2026-05-13 10:06:12', '2026-10-22 06:30:33', 'qrcodes/room-49-ee787160626710c5e76b.png'),
(50, NULL, 'GC-307', 'Classroom', 'Classroom', 50, 'Building C, 3rd Floor', 'Available', '2026-05-13 10:06:27', '2026-10-22 06:30:33', 'qrcodes/room-50-c9d60b1b34a10d185d2c.png'),
(51, NULL, 'GC-304', 'Female', 'Comfort Room', 1, 'Building C, 3rd Floor', 'Occupied', '2026-05-13 10:08:25', '2026-10-22 06:30:33', 'qrcodes/room-51-c2932d9982da8787656d.png'),
(52, NULL, 'GC-305', 'Male', 'Comfort Room', 1, 'Building C, 3rd Floor', 'Under Maintenance', '2026-05-13 10:08:42', '2026-10-22 06:30:33', 'qrcodes/room-52-7eb33679e10dd24707f9.png'),
(53, NULL, 'GD-302', 'Classroom', 'Classroom', 50, 'Building D, 3rd Floor', 'Available', '2026-05-13 11:52:29', '2026-10-22 06:30:34', 'qrcodes/room-53-5afe59c83f5b46f73d4c.png'),
(54, NULL, 'AVR', 'Classroom', 'Classroom', 50, 'Annex B, Building A', 'Available', '2026-05-13 11:53:48', '2026-10-22 06:30:32', 'qrcodes/room-54-fd7b72e0eb9d2cf5a14a.png'),
(55, NULL, 'GC-401', 'Classroom', 'Classroom', 45, 'Building C, 4th Floor', 'Available', '2026-05-13 12:21:44', '2026-10-22 06:30:33', 'qrcodes/room-55-68a3bef18af5ef7e8a8c.png'),
(56, NULL, 'GLS C-101', 'Classroom', 'Classroom', 40, 'Annex B, Building C, 1st Floor', 'Available', '2026-05-13 12:33:01', '2026-10-22 06:30:34', 'qrcodes/room-56-5fc2524c6e4294b95a4c.png'),
(57, NULL, 'GLS C-102', 'Classroom', 'Classroom', 40, 'Annex B, Building C, 1st Floor', 'Available', '2026-05-13 12:33:09', '2026-10-22 06:30:34', 'qrcodes/room-57-3d78b9f8c5aac0bf95ce.png'),
(58, NULL, 'Annex B Lab', 'Science Laboratory', 'Laboratory', 40, 'Annex B, Building C, 1st Floor', 'Available', '2026-05-13 12:33:53', '2026-10-22 06:30:32', 'qrcodes/room-58-f9ee7b31f1f53b94a467.png'),
(59, NULL, 'GLS C-201', 'Classroom', 'Classroom', 40, 'Annex B, Building C, 2nd Floor', 'Available', '2026-05-13 12:34:29', '2026-10-22 06:30:34', 'qrcodes/room-59-72e5581063f0788d8f15.png'),
(60, NULL, 'GLS C-202', 'Classroom', 'Classroom', 45, 'Annex B, Building C, 2nd Floor', 'Available', '2026-05-13 12:34:41', '2026-10-22 06:30:34', 'qrcodes/room-60-526e08f4f6b9f509fd47.png'),
(61, NULL, 'GLS C-203', 'Classroom', 'Classroom', 50, 'Annex B, Building C, 2nd Floor', 'Available', '2026-05-13 12:34:53', '2026-10-22 06:30:34', 'qrcodes/room-61-944ce76750e854b06431.png'),
(62, NULL, 'GLS B-201', 'Classroom', 'Classroom', 50, 'Annex B, Building B, 2nd Floor', 'Available', '2026-05-13 12:35:29', '2026-10-22 06:30:34', 'qrcodes/room-62-397b81d0a99f52fc6eef.png'),
(63, NULL, 'GLS B-202', 'Classroom', 'Classroom', 50, 'Annex B, Building B, 2nd Floor', 'Available', '2026-05-13 12:35:37', '2026-10-22 06:30:34', 'qrcodes/room-63-08609a0c5b742cd2d9f8.png'),
(64, NULL, 'Annex B Canteen', 'Canteen', 'Canteen', 75, 'Annex B, Building B, 1st Floor', 'Occupied', '2026-05-13 12:36:21', '2026-10-22 06:30:32', 'qrcodes/room-64-0a96e0f169a933765dab.png');

-- --------------------------------------------------------

--
-- Table structure for table `school_sections`
--

CREATE TABLE `school_sections` (
  `id` int(11) NOT NULL,
  `section_name` varchar(80) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_sections`
--

INSERT INTO `school_sections` (`id`, `section_name`, `created_at`, `updated_at`) VALUES
(1, 'BSIT-3B', '2026-05-13 18:51:46', '2026-05-13 18:51:46'),
(5, 'BSIT-2D', '2026-05-13 18:52:40', '2026-05-13 18:52:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','faculty','maintenance','vip') NOT NULL DEFAULT 'faculty',
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$KuvE.GUSeQpaWdxVtlTW7.Dk.CMi4OjxFMiWOn7MZmZGkymUNqnsu', 'Julie Pearl Oripaypay', 'admin', 'admin@gcst.edu.ph', 1, '2026-04-20 17:42:34', '2026-04-22 19:34:59'),
(2, 'prof.santos', '$2y$10$4SbW5JNoVPOU9NOogZ3eA.vlxP2vD7gX7G/4N4bQZXgyd2ADi2SKe', 'Maria Santos', 'faculty', 'santos@gcst.edu.ph', 1, '2026-04-20 17:42:34', '2026-05-06 08:23:24'),
(3, 'prof.reyes', '$2y$10$1vVll7ntt57AEypqdgjti.LEeBBe/PuztkX6vtmf4FSRCaGRdETmG', 'Juan  Reyes', 'faculty', 'reyes@gcst.edu.ph', 1, '2026-04-20 17:42:34', '2026-04-22 19:35:47'),
(4, 'maint.staff', '$2y$10$T8FAYfI/LSZ0kboLaPMcE.Sv8KFoBAe.da4bEvAZ8ik.bH29cvriO', 'Carlos Mendoza', 'maintenance', 'mendoza@gcst.edu.ph', 1, '2026-04-20 17:42:34', '2026-04-20 17:51:51'),
(5, 'Marian', '$2y$10$g59YGPsJe/2K3NnpKppX2OJckO.Lfri.WX.hi9OF9Qm8czEX8lHJi', 'Marian Lobrino', 'maintenance', 'marianlobrino@gmail.com', 1, '2026-05-22 06:09:20', '2026-05-22 06:09:20'),
(6, 'Dr.Villanueva', '$2y$10$CfWxXDTFf1FhOcv3TRq2tezZtHX2X1KHq9N26terWK.KhvqnX/mfe', 'Mercurio G. Villanueva', 'vip', 'Dr.Villanueva@gmail.com', 1, '2026-10-22 12:53:47', '2026-10-22 12:55:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_an_user` (`user_id`),
  ADD KEY `idx_an_read` (`is_read`),
  ADD KEY `idx_an_created` (`created_at`);

--
-- Indexes for table `borrowed`
--
ALTER TABLE `borrowed`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_borrowed_equipment` (`equipment_id`),
  ADD KEY `fk_borrowed_registered_borrower` (`student_faculty_id`),
  ADD KEY `idx_borrowed_due_status` (`status`,`due_date`);

--
-- Indexes for table `borrowers`
--
ALTER TABLE `borrowers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_faculty_id` (`student_faculty_id`);

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_lookup` (`room_code`,`day_of_week`,`start_time`,`end_time`),
  ADD KEY `idx_schedule_section` (`year_section`);

--
-- Indexes for table `condition_reports`
--
ALTER TABLE `condition_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_code` (`report_code`),
  ADD KEY `idx_equipment_id` (`equipment_id`),
  ADD KEY `reported_by` (`reported_by`);

--
-- Indexes for table `damage_assessments`
--
ALTER TABLE `damage_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_damage_loan` (`borrowed_id`),
  ADD KEY `fk_damage_equipment` (`equipment_id`),
  ADD KEY `fk_damage_report` (`condition_report_id`);

--
-- Indexes for table `disposal_requests`
--
ALTER TABLE `disposal_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_disposal_assessment` (`assessment_id`),
  ADD KEY `idx_disposal_status` (`disposal_status`,`equipment_id`),
  ADD KEY `fk_disposal_equipment` (`equipment_id`),
  ADD KEY `fk_disposal_loan` (`borrowed_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equip_code` (`equip_code`),
  ADD UNIQUE KEY `qr_string` (`qr_string`),
  ADD KEY `idx_equip_code` (`equip_code`),
  ADD KEY `idx_qr_string` (`qr_string`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `equipment_predictions`
--
ALTER TABLE `equipment_predictions`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `faculty_notifications`
--
ALTER TABLE `faculty_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fn_user` (`user_id`),
  ADD KEY `idx_fn_read` (`is_read`),
  ADD KEY `idx_fn_created` (`created_at`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location_name` (`location_name`);

--
-- Indexes for table `maintenance_notifications`
--
ALTER TABLE `maintenance_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mn_user` (`user_id`),
  ADD KEY `idx_mn_read` (`is_read`),
  ADD KEY `idx_mn_created` (`created_at`);

--
-- Indexes for table `overdue_sms_log`
--
ALTER TABLE `overdue_sms_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_overdue_sms_day` (`borrowed_id`,`notification_date`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `res_code` (`res_code`),
  ADD UNIQUE KEY `idx_res_id` (`res_id`),
  ADD KEY `idx_res_code` (`res_code`),
  ADD KEY `idx_faculty_id` (`faculty_id`),
  ADD KEY `idx_room_id` (`room_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_res_date` (`res_date`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`),
  ADD UNIQUE KEY `idx_room_id` (`room_id`),
  ADD KEY `idx_room_code` (`room_code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `school_sections`
--
ALTER TABLE `school_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_name` (`section_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=547;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `borrowed`
--
ALTER TABLE `borrowed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `borrowers`
--
ALTER TABLE `borrowers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `class_schedules`
--
ALTER TABLE `class_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `condition_reports`
--
ALTER TABLE `condition_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `damage_assessments`
--
ALTER TABLE `damage_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `disposal_requests`
--
ALTER TABLE `disposal_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `faculty_notifications`
--
ALTER TABLE `faculty_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `maintenance_notifications`
--
ALTER TABLE `maintenance_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `overdue_sms_log`
--
ALTER TABLE `overdue_sms_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `school_sections`
--
ALTER TABLE `school_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowed`
--
ALTER TABLE `borrowed`
  ADD CONSTRAINT `fk_borrowed_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_borrowed_registered_borrower` FOREIGN KEY (`student_faculty_id`) REFERENCES `borrowers` (`student_faculty_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `condition_reports`
--
ALTER TABLE `condition_reports`
  ADD CONSTRAINT `condition_reports_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `condition_reports_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `damage_assessments`
--
ALTER TABLE `damage_assessments`
  ADD CONSTRAINT `fk_damage_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `fk_damage_loan` FOREIGN KEY (`borrowed_id`) REFERENCES `borrowed` (`id`),
  ADD CONSTRAINT `fk_damage_report` FOREIGN KEY (`condition_report_id`) REFERENCES `condition_reports` (`id`);

--
-- Constraints for table `disposal_requests`
--
ALTER TABLE `disposal_requests`
  ADD CONSTRAINT `fk_disposal_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `damage_assessments` (`id`),
  ADD CONSTRAINT `fk_disposal_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `fk_disposal_loan` FOREIGN KEY (`borrowed_id`) REFERENCES `borrowed` (`id`);

--
-- Constraints for table `faculty_notifications`
--
ALTER TABLE `faculty_notifications`
  ADD CONSTRAINT `fk_fn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_notifications`
--
ALTER TABLE `maintenance_notifications`
  ADD CONSTRAINT `fk_mn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `overdue_sms_log`
--
ALTER TABLE `overdue_sms_log`
  ADD CONSTRAINT `fk_overdue_sms_borrowed` FOREIGN KEY (`borrowed_id`) REFERENCES `borrowed` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
