-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 12, 2025 at 06:34 AM
-- Server version: 8.0.43
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `layananbackup`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `nik`, `phone`, `email`, `email_verified_at`, `is_verified`, `verified_at`, `verified_by`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'Super Admin', 'eyJpdiI6InhyVkpqeEN6K0NWZVBDNTVTRUh2Umc9PSIsInZhbHVlIjoia0UrU3dlM0VXUm82YlZKbm1zRVBmQT09IiwibWFjIjoiZjIzODRkYTM5YTQ4YjJlZjZjZDRkYWY4MDE4NTU0MGU3NTk4Y2U0ZjEzMmNhMDI2ODE1ZGFiMzY1MTZjMTA0MCIsInRhZyI6IiJ9', '1001', 'admin@kaltaraprov.go.id', NULL, 1, '2025-08-19 04:23:47', 'admin@kaltaraprov.go.id', '$2y$12$e/0f7qg24lXDhEyoJr1U4.7qEs5hvO6iuhMBrMeJTY1XrNE648q/a', 'nfUXafdD3EJEwdjE6tIgrjRid50AjZS33oR1AwvqD3KseSQRrXBq9t0BQOHb', '2025-07-21 08:10:18', '2025-08-19 04:23:47', 'admin'),
(2, 'Bayu Adi Hartanto', 'eyJpdiI6Inc4ZGpSVEZ1R1kyMFYySzhScEx6OFE9PSIsInZhbHVlIjoieG84ei9YbWk1ZmNrYVZzU0dEc09jRGhXVVFsWWs0YjJiOWdLQXl6bnVrbz0iLCJtYWMiOiJmOTczMDdiNDdlZGNiMzRkODA2NGU2YmY3NTg2YjY4YmI4NmEwNTUyZWZjMzNlZjJjYTU4OTc2Y2ViYzVmOTY5IiwidGFnIjoiIn0=', '08122346', 'skivanoc@gmail.com', NULL, 1, '2025-08-19 12:47:28', 'admin@kaltaraprov.go.id', '$2y$12$0Cfl05J4vSpwfjaBLsQhxOcz/FWxXurYZopbDvDlFtYkGGq7Rx6ci', 'uRGDOQgi4tsS4sg0OnryHLv8uCEzD95qNTvJ73VXQAdxfkOsBHYS8BzB0wlA', '2025-07-21 08:10:26', '2025-09-01 03:25:14', 'admin-vidcon'),
(3, 'Tester', 'eyJpdiI6Ilh4eHlhck01NWxRbEhQS2Q4TzNOa3c9PSIsInZhbHVlIjoicEZ1VW9pWmdUWU5ucDVmNDRWbHFXZz09IiwibWFjIjoiOWNlMTRjMjQ2YmRjNWMyNWMwZjA0NWQwYzZjOTJmZjc1MzVhZGZmMTkyMzUxNTgxNDliYjJlNDI0NGEzNGY5NCIsInRhZyI6IiJ9', '111', 'userdummy10@kaltaraprov.go.id', NULL, 1, '2025-08-19 04:23:59', 'admin@kaltaraprov.go.id', '$2y$12$MEZvWycYg1/XN8F8avFqk.uTXYzwlnlWRR35kzNvrj3VlrJnaUx/u', NULL, '2025-07-25 02:06:52', '2025-08-19 04:23:59', 'user'),
(5, 'Revo Angga Putra Suseno', 'eyJpdiI6IlNMRk9Zc1BTVXhlZXdNNFZpNi9UelE9PSIsInZhbHVlIjoiTDNYd3BwRlJCR3dLNFdESXcxODJ0bzNkU0t3ZTN1MkltSm1uZXVtRHAzYz0iLCJtYWMiOiI3MmU1ZjRhMjY4OWFlOGVkYTU3NmEzZTdiZjhhMTQyYzVlNjU4ZTk2ZTUyNzA0ZTFmZjZmNTJkYzI2M2FmNzE1IiwidGFnIjoiIn0=', '08515671814', 'revoanggaputra@kaltaraprov.go.id', NULL, 1, '2025-08-19 04:23:24', 'admin@kaltaraprov.go.id', '$2y$12$unjvRassd/JSnJ3/Asg5YerWLbdvbcIXPX89LCXwhJ2sWUK9aVDO6', NULL, '2025-08-07 02:41:45', '2025-08-27 00:26:46', 'user'),
(7, 'Nurul Dini Purwanti', 'eyJpdiI6InVXNHJiNU0ycDNIYVFtSVo2bkRoenc9PSIsInZhbHVlIjoieTZLYkZuSjYxMTV3ajM1QVU5MnhIV2RBQXJaL0xSeGFDcXphTEl2UnBoWT0iLCJtYWMiOiI4OWZhOTQ2MmVkZWYzYmE4MzExMTkzN2UwYjYyODQ5YzhkNzFlYzI1OGVhOGQ2ODExM2E2YzdiNjIxOTY3NzNhIiwidGFnIjoiIn0=', '08115581408', 'nuruldinip@gmail.com', NULL, 1, '2025-08-19 04:23:29', 'admin@kaltaraprov.go.id', '$2y$12$ffWu8fWG3I69Nx9Y4KrdoOuQuHrt559pcAeWme85N2V0l/gGHa9aG', NULL, '2025-08-07 03:37:48', '2025-08-19 04:23:29', 'user'),
(8, 'sadness', 'eyJpdiI6IjdYNnNzNUQ0V2dmVGFTQldmVk9SR0E9PSIsInZhbHVlIjoiSGZTTE8zbHhnblBTa25GR0JzMzV5elZWeFUxVUhseEtkRCs0N3RUc2pMbz0iLCJtYWMiOiIzMjU3MzRiMzUxYjhkZDBmZTBlMzJhMmExY2U3MjA0ODIwYmJlZTFhNWFjMTVkZDc4MWUzY2Y5NjhhNjQ4OThkIiwidGFnIjoiIn0=', '0823653108', 'tujuhjet7@gmail.com', NULL, 0, NULL, NULL, '$2y$12$yN8Iagmcbx.8EVuIdxs2oOS.AB.a/geC4ZwWXNDJ/mJUUxCy3hEom', NULL, '2025-08-08 11:47:18', '2025-08-11 01:00:57', 'user'),
(9, 'Achmad Musadad', 'eyJpdiI6IjN6citYaW1YV3ROWm5QOTA3VHFoMWc9PSIsInZhbHVlIjoiZEhuV1dSd2VrSXUxV2pHOXJxeEpXL2JiWWNXWWRaeDA5ZG9BTUhnMm92Zz0iLCJtYWMiOiIwMDVhMTFiMDllNGY5ZDU3MzIyZThiMTJlMTgwZGQ0MDJlMzc3NjUwZDJjZTE4OTZkMTQ1NjI0NmE5ZTEwZmIxIiwidGFnIjoiIn0=', '085729743210', 'achmadmusadad@gmail.com', NULL, 1, '2025-08-27 00:30:07', 'admin@kaltaraprov.go.id', '$2y$12$0zs3QctRxKUYxpM6i9tePeNyyjU2S8LiiukHeNOjO7kDkA3B53uay', NULL, '2025-08-14 00:52:21', '2025-08-27 00:30:40', 'user'),
(10, 'Tester tes', 'eyJpdiI6ImlBeXZSOHoxU0xWOENGZWNad0NnSlE9PSIsInZhbHVlIjoiYjY0SEdCYzMxVm5JZEhEV2tyVHlLQT09IiwibWFjIjoiMzBmN2Y4MmRjYTRjYWU5OWQ2NjA5NTE2M2U1ZGQ1NWEzZjEwODRmOGQ1ZDVjNmRhMDM1ODQ2MDAzY2EzZGZiOSIsInRhZyI6IiJ9', '111111', 'userdummy09@kaltaraprov.go.id', NULL, 0, NULL, NULL, '$2y$12$.4p2KnO0u.sWQn0bnVJAWuvOfLgZrlN5TMnrr7IYj4XFg5.aCvzF2', NULL, '2025-08-19 03:59:35', '2025-08-19 03:59:35', 'user'),
(11, 'Putri Ayu Kartini', 'eyJpdiI6ImVjQWwydVB6cGNJcFQrK085RVVua0E9PSIsInZhbHVlIjoiclROeUtDZ1d4SS9tU1c1a3A4N2VxbGFkNnZ1VjAxNm1NcXJBUWRLbU9Hbz0iLCJtYWMiOiI0ODA1ZjZlOTA3MjNiMjhlY2I0ZjNkNWZiNDY3MzY3YWQyOTk1OGVhY2RlZDg0MTY1OWI4OTJlNTVlOWU1MzMxIiwidGFnIjoiIn0=', '085217365558', 'putriayukartini04@gmail.com', NULL, 0, NULL, NULL, '$2y$12$t4J8kwIeILvk.Bsvmja0Ge0OSIBjyrnCoXj3fK57wu7owKndgAsM6', NULL, '2025-08-19 05:52:57', '2025-09-01 01:59:42', 'user'),
(12, 'User Dummy xx', 'eyJpdiI6ImhrL2pOL29DNmdndm52enFuUFgxbnc9PSIsInZhbHVlIjoiTXg5NzhOVXd4akNHbkJ4ZzZFOHVqWGZWVkgwNGtEOVM2QmN6YVRaZWZ5MD0iLCJtYWMiOiJiYWZjZmRlOGE1MTMwMjZmMGE0ZDY3OTk3OWYyMDUzOTgxMzEzMWIyNjRiYWNhNzYxZTA5Y2VjOGVlOTdiYjRiIiwidGFnIjoiIn0=', '0886666', 'userdummy08@kaltaraprov.go.id', NULL, 1, '2025-08-22 08:01:56', 'admin@kaltaraprov.go.id', '$2y$12$Tc.byr.S7qXQfdhgEVgVLOkEZuX91NhjDfswp3yXZNNiUcuIqr9W6', '1fDhRmej6MZucYUlrNa14BMCBLHZox1yL5JQ63FWW9pSFDT84OyoKUcVEDqB', '2025-08-19 12:17:52', '2025-10-28 07:03:06', 'operator-vidcon'),
(13, 'Combet Ohct', 'eyJpdiI6IktxeWFhcUdTMHBxemFZNEp0Y0hmV3c9PSIsInZhbHVlIjoiZHpFakNUZDNQVlRMVDEvandQNm11cWFBYkZjb1pSNVI5aEV5RlBmQjdxST0iLCJtYWMiOiI2NTQzZGNhNjQ3OWNkMmVmMmM1N2Y3OTM0MGY3MmQ0MDY4N2E0M2Y0NDIxYTYwNzdmZDQ1MTIwZTlhNWUxODNmIiwidGFnIjoiIn0=', '09123456789', 'combetohct@yahoo.com', NULL, 0, NULL, NULL, '$2y$12$22DXkxCzTGs1lEb.p15.1eZ3y7dJxgefphW/2Srqk9nQg/6w9/ffC', NULL, '2025-08-19 16:48:52', '2025-09-01 01:58:48', 'user'),
(14, 'Sandryo Gusjani', 'eyJpdiI6IkZFRWNJd2xQQ2ttMmZiY3owRStkdHc9PSIsInZhbHVlIjoiQUFURXhnQmRJN0JyaUlGbEhCS25pRFdhQ3oxZ05JWXlDNXNOU1dsMHpNaz0iLCJtYWMiOiI4NTViOGFjOGZkNjUyNjhiNmUxZDM0NjcwNzA2OTQwYzljYWViYWY1NzYzMWMyNTA2NDJiN2I4ZTc2Y2RiZTllIiwidGFnIjoiIn0=', '085259078771', 'gusjanisandryo@gmail.com', NULL, 1, '2025-08-20 07:48:07', 'admin@kaltaraprov.go.id', '$2y$12$Q85GeTYgzr7.FosmH0jGJeZOQczCAWDaBTaSzeXBvMoamwp66uItm', 'Qsp2QeTc1blLCehfmodVnqgFhGaSBsIYAMXHCu4pZLOuYjeRTlgFz8eUhsIq', '2025-08-20 07:46:27', '2025-09-01 04:24:01', 'admin'),
(15, 'Mochammad Naffian', 'eyJpdiI6IjVKckNlTktjOHRXdndLbmFoRWdKQUE9PSIsInZhbHVlIjoiQzNWS25Qc2tPNzVENjJYUXhSNTVNZHVlVTBBZlNPMkdrd2h5YTZSMGpCMD0iLCJtYWMiOiJkZTgxZjMyMmM2NmI3NDc1ZDg2ODM0MDYyZWI3YTBkNmQ2MzliYjQ2Y2E5NzlkZjQzNTY0MDlkMDRjMWM1ZTY2IiwidGFnIjoiIn0=', '08115992325', 'mcnafian@hotmail.com', NULL, 1, '2025-09-01 03:32:46', 'admin@kaltaraprov.go.id', '$2y$12$Z6lvIFgulUS2Opke5FoVzexKw/rV8NLKiQRssdfW0x7p5bl4dyunW', NULL, '2025-08-28 02:26:14', '2025-09-01 03:32:46', 'user'),
(16, 'teester\'', 'eyJpdiI6IlA3QlN0TDI3VURLV3k1L1dIK2JxV2c9PSIsInZhbHVlIjoiaUo2YjQ3SEZuYXNtdFMvaFdpUGlrampLSVJueEpaU3QvSUp4dTMwWmZ1cz0iLCJtYWMiOiI3MTlmNzdjOThlZWZhYjVlNTExMTVmOWJkZjJmMDIyYzA2ZmYyMWEzZDdiMDExNzVjYjdiODBkYmY4MTg5YWIxIiwidGFnIjoiIn0=', '08990231921', 'bagarid552@besaies.com', NULL, 0, NULL, NULL, '$2y$12$oAlfpvRAJfNTLzIXVKTyqOXaHlZhptDsBNa38bwwPkXX9YBI9hsSi', NULL, '2025-09-04 17:03:39', '2025-09-04 17:04:09', 'user'),
(17, 'gebrielle ann', 'eyJpdiI6ImdseTYyV09uUU5sRkZvOUsvbEdjc2c9PSIsInZhbHVlIjoiNGV5Nm1MQXc4WFFCOUdVaWdmTXVtc3BVVGRYOHh5bmZvVjA0MWxKeDBjbz0iLCJtYWMiOiI5MTJlZmM4NzI2ZGVkOTNmNmY4YTBmODM3MjRiMTM1NzI3NmY2NThkMDFiZDA2ZWM1NzEyZTIxYjU1NjlhNTU0IiwidGFnIjoiIn0=', '085754302912', 'gebyonicann@gmail.com', NULL, 0, NULL, NULL, '$2y$12$5/DnQV7zFFPXEP6G2o9lS..xNLZktF88ABOcn6dR07TSEXpGDPmfC', NULL, '2025-09-13 21:07:02', '2025-09-13 21:07:02', 'user'),
(18, 'Fimanisa Arianingrum', 'eyJpdiI6IkhiUGlKY0RjOFV1dWJ4dmtmTGVWYXc9PSIsInZhbHVlIjoiQUxabTVtcmxqRjNhZXJESUdNTjdrV2RCbzBHa0ZZQ0oxWTN4bHZBL2FpVT0iLCJtYWMiOiI2ODRjNTBlNDFmZTQyMWQ4N2JhODNhMzhlYjMzZjMzNTQ2YWM1OGZmNDRlNTM0NjQ3MDM4ZTZjNTQyZTJhY2YyIiwidGFnIjoiIn0=', '085393314138', 'fimanisaa@kaltaraprov.go.id', NULL, 1, '2025-09-23 10:29:42', 'admin@kaltaraprov.go.id', '$2y$12$c7KZhBfOObw5M7HoGtJcNeR2tuzJoNyRoYFEscY5E6WDHrkRpX53q', NULL, '2025-09-23 10:29:33', '2025-09-23 10:29:52', 'admin'),
(19, 'Fikriansyah Azidan', 'eyJpdiI6IngxalQxcDRhd0pRMnJNYW4zeVk1OGc9PSIsInZhbHVlIjoiYjA5VmFHbmJJM3BKMkpDbzFDb2Ntc2NranBhMUNoVDFacnBlRThNZ0gvWT0iLCJtYWMiOiIxMDE1YWU5ZjhkMTQyOGFiM2Y3NGUwN2I4MWM3NGUwYjUzMGNiMmZmNWY0ZmM4Mjg0NmUxNmZiMTliYmFhNTg1IiwidGFnIjoiIn0=', '082193723646', 'fikriansyahazidan20@gmail.com', NULL, 1, '2025-09-24 10:58:07', 'gusjanisandryo@gmail.com', '$2y$12$CSMP2km5T7Tblrb0xJE/leZY8Gh2wkUT591O8OuatD/aMO6voH/DC', 'tPiGsYwv5n8xV1nzT9bh8ywCAxoLWuiVsNuwSejgUXYMNisrwvHNknIB4z0D', '2025-09-24 10:56:22', '2025-09-24 15:15:27', 'operator-vidcon'),
(20, 'Prezza W', 'eyJpdiI6IjZVS1prZXVDaUN5K1BYbWhGSm9yRVE9PSIsInZhbHVlIjoiRk5SUnJJamhwZ0pmdU9KUGNKZExjK2hMbTBjNzUwNTVJSFQ2SE8wQ2RwYz0iLCJtYWMiOiJhNTRhYWEyNzkzOTllYTI2OTRjM2EzY2QzYjRhNTQ2M2ZkNzM0MDhlNzg5MDE3ZDJlZDYyODcyZGE5NmY5NmE2IiwidGFnIjoiIn0=', '082245606283', 'bayhero002@gmail.com', NULL, 1, '2025-09-24 10:58:25', 'gusjanisandryo@gmail.com', '$2y$12$ELKFhPyd1QHk5dJ4XzmjTOr1MOuqEMfx.WNWOvgFGNFOEiBQBc1OS', 'Un0udSu4sHs09wEH6dpwpj3c7UMXmce9xNHTCou7jIKvA8FqomzduCOvcrQI', '2025-09-24 10:57:00', '2025-09-24 15:15:11', 'operator-vidcon'),
(21, 'Muhammad Khaidir', 'eyJpdiI6ImZuQU5kYzRwWk9GRUticGtPSy9TdVE9PSIsInZhbHVlIjoicGlTTzRSQmlxUFBSUjd3Rm9jQVVEZ1NxbW02ZWdSUU1jM3lPM0Z6R090cz0iLCJtYWMiOiIyMDE4M2JjMGJiYWVkOTkzNzFkZmVkNWYzNDljZTY4NTI4YWM4MDc0NjM1NzE3NDUyZjAxMjc3YTYxZTkzZmZmIiwidGFnIjoiIn0=', '085390462543', 'muhammadkhaidirbup@gmail.com', NULL, 1, '2025-09-24 10:57:59', 'gusjanisandryo@gmail.com', '$2y$12$UqfA9zfRRyKxGMTYSFVn8OEyNMKyddjO6bX2cY7O90lKG2zruzoia', NULL, '2025-09-24 10:57:23', '2025-09-24 15:14:14', 'operator-vidcon'),
(22, 'JUMARDIN', 'eyJpdiI6InNLZ21uWm8zeWQwZXZBS1pteUk5THc9PSIsInZhbHVlIjoiVXVCL09yVUZpTUNuUUZjNi8zTE9ybC9Va1k3NDBPTW9MdCt6WStYalY4QT0iLCJtYWMiOiJiZWQ0NGExMTM0NjljMmE2MmYxYWU3MWZiYzA3NjY1N2Y0YTlmNWYyNzVjYWI2ZTcwNDMwYzQ0YmNlMzY3Zjc1IiwidGFnIjoiIn0=', '085343929330', 'mxk37567@gmail.com', NULL, 1, '2025-09-25 10:54:09', 'gusjanisandryo@gmail.com', '$2y$12$ioS3C8iTYxxk8kSXyANST.5EyECIao1Uujm6ZWRWGtFmth9RLT.GO', NULL, '2025-09-25 10:49:45', '2025-09-25 10:54:09', 'operator-vidcon'),
(23, 'Irfan', 'eyJpdiI6Im9UZWsvcGhwQlc4VUQ1azM2dUo1NEE9PSIsInZhbHVlIjoiVnoycG9qV3JEOFVMYm9ERUFXWjZ2TWFtY2d6ZXhNSVBUc0lTZmVTMEFoST0iLCJtYWMiOiI3MTk3YmVmNTA2NzhiNWEzYzIxNWY5NTBjNGZmMGJhNTVjOTI1ZWM2MTkyN2Q2YTY4M2M0MTM0OWI5YTU1MGEzIiwidGFnIjoiIn0=', '+6282332402892', 'irfan.arnsyah@gmail.com', NULL, 1, '2025-09-29 09:08:09', 'admin@kaltaraprov.go.id', '$2y$12$i17bFVkh/L2eSQIUfwT2XuUScEzZyvG8p6hNIkAxxoOWM6GmMAD66', NULL, '2025-09-29 09:05:17', '2025-09-29 09:08:18', 'admin'),
(24, 'Shhshs', 'eyJpdiI6IitpTEozOXgybTFGVjNqeXRIVmhhcnc9PSIsInZhbHVlIjoibE5ZNUo0Q3cwNjBUS0huOU9ubmxJY0FOYTA5bSswMVNPRjJxNXcvTkFhbz0iLCJtYWMiOiI0NjVjZjI4ZDc1ZDg0OWJkYTQxYWNhZTY4YTUwOGExMWQ5ZDFjYjQzNWQ3MTI5M2VhYjJhZmJhMWE0ODNkMTU5IiwidGFnIjoiIn0=', '09736674534', 'insomnia@zudpck.com', NULL, 0, NULL, NULL, '$2y$12$okigHW06xJgo94.hV75oCOjHxXZrZLgc7luTDmAivFgRv73ztiUdC', NULL, '2025-10-08 00:21:46', '2025-10-28 06:53:39', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_is_verified_index` (`is_verified`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
