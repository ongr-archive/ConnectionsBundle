-- Creates sync storage table for testing.
CREATE TABLE `ongr_sync_storage_1` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(1) COLLATE utf8_unicode_ci NOT NULL COMMENT 'C-CREATE(INSERT),U-UPDATE,D-DELETE',
  `document_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `document_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-new,1-inProgress,2-error',
  PRIMARY KEY (`id`),
  KEY `IDX_EB160B2F7B00651C` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Adds dummy data to sync storage table.
INSERT INTO `ongr_sync_storage_1` (`id`, `type`, `document_type`, `document_id`, `timestamp`, `status`)
VALUES
  (11, 'C', 'product', 3, '2014-12-09 09:00:00', 0),
  (12, 'C', 'product', 4, '2014-12-09 09:00:00', 0),
  (13, 'C', 'product', 5, '2014-12-09 09:00:00', 0),
  (14, 'C', 'product', 6, '2014-12-09 09:00:00', 0),
  (15, 'U', 'product', 1, '2014-12-11 10:00:00', 0),
  (16, 'D', 'product', 2, '2014-12-11 11:00:00', 0);
