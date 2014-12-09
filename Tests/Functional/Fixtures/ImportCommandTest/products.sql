CREATE TABLE `test_products` (
   id INT NOT NULL,
   title VARCHAR(100),
   description VARCHAR(100),
   price FLOAT,
   location VARCHAR(100),
   PRIMARY KEY (id)
);
-- dummy records used to test deletion
INSERT INTO `test_products` (`id`, `title`, `description`, `price`, `location`) VALUES ('1', 'test_prod', 'test_desc', 0.99, '' );
INSERT INTO `test_products` (`id`, `title`, `description`, `price`, `location`) VALUES ('2', 'test_prod2', 'test_desc2', 7.79, '' );
