<?php
/**
* 
*/
class data_recorder extends FrankIO
{
	protected static $module_name = 'Data Recorder';

	protected static function record($input) {
		self::_init();
		
		# Parse command
		$command = explode(' ', $input);
		if (count($command) == 1) {
			$sth = self::$db->prepare("SELECT DISTINCT `data_name` FROM `data` WHERE 1 ORDER BY `data_name`");
			$sth->execute();
			$output = '<ul>';
			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$output .= '<li>'.$row['data_name'].'</li>';
			}
			$output .= '</ul>';
			return $output;
		}
		elseif (count($command) == 2) {
			$sth = self::$db->prepare("SELECT * FROM `data` WHERE `data_name` = ? ORDER BY `data_date` ASC LIMIT 10");
			$sth->execute(array($command[1]));
			$output = '<table class="table">';
			$output .= '<tr><th>Value</th><th>Date</th></tr>';
			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$output .= '<tr><td>'.$row['data_value'].'</td><td>'.$row['data_date'].'</td></tr>';
			}
			$output .= '</table>';
			return $output;
		}
		elseif (count($command) == 3) {
			$sth = self::$db->prepare("INSERT INTO `data` (`data_name`, `data_value`, `data_date`) VALUES(?, ?, NOW())");
			$sth->execute(array($command[1], $command[2]));
			return $command[1].' recorded as '.$command[2];
		}
	}
	
	protected static function _help() {
		return '<p>Help for data logger</p>';
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
