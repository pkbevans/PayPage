drop table if exists orderitems;
ALTER TABLE `orders`
    add column `orderDetails` MEDIUMTEXT NULL;