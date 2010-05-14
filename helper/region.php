<?php
/////////////////////////////////////////////////////////////////////////////
// Region の個別情報を表示する．
//
// usage... http://xxx/yyy/zzz/region.php?region=3a9379b7-1821-4b04-ab97-e38df166bac1
//

require_once '../../../mainfile.php';

define ('_OPENSIM_DIR_NAME',    basename(dirname(dirname(__FILE__))));
define ('_OPENSIM_MODULE_URL',  XOOPS_MODULE_URL.'/'._OPENSIM_DIR_NAME);
define ('_OPENSIM_MODULE_PATH', XOOPS_ROOT_PATH.'/modules/'._OPENSIM_DIR_NAME);

require_once(_OPENSIM_MODULE_PATH."/include/config.php");
require_once(_OPENSIM_MODULE_PATH."/include/opensim.func.php");
require_once(_OPENSIM_MODULE_PATH."/include/xoopensim.func.php");


$root = & XCube_Root::getSingleton();

if ($root->mContext->mUser->isInRole('Site.GuestUser')) {
	exit('<h4>guest user is not allowed!!</h4>');
}
$isAdmin = isXoopensimAdmin($root);

$region = $root->mContext->mRequest->getRequest('region');
if (!preg_match("/^[0-9a-fA-F-]+$/", $region)) exit('<h4>bad region uuid!!</h4>');
$grid_name  = $root->mContext->mModuleConfig['grid_name'];
$action_url = _OPENSIM_MODULE_URL."/helper/region.php";


//////////////
$col = 0;
$users = opensim_get_avatar_infos();
foreach($users as $user) {
	$avatars[$col]['name'] = $user['firstname']." ".$user['lastname'];
	$avatars[$col]['uuid'] = $user['UUID'];
	$col++;
}

if ($isAdmin and xoops_getenv("REQUEST_METHOD")=="POST") {
	$rgnadmin = $root->mContext->mRequest->getRequest('rgnadmin');
	if ($rgnadmin!="") {
		opensim_set_region_owner($region, $rgnadmin);
	}
	$voice_mode = $root->mContext->mRequest->getRequest('voice_mode');
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
