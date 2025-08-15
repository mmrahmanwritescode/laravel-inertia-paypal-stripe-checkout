-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.42 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table laravel-inertia-paypal-stripe.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (concat(`first_name`,_utf8mb4' ',`last_name`)) VIRTUAL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `post_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `address`, `post_code`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(9, 'Mohammad Mahfoozur', 'Rahman', 'mahfoozur.rahman.bd@gmail.com', '01922291533', 'east kafrul', '1206', NULL, NULL, NULL, '2025-08-15 08:41:35', '2025-08-15 08:41:35');


-- Dumping structure for table laravel-inertia-paypal-stripe.food_items
CREATE TABLE IF NOT EXISTS `food_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(8,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.food_items: ~0 rows (approximately)
INSERT INTO `food_items` (`id`, `name`, `description`, `price`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Margherita Pizza', 'Classic pizza with tomato and mozzarella', 12.99, NULL, 1, '2025-08-15 05:53:55', '2025-08-15 05:53:55'),
	(2, 'Chicken Burger', 'Grilled chicken with lettuce and tomato', 9.99, NULL, 1, '2025-08-15 05:53:55', '2025-08-15 05:53:55'),
	(3, 'Caesar Salad', 'Fresh romaine with caesar dressing', 7.99, NULL, 1, '2025-08-15 05:53:55', '2025-08-15 05:53:55'),
	(4, 'Fish & Chips', 'Beer battered fish with crispy chips', 14.99, NULL, 1, '2025-08-15 05:53:55', '2025-08-15 05:53:55'),
	(5, 'Beef Tacos', 'Soft tacos with seasoned beef and salsa', 11.99, NULL, 1, '2025-08-15 05:53:55', '2025-08-15 05:53:55'),
	(6, 'Chicken Wings', 'Spicy buffalo wings with blue cheese', 8.99, NULL, 1, '2025-08-15 05:53:55', '2025-08-15 05:53:55');
	

-- Dumping structure for table laravel-inertia-paypal-stripe.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.cache: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.cache_locks: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_food_item_id_foreign` (`food_item_id`),
  CONSTRAINT `cart_items_food_item_id_foreign` FOREIGN KEY (`food_item_id`) REFERENCES `food_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.cart_items: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.jobs: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.job_batches: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.migrations: ~7 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_08_07_000001_create_food_items_table', 1),
	(5, '2025_08_07_000002_create_cart_items_table', 1),
	(6, '2025_08_07_000003_create_orders_table', 1),
	(7, '2025_08_07_000004_create_order_items_table', 1);

-- Dumping structure for table laravel-inertia-paypal-stripe.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `purchase_order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('order_in_progress','order_placed','confirmed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'order_in_progress',
  `payment_method` enum('paypal','stripe','N/A') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paypal',
  `price` decimal(8,2) NOT NULL,
  `shipping_cost` decimal(8,2) NOT NULL DEFAULT '0.00',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_capture_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_refund_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `order_type` enum('delivery','takeaway','pay_on_spot') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'delivery',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_purchase_order_id_unique` (`purchase_order_id`),
  KEY `orders_user_id_foreign` (`user_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.orders: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `food_item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_food_item_id_foreign` (`food_item_id`),
  CONSTRAINT `order_items_food_item_id_foreign` FOREIGN KEY (`food_item_id`) REFERENCES `food_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.order_items: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table laravel-inertia-paypal-stripe.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table laravel-inertia-paypal-stripe.sessions: ~0 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('EBGwftjWPN354uLEgg5DtlzNjGtM0MlFJ22hDgC7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.21.1 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoic0wyNXFlZnJLak9uQXR4RW1WRDVHTEpUeGc4dXhmSlNtajJBUjRGWSI7czoxNToiY2FydF9zZXNzaW9uX2lkIjtzOjQwOiJFQkd3ZnRqV1BOMzU0dUxFZ2c1RHRsek5qR3RNME1sRkoyMmhEZ0M3IjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1NToiaHR0cDovL2xhcmF2ZWwtaW5lcnRpYS1wYXlwYWwtc3RyaXBlLnRlc3QvP2hlcmQ9cHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755268635),
	('FHKE31XFq0FxlfOv8k35h9bQgy5gKkzgvWDsdWgL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWEJOcTFwNzVNZG45WktWNnExMHJHanNITE11U2N6UFZFTUZHZmRkTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxNToiY2FydF9zZXNzaW9uX2lkIjtzOjQwOiJGSEtFMzFYRnEwRnhsZk92OGszNWg5YlFneTVnS2t6Z3ZXRHNkV2dMIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyMzg6Imh0dHA6Ly9sYXJhdmVsLWluZXJ0aWEtcGF5cGFsLXN0cmlwZS50ZXN0L29yZGVycy9jb25maXJtZWQvT1JEMjAyNTA4MTUxNzQxNDYzMDg/cGF5bWVudF9pbnRlbnQ9cGlfM1J3UmN3QzhZRzRNaVVpODFsaDNxc2NsJnBheW1lbnRfaW50ZW50X2NsaWVudF9zZWNyZXQ9cGlfM1J3UmN3QzhZRzRNaVVpODFsaDNxc2NsX3NlY3JldF9FczBGZGpkS3p0T0hzV0RNenBiNm9DdmNxJnJlZGlyZWN0X3N0YXR1cz1zdWNjZWVkZWQiO319', 1755279731),
	('R3zV7JeHQ8GV6exoAaXjeV84pwLk4rDybyD0Ydm3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS1l4S285ZjVSSFh5R1pJZGRkTkd4VnBhdk1QeFE0SFA0TGFmVkpHNiI7czoxNToiY2FydF9zZXNzaW9uX2lkIjtzOjQwOiJSM3pWN0plSFE4R1Y2ZXhvQWFYamVWODRwd0xrNHJEeWJ5RDBZZG0zIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyMzg6Imh0dHA6Ly9sYXJhdmVsLWluZXJ0aWEtcGF5cGFsLXN0cmlwZS50ZXN0L29yZGVycy9jb25maXJtZWQvT1JEMjAyNTA4MTUxNDQxMzUyMjU/cGF5bWVudF9pbnRlbnQ9cGlfM1J3T25ZQzhZRzRNaVVpODFUb0FORld2JnBheW1lbnRfaW50ZW50X2NsaWVudF9zZWNyZXQ9cGlfM1J3T25ZQzhZRzRNaVVpODFUb0FORld2X3NlY3JldF9lS2djQ2V3cXFCZHo5dXc0RUFIelFlVkdNJnJlZGlyZWN0X3N0YXR1cz1zdWNjZWVkZWQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1755268919),
	('XlirVmyuzRGfrTbbXThXmXtziMVorMLAumGz4AzA', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.21.1 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYzN6dTNhWmVaQnI3QWFnTWl3TkE5TGNRblMxclI5aHJjQWRoeWo0biI7czoxNToiY2FydF9zZXNzaW9uX2lkIjtzOjQwOiJYbGlyVm15dXpSR2ZyVGJiWFRoWG1YdHppTVZvck1MQXVtR3o0QXpBIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1NToiaHR0cDovL2xhcmF2ZWwtaW5lcnRpYS1wYXlwYWwtc3RyaXBlLnRlc3QvP2hlcmQ9cHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755262704),
	('zciu4WcvGTqCafl6ltIcUNSTkSevNTtnr5JTnJ8X', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.21.1 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY1IxeXNURlN0dHZHNmFLTlpVSU9WRURCeWljNWNKVmdWWG5WR0FCMiI7czoxNToiY2FydF9zZXNzaW9uX2lkIjtzOjQwOiJ6Y2l1NFdjdkdUcUNhZmw2bHRJY1VOU1RrU2V2TlR0bnI1SlRuSjhYIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1NToiaHR0cDovL2xhcmF2ZWwtaW5lcnRpYS1wYXlwYWwtc3RyaXBlLnRlc3QvP2hlcmQ9cHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755268467);


/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
