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



ALTER TABLE `lrs_manage`.`order_goods`     ADD COLUMN `type` TINYINT(4) DEFAULT '1' NULL AFTER `goods_price`;

ALTER TABLE `lrs_manage`.`order`
ADD COLUMN `prepay_id` VARCHAR(80) NULL COMMENT '第三方支付订单' AFTER `order_sn`,
CHANGE `order_sn` `order_sn` VARCHAR(80) NOT NULL COMMENT '订单号';


ALTER TABLE `lrs_manage`.`users`
ADD COLUMN `lon` DECIMAL(8,6) DEFAULT '0.0' NULL COMMENT '经度' AFTER `two_way`,
ADD COLUMN `lat` DECIMAL(8,6) DEFAULT '0.0' NULL COMMENT '维度' AFTER `lon`;


ALTER TABLE `lrs_manage`.`store`
ADD COLUMN `lon` DECIMAL(8,6) DEFAULT '0' NOT NULL AFTER `check`,
ADD COLUMN `lat` DECIMAL(8,6) DEFAULT '0' NOT NULL AFTER `lon`;


ALTER TABLE `lrs_manage`.`goods`
ADD COLUMN `tag` VARCHAR(100) NULL COMMENT '默认标签' AFTER `image`;


ALTER TABLE `lrs_manage`.`goods_sku`
 ADD COLUMN `is_act` TINYINT(4) DEFAULT '0' NULL COMMENT '是否默认' AFTER `is_del`;

ALTER TABLE `lrs_manage`.`order_goods`
ADD COLUMN `image` VARCHAR(100) NULL COMMENT '商品图' AFTER `type`,
ADD COLUMN `tag` VARCHAR(100) NULL COMMENT '商品标签' AFTER `image`;

ALTER TABLE `lrs_manage`.`goods_category`
ADD COLUMN `count` INT(10) DEFAULT '0' NULL COMMENT '商品数量' AFTER `company_id`;

/*ALTER TABLE `lrs_manage`.`users`
CHANGE `real_name` `real_name` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' NOT NULL COMMENT '真实姓名',
CHANGE `nickname` `nickname` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' NOT NULL COMMENT '昵称',
CHANGE `email` `email` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' NOT NULL COMMENT '邮箱',
CHANGE `icon` `icon` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' NOT NULL COMMENT '头像',
CHANGE `channel_id` `channel_id` INT(10) DEFAULT '0' NOT NULL,     CHANGE `online` `online` TINYINT(4) DEFAULT '0' NOT NULL COMMENT '是否在线',
CHANGE `play` `play` TINYINT(4) DEFAULT '0' NOT NULL COMMENT '是否在游戏',
CHANGE `two_way` `two_way` TINYINT(4) DEFAULT '0' NOT NULL COMMENT '是否仅双向好友发消息',
CHANGE `lon` `lon` DECIMAL(8,6) DEFAULT '0.000000' NOT NULL COMMENT '经度',
CHANGE `lat` `lat` DECIMAL(8,6) DEFAULT '0.000000' NOT NULL COMMENT '维度';*/

//2020.7.27
ALTER TABLE `lrs_manage`.`goods_category`     ADD COLUMN `is_del` TINYINT(4) DEFAULT '0' NULL COMMENT '是否删除' AFTER `count`;

 ALTER TABLE `lrs_manage`.`goods_sku`
CHANGE `is_act` `is_act` TINYINT(4) DEFAULT '0' NOT NULL COMMENT '是否默认';

ALTER TABLE `lrs_manage`.`goods_image`
ADD COLUMN `store_id` INT(10) DEFAULT '0' NOT NULL COMMENT '门店id' AFTER `goods_id`,
ADD COLUMN `company_id` INT(10) DEFAULT '0' NOT NULL COMMENT '商家id' AFTER `store_id`,
CHANGE `created_at` `created_at` TIMESTAMP NULL ;

ALTER TABLE `lrs_manage`.`goods_image`
ADD COLUMN `is_del` TINYINT(4) DEFAULT '0' NULL COMMENT '是否删除' AFTER `company_id`;

ALTER TABLE `lrs_manage`.`goods`
ADD COLUMN `stock` INT(10) DEFAULT '0' NOT NULL COMMENT '库存' AFTER `category_id`;

ALTER TABLE `lrs_manage`.`goods_sku`
ADD COLUMN `stock` INT(10) DEFAULT '0' NOT NULL COMMENT '规格库存' AFTER `active`;

ALTER TABLE `lrs_manage`.`goods`
ADD COLUMN `daily_sales` INT(10) DEFAULT '0' NOT NULL COMMENT '日销量' AFTER `tag`,
ADD COLUMN `monthly_sales` INT(10) DEFAULT '0' NOT NULL COMMENT '月销量' AFTER `daily_sales`;


ALTER TABLE `lrs_game_logs`.`room_game_log`     ADD COLUMN `replayContentJson` TEXT NULL COMMENT '结果' AFTER `gameRes`;

ALTER TABLE `lrs_manage`.`goods`     CHANGE `goods_price` `goods_price` DECIMAL(8,2) NOT NULL COMMENT '商品单价';
ALTER TABLE `lrs_manage`.`goods_sku`     CHANGE `goods_price` `goods_price` DECIMAL(8,2) NOT NULL COMMENT '规格单价';


ALTER TABLE `lrs_manage`.`order_goods`     ADD COLUMN `goods_name` VARCHAR(50) NULL COMMENT '商品名称' AFTER `tag`;

ALTER TABLE `lrs_manage`.`order`     ADD COLUMN `info` VARCHAR(100) NOT NULL COMMENT '订单简讯' AFTER `prepay_id`;


 ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `token` VARCHAR(80) NULL COMMENT 'token' AFTER `lat`;
 ALTER TABLE `lrs_manage`.`users` ADD INDEX `api_token` (`token`);

 ALTER TABLE `lrs_manage`.`store`     ADD COLUMN `logo` VARCHAR(100) NOT NULL COMMENT 'logo' AFTER `store_name`;

 ALTER TABLE `lrs_manage`.`order`     ADD COLUMN `actual_payment` DECIMAL(10,2) NOT NULL COMMENT '真实支付' AFTER `total_price`;

 ALTER TABLE `lrs_manage`.`order`     ADD COLUMN `integral_price` DECIMAL(10,2) NOT NULL COMMENT '积分减额' AFTER `coupon_price`,
CHANGE `coupon_price` `coupon_price` DECIMAL(10,2) DEFAULT '0.00' NOT NULL COMMENT '券减金额';


 ALTER TABLE `lrs_manage`.`store`     ADD COLUMN `is_close` TINYINT(4) DEFAULT '0' NULL COMMENT '是否关闭' AFTER `lat`;

  ALTER TABLE `lrs_manage`.`order`     ADD COLUMN `is_abnormal` TINYINT(4) DEFAULT '0' NULL COMMENT '是否异常' AFTER `status`;


 ALTER TABLE `lrs_manage`.`users`     ADD COLUMN `phone` VARCHAR(20) NULL AFTER `lat`,     ADD COLUMN `area_code` VARCHAR(5) NULL AFTER `phone`;


 ALTER TABLE `lrs_manage`.`balance_water` DROP COLUMN `order_id`,    ADD COLUMN `order_sn` VARCHAR(80) NULL COMMENT '订单号' AFTER `balance_sn`;

//合并账号id
ALTER TABLE `lrs_manage`.`users` ADD COLUMN `parent_id` INT(10) DEFAULT '0' NOT NULL AFTER `token`;

//open_id,考虑到合并账号，支付需要用到
ALTER TABLE `lrs_manage`.`users` ADD COLUMN `open_id` VARCHAR(50) NULL COMMENT 'open_id' AFTER `parent_id`;

ALTER TABLE `lrs_manage`.`staff` ADD COLUMN `area_code` INT(10) DEFAULT '0' NULL COMMENT '地区码' AFTER `sex`;

 ALTER TABLE `lrs_manage`.`company` CHANGE `state_id` `area_code` INT(10) DEFAULT '0' NOT NULL COMMENT '地区码';


//8.25
 ALTER TABLE `lrs_manage`.`store`     ADD COLUMN `room_count` INT(10) DEFAULT '0' NOT NULL COMMENT '房间数量' AFTER `region_id`;

ALTER TABLE `lrs_manage`.`note_sms`     ADD COLUMN `res` json NULL COMMENT '结果' AFTER `action`;

//退款类型
 ALTER TABLE `lrs_manage`.`refund_order`
ADD COLUMN `refund_reason_type` TINYINT(4) DEFAULT '4' NOT NULL COMMENT '退款类型' AFTER `check_status`;
