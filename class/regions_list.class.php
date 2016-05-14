<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  RegionsList
{
	var $icon = array();
	var $pnum = array();
	var $db_data = array();

	var $action_url;
	var $search_url;
	var $avatar_url;

	var $course_id;
	var $user_id;
	var $url_param = '';

	var $isAvatarMax = false;
	var $use_sloodle = false;

	var $show_all  = false;
	var $hasPermit = false;
	var $isGuest   = true;

	var $Cpstart = 0;
	var $Cplimit = 25;
	var $order   = '';
	var $order_desc = 0;

	var $desc_name = 0;
	var $desc_estate = 0;
	var $desc_x = 0;
	var $desc_y = 0;
	var $desc_ip = 0;
	var $desc_estateid = 0;
	var $desc_owner = 0;

	var $pstart;
	var $plimit;
	var $number;
	var $sitemax;
	var $sitestart;
	var $sql_condition = '';



	function  RegionsList($course_id, $show_all, $userid=0)
	{
		global $CFG, $USER;

		$this->isGuest   = isguestuser();
		$this->hasPermit = hasModlosPermit($course_id);
		$this->course_id = $course_id;
		$this->user_id   = $userid;
		$this->show_all	 = $show_all;
		if (!$show_all and $userid==0) $this->user_id = $USER->id;

		$this->url_param = '?dmmy_param=';
		if ($course_id>0) $this->url_param .= '&course='.$course_id;

		if ($show_all) $this->action_url = CMS_MODULE_URL.'/actions/regions_list.php'.$this->url_param;
		else           $this->action_url = CMS_MODULE_URL.'/actions/personal_regions.php'.$this->url_param.'&userid='.$userid;
		$this->avatar_url   = $CFG->wwwroot.'/user/view.php'.$this->url_param;

		$this->use_sloodle = $CFG->modlos_cooperate_sloodle;
		$avatars_num = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $avatars_num>=$max_avatars) $this->isAvatarMax = true;
	}



	function  set_condition() 
	{
		global $CFG;

		$this->order = optional_param('order', '', PARAM_TEXT);
		$this->order_desc = optional_param('desc', '0', PARAM_INT);
		if (!isAlphabetNumeric($this->order)) $this->order = '';

		$db_ver = opensim_get_db_version(); 
		if ($db_ver==null) {
			$course_url = $CFG->wwwroot;
			if ($this->course_id>0) $course_url .= '/course/view.php?id='.$this->course_id;
			print_error('modlos_db_connect_error', 'block_modlos', $course_url);
		}

		$sql_order = '';
		if ($this->order=='name') {
	 		$sql_order = ' ORDER BY regionName';
			if (!$this->order_desc) $this->desc_name = 1;
		}
		else if ($this->order=='estate') {
			$sql_order = ' ORDER BY EstateName';
			if (!$this->order_desc) $this->desc_estate = 1;
		}
		else if ($this->order=='x')	{
			$sql_order = ' ORDER BY locX';
			if (!$this->order_desc) $this->desc_x = 1;
		}
		else if ($this->order=='y')	{
			$sql_order = ' ORDER BY locY';
			if (!$this->order_desc) $this->desc_y = 1;
		}
		else if ($this->order=='ip') {
			$sql_order = ' ORDER BY serverIP';
			if (!$this->order_desc) $this->desc_ip = 1;
		}
		else if ($this->order=='estid') {
			$sql_order = ' ORDER BY estate_map.EstateID';
			if (!$this->order_desc) $this->desc_estateid = 1;
		}
		else if ($this->order=='owner') {
			if ($db_ver==OPENSIM_V06) $sql_order = ' ORDER BY username';
			else				      $sql_order = ' ORDER BY FirstName';
			if (!$this->order_desc) $this->desc_owner = 1;
		}
		//
		if ($sql_order!='') {
			if ($this->order_desc) {
				$sql_order .= ' DESC';
			}
			else {
				$sql_order .= ' ASC';
			}
		}

		$this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
		$this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);

		// SQL Condition
		$sql_limit = "LIMIT $this->pstart, $this->plimit";
		$this->sql_condition = " $sql_order $sql_limit";

		return true;
	}



	function  execute()
	{
		$where = '';

		if ($this->show_all) {
			$this->number = opensim_get_regions_num();
		}
		else {
			$users = modlos_get_avatars($this->user_id);
			$i = 0;
			foreach($users as $user) {
				$uuid  = $user['UUID'];
				if ($i==0) $where = " WHERE owner_uuid='$uuid' ";
				else       $where.= " or owner_uuid='$uuid' ";
				$i++;
			}
			if ($where!='') $this->number = opensim_get_regions_num($where);
		}
		if (!$this->number) return false;

		// auto synchro
		modlos_sync_opensimdb();
		if ($this->use_sloodle) modlos_sync_sloodle_users();

		// Voice Mode
		$voice_mode[0] = get_string('modlos_voice_inactive_chnl', 'block_modlos');
		$voice_mode[1] = get_string('modlos_voice_private_chnl',  'block_modlos');
		$voice_mode[2] = get_string('modlos_voice_percel_chnl',   'block_modlos');

		//
		$colum = 0;
		$regions = opensim_get_regions_infos($where.$this->sql_condition);

		foreach($regions as $region) {
			$this->db_data[$colum] = $region;
			$this->db_data[$colum]['num']   = $colum;
			$this->db_data[$colum]['locX']  = $this->db_data[$colum]['locX']/256;
			$this->db_data[$colum]['locY']  = $this->db_data[$colum]['locY']/256;
			$vcmode = opensim_get_voice_mode($region['UUID']);
			$this->db_data[$colum]['voice'] = $voice_mode[$vcmode];

			$this->db_data[$colum]['uuid']	  = str_replace('-', '',  $region['UUID']);
//			$this->db_data[$colum]['ow_uuid'] = str_replace('-', '',  $region['owner_uuid']);
//			$this->db_data[$colum]['ip_name'] = str_replace('.', 'X', $region['serverIP2']);

			if ($region['est_fullname']!=null) {
				$this->db_data[$colum]['owner_name'] = $region['est_fullname'];
				$this->db_data[$colum]['owner_uuid'] = $region['estate_owner'];
			}
			else {
				$this->db_data[$colum]['owner_name'] = $region['rgn_fullname'];
				$this->db_data[$colum]['owner_uuid'] = $region['owner_uuid'];
			}

			$colum++;
		}

		//
		$this->sitemax = ceil ($this->number/$this->plimit);
		//$this->sitestart = round($this->pstart/$this->plimit, 0) + 1;
		$this->sitestart = floor(($this->pstart+$this->plimit-1)/$this->plimit) + 1;

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

		$this->icon[3] = $this->icon[4] = $this->icon[5] = $this->icon[6] = 'icon_limit_off';
		if ($this->plimit != 10)  $this->icon[3] = 'icon_limit_10_on'; 
		if ($this->plimit != 25)  $this->icon[4] = 'icon_limit_25_on';
		if ($this->plimit != 50)  $this->icon[5] = 'icon_limit_50_on';
		if ($this->plimit != 100) $this->icon[6] = 'icon_limit_100_on';

		return true;
	}



	function  print_page() 
	{
		global $CFG, $USER;

		$grid_name	= $CFG->modlos_grid_name;
		$content	= $CFG->modlos_regions_content;

		$url_param 	= $this->url_param;
		$order_amp 	= "&amp;order=$this->order&amp;desc=$this->order_desc";
		$course_amp = "&amp;course=$this->course_id";
		$pstart_amp	= "&amp;pstart=$this->pstart";
		$plimit_amp	= "&amp;plimit=$this->plimit";
		$pstart_	= '&amp;pstart=';
		$plimit_	= '&amp;plimit=';

		$desc_name  = "&amp;desc=$this->desc_name";
		$desc_x 	= "&amp;desc=$this->desc_x";
		$desc_y 	= "&amp;desc=$this->desc_y";
		$desc_ip 	= "&amp;desc=$this->desc_ip";
		$desc_owner = "&amp;desc=$this->desc_owner";

		$location_x		 = get_string('modlos_location_x',	   'block_modlos');
		$location_y	  	 = get_string('modlos_location_y',	   'block_modlos');
		$region_name	 = get_string('modlos_region_name',	   'block_modlos');
		$estate_name	 = get_string('modlos_estate',         'block_modlos');
		$estate_owner	 = get_string('modlos_estate_owner',   'block_modlos');
		$owner_ttl	 	 = get_string('modlos_owner',   	   'block_modlos');
		$ip_address	  	 = get_string('modlos_ipaddr',		   'block_modlos');
		$server_name	 = get_string('modlos_server',		   'block_modlos');
		$regions_found   = get_string('modlos_regions_found',  'block_modlos');
		$page_num		 = get_string('modlos_page',		   'block_modlos');
		$page_num_of	 = get_string('modlos_page_of',		   'block_modlos');
		$voice_chat_mode = get_string('modlos_voice_chat_mode','block_modlos');

        if ($this->show_all) {
			$regions_list = get_string('modlos_regions_list', 'block_modlos');
        }
        else if ($this->user_id==$USER->id) {
			$regions_list = get_string('modlos_my_regions', 'block_modlos');
        }
        else {
            $userinfo = get_userinfo_by_id($this->user_id);
            $username = get_display_username($userinfo->firstname, $userinfo->lastname);
            $userurl  = '<a href="'.$this->avatar_url.'&id='.$this->user_id.'" target="_blank">'.$username.'</a>';
			$regions_list = get_string('modlos_personal_regions', 'block_modlos', $userurl);
        }

		include(CMS_MODULE_PATH.'/html/regions.html');
	}
}

