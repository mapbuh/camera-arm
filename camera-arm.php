#!/usr/bin/php

<?php

require('config.inc.php');
require('functions.inc.php');

$motion_config = get_motion_detect_config($config);
$desired_state = check_triggers($config);
if ( $motion_config->isEnable != $desired_state ) {
	$motion_config->isEnable = $desired_state;
	set_motion_detect_config($config, $motion_config);
	notify_state($config, $desired_state );
}
