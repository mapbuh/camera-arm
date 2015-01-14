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

	'debug'    => true,
	# devices which when available will trigger disarmed state
	# if none of these are found, camera motion detection will be enabled
	'triggers' => array(
		array(
			'ip' => '192.168.0.2',
			'mac' => 'aa:bb:cc:dd:ee:ff'
		),
		array(
			'ip' => '192.168.0.3',
			'mac' => 'ff:ee:dd:cc:bb:aa'
		)
	)
);
