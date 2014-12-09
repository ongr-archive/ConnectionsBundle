CREATE TABLE `ongr_sync_jobs`(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 status boolean not null default 0
);

INSERT INTO `ongr_sync_jobs` (`status`) VALUES
(1),
(0),
(1)
