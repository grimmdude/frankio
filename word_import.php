<?php
require_once('incl/db.class.php');
$db = new DB;
$words = file('english.txt');

foreach ($words as $value) {
	$query = "INSERT INTO `words` (`word`) VALUES('".$value."')";
	$db->query($query);
}

echo 'done';

?>