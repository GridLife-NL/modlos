<?php
//
// by Fumi.Iseki for Moodle '10 5/16
//

require_once './config.php';

global $CFG;

$dbPort 	= 3306;
$dbHost 	= $CFG->dbhost;
$dbName 	= $CFG->dbname:
$dbUser 	= $CFG->dbuser;
$dbPassword	= $CFG->dbpass;

$groupReadKey  = MDLOPNSIM_GRP_RKEY;
$groupWriteKey = MDLOPNSIM_GRP_WKEY;


// DB Name
$osagent 				= $CFG->prefix."block_mdlos_group_active";
$osgroup 				= $CFG->prefix."block_mdlos_group_list";
$osgroupinvite 			= $CFG->prefix."block_mdlos_group_invite";
$osgroupmembership 		= $CFG->prefix."block_mdlos_group_membership";
$osgroupnotice 			= $CFG->prefix."block_mdlos_group_notice";
$osgrouprolemembership	= $CFG->prefix."block_mdlos_group_rolemembership";
$osrole 				= $CFG->prefix."block_mdlos_group_role";


$debugXMLRPC = 0;
$debugXMLRPCFile = "xmlrpc.log";
	
$groupRequireAgentAuthForWrite = FALSE;
$groupEnforceGroupPerms = FALSE;

//$request_xml = $HTTP_RAW_POST_DATA;
//error_log("xmlgroups.php: ".$request_xml);

include(MDLOPNSM_BLK_PATH."/helper/xmlrpc.php");

?>
