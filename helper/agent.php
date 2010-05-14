<?php

require_once '../../../mainfile.php';

define ('_OPENSIM_DIR_NAME',    basename(dirname(dirname(__FILE__))));
define ('_OPENSIM_MODULE_URL',  XOOPS_MODULE_URL.'/'._OPENSIM_DIR_NAME);
define ('_OPENSIM_MODULE_PATH', XOOPS_ROOT_PATH.'/modules/'._OPENSIM_DIR_NAME);

require_once(_OPENSIM_MODULE_PATH."/include/config.php");
require_once(_OPENSIM_MODULE_PATH."/include/xoopensim.func.php");



$owner  = ' - ';
$state  = 0;
$userid = 0;


$root = & XCube_Root::getSingleton();

if ($root->mContext->mUser->isInRole('Site.GuestUser')) {
	exit('<h4>guest user is not allowed!!i</h4>');
}

$agent = $root->mContext->mRequest->getRequest('agent');
if (!preg_match("/^[0-9a-fA-F-]+$/", $agent)) exit('<h4>bad agent uuid!!</h4>');
$grid_name = $root->mContext->mModuleConfig['grid_name'];
$userinfo  = $root->mContext->mModuleConfig['userinfo_link'];
$isAdmin = isXoopensimAdmin($root);

if ($agent) {
	// Xoops DB
	$usersdbHandler = & xoops_getmodulehandler('usersdb');
	$avatardata = & $usersdbHandler->get($agent);
	if ($avatardata!=null) {
		$userid = $avatardata->get('uid');
		if ($userid!='0') $owner = get_username_by_id($userid);
		$state = $avatardata->get('state');
	}

	// OpenSim DB
	$DbLink = new DB;
	$online = false;

	$profileTXT = "";

	if ($DbLink->exist_table("UserAccounts")) {
		$DbLink->query("SELECT PrincipalID,FirstName,LastName,HomeRegionID,Created,Login FROM UserAccounts".
						" LEFT JOIN Presence ON PrincipalID=UserID AND Logout!='0' WHERE PrincipalID='$agent'");
		list($UUID, $firstN, $lastN, $regionUUID, $created, $lastlogin) = $DbLink->next_record();

		$DbLink->query("SELECT regionName,serverIP,serverHttpPort,serverURI FROM regions WHERE uuid='$regionUUID'");
		list($regionName, $serverIP, $serverHttpPort, $serverURI) = $DbLink->next_record();

		$DbLink->query("SELECT Online FROM Presence WHERE UserID='$UUID'");
		list($agentOnline) = $DbLink->next_record();
		if ($agentOnline=="true") $online = true;
	}
	else {
		$DbLink->query("SELECT UUID,username,lastname,homeRegion,created,lastLogin,profileAboutText FROM users WHERE uuid='$agent'");
		list($UUID, $firstN, $lastN, $regHandle, $created, $lastlogin, $profileTXT ) = $DbLink->next_record();

		$DbLink->query("SELECT uuid,regionName,serverIP,serverHttpPort,serverURI FROM regions WHERE regionHandle='$regHandle'");
		list($regionUUID, $regionName, $serverIP, $serverHttpPort, $serverURI) = $DbLink->next_record();

		$DbLink->query("SELECT agentOnline FROM agents WHERE UUID='$UUID'");
		list($agentOnline) = $DbLink->next_record();
		if ($agentOnline==1) $online = true;
	}
	$DbLink->close();

	// osprofile
	$handler = & xoops_getmodulehandler('profuserprofiledb');
	if ($handler!=null) {
		$profobj = $handler->get($agent);
		if ($profobj!=null) $profileTXT = $profobj->get('profileAboutText');
	}

	if ($created=='0' or $created==null or $created=="" or $created=='0') {
		$born = ' - ';
	}
	else {
		$born = date("Y M d (D) - A g:i", $created);
	}
	if ($lastlogin==null or $lastlogin=="" or $lastlogin=='0') {
		$lastin = ' - ';
	}
	else {
		$lastin = date("Y M d (D) - A g:i", $lastlogin);
	}
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
$xoopsTpl->assign('userinfo',   $userinfo);
$xoopsTpl->assign('regionName', $regionName);
$xoopsTpl->assign('regionUUID', $regionUUID);
$xoopsTpl->assign('firstN',     $firstN);
$xoopsTpl->assign('lastN',      $lastN);
$xoopsTpl->assign('UUID',       $UUID);
$xoopsTpl->assign('born',       $born);
$xoopsTpl->assign('lastin',     $lastin);
$xoopsTpl->assign('owner',		$owner);
$xoopsTpl->assign('userid',		$userid);
$xoopsTpl->assign('state',		$state);
$xoopsTpl->assign('agentOnline',$online);
$xoopsTpl->assign('profileTXT', $profileTXT);
$xoopsTpl->assign('server',     $server);
$xoopsTpl->assign('guid',       $guid);
$xoopsTpl->assign('isAdmin',    $isAdmin);

$xoopsTpl->assign('module_url', _OPENSIM_MODULE_URL);
$xoopsTpl->assign('st_notsync', XOPNSIM_STATE_NOTSYNC);
$xoopsTpl->assign('st_active',  XOPNSIM_STATE_ACTIVE); 
$xoopsTpl->assign('st_inactive',XOPNSIM_STATE_INACTIVE);

$xoopsTpl->display('db:xoopensim_agent.html');

?>
