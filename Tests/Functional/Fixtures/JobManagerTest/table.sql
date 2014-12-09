CREATE TABLE ongr_sync_jobs (id integer NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `document_type` VARCHAR(255) NOT NULL,
  `document_id` VARCHAR(255) NOT NULL,
  `update_type` VARCHAR(255) NOT NULL,
  `timestamp` DATETIME NOT NULL,
  PRIMARY KEY(`id`))
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
