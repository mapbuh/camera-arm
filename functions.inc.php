<?php

function get_motion_detect_config($config) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=getMotionDetectConfig&usr=%s&pwd=%s', 
			$config['protocol'], $config['address'], $config['port'], urlencode($config['username']), 
			urlencode($config['password']) );

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

function check_triggers($config) {
	$res = '';
	foreach( $config['triggers'] as $tkey => $tval ) {
		exec(sprintf('ping -c 1 %s', $tval['ip']), $output, $res);
		if ( $res == 0 ) {
			exec(sprintf('/usr/sbin/arp -an | grep %s | cut -f 4 -d " "', $tval['ip'] ), $mac );
			if ( !empty( $mac[0] ) and strtolower( $mac[0] ) == strtolower($tval['mac']) ) {
				printf( "%s (%s) available\n", $tval['ip'], $tval['mac'] );
				return 0;
			} else {
				printf( "%s (%s) mismatched MAC address (%s)\n", $tval['ip'], $tval['mac'], $mac );
			}
		} else {
				printf( "%s (%s) unavailable\n", $tval['ip'], $tval['mac'] );
		}
	}
	return 1;
}

function notify_state( $config, $state ) {
	if ( $state == 1 ) {
		print "Camera armed\n";
		mail( $config['email'], '[motion detection] Camera armed', 'Camera armed' );
	} else {
		print "Camera disarmed\n";
		mail( $config['email'], '[motion detection] Camera disarmed', 'Camera disarmed' );
	}
}
