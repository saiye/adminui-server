 #users
 alter table `users` add `popularity` int unsigned not null default '0' comment '人气', add `attention` int unsigned not null default '0' comment '关注', add `fans` int unsigned not null default '0' comment '粉丝', add `remaining` decimal(2, 2) not null default '0' comment '余额', add `income` decimal(2, 2) not null default '0' comment '收入', add `withdrawal` decimal(2, 2) not null default '0' comment '已提现'


ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `channel_id` INT(10) NULL AFTER `withdrawal`;

ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `online` TINYINT(4) DEFAULT '0' NULL COMMENT '是否在线' AFTER `channel_id`;

ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `two_way` TINYINT(4) DEFAULT '0' NULL COMMENT '是否仅双向好友发消息' AFTER `play`;
