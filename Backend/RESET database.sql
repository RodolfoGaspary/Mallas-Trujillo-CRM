-- Delete all rows from tables
DELETE FROM `clientes`;
DELETE FROM `items`;
DELETE FROM `precios`;
DELETE FROM `proformas`;
DELETE FROM `proformas_items`;

-- Reset primary key numbers for tables
ALTER TABLE `clientes` AUTO_INCREMENT = 1;
ALTER TABLE `items` AUTO_INCREMENT = 1;
ALTER TABLE `precios` AUTO_INCREMENT = 1;
ALTER TABLE `proformas` AUTO_INCREMENT = 1;
ALTER TABLE `proformas_items` AUTO_INCREMENT = 1;