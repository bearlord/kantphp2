/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : 4kmovie

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2015-09-21 13:49:47
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `kant_session`
-- ----------------------------
DROP TABLE IF EXISTS `kant_session`;
CREATE TABLE `kant_session` (
  `sessionid` varchar(255) NOT NULL,
  `data` text,
  `lastvisit` int(10) DEFAULT NULL,
  `ip` char(16) DEFAULT NULL,
  `http_cookie` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sessionid`),
  KEY `ip` (`ip`),
  KEY `lastvisit` (`lastvisit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of kant_session
-- ----------------------------
