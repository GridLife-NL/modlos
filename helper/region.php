<?php
/////////////////////////////////////////////////////////////////////////////
// Region の個別情報を表示する．
//
// usage... http://xxx/yyy/zzz/region.php?region=3a9379b7-1821-4b04-ab97-e38df166bac1
//

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");

$region  = optional_param('region', '', PARAM_TEXT);
if (!isGUID($region)) exit('<h4>bad region uuid!!</h4>');


if ($isguest()) {
	exit('<h4>guest user is not allowed!!</h4>');
}

$courseid = optional_param('course', '0', PARAM_INT);
require_login($courseid);
$hasPermit = hasPermit($courseid);


global $CFG;
$grid_name = $CFG->mdlopnsm_grid_name;
//$action_url = MDLOPNSM_BLK_URL."/helper/region.php";



//////////////
$col = 0;
$users = opensim_get_avatar_infos();
foreach($users as $user) {
	$avatars[$col]['name'] = $user['firstname']." ".$user['lastname'];
	$avatars[$col]['uuid'] = $user['UUID'];
	$col++;
}

if ($hasPermit and !empty($_POST)) {
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
$voice_modes[0]['id']    = '0';
$voice_modes[1]['id']    = '1';
$voice_modes[2]['id']    = '2';
$voice_modes[0]['title'] = _MD_XPNSM_VOICE_INACTIVE_CHNL;
$voice_modes[1]['title'] = _MD_XPNSM_VOICE_PRIVATE_CHNL;
$voice_modes[2]['title'] = _MD_XPNSM_VOICE_PERCEL_CHNL;

$vcmode = opensim_get_voice_mode($region);
$vcmode_title = $voice_modes[$vcmode]['title'];


//////////////
$owner_name = $owner_uuid = "";
if ($region) {
	$DbLink = new DB;
	$DbLink->query("SELECT regionName,serverIP,serverHttpPort,serverURI,locX,locY FROM regions WHERE uuid='$region'");
	list($regionName, $serverIP, $serverHttpPort, $serverURI, $locX, $locY) = $DbLink->next_record();
	$DbLink->close();

	$name = opensim_get_region_owner($region);
	if ($name!=null) {
		$owner_name = $name['firstname']." ".$name['lastname'];
		$owner_uuid = $name['owner_uuid'];
	}
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
$xoopsTpl->assign('grid_name',   $grid_name);
$xoopsTpl->assign('region', 	 $region);
$xoopsTpl->assign('regionName',  $regionName);
$xoopsTpl->assign('guid',        $guid);
$xoopsTpl->assign('locX',        $locX);
$xoopsTpl->assign('locY',        $locY);
$xoopsTpl->assign('owner_name',  $owner_name);
$xoopsTpl->assign('owner_uuid',  $owner_uuid);
$xoopsTpl->assign('server',      $server);
$xoopsTpl->assign('module_url',  _OPENSIM_MODULE_URL);

$xoopsTpl->assign('isAdmin',  	 $isAdmin);
$xoopsTpl->assign('action_url',  $action_url);
$xoopsTpl->assign('avatars',     $avatars);

$xoopsTpl->assign('vcmode',      $vcmode);
$xoopsTpl->assign('vcmode_title',$vcmode_title);
$xoopsTpl->assign('voice_modes', $voice_modes);

$xoopsTpl->display('db:xoopensim_sim.html');

?>
