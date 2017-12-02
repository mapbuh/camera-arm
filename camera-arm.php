#!/usr/bin/php
<?php

define('DEBUG', true);

spl_autoload_register(function ($class_name) {
    include 'inc/' . $class_name . '.inc.php';
});

$config = new Config(__DIR__ . '/config.inc.php');
$camera = new Camera($config->protocol, $config->address, $config->port, $config->username, $config->password);
$acl	= new ACL($config);

if ( $acl->is_locked() ) {
	return;
}

$new_state = $acl->check_triggers( $config->ping_count );
if ( $camera->state( $new_state, $config->preset, $config->motion_config ) ) {
	$acl->notify_state($new_state );
}


