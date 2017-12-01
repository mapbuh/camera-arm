<?php



function get_io_alarm_config($config) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=getIOAlarmConfig&usr=%s&pwd=%s', 
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

function get_dev_state($config) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=getDevState&usr=%s&pwd=%s', 
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

function set_dev_state($config,$data) {
	$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=setDevState&usr=%s&pwd=%s', 
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





