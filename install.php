<?php
if (file_exists('config.php')) {
	header('Location: index.php');
	exit;
}

require_once 'frankio.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (empty($_POST['dbhost'])) {
		$error[] = 'Please enter a host.';
	}
	elseif (empty($_POST['dbname'])) {
		$error[] = 'Please enter a database name.';
	}
	elseif (empty($_POST['dbuser'])) {
		$error[] = 'Please enter a database user.';
	}
	elseif (empty($_POST['dbpass'])) {
		$error[] = 'Please enter a database password.';
	}
	elseif (!Frankio::get_db(array('dbhost'=>$_POST['dbhost'],'dbname'=>$_POST['dbname'],'dbuser'=>$_POST['dbuser'],'dbpass'=>$_POST['dbpass']))) {
		$error[] = "Can't connect to database with given credentials.";
	}
	elseif ($fh = fopen('config.php', 'w')) {
		// Write the config file
		$config[] = "<?php";
		$config[] = "class Config {";
		$config[] = "\tpublic static \$dbhost = '".$_POST['dbhost']."';";
		$config[] = "\tpublic static \$dbname = '".$_POST['dbname']."';";
		$config[] = "\tpublic static \$dbuser = '".$_POST['dbuser']."';";
		$config[] = "\tpublic static \$dbpass = '".$_POST['dbpass']."';";
		$config[] = "}";

		if(fwrite($fh, implode("\n", $config))) {
			header('Location: index.php');
		}
		else {
			$error[] = 'Error writing to config.php.  Check permissions.';
		}
	}
	else {
		$error[] = 'Cannot open config.php for writing.  Check permissions.';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="shortcut icon" href="favicon.ico" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>FrankIO</title>
	<link rel="stylesheet" href="css/bootstrap.min.css" media="all" />
	<link rel="stylesheet" href="css/all.css" media="all" />
</head>
<body>
	<div class="container-fluid">
		<div style="width:700px;margin:auto;">
			<h1>Config</h1>
			<?php if (isset($error)): ?>
				<ul>
					<?php foreach ($error as $error_message): ?>
						<li><?php echo $error_message; ?></li>
					<?php endforeach ?>
				</ul>
			<?php endif ?>
			<form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
				<div class="control-group">
					<label class="control-label" for="dbhost">Host</label>
					<div class="controls">
						<input type="text" name="dbhost" id="dbhost" value="localhost" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="dbname">Database</label>
					<div class="controls">
						<input type="text" id="dbname" name="dbname" value="<?php echo isset($_POST['dbname']) ? $_POST['dbname'] : ''; ?>" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="dbuser">User</label>
					<div class="controls">
						<input type="text" id="dbuser" name="dbuser" value="<?php echo isset($_POST['dbuser']) ? $_POST['dbuser'] : ''; ?>" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="dbpass">Password</label>
					<div class="controls">
						<input type="text" id="dbpass" name="dbpass" value="<?php echo isset($_POST['dbpass']) ? $_POST['dbpass'] : ''; ?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn">Submit</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</body>

