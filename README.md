Camera arm/disarm 
==============

A script which will disable/enable FOSCAM FL9831W (and few others which use the same CGI API) motion detection whenever certain devices are found in the network.

Setup
==============

Just edit configuration inside camera-arm.php and create a cron job to run camera-arm.php every minute or so.
When the devices found inside triggers section are available in the network, the motion detection will be stopped. When none of them are found then motion detection will be enabled again. It's useful to link it to your mobile phone, so when you phone connects to your WiFi network the camera will automatically stop sending motion alarms.
