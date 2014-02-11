-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 產生日期: 2013 年 06 月 21 日 16:34
-- 伺服器版本: 5.1.60
-- PHP 版本: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 資料庫: `mailscanner`
--

-- --------------------------------------------------------

--
-- 表的結構 `audit_log`
--

CREATE TABLE IF NOT EXISTS `audit_log` (
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL DEFAULT '',
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  `action` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `blacklist`
--

CREATE TABLE IF NOT EXISTS `blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_address` text,
  `to_domain` text,
  `from_address` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blacklist_uniq` (`to_address`(100),`from_address`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `geoip_country`
--

CREATE TABLE IF NOT EXISTS `geoip_country` (
  `begin_ip` varchar(15) DEFAULT NULL,
  `end_ip` varchar(15) DEFAULT NULL,
  `begin_num` bigint(20) DEFAULT NULL,
  `end_num` bigint(20) DEFAULT NULL,
  `iso_country_code` char(2) DEFAULT NULL,
  `country` text,
  KEY `geoip_country_begin` (`begin_num`),
  KEY `geoip_country_end` (`end_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `inq`
--

CREATE TABLE IF NOT EXISTS `inq` (
  `id` text,
  `cdate` date DEFAULT NULL,
  `ctime` time DEFAULT NULL,
  `from_address` text,
  `to_address` text,
  `subject` text,
  `message` text,
  `size` text,
  `priority` text,
  `attempts` text,
  `lastattempt` text,
  `hostname` text,
  KEY `inq_hostname` (`hostname`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `maillog`
--

CREATE TABLE IF NOT EXISTS `maillog` (
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` text,
  `size` bigint(20) DEFAULT '0',
  `from_address` text,
  `from_domain` text,
  `to_address` text,
  `to_domain` text,
  `subject` text,
  `clientip` text,
  `archive` text,
  `isspam` tinyint(1) DEFAULT '0',
  `ishighspam` tinyint(1) DEFAULT '0',
  `issaspam` tinyint(1) DEFAULT '0',
  `isrblspam` tinyint(1) DEFAULT '0',
  `isfp` tinyint(1) DEFAULT '0',
  `isfn` tinyint(1) DEFAULT '0',
  `spamwhitelisted` tinyint(1) DEFAULT '0',
  `spamblacklisted` tinyint(1) DEFAULT '0',
  `sascore` decimal(7,2) DEFAULT '0.00',
  `spamreport` text,
  `virusinfected` tinyint(1) DEFAULT '0',
  `nameinfected` tinyint(1) DEFAULT '0',
  `otherinfected` tinyint(1) DEFAULT '0',
  `report` text,
  `ismcp` tinyint(1) DEFAULT '0',
  `ishighmcp` tinyint(1) DEFAULT '0',
  `issamcp` tinyint(1) DEFAULT '0',
  `mcpwhitelisted` tinyint(1) DEFAULT '0',
  `mcpblacklisted` tinyint(1) DEFAULT '0',
  `mcpsascore` decimal(7,2) DEFAULT '0.00',
  `mcpreport` text,
  `hostname` text,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `headers` text,
  `quarantined` tinyint(1) DEFAULT '0',
  KEY `maillog_datetime_idx` (`date`,`time`),
  KEY `maillog_id_idx` (`id`(20)),
  KEY `maillog_clientip_idx` (`clientip`(20)),
  KEY `maillog_from_idx` (`from_address`(200)),
  KEY `maillog_to_idx` (`to_address`(200)),
  KEY `maillog_host` (`hostname`(30)),
  KEY `from_domain_idx` (`from_domain`(50)),
  KEY `to_domain_idx` (`to_domain`(50)),
  KEY `maillog_quarantined` (`quarantined`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `mcp_rules`
--

CREATE TABLE IF NOT EXISTS `mcp_rules` (
  `rule` char(100) NOT NULL DEFAULT '',
  `rule_desc` char(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`rule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `mtalog`
--

CREATE TABLE IF NOT EXISTS `mtalog` (
  `timestamp` datetime DEFAULT NULL,
  `host` text,
  `type` text,
  `msg_id` varchar(20) DEFAULT NULL,
  `relay` text,
  `dsn` text,
  `status` text,
  `delay` time DEFAULT NULL,
  UNIQUE KEY `mtalog_uniq` (`timestamp`,`host`(10),`type`(10),`msg_id`,`relay`(20)),
  KEY `mtalog_timestamp` (`timestamp`),
  KEY `mtalog_type` (`type`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `outq`
--

CREATE TABLE IF NOT EXISTS `outq` (
  `id` text,
  `cdate` date DEFAULT NULL,
  `ctime` time DEFAULT NULL,
  `from_address` text,
  `to_address` text,
  `subject` text,
  `message` text,
  `size` text,
  `priority` text,
  `attempts` text,
  `lastattempt` text,
  `hostname` text,
  KEY `outq_hostname` (`hostname`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `saved_filters`
--

CREATE TABLE IF NOT EXISTS `saved_filters` (
  `name` text NOT NULL,
  `col` text NOT NULL,
  `operator` text NOT NULL,
  `value` text NOT NULL,
  `username` text NOT NULL,
  UNIQUE KEY `unique_filters` (`name`(20),`col`(20),`operator`(20),`value`(20),`username`(20))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `sa_rules`
--

CREATE TABLE IF NOT EXISTS `sa_rules` (
  `rule` varchar(100) NOT NULL DEFAULT '',
  `rule_desc` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`rule`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `spamscores`
--

CREATE TABLE IF NOT EXISTS `spamscores` (
  `user` varchar(40) NOT NULL DEFAULT '',
  `lowspamscore` decimal(10,0) NOT NULL DEFAULT '0',
  `highspamscore` decimal(10,0) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(60) NOT NULL DEFAULT '',
  `password` varchar(32) DEFAULT NULL,
  `fullname` varchar(50) NOT NULL DEFAULT '',
  `type` enum('A','D','U','R','H') DEFAULT NULL,
  `quarantine_report` tinyint(1) DEFAULT '0',
  `spamscore` tinyint(4) DEFAULT '0',
  `highspamscore` tinyint(4) DEFAULT '0',
  `noscan` tinyint(1) DEFAULT '0',
  `quarantine_rcpt` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `user_filters`
--

CREATE TABLE IF NOT EXISTS `user_filters` (
  `username` varchar(60) NOT NULL DEFAULT '',
  `filter` text,
  `verify_key` varchar(32) NOT NULL DEFAULT '',
  `active` enum('N','Y') DEFAULT 'N',
  KEY `user_filters_username_idx` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的結構 `whitelist`
--

CREATE TABLE IF NOT EXISTS `whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_address` text,
  `to_domain` text,
  `from_address` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `whitelist_uniq` (`to_address`(100),`from_address`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
