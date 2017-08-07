ALTER TABLE `orders_status`
	CHANGE COLUMN `name` `name` VARCHAR(55) NOT NULL DEFAULT '' AFTER `id`;
