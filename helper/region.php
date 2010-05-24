<?php
/////////////////////////////////////////////////////////////////////////////
// Region の個別情報を表示する．
//
// usage... http://xxx/yyy/zzz/region.php?region=3a9379b7-1821-4b04-ab97-e38df166bac1
//

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");


$isGuest = isguest();
if ($isGuest) {
	exit('<h4>guest user is not allowed!!</h4>');
}


$courseid = optional_param('course', '0', PARAM_INT);
$region   = required_param('region', PARAM_TEXT);
if (!isGUID($region)) exit("<h4>bad region uuid!! ($region)</h4>");

require_login($courseid);
$hasPermit = hasPermit($courseid);

global $CFG;
$grid_name  = $CFG->mdlopnsm_grid_name;
$action_url = CMS_MODULE_URL."/helper/region.php";


//////////////
$col = 0;
$users = opensim_get_avatars_infos();
foreach($users as $user) {
	$avatars[$col]['name'] = $user['firstname']." ".$user['lastname'];
	$avatars[$col]['uuid'] = $user['UUID'];
	$col++;
}


// POST
if ($hasPermit and data_submitted() and confirm_sesskey()) {
	$rgnadmin = optional_param('rgnadmin', '', PARAM_TEXT);
	if ($rgnadmin!="") {
		opensim_set_region_owner($region, $rgnadmin);
	}
	$voice_mode = optional_param('voice_mode', '', PARAM_TEXT);
	if ($voice_mode!="") {
		opensim_set_voice_mode($region, $voice_mode);
	}
}


//////////////
$voice_modes[0]['id']	 = '0';
$voice_modes[1]['id']	 = '1';
$voice_modes[2]['id']	 = '2';
$voice_modes[0]['title'] = get_string("mdlos_voice_inactive_chnl","block_mdlopensim");
$voice_modes[1]['title'] = get_string("mdlos_voice_private_chnl", "block_mdlopensim");
$voice_modes[2]['title'] = get_string("mdlos_voice_percel_chnl",  "block_mdlopensim");

$vcmode = opensim_get_voice_mode($region);
$vcmode_title = $voice_modes[$vcmode]['title'];


//////////////
$owner_name = $owner_uuid = "";
$rginfo = opensim_get_region_info($region);
if ($rginfo!=null) {
	$regionName	 	= $rginfo['regionName'];
	$serverIP		= $rginfo['serverIP'];
	$serverHttpPort = $rginfo['serverHttpPort'];
	$serverURI	  	= $rginfo['serverURI'];
	$locX		   	= $rginfo['locX'];
	$locY		   	= $rginfo['locY'];
	$owner_name	 	= $rginfo['fullname'];
	$owner_uuid	 	= $rginfo['owner_uuid'];
}
else {
	exit("<h4>cannot get region information!! ($region)</h4>");
}

$server = "";
if ($serverURI!="") {
	$dec = explode(":", $serverURI);
	if (!strncasecmp($dec[0], "http", 4)) $server = "$dec[0]:$dec[1]";
}   
if ($server=="") {
	$server = "http://$serverIP";
}
$server = "$server:$serverHttpPort";
$guid = str_replace("-", "", $region);

$locX = $locX/256;
$locY = $locY/256;


//////////////
$course 	  	= "&amp;course=".$courseid;

$region_info_ttl= get_string("mdlos_region_info",	 "block_mdlopensim");
$region_ttl   	= get_string("mdlos_region",   		 "block_mdlopensim");
$uuid_ttl     	= get_string("mdlos_uuid",    		 "block_mdlopensim");
$change_ttl   	= get_string("mdlos_change",		 "block_mdlopensim");

$coordinates  	= get_string("mdlos_coordinates", 	 "block_mdlopensim");
$admin_user   	= get_string("mdlos_admin_user",  	 "block_mdlopensim");
$region_owner 	= get_string("mdlos_region_owner",	 "block_mdlopensim");
$voice_mode	  	= get_string("mdlos_voice_chat_mode","block_mdlopensim");

include(CMS_MODULE_PATH."/html/sim.html");

?>
