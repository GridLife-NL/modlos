<?php

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");



class  RegionsList
{
	var $icon = array();
	var $pnum = array();
	var $action;
	var $action_url;

	var $isAdmin = false;
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


	function  set_condition() 
	{

		$this->isAdmin = isadmin();
		$this->isGuest = isguest();

		$this->order = optional_param('order', '', PARAM_TEXT);
		if ($this->order!="" and !preg_match("/^[a-z]+$/", $this->order)) $this->order = "";

		$db_ver = opensim_get_dbversion(); 
		if ($db_ver=="0.0.0") {
			error(get_string('courseidwrong', 'block_autoattend'));
			//$this->mActionForm->addErrorMessage(_MD_XPNSM_DB_CONNECT_ERROR);
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

		$this->action = "regions_list";
		$this->action_url = MDLOPNSM_BLK_URL."/actions/".$this->action.".php";

		return;
	}



	function  execute()
	{
		$this->number    = opensim_get_regionnum();
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

		//
		$regions = opensim_get_regioninfos($this->sql_condition);
		$colum  = 0;
		foreach($regions as $region) {
			$this->db_data[$colum] = $region;
			$this->db_data[$colum]['num']  = $colum;
			$this->db_data[$colum]['locX'] = $this->db_data[$colum]['locX']/256;
			$this->db_data[$colum]['locY'] = $this->db_data[$colum]['locY']/256;
			$colum++;
		}

	}



	function  print_page() 
	{
		global $CFG;

		$grid_name = $CFG->mdlopnsm_grid_name;
		$content   = $CFG->mdlopnsm_regions_content;

        $module_url =  _OPENSIM_MODULE_URL;
/*
		$this->set_condition();
		$this->execute();

        $isAdmin = $this->isAdmin);
        $render->setAttribute('isGuest',    $this->isGuest);

        $render->setAttribute('db_data',    $this->db_data);
        $render->setAttribute('action',     $this->action);
        $render->setAttribute('action_url', $this->action_url);

        $render->setAttribute('icon',       $this->icon);
        $render->setAttribute('sitestart',  $this->sitestart);
        $render->setAttribute('sitemax',    $this->sitemax);

        $render->setAttribute('pstart',     $this->pstart);
        $render->setAttribute('plimit',     $this->plimit);
        $render->setAttribute('pnum',       $this->pnum);
        $render->setAttribute('number',     $this->number);
        $render->setAttribute('order',      $this->order);
*/

		$regions_list = get_string("mdlos_regions_list","block_mdlopensim");
		$region_name  = get_string("mdlos_region_name", "block_mdlopensim");
		$location_x   = get_string("mdlos_location_X",  "block_mdlopensim");
		$location_y   = get_string("mdlos_location_Y",  "block_mdlopensim");
		$estate_owner = get_string("mdlos_estate_owner","block_mdlopensim");
		$estate_id    = get_string("mdlos_estate_id",  	"block_mdlopensim");
		$region_owner = get_string("mdlos_region_owner","block_mdlopensim");
/*
$smarty.const._MD_XPNSM_IPADDR
$smarty.const._MD_XPNSM_REGIONS_FOUND
$smarty.const._MD_XPNSM_PAGE
$smarty.const._MD_XPNSM_PAGE_OF
*/

		include(MDLOPNSM_BLK_PATH."/html/regions.html");
	}
}

?>
