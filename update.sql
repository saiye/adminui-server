 #users
 alter table `users` add `popularity` int unsigned not null default '0' comment '人气', add `attention` int unsigned not null default '0' comment '关注', add `fans` int unsigned not null default '0' comment '粉丝', add `remaining` decimal(2, 2) not null default '0' comment '余额', add `income` decimal(2, 2) not null default '0' comment '收入', add `withdrawal` decimal(2, 2) not null default '0' comment '已提现'


ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `channel_id` INT(10) NULL AFTER `withdrawal`;

ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `online` TINYINT(4) DEFAULT '0' NULL COMMENT '是否在线' AFTER `channel_id`;

ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `two_way` TINYINT(4) DEFAULT '0' NULL COMMENT '是否仅双向好友发消息' AFTER `play`;

 ALTER TABLE `lrs_manage`.`room`     ADD COLUMN `deviceMqttTopic` VARCHAR(100) NULL COMMENT '主题' AFTER `dup_id`;


 ALTER TABLE `lrs_manage`.`goods`     ADD COLUMN `category_id` INT(10) DEFAULT '0' NULL COMMENT '分类id' AFTER `company_id`;

 ALTER TABLE `lrs_manage`.`goods`     ADD COLUMN `image` VARCHAR(100) NULL AFTER `category_id`;

 ALTER TABLE `lrs_manage`.`order`     ADD COLUMN `coupon_id` INT(10) UNSIGNED NOT NULL COMMENT '券id' AFTER `play_time`;
 ALTER TABLE `lrs_manage`.`order`     ADD COLUMN `coupon_price` DECIMAL(8,3) NULL COMMENT '券减金额' AFTER `coupon_id`;



 ALTER TABLE `lrs_manage`.`store`
ADD COLUMN `close_at` TINYINT(4) DEFAULT '24' NULL AFTER `staff_id`,
ADD COLUMN `open_at` TINYINT(4) DEFAULT '0' NULL AFTER `close_at`;

ALTER TABLE `lrs_manage`.`goods`     ADD COLUMN `info` VARCHAR(80) NULL COMMENT '描述' AFTER `goods_price`;
