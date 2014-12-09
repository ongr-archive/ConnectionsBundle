CREATE TABLE `jobs_test` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(1) COLLATE utf8_unicode_ci NOT NULL COMMENT 'C-CREATE(INSERT),U-UPDATE,D-DELETE',
  `document_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `document_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `update_type` smallint(6) NOT NULL DEFAULT '1' COMMENT '0-partial,1-full',
  `timestamp` datetime NOT NULL,
  `status_shop1` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-new,1-done',
  `status_shop2` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-new,1-done',
  PRIMARY KEY (`id`),
  KEY `IDX_F468762A4CF216CF` (`status_shop1`),
  KEY `IDX_F468762AD5FB4775` (`status_shop2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci