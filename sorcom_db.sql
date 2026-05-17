-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 03:43 PM
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
-- Database: `sorcom_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cdl_customized_mix_list`
--

CREATE TABLE `cdl_customized_mix_list` (
  `id` int(11) NOT NULL,
  `cdl_vb_concrete_psi` varchar(20) DEFAULT NULL,
  `cdl_vb_cubic_meter` varchar(20) DEFAULT NULL,
  `cdl_vb_curation_days` varchar(50) DEFAULT NULL,
  `cdl_vb_concrete_type` varchar(50) DEFAULT NULL,
  `cdl_vb_concrete_slump` varchar(20) DEFAULT NULL,
  `cdl_vb_wc_ratio` varchar(20) DEFAULT NULL,
  `cdl_vb_cement_source` varchar(100) DEFAULT NULL,
  `cdl_vb_admix_source` varchar(100) DEFAULT NULL,
  `cdl_vb_cement_mix` varchar(20) DEFAULT NULL,
  `cdl_vb_fly_ash` varchar(20) DEFAULT NULL,
  `cdl_vb_sand` varchar(20) DEFAULT NULL,
  `cdl_vb_three_eight` varchar(20) DEFAULT NULL,
  `cdl_vb_three_fourth` varchar(20) DEFAULT NULL,
  `cdl_vb_G1` varchar(20) DEFAULT NULL,
  `cdl_vb_admix` varchar(20) DEFAULT NULL,
  `cdl_vb_water` varchar(20) DEFAULT NULL,
  `cdl_vb_air` varchar(20) DEFAULT NULL,
  `cdl_vb_others` varchar(20) DEFAULT NULL,
  `cdl_vb_total` varchar(20) DEFAULT NULL,
  `cdl_concrete_price` varchar(20) DEFAULT NULL,
  `cdl_ssd_cement_mix` varchar(20) DEFAULT NULL,
  `cdl_ssd_fly_ash` varchar(20) DEFAULT NULL,
  `cdl_ssd_sand` varchar(20) DEFAULT NULL,
  `cdl_ssd_three_eight` varchar(20) DEFAULT NULL,
  `cdl_ssd_three_fourth` varchar(20) DEFAULT NULL,
  `cdl_ssd_G1` varchar(20) DEFAULT NULL,
  `cdl_ssd_admix` varchar(20) DEFAULT NULL,
  `cdl_ssd_water` varchar(20) DEFAULT NULL,
  `cdl_ssd_air` varchar(20) DEFAULT NULL,
  `cdl_ssd_others` varchar(20) DEFAULT NULL,
  `cdl_client_name` varchar(100) DEFAULT NULL,
  `cdl_client_contact` varchar(20) DEFAULT NULL,
  `cdl_remarks` text DEFAULT NULL,
  `cdl_is_active` tinyint(1) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cdl_customized_mix_list`
--

INSERT INTO `cdl_customized_mix_list` (`id`, `cdl_vb_concrete_psi`, `cdl_vb_cubic_meter`, `cdl_vb_curation_days`, `cdl_vb_concrete_type`, `cdl_vb_concrete_slump`, `cdl_vb_wc_ratio`, `cdl_vb_cement_source`, `cdl_vb_admix_source`, `cdl_vb_cement_mix`, `cdl_vb_fly_ash`, `cdl_vb_sand`, `cdl_vb_three_eight`, `cdl_vb_three_fourth`, `cdl_vb_G1`, `cdl_vb_admix`, `cdl_vb_water`, `cdl_vb_air`, `cdl_vb_others`, `cdl_vb_total`, `cdl_concrete_price`, `cdl_ssd_cement_mix`, `cdl_ssd_fly_ash`, `cdl_ssd_sand`, `cdl_ssd_three_eight`, `cdl_ssd_three_fourth`, `cdl_ssd_G1`, `cdl_ssd_admix`, `cdl_ssd_water`, `cdl_ssd_air`, `cdl_ssd_others`, `cdl_client_name`, `cdl_client_contact`, `cdl_remarks`, `cdl_is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '2000 psi', '3/4', '7', 'ORDINARY', '4\" - 6\"', '0.68', 'Portland', 'Proem', '13.00', '0.00', '46.40', '0.00', '47.75', '0.00', '0.13', '8.90', '0.00', '0.00', '2444.7', '', '200.00', '0.00', '958.00', '0.00', '992.00', '0.00', '2.00', '180.00', '0.00', '0.00', 'John Villaluna', '0999 999 9999', NULL, 1, 'admin', '2026-03-04', '2026-03-04'),
(6, '3200', '4/8', '3', 'EXPERIMENT', '234', '123', 'Portland', 'Proem', '123', '123', '123', '123', '123', '123', '123', '323', '', '123', '', '1234', '123', '123', '123', '123', '123', '', '123', '123', '2', '', 'Juan Luna Dela Cruz', '0945 555 ', NULL, 1, 'admin', '2026-04-14', '2026-04-20'),
(8, '2500', '3/4', '5', 'Ordinary', '6\"-8\"', '5\"4', 'landport', 'preom', '123', '', '123', '', '123', '', '123', '12', '', '', '', '22000', '123', '', '123', '', '123', '', '123', '12', '', '', 'Johnny Doe', '0912345678', NULL, 0, '070314', '2026-04-15', '2026-05-08'),
(9, '1500', '4/8', '7', 'Ordinary', '6\"-8\"', '54', 'Portland', 'Proem', '456', '', '456', '', '456', '', '456', '45', '', '', '', '14000', '456', '', '456', '', '456', '', '456', '45', '', '', 'Gio Campo', '0912345678', NULL, 0, '070314', '2026-04-15', '2026-05-08');

-- --------------------------------------------------------

--
-- Table structure for table `concrete_designs`
--

CREATE TABLE `concrete_designs` (
  `id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `concrete_psi` varchar(50) NOT NULL,
  `curration_type` varchar(100) NOT NULL,
  `concrete_type` varchar(100) NOT NULL,
  `cubic_meter` varchar(20) DEFAULT NULL,
  `concrete_slump` varchar(50) DEFAULT NULL,
  `wc_ratio` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `concrete_designs`
--

INSERT INTO `concrete_designs` (`id`, `date_created`, `concrete_psi`, `curration_type`, `concrete_type`, `cubic_meter`, `concrete_slump`, `wc_ratio`) VALUES
(1, '2025-10-29 10:04:52', '1500 psi', '7 Days', 'Ordinary', '3 / 4', '4', 0.68);

-- --------------------------------------------------------

--
-- Table structure for table `concrete_volume_batch`
--

CREATE TABLE `concrete_volume_batch` (
  `design_id` int(11) NOT NULL,
  `cement` decimal(10,2) DEFAULT NULL,
  `fly_ash` decimal(10,2) DEFAULT NULL,
  `sand` decimal(10,2) DEFAULT NULL,
  `three_eighth` decimal(10,2) DEFAULT NULL,
  `three_quarter` decimal(10,2) DEFAULT NULL,
  `g1` decimal(10,2) DEFAULT NULL,
  `admix` decimal(10,2) DEFAULT NULL,
  `water` decimal(10,2) DEFAULT NULL,
  `air` decimal(10,2) DEFAULT NULL,
  `others` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (`cement` + `fly_ash` + `sand` + `three_eighth` + `three_quarter` + `g1` + `admix` + `water` + `air` + `others`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scd_ssd_list`
--

CREATE TABLE `scd_ssd_list` (
  `ssd_id` int(11) NOT NULL,
  `ssd_cement_mix` decimal(7,2) NOT NULL,
  `ssd_fly_ash` decimal(7,2) DEFAULT NULL,
  `ssd_sand` decimal(7,2) NOT NULL,
  `ssd_three_eight` decimal(7,2) DEFAULT NULL,
  `ssd_three_fourth` decimal(7,2) NOT NULL,
  `ssd_G1` decimal(7,2) DEFAULT NULL,
  `ssd_admix` decimal(7,2) NOT NULL,
  `ssd_water` decimal(7,2) NOT NULL,
  `ssd_air` decimal(7,3) DEFAULT NULL,
  `ssd_others` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `scd_ssd_list`
--

INSERT INTO `scd_ssd_list` (`ssd_id`, `ssd_cement_mix`, `ssd_fly_ash`, `ssd_sand`, `ssd_three_eight`, `ssd_three_fourth`, `ssd_G1`, `ssd_admix`, `ssd_water`, `ssd_air`, `ssd_others`) VALUES
(1, 260.00, 0.00, 928.00, 0.00, 955.00, 0.00, 2.60, 178.00, 0.000, ''),
(2, 240.00, NULL, 951.00, NULL, 965.00, NULL, 2.40, 182.00, NULL, ''),
(3, 200.00, NULL, 958.00, NULL, 992.00, NULL, 2.00, 180.00, NULL, ''),
(4, 560.00, 0.00, 881.00, 0.00, 890.00, 0.00, 8.40, 190.00, 0.000, ''),
(5, 380.00, 0.00, 792.00, 0.00, 874.00, 0.00, 5.60, 190.00, 0.000, ''),
(6, 340.00, NULL, 765.00, NULL, 872.00, NULL, 5.10, 193.00, NULL, ''),
(7, 480.00, NULL, 765.00, NULL, 856.00, NULL, 7.20, 196.00, NULL, ''),
(8, 420.00, NULL, 790.00, NULL, 885.00, NULL, 6.30, 189.00, NULL, ''),
(9, 340.00, NULL, 755.00, NULL, 870.00, NULL, 5.10, 195.00, NULL, ''),
(10, 580.00, NULL, 745.00, NULL, 850.00, NULL, 8.40, 205.00, NULL, ''),
(11, 540.00, NULL, 750.00, NULL, 870.00, NULL, 8.10, 195.00, NULL, ''),
(12, 360.00, NULL, 765.00, NULL, 906.00, NULL, 5.40, 203.00, NULL, ''),
(13, 420.00, NULL, 765.00, NULL, 906.00, NULL, 6.30, 195.00, NULL, ''),
(14, 520.00, NULL, 772.00, NULL, 875.00, NULL, 5.20, 172.00, NULL, ''),
(15, 420.00, NULL, 863.00, NULL, 901.00, NULL, 4.20, 175.00, NULL, ''),
(16, 400.00, 0.00, 862.00, 0.00, 913.00, 0.00, 4.00, 170.00, 0.000, ''),
(17, 540.00, NULL, 755.00, NULL, 863.00, NULL, 5.40, 171.00, NULL, ''),
(18, 440.00, NULL, 825.00, NULL, 894.00, NULL, 4.40, 178.00, NULL, ''),
(19, 400.00, NULL, 859.00, NULL, 919.00, NULL, 4.00, 175.00, NULL, ''),
(20, 560.00, NULL, 715.00, NULL, 850.00, NULL, 8.40, 185.00, NULL, ''),
(21, 520.00, NULL, 728.00, NULL, 875.00, NULL, 7.80, 192.00, NULL, ''),
(22, 500.00, NULL, 780.00, NULL, 875.00, NULL, 8.10, 199.00, NULL, ''),
(23, 580.00, NULL, 678.00, NULL, 895.00, NULL, 8.70, 202.00, NULL, ''),
(24, 560.00, NULL, 670.00, NULL, 905.00, NULL, 8.40, 198.00, NULL, ''),
(25, 540.00, NULL, 611.00, NULL, 1013.00, NULL, 8.10, 196.00, NULL, ''),
(26, 600.00, NULL, 678.00, NULL, 895.00, NULL, 8.70, 202.00, NULL, ''),
(27, 580.00, NULL, 670.00, NULL, 905.00, NULL, 8.40, 198.00, NULL, ''),
(28, 540.00, NULL, 611.00, NULL, 1013.00, NULL, 8.10, 196.00, NULL, ''),
(29, 640.00, NULL, 722.00, NULL, 790.00, NULL, 6.40, 175.00, NULL, ''),
(30, 540.00, NULL, 748.00, NULL, 871.00, NULL, 5.40, 175.00, NULL, ''),
(31, 500.00, NULL, 764.00, NULL, 879.00, NULL, 5.00, 175.00, NULL, ''),
(32, 640.00, NULL, 692.00, NULL, 910.40, NULL, 7.00, 140.00, NULL, ''),
(33, 560.00, NULL, 724.00, NULL, 915.50, NULL, 6.00, 150.00, NULL, ''),
(34, 500.00, NULL, 781.00, NULL, 872.00, NULL, 5.00, 180.00, NULL, ''),
(35, 760.00, NULL, 725.00, NULL, 771.00, NULL, 7.60, 175.00, NULL, ''),
(36, 620.00, NULL, 719.00, NULL, 841.00, NULL, 6.20, 180.00, NULL, ''),
(37, 580.00, NULL, 742.00, NULL, 858.00, NULL, 5.80, 175.00, NULL, ''),
(38, 760.00, NULL, 630.00, NULL, 764.00, NULL, 7.60, 182.00, NULL, ''),
(39, 620.00, NULL, 713.00, NULL, 832.00, NULL, 6.20, 182.00, NULL, ''),
(40, 580.00, NULL, 732.00, NULL, 851.00, NULL, 5.80, 180.00, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `scd_vb_list`
--

CREATE TABLE `scd_vb_list` (
  `vb_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `vb_concrete_psi` varchar(50) DEFAULT NULL,
  `vb_cubic_meter` varchar(50) DEFAULT NULL,
  `vb_curation_days` varchar(50) DEFAULT NULL,
  `vb_concrete_type` varchar(50) DEFAULT NULL,
  `vb_concrete_slump` varchar(50) DEFAULT NULL,
  `vb_wc_ratio` varchar(50) DEFAULT NULL,
  `vb_cement_source` varchar(50) DEFAULT NULL,
  `vb_admix_source` varchar(50) DEFAULT NULL,
  `vb_cement_mix` varchar(50) DEFAULT NULL,
  `vb_fly_ash` varchar(50) DEFAULT NULL,
  `vb_sand` varchar(50) DEFAULT NULL,
  `vb_three_eight` varchar(50) DEFAULT NULL,
  `vb_three_fourth` varchar(50) DEFAULT NULL,
  `vb_G1` varchar(50) DEFAULT NULL,
  `vb_admix` varchar(50) DEFAULT NULL,
  `vb_water` varchar(50) DEFAULT NULL,
  `vb_air` varchar(50) DEFAULT NULL,
  `vb_others` varchar(50) DEFAULT NULL,
  `vb_total` varchar(50) DEFAULT NULL,
  `concrete_price` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `scd_vb_list`
--

INSERT INTO `scd_vb_list` (`vb_id`, `sort_order`, `vb_concrete_psi`, `vb_cubic_meter`, `vb_curation_days`, `vb_concrete_type`, `vb_concrete_slump`, `vb_wc_ratio`, `vb_cement_source`, `vb_admix_source`, `vb_cement_mix`, `vb_fly_ash`, `vb_sand`, `vb_three_eight`, `vb_three_fourth`, `vb_G1`, `vb_admix`, `vb_water`, `vb_air`, `vb_others`, `vb_total`, `concrete_price`) VALUES
(1, 1, '1500 psi', ' 3/4', '7', 'ORDINARY', '4\" - 6\"', '0.68', 'Portland', 'Proem', '13.00 kgs/0.50 m³', '', '46.40 kgs/0.50 m³', '', '47.75 kgs/0.50 m³', '', '0.13 ltr', '8.90 ltr', '0.001 m³', '', '2323.6 kgs/m³', ''),
(2, 2, '1500 psi', ' 3/4', '14', 'ORDINARY', '4\" - 6\"', '0.76', 'Portland', 'Proem', '12.00 kgs/0.50 m³', '', '47.55 kgs/0.50 m³', '', '48.25 kgs/0.50 m³', '', '0.12 ltr', '9.10 ltr', '0.001 m³', '', '2340.4 kgs/m³', ''),
(3, 3, '1500 psi', ' 3/4', '28', 'ORDINARY', '4\" - 6\"', '0.90', 'Portland', 'Proem', '10.00 kgs/0.50 m³', '', '47.90 kgs/0.50 m³', '', '49.60 kgs/0.50 m³', '', '0.10 ltr', '9.00 ltr', '0.001 m³', '', '2332 kgs/m³', ''),
(4, 4, '3000 psi', ' 3/4', '7', 'ORDINARY', '4\" - 6\"', '0.34', 'Portland', 'Proem', '28.00 kgs/0.50 m³', '', '44.05 kgs/0.50 m³', '', '44.50 kgs/0.50 m³', '', '0.42 ltr', '9.50 ltr', '0.001 m³', '', '2925.4 kgs/m³', ''),
(5, 5, '3000 psi', ' 3/4', '14', 'ORDINARY', '4\" - 6\"', '0.50', 'Portland', 'Proem', '19.00 kgs/0.50 m³', '', '39.60 kgs/0.50 m³', '', '43.70 kgs/0.50 m³', '', '0.28 ltr', '9.50 ltr', '0.001 m³', '', '2214.6 kgs/m³', ''),
(6, 6, '3000 psi', ' 3/4', '28', 'ORDINARY', '4\" - 6\"', '0.57', 'Portland', 'Proem', '17.00 kgs/0.50 m³', '', '38.25 kgs/0.50 m³', '', '43.60 kgs/0.50 m³', '', '0.26 ltr', '9.65 ltr', '0.001 m³', '', '2175.1 kgs/m³', ''),
(7, 7, '3000 psi', ' 3/4', '7', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '24.00 kgs/0.50 m³', '', '38.25 kgs/0.50 m³', '', '42.80 kgs/0.50 m³', '', '0.36 ltr', '9.80 ltr', '0.001 m³', '', '2304.2 kgs/m³', ''),
(8, 8, '3000 psi', ' 3/4', '14', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '21.00 kgs/0.50 m³', '', '39.50 kgs/0.50 m³', '', '44.25 kgs/0.50 m³', '', '0.32 ltr', '9.45 ltr', '0.001 m³', '', '2290.3 kgs/m³', ''),
(9, 9, '3000 psi', ' 3/4', '28', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '16 kgs/0.50 m³', '', '44.90 kgs/0.50 m³', '', '49.10 kgs/0.50 m³', '', '0.16 ltr', '8.75 ltr', '0.001 m³', '', '2165.1 kgs/m³', ''),
(10, 10, '3000 psi', ' 3/4', '7', 'TREMIE', '8\" - 10\"', '0.00', 'Portland', 'Proem', '29.00 kgs/0.50 m³', '', '37.25 kgs/0.50 m³', '', '42.50 kgs/0.50 m³', '', '0.42 ltr', '10.25 ltr', '0.001 m³', '', '2388.4 kgs/m³', ''),
(11, 11, '3000 psi', ' 3/4', '14', 'TREMIE', '8\" - 10\"', '0.00', 'Portland', 'Proem', '27.00 kgs/0.50 m³', '', '37.50 kgs/0.50 m³', '', '43.50 kgs/0.50 m³', '', '0.41 ltr', '9.75 ltr', '0.001 m³', '', '2363.1 kgs/m³', ''),
(12, 12, '3000 psi', ' 3/4', '28', 'TREMIE', '8\" - 10\"', '0.00', 'Portland', 'Proem', '18.00 kgs/0.50 m³', '', '38.25 kgs/0.50 m³', '', '45.30 kgs/0.50 m³', '', '0.27 ltr', '10.15 ltr', '0.001 m³', '', '2239.4 kgs/m³', ''),
(13, 13, '3500 psi', ' 3/4', '28', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '21.00 kgs/0.50 m³', '', '38.25 kgs/0.50 m³', '', '45.30 kgs/0.50 m³', '', '0.32 ltr', '9.75 ltr', '0.001 m³', '', '2292.3 kgs/m³', ''),
(14, 14, '4000 psi', ' 3/4', '7', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '26.00 kgs/0.50 m³', '', '38.60 kgs/0.50 m³', '', '43.75 kgs/0.50 m³', '', '0.26 ltr', '8.60 ltr', '0.001 m³', '', '2344.2 kgs/m³', ''),
(15, 15, '4000 psi', ' 3/4', '14', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '21.00 kgs/0.50 m³', '', '43.15 kgs/0.50 m³', '', '45.05 kgs/0.50 m³', '', '0.21 ltr', '8.75 ltr', '0.001 m³', '', '2366.2 kgs/m³', ''),
(16, 16, '4000 psi', ' 3/4', '28', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '20.00 kgs/0.50 m³', '', '43.10 kgs/0.50 m³', '', '45.65 kgs/0.50 m³', '', '0.20 ltr', 'ltr', '0.001 m³', '', '2349 kgs/m³', ''),
(17, 17, '4000 psi', ' 3/4', '7', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '27.00 kgs/0.50 m³', '', '37.77 kgs/0.50 m³', '', '43.15 kgs/0.50 m³', '', '0.27 ltr', '8.55 ltr', '0.001 m³', '', '2334.4 kgs/m³', ''),
(18, 18, '4000 psi', ' 3/4', '14', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '22.00  kgs/0.50 m³', '', '41.25 kgs/0.50 m³', '', '44.70 kgs/0.50 m³', '', '0.22 ltr', '8.90 ltr', '0.001 m³', '', '2341.4 kgs/m³', ''),
(19, 19, '4000 psi', ' 3/4', '28', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '20.00 kgs/0.50 m³', '', '42.95 kgs/0.50 m³', '', '45.95 kgs/0.50 m³', '', '0.20 ltr', '8.75 ltr', '0.001 m³', '', '2357 kgs/m³', ''),
(20, 20, '4500 psi', ' 3/4', '7', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '28.00 kgs/0.50 m³', '', '35.75 kgs/0.50 m³', '', '42.50 kgs/0.50 m³', '', '0.42 ltr', '9.25 ltr', '0.001 m³', '', '2318.4kgs/m³', ''),
(21, 21, '4500 psi', ' 3/4', '14', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '26.00 kgs/0.50 m³', '', '36.40 kgs/0.50 m³', '', '43.75 kgs/0.50 m³', '', '0.39 ltr', '9.60 ltr', '0.001 m³', '', '2322.8 kgs/m³', ''),
(22, 22, '4500 psi', ' 3/4', '28', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '25.00 kgs/0.50 m³', '', '39.00 kgs/0.50 m³', '', '43.75 kgs/0.50 m³', '', '0.41 ltr', '9.95 ltr', '0.001 m³', '', '2362.1 kgs/m³', ''),
(23, 23, '4500 psi', ' 3/4', '7', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '29.00 kgs/0.50 m³', '', '33.90 kgs/0.50 m³', '', '44.75 kgs/0.50 m³', '', '0.44 ltr', '10.10 ltr', '0.001 m³', '', '2363.7 kgs/m³', ''),
(24, 24, '4500 psi', ' 3/4', '14', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '28.00 kgs/0.50 m³', '', '33.50 kgs/0.50 m³', '', '45.25 kgs/0.50 m³', '', '0.42 ltr', '9.90 ltr', '0.001 m³', '', '2341.4 kgs/m³', ''),
(25, 25, '4500 psi', ' 3/4', '28', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '27.00 kgs/0.50 m³', '', '30.55 kgs/0.50 m³', '', '50.65 kgs/0.50 m³', '', '0.41 ltr', '9.80 ltr', '0.001 m³', '', '2368.1 kgs/m³', ''),
(26, 26, '4500 psi', ' 3/4', '7', 'TREMIE', '8\" - 10\"', '0.00', 'Portland', 'Proem', '30.00 kgs/0.50 m³', '', '33.90 kgs/0.50 m³', '', '44.75 kgs/0.50 m³', '', '0.44 ltr', '10.10 ltr', '0.001 m³', '', '2383.7 kgs/m³', ''),
(27, 27, '4500 psi', ' 3/4', '14', 'TREMIE', '8\" - 10\"', '0.00', 'Portland', 'Proem', '29.00 kgs/0.50 m³', '', '33.50 kgs/0.50 m³', '', '45.25 kgs/0.50 m³', '', '0.42 ltr', '9.90 ltr', '0.001 m³', '', '2361.4 kgs/m³', ''),
(28, 28, '4500 psi', ' 3/4', '28', 'TREMIE', '8\" - 10\"', '0.00', 'Portland', 'Proem', '27.00 kgs/0.50 m³', '', '30.55 kgs/0.50 m³', '', '50.65 kgs/0.50 m³', '', '0.41 ltr', '9.80 ltr', '0.001 m³', '', '2368.1 kgs/m³', ''),
(29, 29, '5000 psi', ' 3/4', '7', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '32.00 kgs/0.50 m³', '', '36.10 kgs/0.50 m³', '', '39.50 kgs/0.50 m³', '', '0.32 ltr', '8.75 ltr', '0.001 m³', '', '2333.4 kgs/m³', ''),
(30, 30, '5000 psi', ' 3/4', '14', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '27.00 kgs/0.50 m³', '', '37.40 kgs/0.50 m³', '', '43.55 kgs/0.50 m³', '', '0.27 ltr', '8.75 ltr', '0.001 m³', '', '2339.4 kgs/m³', ''),
(31, 31, '5000 psi', ' 3/4', '28', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '25.00 kgs/0.50 m³', '', '38.20 kgs/0.50 m³', '', '43.95 kgs/0.50 m³', '', '0.25 ltr', '8.75 ltr', '0.001 m³', '', '2323 kgs/m³', ''),
(32, 32, '5000 psi', ' 3/4', '7', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '32.00 kgs/0.50 m³', '', '34.60 kgs/0.50 m³', '', '45.52 kgs/0.50 m³', '', '0.35 ltr', '7.00 ltr', '0.001 m³', '', '2389.4 kgs/m³', ''),
(33, 33, '5000 psi', ' 3/4', '14', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '28.00 kgs/0.50 m³', '', '36.20 kgs/0.50 m³', '', '45.78 kgs/0.50 m³', '', '0.30 ltr', '7.50 ltr', '0.001 m³', '', '2355.5 kgs/m³', ''),
(34, 34, '5000 psi', ' 3/4', '28', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '25.00 kgs/0.50 m³', '', '39.05 kgs/0.50 m³', '', '43.60 kgs/0.50 m³', '', '0.25 ltr', '9.00 ltr', '0.001 m³', '', '2338 kgs/m³', ''),
(35, 35, '6000 psi', ' 3/4', '7', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '38.00 kgs/0.50 m³', '', '36.25 kgs/0.50 m³', '', '38.55 kgs/0.50 m³', '', '0.38 ltr', '8.75 ltr', '0.001 m³', '', '2438.6 kgs/m³', ''),
(36, 36, '6000 psi', ' 3/4', '14', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '31.00 kgs/0.50 m³', '', '35.95 kgs/0.50 m³', '', '42.05 kgs/0.50 m³', '', '0.31 ltr', '9.00 ltr', '0.001 m³', '', '2366.2 kgs/m³', ''),
(37, 37, '6000 psi', ' 3/4', '28', 'ORDINARY', '4\" - 6\"', '0.00', 'Portland', 'Proem', '29.00 kgs/0.50 m³', '', '37.10 kgs/0.50 m³', '', '42.90 kgs/0.50 m³', '', '0.29 ltr', '8.75 ltr', '0.001 m³', '', '2366.2 kgs/m³', ''),
(38, 38, '6000 psi', ' 3/4', '7', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '38.00 kgs/0.50 m³', '', '31.50 kgs/0.50 m³', '', '38.20 kgs/0.50 m³', '', '0.38 ltr', '9.10 ltr', '0.001 m³', '', '2343.6 kgs/m³', ''),
(39, 39, '6000 psi', ' 3/4', '14', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '31.00 kgs/0.50 m³', '', '35.65 kgs/0.50 m³', '', '41.60 kgs/0.50 m³', '', '0.31 ltr', '9.10 ltr', '0.001 m³', '', '2353.2 kgs/m³', ''),
(40, 40, '6000 psi', ' 3/4', '28', 'PUMPCRETE DESIGN', '6\" - 8\"', '0.00', 'Portland', 'Proem', '29.00 kgs/0.50 m³', '', '36.60 kgs/0.50 m³', '', '42.55 kgs/0.50 m³', '', '0.29 ltr', '9.00 ltr', '0.001 m³', '', '2348.8 kgs/m³', '');

-- --------------------------------------------------------

--
-- Table structure for table `sorcom_master_user`
--

CREATE TABLE `sorcom_master_user` (
  `master_id` varchar(50) NOT NULL,
  `master_name` varchar(100) NOT NULL,
  `master_email` varchar(100) NOT NULL,
  `master_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_email_change` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sorcom_master_user`
--

INSERT INTO `sorcom_master_user` (`master_id`, `master_name`, `master_email`, `master_password`, `created_at`, `last_email_change`, `is_deleted`, `deleted_at`) VALUES
('070314', 'John Villaluna', 'johnolidv15@gmail.com', '$2y$10$wE1UqstjX3t2Kg.va2ibQOmBR0jDeMsxRBSC8UdIO4F2y1a48GSh6', '2026-05-13 22:39:55', '2026-05-16 00:25:11', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sorcom_mixture_inventory`
--

CREATE TABLE `sorcom_mixture_inventory` (
  `id` int(11) NOT NULL,
  `date_added` date DEFAULT curdate(),
  `cement` decimal(10,2) DEFAULT 0.00,
  `fly_ash` decimal(10,2) DEFAULT 0.00,
  `sand` decimal(10,2) DEFAULT 0.00,
  `3_4` decimal(10,2) DEFAULT 0.00,
  `3_8` decimal(10,2) DEFAULT 0.00,
  `g1` decimal(10,2) DEFAULT 0.00,
  `admix` decimal(10,2) DEFAULT 0.00,
  `water` decimal(10,2) DEFAULT 0.00,
  `air` decimal(10,2) DEFAULT 0.00,
  `others` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sorcom_mixture_inventory`
--

INSERT INTO `sorcom_mixture_inventory` (`id`, `date_added`, `cement`, `fly_ash`, `sand`, `3_4`, `3_8`, `g1`, `admix`, `water`, `air`, `others`, `total`) VALUES
(1, '2026-02-19', 360.00, 0.00, 765.00, 906.00, 0.00, 0.00, 5.40, 203.00, 0.00, 0.00, 2239.00),
(2, '2026-02-19', 275.00, 49.00, 469.05, 477.10, 72.00, 43.00, 59.33, 11.90, 78.00, 92.00, 1626.38),
(4, '2026-02-23', 21.00, 0.00, 43.15, 45.05, 0.00, 0.00, 0.21, 8.75, 1.00, 1.00, 20.00),
(6, '2026-02-26', 711.00, 1000.00, 381.15, 316.05, 1000.00, 1000.00, 995.83, 857.45, 1000.00, 0.00, 7261.47);

-- --------------------------------------------------------

--
-- Table structure for table `sorcom_readymix_inventory`
--

CREATE TABLE `sorcom_readymix_inventory` (
  `id` int(11) NOT NULL,
  `date_added` varchar(10) DEFAULT NULL,
  `design_mix_id` varchar(255) DEFAULT NULL,
  `batch` decimal(15,1) DEFAULT 0.0,
  `stocks_in` int(11) DEFAULT NULL,
  `stocks_out` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `concrete_type` varchar(50) DEFAULT NULL,
  `curation_days` varchar(50) DEFAULT '',
  `current_stock_batch` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sorcom_readymix_inventory`
--

INSERT INTO `sorcom_readymix_inventory` (`id`, `date_added`, `design_mix_id`, `batch`, `stocks_in`, `stocks_out`, `remarks`, `concrete_type`, `curation_days`, `current_stock_batch`) VALUES
(15, '2026-02-18', '3000 psi', 2363.1, 33, 32, 'Ready-mix Concrete', 'TREMIE', '14', '30720.3'),
(17, '2026-02-18', '4500 psi', 2368.1, 9, 2, 'Ready-mix Concrete', 'TREMIE', '28', '16576.7'),
(21, '2026-02-19', '3000 psi', 2388.4, 18, 8, 'Ready-mix Concrete', 'TREMIE', '7', '33437.59999999999'),
(22, '2026-02-20', '1500 psi', 2323.6, 20, 2, 'Ready-mix Concrete', 'ORDINARY', '7', '41824.8'),
(38, '2026-02-26', '1500 psi', 2323.6, 4, 0, '', 'ORDINARY', '7', '9294'),
(39, '2026-02-26', '1500 psi', 2323.6, 3, 0, '', 'ORDINARY', '7', '6970.8'),
(40, '2026-02-26', '1500 psi', 2323.6, 5, 0, '', 'ORDINARY', '7', '11618'),
(41, '2026-02-26', '1500 psi', 2323.6, 10, 0, '', 'ORDINARY', '7', '23236'),
(42, '2026-02-26', '1500 psi', 2323.6, 0, 0, '', 'ORDINARY', '7', '0'),
(43, '2026-02-26', '1500 psi', 2340.4, 13, 0, '', 'ORDINARY', '14', '30425.2'),
(44, '2026-02-26', '1500 psi', 2323.6, 30, 0, '', 'ORDINARY', '7', '69708'),
(45, '2026-02-26', '1500 psi', 2323.6, 50, 0, '', 'ORDINARY', '7', '116180'),
(46, '2026-02-26', '1500 psi', 2340.4, 10, 2, 'Readymix Concrete', 'ORDINARY', '14', '18723.2'),
(47, '2026-03-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(48, '2026-03-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(49, '2026-03-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(50, '2026-03-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(51, '2026-03-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(52, '2026-03-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(53, '2026-03-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(54, '2026-03-03', '1500 psi', 2323.6, 2, 0, '', 'ORDINARY', '7', '4647.2'),
(55, '2026-03-03', '1500 psi', 2323.6, 3, 0, '', 'ORDINARY', '7', '6970.799999999999'),
(58, '2026-04-14', '1500 psi', 2323.6, 1, 2, '', 'ORDINARY', '7', '0'),
(59, '2026-04-14', '3000 psi', 2304.2, 3, 1, '', 'PUMPCRETE DESIGN', '7', '4608.4'),
(60, '2026-04-14', '3500 psi', 2292.3, 3, 3, '', 'ORDINARY', '28', '4584.4'),
(61, '2026-04-14', '3500 psi', 2292.3, 2, 1, '', 'ORDINARY', '28', '2292'),
(62, '2026-04-14', '1500 psi', 2323.6, 2, 1, '', 'ORDINARY', '7', '2323.6'),
(63, '2026-04-15', '1500 psi', 2332.0, 0, 9, '', 'ORDINARY', '28', '0'),
(64, '2026-04-19', '1500 psi', 2323.6, 20, 0, '', 'ORDINARY', '7', '46472'),
(65, '2026-04-19', '1500 psi', 2340.4, 20, 0, '', 'ORDINARY', '14', '46808'),
(66, '2026-04-19', '1500 psi', 2332.0, 20, 0, '', 'ORDINARY', '28', '46640'),
(67, '2026-04-21', '1500 psi', 2323.6, 1, 4, '', 'ORDINARY', '7', '2323.6000000000004'),
(68, '2026-04-21', '1500 psi', 2323.6, 2, 0, '', 'ORDINARY', '7', '4647.2'),
(69, '2026-05-03', '1500 psi', 2340.4, 1, 0, '', 'ORDINARY', '14', '2340.4'),
(70, '2026-05-03', '1500 psi', 2332.0, 3, 1, '', 'ORDINARY', '28', '6996'),
(71, '2026-05-03', '1500 psi', 2323.6, 0, 10, '', 'ORDINARY', '7', '0'),
(75, '2026-05-03', '1500 psi', 2323.6, 0, 0, '', 'ORDINARY', '7', '0'),
(81, '2026-05-03', '1500 psi', 2323.6, 0, 0, '', 'ORDINARY', '7', '0'),
(83, '2026-05-03', '1500 psi', 2323.6, 1, 0, '', 'ORDINARY', '7', '2323.6'),
(84, '2026-05-03', '1500 psi', 2323.6, 9, 0, '', 'ORDINARY', '7', '20912.399999999998'),
(85, '2026-05-03', '1500 psi', 2323.6, 8, 0, '', 'ORDINARY', '7', '18588.8'),
(88, '2026-05-03', '1500 psi', 2323.6, 10, 0, '', 'ORDINARY', '7', '23236'),
(89, '2026-05-03', '6000 psi', 2353.2, 10, 0, '', 'PUMPCRETE DESIGN', '14', '23532'),
(92, '2026-05-05', '3000 psi', 2214.6, 3, 0, '', 'ORDINARY', '14', '6643.799999999999'),
(93, '2026-05-05', '3500 psi', 2292.3, 0, 5, '', 'ORDINARY', '28', '0'),
(94, '2026-05-05', '4500 psi', 2362.1, 4, 1, 'Ready-mix Concrete', 'ORDINARY', '28', '7086.299999999999'),
(95, '2026-05-08', '1500 psi', 2323.6, 5, 0, '', 'ORDINARY', '7', '11618');

-- --------------------------------------------------------

--
-- Table structure for table `sorcom_users`
--

CREATE TABLE `sorcom_users` (
  `employee_number` varchar(20) NOT NULL,
  `employee_name` varchar(50) NOT NULL,
  `employee_email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'Accountant',
  `status` varchar(20) DEFAULT 'Active',
  `last_email_change` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sorcom_users`
--

INSERT INTO `sorcom_users` (`employee_number`, `employee_name`, `employee_email`, `password`, `role`, `status`, `last_email_change`, `is_deleted`, `deleted_at`) VALUES
('123456', 'Juan Dela Cruz', 'johnolidv15@gmail.com', '$2y$10$tvxnXZ3CGjTjDg1lltwADOEIrZ0pqFCC3G5dQOCdLHvfGmJSfdvli', 'Accountant', 'Active', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `statement_of_account`
--

CREATE TABLE `statement_of_account` (
  `sap_id` varchar(20) DEFAULT NULL,
  `concrete_psi` varchar(50) DEFAULT NULL,
  `curation_days` varchar(50) DEFAULT NULL,
  `order_date` date NOT NULL,
  `delivery_date` date NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `full_address` varchar(500) NOT NULL,
  `concrete_type` varchar(50) NOT NULL,
  `sap_units` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sap_concrete_price` decimal(10,2) DEFAULT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statement_of_account`
--

INSERT INTO `statement_of_account` (`sap_id`, `concrete_psi`, `curation_days`, `order_date`, `delivery_date`, `customer_name`, `full_address`, `concrete_type`, `sap_units`, `sap_concrete_price`, `order_status`) VALUES
('src-sap-000002', '', '', '2025-11-05', '2025-11-21', 'John Doe', '123 Blk 4, Street, Subd., Brgy., City, Province, 1000', 'high_strength', 45.00, NULL, 'Done'),
('src-sap-000003', '', '', '2025-11-05', '2025-11-21', 'John Doe', '123 Blk 4, Street, Subd., Brgy., City, Province, 1000', 'high_strength', 45.00, NULL, 'Done'),
('src-sap-000005', '', '', '2025-11-22', '2025-12-06', 'asdfasdf', 'asdfasdf asdfasdf asdfasdfasdf asdfasdfasdfasdf asdfasdfasdfasdfasdf', 'decorative', 744171.00, NULL, 'Done'),
('src-sap-000006', '', '', '2025-11-22', '2025-12-06', 'asdfasdf', 'asdfasdf asdfasdf asdfasdfasdf asdfasdfasdfasdf asdfasdfasdfasdfasdf', 'high_strength', 744171.00, NULL, 'Done'),
('src-sap-000007', '', '', '2025-11-22', '2025-12-06', 'asdfasdf', 'asdfasdf asdfasdf asdfasdfasdf asdfasdfasdfasdf asdfasdfasdfasdfasdf', 'standard', 744171.00, NULL, 'Done'),
('src-sap-000009', '', '', '2025-11-14', '2025-11-14', 'dfsghsrfgh', 'asdfasdf asdfasdf asdfasdfasdf asdfasdfasdfasdf asdfasdfasdfasdfasdf', '3000 psi -- 7 days days', 752752.00, NULL, 'Done'),
('src-sap-000010', '', '', '2025-11-18', '2025-11-29', 'John Doe', 'dfjk,hgeioruvkluierw', 'standard', 60.00, NULL, 'Done'),
('src-sap-000011', '', '', '2026-02-19', '2026-02-28', 'hmgntujkh', 'mhjgkgjfjghm', '4000 psi -- 14 days days', 0.00, NULL, 'Done'),
('src-sap-893000', '1500 psi', '14 days', '2026-02-25', '2026-02-28', 'John Villaluna', 'Prieto Diaz, Sorsogon', 'ORDINARY', 2323.00, NULL, 'Done'),
('src-sap-889051', '1500 psi', '7 days', '2026-02-26', '2026-03-07', 'John Doe', '123 Blk 4, Street, Subd., Brgy., City, Province, 1000', 'ORDINARY', 2323.00, NULL, 'Done'),
('src-sap-856485', '1500 psi', '7 days', '2026-02-26', '2026-03-07', 'John Villaluna ', 'Cubao , QC', 'ORDINARY', 2323.00, NULL, 'Done'),
('src-sap-948795', '3500 psi', '28 days', '2026-04-14', '2026-04-15', 'John Villaluna ', 'Cubao , QC', 'ORDINARY', 10.00, NULL, 'Done'),
('src-sap-837939', '3000 psi', '14', '2026-04-15', '2026-04-18', 'Villaluna John Noli Doniña Sr.', ', Burgos Street || Tupaz || Prieto Diaz || Sorsogon || V', 'TREMIE', 5.00, 14000.00, 'Done'),
('src-sap-744585', '1500 psi', '14', '2026-04-18', '2026-04-25', 'Dela Cruz Juan Uy', ', Magsaysay || Sikat || Cubao || Quezon City || NCR', 'ORDINARY', 3.00, 14000.00, 'Done'),
('src-sap-783664', '3000 psi', '7', '2026-04-19', '2026-04-21', 'Villaluna John', ', Burgos St. || Tupaz || Prieto Diaz || Sorsogon || V', 'TREMIE', 2.00, 14000.00, 'Done'),
('src-sap-761183', '3500 psi', '28', '2026-04-19', '2026-04-22', 'Palma Jose', ', Mahogany || Timog || Quezon City || Metro Manila || V', '0', 2.00, 9000.00, 'In Transit'),
('src-sap-752809', '1500 psi', '7', '2026-04-21', '2026-04-26', 'Manlangit Alyssa Detablan', ', Rizal || Baaagong Pag-asa || Quezon City || Metro Manila || NCR', 'ORDINARY', 2.00, 15000.00, 'Done'),
('src-sap-938799', '1500 psi', '7', '2026-05-03', '2026-05-09', 'Umali Reymond', ', Santol St. || Kawayan || Quezon City || Metro Manila || NCR', 'ORDINARY', 2.00, 14000.00, 'Done'),
('src-sap-878497', '1500 psi', '28', '2026-05-03', '2026-05-09', 'Pangilinan Meriam', ', Timog St. || Indang || Quezon City || Metro Manila || NCR', '0', 1.00, 7000.00, 'Pending'),
('src-sap-792587', '3500 psi', '28', '2026-05-05', '2026-05-09', 'Rodriguez Edwin', ', Magsaysay St. || Tatalon || Quezon City || Metro Manila || NCR', 'ORDINARY', 1.00, 18000.00, 'In Transit');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cdl_customized_mix_list`
--
ALTER TABLE `cdl_customized_mix_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `concrete_designs`
--
ALTER TABLE `concrete_designs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `concrete_volume_batch`
--
ALTER TABLE `concrete_volume_batch`
  ADD PRIMARY KEY (`design_id`);

--
-- Indexes for table `scd_ssd_list`
--
ALTER TABLE `scd_ssd_list`
  ADD PRIMARY KEY (`ssd_id`);

--
-- Indexes for table `scd_vb_list`
--
ALTER TABLE `scd_vb_list`
  ADD PRIMARY KEY (`vb_id`),
  ADD UNIQUE KEY `unique_design` (`vb_concrete_psi`,`vb_curation_days`,`vb_concrete_type`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indexes for table `sorcom_master_user`
--
ALTER TABLE `sorcom_master_user`
  ADD PRIMARY KEY (`master_id`),
  ADD UNIQUE KEY `master_email` (`master_email`),
  ADD UNIQUE KEY `master_id` (`master_id`);

--
-- Indexes for table `sorcom_mixture_inventory`
--
ALTER TABLE `sorcom_mixture_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sorcom_readymix_inventory`
--
ALTER TABLE `sorcom_readymix_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sorcom_users`
--
ALTER TABLE `sorcom_users`
  ADD PRIMARY KEY (`employee_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cdl_customized_mix_list`
--
ALTER TABLE `cdl_customized_mix_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `concrete_designs`
--
ALTER TABLE `concrete_designs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `concrete_volume_batch`
--
ALTER TABLE `concrete_volume_batch`
  MODIFY `design_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scd_ssd_list`
--
ALTER TABLE `scd_ssd_list`
  MODIFY `ssd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `scd_vb_list`
--
ALTER TABLE `scd_vb_list`
  MODIFY `vb_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `sorcom_mixture_inventory`
--
ALTER TABLE `sorcom_mixture_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sorcom_readymix_inventory`
--
ALTER TABLE `sorcom_readymix_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
