<?php
//
// Group DB configration by Fumi.Iseki
//

include_once("./config.php");


if (defined('CMS_DB_HOST'))
{
	$dbPort 		= 3306;
	$dbHost 		= CMS_DB_HOST;
	$dbName 		= CMS_DB_NAME;
	$dbUser 		= CMS_DB_USER;
	$dbPassword		= CMS_DB_PASS;

	$groupReadKey  	= XMLGROUP_RKEY;
	$groupWriteKey 	= XMLGROUP_WKEY;

	// DB Table Name
	$osagent 				= XMLGROUP_ACTIVE_TBL;
	$osgroup 				= XMLGROUP_LIST_TBL;
	$osgroupinvite 			= XMLGROUP_INVITE_TBL;
	$osgroupmembership 		= XMLGROUP_MEMBERSHIP_TBL;
	$osgroupnotice 			= XMLGROUP_NOTICE_TBL;
	$osgrouprolemembership	= XMLGROUP_ROLE_MEMBER_TBL;
	$osrole 				= XMLGROUP_ROLE_TBL;
}
else
{
    // Please set information of your mysql server, if you do not use CMS/LMS.
	$dbPort 		= 3306;
	$dbHost 		= "localhost";
	$dbName 		= "";
	$dbUser 		= "";
	$dbPassword		= "";

	$groupReadKey  	= "1234";
	$groupWriteKey 	= "1234";

	// DB Table Name
	$osagent 				= 'osagent';
	$osgroup 				= 'osgroup';
	$osgroupinvite 			= 'osgroupinvite';
	$osgroupmembership 		= 'osgroupmembership';
	$osgroupnotice 			= 'osgroupnotice';
	$osgrouprolemembership	= 'osgrouprolemembership';
	$osrole 				= 'osrole';
}


$groupRequireAgentAuthForWrite = FALSE;
$groupEnforceGroupPerms = FALSE;

?>
