CREATE TABLE ongr_sync_jobs (id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(1) NOT NULL COMMENT 'C-CREATE(INSERT),U-UPDATE,D-DELETE',
  `document_type` VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL,
  `document_id` VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL,
  `update_type` smallint(1) NOT NULL DEFAULT '1' COMMENT '0-partial,1-full',
  `timestamp` DATETIME NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-new,1-done',
  PRIMARY KEY(`id`))
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
