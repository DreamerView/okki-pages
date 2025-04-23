<?php
    ob_start('ob_gzhandler');
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();
    $db->start();
    try {
        $db->query("CREATE TABLE IF NOT EXISTS `users` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY,
            `login` char(100) COLLATE `ascii_bin` NOT NULL,
            `email` varchar(255) NOT NULL COLLATE `utf8_unicode_ci`, 
            `fullname` BLOB NOT NULL,
            `password` varchar(255) COLLATE `ascii_bin` NOT NULL
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `topics` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY, 
            `title` BLOB NOT NULL,
            `description` BLOB NOT NULL,
            `author` char(36) NOT NULL COLLATE `ascii_bin`,
            `time` bigint, 
            `status` tinyint
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `scheme` (
            `uuid` char(36) NOT NULL COLLATE `ascii_bin`, 
            `tag` char(50) NOT NULL COLLATE `ascii_bin`, 
            `tag_uuid` char(36) NOT NULL COLLATE `ascii_bin`,
            `type` char(10) NOT NULL COLLATE `ascii_bin`
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `content` (
            `scheme_uuid` char(36) NOT NULL COLLATE `ascii_bin`,
            `tag` char(50) NOT NULL COLLATE `ascii_bin`,
            `tag_uuid` char(36) NOT NULL COLLATE `ascii_bin`, 
            `component_uuid` char(36) NOT NULL COLLATE `ascii_bin`, 
            `content` BLOB 
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `group` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY, 
            `author` char(36) NOT NULL COLLATE `ascii_bin`,
            `title` BLOB NOT NULL,
            `time` bigint,
            `status` tinyint
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `group_list` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY, 
            `author` char(36) NOT NULL COLLATE `ascii_bin`,
            `article` char(36) NOT NULL COLLATE `ascii_bin`,
            `time` bigint
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `follow_system` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY, 
            `author` char(36) NOT NULL COLLATE `ascii_bin`,
            `follower` char(36) NOT NULL COLLATE `ascii_bin`,
            `time` bigint
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `repost` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY, 
            `author` char(36) NOT NULL COLLATE `ascii_bin`,
            `article` char(36) NOT NULL COLLATE `ascii_bin`,
            `time` bigint
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `saved` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY, 
            `author` char(36) NOT NULL COLLATE `ascii_bin`,
            `article` char(36) NOT NULL COLLATE `ascii_bin`,
            `time` bigint
        )");
        $db->query("CREATE TABLE IF NOT EXISTS `notification` (
            `uuid` char(36) COLLATE `ascii_bin` NOT NULL PRIMARY KEY, 
            `from` char(36) NOT NULL COLLATE `ascii_bin`,
            `to` char(36) NOT NULL COLLATE `ascii_bin`,
            `action` tinyint,
            `url` char(200) COLLATE `ascii_bin` NOT NULL,
            `time` bigint,
            `read` tinyint
        )");
        $db->finish();
    } catch(Exception $e) {
        $db->stop();
        die($e->getMessage());
    }
    ob_end_clean();
?>
