CREATE TABLE `ongr_sync_jobs`(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 `status_shop1` boolean not null default 0,
 `status_shop2` boolean not null default 0,
 `status_shop3` boolean not null default 0
);

INSERT INTO `ongr_sync_jobs` (`status_shop1`, `status_shop2`, `status_shop3`) VALUES
(1, 1, 1),
(0, 1, 1),
(1, 1, 1),
(1, 0, 1),
(1, 1, 1);
