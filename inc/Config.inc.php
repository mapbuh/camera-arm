<?php

class Config {
	function __construct($fname) {
		require($fname);
		foreach( $config as $key => $val ) {
			$this->{ $key } = $val;
		}
	}
}
