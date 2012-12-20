<?php
//
// Configuration file for Moodle
//											by Fumi.Iseki
//
//
require_once(realpath(dirname(__FILE__).'/../../../config.php'));

if (!defined('CMS_DIR_NAME'))	 define('CMS_DIR_NAME',	   basename(dirname(dirname(__FILE__))));
if (!defined('CMS_MODULE_URL'))	 define('CMS_MODULE_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME);
if (!defined('CMS_MODULE_PATH')) define('CMS_MODULE_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME);

if (!defined('ENV_HELPER_URL'))	 define('ENV_HELPER_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME.'/helper');
if (!defined('ENV_HELPER_PATH')) define('ENV_HELPER_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME.'/helper');

//
$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';



//////////////////////////////////////////////////////////////////////////////////i
//
// for OpenSim
//

// for OpenSim DB
define('OPENSIM_DB_HOST',		$CFG->modlos_sql_server_name);
define('OPENSIM_DB_NAME',		$CFG->modlos_sql_db_name);
define('OPENSIM_DB_USER',		$CFG->modlos_sql_db_user);
define('OPENSIM_DB_PASS',		$CFG->modlos_sql_db_pass);

define('USE_CURRENCY_SERVER',	$CFG->modlos_use_currency_server);
define('CURRENCY_SCRIPT_KEY',	$CFG->modlos_currency_script_key);
define('USER_SERVER_URI',		$CFG->modlos_user_server_uri);



//////////////////////////////////////////////////////////////////////////////////i
//
// for Moodle
//

// for CMS/LMS DB
define('CMS_DB_HOST',					$CFG->dbhost);
define('CMS_DB_NAME', 					$CFG->dbname);
define('CMS_DB_USER',					$CFG->dbuser);
define('CMS_DB_PASS',					$CFG->dbpass);

//
define('SYSURL',						$CFG->wwwroot);
define('OPENSIM_PG_ONLY',				$CFG->modlos_pg_only);
define('DATE_FORMAT',					$CFG->modlos_date_format);
define('USE_UTC_TIME',					$CFG->modlos_use_utc_time);

if (USE_UTC_TIME) date_default_timezone_set('UTC');


//
define('MDL_DB_PREFIX',					$CFG->prefix);
define('MODLOS_DB_PREFIX',     			$CFG->prefix.'modlos_');


//////////////////////////////////////////////////////////////////////////////////
//
// External NSL Modules
//

// for Sloodle
define('SLOODLE_USERS_TBL',				$CFG->prefix.'sloodle_users');


// Currency DB for helpers.php
if (USE_CURRENCY_SERVER) {
	define('CURRENCY_DB_HOST',			OPENSIM_DB_HOST);
	define('CURRENCY_DB_NAME',			OPENSIM_DB_NAME);
	define('CURRENCY_DB_USER',			OPENSIM_DB_USER);
	define('CURRENCY_DB_PASS',			OPENSIM_DB_PASS);
	define('CURRENCY_MONEY_TBL',	  	'balances');
	define('CURRENCY_TRANSACTION_TBL',	'transactions');
}
else {
	define('CURRENCY_DB_HOST',			CMS_DB_HOST);
	define('CURRENCY_DB_NAME',			CMS_DB_NAME);
	define('CURRENCY_DB_USER',			CMS_DB_USER);
	define('CURRENCY_DB_PASS',			CMS_DB_PASS);
	define('CURRENCY_MONEY_TBL',		MODLOS_DB_PREFIX.'economy_money');
	define('CURRENCY_TRANSACTION_TBL',	MODLOS_DB_PREFIX.'economy_transactions');
}


// Offline Message
define('OFFLINE_DB_HOST',  				CMS_DB_HOST);
define('OFFLINE_DB_NAME',  				CMS_DB_NAME);
define('OFFLINE_DB_USER',  				CMS_DB_USER);
define('OFFLINE_DB_PASS',  				CMS_DB_PASS);
define('OFFLINE_MESSAGE_TBL', 			MODLOS_DB_PREFIX.'offline_message');


// MuteList
define('MUTE_DB_HOST',  				CMS_DB_HOST);
define('MUTE_DB_NAME',  				CMS_DB_NAME);
define('MUTE_DB_USER',  				CMS_DB_USER);
define('MUTE_DB_PASS',  				CMS_DB_PASS);
define('MUTE_LIST_TBL', 				MODLOS_DB_PREFIX.'mute_list');




//////////////////////////////////////////////////////////////////////////////////
//
// External other Modules
//

// XML Group.  see also xmlgroups_config.php 
define('XMLGROUP_ACTIVE_TBL',			MODLOS_DB_PREFIX.'group_active');
define('XMLGROUP_LIST_TBL',		 		MODLOS_DB_PREFIX.'group_list');
define('XMLGROUP_INVITE_TBL',			MODLOS_DB_PREFIX.'group_invite');
define('XMLGROUP_MEMBERSHIP_TBL',   	MODLOS_DB_PREFIX.'group_membership');
define('XMLGROUP_NOTICE_TBL',			MODLOS_DB_PREFIX.'group_notice');
define('XMLGROUP_ROLE_MEMBER_TBL',  	MODLOS_DB_PREFIX.'group_rolemembership');
define('XMLGROUP_ROLE_TBL',				MODLOS_DB_PREFIX.'group_role');

define('XMLGROUP_RKEY',					$CFG->modlos_groupdb_read_key);
define('XMLGROUP_WKEY',	   				$CFG->modlos_groupdb_write_key);


// Avatar Profile. see also profile_config.php 
define('PROFILE_CLASSIFIEDS_TBL',   	MODLOS_DB_PREFIX.'profile_classifieds');
define('PROFILE_USERNOTES_TBL',  		MODLOS_DB_PREFIX.'profile_usernotes');
define('PROFILE_USERPICKS_TBL',  		MODLOS_DB_PREFIX.'profile_userpicks');
define('PROFILE_USERPROFILE_TBL',  		MODLOS_DB_PREFIX.'profile_userprofile');
define('PROFILE_USERSETTINGS_TBL',		MODLOS_DB_PREFIX.'profile_usersettings');


// Search the In World. see also search_config.php 
define('SEARCH_ALLPARCELS_TBL',			MODLOS_DB_PREFIX.'search_allparcels');
define('SEARCH_EVENTS_TBL',				MODLOS_DB_PREFIX.'search_events');
define('SEARCH_HOSTSREGISTER_TBL', 		MODLOS_DB_PREFIX.'search_hostsregister');
define('SEARCH_OBJECTS_TBL',			MODLOS_DB_PREFIX.'search_objects');
define('SEARCH_PARCELS_TBL',			MODLOS_DB_PREFIX.'search_parcels');
define('SEARCH_PARCELSALES_TBL',		MODLOS_DB_PREFIX.'search_parcelsales');
define('SEARCH_POPULARPLACES_TBL', 		MODLOS_DB_PREFIX.'search_popularplaces');
define('SEARCH_REGIONS_TBL',			MODLOS_DB_PREFIX.'search_regions');
define('SEARCH_CLASSIFIEDS_TBL',		PROFILE_CLASSIFIEDS_TBL);




//////////////////////////////////////////////////////////////////////////////////
//
// for Avatar State for CMS/LMS
//
define('AVATAR_STATE_NOSTATE', 		'0');		// 0x00
define('AVATAR_STATE_SYNCDB',  		'1');		// 0x01
define('AVATAR_STATE_SLOODLE', 		'2');		// 0x02
define('AVATAR_STATE_INACTIVE',		'4');		// 0x04

define('AVATAR_STATE_NOSYNCDB',		'254');		// 0xfe
define('AVATAR_STATE_NOSLOODLE',	'253');		// 0xfd
define('AVATAR_STATE_ACTIVE',		'251');		// 0xfb

// Editable
define('AVATAR_NOT_EDITABLE',		'0');
define('AVATAR_EDITABLE',	 		'1');
define('AVATAR_OWNER_EDITABLE',		'2');

// Lastname
define('AVATAR_LASTN_INACTIVE',		'0');
define('AVATAR_LASTN_ACTIVE',  		'1');

// Password
define('AVATAR_PASSWD_MINLEN',		'8');



//
if (!defined('ENV_READED_CONFIG')) define('ENV_READED_CONFIG', 'YES');
?>
