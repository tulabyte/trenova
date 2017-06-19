-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `course_bundle`;
CREATE TABLE `course_bundle` (
  `bdl_id` int(11) NOT NULL AUTO_INCREMENT,
  `bdl_name` varchar(100) NOT NULL,
  `bdl_description` varchar(500) NOT NULL,
  `bdl_type` enum('CUSTOM','TERM','CLASS','YEAR') NOT NULL,
  `bdl_price` double(10,2) NOT NULL,
  `bdl_subject_id` int(11) DEFAULT NULL,
  `bdl_school_id` int(11) DEFAULT NULL,
  `bdl_created_by` int(11) NOT NULL DEFAULT '0',
  `bdl_creator` varchar(50) NOT NULL,
  `bdl_date_created` date NOT NULL,
  `bdl_is_disabled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bdl_id`),
  KEY `bdl_created_by` (`bdl_created_by`),
  KEY `bdl_subject_id` (`bdl_subject_id`),
  KEY `bdl_school_id` (`bdl_school_id`),
  CONSTRAINT `course_bundle_ibfk_3` FOREIGN KEY (`bdl_subject_id`) REFERENCES `subject` (`sb_id`),
  CONSTRAINT `course_bundle_ibfk_4` FOREIGN KEY (`bdl_school_id`) REFERENCES `school` (`sch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `course_bundle` (`bdl_id`, `bdl_name`, `bdl_description`, `bdl_type`, `bdl_price`, `bdl_subject_id`, `bdl_school_id`, `bdl_created_by`, `bdl_creator`, `bdl_date_created`, `bdl_is_disabled`) VALUES
(2,	'Mathematics 1st Term',	'1st Term Mathematics For Primary School',	'TERM',	15000.00,	2,	1,	5,	'Yemi Tula',	'2017-06-14',	0),
(4,	'New Bundle',	'Just Created New Bundle',	'TERM',	15000.00,	2,	3,	5,	'Yemi Tula',	'2017-06-15',	0),
(5,	'Another New Bundle',	'Just Created New Bundle',	'TERM',	15000.00,	2,	3,	5,	'Yemi Tula',	'2017-06-15',	0),
(6,	'My Term Bundle.',	'Describing My Bundle.',	'TERM',	5000001.00,	2,	3,	5,	'Yemi Tula',	'2017-06-16',	0),
(12,	'Course Bundle',	'Description For Course Bundle.',	'CUSTOM',	10000.00,	NULL,	NULL,	5,	'Yemi Tula',	'2017-06-16',	0);

DROP TABLE IF EXISTS `course_bundle_item`;
CREATE TABLE `course_bundle_item` (
  `cbi_bundle_id` int(11) NOT NULL,
  `cbi_course_id` int(11) NOT NULL,
  PRIMARY KEY (`cbi_bundle_id`,`cbi_course_id`),
  KEY `cbi_course_id` (`cbi_course_id`),
  CONSTRAINT `course_bundle_item_ibfk_1` FOREIGN KEY (`cbi_bundle_id`) REFERENCES `course_bundle` (`bdl_id`) ON DELETE CASCADE,
  CONSTRAINT `course_bundle_item_ibfk_2` FOREIGN KEY (`cbi_course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `course_bundle_item` (`cbi_bundle_id`, `cbi_course_id`) VALUES
(2,	3),
(12,	3),
(12,	4),
(2,	5),
(12,	5),
(12,	6),
(2,	8);

-- 2017-06-19 13:54:04
