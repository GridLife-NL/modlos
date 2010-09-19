<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  EventsList
{
	var $hasPermit = false;
	var $isGuest   = true;
	var $pg_only   = true;
	var $userid	   = 0;
	var $use_utc_time;

	var $action;
	var $action_url;

	var $course_id	 = '';
	var $course_amp	 = '';
	var $isAvatarMax = false;

	var $pstart;
	var $plimit;
	var $Cpstart = 0;
	var $Cplimit = 25;

	var $number;
	var $sitemax;
	var $sitestart;

	var $icon 	 = array();
	var $pnum 	 = array();
	var $db_data = array();



	function  EventsList($course_id)
	{
		global $CFG, $USER;

		require_login($course_id);

		$this->isGuest   = isguest();
		$this->hasPermit = hasModlosPermit($course_id);
		$this->course_id = $course_id;
		$this->userid	 = $USER->id;

		if ($isGuest) {
			exit('<h4>guest user is not allowed to access this page!!</h4>');
		}

		$this->date_frmt 	= $CFG->modlos_date_format;
		$this->pg_only   	= $CFG->modlos_pg_only;
		$this->use_utc_time = $CFG->modlos_use_utc_time;
		if ($this->use_utc_time) date_default_timezone_set('UTC');
   

		$this->pstart = $root->mContext->mRequest->getRequest('pstart');
		$this->plimit = $root->mContext->mRequest->getRequest('plimit');
		if ($this->pstart!='' and !preg_match('/^[0-9]+$/', $this->pstart)) $this->pstart = '';
		if ($this->plimit!='' and !preg_match('/^[0-9]+$/', $this->plimit)) $this->plimit = '';
		if ($this->pstart=='') $this->pstart = $this->Cpstart;
		if ($this->plimit=='') $this->plimit = $this->Cplimit;
   
		$this->action = 'index.php?action=events';
		$this->action_url = CMS_MODULE_URL.'/'.$this->action;
		$this->make_event_url = CMS_MODULE_URL.'/index.php?action=edit_event';
		$this->edit_event_url = CMS_MODULE_URL.'/index.php?action=edit_event&amp;eventid=';
		$this->del_event_url  = CMS_MODULE_URL.'/index.php?action=del_event';
   
		return true;








		$this->action 	  = 'events_list.php';
		$this->action_url = CMS_MODULE_URL.'/actions/'.$this->action;

		$avatars_num = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $avatars_num>=$max_avatars) $this->isAvatarMax = true;

		if ($course_id>0) $this->course_amp = '&amp;course='.$course_id;
	}



	function  set_condition() 
	{
		$this->order = optional_param('order', '', PARAM_TEXT);
		if (!isAlphabetNumeric($this->order)) $this->order = '';

		$db_ver = opensim_get_db_version(); 
		if ($db_ver==null) {
			$course_url = $CFG->wwwroot;
			if ($course_id>0) $course_url .= '/course/view.php?id='.$this->course_id;
			error(get_string('modlos_db_connect_error', 'block_modlos'), $course_url);
		}

		$sql_order = '';
		if ($this->order=='name')       $sql_order = ' ORDER BY regionName ASC';
		else if ($this->order=='x')     $sql_order = ' ORDER BY locX ASC';
		else if ($this->order=='y')     $sql_order = ' ORDER BY locY ASC';
		else if ($this->order=='ip')    $sql_order = ' ORDER BY serverIP ASC';
		else if ($this->order=='estid') $sql_order = ' ORDER BY estate_map.EstateID ASC';
		else if ($this->order=='owner') {
			if ($db_ver=='0.6') $sql_order = ' ORDER BY username, lastname ASC';
			else                $sql_order = ' ORDER BY FirstName,LastName ASC';
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
		$this->number    = opensim_get_events_num();
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

		$this->icon[3] = $this->icon[4] = $this->icon[5] = $this->icon[6] = 'icon_limit_off';
		if ($this->plimit != 10)  $this->icon[3] = 'icon_limit_10_on'; 
		if ($this->plimit != 25)  $this->icon[4] = 'icon_limit_25_on';
		if ($this->plimit != 50)  $this->icon[5] = 'icon_limit_50_on';
		if ($this->plimit != 100) $this->icon[6] = 'icon_limit_100_on';


		$voice_mode[0] = $regions_list_ttl = get_string('modlos_voice_inactive_chnl', 'block_modlos');
		$voice_mode[1] = $regions_list_ttl = get_string('modlos_voice_private_chnl',  'block_modlos');
		$voice_mode[2] = $regions_list_ttl = get_string('modlos_voice_percel_chnl',   'block_modlos');


		// auto synchro
		modlos_sync_opensimdb();
		if ($this->use_sloodle) modlos_sync_sloodle_users();

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

			$this->db_data[$colum]['uuid']    = str_replace('-', '',  $region['UUID']);
			$this->db_data[$colum]['ow_uuid'] = str_replace('-', '',  $region['owner_uuid']);
			$this->db_data[$colum]['ip_name'] = str_replace('.', 'X', $region['serverIP']);

			$colum++;
		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name       = $CFG->modlos_grid_name;
		$content         = $CFG->modlos_regions_content;

		$order_param	 = "?order=$this->order";
		$course_amp 	 = $this->course_amp;
		$pstart_amp	 	 = "&amp;pstart=$this->pstart";
		$plimit_amp	 	 = "&amp;plimit=$this->plimit";
		$pstart_		 = '&amp;pstart=';
		$plimit_		 = '&amp;plimit=';

		$regions_list_ttl= get_string('modlos_regions_list',   'block_modlos');
		$location_x    	 = get_string('modlos_location_x',	   'block_modlos');
		$location_y      = get_string('modlos_location_y',	   'block_modlos');
		$region_name     = get_string('modlos_region_name',	   'block_modlos');
		$estate_owner    = get_string('modlos_estate_owner',   'block_modlos');
		$ip_address      = get_string('modlos_ipaddr',		   'block_modlos');
		$regions_found   = get_string('modlos_regions_found',  'block_modlos');
		$page_num	     = get_string('modlos_page',		   'block_modlos');
		$page_num_of     = get_string('modlos_page_of',		   'block_modlos');
		$voice_chat_mode = get_string('modlos_voice_chat_mode','block_modlos');

		include(CMS_MODULE_PATH.'/html/regions.html');
	}
}

?>
