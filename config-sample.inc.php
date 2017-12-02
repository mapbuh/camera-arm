<?php
$config = array(
	# notifications email
	'email'    => 'notify@example.com',
	# camera settings
	'username' => 'username',
	'password' => 'password',
	'protocol' => 'http',
	'address'  => '192.168.0.1',
	'port'     => 8080,
	# name of PTZ preset to return camera to before arming
	# you can find predefined presets by running list-presets.php
	'preset'   => '5',
	
	# number of pings to use when discovering network device
	'ping_count' => 1,

	'debug'    => true,
	# devices which when available will trigger disarmed state
	# if none of these are found, camera motion detection will be enabled
	'triggers' => (object) array(
		array(
			'ip' => '192.168.0.2',
			'mac' => 'aa:bb:cc:dd:ee:ff'
		),
		array(
			'ip' => '192.168.0.3',
			'mac' => 'ff:ee:dd:cc:bb:aa'
		)
	),
	# when disabling motion detection, lock state for
	'lock_time'	=> 10 * 60,  
	'lock_file'	=> 'lockfile',
	'throttle_dir'	=> 'throttle',
	'unlock_code'	=> '6969',
	'motion_config' => (object) array(
		'linkage'		=> 3,
		'snapInterval'		=> 1,
		'sensitivity' 		=> 0,
		'triggerInterval'	=> 0,
		'schedule0'		=> 281474976710655,
		'schedule1'		=> 281474976710655,
		'schedule2'		=> 281474976710655,
		'schedule3'		=> 281474976710655,
		'schedule4'		=> 281474976710655,
		'schedule5'		=> 281474976710654,
		'schedule6'		=> 281474976710655,
		'area0'			=> 1023,
		'area1'			=> 1023,
		'area2'			=> 1023,
		'area3'			=> 1023,
		'area4'			=> 1023,
		'area5'			=> 1023,
		'area6'			=> 1023,
		'area7'			=> 1023,
		'area8'			=> 1023,
		'area9'			=> 1023,
	)
);
