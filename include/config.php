<?php
//
// Configuration file for Moodle
//										by Fumi.Iseki
//
//

require_once (realpath(dirname(__FILE__).'/../../../config.php'));

global $CFG;
define('CMS_DIR_NAME',    basename(dirname(dirname(__FILE__))));
define('CMS_MODULE_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME);
define('CMS_MODULE_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME);


$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';



// for OpenSim DB
define('OPENSIM_DB_HOST', $CFG->modlos_sql_server_name);
define('OPENSIM_DB_NAME', $CFG->modlos_sql_db_name);
define('OPENSIM_DB_USER', $CFG->modlos_sql_db_user);
define('OPENSIM_DB_PASS', $CFG->modlos_sql_db_pass);
define('OPENSIM_HMREGION',$CFG->modlos_home_region);


// for CMS/LMS DB
define('CMS_DB_HOST',     $CFG->dbhost);
define('CMS_DB_NAME',     $CFG->dbname);
define('CMS_DB_USER',     $CFG->dbuser);
define('CMS_DB_PASS',     $CFG->dbpass);

define('MDL_DB_PREFIX',   'modlos_');


// SQL DB Table definition
define('MDL_XMLGROUP_ACTIVE_TBL',       MDL_DB_PREFIX.'group_active');
define('MDL_XMLGROUP_LIST_TBL',         MDL_DB_PREFIX.'group_list');
define('MDL_XMLGROUP_INVITE_TBL',       MDL_DB_PREFIX.'group_invite');
define('MDL_XMLGROUP_MEMBERSHIP_TBL',   MDL_DB_PREFIX.'group_membership');
define('MDL_XMLGROUP_NOTICE_TBL',       MDL_DB_PREFIX.'group_notice');
define('MDL_XMLGROUP_ROLE_MEMBER_TBL',  MDL_DB_PREFIX.'group_rolemembership');
define('MDL_XMLGROUP_ROLE_TBL',         MDL_DB_PREFIX.'group_role');

define('MDL_CURRENCY_MONEY_TBL',		MDL_DB_PREFIX.'economy_money');
define('MDL_CURRENCY_TRANSACTION_TBL',	MDL_DB_PREFIX.'economy_transactions');

define('MDL_OFFLINE_MESSAGE_TBL', 		MDL_DB_PREFIX.'offline_message');

define('MDL_PROFILE_CLASSIFIEDS_TBL',   MDL_DB_PREFIX.'profile_classifieds');
define('MDL_PROFILE_USERNOTES_TBL',  	MDL_DB_PREFIX.'profile_usernotes');
define('MDL_PROFILE_USERPICKS_TBL',  	MDL_DB_PREFIX.'profile_userpicks');
define('MDL_PROFILE_USERPROFILE_TBL',  	MDL_DB_PREFIX.'profile_userprofile');
define('MDL_PROFILE_USERSETTINGS_TBL',	MDL_DB_PREFIX.'profile_usersettings');

define('MDL_SLOODLE_USERS_TBL',			'sloodle_users');




// XML Group.  see also xmlgroups_config.php 
define('XMLGROUP_ACTIVE_TBL',       $CFG->prefix.MDL_XMLGROUP_ACTIVE_TBL);
define('XMLGROUP_LIST_TBL',         $CFG->prefix.MDL_XMLGROUP_LIST_TBL);
define('XMLGROUP_INVITE_TBL',       $CFG->prefix.MDL_XMLGROUP_INVITE_TBL);
define('XMLGROUP_MEMBERSHIP_TBL',   $CFG->prefix.MDL_XMLGROUP_MEMBERSHIP_TBL);
define('XMLGROUP_NOTICE_TBL',       $CFG->prefix.MDL_XMLGROUP_NOTICE_TBL);
define('XMLGROUP_ROLE_MEMBER_TBL',  $CFG->prefix.MDL_XMLGROUP_ROLE_MEMBER_TBL);
define('XMLGROUP_ROLE_TBL',         $CFG->prefix.MDL_XMLGROUP_ROLE_TBL);

define('XMLGROUP_RKEY',    			$CFG->modlos_groupdb_read_key);
define('XMLGROUP_WKEY',	   			$CFG->modlos_groupdb_write_key);


// Currency DB for helpers.php
define('CURRENCY_DB_HOST', 			CMS_DB_HOST);
define('CURRENCY_DB_NAME', 			CMS_DB_NAME);
define('CURRENCY_DB_USER', 			CMS_DB_USER);
define('CURRENCY_DB_PASS', 			CMS_DB_PASS);
define('CURRENCY_BANKER',  			$CFG->modlos_banker_avatar);

define('CURRENCY_MONEY_TBL',		$CFG->prefix.MDL_CURRENCY_MONEY_TBL);
define('CURRENCY_TRANSACTION_TBL',	$CFG->prefix.MDL_CURRENCY_TRANSACTION_TBL);


// Offline Message
define('OFFLINE_DB_HOST',  			CMS_DB_HOST);
define('OFFLINE_DB_NAME',  			CMS_DB_NAME);
define('OFFLINE_DB_USER',  			CMS_DB_USER);
define('OFFLINE_DB_PASS',  			CMS_DB_PASS);

define('OFFLINE_MESSAGE_TBL', 		$CFG->prefix.MDL_OFFLINE_MESSAGE_TBL);


// Avatar Profile. see also profile_config.php 
define('PROFILE_CLASSIFIEDS_TBL',   $CFG->prefix.MDL_PROFILE_CLASSIFIEDS_TBL);
define('PROFILE_USERNOTES_TBL',  	$CFG->prefix.MDL_PROFILE_USERNOTES_TBL);
define('PROFILE_USERPICKS_TBL',  	$CFG->prefix.MDL_PROFILE_USERPICKS_TBL);
define('PROFILE_USERPROFILE_TBL',  	$CFG->prefix.MDL_PROFILE_USERPROFILE_TBL);
define('PROFILE_USERSETTINGS_TBL',	$CFG->prefix.MDL_PROFILE_USERSETTINGS_TBL);


// for Avatar State
define('AVATAR_STATE_NOSTATE', 	'0');		// 0x00
define('AVATAR_STATE_SYNCDB',  	'1');		// 0x01
define('AVATAR_STATE_SLOODLE', 	'2');		// 0x02
define('AVATAR_STATE_INACTIVE',	'4');		// 0x04

define('AVATAR_STATE_NOSYNCDB',	'254');		// 0xfe
define('AVATAR_STATE_NOSLOODLE','253');		// 0xfd
define('AVATAR_STATE_ACTIVE',	'251');		// 0xfb

// Editable
define('AVATAR_NOT_EDITABLE',	'0');
define('AVATAR_EDITABLE',	 	'1');
define('AVATAR_OWNER_EDITABLE',	'2');

// Lastname
define('AVATAR_LASTN_INACTIVE',	'0');
define('AVATAR_LASTN_ACTIVE',  	'1');

// Password
define('AVATAR_PASSWD_MINLEN',	'8');


// for Currency
// Key of the account that all fees go to:
$economy_sink_account   = CURRENCY_BANKER;

// Key of the account that all purchased currency is debited from:
$economy_source_account = CURRENCY_BANKER;

// Minimum amount of real currency (in CENTS!) to allow purchasing:
$minimum_real = 1;

// Error message if the amount is not reached:
$low_amount_error = 'You tried to buy less than the minimum amount of currency. You cannot buy currency for less than US$ %.2f.';

?>
