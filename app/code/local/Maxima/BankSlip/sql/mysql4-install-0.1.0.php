<?php
 
$installer = $this;
 
$installer->startSetup();

$installer->run
("
	DROP TABLE IF EXISTS `maxima_bankslip_slip`;
	CREATE TABLE `maxima_bankslip_slip`
	(
		`slip_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`order_id` int(10) unsigned NOT NULL,
		`created_at` datetime NOT NULL,
		`bank` varchar(20) DEFAULT NULL,
		`status` varchar(1) NOT NULL DEFAULT 'N',
		`return_file` int(10) unsigned DEFAULT NULL,
		PRIMARY KEY (`slip_id`),
		KEY `FK_maxima_bankslip_slip_file` (`return_file`),
		CONSTRAINT `FK_maxima_bankslip_slip_file` FOREIGN KEY (`return_file`) REFERENCES `maxima_bankslip_file` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
");

$installer->run
("
	DROP TABLE IF EXISTS `maxima_bankslip_file`;
	CREATE TABLE  `maxima_bankslip_file`
	(
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`date` datetime DEFAULT NULL,
		`name` varchar(200) DEFAULT NULL,
		`type` varchar(10) DEFAULT NULL,
		`content` longtext,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");
 
$installer->endSetup(); 
