ALTER TABLE `users`
    add column `verificationCode` VARCHAR(256) default '' not null;