<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");

$isGuest = isguest();
if ($isGuest) {
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
$action_url = MDLOPNSM_BLK_URL."/helper/agent.php";


//////////////
//global $USER;
$owner  = ' - ';
$userid = 0;
$state  = 0;

if ($agent) {
	// Moodle DB
	if ($mdlos = get_record('block_mdlos_users', 'UUID', $agent)) {
		$userid = $mdlos->uid;
		$state  = $mdlos->state;
		if ($moodle = get_record("user", "id", $userid)) {
			$owner = $moodle->firstname." ".$moodle->lastname;
		}
	}

	// OpenSim DB
	$profileTXT = "";
	$avinfo = opensim_get_avatar_info($agent);
	if ($avinfo!=null) {
		$UUID 			= $avinfo['UUID'];
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
	if ($profileTXT=="") {
		if ($rec = get_record('block_mdlos_prof_userprofile', 'useruuid', $agent, '', '', '', '', 'profileAboutText')) {
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
$module_url	  	= MDLOPNSM_BLK_URL;
$course       	= "&amp;course=".$courseid;

$user_info_ttl  = get_string("mdlos_user_info",		"block_mdlopensim");
$avatar_info_ttl= get_string("mdlos_avatar_info",	"block_mdlopensim");
$user_ttl	  	= get_string("mdlos_user",			"block_mdlopensim");
$uuid_ttl	  	= get_string("mdlos_uuid",			"block_mdlopensim");
$status_ttl	  	= get_string("mdlos_status",		"block_mdlopensim");
$no_sync_db_ttl = get_string("mdlos_not_syncdb",	"block_mdlopensim");
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

include(MDLOPNSM_BLK_PATH."/html/agent.html");

?>
