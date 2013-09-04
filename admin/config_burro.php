<?php

//BURRO SPECIFIC CONFIGURATION
//list of available servers:
//for each server add an associative array with its params
$BURRO_SERVERS = array(
	array(
		"name" => "myispconfigserver",  //a unique name for this server. this name will be saved in database for each hosting service, so that you can manage hostings on multiple servers. do not use spaces, commas, or any other special chars ( allowed chars are: a-z|A-Z|0-9|.-_ )
		"software" => "ispconfig",  //the control software used on the server to be controlled. right now only "ispconfig" software is under development, so leave default
		"ispconfig_uri" => "http://myispconfigserver.example.com/",  //something like: https://myispconfigserver.example.com/
		"ispconfig_username" => "admin",  //username for your ispconfig administrator
		"ispconfig_password" => "mypassword",  //password for your ispconfig administrator
		"ispconfig_soap_location" => "https://myispconfigserver.example.com:8080/remote/index.php",  //something like: https://myispconfigserver.example.com:8080/remote/index.php (or localhost:8080/remote/index.php if opencart server and ispconfig server are the same)
		"ispconfig_soap_uri" => "https://myispconfigserver.example.com:8080/remote/"  //something like: https://myispconfigserver.example.com:8080/remote/ (or localhost:8080/remote/ if opencart server and ispconfig server are the same)
		"ispconfig_default_mailserver" => 1,  //it's the id of the mailserver if you have more than one in ispconfig.
		"ispconfig_default_webserver" => 1,  //it's the id of the webserver if you have more than one in ispconfig.
		"ispconfig_default_dnsserver" => 1,  //it's the id of the dnsserver if you have more than one in ispconfig.
		"ispconfig_default_dbserver" => 1,  //it's the id of the dbserver if you have more than one in ispconfig.
		"ispconfig_db_name" => "dbispconfig",  //it's the name of the ispconfig mysql database. there are some queries needed by burro that doesn't exist in ispconfig API, so burro executes them directly on ispconfig database. NOTE: the ispconfig database must be accessible from opencart server if ispconfig is on a different server than opencart!
		"ispconfig_db_username" => "root",  //it's the username of the ispconfig mysql database. you can also use root mysql user here.
		"ispconfig_db_pw" => "mydbrootpassword",  //it's the pw of the ispconfig (or root) mysql user.
		"ispconfig_db_host" => "mydbserver.example.com"  //it's the host of the ispconfig mysql database. could be localhost (if ispconfig is on the same server as opencart), or an external address.
	),
	array(
		"name" => "myserver02"
	),
	array(
		"name" => "myserver03"
	)
);

//the default server:
//when customers buy new services, this is the server where their services will be activated
//even if you change this (for example because your previous server is running out of resources), all services sold till now will remain associated to their original server
$BURRO_DEFAULT_SERVER_NAME = "myispconfigserver"; //must be the name of one of the BURRO_SERVERS above


/*
mail reminders automatically sent every day via cronjob:
usually you must use the appropriate button in the admin section to send reminder messages.
if you want to automatically send reminder messages you can add a line like this in the crontab of the server where opencart is running:

#burro cron
30 01 *   *   *   root   cd /path_to_your_opencart_installation/admin/ && php index_cron.php >/dev/null

*/



?>