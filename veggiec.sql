-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: dedi3930.your-server.de
-- Erstellungszeit: 01. Jan 2021 um 14:38
-- Server-Version: 5.7.32-1
-- PHP-Version: 7.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `veggiec`
--
CREATE DATABASE IF NOT EXISTS `veggiec` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `veggiec`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ajax_chat_bans`
--

CREATE TABLE `ajax_chat_bans` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `userName` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varbinary(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ajax_chat_channels`
--

CREATE TABLE `ajax_chat_channels` (
  `channel_id` smallint(3) UNSIGNED NOT NULL,
  `channel_name` varchar(64) NOT NULL,
  `manual_channel` tinyint(1) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `invite_only` tinyint(1) UNSIGNED NOT NULL,
  `died` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ajax_chat_invitations`
--

CREATE TABLE `ajax_chat_invitations` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `channel` int(11) NOT NULL DEFAULT '0',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ajax_chat_messages`
--

CREATE TABLE `ajax_chat_messages` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `userName` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `userRole` int(1) NOT NULL DEFAULT '0',
  `channel` int(11) NOT NULL DEFAULT '0',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varbinary(16) DEFAULT NULL,
  `text` text COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ajax_chat_messages_archive`
--

CREATE TABLE `ajax_chat_messages_archive` (
  `id` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `userName` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `userRole` int(1) NOT NULL DEFAULT '0',
  `channel` int(11) NOT NULL DEFAULT '0',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varbinary(16) DEFAULT NULL,
  `text` text CHARACTER SET utf8 COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ajax_chat_messages_censored`
--

CREATE TABLE `ajax_chat_messages_censored` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `userName` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `userRole` int(1) NOT NULL DEFAULT '0',
  `channel` int(11) NOT NULL DEFAULT '0',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varbinary(16) DEFAULT NULL,
  `text` text COLLATE utf8_bin,
  `deleted_on` datetime NOT NULL,
  `moderator_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ajax_chat_online`
--

CREATE TABLE `ajax_chat_online` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `userName` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `userRole` int(1) NOT NULL DEFAULT '0',
  `chatMarker` varchar(50) COLLATE utf8_bin NOT NULL,
  `channel` int(11) NOT NULL DEFAULT '0',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varbinary(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `geoip_country`
--

CREATE TABLE `geoip_country` (
  `ip_from` int(10) UNSIGNED NOT NULL,
  `ip_to` int(10) UNSIGNED NOT NULL,
  `iso2` char(2) NOT NULL,
  `updated` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_activation_token`
--

CREATE TABLE `vc_activation_token` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `token` varchar(25) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `created_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_activity`
--

CREATE TABLE `vc_activity` (
  `id` int(11) UNSIGNED NOT NULL,
  `profileid` int(11) UNSIGNED NOT NULL,
  `activity_type` int(10) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `message` varchar(500) DEFAULT NULL,
  `related_profileid` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_banned_email`
--

CREATE TABLE `vc_banned_email` (
  `email` varchar(255) NOT NULL,
  `added_at` datetime NOT NULL,
  `last_occurrence` datetime NOT NULL,
  `count` smallint(5) UNSIGNED NOT NULL,
  `by_admin` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_banned_picture`
--

CREATE TABLE `vc_banned_picture` (
  `profile_id` int(11) NOT NULL,
  `filehash` char(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_blocked`
--

CREATE TABLE `vc_blocked` (
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `blocked_id` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `blocked_by_admin` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_blocked_login`
--

CREATE TABLE `vc_blocked_login` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `blocked_till` datetime NOT NULL,
  `reason` varchar(250) DEFAULT NULL,
  `blocked_by` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_change_pw_token`
--

CREATE TABLE `vc_change_pw_token` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `token` varchar(25) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `created_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_country`
--

CREATE TABLE `vc_country` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name_de` varchar(25) DEFAULT NULL,
  `name_en` varchar(25) DEFAULT NULL,
  `iso2` char(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_cron_log`
--

CREATE TABLE `vc_cron_log` (
  `task_name` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `duration` smallint(5) UNSIGNED NOT NULL,
  `debug_info` text NOT NULL,
  `test_mode` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_custom_design`
--

CREATE TABLE `vc_custom_design` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `colors` text NOT NULL,
  `css` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_event`
--

CREATE TABLE `vc_event` (
  `id` int(11) UNSIGNED NOT NULL,
  `hash_id` char(7) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `group_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `image` varchar(25) DEFAULT NULL,
  `location_caption` varchar(255) NOT NULL,
  `location_street` varchar(100) DEFAULT NULL,
  `location_postal` varchar(8) DEFAULT NULL,
  `location_city` varchar(50) DEFAULT NULL,
  `location_region` char(3) DEFAULT NULL,
  `location_country` int(11) UNSIGNED DEFAULT NULL,
  `location_lat` double(10,6) NOT NULL,
  `location_lng` double(10,6) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `fb_url` varchar(255) DEFAULT NULL,
  `event_visibility` tinyint(1) UNSIGNED NOT NULL,
  `guest_visibility` tinyint(1) UNSIGNED NOT NULL,
  `can_guest_invite` tinyint(1) UNSIGNED NOT NULL,
  `category_id` tinyint(3) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `deleted_by` int(11) UNSIGNED DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `feed_thread_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_event_participant`
--

CREATE TABLE `vc_event_participant` (
  `event_id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `degree` tinyint(2) UNSIGNED NOT NULL,
  `is_host` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_faq`
--

CREATE TABLE `vc_faq` (
  `id` int(7) UNSIGNED NOT NULL,
  `locale` varchar(3) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_favorite`
--

CREATE TABLE `vc_favorite` (
  `profileid` int(11) NOT NULL DEFAULT '0',
  `favoriteid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_feed_thread`
--

CREATE TABLE `vc_feed_thread` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `thread_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_flag`
--

CREATE TABLE `vc_flag` (
  `id` int(11) NOT NULL,
  `hash_id` char(7) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `entity_type` tinyint(3) UNSIGNED NOT NULL,
  `entity_id` int(11) UNSIGNED NOT NULL,
  `aggregate_type` tinyint(3) UNSIGNED NOT NULL,
  `aggregate_id` int(11) UNSIGNED DEFAULT NULL,
  `comment` text,
  `flagged_by` int(11) UNSIGNED NOT NULL,
  `flagged_at` datetime NOT NULL,
  `processed_by` int(11) UNSIGNED DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_forum_thread`
--

CREATE TABLE `vc_forum_thread` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hash_id` char(7) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `context_type` tinyint(3) UNSIGNED NOT NULL,
  `context_id` int(11) UNSIGNED NOT NULL,
  `thread_type` tinyint(3) UNSIGNED NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text,
  `additional` varchar(800) DEFAULT NULL,
  `picture` char(24) DEFAULT NULL,
  `is_official` tinyint(1) UNSIGNED NOT NULL,
  `is_sticky` tinyint(1) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_by` int(11) UNSIGNED DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `content_updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_forum_thread_comment`
--

CREATE TABLE `vc_forum_thread_comment` (
  `id` int(11) UNSIGNED NOT NULL,
  `hash_id` char(7) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `thread_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `is_official` tinyint(1) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_by` int(11) UNSIGNED DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_friend`
--

CREATE TABLE `vc_friend` (
  `friend1id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `friend1_publiccomment` text,
  `friend2id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `friend2_publiccomment` text,
  `friend2_accepted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `feed_thread_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_full_log`
--

CREATE TABLE `vc_full_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL,
  `url` varchar(250) NOT NULL,
  `parameters` text NOT NULL,
  `method` tinyint(3) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_geoname`
--

CREATE TABLE `vc_geoname` (
  `country_id` int(11) UNSIGNED NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `place_name` varchar(180) NOT NULL,
  `latitude` double(10,6) NOT NULL,
  `longitude` double(10,6) NOT NULL,
  `accuracy` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group`
--

CREATE TABLE `vc_group` (
  `id` int(11) UNSIGNED NOT NULL,
  `hash_id` char(7) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `image` varchar(25) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `rules` text,
  `mod_message` text NOT NULL,
  `latitude` double(10,6) DEFAULT NULL,
  `longitude` double(10,6) DEFAULT NULL,
  `language` char(2) NOT NULL,
  `member_visibility` tinyint(1) UNSIGNED NOT NULL,
  `auto_confirm_members` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `activity` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `confirmed_by` int(11) UNSIGNED DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `deleted_by` int(11) UNSIGNED DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_activity`
--

CREATE TABLE `vc_group_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  `entity_type` tinyint(1) UNSIGNED NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_ban`
--

CREATE TABLE `vc_group_ban` (
  `id` int(11) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `banned_by` int(11) UNSIGNED NOT NULL,
  `banned_at` datetime NOT NULL,
  `reason` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_forum`
--

CREATE TABLE `vc_group_forum` (
  `id` int(11) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  `hash_id` char(7) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `content_visibility` tinyint(1) UNSIGNED NOT NULL,
  `is_main` tinyint(1) UNSIGNED NOT NULL,
  `weight` tinyint(3) UNSIGNED NOT NULL,
  `deleted_by` int(11) UNSIGNED DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_invitation`
--

CREATE TABLE `vc_group_invitation` (
  `group_id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `comment` text,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_member`
--

CREATE TABLE `vc_group_member` (
  `group_id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `confirmed_by` int(11) UNSIGNED DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_notification`
--

CREATE TABLE `vc_group_notification` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `notification_type` tinyint(3) UNSIGNED NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `seen_at` datetime DEFAULT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_role`
--

CREATE TABLE `vc_group_role` (
  `group_id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `role` tinyint(1) UNSIGNED NOT NULL,
  `granted_by` int(11) UNSIGNED DEFAULT NULL,
  `granted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_group_setting`
--

CREATE TABLE `vc_group_setting` (
  `group_id` int(11) UNSIGNED NOT NULL,
  `setting` tinyint(3) UNSIGNED NOT NULL,
  `value` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_help_notification`
--

CREATE TABLE `vc_help_notification` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_hobby`
--

CREATE TABLE `vc_hobby` (
  `id` int(11) NOT NULL,
  `groupid` int(11) NOT NULL DEFAULT '0',
  `name_de` varchar(50) NOT NULL,
  `description_de` varchar(250) NOT NULL,
  `name_en` varchar(50) NOT NULL,
  `description_en` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_hobby_group`
--

CREATE TABLE `vc_hobby_group` (
  `id` int(11) NOT NULL,
  `name_de` varchar(30) NOT NULL,
  `name_en` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_javascript_log`
--

CREATE TABLE `vc_javascript_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `line` smallint(5) UNSIGNED NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_keyword`
--

CREATE TABLE `vc_keyword` (
  `field` varchar(6) NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  `locale` varchar(3) NOT NULL,
  `keyword` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_last_visitor`
--

CREATE TABLE `vc_last_visitor` (
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `visitor_id` int(11) NOT NULL DEFAULT '0',
  `last_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_like`
--

CREATE TABLE `vc_like` (
  `entity_type` tinyint(3) UNSIGNED NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `up_down` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_link`
--

CREATE TABLE `vc_link` (
  `id` int(11) NOT NULL,
  `linkgroup` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `href` varchar(255) NOT NULL,
  `locale` varchar(3) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_link_group`
--

CREATE TABLE `vc_link_group` (
  `id` int(11) NOT NULL,
  `description_de` varchar(25) NOT NULL,
  `description_en` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_match`
--

CREATE TABLE `vc_match` (
  `min_user_id` int(11) UNSIGNED NOT NULL,
  `max_user_id` int(11) UNSIGNED NOT NULL,
  `percentage` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_matching`
--

CREATE TABLE `vc_matching` (
  `user_id` int(11) NOT NULL,
  `adventure` tinyint(1) UNSIGNED NOT NULL,
  `bed_ds` tinyint(1) UNSIGNED NOT NULL,
  `calm` tinyint(1) UNSIGNED NOT NULL,
  `conflict` tinyint(1) UNSIGNED NOT NULL,
  `couch` tinyint(1) UNSIGNED NOT NULL,
  `driven` tinyint(1) UNSIGNED NOT NULL,
  `extroverted` tinyint(1) UNSIGNED NOT NULL,
  `individuality` tinyint(1) UNSIGNED NOT NULL,
  `logic` tinyint(1) UNSIGNED NOT NULL,
  `messy` tinyint(1) UNSIGNED NOT NULL,
  `mood` tinyint(1) UNSIGNED NOT NULL,
  `optimistic` tinyint(1) UNSIGNED NOT NULL,
  `other_ds` tinyint(1) UNSIGNED NOT NULL,
  `poly` tinyint(1) UNSIGNED NOT NULL,
  `proactive` tinyint(1) UNSIGNED NOT NULL,
  `stayhome` tinyint(1) UNSIGNED NOT NULL,
  `weird` tinyint(1) UNSIGNED NOT NULL,
  `fitness` tinyint(1) UNSIGNED NOT NULL,
  `money` tinyint(1) UNSIGNED NOT NULL,
  `my_looks` tinyint(1) UNSIGNED NOT NULL,
  `their_looks` tinyint(1) UNSIGNED NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_matchmaking`
--

CREATE TABLE `vc_matchmaking` (
  `hashid` char(6) CHARACTER SET latin1 NOT NULL,
  `age` tinyint(3) UNSIGNED NOT NULL,
  `gender` char(1) CHARACTER SET latin1 NOT NULL,
  `extroverted` tinyint(1) UNSIGNED NOT NULL,
  `bed_ds` tinyint(1) UNSIGNED NOT NULL,
  `other_ds` tinyint(1) UNSIGNED NOT NULL,
  `mood` tinyint(1) UNSIGNED NOT NULL,
  `proactive` tinyint(1) UNSIGNED NOT NULL,
  `bodymind` tinyint(1) UNSIGNED NOT NULL,
  `optimistic` tinyint(1) UNSIGNED NOT NULL,
  `logic` tinyint(1) UNSIGNED NOT NULL,
  `driven` tinyint(1) UNSIGNED NOT NULL,
  `weird` tinyint(1) UNSIGNED NOT NULL,
  `poly` tinyint(1) UNSIGNED NOT NULL,
  `adventure` tinyint(1) UNSIGNED NOT NULL,
  `calm` tinyint(1) UNSIGNED NOT NULL,
  `messy` tinyint(1) UNSIGNED NOT NULL,
  `couch` tinyint(1) UNSIGNED NOT NULL,
  `individuality` tinyint(1) UNSIGNED NOT NULL,
  `stayhome` tinyint(1) UNSIGNED NOT NULL,
  `conflict` tinyint(1) UNSIGNED NOT NULL,
  `touch` tinyint(1) UNSIGNED NOT NULL,
  `money` tinyint(1) UNSIGNED NOT NULL,
  `mylooks` tinyint(1) UNSIGNED NOT NULL,
  `theirlooks` tinyint(1) UNSIGNED NOT NULL,
  `intelligence` tinyint(1) UNSIGNED NOT NULL,
  `health` tinyint(1) UNSIGNED NOT NULL,
  `fitness` tinyint(1) UNSIGNED NOT NULL,
  `ecology` tinyint(1) UNSIGNED NOT NULL,
  `animalrights` tinyint(1) UNSIGNED NOT NULL,
  `sex` tinyint(1) UNSIGNED NOT NULL,
  `ip` varchar(12) CHARACTER SET latin1 NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_message`
--

CREATE TABLE `vc_message` (
  `id` int(11) NOT NULL,
  `senderid` int(11) NOT NULL DEFAULT '0',
  `senderip` varchar(15) DEFAULT NULL,
  `senderstatus` tinyint(1) NOT NULL DEFAULT '0',
  `recipientstatus` tinyint(1) NOT NULL DEFAULT '0',
  `recipientreplied` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `recipient_delete_date` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `recipientid` int(11) DEFAULT NULL,
  `name` varchar(70) DEFAULT NULL,
  `email` varchar(70) DEFAULT NULL,
  `hide_email` tinyint(1) NOT NULL DEFAULT '0',
  `subject` varchar(50) DEFAULT NULL,
  `body` text,
  `body_hash` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_metric`
--

CREATE TABLE `vc_metric` (
  `day` date NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `value` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_mod_message`
--

CREATE TABLE `vc_mod_message` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL,
  `message` varchar(5000) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_news`
--

CREATE TABLE `vc_news` (
  `id` int(10) UNSIGNED NOT NULL,
  `current` datetime NOT NULL,
  `profileid` int(10) UNSIGNED NOT NULL,
  `content_de` text NOT NULL,
  `content_en` text NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_online`
--

CREATE TABLE `vc_online` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_page_view_log`
--

CREATE TABLE `vc_page_view_log` (
  `request_method` tinyint(1) UNSIGNED NOT NULL,
  `site` varchar(250) NOT NULL,
  `site_params` varchar(250) DEFAULT NULL,
  `session_id` char(27) NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `script_time` mediumint(8) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_persistent_login`
--

CREATE TABLE `vc_persistent_login` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `token` char(25) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `active` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_picture`
--

CREATE TABLE `vc_picture` (
  `id` int(11) NOT NULL,
  `profileid` int(11) DEFAULT NULL,
  `filename` varchar(25) DEFAULT NULL,
  `description` varchar(200) NOT NULL,
  `visibility` tinyint(2) UNSIGNED NOT NULL DEFAULT '1',
  `weight` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `defaultpic` tinyint(1) DEFAULT '0',
  `width` smallint(2) UNSIGNED NOT NULL DEFAULT '0',
  `height` smallint(2) UNSIGNED NOT NULL DEFAULT '0',
  `smallwidth` smallint(2) UNSIGNED NOT NULL DEFAULT '0',
  `smallheight` smallint(2) UNSIGNED NOT NULL DEFAULT '0',
  `creation` datetime DEFAULT NULL,
  `feed_thread_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_picture_checklist`
--

CREATE TABLE `vc_picture_checklist` (
  `id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_picture_warning`
--

CREATE TABLE `vc_picture_warning` (
  `picture_id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `ticket_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `own_pic_confirmed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `closed_by` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_plus`
--

CREATE TABLE `vc_plus` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `plus_type` tinyint(1) UNSIGNED NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `payment_type` tinyint(3) UNSIGNED NOT NULL,
  `payment_id` int(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_plus_paypal_payment`
--

CREATE TABLE `vc_plus_paypal_payment` (
  `id` int(11) UNSIGNED NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `payer_id` varchar(255) DEFAULT NULL,
  `paypal_create_time` datetime DEFAULT NULL,
  `paypal_update_time` datetime DEFAULT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_pm_draft`
--

CREATE TABLE `vc_pm_draft` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) UNSIGNED NOT NULL,
  `recipient_id` int(11) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_pm_thread`
--

CREATE TABLE `vc_pm_thread` (
  `min_user_id` int(11) UNSIGNED NOT NULL,
  `max_user_id` int(11) UNSIGNED NOT NULL,
  `min_user_last_pm_id` int(11) UNSIGNED DEFAULT NULL,
  `min_user_last_update` datetime DEFAULT NULL,
  `min_user_is_new` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `max_user_last_pm_id` int(11) UNSIGNED DEFAULT NULL,
  `max_user_last_update` datetime DEFAULT NULL,
  `max_user_is_new` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_poll`
--

CREATE TABLE `vc_poll` (
  `poll_id` int(11) UNSIGNED NOT NULL,
  `question_de` varchar(255) DEFAULT NULL,
  `question_en` varchar(255) DEFAULT NULL,
  `start_time` date NOT NULL,
  `end_time` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_poll_option`
--

CREATE TABLE `vc_poll_option` (
  `poll_id` int(11) UNSIGNED NOT NULL,
  `option_id` tinyint(1) UNSIGNED NOT NULL,
  `option_de` varchar(100) NOT NULL,
  `option_en` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_poll_selection`
--

CREATE TABLE `vc_poll_selection` (
  `poll_id` int(11) UNSIGNED NOT NULL,
  `option_id` tinyint(1) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile`
--

CREATE TABLE `vc_profile` (
  `id` int(11) UNSIGNED NOT NULL,
  `nickname` varchar(20) DEFAULT NULL,
  `gender` tinyint(2) UNSIGNED NOT NULL,
  `birth` date DEFAULT NULL,
  `age` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `hide_age` tinyint(1) UNSIGNED NOT NULL,
  `age_from_friends` tinyint(3) UNSIGNED NOT NULL DEFAULT '8',
  `age_to_friends` tinyint(3) UNSIGNED NOT NULL DEFAULT '120',
  `age_from_romantic` tinyint(3) UNSIGNED NOT NULL DEFAULT '18',
  `age_to_romantic` tinyint(3) UNSIGNED NOT NULL DEFAULT '120',
  `zodiac` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(70) DEFAULT NULL,
  `salt` char(25) DEFAULT NULL,
  `postalcode` varchar(10) DEFAULT NULL,
  `residence` varchar(30) DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `region` varchar(25) DEFAULT NULL,
  `search` int(11) DEFAULT NULL,
  `nutrition` tinyint(3) UNSIGNED DEFAULT NULL,
  `nutrition_freetext` varchar(25) NOT NULL,
  `smoking` tinyint(3) UNSIGNED DEFAULT NULL,
  `alcohol` tinyint(3) UNSIGNED DEFAULT NULL,
  `religion` tinyint(3) UNSIGNED DEFAULT NULL,
  `children` tinyint(3) UNSIGNED DEFAULT NULL,
  `political` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `marital` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `bodyheight` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `bodytype` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `clothing` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `haircolor` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `eyecolor` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `relocate` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `word1` varchar(25) DEFAULT NULL,
  `word2` varchar(25) DEFAULT NULL,
  `word3` varchar(25) DEFAULT NULL,
  `tabQuestionaire1Hide` tinyint(4) NOT NULL DEFAULT '0',
  `tabQuestionaire2Hide` tinyint(4) NOT NULL DEFAULT '0',
  `tabQuestionaire3Hide` tinyint(4) NOT NULL DEFAULT '0',
  `tabQuestionaire4Hide` tinyint(4) NOT NULL DEFAULT '0',
  `tabQuestionaire5Hide` tinyint(4) NOT NULL DEFAULT '0',
  `defaultPicHide` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `questionairelength` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `tabAlbumHide` tinyint(4) NOT NULL DEFAULT '0',
  `tabMapHide` tinyint(4) NOT NULL DEFAULT '0',
  `facebook_id` varchar(50) DEFAULT NULL,
  `first_entry` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_chat_login` datetime DEFAULT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_reason` text,
  `reminder_date` datetime DEFAULT NULL,
  `counter` int(11) NOT NULL DEFAULT '0',
  `locale` varchar(3) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `latitude` double(10,6) NOT NULL DEFAULT '0.000000',
  `longitude` double(10,6) NOT NULL DEFAULT '0.000000',
  `sin_latitude` double(18,16) NOT NULL DEFAULT '0.0000000000000000' COMMENT 'SIN(PI() * latitude/180)',
  `cos_latitude` double(18,16) NOT NULL DEFAULT '0.0000000000000000' COMMENT 'COS(PI() * latitude/180)',
  `longitude_radius` double(18,16) NOT NULL DEFAULT '0.0000000000000000' COMMENT 'PI() * longitude/180',
  `homepage` varchar(255) DEFAULT NULL,
  `favlink1` varchar(255) DEFAULT NULL,
  `favlink2` varchar(255) DEFAULT NULL,
  `favlink3` varchar(255) DEFAULT NULL,
  `plus_marker` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `real_marker` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `admin` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `chat_banned` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `chat_marker` varchar(50) NOT NULL DEFAULT '',
  `debuginfo` text,
  `registration_referer` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile_comment_log`
--

CREATE TABLE `vc_profile_comment_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `comment` varchar(1000) NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile_email_log`
--

CREATE TABLE `vc_profile_email_log` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile_field_political`
--

CREATE TABLE `vc_profile_field_political` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `field_value` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile_field_search`
--

CREATE TABLE `vc_profile_field_search` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `field_value` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile_hobby`
--

CREATE TABLE `vc_profile_hobby` (
  `profileid` int(11) NOT NULL DEFAULT '0',
  `hobbyid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile_password_log`
--

CREATE TABLE `vc_profile_password_log` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `password` varchar(70) NOT NULL,
  `salt` char(25) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_profile_visit`
--

CREATE TABLE `vc_profile_visit` (
  `profile_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL,
  `visit` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_questionaire`
--

CREATE TABLE `vc_questionaire` (
  `profileid` int(11) NOT NULL DEFAULT '0',
  `topic` tinyint(4) NOT NULL DEFAULT '0',
  `item` tinyint(4) NOT NULL DEFAULT '0',
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_real_check`
--

CREATE TABLE `vc_real_check` (
  `id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `code` char(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `picture` varchar(25) DEFAULT NULL,
  `checked_by` int(11) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  `admin_comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_real_picture`
--

CREATE TABLE `vc_real_picture` (
  `picture_id` int(11) UNSIGNED NOT NULL,
  `real_check_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_redirect`
--

CREATE TABLE `vc_redirect` (
  `url` varchar(255) NOT NULL,
  `count` smallint(5) UNSIGNED NOT NULL,
  `last_redirect` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_registration_referer`
--

CREATE TABLE `vc_registration_referer` (
  `id` int(11) UNSIGNED NOT NULL,
  `url` varchar(500) NOT NULL,
  `registration_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_search`
--

CREATE TABLE `vc_search` (
  `id` int(11) NOT NULL,
  `profileid` int(11) NOT NULL DEFAULT '0',
  `name` text NOT NULL,
  `url` text NOT NULL,
  `message_interval` tinyint(4) NOT NULL DEFAULT '0',
  `message_type` tinyint(1) NOT NULL DEFAULT '0',
  `last_message` datetime DEFAULT NULL,
  `weight` tinyint(3) UNSIGNED NOT NULL DEFAULT '250'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_searchstring_index`
--

CREATE TABLE `vc_searchstring_index` (
  `profileid` int(11) NOT NULL DEFAULT '0',
  `locale` varchar(3) NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT '0',
  `searchtext` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_search_match`
--

CREATE TABLE `vc_search_match` (
  `user_1_gender` tinyint(3) UNSIGNED NOT NULL,
  `user_1_search` tinyint(3) UNSIGNED NOT NULL,
  `user_2_gender` tinyint(3) UNSIGNED NOT NULL,
  `user_2_search` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_setting`
--

CREATE TABLE `vc_setting` (
  `profileid` int(11) NOT NULL DEFAULT '0',
  `field` int(1) NOT NULL DEFAULT '0',
  `value` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_subscription`
--

CREATE TABLE `vc_subscription` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `entity_type` tinyint(3) UNSIGNED NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_suspicion`
--

CREATE TABLE `vc_suspicion` (
  `occurrence` datetime NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `weight` tinyint(3) UNSIGNED NOT NULL,
  `debug_data` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_system_message`
--

CREATE TABLE `vc_system_message` (
  `id` int(11) NOT NULL,
  `recipient` varchar(70) DEFAULT NULL,
  `subject` varchar(70) DEFAULT NULL,
  `body` text,
  `attachments` varchar(500) DEFAULT NULL,
  `created` datetime NOT NULL,
  `mail_config` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_termsofuse`
--

CREATE TABLE `vc_termsofuse` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL,
  `locale` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `version` tinyint(3) UNSIGNED NOT NULL,
  `confirmation_necessary` tinyint(1) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `changes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_termsofuse_confirm`
--

CREATE TABLE `vc_termsofuse_confirm` (
  `terms_id` int(11) UNSIGNED NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `confirmation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_ticket`
--

CREATE TABLE `vc_ticket` (
  `id` int(11) NOT NULL,
  `hash_id` char(6) DEFAULT NULL,
  `lng` char(2) NOT NULL,
  `profile_id` int(11) UNSIGNED DEFAULT NULL,
  `nickname` varchar(20) DEFAULT NULL,
  `email` varchar(70) DEFAULT NULL,
  `category` tinyint(1) UNSIGNED NOT NULL,
  `subject` varchar(50) NOT NULL,
  `debuginfo` text,
  `status` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_ticket_message`
--

CREATE TABLE `vc_ticket_message` (
  `id` int(11) UNSIGNED NOT NULL,
  `ticket_id` int(11) UNSIGNED NOT NULL,
  `by_admin` tinyint(1) UNSIGNED NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_tip`
--

CREATE TABLE `vc_tip` (
  `id` int(11) UNSIGNED NOT NULL,
  `locale` char(2) NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `body` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_toldafriend`
--

CREATE TABLE `vc_toldafriend` (
  `id` int(11) UNSIGNED NOT NULL,
  `profileid` int(11) NOT NULL DEFAULT '0',
  `sender` varchar(70) NOT NULL,
  `reciever1` varchar(70) NOT NULL,
  `reciever2` varchar(70) NOT NULL,
  `reciever3` varchar(70) NOT NULL,
  `reciever4` varchar(70) NOT NULL,
  `reciever5` varchar(70) NOT NULL,
  `reciever6` varchar(70) NOT NULL,
  `body` text NOT NULL,
  `subject` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_sent` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_update`
--

CREATE TABLE `vc_update` (
  `entity_id` int(11) UNSIGNED NOT NULL,
  `entity_type` tinyint(3) UNSIGNED NOT NULL,
  `action` tinyint(3) UNSIGNED NOT NULL,
  `context_type` tinyint(3) UNSIGNED NOT NULL,
  `context_id` int(11) UNSIGNED DEFAULT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_user_ip_log`
--

CREATE TABLE `vc_user_ip_log` (
  `ip` varchar(15) NOT NULL,
  `profile_id` int(11) UNSIGNED NOT NULL,
  `access` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_watchlist`
--

CREATE TABLE `vc_watchlist` (
  `profile_id` int(11) UNSIGNED NOT NULL,
  `undesirable` tinyint(1) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_websocket_message`
--

CREATE TABLE `vc_websocket_message` (
  `context_type` tinyint(3) UNSIGNED NOT NULL,
  `context_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_websocket_server_log`
--

CREATE TABLE `vc_websocket_server_log` (
  `message_type` tinyint(3) UNSIGNED NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vc_websocket_user`
--

CREATE TABLE `vc_websocket_user` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `websocket_key` char(64) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `ajax_chat_channels`
--
ALTER TABLE `ajax_chat_channels`
  ADD PRIMARY KEY (`channel_id`);

--
-- Indizes für die Tabelle `ajax_chat_messages`
--
ALTER TABLE `ajax_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel_id` (`channel`,`id`) USING BTREE,
  ADD KEY `channelIdDate` (`channel`,`id`,`dateTime`) USING BTREE,
  ADD KEY `userID` (`userID`,`dateTime`);

--
-- Indizes für die Tabelle `ajax_chat_messages_archive`
--
ALTER TABLE `ajax_chat_messages_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`,`dateTime`),
  ADD KEY `dateTime` (`dateTime`);

--
-- Indizes für die Tabelle `ajax_chat_online`
--
ALTER TABLE `ajax_chat_online`
  ADD KEY `userID` (`channel`,`userID`) USING BTREE;

--
-- Indizes für die Tabelle `geoip_country`
--
ALTER TABLE `geoip_country`
  ADD UNIQUE KEY `ip_from` (`ip_from`,`ip_to`);

--
-- Indizes für die Tabelle `vc_activation_token`
--
ALTER TABLE `vc_activation_token`
  ADD UNIQUE KEY `profile_id` (`profile_id`,`token`);

--
-- Indizes für die Tabelle `vc_activity`
--
ALTER TABLE `vc_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_banned_email`
--
ALTER TABLE `vc_banned_email`
  ADD UNIQUE KEY `email` (`email`);

--
-- Indizes für die Tabelle `vc_blocked`
--
ALTER TABLE `vc_blocked`
  ADD KEY `profile_id` (`profile_id`,`blocked_id`),
  ADD KEY `blocked_id` (`blocked_id`);

--
-- Indizes für die Tabelle `vc_blocked_login`
--
ALTER TABLE `vc_blocked_login`
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `vc_change_pw_token`
--
ALTER TABLE `vc_change_pw_token`
  ADD UNIQUE KEY `profile_id` (`profile_id`,`token`);

--
-- Indizes für die Tabelle `vc_country`
--
ALTER TABLE `vc_country`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `iso2` (`iso2`),
  ADD KEY `id` (`id`);

--
-- Indizes für die Tabelle `vc_cron_log`
--
ALTER TABLE `vc_cron_log`
  ADD KEY `start` (`start`);

--
-- Indizes für die Tabelle `vc_custom_design`
--
ALTER TABLE `vc_custom_design`
  ADD PRIMARY KEY (`profile_id`);

--
-- Indizes für die Tabelle `vc_event`
--
ALTER TABLE `vc_event`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash_id` (`hash_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `feed_thread_id` (`feed_thread_id`);

--
-- Indizes für die Tabelle `vc_event_participant`
--
ALTER TABLE `vc_event_participant`
  ADD PRIMARY KEY (`event_id`,`profile_id`),
  ADD KEY `profile_id` (`profile_id`),
  ADD KEY `event_id` (`event_id`,`degree`);

--
-- Indizes für die Tabelle `vc_faq`
--
ALTER TABLE `vc_faq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `locale` (`locale`);

--
-- Indizes für die Tabelle `vc_favorite`
--
ALTER TABLE `vc_favorite`
  ADD KEY `profileid` (`profileid`),
  ADD KEY `favoriteid` (`favoriteid`);

--
-- Indizes für die Tabelle `vc_feed_thread`
--
ALTER TABLE `vc_feed_thread`
  ADD PRIMARY KEY (`user_id`,`thread_id`),
  ADD KEY `thread_id` (`thread_id`);

--
-- Indizes für die Tabelle `vc_flag`
--
ALTER TABLE `vc_flag`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aggregate_type` (`aggregate_type`,`aggregate_id`,`entity_type`);

--
-- Indizes für die Tabelle `vc_forum_thread`
--
ALTER TABLE `vc_forum_thread`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash_id` (`hash_id`),
  ADD KEY `forum_id` (`context_id`),
  ADD KEY `context_type` (`context_type`,`context_id`,`content_updated_at`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `context_type_2` (`context_type`,`created_by`);

--
-- Indizes für die Tabelle `vc_forum_thread_comment`
--
ALTER TABLE `vc_forum_thread_comment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash_id` (`hash_id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indizes für die Tabelle `vc_friend`
--
ALTER TABLE `vc_friend`
  ADD KEY `buddy1id` (`friend1id`),
  ADD KEY `buddy2id` (`friend2id`),
  ADD KEY `friend2id` (`friend2id`,`friend2_accepted`);

--
-- Indizes für die Tabelle `vc_full_log`
--
ALTER TABLE `vc_full_log`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_geoname`
--
ALTER TABLE `vc_geoname`
  ADD KEY `country_id` (`country_id`,`postal_code`);

--
-- Indizes für die Tabelle `vc_group`
--
ALTER TABLE `vc_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `confirmed_at` (`confirmed_at`,`deleted_at`),
  ADD KEY `hash_id` (`hash_id`);

--
-- Indizes für die Tabelle `vc_group_activity`
--
ALTER TABLE `vc_group_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_group_ban`
--
ALTER TABLE `vc_group_ban`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_group_forum`
--
ALTER TABLE `vc_group_forum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`,`is_main`),
  ADD KEY `hash_id` (`hash_id`);

--
-- Indizes für die Tabelle `vc_group_invitation`
--
ALTER TABLE `vc_group_invitation`
  ADD UNIQUE KEY `group_id` (`group_id`,`profile_id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indizes für die Tabelle `vc_group_member`
--
ALTER TABLE `vc_group_member`
  ADD PRIMARY KEY (`group_id`,`profile_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indizes für die Tabelle `vc_group_notification`
--
ALTER TABLE `vc_group_notification`
  ADD UNIQUE KEY `profile_id_2` (`profile_id`,`notification_type`,`entity_id`),
  ADD KEY `profile_id_3` (`profile_id`,`seen_at`);

--
-- Indizes für die Tabelle `vc_group_role`
--
ALTER TABLE `vc_group_role`
  ADD PRIMARY KEY (`group_id`,`profile_id`);

--
-- Indizes für die Tabelle `vc_group_setting`
--
ALTER TABLE `vc_group_setting`
  ADD UNIQUE KEY `group_id` (`group_id`,`setting`),
  ADD UNIQUE KEY `group_id_3` (`group_id`,`setting`),
  ADD KEY `group_id_2` (`group_id`),
  ADD KEY `group_id_4` (`group_id`);

--
-- Indizes für die Tabelle `vc_help_notification`
--
ALTER TABLE `vc_help_notification`
  ADD PRIMARY KEY (`profile_id`,`ticket_id`);

--
-- Indizes für die Tabelle `vc_hobby`
--
ALTER TABLE `vc_hobby`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `groupid` (`groupid`);

--
-- Indizes für die Tabelle `vc_hobby_group`
--
ALTER TABLE `vc_hobby_group`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_javascript_log`
--
ALTER TABLE `vc_javascript_log`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_keyword`
--
ALTER TABLE `vc_keyword`
  ADD KEY `field` (`field`,`value`);

--
-- Indizes für die Tabelle `vc_last_visitor`
--
ALTER TABLE `vc_last_visitor`
  ADD PRIMARY KEY (`profile_id`,`visitor_id`),
  ADD KEY `profileid` (`profile_id`);

--
-- Indizes für die Tabelle `vc_like`
--
ALTER TABLE `vc_like`
  ADD PRIMARY KEY (`entity_type`,`entity_id`,`profile_id`),
  ADD KEY `entity_type` (`entity_type`,`entity_id`),
  ADD KEY `entity_type_2` (`entity_type`,`entity_id`,`up_down`);

--
-- Indizes für die Tabelle `vc_link`
--
ALTER TABLE `vc_link`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group` (`linkgroup`);

--
-- Indizes für die Tabelle `vc_link_group`
--
ALTER TABLE `vc_link_group`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_match`
--
ALTER TABLE `vc_match`
  ADD PRIMARY KEY (`min_user_id`,`max_user_id`),
  ADD KEY `max_user_id` (`max_user_id`,`min_user_id`);

--
-- Indizes für die Tabelle `vc_matching`
--
ALTER TABLE `vc_matching`
  ADD PRIMARY KEY (`user_id`);

--
-- Indizes für die Tabelle `vc_message`
--
ALTER TABLE `vc_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipientstatus` (`recipientstatus`,`recipientid`),
  ADD KEY `recipientid` (`recipientid`),
  ADD KEY `senderid_2` (`senderid`,`body_hash`),
  ADD KEY `senderid` (`senderid`,`senderstatus`,`recipientid`,`recipientstatus`),
  ADD KEY `pm_thread_sender` (`senderid`,`recipientid`,`senderstatus`),
  ADD KEY `pm_thread_recipient` (`senderid`,`recipientid`,`recipientstatus`),
  ADD KEY `created` (`created`);

--
-- Indizes für die Tabelle `vc_metric`
--
ALTER TABLE `vc_metric`
  ADD PRIMARY KEY (`day`,`type`),
  ADD KEY `type` (`type`);

--
-- Indizes für die Tabelle `vc_mod_message`
--
ALTER TABLE `vc_mod_message`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_news`
--
ALTER TABLE `vc_news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profileid` (`profileid`);

--
-- Indizes für die Tabelle `vc_online`
--
ALTER TABLE `vc_online`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `lastseen` (`updated_at`),
  ADD KEY `updated_at` (`updated_at`);

--
-- Indizes für die Tabelle `vc_page_view_log`
--
ALTER TABLE `vc_page_view_log`
  ADD KEY `site` (`site`),
  ADD KEY `script_time` (`script_time`);

--
-- Indizes für die Tabelle `vc_persistent_login`
--
ALTER TABLE `vc_persistent_login`
  ADD KEY `profile_id` (`profile_id`,`token`);

--
-- Indizes für die Tabelle `vc_picture`
--
ALTER TABLE `vc_picture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profileid_2` (`profileid`,`defaultpic`),
  ADD KEY `profileid` (`profileid`,`weight`),
  ADD KEY `profileid_3` (`profileid`,`weight`);

--
-- Indizes für die Tabelle `vc_picture_checklist`
--
ALTER TABLE `vc_picture_checklist`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_picture_warning`
--
ALTER TABLE `vc_picture_warning`
  ADD PRIMARY KEY (`picture_id`),
  ADD KEY `profileid` (`profile_id`);

--
-- Indizes für die Tabelle `vc_plus`
--
ALTER TABLE `vc_plus`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_plus_paypal_payment`
--
ALTER TABLE `vc_plus_paypal_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_pm_draft`
--
ALTER TABLE `vc_pm_draft`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`,`recipient_id`);

--
-- Indizes für die Tabelle `vc_pm_thread`
--
ALTER TABLE `vc_pm_thread`
  ADD PRIMARY KEY (`min_user_id`,`max_user_id`),
  ADD KEY `min_user_id` (`min_user_id`,`min_user_last_update`),
  ADD KEY `max_user_id` (`max_user_id`,`max_user_last_update`),
  ADD KEY `created_at` (`created_at`);

--
-- Indizes für die Tabelle `vc_poll`
--
ALTER TABLE `vc_poll`
  ADD PRIMARY KEY (`poll_id`);

--
-- Indizes für die Tabelle `vc_poll_option`
--
ALTER TABLE `vc_poll_option`
  ADD PRIMARY KEY (`poll_id`,`option_id`),
  ADD KEY `poll_id` (`poll_id`);

--
-- Indizes für die Tabelle `vc_poll_selection`
--
ALTER TABLE `vc_poll_selection`
  ADD PRIMARY KEY (`poll_id`,`profile_id`),
  ADD KEY `poll_id` (`poll_id`,`option_id`);

--
-- Indizes für die Tabelle `vc_profile`
--
ALTER TABLE `vc_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `search` (`search`),
  ADD KEY `nutrition` (`nutrition`),
  ADD KEY `smoking` (`smoking`),
  ADD KEY `alcohol` (`alcohol`),
  ADD KEY `religion` (`religion`),
  ADD KEY `children` (`children`),
  ADD KEY `zodiac` (`zodiac`),
  ADD KEY `political` (`political`),
  ADD KEY `marital` (`marital`),
  ADD KEY `bodyheight` (`bodyheight`),
  ADD KEY `bodytype` (`bodytype`),
  ADD KEY `haircolor` (`haircolor`),
  ADD KEY `eyecolor` (`eyecolor`),
  ADD KEY `relocate` (`relocate`),
  ADD KEY `clothing` (`clothing`),
  ADD KEY `active` (`active`),
  ADD KEY `locale` (`locale`,`active`),
  ADD KEY `age` (`age`),
  ADD KEY `active_2` (`id`,`active`) USING BTREE,
  ADD KEY `active_4` (`nickname`,`active`) USING BTREE,
  ADD KEY `active_3` (`email`,`active`) USING BTREE,
  ADD KEY `facebook_id` (`facebook_id`);

--
-- Indizes für die Tabelle `vc_profile_comment_log`
--
ALTER TABLE `vc_profile_comment_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indizes für die Tabelle `vc_profile_field_political`
--
ALTER TABLE `vc_profile_field_political`
  ADD PRIMARY KEY (`profile_id`,`field_value`),
  ADD KEY `field_value` (`field_value`);

--
-- Indizes für die Tabelle `vc_profile_field_search`
--
ALTER TABLE `vc_profile_field_search`
  ADD PRIMARY KEY (`profile_id`,`field_value`),
  ADD KEY `field_value` (`field_value`);

--
-- Indizes für die Tabelle `vc_profile_hobby`
--
ALTER TABLE `vc_profile_hobby`
  ADD PRIMARY KEY (`profileid`,`hobbyid`),
  ADD KEY `profileid` (`profileid`);

--
-- Indizes für die Tabelle `vc_profile_visit`
--
ALTER TABLE `vc_profile_visit`
  ADD UNIQUE KEY `profileid` (`profile_id`,`ip`,`visit`);

--
-- Indizes für die Tabelle `vc_questionaire`
--
ALTER TABLE `vc_questionaire`
  ADD PRIMARY KEY (`profileid`,`topic`,`item`),
  ADD KEY `profileid` (`profileid`,`topic`);

--
-- Indizes für die Tabelle `vc_real_check`
--
ALTER TABLE `vc_real_check`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indizes für die Tabelle `vc_real_picture`
--
ALTER TABLE `vc_real_picture`
  ADD PRIMARY KEY (`picture_id`,`real_check_id`);

--
-- Indizes für die Tabelle `vc_redirect`
--
ALTER TABLE `vc_redirect`
  ADD UNIQUE KEY `url` (`url`);

--
-- Indizes für die Tabelle `vc_registration_referer`
--
ALTER TABLE `vc_registration_referer`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_search`
--
ALTER TABLE `vc_search`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_searchstring_index`
--
ALTER TABLE `vc_searchstring_index`
  ADD PRIMARY KEY (`profileid`,`locale`,`visibility`),
  ADD KEY `locale` (`locale`,`visibility`);
ALTER TABLE `vc_searchstring_index` ADD FULLTEXT KEY `searchtext` (`searchtext`);

--
-- Indizes für die Tabelle `vc_search_match`
--
ALTER TABLE `vc_search_match`
  ADD KEY `user_1_gender` (`user_1_gender`,`user_1_search`);

--
-- Indizes für die Tabelle `vc_setting`
--
ALTER TABLE `vc_setting`
  ADD PRIMARY KEY (`profileid`,`field`);

--
-- Indizes für die Tabelle `vc_subscription`
--
ALTER TABLE `vc_subscription`
  ADD UNIQUE KEY `profile_id` (`profile_id`,`entity_type`,`entity_id`),
  ADD KEY `group_id` (`profile_id`);

--
-- Indizes für die Tabelle `vc_suspicion`
--
ALTER TABLE `vc_suspicion`
  ADD KEY `profile_id` (`profile_id`),
  ADD KEY `ip` (`ip`);

--
-- Indizes für die Tabelle `vc_system_message`
--
ALTER TABLE `vc_system_message`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `vc_termsofuse`
--
ALTER TABLE `vc_termsofuse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`,`locale`);

--
-- Indizes für die Tabelle `vc_termsofuse_confirm`
--
ALTER TABLE `vc_termsofuse_confirm`
  ADD PRIMARY KEY (`terms_id`,`profile_id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indizes für die Tabelle `vc_ticket`
--
ALTER TABLE `vc_ticket`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash_id` (`hash_id`),
  ADD KEY `status` (`status`);

--
-- Indizes für die Tabelle `vc_ticket_message`
--
ALTER TABLE `vc_ticket_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indizes für die Tabelle `vc_tip`
--
ALTER TABLE `vc_tip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `locale` (`locale`,`number`);

--
-- Indizes für die Tabelle `vc_toldafriend`
--
ALTER TABLE `vc_toldafriend`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_sent` (`is_sent`),
  ADD KEY `is_sent_2` (`is_sent`);

--
-- Indizes für die Tabelle `vc_update`
--
ALTER TABLE `vc_update`
  ADD UNIQUE KEY `entity_id` (`entity_id`,`entity_type`,`action`,`context_id`),
  ADD KEY `last_update` (`last_update`),
  ADD KEY `entity_type` (`context_id`,`entity_type`) USING BTREE;

--
-- Indizes für die Tabelle `vc_user_ip_log`
--
ALTER TABLE `vc_user_ip_log`
  ADD KEY `ip` (`ip`),
  ADD KEY `profile_id` (`profile_id`),
  ADD KEY `access` (`access`);

--
-- Indizes für die Tabelle `vc_watchlist`
--
ALTER TABLE `vc_watchlist`
  ADD PRIMARY KEY (`profile_id`);

--
-- Indizes für die Tabelle `vc_websocket_message`
--
ALTER TABLE `vc_websocket_message`
  ADD PRIMARY KEY (`context_type`,`context_id`);

--
-- Indizes für die Tabelle `vc_websocket_server_log`
--
ALTER TABLE `vc_websocket_server_log`
  ADD KEY `created_at` (`created_at`);

--
-- Indizes für die Tabelle `vc_websocket_user`
--
ALTER TABLE `vc_websocket_user`
  ADD UNIQUE KEY `websocket_key` (`websocket_key`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `ajax_chat_messages`
--
ALTER TABLE `ajax_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_activity`
--
ALTER TABLE `vc_activity`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_event`
--
ALTER TABLE `vc_event`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_faq`
--
ALTER TABLE `vc_faq`
  MODIFY `id` int(7) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_flag`
--
ALTER TABLE `vc_flag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_forum_thread`
--
ALTER TABLE `vc_forum_thread`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_forum_thread_comment`
--
ALTER TABLE `vc_forum_thread_comment`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_full_log`
--
ALTER TABLE `vc_full_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_group`
--
ALTER TABLE `vc_group`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_group_activity`
--
ALTER TABLE `vc_group_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_group_ban`
--
ALTER TABLE `vc_group_ban`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_group_forum`
--
ALTER TABLE `vc_group_forum`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_hobby`
--
ALTER TABLE `vc_hobby`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_hobby_group`
--
ALTER TABLE `vc_hobby_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_javascript_log`
--
ALTER TABLE `vc_javascript_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_link`
--
ALTER TABLE `vc_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_link_group`
--
ALTER TABLE `vc_link_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_message`
--
ALTER TABLE `vc_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_mod_message`
--
ALTER TABLE `vc_mod_message`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_news`
--
ALTER TABLE `vc_news`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_picture`
--
ALTER TABLE `vc_picture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_plus`
--
ALTER TABLE `vc_plus`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_plus_paypal_payment`
--
ALTER TABLE `vc_plus_paypal_payment`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_pm_draft`
--
ALTER TABLE `vc_pm_draft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_poll`
--
ALTER TABLE `vc_poll`
  MODIFY `poll_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_profile`
--
ALTER TABLE `vc_profile`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_profile_comment_log`
--
ALTER TABLE `vc_profile_comment_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_real_check`
--
ALTER TABLE `vc_real_check`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_registration_referer`
--
ALTER TABLE `vc_registration_referer`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_search`
--
ALTER TABLE `vc_search`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_system_message`
--
ALTER TABLE `vc_system_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_termsofuse`
--
ALTER TABLE `vc_termsofuse`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_ticket`
--
ALTER TABLE `vc_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_ticket_message`
--
ALTER TABLE `vc_ticket_message`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_tip`
--
ALTER TABLE `vc_tip`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vc_toldafriend`
--
ALTER TABLE `vc_toldafriend`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
