<?php
/////////////////////////////////////////////////////////////////////////////
// Region の個別情報を表示する．
//
// usage... http://xxx/yyy/zzz/sim.php?region=3a9379b7-1821-4b04-ab97-e38df166bac1
//

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/config.php'));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


$isGuest = isguest();
if ($isGuest) {
	exit('<h4>guest user is not allowed!!</h4>');
}


$course_id = optional_param('course', '0', PARAM_INT);
$region    = required_param('region', PARAM_TEXT);
if (!isGUID($region)) exit("<h4>bad region uuid!! ($region)</h4>");

require_login($course_id);
$hasPermit = hasPermit($course_id);

global $CFG;
$grid_name  = $CFG->modlos_grid_name;
$action_url = CMS_MODULE_URL.'/helper/sim.php';


//////////////
$col = 0;
$users = opensim_get_avatars_infos();
foreach($users as $user) {
	$avatars[$col]['name'] = $user['firstname'].' '.$user['lastname'];
	$avatars[$col]['uuid'] = $user['UUID'];
	$col++;
}
$avatar_num = $col;


$vcmode = '';
$rginfo = '';

// POST
if ($hasPermit and data_submitted() and confirm_sesskey()) {
	//
	$rgnadmin = optional_param('rgnadmin', '', PARAM_TEXT);
	if (!isGUID($rgnadmin)) {   // owner name
		$rgnuuid = opensim_get_avatar_uuid($rgnadmin);
		if (!isGUID($rgnuuid)) {
			exit("<h4>unknown avatar name!! ($rgnadmin)</h4>");
		}
		$rgnadmin = $rgnuuid;
	}

	$rginfo = opensim_get_region_info($region);
	if ($rginfo!=null && $rginfo['owner_uuid']!=$rgnadmin) {
		$ret = opensim_set_region_owner($region, $rgnadmin);
		if (!$ret) exit("<h4>updating of region owner is fail!! ($region, $rgnadmin)</h4>");
		$rgninfo = null;
	}
		
	$voice_mode = optional_param('voice_mode', '', PARAM_TEXT);
	if (isNumeric($voice_mode)) {
		$vcmode = opensim_get_voice_mode($region);
		if ($vcmode!=$voice_mode) {
			$ret = opensim_set_voice_mode($region, $voice_mode);
			if (!$ret) exit("<h4>updating of voice mode is fail!! ($region, $voice_mode)</h4>");
			$vcmode = '';
		}
	}	
}


//////////////
$voice_modes[0]['id']	 = '0';
$voice_modes[1]['id']	 = '1';
$voice_modes[2]['id']	 = '2';
$voice_modes[0]['title'] = get_string('modlos_voice_inactive_chnl','block_modlos');
$voice_modes[1]['title'] = get_string('modlos_voice_private_chnl', 'block_modlos');
$voice_modes[2]['title'] = get_string('modlos_voice_percel_chnl',  'block_modlos');

if ($vcmode=='') $vcmode = opensim_get_voice_mode($region);
$vcmode_title = $voice_modes[$vcmode]['title'];


//////////////
$owner_name = $owner_uuid = '';
if ($rgnifo=='') $rginfo = opensim_get_region_info($region);
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

$server = '';
if ($serverURI!='') {
	$dec = explode(':', $serverURI);
	if (!strncasecmp($dec[0], 'http', 4)) $server = "$dec[0]:$dec[1]";
}   
if ($server=='') {
	$server = "http://$serverIP";
}
$server = $server.':'.$serverHttpPort;
$guid = str_replace('-', '', $region);

$locX = $locX/256;
$locY = $locY/256;


$avatar_select = true;
if ($avatar_num>100) $avatar_select = false;

//////////////
$course_amp = '';
if ($course_id>0) $course_amp = '&amp;course='.$course_id;

$region_info_ttl= get_string('modlos_region_info',	 'block_modlos');
$region_ttl   	= get_string('modlos_region',   		 'block_modlos');
$uuid_ttl     	= get_string('modlos_uuid',    		 'block_modlos');
$change_ttl   	= get_string('modlos_change',		 'block_modlos');

$coordinates  	= get_string('modlos_coordinates', 	 'block_modlos');
$admin_user   	= get_string('modlos_admin_user',  	 'block_modlos');
$region_owner 	= get_string('modlos_region_owner',	 'block_modlos');
$voice_mode	  	= get_string('modlos_voice_chat_mode','block_modlos');

include(CMS_MODULE_PATH.'/html/sim.html');

?>
