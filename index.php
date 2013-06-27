<?php
session_start();
if (!isset($_SESSION['salt'])) {
	$_SESSION['salt'] = sha1(uniqid());
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'true') {
	if ($_REQUEST['salt'] == $_SESSION['salt']) {
		require_once 'frankio.php';
		echo json_encode(FrankIO::execute(($_REQUEST['input'])));
		exit;
	}
	else {
		echo 'No bots please.';
	}
}
else 
{
	# Check if everything is installed
	if (!file_exists('config.php')) {
		header('Location: install.php');
		exit;
	}
	?>
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
				<h1>Frank IO</h1>
				<input type="text" name="input" id="input" style="width:98%;" disabled="disabled" placeholder="Type command..." />
				<input type="hidden" name="salt" value="<?php echo $_SESSION['salt']; ?>" id="salt" />
				<div id="response"></div>
				<?php /*
				<h2>Command History</h2>
				<div id="command_history"></div>
				*/ ?>
				<p><a href="javascript:;" id="show_help">Show Help</a></p>
				<div id="help" style="display:none;">
					<p>Frank IO is an online command based personal data logger. It’s expandable with different ‘modules’, each of which can define multiple commands.</p>

					<p>It’s still in it’s early stages, but soon will be able to output reports on recorded data and possibly setup reminders.</p>

					<h3>Commands</h3>

					<h4>Activity Logger</h4>

					<p>Start activities by typing:</p>

					<pre>activity gym start</pre>
					<p>Stop activities by typing:</p>

					<pre>activity gym stop</pre>
					<p>Where ‘gym’ can be any activity you like.</p>

					<p>To view a list of all logged activities just type:</p>

					<pre>activity</pre>
					<h4>Data Recorder</h4>

					<p>The data recorder module provides the record command which you can use to save any type of data you like.</p>

					<p>To record some data type:</p>

					<pre>record weight 172</pre>
					<p>To view a report of a particular type of data type:</p>

					<pre>record weight</pre>
					<p>Where weight is whatever data key you like.</p>

					<p>To view a list of all recorded data keys type:</p>

					<pre>record</pre>

					<p><a href="https://github.com/grimmdude/frankio">GitHub Repo</a></p>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
		<script type="text/javascript" src="js/all.js"></script>
		<?php if (!strstr($_SERVER['SERVER_NAME'], 'local')): ?>
			<!--Analytics-->
			<script type="text/javascript">

			  var _gaq = _gaq || [];
			  _gaq.push(['_setAccount', 'UA-1454657-6']);
			  _gaq.push(['_trackPageview']);

			  (function() {
			    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			  })();
			</script>
		<?php endif ?>
	</body>
	</html>
	<?php
}
?>
