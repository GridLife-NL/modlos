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
$osagent 				= MDLOPNSM_DB_PREFIX."group_active";
$osgroup 				= MDLOPNSM_DB_PREFIX."group_list";
$osgroupinvite 			= MDLOPNSM_DB_PREFIX."group_invite";
$osgroupmembership 		= MDLOPNSM_DB_PREFIX."group_membership";
$osgroupnotice 			= MDLOPNSM_DB_PREFIX."group_notice";
$osgrouprolemembership	= MDLOPNSM_DB_PREFIX."group_rolemembership";
$osrole 				= MDLOPNSM_DB_PREFIX."group_role";


$debugXMLRPC = 0;
$debugXMLRPCFile = "xmlrpc.log";
	
$groupRequireAgentAuthForWrite = FALSE;
$groupEnforceGroupPerms = FALSE;

//$request_xml = $HTTP_RAW_POST_DATA;
//error_log("xmlgroups.php: ".$request_xml);

include(MDLOPNSM_BLK_PATH."/helper/xmlrpc.php");

?>
