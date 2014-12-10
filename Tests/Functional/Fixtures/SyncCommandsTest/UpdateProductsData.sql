-- insert more data
INSERT INTO `test_products` (`id`, `title`, `description`, `price`, `location`)
VALUES
  (3, 'test_prod3', 'test_desc3', 0.3, ''),
  (4, 'test_prod4', 'test_desc4', 0.4, ''),
  (5, 'test_prod5', 'test_desc5', 0.5, ''),
  (6, 'test_prod6', 'test_desc6', 0.6, '');

-- update one record
UPDATE `test_products`
SET
  `title` = 'test product title 1',
  `description` = 'test product description 1'
WHERE
  `id` = '1';

-- delete one record
DELETE FROM `test_products`
WHERE
  `id` = '2';
