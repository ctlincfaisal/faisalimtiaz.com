-- ============================================================
-- Background email queue + real-time send progress
-- Run directly on the server database (MySQL)
-- Equivalent to these Laravel migrations:
--   2026_10_01_000000_create_jobs_tables.php
--   2026_10_01_000001_add_send_progress_to_marketing_emails_table.php
-- ============================================================

-- 1) Jobs table (stores one queued send per recipient)
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Job batches table (Laravel batch metadata, part of the standard queue setup)
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Add progress counters to the existing marketing_emails table.
-- Run only once. If you already ran the migration, these columns exist.
ALTER TABLE `marketing_emails`
  ADD COLUMN `sent_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `recipient_count`,
  ADD COLUMN `failed_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `sent_count`;