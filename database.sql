CREATE DATABASE IF NOT EXISTS `rdt-track` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `rdt-track`;

-- Dumping structure for table rdt-track.details
CREATE TABLE IF NOT EXISTS `details` (
  `pid` varchar(8) NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  `flair` varchar(124) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `nsfw` int(11) DEFAULT '0',
  `thumbnail` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table rdt-track.posts
CREATE TABLE IF NOT EXISTS `posts` (
  `pid` varchar(8) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `domain` varchar(124) DEFAULT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `sub` varchar(124) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table rdt-track.self_text
CREATE TABLE IF NOT EXISTS `self_text` (
  `pid` varchar(8) NOT NULL,
  `text` text,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table rdt-track.status
CREATE TABLE IF NOT EXISTS `status` (
  `pid` varchar(8) NOT NULL,
  `result` tinyint(4) DEFAULT '0',
  `ups` int(11) DEFAULT '0',
  `comments` int(11) DEFAULT '0',
  `checked` tinyint(4) DEFAULT '0',
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;