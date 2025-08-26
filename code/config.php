<?php
require_once 'environment.php';


define("APP_NAME", "C.G.F.");

define("PORTAL_NAME", "Portal ". APP_NAME);

define("INFO_TITULO", "INFORMATIVO DIÁRIO ". APP_NAME);

/**
 * KEY DAS APIs DO GOOGLE
 */

/**
 * cgfportal - usada no C.G.F. Portal FRONT
 */
define("FRONTKEYGOOGLE", ""); // TODO: POPULATE WITH KEY

/**
 * cgfportal - usada no C.G.F. Portal BACK
 */
define("BACKKEYGOOGLE", ""); // TODO: POPULATE WITH KEY

/**
 * cgfapp - usada no C.G.F. PASS
 */
define("APPKEYGOOGLE", ""); // TODO: POPULATE WITH KEY


/**
 * cgfportal searchEngineId - usada no CGF Portal BACK
 */
define("SEARCHENGINEID", "");

$config = array();

if(ENVIRONMENT == 'development') {
	define("BASE_URL", "/");
	define("BASE_URLB", ""); // TODO: POPULATE WITH DEVELOPMENT URL
	$config['dbname'] 	= ''; // TODO: POPULATE WITH DEVELOPMENT DATABASE NAME
	$config['host'] 	= ''; // TODO: POPULATE WITH DEVELOPMENT DATABASE ADDRESS
	$config['dbuser'] 	= ''; // TODO: POPULATE WITH DEVELOPMENT DATABASE USER
	$config['dbpass'] 	= ''; // TODO: POPULATE WITH DEVELOPMENT DATABASE PASSWORD

} else {
	define("BASE_URL", "/");
	define("BASE_URLB", ""); // TODO: POPULATE WITH PRODUCTION URL
	$config['dbname'] 	= ''; // TODO: POPULATE WITH PRODUCTION DATABASE NAME
	$config['host'] 	= ''; // TODO: POPULATE WITH PRODUCTION DATABASE ADDRESS
	$config['dbuser'] 	= ''; // TODO: POPULATE WITH PRODUCTION DATABASE USER
	$config['dbpass'] 	= ''; // TODO: POPULATE WITH PRODUCTION DATABASE PASSWORD

}

define("PICSPATH", BASE_URL."assets/images/users/");
define("PICSDEVICES", BASE_URL."assets/images/devices/");
define("TEMP_DIR", BASE_URL."assets/images/temp/");//fica dentro da pasta cron

global $db;
try {
	$db = new PDO("mysql:dbname=".$config['dbname'].";charset=utf8;host=".$config['host'], $config['dbuser'], $config['dbpass']);
} catch(PDOException $e) {
	echo "ERRO: ".$e->getMessage();
	exit;
}
