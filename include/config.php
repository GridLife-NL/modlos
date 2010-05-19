<?php
//
// Configuration file for Moodle
//										by Fumi.Iseki
//
//

require_once (realpath(dirname(__FILE__)."/../../../config.php"));

global $CFG;
define('CMS_DIR_NAME',    basename(dirname(dirname(__FILE__))));
define('CMS_MODULE_URL',  $CFG->wwwroot.'/blocks/'.CMS_DIR_NAME);
define('CMS_MODULE_PATH', $CFG->dirroot.'/blocks/'.CMS_DIR_NAME);


$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';



// for OpenSim DB
define('OPENSIM_DB_HOST',  $CFG->mdlopnsm_sql_server_name);
define('OPENSIM_DB_NAME',  $CFG->mdlopnsm_sql_db_name);
define('OPENSIM_DB_USER',  $CFG->mdlopnsm_sql_db_user);
define('OPENSIM_DB_PASS',  $CFG->mdlopnsm_sql_db_pass);
define('OPENSIM_HMREGION', $CFG->mdlopnsm_home_region);


// for CMS/LMS DB
define('CMS_DB_HOST',      $CFG->dbhost);
define('CMS_DB_NAME',      $CFG->dbname);
define('CMS_DB_USER',      $CFG->dbuser);
define('CMS_DB_PASS',      $CFG->dbpass);
define('CMS_DB_PREFIX',    $CFG->prefix.'block_mdlos_');


// XML Group.  see also xmlgroups_config.php 
define('XMLGROUP_ACTIVE_TBL',       CMS_DB_PREFIX.'grp_active');
define('XMLGROUP_LIST_TBL',         CMS_DB_PREFIX.'grp_list');
define('XMLGROUP_INVITE_TBL',       CMS_DB_PREFIX.'grp_invite');
define('XMLGROUP_MEMBERSHIP_TBL',   CMS_DB_PREFIX.'grp_mbrship');
define('XMLGROUP_NOTICE_TBL',       CMS_DB_PREFIX.'grp_notice');
define('XMLGROUP_ROLE_MEMBER_TBL',  CMS_DB_PREFIX.'grp_rolembrship');
define('XMLGROUP_ROLE_TBL',         CMS_DB_PREFIX.'grp_role');

define('XMLGROUP_RKEY',    $CFG->mdlopnsm_groupdb_read_key);
define('XMLGROUP_WKEY',	   $CFG->mdlopnsm_groupdb_write_key);


// Currency DB for helpers.php
define('CURRENCY_DB_HOST', CMS_DB_HOST);
define('CURRENCY_DB_NAME', CMS_DB_NAME);
define('CURRENCY_DB_USER', CMS_DB_USER);
define('CURRENCY_DB_PASS', CMS_DB_PASS);
define('CURRENCY_BANKER',  $CFG->mdlopnsm_banker_avatar);

define('CURRENCY_MONEY_TBL',       CMS_DB_PREFIX.'ecnmy_money');
define('CURRENCY_TRANSACTION_TBL', CMS_DB_PREFIX.'ecnmy_trnsctn');


// Offline Message
define('OFFLINE_DB_HOST',  CMS_DB_HOST);
define('OFFLINE_DB_NAME',  CMS_DB_NAME);
define('OFFLINE_DB_USER',  CMS_DB_USER);
define('OFFLINE_DB_PASS',  CMS_DB_PASS);

define('OFFLINE_MESSAGE_TBL', CMS_DB_PREFIX.'offline_message');


// Avatar Profile. see also profile_config.php 
define('PROFILE_CLASSIFIEDS_TBL',   CMS_DB_PREFIX.'prof_classifieds');
define('PROFILE_USERNOTES_TBL',  	CMS_DB_PREFIX.'prof_usernotes');
define('PROFILE_USERPICKS_TBL',  	CMS_DB_PREFIX.'prof_userpicks');
define('PROFILE_USERPROFILE_TBL',  	CMS_DB_PREFIX.'prof_userprofile');
define('PROFILE_USERSETTINGS_TBL',	CMS_DB_PREFIX.'prof_usersetting');


// for Avatar State
define('AVATAR_STATE_NOTSYNC', 	'0');
define('AVATAR_STATE_ACTIVE',  	'1');
define('AVATAR_STATE_INACTIVE',	'5');		// Max Number of state

// Editable
define('AVATAR_NOT_EDITABLE',	'0');
define('AVATAR_EDITABLE',	 	'1');
define('AVATAR_OWNER_EDITABLE',	'2');


// for Currency
// Key of the account that all fees go to:
$economy_sink_account   = CURRENCY_BANKER;

// Key of the account that all purchased currency is debited from:
$economy_source_account = CURRENCY_BANKER;

// Minimum amount of real currency (in CENTS!) to allow purchasing:
$minimum_real = 1;

// Error message if the amount is not reached:
$low_amount_error = "You tried to buy less than the minimum amount of currency. You cannot buy currency for less than US$ %.2f.";

?>
