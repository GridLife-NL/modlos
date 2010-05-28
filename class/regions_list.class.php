<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  RegionsList
{
	var $icon = array();
	var $pnum = array();
	var $action;
	var $action_url;
	var $course_id = 0;

	var $hasPermit = false;
	var $isGuest = true;
	var $db_ver  = "";

	var $Cpstart = 0;
	var $Cplimit = 25;
	var $order   = "";
	var $pstart;
	var $plimit;
	var $number;
	var $sitemax;
	var $sitestart;
	var $sql_condition = "";



	function  RegionsList($course_id)
	{
		$this->course_id  = $course_id;
		$this->isGuest    = isguest();
		$this->hasPermit  = hasPermit($course_id);

		$this->action 	  = "regions_list.php";
		$this->action_url = CMS_MODULE_URL."/actions/".$this->action;
	}



	function  set_condition() 
	{
		$this->order = optional_param('order', '', PARAM_TEXT);
		if (!isAlphabetNumeric($this->order)) $this->order = "";

		$db_ver = opensim_get_db_version(); 
		if ($db_ver=="0.0") {
			error('<h4>'.get_string('mdlos_db_connect_error', 'block_mdlopensim').'</h4>');
		}

		if ($this->order=="name")       $sql_order = " ORDER BY regionName ASC";
		else if ($this->order=="x")     $sql_order = " ORDER BY locX ASC";
		else if ($this->order=="y")     $sql_order = " ORDER BY locY ASC";
		else if ($this->order=="ip")    $sql_order = " ORDER BY serverIP ASC";
		else if ($this->order=="estid") $sql_order = " ORDER BY estate_map.EstateID ASC";
		else if ($this->order=="owner") {
			if ($db_ver=="0.6") $sql_order = " ORDER BY username, lastname ASC";
			else                $sql_order = " ORDER BY FirstName,LastName ASC";
		}

		$this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
		$this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);

		// SQL Condition
		$sql_limit = "LIMIT $this->pstart, $this->plimit";
		$this->sql_condition = " $sql_order $sql_limit";

		return;
	}



	function  execute()
	{
		$this->number    = opensim_get_regions_num();
		$this->sitemax   = ceil ($this->number/$this->plimit);
		$this->sitestart = round($this->pstart/$this->plimit, 0) + 1;
		if ($this->sitemax==0) $this->sitemax = 1; 

		// back more and back one
		if (0==$this->pstart) {
			$this->icon[0] = 'off';
			$this->pnum[0] = 0;
		}
		else {
			$this->icon[0] = 'on';
			$this->pnum[0] = $this->pstart - $this->plimit;
			if ($this->pnum[0]<0) $this->pnum[0] = 0;
		}

		// forward one
		if ($this->number <= ($this->pstart + $this->plimit)) {
			$this->icon[1] = 'off'; 
			$this->pnum[1] = 0; 
		}
		else {
			$this->icon[1] = 'on'; 
			$this->pnum[1] = $this->pstart + $this->plimit;
		}

		// forward more
		if (0 > ($this->number - $this->plimit)) {
			$this->icon[2] = 'off';
			$this->pnum[2] = 0;
		}
		else {
			$this->icon[2] = 'on';
			$this->pnum[2] = $this->number - $this->plimit;
		}

		$this->icon[3] = $this->icon[4] = $this->icon[5] = $this->icon[6] = "icon_limit_off";
		if ($this->plimit != 10)  $this->icon[3] = "icon_limit_10_on"; 
		if ($this->plimit != 25)  $this->icon[4] = "icon_limit_25_on";
		if ($this->plimit != 50)  $this->icon[5] = "icon_limit_50_on";
		if ($this->plimit != 100) $this->icon[6] = "icon_limit_100_on";


		$voice_mode[0] = $regions_list_ttl= get_string("mdlos_voice_inactive_chnl", "block_mdlopensim");
		$voice_mode[1] = $regions_list_ttl= get_string("mdlos_voice_private_chnl",  "block_mdlopensim");
		$voice_mode[2] = $regions_list_ttl= get_string("mdlos_voice_percel_chnl",   "block_mdlopensim");

		//
		$regions = opensim_get_regions_infos($this->sql_condition);
		$colum = 0;
		foreach($regions as $region) {
			$this->db_data[$colum] = $region;
			$this->db_data[$colum]['num']   = $colum;
			$this->db_data[$colum]['locX']  = $this->db_data[$colum]['locX']/256;
			$this->db_data[$colum]['locY']  = $this->db_data[$colum]['locY']/256;
			$vcmode = opensim_get_voice_mode($region['UUID']);
			$this->db_data[$colum]['voice'] = $voice_mode[$vcmode];
			$colum++;
		}

	}



	function  print_page() 
	{
		global $CFG;

		$this->set_condition();
		$this->execute();

		$grid_name       = $CFG->mdlopnsm_grid_name;
		$content         = $CFG->mdlopnsm_regions_content;

		$course_param	 = "";
		if ($this->course_id>0) $course_param = "&amp;course=$this->course_id";

		$order_param	 = "?order=$this->order";
		$pstart_param	 = "&amp;pstart=$this->pstart";
		$plimit_param	 = "&amp;plimit=$this->plimit";
		$pstart_		 = "&amp;pstart=";
		$plimit_		 = "&amp;plimit=";

		$regions_list_ttl= get_string("mdlos_regions_list",   "block_mdlopensim");
		$location_x    	 = get_string("mdlos_location_x",     "block_mdlopensim");
		$location_y      = get_string("mdlos_location_y",     "block_mdlopensim");
		$region_name     = get_string("mdlos_region_name",    "block_mdlopensim");
		$estate_owner    = get_string("mdlos_estate_owner",   "block_mdlopensim");
		$ip_address      = get_string("mdlos_ipaddr",		  "block_mdlopensim");
		$regions_found   = get_string("mdlos_regions_found",  "block_mdlopensim");
		$page_num	     = get_string("mdlos_page",		      "block_mdlopensim");
		$page_num_of     = get_string("mdlos_page_of",	      "block_mdlopensim");
		$voice_chat_mode = get_string("mdlos_voice_chat_mode","block_mdlopensim");
		//$region_owner = get_string("mdlos_region_owner", "block_mdlopensim");
		//$estate_id    = get_string("mdlos_estate_id",    "block_mdlopensim");

		include(CMS_MODULE_PATH."/html/regions.html");
	}
}

?>
