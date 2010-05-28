<?php
//////////////////////////////////////////////////
//
// ?action=home または ?action= の場合に読み出される．
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  ShowHome
{
	var	$grid_status;
	var $now_online;
	var $lastmonth_online;
	var $user_count;
	var $region_count;


	function  ShowHome($course_id) 
	{
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
		$content         	= $CFG->mdlopnsm_home_content;

		$home_page 			= get_string("mdlos_home_page", 		  "block_mdlopensim");
		$online_ttl 		= get_string("mdlos_online_ttl", 		  "block_mdlopensim");
		$offline_ttl 		= get_string("mdlos_offline_ttl", 		  "block_mdlopensim");
		$total_users 		= get_string("mdlos_total_users", 		  "block_mdlopensim");
		$total_regions 		= get_string("mdlos_total_regions", 	  "block_mdlopensim");
		$visitors_last30days= get_string("mdlos_visitors_last30days", "block_mdlopensim");
		$online_now 		= get_string("mdlos_online_now", 		  "block_mdlopensim");

		include(CMS_MODULE_PATH."/html/show_home.html");
	}

}

?>
