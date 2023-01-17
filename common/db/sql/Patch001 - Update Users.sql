ALTER TABLE `users`
    add column entry_fee float default 0.0 not null,
    add column `customerId` varchar(45) NOT NULL,
    add column `type` varchar(45) NOT NULL COMMENT 'CUSTOMER/INTERNAL',
    add column `admin` varchar(1) NOT NULL DEFAULT 'N';