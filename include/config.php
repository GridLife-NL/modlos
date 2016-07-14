<?php
//
// Configuration file 
//
//                        for Moodle by Fumi.Iseki
//

require_once(realpath('../../../config.php'));


if (!defined('CMS_DIR_NAME'))	 define('CMS_DIR_NAME',	   basename(dirname(dirname(__FILE__))));
if (!defined('CMS_MODULE_URL'))	 define('CMS_MODULE_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME);
if (!defined('CMS_MODULE_PATH')) define('CMS_MODULE_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME);

if (!defined('ENV_HELPER_URL'))	 define('ENV_HELPER_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME.'/helper');
if (!defined('ENV_HELPER_PATH')) define('ENV_HELPER_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME.'/helper');



//////////////////////////////////////////////////////////////////////////////////
// for OpenSim

// for OpenSim DB
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


///////////////////////////////////////////////////////////
// for Moodle DB

define('CMS_DB_HOST',			$CFG->dbhost);
define('CMS_DB_NAME', 			$CFG->dbname);
define('CMS_DB_USER',			$CFG->dbuser);
define('CMS_DB_PASS',			$CFG->dbpass);
define('CMS_DB_MYSQLI',			$CFG->modlos_use_mysqli);



//////////////////////////////////////////////////////////////////////////////////
// // You need not change the below usually. 

define('SYSURL', $CFG->wwwroot);
$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';

if (USE_UTC_TIME) date_default_timezone_set('UTC');


////////////////////////////////////////////////////////////
// External NSL Modules

define('MDL_DB_PREFIX',				$CFG->prefix);
define('MODLOS_DB_PREFIX',   		$CFG->prefix.'modlos_');


// Offline Message
define('OFFLINE_DB_HOST',  			CMS_DB_HOST);
define('OFFLINE_DB_NAME',  			CMS_DB_NAME);
define('OFFLINE_DB_USER',  			CMS_DB_USER);
define('OFFLINE_DB_PASS',  			CMS_DB_PASS);
define('OFFLINE_DB_MYSQLI',  		CMS_DB_MYSQLI);
define('OFFLINE_MESSAGE_TBL', 		MODLOS_DB_PREFIX.'offline_message');

// MuteList
define('MUTE_DB_HOST',  			CMS_DB_HOST);
define('MUTE_DB_NAME',  			CMS_DB_NAME);
define('MUTE_DB_USER',  			CMS_DB_USER);
define('MUTE_DB_PASS',  			CMS_DB_PASS);
define('MUTE_DB_MYSQLI',  			CMS_DB_MYSQLI);
define('MUTE_LIST_TBL', 			MODLOS_DB_PREFIX.'mute_list');



////////////////////////////////////////////////////////////
// External other Modules
//
//		CMS/LMS の外側から使用する場合の変数
//

// XML Group.  see also xmlgroups_config.php 
define('XMLGROUP_ACTIVE_TBL',		MODLOS_DB_PREFIX.'group_active');
define('XMLGROUP_LIST_TBL',		 	MODLOS_DB_PREFIX.'group_list');
define('XMLGROUP_INVITE_TBL',		MODLOS_DB_PREFIX.'group_invite');
define('XMLGROUP_MEMBERSHIP_TBL',  	MODLOS_DB_PREFIX.'group_membership');
define('XMLGROUP_NOTICE_TBL',		MODLOS_DB_PREFIX.'group_notice');
define('XMLGROUP_ROLE_MEMBER_TBL', 	MODLOS_DB_PREFIX.'group_rolemembership');
define('XMLGROUP_ROLE_TBL',			MODLOS_DB_PREFIX.'group_role');

// Avatar Profile. see also profile_config.php 
define('PROFILE_CLASSIFIEDS_TBL',  	MODLOS_DB_PREFIX.'profile_classifieds');
define('PROFILE_USERNOTES_TBL',  	MODLOS_DB_PREFIX.'profile_usernotes');
define('PROFILE_USERPICKS_TBL',  	MODLOS_DB_PREFIX.'profile_userpicks');
define('PROFILE_USERPROFILE_TBL',  	MODLOS_DB_PREFIX.'profile_userprofile');
define('PROFILE_USERSETTINGS_TBL',	MODLOS_DB_PREFIX.'profile_usersettings');

// Search the In World. see also search_config.php 
define('SEARCH_ALLPARCELS_TBL',		MODLOS_DB_PREFIX.'search_allparcels');
define('SEARCH_EVENTS_TBL',			MODLOS_DB_PREFIX.'search_events');
define('SEARCH_HOSTSREGISTER_TBL', 	MODLOS_DB_PREFIX.'search_hostsregister');
define('SEARCH_OBJECTS_TBL',		MODLOS_DB_PREFIX.'search_objects');
define('SEARCH_PARCELS_TBL',		MODLOS_DB_PREFIX.'search_parcels');
define('SEARCH_PARCELSALES_TBL',	MODLOS_DB_PREFIX.'search_parcelsales');
define('SEARCH_POPULARPLACES_TBL', 	MODLOS_DB_PREFIX.'search_popularplaces');
define('SEARCH_REGIONS_TBL',		MODLOS_DB_PREFIX.'search_regions');
define('SEARCH_CLASSIFIEDS_TBL',	PROFILE_CLASSIFIEDS_TBL);



////////////////////////////////////////////////////////////
// Event Categories

$Categories[0]  = get_string('modlos_events_all_category',  'block_modlos');
$Categories[18] = get_string('modlos_events_discussion',    'block_modlos');
$Categories[19] = get_string('modlos_events_sports',        'block_modlos');
$Categories[20] = get_string('modlos_events_music',         'block_modlos');
$Categories[22] = get_string('modlos_events_commercial',    'block_modlos');
$Categories[23] = get_string('modlos_events_enteme',        'block_modlos');
$Categories[24] = get_string('modlos_events_games',         'block_modlos');
$Categories[25] = get_string('modlos_events_pageants',      'block_modlos');
$Categories[26] = get_string('modlos_events_edu',           'block_modlos');
$Categories[27] = get_string('modlos_events_arts',          'block_modlos');
$Categories[28] = get_string('modlos_events_charity',       'block_modlos');
$Categories[29] = get_string('modlos_events_misc',          'block_modlos');
if (!OPENSIM_PG_ONLY) $Categories[23] = get_string('modlos_events_nightlife', 'block_modlos').$Categories[23];



//////////////////////////////////////////////////////////////////////
if (!defined('ENV_READ_CONFIG')) define('ENV_READ_CONFIG', 'YES');
