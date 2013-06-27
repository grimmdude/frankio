<?php if (count($_POST)): ?>
	<?php 
	if ($fh = fopen('config.php', 'w')) {
		// Write the config file
		$config[] = '<?php';
		$config[] = 'class Config {';
		$config[] = 'public static $dbhost = "'.$_POST['dbhost'].'";';
		$config[] = 'public static $dbname = "'.$_POST['dbname'].'";';
		$config[] = 'public static $dbuser = "'.$_POST['dbuser'].'";';
		$config[] = 'public static $dbpass = "'.$_POST['dbpass'].'";';
		$config[] = '}';

		if(fwrite($fh, implode("\n", $config))) {
			echo 'Config file created succesfully!';
		}
		else {
			echo 'Error writing to config.php';
		}
	}
	else {
		echo 'Cannot open config.php for writing.  Check permissions.';
	}
	?>
<?php else: ?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<title>FrankIO</title>
		<link rel="stylesheet" href="css/bootstrap.min.css" media="all" />
		<link rel="stylesheet" href="css/all.css" media="all" />
	</head>
	<body>
		<div class="container-fluid">
			<div style="width:700px;margin:auto;">
				<h1>Config</h1>
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
							<input type="text" id="dbname" name="dbname" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="dbuser">User</label>
						<div class="controls">
							<input type="text" id="dbuser" name="dbuser" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="dbpass">Password</label>
						<div class="controls">
							<input type="text" id="dbpass" name="dbpass" />
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
<?php endif; ?>
