<?php
//
// CMS/LMS Web Interface for Moodle
//
//		れぞれのインターフェイスのライブラリに必要な定義を記述する
//		
//											 by Fumi.Iseki
//

if (!defined('ENV_READ_CONFIG')) require_once(realpath(dirname(__FILE__).'/config.php'));
if ( defined('ENV_READ_DEFINE')) return;

require_once(realpath(ENV_HELPER_PATH.'/../include/profile_config.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/search_config.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/xmlgroups_config.php'));



//////////////////////////////////////////////////////////////////////////////////
// Avatar State for CMS/LMS
//
define('AVATAR_STATE_NOSTATE',		'0');	// 0x00
define('AVATAR_STATE_SYNCDB',		'1');	// 0x01
define('AVATAR_STATE_SLOODLE',		'2');	// 0x02
define('AVATAR_STATE_INACTIVE',		'4');	// 0x04

define('AVATAR_STATE_NOSYNCDB',		'254');	// 0xfe
define('AVATAR_STATE_NOSLOODLE',	'253');	// 0xfd
define('AVATAR_STATE_ACTIVE',		'251');	// 0xfb

// Editable
define('AVATAR_NOT_EDITABLE',		'0');
define('AVATAR_EDITABLE',			'1');
define('AVATAR_OWNER_EDITABLE',		'2');

// Lastname
define('AVATAR_LASTN_INACTIVE',		'0');
define('AVATAR_LASTN_ACTIVE',		'1');

// Password
define('AVATAR_PASSWD_MINLEN',		'8');



//////////////////////////////////////////////////////////////////////////////////
// Offline Message
$OFFLINE_DB_HOST   = HELPER_DB_HOST;
$OFFLINE_DB_NAME   = HELPER_DB_NAME;
$OFFLINE_DB_USER   = HELPER_DB_USER;
$OFFLINE_DB_PASS   = HELPER_DB_PASS;
$OFFLINE_DB_MYSQLI = HELPER_DB_MYSQLI;

// MuteList
$MUTE_DB_HOST      = HELPER_DB_HOST;
$MUTE_DB_NAME      = HELPER_DB_NAME;
$MUTE_DB_USER      = HELPER_DB_USER;
$MUTE_DB_PASS      = HELPER_DB_PASS;
$MUTE_DB_MYSQLI    = HELPER_DB_MYSQLI;



//////////////////////////////////////////////////////////////////////////////////
// DB Table Name

// prefix
define('MDL_DB_PREFIX',             	$CFG->prefix);
define('MODLOS_DB_PREFIX',          	'modlos_');
define('FULL_DB_PREFIX',          		MDL_DB_PREFIX.MODLOS_DB_PREFIX);

// for Sloodle
define('SLOODLE_USERS_TBL',				MDL_DB_PREFIX.'sloodle_users');

// Offline Message and MuteList
define('OFFLINE_MESSAGE_TBL',       	FULL_DB_PREFIX.'offline_message');
define('MUTE_LIST_TBL',             	FULL_DB_PREFIX.'mute_list');

// XML Group.  see also xmlgroups_config.php 
define('XMLGROUP_ACTIVE_TBL',			FULL_DB_PREFIX.XMLGROUP_ACTIVE_TBL_BASE);
define('XMLGROUP_LIST_TBL',		 		FULL_DB_PREFIX.XMLGROUP_LIST_TBL_BASE);
define('XMLGROUP_INVITE_TBL',			FULL_DB_PREFIX.XMLGROUP_INVITE_TBL_BASE);
define('XMLGROUP_MEMBERSHIP_TBL',  		FULL_DB_PREFIX.XMLGROUP_MEMBERSHIP_TBL_BASE);
define('XMLGROUP_NOTICE_TBL',			FULL_DB_PREFIX.XMLGROUP_NOTICE_TBL_BASE);
define('XMLGROUP_ROLE_MEMBER_TBL', 		FULL_DB_PREFIX.XMLGROUP_ROLE_MEMBER_TBL_BASE);
define('XMLGROUP_ROLE_TBL',				FULL_DB_PREFIX.XMLGROUP_ROLE_TBL_BASE);

// Avatar Profile. see also profile_config.php 
define('PROFILE_CLASSIFIEDS_TBL',  		FULL_DB_PREFIX.PROFILE_CLASSIFIEDS_TBL_BASE);
define('PROFILE_USERNOTES_TBL',  		FULL_DB_PREFIX.PROFILE_USERNOTES_TBL_BASE);
define('PROFILE_USERPICKS_TBL',  		FULL_DB_PREFIX.PROFILE_USERPICKS_TBL_BASE);
define('PROFILE_USERPROFILE_TBL',  		FULL_DB_PREFIX.PROFILE_USERPROFILE_TBL_BASE);
define('PROFILE_USERSETTINGS_TBL',		FULL_DB_PREFIX.PROFILE_USERSETTINGS_TBL_BASE);

// Search the In World. see also search_config.php 
define('SEARCH_ALLPARCELS_TBL',			FULL_DB_PREFIX.SEARCH_ALLPARCELS_TBL_BASE);
define('SEARCH_EVENTS_TBL',				FULL_DB_PREFIX.SEARCH_EVENTS_TBL_BASE);
define('SEARCH_HOSTSREGISTER_TBL', 		FULL_DB_PREFIX.SEARCH_HOSTSREGISTER_TBL_BASE);
define('SEARCH_OBJECTS_TBL',			FULL_DB_PREFIX.SEARCH_OBJECTS_TBL_BASE);
define('SEARCH_PARCELS_TBL',			FULL_DB_PREFIX.SEARCH_PARCELS_TBL_BASE);
define('SEARCH_PARCELSALES_TBL',		FULL_DB_PREFIX.SEARCH_PARCELSALES_TBL_BASE);
define('SEARCH_POPULARPLACES_TBL', 		FULL_DB_PREFIX.SEARCH_POPULARPLACES_TBL_BASE);
define('SEARCH_REGIONS_TBL',			FULL_DB_PREFIX.SEARCH_REGIONS_TBL_BASE);
define('SEARCH_CLASSIFIEDS_TBL',		PROFILE_CLASSIFIEDS_TBL);



//////////////////////////////////////////////////////////////////////////////////
// Table Name without Moodle DB Prefix   Modlos ライブラリで使用する変数名

// for Sloodle
define('MDL_SLOODLE_USERS_TBL',		 	'sloodle_users');

// Offline Message and MuteList
define('MODLOS_OFFLINE_MESSAGE_TBL', 	MODLOS_DB_PREFIX.'offline_message');
define('MODLOS_MUTE_LIST_TBL', 			MODLOS_DB_PREFIX.'mute_list');

// XML Group.  see also xmlgroups_config.php 
define('MDL_XMLGROUP_ACTIVE_TBL',		MODLOS_DB_PREFIX.XMLGROUP_ACTIVE_TBL_BASE);
define('MDL_XMLGROUP_LIST_TBL',		 	MODLOS_DB_PREFIX.XMLGROUP_LIST_TBL_BASE);
define('MDL_XMLGROUP_INVITE_TBL',		MODLOS_DB_PREFIX.XMLGROUP_INVITE_TBL_BASE);
define('MDL_XMLGROUP_MEMBERSHIP_TBL',   MODLOS_DB_PREFIX.XMLGROUP_MEMBERSHIP_TBL_BASE);
define('MDL_XMLGROUP_NOTICE_TBL',		MODLOS_DB_PREFIX.XMLGROUP_NOTICE_TBL_BASE);
define('MDL_XMLGROUP_ROLE_MEMBER_TBL',  MODLOS_DB_PREFIX.XMLGROUP_ROLE_MEMBER_TBL_BASE);
define('MDL_XMLGROUP_ROLE_TBL',			MODLOS_DB_PREFIX.XMLGROUP_ROLE_TBL_BASE);

// Avatar Profile. see also profile_config.php 
define('MDL_PROFILE_CLASSIFIEDS_TBL',   MODLOS_DB_PREFIX.PROFILE_CLASSIFIEDS_TBL_BASE);
define('MDL_PROFILE_USERNOTES_TBL',  	MODLOS_DB_PREFIX.PROFILE_USERNOTES_TBL_BASE);
define('MDL_PROFILE_USERPICKS_TBL',  	MODLOS_DB_PREFIX.PROFILE_USERPICKS_TBL_BASE);
define('MDL_PROFILE_USERPROFILE_TBL',  	MODLOS_DB_PREFIX.PROFILE_USERPROFILE_TBL_BASE);
define('MDL_PROFILE_USERSETTINGS_TBL',	MODLOS_DB_PREFIX.PROFILE_USERSETTINGS_TBL_BASE);

// Search the In World. see also search_config.php 
define('MDL_SEARCH_ALLPARCELS_TBL',		MODLOS_DB_PREFIX.SEARCH_ALLPARCELS_TBL_BASE);
define('MDL_SEARCH_EVENTS_TBL',			MODLOS_DB_PREFIX.SEARCH_EVENTS_TBL_BASE);
define('MDL_SEARCH_HOSTSREGISTER_TBL', 	MODLOS_DB_PREFIX.SEARCH_HOSTSREGISTER_TBL_BASE);
define('MDL_SEARCH_OBJECTS_TBL',		MODLOS_DB_PREFIX.SEARCH_OBJECTS_TBL_BASE);
define('MDL_SEARCH_PARCELS_TBL',		MODLOS_DB_PREFIX.SEARCH_PARCELS_TBL_BASE);
define('MDL_SEARCH_PARCELSALES_TBL',	MODLOS_DB_PREFIX.SEARCH_PARCELSALES_TBL_BASE);
define('MDL_SEARCH_POPULARPLACES_TBL', 	MODLOS_DB_PREFIX.SEARCH_POPULARPLACES_TBL_BASE);
define('MDL_SEARCH_REGIONS_TBL',		MODLOS_DB_PREFIX.SEARCH_REGIONS_TBL_BASE);
define('MDL_SEARCH_CLASSIFIEDS_TBL',	MDL_PROFILE_CLASSIFIEDS_TBL);



//////////////////////////////////////////////////////////////////////////////////
// Event Categories

$Categories		= array();
$Categories[0]  = get_string('modlos_events_all_category', 'block_modlos');
$Categories[18] = get_string('modlos_events_discussion',   'block_modlos');
$Categories[19] = get_string('modlos_events_sports',       'block_modlos');
$Categories[20] = get_string('modlos_events_music',        'block_modlos');
$Categories[22] = get_string('modlos_events_commercial',   'block_modlos');
$Categories[23] = get_string('modlos_events_enteme',       'block_modlos');
$Categories[24] = get_string('modlos_events_games',        'block_modlos');
$Categories[25] = get_string('modlos_events_pageants',     'block_modlos');
$Categories[26] = get_string('modlos_events_edu',          'block_modlos');
$Categories[27] = get_string('modlos_events_arts',         'block_modlos');
$Categories[28] = get_string('modlos_events_charity',      'block_modlos');
$Categories[29] = get_string('modlos_events_misc',         'block_modlos');
if (!OPENSIM_PG_ONLY) $Categories[23] = get_string('modlos_events_nightlife', 'block_modlos').$Categories[23];



//////////////////////////////////////////////////////////////////////////////////
if (!defined('ENV_READ_DEFINE')) define('ENV_READ_DEFINE', 'YES');


