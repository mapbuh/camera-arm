#!/usr/bin/php
<?php

/**
 * List PTZ presets
 */

require('config.inc.php');
require('functions.inc.php');

$res = ptz_list_presets( $config );
print "Predefined presets:\n";
print "===================\n";
foreach( $res as $rkey => $rval ) {
	printf( "$rval\n" );
}
print "===================\n";
