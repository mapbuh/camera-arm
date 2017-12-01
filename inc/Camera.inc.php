<?php

class Camera {
	public static $STATE_DISARM = 0;
	public static $STATE_ARM = 1;
	public static $STATE_UNAVAILABLE = 2;
	public static $STATE_DISARM_LOCK = 3;

	function __construct($proto, $address, $port, $username, $password) {
		$this->proto	= $proto;
		$this->address	= $address;
		$this->port 	= $port;
		$this->username	= $username;
		$this->password	= $password;
	}

	public function disarm() {
		$motion_config = $this->motion_config();
		if ( $motion_config === false ) {
			throw new Exception('Camera inaccessible');
		}
		$motion_config->isEnable = 0;
		$this->motion_config( $motion_config);
	}

	public function motion_config( $mc = null) {
		if ( empty( $mc ) ) {
			return $this->motion_config_get();
		} else {
			return $this->motion_config_set( $mc );
		}
	}

	private function motion_config_get() {
		$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=getMotionDetectConfig&usr=%s&pwd=%s', 
				$this->proto, $this->address, $this->port, urlencode($this->username), 
				urlencode($this->password) );

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

	function motion_config_set($data) {
		$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=setMotionDetectConfig&usr=%s&pwd=%s', 
			$this->proto, $this->address, $this->port, urlencode($this->username), 
			urlencode($this->password) );
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

	public function state_hr() {
		switch( $this->state() ) {
			case Camera::$STATE_ARM:
				return 'State: armed';
			case Camera::$STATE_DISARM:
				return 'State: disarmed';
			default:
				return 'State: unknown';
		}
	}

	public function state($new_state = null, $preset = null, $motion_config_override = null) {
		if ( $new_state === null and $preset === null and $motion_config_override === null ) {
			return $this->state_get();
		} else {
			return $this->state_set( $new_state, $preset, $motion_config_override );
		}
	}

	public function state_get() {
		$motion_config = $this->motion_config();
		if ( empty( $motion_config ) or $motion_config->result != 0 ) {
			return Camera::$STATE_UNAVAILABLE;
		} elseif( $motion_config->isEnable == 0 ) {
			return Camera::$STATE_DISARM;
		} elseif( $motion_config->isEnable == 1 ) {
			return Camera::$STATE_ARM;
		}
	}

	public function state_set( $new_state, $preset = null, $motion_config_override = null) {
		$motion_config = $this->motion_config();
		if ( $motion_config->isEnable != $new_state ) {
			$motion_config->isEnable = $new_state;
			if ( $new_state && !empty($preset) ) {
				$this->ptz_goto_preset( $preset );
				sleep(1);
			}
			if ( $motion_config->isEnable == 1 and !empty( $motion_config_override )) {
				$this->override_config($motion_config, $motion_config_override);
			}
			$this->motion_config($motion_config);
			return true;
		}
		return false;
	}

	private function override_config(&$motion_config, $motion_config_override) {
		foreach( $motion_config_override as $key => $val ) {
			$motion_config->{$key} = $val;
		}
	}


	private function ptz_goto_preset($preset) {
		$url = sprintf( '%s://%s:%d/cgi-bin/CGIProxy.fcgi?cmd=ptzGotoPresetPoint&usr=%s&pwd=%s&name=%s', 
			$this->proto, $this->address, $this->port, urlencode($this->username), 
			urlencode($this->password), urlencode( $preset ) );

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
}
