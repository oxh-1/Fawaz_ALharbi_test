-- ============================================================
-- Company 2 — Complete MySQL Database Schema
-- Compatible with MySQL 8.0 (XAMPP)
-- Run this file in phpMyAdmin or via: mysql -u root < schema.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ─────────────────────────────────────────
-- Create & Use Database
-- ─────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `company2_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `company2_db`;

-- ─────────────────────────────────────────
-- 1. TENANTS
-- ─────────────────────────────────────────
CREATE TABLE `tenants` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL,
  `slug`       VARCHAR(100)    NOT NULL UNIQUE,
  `status`     ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `plan_id`    BIGINT UNSIGNED NULL,
  `owner_id`   BIGINT UNSIGNED NULL,
  `logo`       VARCHAR(500)    NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenants_slug` (`slug`),
  INDEX `idx_tenants_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 2. DOMAINS (Multi-tenant domain mapping)
-- ─────────────────────────────────────────
CREATE TABLE `domains` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain`     VARCHAR(255)    NOT NULL UNIQUE,
  `tenant_id`  BIGINT UNSIGNED NOT NULL,
  `type`       ENUM('main','subdomain','custom') NOT NULL DEFAULT 'main',
  `is_primary` TINYINT(1)      NOT NULL DEFAULT 0,
  `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_domains_domain` (`domain`),
  INDEX `idx_domains_tenant` (`tenant_id`),
  CONSTRAINT `fk_domains_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 3. USERS
-- ─────────────────────────────────────────
CREATE TABLE `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`         BIGINT UNSIGNED NULL COMMENT 'NULL = super admin',
  `name`              VARCHAR(255)    NOT NULL,
  `email`             VARCHAR(255)    NOT NULL,
  `email_verified_at` TIMESTAMP       NULL,
  `password`          VARCHAR(255)    NULL COMMENT 'NULL for OAuth-only users',
  `google_id`         VARCHAR(255)    NULL,
  `picture`           VARCHAR(500)    NULL,
  `is_super_admin`    TINYINT(1)      NOT NULL DEFAULT 0,
  `status`            ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
  `last_login_at`     TIMESTAMP       NULL,
  `remember_token`    VARCHAR(100)    NULL,
  `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_users_email` (`email`),
  INDEX `idx_users_tenant` (`tenant_id`),
  INDEX `idx_users_google` (`google_id`),
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 4. ROLES
-- ─────────────────────────────────────────
CREATE TABLE `roles` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL,
  `slug`        VARCHAR(100)    NOT NULL UNIQUE,
  `description` VARCHAR(500)    NULL,
  `is_system`   TINYINT(1)      NOT NULL DEFAULT 0 COMMENT 'Cannot be deleted',
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 5. PERMISSIONS
-- ─────────────────────────────────────────
CREATE TABLE `permissions` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150)    NOT NULL,
  `slug`        VARCHAR(150)    NOT NULL UNIQUE,
  `module`      VARCHAR(100)    NOT NULL COMMENT 'merchants, bookings, reviews, etc.',
  `action`      ENUM('view','create','edit','delete') NOT NULL,
  `description` VARCHAR(500)    NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_permissions_slug` (`slug`),
  INDEX `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 6. ROLE_PERMISSIONS (pivot)
-- ─────────────────────────────────────────
CREATE TABLE `role_permissions` (
  `role_id`       BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 7. USER_ROLES (pivot)
-- ─────────────────────────────────────────
CREATE TABLE `user_roles` (
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `role_id`    BIGINT UNSIGNED NOT NULL,
  `tenant_id`  BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `role_id`, `tenant_id`),
  CONSTRAINT `fk_ur_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_role`   FOREIGN KEY (`role_id`)   REFERENCES `roles`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 8. API TOKENS (Sanctum-style)
-- ─────────────────────────────────────────
CREATE TABLE `personal_access_tokens` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255)    NOT NULL,
  `tokenable_id`   BIGINT UNSIGNED NOT NULL,
  `name`           VARCHAR(255)    NOT NULL,
  `token`          VARCHAR(64)     NOT NULL UNIQUE,
  `abilities`      TEXT            NULL,
  `last_used_at`   TIMESTAMP       NULL,
  `expires_at`     TIMESTAMP       NULL,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tokens_tokenable` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 9. CATEGORIES
-- ─────────────────────────────────────────
CREATE TABLE `categories` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  BIGINT UNSIGNED NOT NULL,
  `name`       VARCHAR(255)    NOT NULL,
  `slug`       VARCHAR(255)    NOT NULL,
  `icon`       VARCHAR(10)     NOT NULL DEFAULT '📦' COMMENT 'Emoji icon',
  `sort_order` INT             NOT NULL DEFAULT 0,
  `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_categories_tenant_slug` (`tenant_id`, `slug`),
  INDEX `idx_categories_status` (`status`),
  CONSTRAINT `fk_categories_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 10. MERCHANTS
-- ─────────────────────────────────────────
CREATE TABLE `merchants` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `user_id`     BIGINT UNSIGNED NULL COMMENT 'Linked user account',
  `name`        VARCHAR(255)    NOT NULL,
  `email`       VARCHAR(255)    NOT NULL,
  `phone`       VARCHAR(50)     NULL,
  `address`     TEXT            NULL,
  `category_id` BIGINT UNSIGNED NULL,
  `logo`        VARCHAR(500)    NULL,
  `status`      ENUM('active','inactive','pending','suspended') NOT NULL DEFAULT 'pending',
  `joined_date` DATE            NOT NULL DEFAULT (CURDATE()),
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_merchants_tenant` (`tenant_id`),
  INDEX `idx_merchants_status` (`status`),
  INDEX `idx_merchants_email` (`email`),
  CONSTRAINT `fk_merchants_tenant`   FOREIGN KEY (`tenant_id`)   REFERENCES `tenants`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_merchants_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`      (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_merchants_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 11. SERVICES
-- ─────────────────────────────────────────
CREATE TABLE `services` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `merchant_id` BIGINT UNSIGNED NOT NULL,
  `category_id` BIGINT UNSIGNED NULL,
  `name`        VARCHAR(255)    NOT NULL,
  `description` TEXT            NULL,
  `price`       DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
  `duration_min`INT             NULL COMMENT 'Service duration in minutes',
  `tags`        JSON            NULL COMMENT 'Array of tag strings',
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_services_tenant`   (`tenant_id`),
  INDEX `idx_services_merchant` (`merchant_id`),
  INDEX `idx_services_active`   (`is_active`),
  CONSTRAINT `fk_services_tenant`   FOREIGN KEY (`tenant_id`)   REFERENCES `tenants`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 12. BOOKINGS
-- ─────────────────────────────────────────
CREATE TABLE `bookings` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      BIGINT UNSIGNED NOT NULL,
  `service_id`     BIGINT UNSIGNED NOT NULL,
  `merchant_id`    BIGINT UNSIGNED NOT NULL,
  `customer_name`  VARCHAR(255)    NOT NULL,
  `customer_email` VARCHAR(255)    NOT NULL,
  `customer_phone` VARCHAR(50)     NULL,
  `booking_date`   DATE            NOT NULL,
  `booking_time`   TIME            NOT NULL,
  `notes`          TEXT            NULL,
  `status`         ENUM('pending','confirmed','cancelled','completed','no_show') NOT NULL DEFAULT 'pending',
  `total_price`    DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
  `cancelled_at`   TIMESTAMP       NULL,
  `cancel_reason`  VARCHAR(500)    NULL,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_bookings_tenant`   (`tenant_id`),
  INDEX `idx_bookings_merchant` (`merchant_id`),
  INDEX `idx_bookings_date`     (`booking_date`),
  INDEX `idx_bookings_status`   (`status`),
  CONSTRAINT `fk_bookings_tenant`   FOREIGN KEY (`tenant_id`)   REFERENCES `tenants`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_service`  FOREIGN KEY (`service_id`)  REFERENCES `services`  (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_merchant` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 13. REVIEWS
-- ─────────────────────────────────────────
CREATE TABLE `reviews` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    BIGINT UNSIGNED NOT NULL,
  `merchant_id`  BIGINT UNSIGNED NOT NULL,
  `booking_id`   BIGINT UNSIGNED NULL,
  `author_name`  VARCHAR(255)    NOT NULL,
  `author_email` VARCHAR(255)    NULL,
  `rating`       TINYINT         NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `text`         TEXT            NOT NULL,
  `status`       ENUM('pending','approved','rejected','flagged') NOT NULL DEFAULT 'pending',
  `moderated_by` BIGINT UNSIGNED NULL,
  `moderated_at` TIMESTAMP       NULL,
  `reject_reason`VARCHAR(500)    NULL,
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_reviews_tenant`   (`tenant_id`),
  INDEX `idx_reviews_merchant` (`merchant_id`),
  INDEX `idx_reviews_status`   (`status`),
  INDEX `idx_reviews_rating`   (`rating`),
  CONSTRAINT `fk_reviews_tenant`      FOREIGN KEY (`tenant_id`)    REFERENCES `tenants`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_merchant`    FOREIGN KEY (`merchant_id`)  REFERENCES `merchants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_booking`     FOREIGN KEY (`booking_id`)   REFERENCES `bookings`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reviews_moderatedby` FOREIGN KEY (`moderated_by`) REFERENCES `users`     (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 14. CONTACT MESSAGES
-- ─────────────────────────────────────────
CREATE TABLE `contact_messages` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `name`        VARCHAR(255)    NOT NULL,
  `email`       VARCHAR(255)    NOT NULL,
  `subject`     VARCHAR(500)    NOT NULL,
  `message`     TEXT            NOT NULL,
  `ip_address`  VARCHAR(45)     NULL,
  `is_read`     TINYINT(1)      NOT NULL DEFAULT 0,
  `read_by`     BIGINT UNSIGNED NULL,
  `read_at`     TIMESTAMP       NULL,
  `replied_at`  TIMESTAMP       NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_contact_tenant`  (`tenant_id`),
  INDEX `idx_contact_is_read` (`is_read`),
  CONSTRAINT `fk_contact_tenant`  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contact_read_by` FOREIGN KEY (`read_by`)   REFERENCES `users`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 15. PRICING PLANS
-- ─────────────────────────────────────────
CREATE TABLE `pricing_plans` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`       BIGINT UNSIGNED NOT NULL,
  `name`            VARCHAR(100)    NOT NULL,
  `slug`            VARCHAR(100)    NOT NULL,
  `description`     TEXT            NULL,
  `monthly_price`   DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
  `annual_price`    DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
  `features`        JSON            NOT NULL COMMENT '[{"label":"...", "included": true}]',
  `max_merchants`   INT             NULL COMMENT 'NULL = unlimited',
  `max_bookings`    INT             NULL COMMENT 'NULL = unlimited',
  `is_featured`     TINYINT(1)      NOT NULL DEFAULT 0,
  `is_active`       TINYINT(1)      NOT NULL DEFAULT 1,
  `sort_order`      INT             NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_plans_tenant_slug` (`tenant_id`, `slug`),
  CONSTRAINT `fk_plans_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 16. ADS
-- ─────────────────────────────────────────
CREATE TABLE `ads` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `name`        VARCHAR(255)    NOT NULL,
  `type`        ENUM('banner','video','sponsored','push','native') NOT NULL DEFAULT 'banner',
  `image_url`   VARCHAR(500)    NULL,
  `target_url`  VARCHAR(500)    NULL,
  `start_date`  DATE            NOT NULL,
  `end_date`    DATE            NOT NULL,
  `impressions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `clicks`      BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `status`      ENUM('active','inactive','scheduled','expired') NOT NULL DEFAULT 'scheduled',
  `budget`      DECIMAL(10, 2)  NULL,
  `created_by`  BIGINT UNSIGNED NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ads_tenant`  (`tenant_id`),
  INDEX `idx_ads_status`  (`status`),
  INDEX `idx_ads_dates`   (`start_date`, `end_date`),
  CONSTRAINT `fk_ads_tenant`     FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ads_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 17. CONTENT PAGES (CMS)
-- ─────────────────────────────────────────
CREATE TABLE `content_pages` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`        BIGINT UNSIGNED NOT NULL,
  `title`            VARCHAR(500)    NOT NULL,
  `slug`             VARCHAR(500)    NOT NULL,
  `content`          LONGTEXT        NULL,
  `meta_description` TEXT            NULL,
  `meta_keywords`    VARCHAR(500)    NULL,
  `status`           ENUM('published','draft') NOT NULL DEFAULT 'draft',
  `author_id`        BIGINT UNSIGNED NULL,
  `published_at`     TIMESTAMP       NULL,
  `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_content_tenant_slug` (`tenant_id`, `slug`(191)),
  INDEX `idx_content_status` (`status`),
  CONSTRAINT `fk_content_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_content_author` FOREIGN KEY (`author_id`) REFERENCES `users`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 18. SETTLEMENTS
-- ─────────────────────────────────────────
CREATE TABLE `settlements` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    BIGINT UNSIGNED NOT NULL,
  `merchant_id`  BIGINT UNSIGNED NOT NULL,
  `reference_no` VARCHAR(50)     NOT NULL UNIQUE,
  `amount`       DECIMAL(12, 2)  NOT NULL,
  `fee`          DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
  `net_amount`   DECIMAL(12, 2)  NOT NULL,
  `currency`     CHAR(3)         NOT NULL DEFAULT 'SAR',
  `method`       ENUM('bank_transfer','sadad','stc_pay','cash') NOT NULL DEFAULT 'bank_transfer',
  `status`       ENUM('pending','processing','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
  `period_start` DATE            NULL,
  `period_end`   DATE            NULL,
  `paid_at`      TIMESTAMP       NULL,
  `notes`        TEXT            NULL,
  `processed_by` BIGINT UNSIGNED NULL,
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_settlements_ref` (`reference_no`),
  INDEX `idx_settlements_tenant`   (`tenant_id`),
  INDEX `idx_settlements_merchant` (`merchant_id`),
  INDEX `idx_settlements_status`   (`status`),
  CONSTRAINT `fk_settlements_tenant`       FOREIGN KEY (`tenant_id`)    REFERENCES `tenants`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_settlements_merchant`     FOREIGN KEY (`merchant_id`)  REFERENCES `merchants` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_settlements_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users`     (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 19. NOTIFICATION SETTINGS
-- ─────────────────────────────────────────
CREATE TABLE `notification_settings` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `tenant_id`  BIGINT UNSIGNED NOT NULL,
  `type`       VARCHAR(100)    NOT NULL COMMENT 'new_booking, new_review, payment, merchant_reg, billing',
  `label`      VARCHAR(255)    NOT NULL,
  `channel`    ENUM('email','push','sms','in_app') NOT NULL DEFAULT 'email',
  `is_enabled` TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_notif_user_type_channel` (`user_id`, `type`, `channel`),
  CONSTRAINT `fk_notif_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 20. SETTINGS (key-value per tenant)
-- ─────────────────────────────────────────
CREATE TABLE `settings` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  BIGINT UNSIGNED NOT NULL,
  `key`        VARCHAR(100)    NOT NULL,
  `value`      LONGTEXT        NULL,
  `type`       ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
  `group`      VARCHAR(100)    NOT NULL DEFAULT 'general',
  `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_settings_tenant_key` (`tenant_id`, `key`),
  CONSTRAINT `fk_settings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- 21. AUDIT LOGS
-- ─────────────────────────────────────────
CREATE TABLE `audit_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NULL,
  `tenant_id`  BIGINT UNSIGNED NULL,
  `action`     VARCHAR(100)    NOT NULL COMMENT 'create, update, delete, login, logout',
  `model`      VARCHAR(100)    NULL COMMENT 'Merchant, Booking, etc.',
  `model_id`   BIGINT UNSIGNED NULL,
  `old_data`   JSON            NULL,
  `new_data`   JSON            NULL,
  `ip_address` VARCHAR(45)     NULL,
  `user_agent` VARCHAR(500)    NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_user`    (`user_id`),
  INDEX `idx_audit_tenant`  (`tenant_id`),
  INDEX `idx_audit_model`   (`model`, `model_id`),
  INDEX `idx_audit_created` (`created_at`),
  CONSTRAINT `fk_audit_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_audit_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Update tenants FK to users
-- ─────────────────────────────────────────
ALTER TABLE `tenants`
  ADD CONSTRAINT `fk_tenants_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `tenants`
  ADD CONSTRAINT `fk_tenants_plan` FOREIGN KEY (`plan_id`) REFERENCES `pricing_plans` (`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;
