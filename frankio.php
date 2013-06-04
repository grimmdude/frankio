<?php
class FrankIO {
	public static $db;
	public static $modules = array();
	
	public static function execute($input) {
		# Initialize
		self::init();
		
		self::log_input($input);
		
		# Grab command
		$command = explode(' ', $input);
		$command = $command[0];
		
		# Check if any modules recognize this command
		foreach (self::$modules as $module) {
			if (method_exists($module, $command)) {
				return array('output' => $module::$command($input));
			}
		}
		
		return array('output' => 'No command was found matching '.$input);
	}
	
	private static function init() {	
		# Grab DB
		try {
			self::$db = new PDO("mysql:host=localhost;dbname=frankio", 'root', 'root');
		} catch (Exception $e) {
			echo $e->getMessage();
		}

		# Auto load modules when called
		spl_autoload_register(function ($module_name) {
			require_once 'modules/' . $module_name . '.php';
		});
		
		# Init modules
		if ($handle = opendir('modules')) {
			while (false !== ($module = readdir($handle))) {
				if (!in_array($module, array('.','..'))) {
					$module = rtrim($module, '.php');
					self::$modules[] = $module;
				}
		    }
		}
	}
	
	/**
	 * Logs input
	 */
	static private function log_input($input) {
		$query = "INSERT INTO `input_log` (`input`,`ip`,`date_added`) VALUES('".mysql_real_escape_string($input)."','".$_SERVER['REMOTE_ADDR']."',NOW())";
		if (self::$db->query($query)) {
			return true;
		}
		return false;
	}
}