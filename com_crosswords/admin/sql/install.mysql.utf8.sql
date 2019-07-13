CREATE TABLE IF NOT EXISTS  `#__crosswords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` mediumtext,
  `catid` int(10) unsigned NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_alias` varchar(255) DEFAULT NULL,
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL,
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `questions` int(10) unsigned NOT NULL DEFAULT '15',
  `rows` int(10) unsigned NOT NULL DEFAULT '15',
  `columns` int(10) unsigned NOT NULL DEFAULT '15',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `solved` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` varchar(45) NOT NULL DEFAULT '*',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `attribs` varchar(5120) DEFAULT NULL,
  `version` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
  `metakey` text,
  `metadesc` text,
  `metadata` text,
  PRIMARY KEY (`id`),
  KEY `idx_crosswords_created_by` (`created_by`),
  KEY `idx_crosswords_catid` (`catid`),
  KEY `idx_crosswords_published` (`published`),
  KEY `idx_crosswords_checkout` (`checked_out`),
  KEY `idx_crosswords_access` (`access`),
  KEY `idx_crosswords_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci

CREATE TABLE IF NOT EXISTS  `#__crosswords_keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `keyword` varchar(32) NOT NULL,
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `language` varchar(45) NOT NULL DEFAULT '*',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_crosswords_keywords_created_by` (`created_by`),
  KEY `idx_crosswords_keywords_catid` (`catid`),
  KEY `idx_crosswords_keywords_published` (`published`),
  KEY `idx_crosswords_keywords_checkout` (`checked_out`),
  KEY `idx_crosswords_keywords_access` (`access`),
  KEY `idx_crosswords_keywords_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS  `#__crosswords_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL,
  `keyid` int(10) unsigned NOT NULL,
  `row` int(10) unsigned NOT NULL,
  `column` int(10) unsigned NOT NULL,
  `axis` tinyint(3) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci

CREATE TABLE IF NOT EXISTS  `#__crosswords_response_details` (
  `response_id` int(10) unsigned NOT NULL DEFAULT '0',
  `question_id` int(10) unsigned NOT NULL,
  `crossword_id` int(10) unsigned NOT NULL,
  `answer` varchar(32) NOT NULL,
  `valid` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci

CREATE TABLE IF NOT EXISTS  `#__crosswords_responses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `solved` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci