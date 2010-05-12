<?php
//
// by Fumi.Iseki for Xoops Cube '09 5/31
//

//define('_LEGACY_PREVENT_EXEC_COMMON_', 1);
//define('_LEGACY_PREVENT_LOAD_CORE_', 1);
require_once '../../../mainfile.php';

require_once './config.php';


$dbPort 	= 3306;
$dbHost 	= XOOPS_DB_HOST;
$dbName 	= XOOPS_DB_NAME;
$dbUser 	= XOOPS_DB_USER;
$dbPassword	= XOOPS_DB_PASS;

$groupReadKey  = XOPNSIM_GRP_RKEY;
$groupWriteKey = XOPNSIM_GRP_WKEY;


// DB Name
$osagent 				= XOPNSIM_DB_PREFIX."group_active";
$osgroup 				= XOPNSIM_DB_PREFIX."group_list";
$osgroupinvite 			= XOPNSIM_DB_PREFIX."group_invite";
$osgroupmembership 		= XOPNSIM_DB_PREFIX."group_membership";
$osgroupnotice 			= XOPNSIM_DB_PREFIX."group_notice";
$osgrouprolemembership	= XOPNSIM_DB_PREFIX."group_rolemembership";
$osrole 				= XOPNSIM_DB_PREFIX."group_role";


$debugXMLRPC = 0;
$debugXMLRPCFile = "xmlrpc.log";
	
$groupRequireAgentAuthForWrite = FALSE;
$groupEnforceGroupPerms = FALSE;

//$request_xml = $HTTP_RAW_POST_DATA;
//error_log("xmlgroups.php: ".$request_xml);

include(_OPENSIM_MODULE_PATH."/helper/xmlrpc.php");

?>
