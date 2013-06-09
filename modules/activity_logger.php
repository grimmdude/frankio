<?php
/**
* Module to start/stop general activities
*/
class activity_logger extends FrankIO {	
	protected static $module_name = 'Activity Logger';

	protected static function activity($input) {
		self::_init();
	
		# Parse command
		$command = explode(' ', $input);
		
		# There should be 1 or 3 arguments sent
		if (count($command) <= 3) {
			# List all activities
			if (count($command) == 1) {
				$sth = self::$db->prepare("SELECT DISTINCT `activity_name` FROM `activities` WHERE 1");	
				$sth->execute();
				
				if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
					$output = '<ul>';
					while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
						$output .= '<li>'.$row['activity_name'].'</li>';
					}
					$output .= '</ul>';
				}

				if (isset($output)) {
					return $output;
				}
				return 'No activities found.';
			}
			elseif (count($command) == 2) {
				# pull last ten records of this activity
				$sth = self::$db->prepare("SELECT * FROM `activities` WHERE `activity_name` = ?");
				$sth->execute(array($command[1]));
				$output = '<table class="table">';
				$output .= '<tr><th>Activity</th><th>Start</th><th>Stop</th></tr>';
				while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
					$output .= '<tr><td>'.$row['activity_name'].'</td>';
					$output .= '<td>'.$row['activity_start'].'</td>';
					$output .= '<td>'.$row['activity_stop'].'</td></tr>';
				}
				$output .= '</table>';
				return $output;
			}

			else {
				# Check if this activity has any open records
				$activity_open = self::_open_activity($command[1]);

				# Start activity
				if ($command[2] == strtolower('start')) {
					if ($activity_open === false) {
						$sth = self::$db->prepare("INSERT INTO `activities` (`activity_name`, `activity_start`) VALUES(?, NOW())");
						$sth->execute(array($command[1]));
						return $command[1]. ' started at '.date('g:ia').'.';
					}
					else {
						return '<p>Activity already open.</p>';
					}
				}
				# Stop activity
				elseif ($command[2] == strtolower('stop')) {
					if (is_numeric($activity_open)) {
						$sth = self::$db->prepare("UPDATE `activities` SET `activity_stop` = NOW() WHERE `activity_id` = ?");
						$sth->execute(array($activity_open));
						return $command[1]. ' stopped at '.date('g:ia').'.';
					}
					else {
						return '<p>No open '.$command[1].' activity found.</p>';
					}
				}
				else {
					return '<p>Third argument should be "start" or "stop".</p>';
				}
			}
		}
		else {
			return 'Help.';
		}
	}
	
	/**
	 * Checks if an activity is open
	 * @param $activity_name
	 * @return BOOL
	 */
	private static function _open_activity($activity_name) {
		$sth = self::$db->prepare("SELECT MAX(`activity_id`) as 'activity_id' FROM `activities` WHERE `activity_name` = ? AND `activity_stop` = 0");
		$sth->execute(array($activity_name));
		if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			if (empty($row['activity_id'])) {
				return false;
			}
			return $row['activity_id'];
		}
		return false;
	}
	
	private static function	_init() {
		$sth = self::$db->prepare("CREATE TABLE IF NOT EXISTS `activities` (
		  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
		  `activity_name` varchar(50) NOT NULL,
		  `activity_start` datetime NOT NULL,
		  `activity_stop` datetime NOT NULL,
		  PRIMARY KEY (`activity_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		$sth->execute();
	}

	protected static function _help() {
		return '<p>This is help for activity logger</p>';
	}
}
