CREATE TABLE `test_products` (
   id INT NOT NULL,
   title VARCHAR(100),
   description VARCHAR(100),
   price FLOAT,
   location VARCHAR(100),
   PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `test_products` (`id`, `title`, `description`, `price`, `location`)
VALUES
  ('1', 'test_prod1', 'test_desc1', 0.1, ''),
  ('2', 'test_prod2', 'test_desc2', 0.2, '');
