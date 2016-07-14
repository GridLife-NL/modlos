<?php
//
// Configuration file 
//
//                        for Moodle by Fumi.Iseki
//

require_once(realpath(dirname(__FILE__).'/../../../config.php'));

if (!defined('CMS_DIR_NAME'))	 define('CMS_DIR_NAME',	   basename(dirname(dirname(__FILE__))));
if (!defined('CMS_MODULE_URL'))	 define('CMS_MODULE_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME);
if (!defined('CMS_MODULE_PATH')) define('CMS_MODULE_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME);

if (!defined('ENV_HELPER_URL'))	 define('ENV_HELPER_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME.'/helper');
if (!defined('ENV_HELPER_PATH')) define('ENV_HELPER_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME.'/helper');



//////////////////////////////////////////////////////////////////////////////////
// for Moodle DB

define('CMS_DB_HOST',			$CFG->dbhost);
define('CMS_DB_NAME', 			$CFG->dbname);
define('CMS_DB_USER',			$CFG->dbuser);
define('CMS_DB_PASS',			$CFG->dbpass);
define('CMS_DB_MYSQLI',			$CFG->modlos_use_mysqli);



//////////////////////////////////////////////////////////////////////////////////
// for OpenSim (Modlos)

define('OPENSIM_DB_HOST',		$CFG->modlos_sql_server_name);
define('OPENSIM_DB_NAME',		$CFG->modlos_sql_db_name);
define('OPENSIM_DB_USER',		$CFG->modlos_sql_db_user);
define('OPENSIM_DB_PASS',		$CFG->modlos_sql_db_pass);

if (!property_exists($CFG, 'modlos_use_mysqli')) {
	if (function_exists('mysqli_connect')) $CFG->modlos_use_mysqli = true;
	else                                   $CFG->modlos_use_mysqli = false;
}
else {
	if (!$CFG->modlos_use_mysqli and !function_exists('mysql_connect')) $CFG->modlos_use_mysqli = true;
}
define('OPENSIM_DB_MYSQLI',		$CFG->modlos_use_mysqli);

define('USE_CURRENCY_SERVER',	$CFG->modlos_use_currency_server);
define('CURRENCY_SCRIPT_KEY',	$CFG->modlos_currency_script_key);

define('XMLGROUP_RKEY',			$CFG->modlos_groupdb_read_key);
define('XMLGROUP_WKEY',	   		$CFG->modlos_groupdb_write_key);

define('OPENSIM_PG_ONLY',		$CFG->modlos_pg_only);
define('USE_UTC_TIME',			$CFG->modlos_use_utc_time);

define('DATE_FORMAT', 			$CFG->modlos_date_format);



//////////////////////////////////////////////////////////////////////////////////
// System

define('SYSURL', $CFG->wwwroot);
$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';

if (USE_UTC_TIME) date_default_timezone_set('UTC');



//////////////////////////////////////////////////////////////////////
if (!defined('ENV_READ_CONFIG')) define('ENV_READ_CONFIG', 'YES');

