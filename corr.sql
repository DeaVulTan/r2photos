-- --------------------------------------------------------
-- Хост:                         mysql.interlabs.lan
-- Версия сервера:               5.1.49-3 - (Debian)
-- ОС Сервера:                   debian-linux-gnu
-- HeidiSQL Версия:              9.0.0.4865
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица dev_r2photos.corr
CREATE TABLE IF NOT EXISTS `corr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ord_id` int(11) unsigned NOT NULL DEFAULT '0',
  `quest` text NOT NULL,
  `answer` text NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `idate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='переписка';

-- Экспортируемые данные не выделены.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
