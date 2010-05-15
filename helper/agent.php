<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");

$region = optional_param('region', '', PARAM_TEXT);
if (!isGUID($region)) exit("<h4>bad region uuid!! ($region)</h4>");

$isGuest = isguest();
if ($isGuest)) {
	exit('<h4>guest user is not allowed!!</h4>');
}


$owner  = ' - ';
$state  = 0;
$userid = 0;	// Xoops

$courseid = optional_param('course', '0', PARAM_INT);
$agent 	  = required_param('agent', PARAM_ALPHAEXT);
if (!isGUID($agent)) exit('<h4>bad agent uuid!!</h4>');

require_login($courseid);
$hasPermit  = hasPermit($courseid);

global $CFG;
$grid_name  = $CFG->mdlopnsm_grid_name;
$userinfo   = $CFG->mdlopnsm_userinfo_link;
$action_url = MDLOPNSM_BLK_URL."/helper/agent.php";


//////////////
if ($agent) {
	// Moodle DB
	$userid = $avatardata->get('uid');
	if ($userid!='0') $owner = get_username_by_id($userid);
	$state = $avatardata->get('state');

	// OpenSim DB
	$profileTXT = "";
	$avinfo = opensim_get_avatar_info($agent);
	if ($avinfo!=null) {
		$UUI 			= $avinfo['UUID'];
		$firstname		= $avinfo['firstname'];
		$lastname		= $avinfo['lastname'];
		$fullname		= $avinfo['fullname'];       
		$created		= $avinfo['created'];
		$lastlogin		= $avinfo['lastlogin'];
		$regionUUID		= $avinfo['regionUUID'];
		$regionName		= $avinfo['regionName'];
		$serverIP		= $avinfo['serverIP'];
		$serverHttpPort	= $avinfo['serverHttpPort'];
		$serverURI		= $avinfo['serverURI'];
		$agentOnline	= $avinfo['agentOnline'];
		$profileTXT 	= $avinfp['profileTXT'];
	}

	// osprofile
	$handler = & xoops_getmodulehandler('profuserprofiledb');
	if ($handler!=null) {
		$profobj = $handler->get($agent);
		if ($profobj!=null) $profileTXT = $profobj->get('profileAboutText');
	}

	//
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


$module_url	  = MDLOPNSM_BLK_URL
$course       = "&amp;course=".$courseid;

$st_notsync   = get_string("mdlos_state_notsync",	"block_mdlopensim");
$st_active    = get_string("mdlos_state_active",	"block_mdlopensim");
$st_inactive  = get_string("mdlos_state_inactive",	"block_mdlopensim");

$region_ttl   = get_string("mdlos_region",          "block_mdlopensim");
$uuid_ttl     = get_string("mdlos_uuid",            "block_mdlopensim");
$change_ttl   = get_string("mdlos_change",          "block_mdlopensim");


$coordinates  = get_string("mdlos_coordinates",     "block_mdlopensim");
$admin_user   = get_string("mdlos_admin_user",      "block_mdlopensim");
$region_owner = get_string("mdlos_region_owner",    "block_mdlopensim");
$voice_mode   = get_string("mdlos_voice_chat_mode", "block_mdlopensim");

include(MDLOPNSM_BLK_PATH."/html/agent.html");

_MD_XPNSM_AVATARS_LIST
_MD_XPNSM_AVATARS_LIST
_MD_XPNSM_USERS_FOUND
_MD_XPNSM_PAGE
_MD_XPNSM_PAGE_OF
_MD_XPNSM_USER_SEARCH
_MD_XPNSM_FIRSTNAME
_MD_XPNSM_LASTNAME
_MD_XPNSM_NO
_MD_XPNSM_EDIT
_MD_XPNSM_FIRSTNAME
_MD_XPNSM_LASTNAME
_MD_XPNSM_LASTLOGIN
_MD_XPNSM_STATUS
_MD_XPNSM_CRNTREGION
_MD_XPNSM_OWNER
_MD_XPNSM_EDIT_TTL
_MD_XPNSM_GET_OWNER_TTL
_MD_XPNSM_NOT_SYNCDB
_MD_XPNSM_ONLINE_TTL
_MD_XPNSM_ACTIVE
_MD_XPNSM_INACTIVE
_MD_XPNSM_UNKNOWN_STATUS

$region_ttl   = get_string("mdlos_region",    		"block_mdlopensim");
$uuid_ttl     = get_string("mdlos_uuid",    		"block_mdlopensim");
$change_ttl   = get_string("mdlos_change",			"block_mdlopensim");
$region_info  = get_string("mdlos_region_info",		"block_mdlopensim");
$coordinates  = get_string("mdlos_coordinates", 	"block_mdlopensim");
$admin_user   = get_string("mdlos_admin_user",  	"block_mdlopensim");
$region_owner = get_string("mdlos_region_owner",	"block_mdlopensim");
$voice_mode	  = get_string("mdlos_voice_chat_mode", "block_mdlopensim");

		error(get_string('mdlos_db_connect_error', 'block_mdlopensim'));
		$regions_list  = get_string("mdlos_regions_list",  "block_mdlopensim");
		$location_x    = get_string("mdlos_location_x",    "block_mdlopensim");
		$location_y    = get_string("mdlos_location_y",    "block_mdlopensim");
		$region_name   = get_string("mdlos_region_name",   "block_mdlopensim");
		$estate_owner  = get_string("mdlos_estate_owner",  "block_mdlopensim");
		$ip_address    = get_string("mdlos_ipaddr",		   "block_mdlopensim");
		$regions_found = get_string("mdlos_regions_found", "block_mdlopensim");
		$page_num	   = get_string("mdlos_page",		   "block_mdlopensim");
		$page_num_of   = get_string("mdlos_page_of",	   "block_mdlopensim");
		//$region_owner = get_string("mdlos_region_owner", "block_mdlopensim");
		//$estate_id    = get_string("mdlos_estate_id",    "block_mdlopensim");
		error(get_string('mdlos_db_connect_error', 'block_mdlopensim'));

?>
