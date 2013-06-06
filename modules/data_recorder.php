<?php
/**
* 
*/
class data_recorder extends FrankIO
{
	protected static function record($input) {
		self::_init();
		
		# Parse command
		$command = explode(' ', $input);
		if (count($command) == 1) {
			$sth = self::$db->prepare("SELECT DISTINCT `data_name` FROM `data` WHERE 1");
			$sth->execute();
			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$output[] = $row['data_name'];
			}
			return $output;
		}
		elseif (count($command) == 2) {
			$sth = self::$db->prepare("SELECT * FROM `data` WHERE `data_name` = ?");
			$sth->execute(array($command[1]));
			$output = array();
			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$output[] = $row;
			}
			return $output;
		}
		elseif (count($command) == 3) {
			$sth = self::$db->prepare("INSERT INTO `data` (`data_name`, `data_value`, `data_date`) VALUES(?, ?, NOW())");
			$sth->execute(array($command[1], $command[2]));
			return 'Recorded';
		}
	}
	
	private static function _init() {
		$sth = self::$db->prepare("CREATE TABLE IF NOT EXISTS `data` (
		  `data_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `data_name` varchar(50) NOT NULL,
		  `data_value` varchar(50) NOT NULL,
		  `data_date` datetime NOT NULL,
		  PRIMARY KEY (`data_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		$sth->execute();
	}	
}
