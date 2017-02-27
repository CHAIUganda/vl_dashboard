ALTER TABLE `vl_facility_printing` ADD `ready` ENUM( 'YES', 'NO' ) NOT NULL DEFAULT 'YES' AFTER `sample_id` ;
ALTER TABLE `vl_facility_printing` ADD `comments` VARCHAR(250) NULL AFTER `qc_by` ;