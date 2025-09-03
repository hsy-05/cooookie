-- --------------------------------------------------------
-- 主機:                           127.0.0.1
-- 伺服器版本:                        11.2.0-MariaDB - mariadb.org binary distribution
-- 伺服器作業系統:                      Win64
-- HeidiSQL 版本:                  12.3.0.6589
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- 傾印  資料表 laravel-adminlte.advert 結構
CREATE TABLE IF NOT EXISTS `advert` (
  `adv_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` bigint(20) unsigned NOT NULL,
  `adv_img_url` varchar(255) DEFAULT NULL COMMENT '電腦版圖片',
  `adv_img_m_url` varchar(255) DEFAULT NULL COMMENT '手機版圖片',
  `adv_link_url` varchar(255) DEFAULT NULL COMMENT '廣告連結',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_visible` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`adv_id`),
  KEY `advert_cat_id_foreign` (`cat_id`),
  CONSTRAINT `advert_cat_id_foreign` FOREIGN KEY (`cat_id`) REFERENCES `advert_category` (`cat_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.advert 的資料：~2 rows (近似值)
INSERT INTO `advert` (`adv_id`, `cat_id`, `adv_img_url`, `adv_img_m_url`, `adv_link_url`, `display_order`, `is_visible`, `created_at`, `updated_at`) VALUES
	(5, 2, NULL, NULL, 'https://www.youtube.com/', 0, 1, '2025-09-02 10:52:19', '2025-09-02 11:01:02'),
	(6, 1, 'adv/adv_1756839745.jpg', 'adv/1756839772.jpg', 'https://www.youtube.com/watch?v=_8Myg8xUOMo&list=RD_8Myg8xUOMo&start_radio=1', 0, 1, '2025-09-02 11:02:27', '2025-09-02 11:02:52');

-- 傾印  資料表 laravel-adminlte.advert_category 結構
CREATE TABLE IF NOT EXISTS `advert_category` (
  `cat_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cat_code` varchar(50) NOT NULL COMMENT '分類代碼，例如 idx_banner',
  `cat_func_scope` varchar(255) DEFAULT NULL COMMENT '功能範圍，例如 adv_img_url, adv_img_m_url',
  `cat_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '功能參數，存 JSON 結構' CHECK (json_valid(`cat_params`)),
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否顯示',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `advert_category_cat_code_unique` (`cat_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.advert_category 的資料：~2 rows (近似值)
INSERT INTO `advert_category` (`cat_id`, `cat_code`, `cat_func_scope`, `cat_params`, `display_order`, `is_visible`, `created_at`, `updated_at`) VALUES
	(1, 'idx_banner', '["adv_img_url","adv_img_m_url","adv_link_url"]', '{"item_limit_num":-1,"fields":{"adv_img_url":{"width":1920,"height":960},"adv_img_m_url":{"width":800,"height":960},"adv_link_url":[]}}', 0, 1, '2025-08-31 11:55:33', '2025-08-31 11:55:33'),
	(2, 'idx_block1', '["adv_link_url"]', '{}', 0, 1, '2025-08-31 12:02:22', '2025-08-31 12:02:22');

-- 傾印  資料表 laravel-adminlte.advert_category_desc 結構
CREATE TABLE IF NOT EXISTS `advert_category_desc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` bigint(20) unsigned NOT NULL,
  `lang_id` bigint(20) unsigned NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `advert_category_desc_cat_id_foreign` (`cat_id`),
  CONSTRAINT `advert_category_desc_cat_id_foreign` FOREIGN KEY (`cat_id`) REFERENCES `advert_category` (`cat_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.advert_category_desc 的資料：~1 rows (近似值)
INSERT INTO `advert_category_desc` (`id`, `cat_id`, `lang_id`, `cat_name`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, '首頁-橫幅廣告', '2025-08-31 11:55:33', '2025-08-31 11:55:33');

-- 傾印  資料表 laravel-adminlte.advert_desc 結構
CREATE TABLE IF NOT EXISTS `advert_desc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adv_id` bigint(20) unsigned NOT NULL,
  `lang_id` varchar(10) NOT NULL,
  `adv_name` varchar(100) NOT NULL,
  `adv_subname` varchar(150) DEFAULT NULL,
  `adv_brief` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `advert_desc_adv_id_foreign` (`adv_id`),
  CONSTRAINT `advert_desc_adv_id_foreign` FOREIGN KEY (`adv_id`) REFERENCES `advert` (`adv_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.advert_desc 的資料：~2 rows (近似值)
INSERT INTO `advert_desc` (`id`, `adv_id`, `lang_id`, `adv_name`, `adv_subname`, `adv_brief`, `created_at`, `updated_at`) VALUES
	(3, 5, '1', '廣告111', NULL, NULL, '2025-09-02 10:52:19', '2025-09-02 10:52:19'),
	(4, 6, '1', '廣告222', NULL, NULL, '2025-09-02 11:02:27', '2025-09-02 11:02:27');

-- 傾印  資料表 laravel-adminlte.cache 結構
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.cache 的資料：~0 rows (近似值)

-- 傾印  資料表 laravel-adminlte.cache_locks 結構
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.cache_locks 的資料：~0 rows (近似值)

-- 傾印  資料表 laravel-adminlte.failed_jobs 結構
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.failed_jobs 的資料：~0 rows (近似值)

-- 傾印  資料表 laravel-adminlte.jobs 結構
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.jobs 的資料：~0 rows (近似值)

-- 傾印  資料表 laravel-adminlte.job_batches 結構
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.job_batches 的資料：~0 rows (近似值)

-- 傾印  資料表 laravel-adminlte.languages 結構
CREATE TABLE IF NOT EXISTS `languages` (
  `lang_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '語系名稱',
  `alias` varchar(255) DEFAULT NULL COMMENT '別名',
  `code` varchar(10) NOT NULL COMMENT '代碼',
  `iso_code` varchar(10) DEFAULT NULL COMMENT 'ISO 代碼',
  `region` varchar(255) DEFAULT NULL COMMENT '區域',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '啟用',
  `display_scope` enum('both','backend') NOT NULL DEFAULT 'both' COMMENT '顯示範圍：both=前後台, backend=僅後台',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.languages 的資料：~2 rows (近似值)
INSERT INTO `languages` (`lang_id`, `name`, `alias`, `code`, `iso_code`, `region`, `display_order`, `enabled`, `display_scope`, `created_at`, `updated_at`) VALUES
	(1, '繁體中文', 'TW', 'zh-tw', 'zh-Hant', 'zh,zh-tw,zh_tw,zh_tw.UTF-8,tw,chinese', 0, 1, 'both', '2025-08-12 01:46:16', '2025-08-12 01:46:16');

-- 傾印  資料表 laravel-adminlte.migrations 結構
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.migrations 的資料：~15 rows (近似值)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_08_09_190321_create_news_table', 2),
	(5, '2025_08_12_081502_create_news_category_table', 3),
	(6, '2025_08_12_091357_create_news_category_desc_table', 3),
	(7, '2025_08_12_091402_create_languages_table', 3),
	(8, '2025_08_12_091440_create_news_desc_table', 3),
	(9, '2025_08_12_195945_create_news_category_table', 4),
	(10, '2025_08_12_200124_create_news_category_desc_table', 5),
	(11, '2025_08_31_184541_create_advert_tables', 6),
	(12, '2025_08_31_194544_create_advert_category_tables', 7),
	(13, '2025_09_02_192102_rename_sort_order_to_display_order_in_advert_table', 8),
	(15, '2025_09_02_193425_rename_sort_order_to_display_order_in_advert_category_table', 9),
	(16, '2025_09_02_194433_rename_sort_order_to_display_order_in_languages_table', 10);

-- 傾印  資料表 laravel-adminlte.news 結構
CREATE TABLE IF NOT EXISTS `news` (
  `news_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` smallint(5) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`news_id`) USING BTREE,
  KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.news 的資料：~4 rows (近似值)
INSERT INTO `news` (`news_id`, `cat_id`, `image`, `is_visible`, `display_order`, `created_at`, `updated_at`) VALUES
	(1, 2, 'news/1756716029.jpg', 1, 1, '2025-08-13 11:35:04', '2025-09-01 00:40:29'),
	(2, 2, 'news/1755549470.jpg', 1, 0, '2025-08-18 12:37:51', '2025-08-18 12:37:51'),
	(3, 1, 'news/1755974322.jpg', 1, 0, '2025-08-20 00:28:02', '2025-08-23 10:38:42'),
	(4, 2, 'news/1755681095jpg', 1, 0, '2025-08-20 01:07:05', '2025-08-20 01:11:35');

-- 傾印  資料表 laravel-adminlte.news_category 結構
CREATE TABLE IF NOT EXISTS `news_category` (
  `cat_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL COMMENT '上層分類 cat_id',
  `parent_ids` varchar(255) DEFAULT NULL COMMENT '上層 ID 串, 例如: 1,3,5',
  `super_id` bigint(20) unsigned DEFAULT NULL COMMENT '最上層分類 cat_id',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否顯示',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT '顯示排序，數字大者優先',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cat_id`),
  KEY `news_category_parent_id_index` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.news_category 的資料：~4 rows (近似值)
INSERT INTO `news_category` (`cat_id`, `parent_id`, `parent_ids`, `super_id`, `is_visible`, `display_order`, `created_at`, `updated_at`) VALUES
	(1, NULL, NULL, NULL, 1, 0, '2025-08-13 10:11:40', '2025-08-13 10:11:40'),
	(2, 1, NULL, NULL, 1, 0, '2025-08-14 10:24:56', '2025-08-14 10:24:56'),
	(3, NULL, NULL, NULL, 1, 0, '2025-08-25 10:33:01', '2025-08-25 10:33:01'),
	(4, NULL, NULL, NULL, 0, 999, '2025-08-26 00:31:31', '2025-08-26 00:31:31');

-- 傾印  資料表 laravel-adminlte.news_category_desc 結構
CREATE TABLE IF NOT EXISTS `news_category_desc` (
  `cat_id` bigint(20) unsigned NOT NULL COMMENT '參照 news_category.cat_id',
  `lang_id` bigint(20) unsigned NOT NULL COMMENT 'language.lang_id',
  `name` varchar(255) NOT NULL COMMENT '分類名稱（各語系）',
  `description` varchar(255) DEFAULT '' COMMENT '簡述（各語系）',
  `content` text DEFAULT NULL COMMENT '內文（各語系），可使用 CKEditor 編輯',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cat_id`,`lang_id`),
  KEY `news_category_desc_cat_id_index` (`cat_id`),
  KEY `news_category_desc_lang_id_index` (`lang_id`),
  CONSTRAINT `news_category_desc_cat_id_foreign` FOREIGN KEY (`cat_id`) REFERENCES `news_category` (`cat_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.news_category_desc 的資料：~4 rows (近似值)
INSERT INTO `news_category_desc` (`cat_id`, `lang_id`, `name`, `description`, `content`, `created_at`, `updated_at`) VALUES
	(1, 1, '分類名稱1', NULL, '<h1><strong>sss</strong></h1>', '2025-08-13 10:15:18', '2025-08-13 10:15:18'),
	(2, 1, '子分類名稱1', '簡述 (description)', NULL, '2025-08-14 10:24:56', '2025-08-14 10:24:56'),
	(3, 1, '分類名稱2', NULL, NULL, '2025-08-25 10:33:01', '2025-08-25 10:33:01'),
	(4, 1, '分類名稱３', NULL, '<p><br></p>', '2025-08-26 01:01:40', '2025-08-26 01:01:40');

-- 傾印  資料表 laravel-adminlte.news_desc 結構
CREATE TABLE IF NOT EXISTS `news_desc` (
  `news_id` bigint(20) unsigned NOT NULL COMMENT 'news.id',
  `lang_id` bigint(20) unsigned NOT NULL COMMENT 'language.lang_id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '標題（語系）',
  `content` text DEFAULT NULL COMMENT '內容（語系）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `news_desc_news_id_lang_id_unique` (`news_id`,`lang_id`),
  KEY `news_desc_news_id_index` (`news_id`),
  KEY `news_desc_lang_id_index` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.news_desc 的資料：~4 rows (近似值)
INSERT INTO `news_desc` (`news_id`, `lang_id`, `title`, `content`, `created_at`, `updated_at`) VALUES
	(1, 1, '標題1', '<p>123123</p><p><b><span style="font-size: 36px; background-color: rgb(255, 156, 0);"><font color="#cee7f7">123123123</font></span></b></p><p><b><span style="font-size: 36px; background-color: rgb(255, 156, 0);"><font color="#cee7f7"><br></font></span></b></p>', '2025-08-13 11:35:04', '2025-08-26 01:15:52'),
	(2, 1, '標題2', '<p><img style="width: 25%; float: right;" src="http://localhost:82/laravel-adminlte/public/storage/uploads/XEVjLcwj8HWPc14o4IOClprZ44qhn9vvttLM9cAH.jpg" class="note-float-right"><br></p>', '2025-08-18 12:37:51', '2025-08-18 13:38:40'),
	(3, 1, '標題3', '<p><img style="width: 10%;" src="[[SITE_URL]]storage/uploads/4FFC5VrucUQD4pUwWIfcTzHiY6yG3oFuR5It63xn.jpg"><br></p>', '2025-08-20 00:28:02', '2025-08-24 09:51:33'),
	(4, 1, '標題4', '<h1><span style="background-color: rgb(0, 49, 99);"><font color="#ffe79c"><b>111</b></font></span></h1>', '2025-08-20 01:07:05', '2025-08-20 01:07:05');

-- 傾印  資料表 laravel-adminlte.password_reset_tokens 結構
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.password_reset_tokens 的資料：~0 rows (近似值)

-- 傾印  資料表 laravel-adminlte.sessions 結構
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.sessions 的資料：~5 rows (近似值)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('1n2MH1YMroTMAU4BvxQlJ9ltAqXSnVLtkp66QwKM', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiaUdVVVIzWDhMWmdkSHd3VkFkcW9PSVdnZERuVzFrVkwyOFBLN0xCQyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjcyOiJodHRwOi8vbG9jYWxob3N0OjgyL2xhcmF2ZWwtYWRtaW5sdGUvcHVibGljL2FkbWluL2FkdmVydF9jYXRlZ29yeS9jcmVhdGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc1NjgwMDM5Njt9fQ==', 1756804319),
	('gYlKe4vMYrHXsp5dYSd5sftIlBPpnEZER17HsDJ1', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiOXZpUEdCbHhwUHhvSktvT0E3NEdKaUYwek0zc2VYVnZwZndnNXcxUCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjYzOiJodHRwOi8vbG9jYWxob3N0OjgyL2xhcmF2ZWwtYWRtaW5sdGUvcHVibGljL2FkbWluL2FkdmVydC8xL2VkaXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc1Njc1MzA3OTt9fQ==', 1756758606),
	('rK5hCbfyIhLf4XfCadE3o41tJQxuoP49D2Zi4XfC', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicFB5cEZ1UnluZEVMWWo4ODgzVW5zeHl6Ull2bmpBeHdScW1XeFE4TCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo2MzoiaHR0cDovL2xvY2FsaG9zdDo4Mi9sYXJhdmVsLWFkbWlubHRlL3B1YmxpYy9hZG1pbi9hZHZlcnQvMS9lZGl0Ijt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODIvbGFyYXZlbC1hZG1pbmx0ZS9wdWJsaWMvYWRtaW4vYWR2ZXJ0LzEvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756879280),
	('sOrhm2sNH56t5eI3eN3Q1lN4flqB6ip3YbqScvFi', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiWHNxSnZGeGM4SVA1NTBWRk5qM2lMUWlTT3I4SzZNSVlCbU1KQkt5RSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjYzOiJodHRwOi8vbG9jYWxob3N0OjgyL2xhcmF2ZWwtYWRtaW5sdGUvcHVibGljL2FkbWluL25ld3NfY2F0ZWdvcnkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc1NjgzNTk4OTt9fQ==', 1756843138),
	('toDsBQwIMyep99yNXUibruvVI4MaBMsuS9kI9VIW', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT1ZwaHZpMXdURkNsNzduWTEzaVp4QW00aDZndkZpQjdCRW5naUhTSiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo2MToiaHR0cDovL2xvY2FsaG9zdDo4Mi9sYXJhdmVsLWFkbWlubHRlL3B1YmxpYy9hZG1pbi9uZXdzLzEvZWRpdCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQ5OiJodHRwOi8vbG9jYWxob3N0OjgyL2xhcmF2ZWwtYWRtaW5sdGUvcHVibGljL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756879282);

-- 傾印  資料表 laravel-adminlte.users 結構
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在傾印表格  laravel-adminlte.users 的資料：~0 rows (近似值)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'test', 'test@mail', NULL, '$2y$12$IabkbMW5RW24QgkkTDxOI.XVs0DfPbNXScb5CXxTzNk3HiAR7EKpG', 'YsMt2r7VJK6Im0pZnrdBH5eJQZuGNFVwn96q5NvP1Py6AEYiNUUmnj8CU0do', '2025-07-21 02:16:20', '2025-07-21 02:16:20');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
