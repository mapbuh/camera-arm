<?php

define('DEBUG', false);

spl_autoload_register(function ($class_name) {
    include 'inc/' . $class_name . '.inc.php';
});

$config = new Config(__DIR__ . '/config.inc.php');
$camera = new Camera($config->protocol, $config->address, $config->port, $config->username, $config->password);
$acl	= new ACL($config->triggers, $config->lock_file, $config->lock_time, $config->email, $config->unlock_code);

$action = empty($_REQUEST['action']) ? 'home' : $_REQUEST['action'];
$code   = empty($_REQUEST['code']) ? '' : $_REQUEST['code'];
$msg 	= empty($_REQUEST['msg']) ? '' : $_REQUEST['msg'];
$authenticated = false;

if ( !empty( $code ) and $acl->allowed( $code ) ) {
	$authenticated = true;
} elseif ( $acl->allowed( $_SERVER['REMOTE_ADDR'] ) ) {
	$authenticated = true;
}

if ( $authenticated ) {
	$msg = $camera->state_hr() . '<br>' . $msg;
}


switch( $action ) {
	case 'unlock':
		if ( $authenticated ) {
			$camera->disarm();
			$acl->lock_state();
			$acl->notify_state(Camera::$STATE_DISARM_LOCK, $_SERVER['REMOTE_ADDR'] );
			header("Location: camera-www.php?action=home&msg=" . urlencode( 'Disarmed for ' . $config->lock_time . ' seconds'));
			exit;
		} else {
			header("Location: camera-www.php?action=home&msg=Unauthorized");
			exit;
		}
		break;
	default:
		# home
		break;
}

?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<h1>Camera control panel</h1>
		<?php if ( !empty( $msg ) ) { ?>
			<h2 style="color: #f77;"><?= $msg ?></h2>
		<?php } ?>
		<?php if ( $action == 'home' ) { ?>
			<form method="post" action="camera-www.php" id="form1">
				<input type="hidden" name="action" value="unlock">
				<?php if ( !$authenticated ) { ?>
					<input type="number" name="code" value="" id="codeInp" style="height: 1.3em;">
					<script>
						document.addEventListener("DOMContentLoaded", function(event) { 
							document.getElementById('codeInp').focus();
							document.getElementById('codeInp').click();
						});
					</script>
				<?php } ?>
				<input type="submit" value="Unlock" style="padding: 10px;">
			</form>
		<?php } ?>
	</body>
</html>
