<?php

require_once (realpath(dirname(__FILE__)."/../../../config.php"));

define('MDLOPNSM_DIR_NAME', basename(dirname(dirname(__FILE__))));
define('MDLOPNSM_BLK_URL',  $CFG->wwwroot.'/blocks/'.MDLOPNSM_DIR_NAME);
define('MDLOPNSM_BLK_PATH', $CFG->dirroot.'/blocks/'.MDLOPNSM_DIR_NAME);

// top directory of this block
$module_path = MDLOPNSM_BLK_PATH.'/';


// for DB
define("OPENSIM_DB_HOST",   $CFG->mdlopnsm_sql_server_name);
define("OPENSIM_DB_NAME",   $CFG->mdlopnsm_sql_db_name);
define("OPENSIM_DB_USER",   $CFG->mdlopnsm_sql_db_user);
define("OPENSIM_DB_PASS",   $CFG->mdlopnsm_sql_db_pass);

define("MDLOPNSM_GRP_RKEY", $CFG->mdlopnsm_groupdb_read_key);
define("MDLOPNSM_GRP_WKEY", $CFG->mdlopnsm_groupdb_write_key);
//define("MDLOPNSM_BANKER",   $CFG->mdlopnsm_banker_avatar);

// OpenSim Default Tables
//define("OPENSIM_USERS_TBL",	  "users");
//define("OPENSIM_AGENTS_TBL",  "agents");
//define("OPENSIM_REGIONS_TBL", "regions");
//define("OPENSIM_ESTMAP_TBL",  "estate_map");
//define("OPENSIM_ESTSET_TBL",  "estate_settings");
// 0.6.9
//define("OPENSIM_AUTH_TBL",    "auth");
//define("OPENSIM_PRESENCE_TBL","Presence");



// OpenSim Default Tables
define("MDLOPNSM_CURRENCY_TBL",	   "block_opsm_ecnmy_money");
define("MDLOPNSM_TRANSACTION_TBL", "block_opsm_ecnmy_transaction");

// for Avatar State
define("MDLOPNSM_STATE_NOTSYNC", "0");
define("MDLOPNSM_STATE_ACTIVE",  "1");
define("MDLOPNSM_STATE_INACTIVE","5");		// Max Number of state

// editable
define("MDLOPNSM_NOT_EDITABLE",	 "0");
define("MDLOPNSM_EDITABLE",	 	 "1");
define("MDLOPNSM_OWNER_EDITABLE","2");


// for Currency
// Key of the account that all fees go to:
//$economy_sink_account   = MDLOPNSM_BANKER;

// Key of the account that all purchased currency is debited from:
//$economy_source_account = MDLOPNSM_BANKER;

// Minimum amount of real currency (in CENTS!) to allow purchasing:
$minimum_real = 1;

// Error message if the amount is not reached:
$low_amount_error = "You tried to buy less than the minimum amount of currency. You cannot buy currency for less than US$ %.2f.";

?>
