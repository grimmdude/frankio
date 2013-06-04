<?php
//	File:			db.class.php
//  Author: 		Adam Stitzer
//	Creation Date:	08/23/06
//
//  Description:	Database class with functions to connect, countRows, getRow, and close
//
//	Notes:
//
//  Usage:			$DB = new DB;
//					$q = $DB->query("SELECT * FROM `mytable` WHERE id='$id';");
//						...
//					$DB->close();
// 					destroyObject("DB");
//

class DB {
	function DB() {
		$this->host = "localhost";
		$this->db = "frankio";
		$this->user = "root";
		$this->pass = "root";
		$this->handle = @mysql_connect($this->host, $this->user, $this->pass);
		if (!$this->handle)
			return false;
		mysql_select_db($this->db, $this->handle);
		return true;
	}

	function __destruct()
	{
		//echo "<p>DESTRUCT CALLED</p>";
		$this->close();
		destroyObject("DB");
	}

	function query($query) {
		if ($this->handle)
			$result = @mysql_query($query, $this->handle);
		else
			$result = '';
		return $result;
	}
	function countRows($res) {
		if ( !empty($res) )
			$this->numrows = mysql_num_rows($res);
		else
			$this->numrows = 0;
		return $this->numrows;
	}
	function getRow($db_conn) {
		$row = mysql_fetch_array($db_conn);
		return $row;
	}
	function getLastID() {
		return mysql_insert_id();
	}
	function showQuery($query) {
		echo "\n\n<!-- $query -->\n\n";
	}

	function close() {
		//if (mysql_ping ($this->handle)) {
		//	@mysql_close($this->handle);
	}
}

function destroyObject ($name) {
	unset ($GLOBALS[$name]);
}
?>
