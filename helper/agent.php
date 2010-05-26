<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");

if (isguest()) {
	exit('<h4>guest user is not allowed!!</h4>');
}


$courseid = optional_param('course', '0', PARAM_INT);
$agent 	  = required_param('agent', PARAM_TEXT);
if (!isGUID($agent)) exit("<h4>bad agent uuid!! ($agent)</h4>");

require_login($courseid);
$hasPermit  = hasPermit($courseid);

global $CFG;
$grid_name  = $CFG->mdlopnsm_grid_name;
$userinfo   = $CFG->mdlopnsm_userinfo_link;
$action_url = CMS_MODULE_URL."/helper/agent.php";


//////////////
//global $USER;
$owner  = ' - ';
$userid = 0;
$state  = 0;

if ($agent) {
	// OpenSim DB
	$profileTXT = "";
	$avinfo = opensim_get_avatar_info($agent);
	if ($avinfo!=null) {
		$UUID 			= $avinfo['UUID'];
		$firstN			= $avinfo['firstname'];
		$lastN			= $avinfo['lastname'];
		$fullname		= $avinfo['fullname'];       
		$created		= $avinfo['created'];
		$lastlogin		= $avinfo['lastlogin'];
		$regionUUID		= $avinfo['regionUUID'];
		$regionName		= $avinfo['regionName'];
		$serverIP		= $avinfo['serverIP'];
		$serverHttpPort	= $avinfo['serverHttpPort'];
		$serverURI		= $avinfo['serverURI'];
		$agentOnline	= $avinfo['online'];
		$profileTXT 	= $avinfp['profileTXT'];
	}

	// Moodle DB
	if ($mdlos = get_record('mdlos_users', 'uuid', $agent)) {
		$userid = $mdlos->user_id;
		$state  = $mdlos->state;
		if ($moodle = get_record("user", "id", $userid)) {
			$owner  = getUserName($moodle->firstname, $moodle->lastname);
		}
	}

	// osprofile
	if ($profileTXT=="") {
		if ($rec = get_record(MDL_PROFILE_USERPROFILE_TBL, 'useruuid', $agent, '', '', '', '', 'profileAboutText')) {
			$profileTXT = $rec->profileAboutText;
		}
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


///////////////
$module_url	  	= CMS_MODULE_URL;
$course       	= "&amp;course=".$courseid;

$user_info_ttl  = get_string("mdlos_user_info",		"block_mdlopensim");
$avatar_info_ttl= get_string("mdlos_avatar_info",	"block_mdlopensim");
$user_ttl	  	= get_string("mdlos_user",			"block_mdlopensim");
$uuid_ttl	  	= get_string("mdlos_uuid",			"block_mdlopensim");
$status_ttl	  	= get_string("mdlos_status",		"block_mdlopensim");
$not_syncdb_ttl	= get_string("mdlos_not_syncdb",	"block_mdlopensim");
$active_ttl   	= get_string("mdlos_active",		"block_mdlopensim");
$inactive_ttl	= get_string("mdlos_inactive",		"block_mdlopensim");
$online_ttl		= get_string("mdlos_online_ttl",	"block_mdlopensim");
$offline_ttl	= get_string("mdlos_offline_ttl",	"block_mdlopensim");
$profile_ttl	= get_string("mdlos_profile",		"block_mdlopensim");

$born_on	  	= get_string("mdlos_born_on",		"block_mdlopensim");
$lastlogin	  	= get_string("mdlos_lastlogin",		"block_mdlopensim");
$ownername	  	= get_string("mdlos_ownername",		"block_mdlopensim");
$unknown_status	= get_string("mdlos_unknown_status","block_mdlopensim");
$home_region	= get_string("mdlos_home_region",	"block_mdlopensim");
$has_noprofile	= get_string("mdlos_has_noprofile",	"block_mdlopensim");

include(CMS_MODULE_PATH."/html/agent.html");

?>
