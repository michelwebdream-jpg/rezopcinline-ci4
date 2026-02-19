-- Script SQL pour créer les tables des annonces de la page login (production).
-- À exécuter une seule fois sur la base de production (phpMyAdmin, MySQL, etc.).
-- Tables préfixées REZO_

-- Table des annonces
CREATE TABLE IF NOT EXISTS `REZO_login_notices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` mediumtext DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table de configuration (durée d'affichage)
CREATE TABLE IF NOT EXISTS `REZO_login_notice_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `display_duration_seconds` int(11) NOT NULL DEFAULT 8,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Valeur par défaut : 8 secondes par annonce
INSERT INTO `REZO_login_notice_config` (`id`, `display_duration_seconds`) VALUES (1, 8)
ON DUPLICATE KEY UPDATE `display_duration_seconds` = VALUES(`display_duration_seconds`);
