<?php

if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));

class ACL {
	private $triggers = null;
	private $lock_file = null;
	private $lock_time = null;
	private $email = null;

	function __construct( $triggers, $lock_file, $lock_time, $email, $unlock_code ) {
		$this->triggers = $triggers;
		$this->lock_time = $lock_time;
		$this->lock_file = __DIR__ . '/../' . basename($lock_file);
		$this->email = $email;
		$this->unlock_code = $unlock_code;
	}

	public function allowed( $match ) {
		if ( filter_var( $match, FILTER_VALIDATE_IP ) ) {
			return $this->auth_by_ip( $match );
		} else {
			return $this->auth_by_code( $match );
		}
	}

	private function auth_by_ip( $ip ) {
		foreach( $this->triggers as $trigger ) {
			if ( $trigger->ip == $ip ) {
				return $this->ping( $ip, $trigger->mac, 1 );
			}
		}
		return false;
	}

	private function auth_by_code( $code ) {
		if ( $code == $this->unlock_code ) {
			return true;
		}
		return false;
	}

	private function ping($ip, $mac, $ping_count = 1) {
		exec(sprintf('ping -c %d %s', $ping_count, $ip), $output, $res);
		if ( $res == 0 ) {
			exec(sprintf('/usr/sbin/arp -an | grep %s | cut -f 4 -d " "', $ip ), $mac_res );
			if ( !empty( $mac_res[0] ) and strtolower( $mac_res[0] ) == strtolower($mac) ) {
				if ( DEBUG ) { 
					printf( "%s (%s) available\n", $ip, $mac );
				}
				return true;
			} else {
				if ( DEBUG ) {
					printf( "%s (%s) mismatched MAC address (%s)\n", $ip, $mac, $mac_res[0] );
				}
			}
		} else {
			if( DEBUG ) {
				printf( "%s (%s) unavailable\n", $ip, $mac );
			}
		}
		return false;
	}

	public function lock_state() {
		touch($this->lock_file);
	}

	public function is_locked() {
		if ( file_exists( $this->lock_file ) ) {
			$stat = stat( $this->lock_file );
			if ( $stat['mtime'] > (time() - $this->lock_time) ) {
				if ( DEBUG ) {
					fprintf( STDERR, "Camera state locked, expires in %d seconds\n", 
						$this->lock_time - (time() - $stat['mtime']) );
				}
				return true;
			}
		}
		return false;
	}

	public function notify_state( $state, $ip = null) {
		$ip_msg = '';
		if ( $ip != null ) {
			$ip_msg = "by $ip";
		}
		if ( $state == Camera::$STATE_ARM ) {
			if ( DEBUG ) {
				fprintf(STDERR, "Camera armed\n");
			}
			mail( $this->email, '[motion detection] Camera armed', 'Camera armed' );
		} elseif ( $state == Camera::$STATE_DISARM ) {
			if ( DEBUG ) {
				fprintf(STDERR, "Camera disarmed\n");
			}
			mail( $this->email, '[motion detection] Camera disarmed', 'Camera disarmed' );
		} elseif ( $state == Camera::$STATE_UNAVAILABLE ) {
			if ( DEBUG ) {
				fprintf(STDERR, "Camera down\n");
			}
			mail( $this->email, '[motion detection] Camera down', 'Could not connect to camera' );
		} elseif ( $state == Camera::$STATE_DISARM_LOCK ) {
			if ( DEBUG ) {
				fprintf(STDERR, "Camera disarmed with command\n");
			}
			mail( $this->email, '[motion detection] Camera disarmed with command', "Camera disarmed $ip_msg" );
		} else {
		}
	}

	public function check_triggers($ping_count) {
		$res = '';
		foreach( $this->triggers as $trigger ) {
			if ( $this->ping( $trigger->ip, $trigger->mac, $ping_count ) ) {
				return Camera::$STATE_DISARM;
			}
		}
		return Camera::$STATE_ARM;
	}
}

