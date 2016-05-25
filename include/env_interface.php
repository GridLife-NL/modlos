<?php
//
// CMS/LMS Web Interface for Moodle
//												by Fumi.Iseki
//
//

require_once(realpath(dirname(__FILE__).'/config.php'));

require_once(ENV_HELPER_PATH.'/../include/tools.func.php');
require_once(ENV_HELPER_PATH.'/../include/mysql.func.php');
require_once(ENV_HELPER_PATH.'/../include/opensim.mysql.php');
require_once(ENV_HELPER_PATH.'/../include/modlos.func.php');


// for Login Page
if (isset($LOGINPAGE) and $LOGINPAGE) {
	$LOGIN_SCREEN_CONTENT = env_get_config('loginscreen_content');

	$alert = modlos_get_loginscreen_alert();
	$BOX_TITLE        = $alert['title'];
	$BOX_COLOR        = $alert['bordercolor'];
	$BOX_INFOTEXT     = $alert['information'];

	$GRID_NAME        = $CFG->modlos_grid_name;
	$REGION_TTL       = get_string('modlos_region','block_modlos');

	$DB_STATUS_TTL    = get_string('modlos_db_status','block_modlos');
	$ONLINE           = get_string('modlos_online_ttl','block_modlos');
	$OFFLINE          = get_string('modlos_offline_ttl','block_modlos');
	$TOTAL_USER_TTL   = get_string('modlos_total_users','block_modlos');
	$TOTAL_REGION_TTL = get_string('modlos_total_regions','block_modlos');
	$LAST_USERS_TTL   = get_string('modlos_visitors_last30days','block_modlos');
	$ONLINE_TTL       = get_string('modlos_online_now','block_modlos');
	$HG_ONLINE_TTL    = get_string('modlos_online_hg','block_modlos');
}

//
$Categories[0]  = get_string('modlos_events_all_category',	'block_modlos');
$Categories[18] = get_string('modlos_events_discussion',	'block_modlos');
$Categories[19] = get_string('modlos_events_sports',		'block_modlos');
$Categories[20] = get_string('modlos_events_music',			'block_modlos');
$Categories[22] = get_string('modlos_events_commercial',	'block_modlos');
$Categories[23] = get_string('modlos_events_enteme',		'block_modlos');
$Categories[24] = get_string('modlos_events_games',			'block_modlos');
$Categories[25] = get_string('modlos_events_pageants',	  	'block_modlos');
$Categories[26] = get_string('modlos_events_edu',		   	'block_modlos');
$Categories[27] = get_string('modlos_events_arts',			'block_modlos');
$Categories[28] = get_string('modlos_events_charity',		'block_modlos');
$Categories[29] = get_string('modlos_events_misc',			'block_modlos');
if (!OPENSIM_PG_ONLY) $Categories[23] = get_string('modlos_events_nightlife', 'block_modlos').$Categories[23];


//
function  env_get_user_email($uid)
{                  
    return modlos_get_user_email($uid);
}


function  env_get_config($name)
{                  
    global $CFG;
	
	$name = 'modlos_'.$name;
 
    return $CFG->$name;
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// Tables Name without DB prefix
//

define('MDL_CURRENCY_MONEY_TBL',			'modlos_economy_money');
define('MDL_CURRENCY_TRANSACTION_TBL',		'modlos_economy_transactions');


// Offline Message and MuteList
define('MDL_OFFLINE_MESSAGE_TBL', 			'modlos_offline_message');
define('MDL_MUTE_LIST_TBL', 				'modlos_mute_list');



//////////////////////////////////////////////////////////////////////////////////
//
// External other Modules
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

//
if (!defined('ENV_READED_INTERFACE')) define('ENV_READED_INTERFACE', 'YES');

