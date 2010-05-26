<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/moodle.func.php");
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  AvatarsList
{
	var $db_data = array();
	var $icon = array();
	var $pnum = array();
	var $action_url;
	var $edit_url;
	var $owner_url;
	var $courseid = 0;

	var $hasPermit = false;
	var $isGuest = true;
	var $userid  = 0;
	var $db_ver  = "";

	// Page Control
	var $Cpstart = 0;
	var $Cplimit = 25;
	var $firstname = "";
	var $lastname  = "";
	var $pstart;
	var $plimit;
	var $number;
	var $sitemax;
	var $sitestart;

	// SQL
	var $lnk_firstname = "";
	var $lnk_lastname  = "";
	var $sql_countcnd  = "";
	var $sql_condition = "";



	function  AvatarsList($courseid)
	{
		$this->courseid  = $courseid;
		$this->isGuest   = isguest();
		$this->hasPermit = hasPermit($courseid);
	}


	function  set_condition() 
	{
		$this->db_ver  = opensim_get_db_version();
		if ($db_ver=="0.0") {
			error(get_string('mdlos_db_connect_error', 'block_mdlopensim'));
		}

		$sql_order = "ORDER BY created ASC";

		// firstname & lastname
		$this->firstname = optional_param('firsrname', '', PARAM_TEXT);
		$this->lastname  = optional_param('lastname',  '', PARAM_TEXT);
		if (!isAlphabetNumeric($this->firstname)) $this->firstname = "";
		if (!isAlphabetNumeric($this->lastname))  $this->lastname  = "";

		$sql_validuser = $sql_firstname = $sql_lastname = "";
		if ($this->firstname=="" and $this->lastname=="") {
			if ($this->db_ver=="0.6") $sql_validuser = "username!=''";
			else                      $sql_validuser = "FirstName!=''";
		}
		else {
			if ($this->firstname!="") { 
				if ($this->db_ver=="0.6") $sql_firstname = "username  LIKE '$this->firstname'";
				else                      $sql_firstname = "FirstName LIKE '$this->firstname'";
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
		$sql_limit = "LIMIT $this->pstart, $this->plimit";

		$this->sql_countcnd  = " WHERE $sql_validuser $sql_firstname $sql_lastname";
		$this->sql_condition = " WHERE $sql_validuser $sql_firstname $sql_lastname $sql_order $sql_limit";
		$this->action_url    = CMS_MODULE_URL."/?action=avatars";
		$this->edit_url      = CMS_MODULE_URL."/?action=edit";
		$this->owner_url     = CMS_MODULE_URL."/?action=owner";
	}



	function  execute()
	{
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
			$this->db_data[$colum]['uname']		= ' - ';        // user name in Xoops
			$this->db_data[$colum]['region_id']	= $user['hmregion'];
			$this->db_data[$colum]['region']	= opensim_get_region_name($user['hmregion'], $DbLink);
			$this->db_data[$colum]['state']		= AVATAR_STATE_NOTSYNC;
			$this->db_data[$colum]['editable']	= AVATAR_NOT_EDITABLE;


			$created = $this->db_data[$colum]['created'];
			if ($created==null or $created=="" or $created=='0') {
				$this->db_data[$colum]['born'] = ' - ';
			}
			else {
				$this->db_data[$colum]['born'] = date("Y.m.d", $created);
			}

			$lastlogin = $this->db_data[$colum]['lastlogin'];
			if ($lastlogin==null or $lastlogin=="" or $lastlogin=='0') {
				$this->db_data[$colum]['lastin'] = ' - ';
			}
			else {
				$this->db_data[$colum]['lastin'] = date("Y.m.d - H:i", $lastlogin);
			}

			// Agent Online Info
			$UUID = $this->db_data[$colum]['UUID'];
			$online = opensim_get_avatar_online($UUID, $DbLink);
			$this->db_data[$colum]['online'] = $online['online'];
			if ($online['online']) {
				$this->db_data[$colum]['region_id']	= $online['region'];
				$this->db_data[$colum]['region'] 	= opensim_get_region_name($online['region'], $DbLink);
			}

			// serach Moodle DB
			$avatadata = get_record('mdlos_users', 'uuid', $UUID);
			if ($avatadata!=null) {
				$uid = $avatadata->user_id;
				$this->db_data[$colum]['state'] = $avatardata->state;
				if ($uid>0) {
					$user_info = get_record('user', 'id', $uid, 'deleted', '0');
					if ($user_info!=null) {
						$this->db_data[$colum]['uname'] = getUserName($user_info->firstname, $user_info->lastname);
					}
				}
			}

			$this->db_data[$colum]['uid'] = $uid;

			if ($this->isAdmin or $this->userid==$uid) {
				$this->db_data[$colum]['editable'] = AVATAR_EDITABLE;
			}
			elseif ($uid==0) {
				$this->db_data[$colum]['editable'] = AVATAR_OWNER_EDITABLE;
			}

			$colum++;
		}

		$DbLink->close();
	}



	function  print_page() 
	{
        global $CFG;

        $this->set_condition();
        $this->execute();

        $grid_name 		= $CFG->mdlopnsm_grid_name;
        $content   		= $CFG->mdlopnsm_avatars_content;
		$module_url		= CMS_MODULE_URL;
        $course        	= "&amp;course=$this->courseid";
        $pstart0        = "&amp;pstart=$this->pnum[0]";
        $pstart1        = "&amp;pstart=$this->pnum[1]";
        $pstart2        = "&amp;pstart=$this->pnum[2]";
        $plimit         = "&amp;plimit=$this->plimit";


		$page_num			= get_string("mdlos_page","block_mdlopensim");
		$page_num_of		= get_string("mdlos_page_of","block_mdlopensim");
		$user_search		= get_string("mdlos_user_search","block_mdlopensim");
		$avatars_list_ttl	= get_string('mdlos_avatars_list', 'block_mdlopensim'));
		$firstname_ttl 		= get_string('mdlos_firstname', 'block_mdlopensim'));
		$lastname_ttl 		= get_string('mdlos_lastname', 'block_mdlopensim'));
		$users_found	  	= get_string('mdlos_users_found', 'block_mdlopensim'));

        include(CMS_MODULE_PATH."/html/avatars.html");
	}
}

?>
