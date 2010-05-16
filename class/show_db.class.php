<?php
//////////////////////////////////////////////////
//
// ?action=home または ?action= の場合に読み出される．
//

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");



class  ShowDataBase
{
	var	$grid_status;
	var $now_online;
	var $lastmonth_online;
	var $user_count;
	var $region_count;
	var $courseid;


	function  ShowDataBase($courseid) 
	{
		$this->courseid = $courseid;

		$this->grid_status 		= false;
		$this->now_online 		= '0';
		$this->lastmonth_online = '0';
		$this->user_count 		= '0';
		$this->region_count 	= '0';
	}



	function  execute()
	{
		$ret = opensim_check_db();

		$this->grid_status      = $ret['grid_status'];
		$this->now_online       = $ret['now_online'];
		$this->lastmonth_online = $ret['lastmonth_online'];
		$this->user_count       = $ret['user_count'];
		$this->region_count     = $ret['region_count'];
	}



	function  print_page() 
	{
		global $CFG;

		$this->execute();

		$grid_name       	= $CFG->mdlopnsm_grid_name;
		$content         	= $CFG->mdlopnsm_db_status_content;
		$module_url 		= MDLOPNSM_BLK_URL;

		$db_status 			= get_string("mdlos_db_status", 		  "block_mdlopensim");
		$online_ttl 		= get_string("mdlos_online_ttl", 		  "block_mdlopensim");
		$offline_ttl 		= get_string("mdlos_offline_ttl", 		  "block_mdlopensim");
		$total_users 		= get_string("mdlos_total_users", 		  "block_mdlopensim");
		$total_regions 		= get_string("mdlos_total_regions", 	  "block_mdlopensim");
		$visitors_last30days= get_string("mdlos_visitors_last30days", "block_mdlopensim");
		$online_now 		= get_string("mdlos_online_now", 		  "block_mdlopensim");

		include(MDLOPNSM_BLK_PATH."/html/show_db.html");
	}


}

?>
