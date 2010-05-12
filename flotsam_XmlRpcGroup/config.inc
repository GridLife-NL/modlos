<?php
    $dbName = "xoops_cube";
    $dbHost = "localhost";
    $dbPort = 3306;
    $dbUser = "xooxoo_user";
    $dbPassword	="xooxoo_Pass";

    // Set to 0 for no debugging, 1 for essential debugging, 2 for more information.
    $debugXMLRPC = 0;
    $debugXMLRPCFile = "xmlrpc.log";
	
	//////////////////////////////
	//// Group security
	/////////////////////
	
	// A xmlrpc client must have this key to commit changes to the Groups DB
	// Leave blank to allow all clients to make changes.
	$groupReadKey  = '80386';
	$groupWriteKey = 'Z8000';
	
	$groupRequireAgentAuthForWrite = FALSE;
	
	$groupEnforceGroupPerms = FALSE;
	
?>
