<?php
//
// CMS/LMS Web Interface for Moodle
//
//        れぞれのインターフェイスのライブラリに必要な定義を記述する
//
//                                             by Fumi.Iseki
//

require_once(realpath(dirname(__FILE__).'/config.php'));

require_once(ENV_HELPER_PATH.'/../include/tools.func.php');
require_once(ENV_HELPER_PATH.'/../include/mysql.func.php');
require_once(ENV_HELPER_PATH.'/../include/opensim.mysql.php');
require_once(ENV_HELPER_PATH.'/../include/modlos.func.php');


//////////////////////////////////////////////////////////////////////////////////
// date format
define('DATE_FORMAT',           $CFG->modlos_date_format);

// for Sloodle
define('SLOODLE_USERS_TBL',     $CFG->prefix.'sloodle_users');



//////////////////////////////////////////////////////////////////////////////////
//
// Avatar State for CMS/LMS
//
define('AVATAR_STATE_NOSTATE',      '0');       // 0x00
define('AVATAR_STATE_SYNCDB',       '1');       // 0x01
define('AVATAR_STATE_SLOODLE',      '2');       // 0x02
define('AVATAR_STATE_INACTIVE',     '4');       // 0x04

define('AVATAR_STATE_NOSYNCDB',     '254');     // 0xfe
define('AVATAR_STATE_NOSLOODLE',    '253');     // 0xfd
define('AVATAR_STATE_ACTIVE',       '251');     // 0xfb

// Editable
define('AVATAR_NOT_EDITABLE',       '0');
define('AVATAR_EDITABLE',           '1');
define('AVATAR_OWNER_EDITABLE',     '2');

// Lastname
define('AVATAR_LASTN_INACTIVE',     '0');
define('AVATAR_LASTN_ACTIVE',       '1');

// Password
define('AVATAR_PASSWD_MINLEN',      '8');



//////////////////////////////////////////////////////////////////////////////////
//
// Tables Name without DB prefix
//
//    Modlos ライブラリで使用する変数名
//

// Offline Message and MuteList
define('MDL_OFFLINE_MESSAGE_TBL', 			'modlos_offline_message');
define('MDL_MUTE_LIST_TBL', 				'modlos_mute_list');


//////////////////////////////////////////////////////////////////////////////////
//
// External other Modules
//
//    Modlos ライブラリで使用する変数名
//

// for Sloodle
define('MDL_SLOODLE_USERS_TBL',             'sloodle_users');

// XML Group.  see also xmlgroups_config.php 
define('MDL_XMLGROUP_ACTIVE_TBL',			'modlos_group_active');
define('MDL_XMLGROUP_LIST_TBL',		 		'modlos_group_list');
define('MDL_XMLGROUP_INVITE_TBL',			'modlos_group_invite');
define('MDL_XMLGROUP_MEMBERSHIP_TBL',   	'modlos_group_membership');
define('MDL_XMLGROUP_NOTICE_TBL',			'modlos_group_notice');
define('MDL_XMLGROUP_ROLE_MEMBER_TBL',  	'modlos_group_rolemembership');
define('MDL_XMLGROUP_ROLE_TBL',				'modlos_group_role');

// Avatar Profile. see also profile_config.php 
define('MDL_PROFILE_CLASSIFIEDS_TBL',   	'modlos_profile_classifieds');
define('MDL_PROFILE_USERNOTES_TBL',  		'modlos_profile_usernotes');
define('MDL_PROFILE_USERPICKS_TBL',  		'modlos_profile_userpicks');
define('MDL_PROFILE_USERPROFILE_TBL',  		'modlos_profile_userprofile');
define('MDL_PROFILE_USERSETTINGS_TBL',		'modlos_profile_usersettings');

// Search the In World. see also search_config.php 
define('MDL_SEARCH_ALLPARCELS_TBL',			'modlos_search_allparcels');
define('MDL_SEARCH_EVENTS_TBL',				'modlos_search_events');
define('MDL_SEARCH_HOSTSREGISTER_TBL', 		'modlos_search_hostsregister');
define('MDL_SEARCH_OBJECTS_TBL',			'modlos_search_objects');
define('MDL_SEARCH_PARCELS_TBL',			'modlos_search_parcels');
define('MDL_SEARCH_PARCELSALES_TBL',		'modlos_search_parcelsales');
define('MDL_SEARCH_POPULARPLACES_TBL', 		'modlos_search_popularplaces');
define('MDL_SEARCH_REGIONS_TBL',			'modlos_search_regions');
define('MDL_SEARCH_CLASSIFIEDS_TBL',		MDL_PROFILE_CLASSIFIEDS_TBL);



//////////////////////////////////////////////////////////////////////////////////
if (!defined('ENV_READED_INTERFACE')) define('ENV_READED_INTERFACE', 'YES');

