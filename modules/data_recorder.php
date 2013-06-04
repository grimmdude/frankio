<?php
/**
* 
*/
class data_recorder extends FrankIO
{
	public static function record($input) {
		# Parse command
		$command = explode(' ', $input);
		if (count($command) == 1) {
			$sth = parent::$db->prepare("SELECT DISTINCT `data_name` FROM `data` WHERE 1");
			$sth->execute();
			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$output[] = $row['data_name'];
			}
			return $output;
		}
		elseif (count($command) == 2) {
			$sth = parent::$db->prepare("SELECT * FROM `data` WHERE `data_name` = ?");
			$sth->execute(array($command[1]));
			$output = array();
			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$output[] = $row;
			}
			return $output;
		}
		elseif (count($command) == 3) {
			$sth = parent::$db->prepare("INSERT INTO `data` (`data_name`, `data_value`, `data_date`) VALUES(?, ?, NOW())");
			$sth->execute(array($command[1], $command[2]));
			return 'Recorded';
		}
	}	
}
