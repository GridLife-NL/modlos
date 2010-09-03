<?php
//
//
//
require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/config.php'));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');

global $CFG;

$LOGIN_SCREEN_CONTENT = $CFG->modlos_loginscreen_content;

$BOX_TITLE		  = get_string('modlos_lgscn_box_ttl', 'block_modlos');
$BOX_COLOR		  = "green";
//$BOX_INFOTEXT 	  = "BOX_INFOTEXT<br /> sssss<br /><br /><br /><br />SSSSSSSSSS";
$BOX_INFOTEXT     = "BOX_INFOTEXT<br /> sssss<br /><br /><br /><br />SSSSSSSSSS<br/>ssssssssss<br />ssssssssss";


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

include('./loginscreen.php');

?>
