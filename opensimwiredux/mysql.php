<?php
/*
 * Copyright (c) 2007, 2008 Contributors, http://opensimulator.org/
 * See CONTRIBUTORS for a full list of copyright holders.
 *
 * See LICENSE for the full licensing terms of this file.
 *
*/

//This looks like its lifted from http://www.weberdev.com/get_example-4372.html  
//I'd contact the original developer for licensing info, but his website is broken.

//
// Modified by Fumi.Iseki for CMS/LMS '10 3/29
//


// for TEST
if (!defined("OPENSIM_DB_HOST")) {
	exit();
	//define("OPENSIM_DB_HOST",  "202.26.159.xxx");
	//define("OPENSIM_DB_NAME",  "opensim_xxxx");
	//define("OPENSIM_DB_USER",  "opensim_xxxx");
	//define("OPENSIM_DB_PASS",  "opensim_xxxx");
}



class DB
{
	var $Host 	  = OPENSIM_DB_HOST;	// Hostname of our MySQL server
	var $Database = OPENSIM_DB_NAME;	// Logical database name on that server
	var $User 	  = OPENSIM_DB_USER;	// Database user
	var $Password = OPENSIM_DB_PASS;	// Database user's password
	var $Link_ID  = 0;					// Result of mysql_connect()
	var $Query_ID = 0;					// Result of most recent mysql_query()
	var $Record	  = array();			// Current mysql_fetch_array()-result
	var $Row;							// Current row number
	var $Errno    = 0;					// Error state of query
	var $Error    = "";


	/*
	function DB($connect=false)
	{
		$this->Host 	= OPENSIM_DB_HOST;	
		$this->Database = OPENSIM_DB_NAME;	
		$this->User 	= OPENSIM_DB_USER;	
		$this->Password = OPENSIM_DB_PASS;	

		if ($connect) $this->connect();
	}
	*/



	function halt($msg)
	{
		echo("</td></tr></table><b>Database error:</b> $msg<br />\n");
		echo("<b>MySQL error</b>: $this->Errno ($this->Error)<br />\n");
		die("Session halted.");
	}



	// Added by Fumi.Iseki
	function set_DB($dbhost, $dbname, $dbuser, $dbpass)
	{
		$this->Host 	= $dbhost;	
		$this->Database = $dbname;	
		$this->User 	= $dbuser;	
		$this->Password = $dbpass;	
	}



	function connect()
	{
		if($this->Link_ID==0) {
			$this->Link_ID = mysql_connect($this->Host, $this->User, $this->Password);
			if (!$this->Link_ID) {
				//$this->halt("Link_ID == false, connect failed");
				$this->Errno = 999;
				return;
			}
			//if (_CHARSET=="UTF-8") mysql_set_charset('utf8'); 
			mysql_set_charset('utf8'); 
			$SelectResult = mysql_select_db($this->Database, $this->Link_ID);
			if (!$SelectResult) {
				$this->Errno = mysql_errno($this->Link_ID);
				$this->Error = mysql_error($this->Link_ID);
				$this->Link_ID = 0;
				$this->halt("cannot select database <i>".$this->Database."</i>");
			}
		}
	}



 	function escape($String)
 	{
 		return mysql_escape_string($String);
 	}



	function query($Query_String)
	{
		$this->connect();
		if ($this->Errno!=0) return 0; 	// added by Fumi.Iseki

		$this->Query_ID = mysql_query($Query_String,$this->Link_ID);
		$this->Row = 0;
		$this->Errno = mysql_errno();
		$this->Error = mysql_error();
		if (!$this->Query_ID) {
			$this->halt("Invalid SQL: ".$Query_String);
		}
		return $this->Query_ID;
	}



	function next_record()
	{
		$this->Record = @mysql_fetch_array($this->Query_ID);
		$this->Row += 1;
		$this->Errno = mysql_errno();
		$this->Error = mysql_error();
		$stat = is_array($this->Record);
		if (!$stat) {
			@mysql_free_result($this->Query_ID);
			$this->Query_ID = 0;
		}
		return $this->Record;
	}



	function num_rows()
	{
		return mysql_num_rows($this->Query_ID);
	}



	function affected_rows()
	{
		return mysql_affected_rows($this->Link_ID);
	}



	function optimize($tbl_name)
	{
		$this->connect();
		if ($this->Errno!=0) return;

		$this->Query_ID = @mysql_query("OPTIMIZE TABLE $tbl_name",$this->Link_ID);
	}



	function clean_results()
	{
		if ($this->Query_ID!=0) {
			mysql_freeresult($this->Query_ID);
			$this->Query_ID = 0;
		}
	}



	function close()
	{
		if ($this->Link_ID) {
			mysql_close($this->Link_ID);
			$this->Link_ID = 0;
		}
	}



	// added by Fumi.Iseki
	function exist_table($table)
	{
		$this->connect();
		if ($this->Errno!=0) return false;

		$tl = mysql_list_tables($this->Database, $this->Link_ID);
		while($row=mysql_fetch_row($tl)) {
			if (in_array($table, $row)) return true;
		}
		return false;
	}

}

?>
