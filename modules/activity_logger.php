<?php
/**
* Module to start/stop general activities
*/
class activity_logger extends FrankIO {	
	public static function activity($input) {
		# Parse command
		$command = explode(' ', $input);
		
		# There should be 1 or 3 arguments sent
		if (count($command) <= 3) {
			# List all activities
			if (count($command) == 1) {
				$query = "SELECT DISTINCT `activity_name` FROM `activities` WHERE 1";
				$result = parent::$db->query($query);
				while ($row = mysql_fetch_assoc($result)) {
					$output[] = $row['activity_name'];
				}
				return $output;
			}
			elseif (count($command) == 2) {
				# pull last ten records of this activity
			}
			else {
				# Check if this activity has any open records
				$activity_open = self::_open_activity($command[1]);

				# Start activity
				if ($command[2] == strtolower('start')) {
					if ($activity_open === false) {
						$query = "INSERT INTO `activities` SET `activity_name` = '".mysql_real_escape_string($command[1])."', `activity_start` = NOW()";
						parent::$db->query($query);
						return $command[1]. ' started.';
					}
					else {
						return 'Activity already open.';
					}
				}
				# Stop activity
				elseif ($command[2] == strtolower('stop')) {
					if (is_numeric($activity_open)) {
						$query = "UPDATE `activities` SET `activity_stop` = NOW() WHERE `activity_id` = ".$activity_open;
						parent::$db->query($query);
						return $command[1]. ' stopped.';
					}
					else {
						return 'No open '.$command[1].' activity found.';
					}
				}
				else {
					return 'Third argument should be "start" or "stop".';
				}
			}
		}
		else {
			return 'Help.';
		}
	}
	
	private static function _open_activity($activity_name) {
		$query = "SELECT MAX(`activity_id`) as 'activity_id' FROM `activities` WHERE `activity_name` = '".mysql_real_escape_string($activity_name)."' AND `activity_stop` = 0";
		$result = parent::$db->query($query);
		if ($row = mysql_fetch_assoc($result)) {
			if (empty($row['activity_id'])) {
				return false;
			}
			return $row['activity_id'];
		}
	}
}