<?php

require_once('./config.php');
require_once('./opensim.mysql.php');



//$request_xml = $HTTP_RAW_POST_DATA;
//error_log("offline.php: ".$request_xml);



$DbLink = new DB;
$DbLink->set_DB(OFFLINE_DB_HOST, OFFLINE_DB_NAME, OFFLINE_DB_USER, OFFLINE_DB_PASS);


$method = $_SERVER["PATH_INFO"];

if ($method == "/SaveMessage/") {
	$msg = $HTTP_RAW_POST_DATA;
	$start = strpos($msg, "?>");

	if ($start != -1) {
		$start+=2;
		$msg = substr($msg, $start);
		$parts = split("[<>]", $msg);
		$from_agent = $parts[4];
		$to_agent   = $parts[12];
        $DbLink->query("INSERT INTO ".OFFLINE_MESSAGE_TBL." (to_uuid, from_uuid, message) ".
					   "VALUES ('".mysql_escape_string($to_agent)."','".mysql_escape_string($from_agent)."','".mysql_escape_string($msg)."')");
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>true</boolean>";
    }
    else {
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><boolean>false</boolean>";
    }
    exit;
}

if ($method == "/RetrieveMessages/") {
    $parms = $HTTP_RAW_POST_DATA;
    $parts = split("[<>]", $parms);
    $agent_id = $parts[6];
       
    $DbLink->query("SELECT message FROM ".OFFLINE_MESSAGE_TBL." WHERE to_uuid='".mysql_escape_string($agent_id)."'");

    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>".
		 "<ArrayOfGridInstantMessage xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">";
    while(list($message) = $DbLink->next_record()) {
        echo $message;
    }
    echo "</ArrayOfGridInstantMessage>";
       
    $DbLink->query("DELETE FROM ".OFFLINE_MESSAGE_TBL." WHERE to_uuid='".mysql_escape_string($agent_id)."'");
    exit;
}


$DbLink->close();


?>
