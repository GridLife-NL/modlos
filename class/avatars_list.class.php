<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/moodle.func.php');
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  AvatarsList
{
	var $db_data 	= array();
	var $icon 		= array();
	var $pnum 		= array();

	var $action_url;
	var $edit_url;
	var $owner_url;
	var $search_url;
	var $avatar_url;

	var $course_id  = 0;
	var $url_param  = '';

	var $use_sloodle = false;
	var $isAvatarMax = false;

	var $hasPermit 	= false;
	var $isGuest   	= true;

	// Page Control
	var $Cpstart 	= 0;
	var $Cplimit 	= 25;
	var $firstname 	= '';
	var $lastname  	= '';
	var $order		= '';
	var $pstart;
	var $plimit;
	var $number;
	var $sitemax;
	var $sitestart;

	// SQL
	var $lnk_firstname = '';
	var $lnk_lastname  = '';
	var $sql_countcnd  = '';
	var $sql_condition = '';



	function  AvatarsList($course_id)
	{
		global $CFG, $USER;

		// for Guest
		$this->isGuest = isguestuser();
		if ($this->isGuest) {
			print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
		}

		$this->course_id   = $course_id;
		$this->hasPermit   = hasModlosPermit($course_id);
		$this->course_id   = $course_id;
		$this->use_sloodle = $CFG->modlos_cooperate_sloodle;

		$this->url_param = '?dmmy_param=';	
		if ($course_id>0) $this->url_param .= '&amp;course='.$course_id;

		$this->action_url  = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_param;
		$this->edit_url	   = CMS_MODULE_URL.'/actions/edit_avatar.php'. $this->url_param;
		$this->owner_url   = CMS_MODULE_URL.'/actions/owner_avatar.php'.$this->url_param;
		$this->search_url  = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_param.'&amp;pstart=0';
		$this->avatar_url  = $CFG->wwwroot.'/user/view.php'.$this->url_param;

		$avatars_num = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $avatars_num>=$max_avatars) $this->isAvatarMax = true;
	}




	function  set_condition() 
	{
		$db_ver = opensim_get_db_version();
		if ($db_ver==null) {
			$course_url = $CFG->wwwroot;
			if ($ithis->course_id>0) $course_url.= '/course/view.php?id='.$course_id;
			print_error('modlos_db_connect_error', 'block_modlos', $course_url);
		}

		// Post Check
		if (data_submitted()) {
			if (!confirm_sesskey()) {
				print_error('modlos_sesskey_error', 'block_modlos', $this->action_url);
			}
		}

		// firstname & lastname
		$this->firstname = optional_param('firstname', '', PARAM_TEXT);
		$this->lastname  = optional_param('lastname',  '', PARAM_TEXT);
		if (!isAlphabetNumeric($this->firstname)) $this->firstname = '';
		if (!isAlphabetNumeric($this->lastname))  $this->lastname  = '';

		$this->order = optional_param('order', '', PARAM_ALPHA);

		$sql_validuser = $sql_firstname = $sql_lastname = '';
		if ($this->firstname=='' and $this->lastname=='') {
			if ($db_ver=='0.6') $sql_validuser = "username!=''";
			else				$sql_validuser = "FirstName!=''";
		}
		else {
			if ($this->firstname!='') { 
				if ($db_ver=='0.6') $sql_firstname = "username  LIKE '$this->firstname'";
				else				$sql_firstname = "FirstName LIKE '$this->firstname'";
				$this->lnk_firstname = "&amp;firstname=$this->firstname";
			}
			if ($this->lastname!='') { 
				if ($this->firstname!='') $sql_lastname = "and lastname LIKE '$this->lastname'";
				else					  $sql_lastname = "lastname LIKE '$this->lastname'";
				$this->lnk_lastname  = "&amp;lastname=$this->lastname";
			}
		}

        // 0.7: PrincipalID, FirstName, LastName, Created, Login,     homeRegionID 
        // 0.6: users.UUID,  username,  lastname, created, lastLogin, regions.uuid 
		$sql_order = $this->order;
		if ($this->order!='') {
			if ($db_ver=='0.6') {
				if ($sql_order=='firstname')  $sql_order = 'username';
				else if ($sql_order=='login') $sql_order = 'lastlogin';
			}
		}

		// pstart & plimit
		$this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
		$this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);
		$sql_limit = "LIMIT $this->pstart, $this->plimit";

		// Order
		if ($sql_order=='login') $sql_order = 'ORDER BY '.$sql_order.' DESC ';
		else if ($sql_order!='') $sql_order = 'ORDER BY '.$sql_order.' ASC ';
		else					 $sql_order = 'ORDER BY created ASC ';

		// SQL Condition
		$this->sql_countcnd  = " WHERE $sql_validuser $sql_firstname $sql_lastname";
		$this->sql_condition = " WHERE $sql_validuser $sql_firstname $sql_lastname $sql_order $sql_limit";

		return true;
	}



	function  execute()
	{
		global $CFG, $USER;

		$dummy = opensim_get_avatars_infos($this->sql_countcnd);
		if (is_array($dummy)) $this->number = count($dummy);
		else $this->number = 0;

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


		// auto synchro
		modlos_sync_opensimdb();
		if ($this->use_sloodle) modlos_sync_sloodle_users();

		// OpenSim DB
		$users = opensim_get_avatars_infos($this->sql_condition);

		$colum = 0;
		foreach($users as $user) {
			$this->db_data[$colum]				= $user;
			$this->db_data[$colum]['num']		= $colum;
			$this->db_data[$colum]['ownername']	= ' - ';
			$this->db_data[$colum]['region_id']	= $user['hmregion'];
			$this->db_data[$colum]['region']	= opensim_get_region_name($user['hmregion']);
			$this->db_data[$colum]['state']		= AVATAR_STATE_NOSTATE;
			$this->db_data[$colum]['editable']	= AVATAR_NOT_EDITABLE;

			$created = $this->db_data[$colum]['created'];
			if ($created==null or $created=='' or $created=='0') {
				$this->db_data[$colum]['born'] = ' - ';
			}
			else {
				$this->db_data[$colum]['born'] = date(DATE_FORMAT, $created);
			}

			$lastlogin = $this->db_data[$colum]['lastlogin'];
			if ($lastlogin==null or $lastlogin=='' or $lastlogin=='0') {
				$this->db_data[$colum]['lastin'] = ' - ';
			}
			else {
				$this->db_data[$colum]['lastin'] = date(DATE_FORMAT, $lastlogin);
			}

			// Agent Online Info
			$UUID = $this->db_data[$colum]['UUID'];
			$online = opensim_get_avatar_online($UUID);
			$this->db_data[$colum]['online'] = $online['online'];
			if ($online['online']) {
				$this->db_data[$colum]['region_id']	= $online['region_id'];
				$this->db_data[$colum]['region'] 	= $online['region_name'];
			}

			$this->db_data[$colum]['uuid']	  = str_replace('-', '', $UUID);
			$this->db_data[$colum]['rg_uuid'] = str_replace('-', '', $this->db_data[$colum]['region_id']);


			// serach Moodle, Modlos and Sloodle DB
			$uid = -1;
			$avatardata = modlos_get_avatar_info($UUID, $this->use_sloodle);

			// synchro
			if ($avatardata==null) {
				modlos_sync_opensimdb(false);
				if ($this->use_sloodle) modlos_sync_sloodle_users(false);
				$avatardata = modlos_get_avatar_info($UUID, $this->use_sloodle);
			}

			if ($avatardata!=null) {
				if (!((int)$avatardata['state']&AVATAR_STATE_SYNCDB)) {
					modlos_sync_opensimdb(false);
				}

				$uid = $avatardata['uid'];
				$this->db_data[$colum]['state'] = (int)$avatardata['state'];

				if ($uid>0) {
					$user_info = get_userinfo_by_id($uid);
					if ($user_info!=null) {
						$this->db_data[$colum]['ownername'] = get_display_username($user_info->firstname, $user_info->lastname);
					}
				}
			}

			$this->db_data[$colum]['uid'] = $uid;


			if ($this->hasPermit or $USER->id==$uid) {
				$this->db_data[$colum]['editable'] = AVATAR_EDITABLE;
			}
			elseif ($uid==0) {
				if (!$this->isAvatarMax) {
					$this->db_data[$colum]['editable'] = AVATAR_OWNER_EDITABLE;
				}
			}

			$colum++;
		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name 		= $CFG->modlos_grid_name;
		$content   		= $CFG->modlos_avatars_content;
		$userinfo		= $CFG->modlos_userinfo_link;
		$date_format	= DATE_FORMAT;

		$lnk_firstname	= $this->lnk_firstname;
		$lnk_lastname	= $this->lnk_lastname;
		$url_param		= $this->url_param;
		$plimit_amp		= "&amp;plimit=$this->plimit";
		$pstart_amp		= "&amp;pstart=$this->pstart";
		$order_amp		= "&amp;order=$this->order";
		$plimit_		= '&amp;plimit=';
		$pstart_		= '&amp;pstart=';
		$order_			= '&amp;order=';

		$avatars_list	= get_string('modlos_avatars_list',  'block_modlos');
		$number_ttl		= get_string('modlos_num',			 'block_modlos');
		$edit_ttl		= get_string('modlos_edit',			 'block_modlos');
		$editable_ttl	= get_string('modlos_edit_ttl',		 'block_modlos');
		$lastlogin_ttl	= get_string('modlos_lastlogin',	 'block_modlos');
		$status_ttl		= get_string('modlos_status',		 'block_modlos');
		$crntregion_ttl	= get_string('modlos_crntregion',	 'block_modlos');
		$owner_ttl		= get_string('modlos_owner',		 'block_modlos');
		$get_owner_ttl	= get_string('modlos_get_owner_ttl', 'block_modlos');
		$firstname_ttl 	= get_string('modlos_firstname', 	 'block_modlos');
		$lastname_ttl 	= get_string('modlos_lastname', 	 'block_modlos');
		$not_syncdb_ttl = get_string('modlos_not_syncdb',	 'block_modlos');
		$online_ttl	 	= get_string('modlos_online_ttl',	 'block_modlos');
		$active_ttl		= get_string('modlos_active',		 'block_modlos');
		$inactive_ttl	= get_string('modlos_inactive',		 'block_modlos');
		$reset_ttl		= get_string('modlos_reset_ttl',	 'block_modlos');
		$unknown_status	= get_string('modlos_unknown_status','block_modlos');
		$page_num		= get_string('modlos_page',			 'block_modlos');
		$page_num_of	= get_string('modlos_page_of',		 'block_modlos');
		$user_search	= get_string('modlos_user_search',	 'block_modlos');
		$users_found  	= get_string('modlos_users_found', 	 'block_modlos');
		$sloodle_ttl  	= get_string('modlos_sloodle_short', 'block_modlos');

		include(CMS_MODULE_PATH.'/html/avatars.html');
	}
}

?>
