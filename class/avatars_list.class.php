<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/moodle.func.php");
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



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
	var $course_amp	= "";

	var $hasPermit 	= false;
	var $isGuest   	= true;

	// Page Control
	var $Cpstart 	= 0;
	var $Cplimit 	= 25;
	var $firstname 	= "";
	var $lastname  	= "";
	var $pstart;
	var $plimit;
	var $number;
	var $sitemax;
	var $sitestart;
	var $date_format;

	// SQL
	var $lnk_firstname = "";
	var $lnk_lastname  = "";
	var $sql_countcnd  = "";
	var $sql_condition = "";



	function  AvatarsList($course_id)
	{
		global $CFG;

		require_login($course_id);

		$this->course_id  = $course_id;
		$this->isGuest    = isguest();
		$this->hasPermit  = hasPermit($course_id);
		$this->date_format= $CFG->mdlopnsm_date_format;
		$course_param 	  = "?course=".$course_id;
		$this->course_id  = $course_id;

		$this->action_url = CMS_MODULE_URL."/actions/avatars_list.php".$course_param;
		$this->edit_url	  = CMS_MODULE_URL."/actions/edit_avatar.php". $course_param;
		$this->owner_url  = CMS_MODULE_URL."/actions/owner_avatar.php".$course_param;
		$this->search_url = CMS_MODULE_URL."/actions/avatars_list.php";
		$this->avatar_url = $CFG->wwwroot."/user/view.php";

		if ($course_id>0) $this->course_amp = "&amp;course=".$course_id;
	}



	function  set_condition() 
	{
		$db_ver = opensim_get_db_version();
		if ($db_ver=="0.0") {
			$course_url = $CFG->wwwroot;
			if ($ithis->course_id>0) $course_url.= "/course/view.php?id=".$course_id;
			error(get_string('mdlos_db_connect_error', 'block_mdlopensim'), $course_url);
		}

		// Post Check
		if (data_submitted()) {
			if (!confirm_sesskey()) {
				error(get_string('mdlos_sesskey_error', 'block_mdlopensim'), $this->action_url);
			}
		}

		// firstname & lastname
		$this->firstname = optional_param('firstname', '', PARAM_TEXT);
		$this->lastname  = optional_param('lastname',  '', PARAM_TEXT);
		if (!isAlphabetNumeric($this->firstname)) $this->firstname = "";
		if (!isAlphabetNumeric($this->lastname))  $this->lastname  = "";

		$sql_validuser = $sql_firstname = $sql_lastname = "";
		if ($this->firstname=="" and $this->lastname=="") {
			if ($db_ver=="0.6") $sql_validuser = "username!=''";
			else                $sql_validuser = "FirstName!=''";
		}
		else {
			if ($this->firstname!="") { 
				if ($db_ver=="0.6") $sql_firstname = "username  LIKE '$this->firstname'";
				else                $sql_firstname = "FirstName LIKE '$this->firstname'";
				$this->lnk_firstname = "&amp;firstname=$this->firstname";
			}
			if ($this->lastname!="") { 
				if ($this->firstname!="") $sql_lastname = "and lastname LIKE '$this->lastname'";
				else                      $sql_lastname = "lastname LIKE '$this->lastname'";
				$this->lnk_lastname  = "&amp;lastname=$this->lastname";
			}
		}

		// pstart & plimit
		$this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
		$this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);

		// SQL Condition
		$sql_order = "ORDER BY created ASC";
		$sql_limit = "LIMIT $this->pstart, $this->plimit";
		$this->sql_countcnd  = " WHERE $sql_validuser $sql_firstname $sql_lastname";
		$this->sql_condition = " WHERE $sql_validuser $sql_firstname $sql_lastname $sql_order $sql_limit";

		return true;
	}



	function  execute()
	{
		global $CFG, $USER;

		$use_sloodle = $CFG->mdlopnsm_cooperate_sloodle;
		$pri_sloodle = $CFG->mdlopnsm_priority_sloodle;

		$this->number    = count(opensim_get_avatars_infos($this->sql_countcnd));;
		$this->sitemax   = ceil ($this->number/$this->plimit);
		$this->sitestart = round($this->pstart/$this->plimit, 0) + 1;
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

		$this->icon[3] = $this->icon[4] = $this->icon[5] = $this->icon[6] = "icon_limit_off";
		if ($this->plimit != 10)  $this->icon[3] = "icon_limit_10_on"; 
		if ($this->plimit != 25)  $this->icon[4] = "icon_limit_25_on";
		if ($this->plimit != 50)  $this->icon[5] = "icon_limit_50_on";
		if ($this->plimit != 100) $this->icon[6] = "icon_limit_100_on";


		// OpenSim DB
		$users = opensim_get_avatars_infos($this->sql_condition);

		$DbLink = new DB;
		$colum  = 0;
		foreach($users as $user) {
			$this->db_data[$colum]				= $user;
			$this->db_data[$colum]['num']		= $colum;
			$this->db_data[$colum]['ownername']	= ' - ';
			$this->db_data[$colum]['region_id']	= $user['hmregion'];
			$this->db_data[$colum]['region']	= opensim_get_region_name($user['hmregion'], $DbLink);
			$this->db_data[$colum]['state']		= AVATAR_STATE_NOSTATE;
			$this->db_data[$colum]['editable']	= AVATAR_NOT_EDITABLE;

			$created = $this->db_data[$colum]['created'];
			if ($created==null or $created=="" or $created=='0') {
				$this->db_data[$colum]['born'] = ' - ';
			}
			else {
				$this->db_data[$colum]['born'] = date($this->date_format, $created);
			}

			$lastlogin = $this->db_data[$colum]['lastlogin'];
			if ($lastlogin==null or $lastlogin=="" or $lastlogin=='0') {
				$this->db_data[$colum]['lastin'] = ' - ';
			}
			else {
				$this->db_data[$colum]['lastin'] = date($this->date_format, $lastlogin);
			}

			// Agent Online Info
			$UUID = $this->db_data[$colum]['UUID'];
			$online = opensim_get_avatar_online($UUID, $DbLink);
			$this->db_data[$colum]['online'] = $online['online'];
			if ($online['online']) {
				$this->db_data[$colum]['region_id']	= $online['region'];
				$this->db_data[$colum]['region'] 	= opensim_get_region_name($online['region'], $DbLink);
			}

			// serach Moodle, Mdlopensim and Sloodle DB
			$uid = -1;
			$avatardata = mdlopensim_get_avatar_info($UUID, $use_sloodle, $pri_sloodle);
			if ($avatardata!=null) {
				$uid = $avatardata['uid'];
				$this->db_data[$colum]['state'] = $avatardata['state'];
				if ($uid>0) {
					$user_info = get_record('user', 'id', $uid, 'deleted', '0');
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
				$this->db_data[$colum]['editable'] = AVATAR_OWNER_EDITABLE;
			}

			$colum++;
		}

		$DbLink->close();
		return true;
	}



	function  print_page() 
	{
        global $CFG;

        $grid_name 		= $CFG->mdlopnsm_grid_name;
        $content   		= $CFG->mdlopnsm_avatars_content;
		$userinfo		= $CFG->mdlopnsm_userinfo_link;
		$date_format	= $CFG->mdlopnsm_date_format;

		$course_amp		= $this->course_amp;
        $plimit_amp     = "&amp;plimit=$this->plimit";
        $pstart_        = "&amp;pstart=";
        $plimit_        = "&amp;plimit=";

		$avatars_list	= get_string('mdlos_avatars_list', 	'block_mdlopensim');
		$number_ttl		= get_string('mdlos_no',			'block_mdlopensim');
		$edit_ttl		= get_string('mdlos_edit',			'block_mdlopensim');
		$editable_ttl	= get_string('mdlos_edit_ttl',		'block_mdlopensim');
		$lastlogin_ttl	= get_string('mdlos_lastlogin',		'block_mdlopensim');
		$status_ttl		= get_string('mdlos_status',		'block_mdlopensim');
		$crntregion_ttl	= get_string('mdlos_crntregion',	'block_mdlopensim');
		$owner_ttl		= get_string('mdlos_owner',			'block_mdlopensim');
		$get_owner_ttl	= get_string('mdlos_get_owner_ttl',	'block_mdlopensim');
		$firstname_ttl 	= get_string('mdlos_firstname', 	'block_mdlopensim');
		$lastname_ttl 	= get_string('mdlos_lastname', 		'block_mdlopensim');
		$not_syncdb_ttl = get_string("mdlos_not_syncdb",	"block_mdlopensim");
		$online_ttl     = get_string("mdlos_online_ttl",	"block_mdlopensim");
		$active_ttl		= get_string('mdlos_active',		'block_mdlopensim');
		$inactive_ttl	= get_string('mdlos_inactive',		'block_mdlopensim');
		$unknown_status	= get_string('mdlos_unknown_status','block_mdlopensim');
		$page_num		= get_string('mdlos_page',			'block_mdlopensim');
		$page_num_of	= get_string('mdlos_page_of',		'block_mdlopensim');
		$user_search	= get_string('mdlos_user_search',	'block_mdlopensim');
		$users_found  	= get_string('mdlos_users_found', 	'block_mdlopensim');

        include(CMS_MODULE_PATH."/html/avatars.html");
	}
}

?>
