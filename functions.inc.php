<?php

define( 'STATE_DISARM',      0 );
define( 'STATE_ARM',         1 );
define( 'STATE_UNAVAILABLE', 2 );

function get_motion_detect_config($config) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=getMotionDetectConfig&usr=%s&pwd=%s', 
			$config['protocol'], $config['address'], $config['port'], urlencode($config['username']), 
			urlencode($config['password']) );

	$curl = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	$res_xml = curl_exec( $curl );
	curl_close( $curl );

	try {
		$res = new SimpleXMLElement( $res_xml );
	} catch ( Exception $e ) {
		return false;
	}
	$data = new stdClass();
	foreach( (array)$res as $rkey => $rval ) {
		$data->{$rkey} = $rval;
	}
	if ( $res->result != 0 ) {
		return false;
	} else {
		return $data;
	}

}

function set_motion_detect_config($config,$data) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=setMotionDetectConfig&usr=%s&pwd=%s', 
			$config['protocol'], $config['address'], $config['port'], urlencode($config['username']), 
			urlencode($config['password']) );
	foreach( $data as $dkey => $dval ) {
		$url .= sprintf( '&%s=%s', urlencode( $dkey ), urlencode( $dval ) );
	}

	$curl = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	$res_xml = curl_exec( $curl );
	curl_close( $curl );

	$res = new SimpleXMLElement( $res_xml );
	$data = new stdClass();
	foreach( (array)$res as $rkey => $rval ) {
		$data->{$rkey} = $rval;
	}
	if ( $res->result != 0 ) {
		return false;
	} else {
		return $data;
	}

}

function ptz_goto_preset($config) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=ptzGotoPresetPoint&usr=%s&pwd=%s&name=%s', 
			$config['protocol'], $config['address'], $config['port'], urlencode($config['username']), 
			urlencode($config['password']), urlencode( $config['preset'] ) );

	$curl = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	$res_xml = curl_exec( $curl );
	curl_close( $curl );

	$res = new SimpleXMLElement( $res_xml );

	if ( $res->result != 0 ) {
		return false;
	} else {
		return true;
	}

}

function ptz_list_presets($config) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=getPTZPresetPointList&usr=%s&pwd=%s', 
			$config['protocol'], $config['address'], $config['port'], urlencode($config['username']), 
			urlencode($config['password']) );

	$curl = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	$res_xml = curl_exec( $curl );
	curl_close( $curl );

	$res = new SimpleXMLElement( $res_xml );
	$data = array();
	for( $i = 0; $i < $res->cnt; $i++ ) {
		$data[] = $res->{"point$i"} . '' ;
	}

	if ( $res->result != 0 ) {
		return false;
	} else {
		return $data;
	}

}

function check_triggers($config) {
	$res = '';
	foreach( $config['triggers'] as $tkey => $tval ) {
		exec(sprintf('ping -c %d %s', empty($config['ping_count']) ? 1 : $config['ping_count'], $tval['ip']), $output, $res);
		if ( $res == 0 ) {
			exec(sprintf('/usr/sbin/arp -an | grep %s | cut -f 4 -d " "', $tval['ip'] ), $mac );
			if ( !empty( $mac[0] ) and strtolower( $mac[0] ) == strtolower($tval['mac']) ) {
				if ( $config['debug'] ) { 
					printf( "%s (%s) available\n", $tval['ip'], $tval['mac'] );
				}
				return 0;
			} else {
				if ( $config['debug'] ) {
					printf( "%s (%s) mismatched MAC address (%s)\n", $tval['ip'], $tval['mac'], $mac );
				}
			}
		} else {
			if( $config['debug'] ) {
				printf( "%s (%s) unavailable\n", $tval['ip'], $tval['mac'] );
			}
		}
	}
	return 1;
}

function notify_state( $config, $state ) {
	if ( $state == STATE_ARM ) {
		if ( $config['debug'] ) {
			print "Camera armed\n";
		}
		mail( $config['email'], '[motion detection] Camera armed', 'Camera armed' );
	} elseif ( $state == STATE_DISARM ) {
		if ( $config['debug'] ) {
			print "Camera disarmed\n";
		}
		mail( $config['email'], '[motion detection] Camera disarmed', 'Camera disarmed' );
	} elseif ( $state == STATE_UNAVAILABLE ) {
		if ( $config['debug'] ) {
			print "Camera down\n";
		}
		mail( $config['email'], '[motion detection] Camera down', 'Could not connect to camera' );
	} else {
	}
}
