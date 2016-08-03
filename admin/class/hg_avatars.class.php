<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/moodle.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));



class  HgAvatars
{
	var $db_data 	= array();
	var $icon 		= array();
	var $pnum 		= array();

	var $action_url;
	var $currency_url;

	var $url_params   = '';

	var $course_id    = 0;

	var $isAvatarMax  = false;
	var	$use_currency = false;

	var $hasPermit 	= false;
	var $isGuest   	= true;

	// Page Control
	var $pstart    = 0;
	var $plimit    = 25;
	var $number    = 25;
	var $sitemax;
	var	$sitestart;

	var $sql_limit = '';
	var $sql_order = '';



	function  __construct($course_id)
	{
		global $CFG, $USER;

		// for Guest
		$this->isGuest = isguestuser();
		if ($this->isGuest) {
			print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
		}

		$this->course_id	= $course_id;
		$this->hasPermit	= hasModlosPermit($course_id);
		$this->use_currency = $CFG->modlos_use_currency_server;

		$this->url_params   = '?course='.$course_id;
		$this->action_url   = CMS_MODULE_URL.'/admin/actions/hg_avatars.php'.  $this->url_params;
		$this->currency_url = CMS_MODULE_URL.'/actions/currency_log.php'.$this->url_params;

		//
		$my_avatars = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $my_avatars>=$max_avatars) $this->isAvatarMax = true;
	}


	// アバターの検索条件
	function  set_condition() 
	{
		global $CFG, $USER;

		$this->plimit = optional_param('plimit', 25, PARAM_INT);
		$this->sql_limit = " $this->pstart, $this->plimit ";

		$this->sql_order = ' Login DESC';

		return true;
	}


	function  execute()
	{
		global $CFG, $USER;

		// auto synchro
		modlos_sync_opensimdb();

		////////////////////////////////////////////////////////////////////
		// Read Data from DB
		$users = array();
		$this->number = $this->plimit;

		$infos = opensim_get_hg_avatars_infos('', $this->sql_order, $this->sql_limit);
		/*
    	$infos[$UUID]['UUID']      ... UUID
    	$infos[$UUID]['firstname'] ... first name
    	$infos[$UUID]['lastname']  ... lasti name
    	$infos[$UUID]['created']   ... always 0
    	$infos[$UUID]['lastlogin'] ... lastlogin time
    	$infos[$UUID]['hgURI']     ... 
    	$infos[$UUID]['hgName
		*/
		//
		$num = 0;
		foreach($infos as $user) {
			//
			$lastlogin = $user['lastlogin'];
			if ($lastlogin==null or $lastlogin=='' or $lastlogin=='0') {
				$user['lastin'] = ' - ';
			}
			else {
				$user['lastin'] = date(DATE_FORMAT, $lastlogin);
			}

			$UUID = $user['UUID'];
			$online = opensim_get_avatar_online($UUID);
			$user['online'] = $online['online'];
			if ($online['online']) {
				$user['region']    = $online['regionName'];
				$user['region_id'] = $online['regionUUID'];
				//$user['lastin']    = date(DATE_FORMAT, $online['timeStamp']);
			}

			$this->db_data[$num] = $user; 
			$num++;
		}
		unset($infos);


		////////////////////////////////////////////////////////////////////
		// Paging
		$this->sitemax   = ceil ($this->number/$this->plimit);
		$this->sitestart = floor(($this->pstart+$this->plimit-1)/$this->plimit) + 1;
		if ($this->sitemax==0) $this->sitemax = 1;

		// back more and back one
		if ($this->pstart==0) {
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
		if (($this->number-$this->plimit) < 0) {
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

		$grid_name 		= $CFG->modlos_grid_name;
		$content   		= $CFG->modlos_avatars_content;
		$userinfo		= $CFG->modlos_userinfo_link;
		$money_unit 	= $CFG->modlos_currency_unit;
		$date_format	= DATE_FORMAT;

		$has_permit		= $this->hasPermit;
		$avatar_max		= $this->isAvatarMax;
		$use_currency	= $this->use_currency;
		$url_params		= $this->url_params;

		$plimit_amp		= "&amp;plimit=$this->plimit";
		$plimit_		= '&amp;plimit=';
		$action_url		= $this->action_url;

		$number_ttl		= get_string('modlos_num',			 'block_modlos');
		$edit_ttl		= get_string('modlos_edit',			 'block_modlos');
		$show_ttl		= get_string('modlos_show',			 'block_modlos');
		$editable_ttl	= get_string('modlos_edit_ttl',		 'block_modlos');
		$lastlogin_ttl	= get_string('modlos_login_time',	 'block_modlos');
		$status_ttl		= get_string('modlos_status',		 'block_modlos');
		$crntregion_ttl	= get_string('modlos_crntregion',	 'block_modlos');
		$owner_ttl		= get_string('modlos_owner',		 'block_modlos');
		$get_owner_ttl	= get_string('modlos_get_owner_ttl', 'block_modlos');
		$firstname_ttl	= get_string('modlos_firstname', 	 'block_modlos');
		$lastname_ttl 	= get_string('modlos_lastname', 	 'block_modlos');
		$avatarname_ttl = get_string('modlos_avatar_name', 	 'block_modlos');
		$not_syncdb_ttl = get_string('modlos_not_syncdb',	 'block_modlos');
		$online_ttl	 	= get_string('modlos_online_ttl',	 'block_modlos');
		$active_ttl		= get_string('modlos_active',		 'block_modlos');
		$inactive_ttl	= get_string('modlos_inactive',		 'block_modlos');
		$reset_ttl		= get_string('modlos_reset_ttl',	 'block_modlos');
		$find_owner_ttl	= get_string('modlos_find_owner_ttl','block_modlos');
		$unknown_status	= get_string('modlos_unknown_status','block_modlos');
		$page_num		= get_string('modlos_page',			 'block_modlos');
		$page_num_of	= get_string('modlos_page_of',		 'block_modlos');
		$user_search	= get_string('modlos_avatar_search', 'block_modlos');
		$users_found  	= get_string('modlos_avatars_found', 'block_modlos');
		$sloodle_ttl  	= get_string('modlos_sloodle_short', 'block_modlos');
		$currency_ttl  	= get_string('modlos_currency_ttl',  'block_modlos');

		$avarars_list_url = CMS_MODULE_URL.'/admin/actions/hg_avatars.php'.$this->url_params;

		$hg_avatars = "AAAAAAAAAAAA";//get_string('modlos_hg_avatars', 'block_modlos');

		include(CMS_MODULE_PATH.'/admin/html/hg_avatars.html');
	}
}
