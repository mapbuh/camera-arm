#!/usr/bin/php
<?php

require('config.inc.php');
require('functions.inc.php');

$motion_config = get_motion_detect_config($config);
if ( $motion_config === false ) {
	notify_state( $config, STATE_UNAVAILABLE );
	exit;
}
$desired_state = check_triggers($config);
if ( $motion_config->isEnable != $desired_state ) {
	$motion_config->isEnable = $desired_state;
	if ( $desired_state && !empty($config['preset']) ) {
		ptz_goto_preset( $config );
		sleep(1);
	}
	set_motion_detect_config($config, $motion_config);
	notify_state($config, $desired_state );
}
