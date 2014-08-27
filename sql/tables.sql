SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `scrapy` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `scrapy`;

DROP TABLE IF EXISTS `eva_blog_archives`;
CREATE TABLE IF NOT EXISTS `eva_blog_archives` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `postId` int(10) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `archivedAt` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`postId`),
  KEY `post_id_2` (`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_blog_categories`;
CREATE TABLE IF NOT EXISTS `eva_blog_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `categoryName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `parentId` int(10) DEFAULT '0',
  `rootId` int(10) DEFAULT '0',
  `sortOrder` int(10) DEFAULT '0',
  `createdAt` int(10) DEFAULT NULL,
  `count` int(10) DEFAULT '0',
  `leftId` int(15) DEFAULT '0',
  `rightId` int(15) DEFAULT '0',
  `imageId` int(10) DEFAULT NULL,
  `image` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

DROP TABLE IF EXISTS `eva_blog_categories_posts`;
CREATE TABLE IF NOT EXISTS `eva_blog_categories_posts` (
  `categoryId` int(11) NOT NULL,
  `postId` int(11) NOT NULL,
  PRIMARY KEY (`categoryId`,`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `eva_blog_connections`;
CREATE TABLE IF NOT EXISTS `eva_blog_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` int(10) NOT NULL,
  `targetId` int(10) NOT NULL,
  `priority` int(3) NOT NULL DEFAULT '0',
  `detectedType` enum('system','user') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `createdAt` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sourceId` (`sourceId`,`targetId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_blog_favors`;
CREATE TABLE IF NOT EXISTS `eva_blog_favors` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `postId` int(10) NOT NULL,
  `createdAt` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_blog_posts`;
CREATE TABLE IF NOT EXISTS `eva_blog_posts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('deleted','draft','published','pending') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `flag` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `visibility` enum('public','private','password') COLLATE utf8_unicode_ci NOT NULL,
  `codeType` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'markdown',
  `language` varchar(5) COLLATE utf8_unicode_ci DEFAULT 'en',
  `parentId` int(10) DEFAULT '0',
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sortOrder` int(10) DEFAULT '0',
  `createdAt` int(10) NOT NULL,
  `userId` int(10) DEFAULT NULL,
  `username` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updatedAt` int(10) DEFAULT NULL,
  `editorId` int(10) DEFAULT NULL,
  `editorName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commentStatus` enum('open','closed','authority') COLLATE utf8_unicode_ci DEFAULT 'open',
  `commentType` varchar(15) COLLATE utf8_unicode_ci DEFAULT 'local',
  `commentCount` int(10) DEFAULT '0',
  `count` bigint(20) DEFAULT '0',
  `imageId` int(10) DEFAULT NULL,
  `image` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `summary` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sourceName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sourceUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `voteScore` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `createdAt` (`createdAt`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=78723 ;

DROP TABLE IF EXISTS `eva_blog_tags`;
CREATE TABLE IF NOT EXISTS `eva_blog_tags` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tagName` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `parentId` int(10) DEFAULT '0',
  `rootId` int(10) DEFAULT '0',
  `sortOrder` int(10) DEFAULT '0',
  `count` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_blog_tags_posts`;
CREATE TABLE IF NOT EXISTS `eva_blog_tags_posts` (
  `tagId` int(10) NOT NULL,
  `postId` int(10) NOT NULL,
  PRIMARY KEY (`tagId`,`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `eva_blog_texts`;
CREATE TABLE IF NOT EXISTS `eva_blog_texts` (
  `postId` int(20) NOT NULL,
  `metaKeywords` text COLLATE utf8_unicode_ci,
  `metaDescription` text COLLATE utf8_unicode_ci,
  `toc` text COLLATE utf8_unicode_ci,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `eva_blog_votes`;
CREATE TABLE IF NOT EXISTS `eva_blog_votes` (
  `postId` int(10) NOT NULL,
  `upVote` int(10) NOT NULL,
  `downVote` int(10) NOT NULL,
  `lastVotedAt` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `eva_blog_votes_users`;
CREATE TABLE IF NOT EXISTS `eva_blog_votes_users` (
  `postId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `voteType` enum('upVote','downVote') COLLATE utf8_unicode_ci DEFAULT 'upVote',
  `createdAt` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`postId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
