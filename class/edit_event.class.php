<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  EditEvent
{
	var $hasPermit = false;
	var $isGuest   = true;
	var $pg_only   = true;
	var $userid	   = 0;
	var $use_utc_time;

	var $action_url;
	var $make_event_url;
	var $edit_event_url;
	var $del_event_url;

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



	function  EditEvent($course_id)
	{
		global $CFG, $USER;

		require_login($course_id);

		// for Guest
		$this->isGuest = isguest();
		if ($this->isGuest) {
			error(get_string('modlos_access_forbidden', 'block_modlos'), CMS_MODULE_URL);
		}

		$this->hasPermit = hasModlosPermit($course_id);
		$this->course_id = $course_id;
		$this->userid	 = $USER->id;

		$this->date_frmt = $CFG->modlos_date_format;
		$this->pg_only   = $CFG->modlos_pg_only;

		$this->use_utc_time = $CFG->modlos_use_utc_time;
		if ($this->use_utc_time) date_default_timezone_set('UTC');
   
		$this->action_url	  = CMS_MODULE_URL.'/actions/events_list.php';
		$this->make_event_url = CMS_MODULE_URL.'/actions/edit_event.php';
		$this->edit_event_url = CMS_MODULE_URL.'/actions/edit_event.php?eventid=';
		$this->del_event_url  = CMS_MODULE_URL.'/actions/delete_event.php';

		$avatars_num = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $avatars_num>=$max_avatars) $this->isAvatarMax = true;

		if ($course_id>0) $this->course_amp = '&amp;course='.$course_id;
	}



	function  set_condition() 
	{
		$this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
		$this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);

		return true;
	}



	function  execute()
	{
		if ($this->hasPermit) {
			$this->number = modlos_get_events_num(0, $this->pg_only);
		}
		else {
			$this->number = modlos_get_events_num($this->userid, $this->pg_only);
		}

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


		//
		if ($this->hasPermit) {
			$events = modlos_get_events(0, $this->pstart, $this->plimit, $this->pg_only);
		}
		else {
			$events = modlos_get_events($this->userid, $this->pstart, $this->plimit, $this->pg_only);
		}
   
		$colum = 0;
		foreach($events as $event) {
			if (!$this->pg_only or $event['eventflags']==0) {
				$avatar_name = opensim_get_avatar_name($event['creatoruuid']);
				$this->db_data[$colum] = $event;
				$this->db_data[$colum]['num']	  = $colum;
				$this->db_data[$colum]['time']	  = date($this->date_frmt, $event['dateUTC']);
				$this->db_data[$colum]['creator'] = $avatar_name['fullname'];
   
				if ($event['eventflags']==0) {
					$this->db_data[$colum]['type'] = "title='PG Event' src=./images/events/blue_star.gif";
				}
				else {
					$this->db_data[$colum]['type'] = "title='Mature Event' src=./images/events/pink_star.gif";
				}
			}
			$colum++;
		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name		= $CFG->modlos_grid_name;
		$module_url		= CMS_MODULE_URL;

		$course_amp 	= $this->course_amp;
		$pstart_amp	 	= "&amp;pstart=$this->pstart";
		$plimit_amp	 	= "&amp;plimit=$this->plimit";
		$pstart_		= '&amp;pstart=';
		$plimit_		= '&amp;plimit=';

		$events_list_ttl 	= get_string('modlos_events_list',   	'block_modlos');
		$events_make_link	= get_string('modlos_events_make_link', 'block_modlos');
		$events_click_here	= get_string('modlos_events_click_here','block_modlos');

		$events_date	= get_string('modlos_events_date',  'block_modlos');
		$events_type	= get_string('modlos_events_type',  'block_modlos');
		$events_name	= get_string('modlos_events_name',  'block_modlos');
		$events_owner	= get_string('modlos_events_owner', 'block_modlos');
		$events_found	= get_string('modlos_events_found', 'block_modlos');

		$page_num		= get_string('modlos_page',		   	'block_modlos');
		$page_num_of	= get_string('modlos_page_of',	 	'block_modlos');
		$modlos_edit	= get_string('modlos_edit',		  	'block_modlos');
		$modlos_delete	= get_string('modlos_delete',	   	'block_modlos');

		include(CMS_MODULE_PATH.'/html/edit_event.html');
	}
}

?>
