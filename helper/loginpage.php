<?php
//
//
//
require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));


$LOGIN_SCREEN_CONTENT = env_get_config('loginscreen_content');

$alert = modlos_get_loginscreen_alert();
$BOX_TITLE		  = $alert['title'];
$BOX_COLOR		  = $alert['bordercolor'];
$BOX_INFOTEXT     = $alert['information'];

$GRID_NAME		  = $CFG->modlos_grid_name;
$REGION_TTL		  = get_string('modlos_region','block_modlos');

$DB_STATUS_TTL	  = get_string('modlos_db_status','block_modlos');
$ONLINE			  = get_string('modlos_online_ttl','block_modlos');
$OFFLINE		  = get_string('modlos_offline_ttl','block_modlos');
$TOTAL_USER_TTL   = get_string('modlos_total_users','block_modlos');
$TOTAL_REGION_TTL = get_string('modlos_total_regions','block_modlos');
$LAST_USERS_TTL   = get_string('modlos_visitors_last30days','block_modlos');
$ONLINE_TTL		  = get_string('modlos_online_now','block_modlos');


$status = opensim_check_db();

$GRID_STATUS	  = $status['grid_status'];
$NOW_ONLINE		  = $status['now_online'];
$LASTMONTH_ONLINE = $status['lastmonth_online'];
$USER_COUNT		  = $status['user_count'];
$REGION_COUNT	  = $status['region_count'];

header('pragma: no-cache');
include('./loginscreen.php');

?>
