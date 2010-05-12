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
require_once(_OPENSIM_MODULE_PATH."/include/opensim_mysql.php");

$root = & XCube_Root::getSingleton();

if ($root->mContext->mUser->isInRole('Site.GuestUser')) {
	exit('<h4>guest user is not allowed!!</h4>');
}    


$region = $root->mContext->mRequest->getRequest('region');
if (!preg_match("/^[0-9a-fA-F-]+$/", $region)) exit('<h4>bad region uuid!!</h4>');
$grid_name = $root->mContext->mModuleConfig['grid_name'];

if ($region) {
	$DbLink = new DB;
	$DbLink->query("SELECT uuid,regionName,serverIP,serverHttpPort,serverURI,locX,locY,owner_uuid FROM ".OPENSIM_REGIONS_TBL." where uuid='$region'");
	list($UUID, $regionName, $serverIP, $serverHttpPort, $serverURI, $locX, $locY, $owner) = $DbLink->next_record();

	$DbLink->query("SELECT username,lastname FROM ".OPENSIM_USERS_TBL." where uuid='$owner'");
	list($firstN, $lastN) = $DbLink->next_record();

	$DbLink->close();
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
$guid = str_replace("-", "", $UUID);

$locX = $locX/256;
$locY = $locY/256;

$xoopsTpl->assign('grid_name',  $grid_name);
$xoopsTpl->assign('regionName', $regionName);
$xoopsTpl->assign('UUID',       $UUID);
$xoopsTpl->assign('guid',       $guid);
$xoopsTpl->assign('locX',       $locX);
$xoopsTpl->assign('locY',       $locY);
$xoopsTpl->assign('firstN',     $firstN);
$xoopsTpl->assign('lastN',      $lastN);
$xoopsTpl->assign('owner',      $owner);
$xoopsTpl->assign('server',     $server);
$xoopsTpl->assign('module_url', _OPENSIM_MODULE_URL);

$xoopsTpl->display('db:xoopensim_sim.html');

?>
