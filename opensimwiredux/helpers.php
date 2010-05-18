<?php
# 
#  Copyright (c)Melanie Thielker and Teravus Ovares (http://opensimulator.org/)
# 
#  Redistribution and use in source and binary forms, with or without
#  modification, are permitted provided that the following conditions are met:
#      * Redistributions of source code must retain the above copyright
#        notice, this list of conditions and the following disclaimer.
#      * Redistributions in binary form must reproduce the above copyright
#        notice, this list of conditions and the following disclaimer in the
#        documentation and/or other materials provided with the distribution.
#      * Neither the name of the OpenSim Project nor the
#        names of its contributors may be used to endorse or promote products
#        derived FROM this software without specific prior written permission.
# 
#  THIS SOFTWARE IS PROVIDED BY THE DEVELOPERS ``AS IS'' AND ANY
#  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
#  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
#  DISCLAIMED. IN NO EVENT SHALL THE CONTRIBUTORS BE LIABLE FOR ANY
#  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
#  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
#  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
#  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
#  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
#  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
# 

#
# need external variable  by Fumi.Iseki
#	class DB
# 	CURRENCY_DB_HOST, CURRENCY_DB_NAME, CURRENCY_DB_USER, CURRENCY_DB_PASS
#	CURRENCY_MONEY_TBL,	CURRENCY_TRANSACTION_TBL
# 
#

####################################################################

#
# User provided interface routine to interface with payment processor
#

function process_transaction($avatarId, $amount, $ipAddress)
{
	# Do Credit Card Processing here!  Return False if it fails!
	# Remember, $amount is stored without decimal places, however it's assumed
	# that the transaction amount is in Cents and has two decimal places
	# 5 dollars will be 500
	# 15 dollars will be 1500

	return True;
}

###################### No user serviceable parts below #####################

#
# Helper routines
#

function convert_to_real($currency)
{
	if($currency == 0) return 0;

	$db = new DB;
	$db->set_DB(CURRENCY_DB_HOST, CURRENCY_DB_NAME, CURRENCY_DB_USER, CURRENCY_DB_PASS);

	# Get the currency conversion ratio in USD Cents per Money Unit
	# Actually, it's whatever currency your credit card processor uses

	$db->query("SELECT CentsPerMoneyUnit FROM ".CURRENCY_MONEY_TBL." limit 1");
	list($CentsPerMoneyUnit) = $db->next_record();
	$db->close();

	if (!$CentsPerMoneyUnit) $CentsPerMoneyUnit = 0;
		
	# Multiply the cents per unit times the requested amount

	$real = $CentsPerMoneyUnit * $currency;
	
	// Dealing in cents here. The XML requires an integer
	// so we have to ceil out any decimal places and cast as an integer

	$real = (integer)ceil($real);		

	return $real;
}



function update_simulator_balance($agentId)
{
	$db = new DB;

	if ($db->exist_table("Presence")) {
		$sql = "SELECT serverIP,serverHttpPort,serverURI FROM Presence".
				" INNER JOIN regions ON regions.uuid=Presence.RegionID WHERE Presence.UserID='".$db->escape($agentId)."'";
	}
	else {
		$sql = "SELECT serverIP,serverHttpPort,serverURI FROM agents".
				" INNER JOIN regions ON regions.uuid=agents.currentRegion WHERE agents.UUID='".$db->escape($agentId)."'";
	}
	$db->query($sql);
	$results = $db->next_record();
	$db->close();

	if ($results) {
		$serverip  = $results["serverIP"];
		$httpport  = $results["serverHttpPort"];
		$serveruri = $results["serverURI"];
	
		$req      = array('agentId' => $agentId);
		$params   = array($req);

		$request  = xmlrpc_encode_request('balanceUpdateRequest', $params);
		$response = do_call($serverip, $httpport, $serveruri, $request); 
	}
}



function user_alert($agentId, $soundId, $text)
{
    $db = new DB;

	if ($db->exist_table("Presence")) {
    	$sql = "SELECT serverIP,serverHttpPort,serverURI,regionSecret FROM Presence".
				" INNER JOIN regions ON regions.uuid=Presence.RegionID WHERE Presence.UserID='".$db->escape($agentId)."'";
	}
	else {
    	$sql = "SELECT serverIP,serverHttpPort,serverURI,regionSecret FROM agents".
				" INNRT JOIN regions ON regions.uuid=agents.currentRegion WHERE agents.UUID='".$db->escape($agentId)."'";
	}
    $db->query($sql);
    $results = $db->next_record();
	$db->close();

    if ($results) {
        $serverip  = $results["serverIP"];
        $httpport  = $results["serverHttpPort"];
		$serveruri = $results["serverURI"];
		$secret    = $results["regionSecret"];
        
        $req = array('agentId'=>$agentId, 'soundID'=>$soundId, 'text'=>$text, 'secret'=>$secret);
        $params = array($req);

        $request = xmlrpc_encode_request('userAlert', $params);
        $response = do_call($serverip, $httpport, $serveruti, $request);
    }
}



function move_money($sourceId, $destId, $amount, $aggregatePermInventory,
		$aggregatePermNextOwner, $flags, $transactionType, $description,
		$regionGenerated,$ipGenerated)
{
	$db = new DB;
	# SELECT current region
	if ($db->exist_table("Presence")) {
		$sql = "SELECT RegionID FROM Presence WHERE UserID='".$db->escape($sourceId)."'";
	}
	else {
		$sql = "SELECT currentRegion FROM agents WHERE UUID='".$db->escape($sourceId)."'";
	}
    $db->query($sql);

    $results = $db->next_record();
    if ($results) $currentRegion = $results["currentRegion"];
	$db->close();

	// CURRENCY
	$db = new DB;
	$db->set_DB(CURRENCY_DB_HOST, CURRENCY_DB_NAME, CURRENCY_DB_USER, CURRENCY_DB_PASS);

	# Add Cash to one account
	$sql = "INSERT INTO ".CURRENCY_TRANSACTION_TBL." (sourceId,destId,amount,flags,".
			"aggregatePermInventory,aggregatePermNextOwner,transactionType,".
			"description,timeOccurred,RegionGenerated,ipGenerated) ".
			"VALUES ('".
				$db->escape($sourceId)."','".
				$db->escape($destId)."',".
				$db->escape($amount).",".
				$db->escape($aggregatePermInventory).",".
				$db->escape($aggregatePermNextOwner).",".
				$db->escape($flags).",".
				$db->escape($transactionType).",'".
				$db->escape($description)."',".
				time().",'".
				$db->escape($currentRegion)."','".
				$db->escape($ipGenerated)."')";
	$db->query($sql);

	# Remove Cash FROM the other account
	
	$sql = "INSERT INTO ".CURRENCY_TRANSACTION_TBL." (sourceId,destId,amount,flags,".
			"aggregatePermInventory,aggregatePermNextOwner,transactionType,".
			"description,timeOccurred,RegionGenerated,ipGenerated) ".
			"VALUES ('".
				$db->escape($destId)."','".
				$db->escape($sourceId)."',".
				$db->escape(-$amount).",".
				$db->escape($aggregatePermInventory).",".
				$db->escape($aggregatePermNextOwner).",".
				$db->escape($flags).",".
				$db->escape($transactionType).",'".
				$db->escape($description)."',".
				time().",'".
				$db->escape($currentRegion)."','".
				$db->escape($ipGenerated)."')";
	$db->query($sql);
	$db->close();
}



function get_balance($avatarId)
{
    $db = new DB;
	$db->set_DB(CURRENCY_DB_HOST, CURRENCY_DB_NAME, CURRENCY_DB_USER, CURRENCY_DB_PASS);

    $cash = 0;
    $sql = "SELECT SUM(amount) FROM ".CURRENCY_TRANSACTION_TBL." WHERE destId='".$db->escape($avatarId)."'";
	$db->query($sql);

    list($cash) = $db->next_record();

	$db -> close();
    return (integer)$cash;
}



function do_call($host, $port, $uri, $request)
{
	$url = "";
	if ($uri!="") {
    	$dec = explode(":", $uri);
    	if (!strncasecmp($dec[0], "http", 4)) $url = "$dec[0]:$dec[1]";
	}   
	if ($url=="") $url ="http://$serverIP";
	$url = "$url:$serverHttpPort/";

    $header[] = "Content-type: text/xml";
    $header[] = "Content-length: ".strlen($request);
    
    $ch = curl_init();   
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    
    $data = curl_exec($ch);       
    if (!curl_errno($ch)) {
        curl_close($ch);
        return $data;
    }
}



function agent_name($agentId)
{
	$db = new DB;

	if ($db->exist_table("UserAccounts")){
		$sql = "SELECT FirstName,LastName FROM UserAccounts WHERE PrincipalID='".$agentId."'";
	}
	else {
		$sql = "SELECT username, lastname FROM users WHERE UUID='".$agentId."'";
	}
	$db->query($sql);

	$record = $db->next_record();
	$db->close();
	if(!$record) return "";

	$name = implode(" ", array($record[0], $record[1]));

	return $name;
}
?>
