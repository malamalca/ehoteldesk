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
  `closed` tinyint(1) NOT NULL DEFAULT 0,
  `sw_taxno` varchar(8) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `last_request` text DEFAULT NULL,
  `last_response` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `companies` (
  `id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `tax_no` varchar(15) DEFAULT NULL,
  `tax_status` tinyint(1) NOT NULL DEFAULT 1,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` (`id`, `name`, `street`, `zip`, `city`, `tax_no`, `tax_status`, `created`, `modified`) VALUES
('e19036b2-103d-4fd1-9254-d10bb11616a3', 'Testna Enota', 'Počitniška 12', '1000', 'Ljubljana', '10039953', 0, '2018-04-13 10:27:10', '2018-06-10 16:50:07');

CREATE TABLE `contacts` (
  `id` char(36) NOT NULL,
  `owner_id` char(36) DEFAULT NULL,
  `no` int(11) NOT NULL,
  `kind` varchar(1) NOT NULL DEFAULT 'T',
  `name` varchar(255) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `sex` char(1) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `descript` text DEFAULT NULL,
  `mat_no` varchar(13) DEFAULT NULL,
  `tax_no` varchar(50) DEFAULT NULL,
  `tax_status` tinyint(1) DEFAULT NULL,
  `company_id` char(36) DEFAULT NULL,
  `job` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `plob` varchar(255) DEFAULT NULL,
  `nationality` char(2) DEFAULT NULL,
  `syncable` tinyint(1) NOT NULL DEFAULT 1,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contacts_accounts` (
  `id` char(36) NOT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `kind` varchar(1) DEFAULT NULL,
  `iban` varchar(255) DEFAULT NULL,
  `bic` varchar(8) DEFAULT NULL,
  `bank` varchar(255) DEFAULT NULL,
  `primary` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contacts_addresses` (
  `id` char(36) NOT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `kind` varchar(1) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `primary` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contacts_emails` (
  `id` char(36) NOT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `kind` varchar(1) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `primary` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contacts_phones` (
  `id` char(36) NOT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `kind` varchar(1) DEFAULT NULL,
  `no` varchar(255) DEFAULT NULL,
  `primary` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `counters` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `invoices_counter_id` char(36) DEFAULT NULL,
  `kind` char(1) DEFAULT NULL,
  `no` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `counter` int(11) NOT NULL DEFAULT 0,
  `primary` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `counters` (`id`, `company_id`, `invoices_counter_id`, `kind`, `no`, `title`, `template`, `counter`, `primary`, `created`, `modified`) VALUES
('5dcb5ddd-f7bf-4454-84d2-7e60a15acc9c', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '52d7ba87-44be-4af6-88c0-f0ca5353d69f', 'V', 0, 'Hotel pod Borovci', '%d', 10, 0, '2018-04-13 10:27:10', '2020-09-24 10:50:34');

CREATE TABLE `eturizem_logs` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `status` mediumint(4) DEFAULT NULL,
  `xml` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices` (
  `id` char(36) NOT NULL,
  `owner_id` char(36) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `counter_id` char(36) NOT NULL,
  `project_id` char(36) DEFAULT NULL,
  `reservation_id` char(36) DEFAULT NULL,
  `doc_type` varchar(5) DEFAULT NULL,
  `tpl_header_id` char(36) DEFAULT NULL,
  `tpl_body_id` char(36) DEFAULT NULL,
  `tpl_footer_id` char(36) DEFAULT NULL,
  `invoices_attachment_count` int(11) NOT NULL DEFAULT 0,
  `counter` int(11) DEFAULT NULL,
  `no` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `descript` text DEFAULT NULL,
  `signed` text DEFAULT NULL,
  `dat_sign` datetime DEFAULT NULL,
  `dat_issue` date DEFAULT NULL,
  `dat_service` date DEFAULT NULL,
  `dat_expire` date DEFAULT NULL,
  `dat_approval` date DEFAULT NULL,
  `net_total` decimal(15,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `inversed_tax` tinyint(1) NOT NULL DEFAULT 0,
  `pmt_kind` int(11) DEFAULT NULL,
  `pmt_sepa_type` varchar(10) DEFAULT NULL,
  `pmt_type` varchar(10) DEFAULT NULL,
  `pmt_module` varchar(4) DEFAULT NULL,
  `pmt_ref` varchar(35) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices_attachments` (
  `id` char(36) NOT NULL,
  `invoice_id` char(36) DEFAULT NULL,
  `foreign_id` char(36) DEFAULT NULL,
  `model` varchar(20) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `original` varchar(255) DEFAULT NULL,
  `ext` varchar(20) DEFAULT NULL,
  `mimetype` varchar(30) DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,
  `height` int(4) DEFAULT NULL,
  `width` int(4) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices_clients` (
  `id` char(36) NOT NULL,
  `invoice_id` char(36) DEFAULT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `kind` varchar(2) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zip` varchar(35) DEFAULT NULL,
  `country` varchar(35) DEFAULT NULL,
  `country_code` varchar(35) DEFAULT NULL,
  `iban` varchar(35) DEFAULT NULL,
  `bank` varchar(255) DEFAULT NULL,
  `tax_no` varchar(35) DEFAULT NULL,
  `mat_no` varchar(35) DEFAULT NULL,
  `person` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices_counters` (
  `id` char(36) NOT NULL,
  `owner_id` char(36) DEFAULT NULL,
  `kind` varchar(20) DEFAULT NULL,
  `doc_type` varchar(5) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `counter` int(11) NOT NULL DEFAULT 0,
  `mask` varchar(255) DEFAULT NULL,
  `template_descript` text DEFAULT NULL,
  `tpl_header_id` char(36) DEFAULT NULL,
  `tpl_body_id` char(36) DEFAULT NULL,
  `tpl_footer_id` char(36) DEFAULT NULL,
  `tax_confirmation` tinyint(1) NOT NULL DEFAULT 0,
  `business_premise_id` char(36) DEFAULT NULL,
  `device_no` char(20) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices_items` (
  `id` int(11) NOT NULL,
  `invoice_id` varchar(36) DEFAULT NULL,
  `item_id` char(36) DEFAULT NULL,
  `vat_id` char(36) DEFAULT NULL,
  `vat_title` varchar(255) DEFAULT NULL,
  `vat_percent` decimal(8,1) NOT NULL DEFAULT 0.0,
  `descript` varchar(255) DEFAULT NULL,
  `qty` decimal(17,4) NOT NULL DEFAULT 0.0000,
  `unit` varchar(10) DEFAULT NULL,
  `price` decimal(17,4) NOT NULL DEFAULT 0.0000,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices_links` (
  `id` char(36) NOT NULL,
  `link_id` char(36) DEFAULT NULL,
  `invoice_id` char(36) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `error_code` mediumint(4) DEFAULT NULL,
  `last_request` text DEFAULT NULL,
  `last_response` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices_taxes` (
  `id` int(11) NOT NULL,
  `invoice_id` char(36) DEFAULT NULL,
  `vat_id` char(36) DEFAULT NULL,
  `vat_title` varchar(255) DEFAULT NULL,
  `vat_percent` decimal(8,1) DEFAULT NULL,
  `base` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices_templates` (
  `id` char(36) NOT NULL,
  `owner_id` char(36) DEFAULT NULL,
  `kind` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `main` int(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `invoices_templates` (`id`, `owner_id`, `kind`, `title`, `body`, `main`, `created`, `modified`) VALUES
('8e90e66e-8f27-439b-9ff8-564bf0deb389', 'e19036b2-103d-4fd1-9254-d10bb11616a3', 'header', 'Privzeta glava', '<table width=\"100%\">\r\n<tr>\r\n<td width=\"33%\">Firma d.o.o.<br />Delavska cesta 12<br/>1000 Ljubljana</td>\r\n<td width=\"33%\">Center</td>\r\n<td width=\"33%\">Right</td>\r\n</tr>\r\n</table>', 1, '2018-06-11 17:09:04', '2018-06-11 17:12:17');

CREATE TABLE `items` (
  `id` char(36) NOT NULL,
  `owner_id` char(36) DEFAULT NULL,
  `vat_id` char(36) DEFAULT NULL,
  `descript` varchar(255) DEFAULT NULL,
  `qty` decimal(17,4) NOT NULL DEFAULT 0.0000,
  `unit` varchar(10) DEFAULT NULL,
  `price` decimal(17,4) NOT NULL DEFAULT 0.0000,
  `discount` decimal(15,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `registrations` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `counter_id` char(36) DEFAULT NULL,
  `client_id` char(36) DEFAULT NULL,
  `client_no` int(11) DEFAULT NULL,
  `room_id` char(36) DEFAULT NULL,
  `service_id` char(36) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `sex` char(1) NOT NULL DEFAULT 'M',
  `street` varchar(255) DEFAULT NULL,
  `zip` varchar(20) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country_code` char(2) NOT NULL,
  `dob` date DEFAULT NULL,
  `plob` varchar(255) DEFAULT NULL,
  `nationality_code_code` char(2) DEFAULT NULL,
  `kind` char(1) NOT NULL DEFAULT 'R',
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `ident_kind` char(1) DEFAULT NULL,
  `ident_no` varchar(200) DEFAULT NULL,
  `ttax_kind` char(2) DEFAULT NULL,
  `ttax_amount` decimal(15,2) DEFAULT NULL,
  `etur_guid` char(36) DEFAULT NULL,
  `etur_time` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservations` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `counter_id` char(36) DEFAULT NULL,
  `room_id` char(36) DEFAULT NULL,
  `client_id` char(36) DEFAULT NULL,
  `no` varchar(255) DEFAULT NULL,
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `persons` mediumint(4) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `country_code` char(2) DEFAULT NULL,
  `descript` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rooms` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `room_type_id` char(36) DEFAULT NULL,
  `vat_id` char(36) DEFAULT NULL,
  `no` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `beds` mediumint(4) NOT NULL DEFAULT 0,
  `priceperday` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rooms` (`id`, `company_id`, `room_type_id`, `vat_id`, `no`, `title`, `beds`, `priceperday`, `created`, `modified`) VALUES
('21ad4f2d-8e2a-460c-8064-a85a055c72f1', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '205b633d-1296-402c-b644-566b795218e3', '90d883df-5a50-4a75-9b86-3c2b6ed78150', '102', 'Soba v nadstropju', 2, '0.00', '2020-09-23 15:38:34', '2020-09-23 15:38:34'),
('8b4e15e0-d693-455c-bbfb-59b327761303', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '205b633d-1296-402c-b644-566b795218e3', '90d883df-5a50-4a75-9b86-3c2b6ed78150', '101', 'Soba v prvem nadstropju', 3, '123.00', '2018-04-13 10:40:02', '2020-09-23 14:19:40');

CREATE TABLE `room_types` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `room_types` (`id`, `company_id`, `title`, `created`, `modified`) VALUES
('1ba613e0-a5c9-49dd-a220-2d0b7742c2a1', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '1. enoposteljna soba', '2016-10-21 07:11:51', '2016-11-19 17:53:20'),
('34375a9e-32be-4ce5-8bea-7549880cc6bd', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '4. Apartma', '2016-11-19 17:15:22', '2016-11-19 17:15:22'),
('906d0ca4-c53e-4c9e-b463-87e998eb401c', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '5. triposteljna soba', '2016-11-21 12:31:52', '2017-03-15 20:52:48'),
('d0771078-1561-498d-be5b-8907ca01e09b', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '3. triposteljna soba', '2016-10-25 10:52:29', '2016-10-25 10:52:29'),
('e2923e7f-8d3f-4d03-afac-b21aed8c586e', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '2. dvoposteljna soba', '2016-10-21 07:12:00', '2016-10-21 16:47:22');

CREATE TABLE `service_types` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NULL,
  `title` varchar(255) NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `service_types` (`id`, `company_id`, `title`, `created`, `modified`) VALUES
('198bcc88-1b66-4bd5-8a0f-f8c910a2affc', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '03. VEČERJA', '2016-10-21 07:48:44', '2016-10-21 07:48:44'),
('1b5f10e7-a547-414e-bdfc-cb45637cbb13', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '10. ZAJTRK, VEČERJA', '2016-10-21 07:49:01', '2016-10-21 07:49:01'),
('3e593da4-c003-4940-a12a-653727d89154', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '11. ZAJTRK, KOSILO', '2016-10-21 07:49:18', '2016-10-21 07:49:18'),
('499b2846-e6cc-451d-b96d-bbb88571931d', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '99. ALL INCLUSIVE', '2016-10-23 05:04:30', '2016-10-23 05:05:00'),
('51458607-3ca2-447f-a3fc-dbe388b6e0c1', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '90. ZAJTRK, KOSILO, VEČERJA', '2016-10-21 07:49:57', '2016-10-21 07:49:57'),
('7394239c-a17b-41fd-bf04-a87dbfc6b07c', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '01. ZAJTRK', '2016-10-21 07:48:24', '2016-10-21 07:48:24'),
('9bf1e63e-f8d9-4892-b588-6be2c3ddec9a', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '02. KOSILO', '2016-10-21 07:48:34', '2016-10-21 07:48:34'),
('f34b7882-405f-4fff-aadc-40cf0ff8d8c6', 'e19036b2-103d-4fd1-9254-d10bb11616a3', '12. KOSILO, VEČERJA', '2016-10-21 07:49:35', '2016-10-21 07:49:35');

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `passwd` varchar(100) DEFAULT NULL,
  `cert_p12` text NULL,
  `etur_p12` text NULL,
  `etur_username` varchar(50) NULL,
  `etur_password` varchar(50) NULL,
  `email` varchar(200) DEFAULT NULL,
  `reset_key` varchar(200) DEFAULT NULL,
  `privileges` int(4) NOT NULL DEFAULT 10,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `company_id`, `name`, `username`, `passwd`, `cert_p12`, `etur_p12`, `etur_username`, `etur_password`, `email`, `reset_key`, `privileges`, `active`, `created`, `modified`) VALUES
('55c2c170-4b24-4997-9850-2198f5f5032f', 'e19036b2-103d-4fd1-9254-d10bb11616a3', 'Admin User', 'test', '', null, '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', 'apiTest', 'Test123!', 'test@test.si', NULL, 2, 1, '2018-04-13 10:27:10', '2020-09-24 11:01:54');

CREATE TABLE `vats` (
  `id` char(36) NOT NULL,
  `owner_id` char(36) DEFAULT NULL,
  `descript` varchar(255) DEFAULT NULL,
  `percent` decimal(8,1) NOT NULL DEFAULT 0.0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `vats` (`id`, `owner_id`, `descript`, `percent`, `created`, `modified`) VALUES
('90d883df-5a50-4a75-9b86-3c2b6ed78150', 'e19036b2-103d-4fd1-9254-d10bb11616a3', 'DDV 9,5%', '9.5', '2018-04-13 11:23:18', '2018-06-07 12:38:31'),
('cea1d29b-9941-11e6-9b1e-b8ac6f7cbae5', 'e19036b2-103d-4fd1-9254-d10bb11616a3', 'DDV 22%', '22.0', NULL, '2016-11-19 17:06:45');


ALTER TABLE `business_premises`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contacts`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `IX_ID` (`id`),
  ADD KEY `IX_OWNER` (`owner_id`,`kind`);

ALTER TABLE `contacts_accounts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contacts_addresses`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contacts_emails`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contacts_phones`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `counters`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `eturizem_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_attachments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_clients`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_counters`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_items`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_links`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_taxconfirmations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_taxes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `invoices_templates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IX_DATE` (`company_id`,`start`,`room_id`);

ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `vats`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contacts`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoices_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoices_taxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
