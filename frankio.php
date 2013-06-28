<?php
if (file_exists('config.php')) {
	require_once 'config.php';
}

class FrankIO {
	private static $modules = array();
	protected static $error = false;
	protected static $db;
	
	/**
	 * Execute command
	 * @param $input
	 * @return Output array
	 */
	public static function execute($input) {
		# Initialize
		if (self::init()) {
			self::log_input($input);

			# Grab command
			$command = explode(' ', $input);
			$command = $command[0];
			
			if ($command == 'help') {
				return array('input' => $input, 'output' => self::help());	
			}
			else {
				# Check if any modules recognize this command
				foreach (self::$modules as $module) {
					if (method_exists($module, $command)) {
						return array('input' => $input, 'output' => $module::$command($input));
					}
				}
			}
			return array('input' => $input, 'output' => 'No command was found matching '.$command);
		}
		else {
			return array('input' => $input, 'output' => 'Error: '.self::$error);
		}
	}
	
	/**
	 * Function to initialize database and modules
	 * @return BOOL
	 */
	private static function init() {
		if (self::get_db()) {
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
			return true;
		}
		else {
			return false;
		}
	}
	
	public static function get_db($dbcreds = false) {
		if (is_array($dbcreds) && count($dbcreds) == 4) {
			$dbhost = $dbcreds['dbhost'];
			$dbname = $dbcreds['dbname'];
			$dbuser = $dbcreds['dbuser'];
			$dbpass = $dbcreds['dbpass'];
		}
		else {
			$dbhost = Config::$dbhost;
			$dbname = Config::$dbname;
			$dbuser = Config::$dbuser;
			$dbpass = Config::$dbpass;
		}
		
		try {
		    self::$db = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpass);
			self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
		   	//echo 'Connection failed: ' . $e->getMessage();
			return false;
		}
		return true;
	}
	
	/**
	 * Logs input
	 * @param $input
	 * @return BOOL
	 */
	private static function log_input($input) {
		$sth = self::$db->prepare("INSERT INTO `input_log` (`input`,`ip`,`date_added`) VALUES(?, ?, NOW())");
		return $sth->execute(array($input, $_SERVER['REMOTE_ADDR']));
	}
	
	private static function handle_error($error_message) {
		self::$error = $error_message;
	}

	private static function help() {
		$help_html = '';
		foreach (self::$modules as $module) {
			$help_html .= '<h2>'.$module::$module_name.'</h2>';
			$help_html .= $module::_help();
			
			if (property_exists($module, 'module_commands')) {
				$help_html .= '<h3>Commands</h3>';
				$help_html .= '<ul>';
				foreach ($module::$module_commands as $command) {
					$help_html .= '<li>'.$command.'</li>';
				}
				$help_html .= '</ul>';
			}			
		}
		return $help_html;
	}
}
