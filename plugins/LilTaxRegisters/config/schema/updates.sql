CREATE TABLE `business_premises` (
 `id` char(36) NOT NULL,
 `owner_id` char(36) DEFAULT NULL,
 `issuer_taxno` char(8) DEFAULT NULL,
 `no` char(20) DEFAULT NULL,
 `title` varchar(255) DEFAULT NULL,
 `kind` char(2) NOT NULL DEFAULT 'RL' COMMENT 'ReaLestate or MOvable',
 `casadral_number` char(4) DEFAULT NULL,
 `building_number` char(5) DEFAULT NULL,
 `building_section_number` char(4) DEFAULT NULL,
 `street` varchar(100) DEFAULT NULL,
 `house_number` varchar(10) DEFAULT NULL,
 `house_number_additional` varchar(10) DEFAULT NULL,
 `community` varchar(100) DEFAULT NULL,
 `city` varchar(40) DEFAULT NULL,
 `postal_code` char(4) DEFAULT NULL,
 `mo_type` char(1) DEFAULT NULL,
 `validity_date` date DEFAULT NULL,
 `closed` tinyint(1) NOT NULL DEFAULT '0',
 `sw_taxno` varchar(8) DEFAULT NULL,
 `active` tinyint(1) NOT NULL DEFAULT '0',
 `last_request` text,
 `last_response` text,
 `notes` text,
 `created` datetime DEFAULT NULL,
 `modified` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
);

CREATE TABLE `invoices_taxconfirmations` (
 `id` char(36) NOT NULL,
 `invoice_id` char(36) DEFAULT NULL,
 `bp_no` char(20) DEFAULT NULL,
 `device_no` char(20) DEFAULT NULL,
 `issuer_taxno` char(9) DEFAULT NULL,
 `operator_taxno` char(9) DEFAULT NULL,
 `zoi` char(36) DEFAULT NULL,
 `qr` char(60) DEFAULT NULL,
 `eor` char(40) DEFAULT NULL,
 `last_request` text,
 `last_response` text,
 `created` datetime DEFAULT NULL,
 `modified` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
);

ALTER TABLE `invoices_counters` ADD `tax_confirmation` tinyint(1) NOT NULL DEFAULT '0' AFTER `tpl_footer_id`; 
ALTER TABLE `invoices_counters` ADD `business_premise_id` CHAR(36) NULL AFTER `tax_confirmation`; 
ALTER TABLE `invoices_counters` ADD `device_no` CHAR(20) NULL AFTER `business_premise_id`; 

ALTER TABLE `users` ADD `tax_no` CHAR(9) NULL AFTER `email`; 
ALTER TABLE `users` ADD `cert_p12` TEXT NULL AFTER `tax_no`; 

ALTER TABLE `companies` ADD `tax_no` CHAR(9) NULL AFTER `city`; 