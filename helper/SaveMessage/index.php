<?php
//
// by Fumi.Iseki
//

require_once '../../../../mainfile.php';

require_once '../config.php';


$dbPort 	= 3306;
$dbHost 	= XOOPS_DB_HOST;
$dbName 	= XOOPS_DB_NAME;
$dbUser 	= XOOPS_DB_USER;
$dbPassword	= XOOPS_DB_PASS;


// DB Name
$db_messages = XOPNSIM_DB_PREFIX."offline_messages";

//$request_xml = $HTTP_RAW_POST_DATA;
//error_log("SaveMessage/index.php: ".$request_xml);

include(_OPENSIM_MODULE_PATH."/helper/SaveMessage/save_message.php");

?>
