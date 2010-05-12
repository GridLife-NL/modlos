<?php

require_once '../../../mainfile.php';

define ('_OPENSIM_DIR_NAME',    basename(dirname(dirname(__FILE__))));
define ('_OPENSIM_MODULE_URL',  XOOPS_MODULE_URL.'/'._OPENSIM_DIR_NAME);
define ('_OPENSIM_MODULE_PATH', XOOPS_ROOT_PATH.'/modules/'._OPENSIM_DIR_NAME);

require_once(_OPENSIM_MODULE_PATH."/include/config.php");
require_once(_OPENSIM_MODULE_PATH."/include/opensim_mysql.php");
require_once(_OPENSIM_MODULE_PATH."/include/xoopensim.func.php");


$owner = ' - ';
$state = 0;


$root = & XCube_Root::getSingleton();

if ($root->mContext->mUser->isInRole('Site.GuestUser')) {
	exit('<h4>guest user is not allowed!!i</h4>');
}

$agent = $root->mContext->mRequest->getRequest('agent');
if (!preg_match("/^[0-9a-fA-F-]+$/", $agent)) exit('<h4>bad agent uuid!!</h4>');
$grid_name = $root->mContext->mModuleConfig['grid_name'];


if ($agent) {
	// Xoops DB
	$usersdbHandler = & xoops_getmodulehandler('usersdb');
	$avatardata = & $usersdbHandler->get($agent);
	if ($avatardata!=null) {
		$userid = $avatardata->get('uid');
		if ($userid!='0') $owner = get_username_byid($userid);
		$state = $avatardata->get('state');
	}

	// OpenSim DB
	$DbLink = new DB;
	$DbLink->query("SELECT UUID,username,lastname,homeRegion,created,lastLogin,profileAboutText FROM ".OPENSIM_USERS_TBL." where uuid='$agent'");
	list($UUID, $firstN, $lastN, $regHandle, $created, $lastlogin, $profileTXT ) = $DbLink->next_record();

	$DbLink->query("SELECT UUID,regionName,serverIP,serverHttpPort,serverURI FROM ".OPENSIM_REGIONS_TBL." where regionHandle='$regHandle'");
	list($regionUUID, $regionName, $serverIP, $serverHttpPort, $serverURI) = $DbLink->next_record();

	$DbLink->query("SELECT agentOnline FROM ".OPENSIM_AGENTS_TBL." where UUID='$UUID'");
	list($agentOnline) = $DbLink->next_record();

	$born   = date("Y M d (D) - A g:i", $created);
	if ($lastlogin=='0') $lastin = ' - ';
	else				 $lastin = date("Y M d (D) - A g:i", $lastlogin);
	$DbLink->close();
}


$server = "";
if ($serverURI!="") {
	$dec = explode(":", $serverURI);
    if (!strncasecmp($dec[0], "http", 4)) $server = "$dec[0]:$dec[1]";
}
if ($server=="") {
	$server ="http://$serverIP";
}
$server = "$server:$serverHttpPort";
$guid = str_replace("-", "", $UUID);


$xoopsTpl->assign('grid_name',  $grid_name);
$xoopsTpl->assign('regionName', $regionName);
$xoopsTpl->assign('regionUUID', $regionUUID);
$xoopsTpl->assign('firstN',     $firstN);
$xoopsTpl->assign('lastN',      $lastN);
$xoopsTpl->assign('UUID',       $UUID);
$xoopsTpl->assign('born',       $born);
$xoopsTpl->assign('lastin',     $lastin);
$xoopsTpl->assign('owner',		$owner);
$xoopsTpl->assign('state',		$state);
$xoopsTpl->assign('agentOnline',$agentOnline);
$xoopsTpl->assign('profileTXT', $profileTXT);
$xoopsTpl->assign('server',     $server);
$xoopsTpl->assign('guid',       $guid);

$xoopsTpl->assign('module_url', _OPENSIM_MODULE_URL);
$xoopsTpl->assign('st_notsync', XOPNSIM_STATE_NOTSYNC);
$xoopsTpl->assign('st_active',  XOPNSIM_STATE_ACTIVE); 
$xoopsTpl->assign('st_inactive',XOPNSIM_STATE_INACTIVE);

$xoopsTpl->display('db:xoopensim_agent.html');

?>
