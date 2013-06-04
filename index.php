<?php
session_start();

if (!isset($_SESSION['salt'])) 
{
	$_SESSION['salt'] = sha1(uniqid());
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'true' && $_REQUEST['salt'] == $_SESSION['salt']) {
	require_once 'frankio.php';
	echo json_encode(FrankIO::execute(($_REQUEST['input'])));
	die;
}
else 
{
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<title>FrankIO</title>
		<link rel="stylesheet" href="css/bootstrap.min.css" media="all" />
		<link rel="stylesheet" href="css/bootstrap-responsive.min.css" media="all" />
	</head>
	<body>
		<div class="container-fluid">
			<div style="width:700px;margin:auto;">
				<h1>Frank IO</h1>
				<input type="text" name="input" id="input" style="width:425px;" disabled="disabled" />
				<input type="hidden" name="salt" value="<?php echo $_SESSION['salt']; ?>" id="salt" />
				<p class="lead" id="response"></p>
				<p id="options"></p>
				<?php /*
				<p>Try teaching me something by typing this:</p>
				<blockquote>
					When I say 'I'm cool' you say 'I know!'
				</blockquote>
				<p>Look up something in Wikipedia like this:</p>
				<blockquote>
					wiki San Clemente
				</blockquote>
				*/ ?>	
			</div>
		</div>
		<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
		<script type="text/javascript" src="js/all.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
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