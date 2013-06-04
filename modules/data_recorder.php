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
			$query = "SELECT DISTINCT `data_name` FROM `data` WHERE 1";
			$result = parent::$db->query($query);
			while ($row = mysql_fetch_assoc($result)) {
				$output[] = $row['data_name'];
			}
			return $output;
		}
		elseif (count($command) == 2) {
			$query = "SELECT * FROM `data` WHERE `data_name` = '".mysql_real_escape_string($command[1])."'";
			$result = parent::$db->query($query);
			$output = array();
			while ($row = mysql_fetch_assoc($result)) {
				$output[] = $row;
			}
			return $output;
		}
		elseif (count($command) == 3) {
			$query = "INSERT INTO `data` (`data_name`, `data_value`, `data_date`) VALUES('".$command[1]."', '".$command[2]."', NOW())";
			parent::$db->query($query);
			return 'Recorded';
		}
	}	
}
