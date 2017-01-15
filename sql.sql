CREATE TABLE `cmf_admin_users` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_login` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '用户名',
	`user_pass` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '登录密码；sp_password加密',
	`mobile` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '手机号',
	`user_email` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '登录邮箱',
	`last_login_ip` VARCHAR(16) NULL DEFAULT NULL COMMENT '最后登录ip',
	`last_login_time` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '最后登录时间',
	`create_time` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '注册时间',
	`user_status` INT(11) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常 ',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `user_login` (`user_login`)
) COMMENT='管理组' COLLATE='utf8_general_ci' ENGINE=MyISAM;
