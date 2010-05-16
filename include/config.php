<?php

require_once (realpath(dirname(__FILE__)."/../../../config.php"));

define('MDLOPNSM_DIR_NAME', basename(dirname(dirname(__FILE__))));
define('MDLOPNSM_BLK_URL',  $CFG->wwwroot.'/blocks/'.MDLOPNSM_DIR_NAME);
define('MDLOPNSM_BLK_PATH', $CFG->dirroot.'/blocks/'.MDLOPNSM_DIR_NAME);


global $CFG;
$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';


// module_path is top directory of this block
global $module_path;
$module_path = MDLOPNSM_BLK_PATH.'/';


// for OpenSim DB
define("OPENSIM_DB_HOST",  $CFG->mdlopnsm_sql_server_name);
define("OPENSIM_DB_NAME",  $CFG->mdlopnsm_sql_db_name);
define("OPENSIM_DB_USER",  $CFG->mdlopnsm_sql_db_user);
define("OPENSIM_DB_PASS",  $CFG->mdlopnsm_sql_db_pass);
define("OPENSIM_HMREGION", $CFG->mdlopnsm_home_region);


// for WebIF DB
define("WEBIF_DB_HOST",    $CFG->dbhost);
define("WEBIF_DB_NAME",    $CFG->dbname);
define("WEBIF_DB_USER",    $CFG->dbuser);
define("WEBIF_DB_PASS",    $CFG->dbpass);
define("WEBIF_DB_PREFIX",  $CFG->prefix."block_mdlos_");


// XML Group
define("XMLGROUP_RKEY",    $CFG->mdlopnsm_groupdb_read_key);
define("XMLGROUP_WKEY",	   $CFG->mdlopnsm_groupdb_write_key);


// Currency DB for helpers.php
define("CURRENCY_DB_HOST", WEBIF_DB_HOST);
define("CURRENCY_DB_NAME", WEBIF_DB_NAME);
define("CURRENCY_DB_USER", WEBIF_DB_USER);
define("CURRENCY_DB_PASS", WEBIF_DB_PASS);
define("CURRENCY_BANKER",  $CFG->mdlopnsm_banker_avatar);

define("CURRENCY_MONEY_TBL",       WEBIF_DB_PREFIX."economy_money");
define("CURRENCY_TRANSACTION_TBL", WEBIF_DB_PREFIX."economy_transactions");


// Offline Message
define("OFFLINE_DB_HOST",  WEBIF_DB_HOST);
define("OFFLINE_DB_NAME",  WEBIF_DB_NAME);
define("OFFLINE_DB_USER",  WEBIF_DB_USER);
define("OFFLINE_DB_PASS",  WEBIF_DB_PASS);

define("OFFLINE_MESSAGE_TBL", WEBIF_DB_PREFIX."offline_message");


// Avatar Profile
define("PROFILE_CLASSIFIEDS_TBL",   WEBIF_DB_PREFIX."prof_classifieds");
define("PROFILE_USERNOTES_TBL",  	WEBIF_DB_PREFIX."prof_usernotes");
define("PROFILE_USERPICKS_TBL",  	WEBIF_DB_PREFIX."prof_userpicks");
define("PROFILE_USERPROFILE_TBL",  	WEBIF_DB_PREFIX."prof_userprofile");
define("PROFILE_USERSETTINGS_TBL",	WEBIF_DB_PREFIX."prof_usersettings");


// for Avatar State
define("AVATAR_STATE_NOTSYNC", 	"0");
define("AVATAR_STATE_ACTIVE",  	"1");
define("AVATAR_STATE_INACTIVE",	"5");		// Max Number of state

// editable
define("AVATAR_NOT_EDITABLE",	"0");
define("AVATAR_EDITABLE",	 	"1");
define("AVATAR_OWNER_EDITABLE",	"2");


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
