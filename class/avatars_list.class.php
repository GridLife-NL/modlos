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
	var $currency_url;
	var $owner_url;
	var $search_url;
	var $avatar_url;
	var $personal_url;

	var $course_id  = 0;
	var $user_id    = 0;
	var $url_param  = '';

	var $use_sloodle  = false;
	var $isAvatarMax  = false;
	var	$use_currency = false;

	var $show_all   = false;
	var $hasPermit 	= false;
	var $isGuest   	= true;

	// Page Control
	var $ownerloss  = 0;	// false
	var $Cpstart 	= 0;
	var $Cplimit 	= 25;
	var $firstname 	= '';
	var $lastname  	= '';
	var $order		= '';
	var $order_desc	= 0;
	var $pstart;
	var $plimit;
	var $number;
	var $sitemax;
	var $sitestart;
	var $my_avatars = 0;

    var $desc_fname = 0;
    var $desc_lname = 0;
    var $desc_login = 0;
    var $desc_created = 0;
 
	// SQL
	var $lnk_firstname = '';
	var $lnk_lastname  = '';
	var $sql_countcnd  = '';
	var $sql_condition = '';



	function  AvatarsList($course_id, $show_all, $userid=0)
	{
		global $CFG, $USER;

		// for Guest
		$this->isGuest = isguestuser();
		if ($this->isGuest) {
			print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
		}

		$this->course_id    = $course_id;
		$this->hasPermit    = hasModlosPermit($course_id);
		$this->course_id    = $course_id;
		$this->use_sloodle  = $CFG->modlos_cooperate_sloodle;
		$this->use_currency = modlos_use_currency_server;
		$this->show_all     = $show_all;
		$this->user_id		= $userid;

		if (!$show_all and $userid==0) $this->user_id = $USER->id;

		$this->url_param = '?dmmy_param=';	
		if ($course_id>0) $this->url_param .= '&amp;course='.$course_id;

		if ($this->show_all) {
			$this->action_url  = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_param;
			$this->search_url  = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_param.'&amp;pstart=0';
		}
		else {
			$this->action_url  = CMS_MODULE_URL.'/actions/personal_avatars.php'.$this->url_param;
			$this->search_url  = CMS_MODULE_URL.'/actions/personal_avatars.php'.$this->url_param.'&amp;pstart=0';
		}

		$this->edit_url	    = CMS_MODULE_URL.'/actions/edit_avatar.php'.  $this->url_param;
		$this->owner_url    = CMS_MODULE_URL.'/actions/owner_avatar.php'. $this->url_param;
		$this->currency_url = CMS_MODULE_URL.'/actions/show_currency.php'.$this->url_param;
		$this->personal_url = CMS_MODULE_URL.'/actions/personal_avatars.php'.$this->url_param;
		$this->avatar_url   = $CFG->wwwroot.'/user/view.php'.$this->url_param;

		$this->my_avatars = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $this->my_avatars>=$max_avatars) $this->isAvatarMax = true;
	}



	// アバターの検索条件
	function  set_condition() 
	{
		global $CFG, $USER;

		$db_ver = opensim_get_db_version();
		if ($db_ver==null) {
			$course_url = $CFG->wwwroot;
			if ($this->course_id>0) $course_url.= '/course/view.php?id='.$this->course_id;
			print_error('modlos_db_connect_error', 'block_modlos', $course_url);
		}

		$this->order = optional_param('order', '', PARAM_TEXT);
		$this->order_desc = optional_param('desc', '0', PARAM_INT);
		if (!isAlphabetNumeric($this->order)) $this->order = '';

		// Post Check
		if (data_submitted()) {
			if (!confirm_sesskey()) {
				print_error('modlos_sesskey_error', 'block_modlos', $this->action_url);
			}
		}

		// firstname & lastname Seacrh
		$this->firstname = optional_param('firstname', '', PARAM_TEXT);
		$this->lastname  = optional_param('lastname',  '', PARAM_TEXT);
		if (!isAlphabetNumericSpecial($this->firstname)) $this->firstname = '';
		if (!isAlphabetNumericSpecial($this->lastname))  $this->lastname  = '';

		$sql_validuser = $sql_firstname = $sql_lastname = '';
		if ($this->firstname=='' and $this->lastname=='') {
			if ($db_ver==OPENSIM_V06) $sql_validuser = "username!=''";
			else				      $sql_validuser = "FirstName!=''";
		}
		else {
			if ($this->firstname!='') { 
				if ($db_ver==OPENSIM_V06) $sql_firstname = "username  LIKE '$this->firstname'";
				else				      $sql_firstname = "FirstName LIKE '$this->firstname'";
				$this->lnk_firstname = "&amp;firstname=$this->firstname";
			}
			if ($this->lastname!='') { 
				if ($this->firstname!='') $sql_lastname = "AND lastname LIKE '$this->lastname'";
				else					  $sql_lastname = "lastname LIKE '$this->lastname'";
				$this->lnk_lastname  = "&amp;lastname=$this->lastname";
			}
		}

		// ORDER
        // OPENSIM_V06: users.UUID,  username,  lastname, created, lastLogin, regions.uuid 
        // OPENSIM_V07: PrincipalID, FirstName, LastName, Created, Login,     homeRegionID  
		$sql_order = '';
		if ($this->order=='firstname') {
			if ($db_ver==OPENSIM_V06) $sql_order = 'ORDER BY username';
			else                      $sql_order = 'ORDER BY FirstName';
			if (!$this->order_desc) $this->desc_fname = 1;
		}
		else if ($this->order=='lastname') {
			if ($db_ver==OPENSIM_V06) $sql_order = 'ORDER BY lastname';
			else                      $sql_order = 'ORDER BY LastName';
			if (!$this->order_desc) $this->desc_lname = 1;
		}
		else if ($this->order=='login') {
			if ($db_ver==OPENSIM_V06) $sql_order = 'ORDER BY lastlogin';
			else                      $sql_order = 'ORDER BY Login';
			if (!$this->order_desc) $this->desc_login = 1;
		}
		else {
			if ($db_ver==OPENSIM_V06) $sql_order = 'ORDER BY created';
			else                      $sql_order = 'ORDER BY Created';
			if (!$this->order_desc) $this->desc_created = 1;
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

		// pstart & plimit
		$this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
		$this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);
		//
		$sql_limit = "LIMIT $this->pstart, $this->plimit";
		//
		$this->ownerloss = optional_param('ownerloss', "$this->ownerloss", PARAM_INT);

		// SQL Condition
		$this->sql_countcnd  = " WHERE $sql_validuser $sql_firstname $sql_lastname $sql_order";
		$this->sql_condition = " WHERE $sql_validuser $sql_firstname $sql_lastname $sql_order $sql_limit";

		return true;
	}



	function  execute()
	{
		global $CFG, $USER;

		////////////////////////////////////////////////////////////////////
		// Read DB
		$num = 0;
		$los = 0;
		$all_users = array();

		if ($this->show_all) {	// 	全アバター
			$dummy = opensim_get_avatars_infos($this->sql_countcnd);
			if (is_array($dummy)) {
				foreach($dummy as $user) {
					$all_users[$num] = $user;
					$avatardata = modlos_get_avatar_info($user['UUID'], $this->use_sloodle); // from sloodle
					if ($avatardata==null) {
						$all_users[$num]['uid']   = 0;
						$all_users[$num]['state'] = AVATAR_STATE_NOSTATE;
						$all_users[$num]['owner_name'] = ' - ';
						$los++;
					}
					else {
						$all_users[$num]['uid']   = $avatardata['uid'];
						$all_users[$num]['state'] = $avatardata['state'];
						if ($avatardata['uid']>0) {
							$user_info = get_userinfo_by_id($avatardata['uid']);
							if ($user_info!=null) {
								$all_users[$num]['owner_name'] = get_display_username($user_info->firstname, $user_info->lastname);
							}
							else {
								$all_users[$num]['uid'] = 0;
								$all_users[$num]['owner_name'] = ' - ';
								$los++;
							}
						}
						else {
							if (!($avatardata['state']&AVATAR_STATE_INACTIVE)) {
								$all_users[$num]['uid'] = 0;
								$los++;
							}
						}
					}
					$num++;
				}
			}
		}
		//
		else {					// パーソナル アバター
			$dummy = modlos_get_avatars($this->user_id);
			if (is_array($dummy)) {
				$num = 0;
				foreach($dummy as $user) {
					$all_users[$num] = $user;
					$all_users[$num]['uid'] = $this->user_id;
					$avatardata = modlos_get_avatar_info($user['UUID'], $this->use_sloodle); // from sloodle
					if ($avatardata!=null) {
						$all_users[$num]['state'] = $avatardata['state'];
					}
					unset($avatardata);
					//
					$avatardata = opensim_get_avatar_info($user['UUID']);
					if ($avatardata!=null) {
						$all_users[$num]['lastlogin']   = $avatardata['lastlogin'];
						$all_users[$num]['hmregion_id'] = $avatardata['regionUUID'];
						$all_users[$num]['hmregion']    = $avatardata['regionName'];
						$all_users[$num]['created'] 	= $avatardata['created'];
						if (isGUID($all_users[$num]['hmregion'])) $all_users[$num]['hmregion'] = '';
					}
					$num++;
				}
			}
		}
		//
		if ($this->ownerloss) $this->number = $los;
		else                  $this->number = $num;

		//
		$i = 0;
		$count = 0;
		$users = array();
		foreach($all_users as $user) {
			if ($user['uid']==0 or !$this->ownerloss) {
				if ($i>=$this->pstart) {
					$users[$count] = $user;
					$count++;
					if ($count==$this->plimit) break;
				}
				$i++;
			}
		}
		unset($all_users);

		// auto synchro
		modlos_sync_opensimdb();
		if ($this->use_sloodle) modlos_sync_sloodle_users();

		////////////////////////////////////////////////////////////////////
		// OpenSim DB
		$colum = 0;
		$dat   = array();

		if ($this->show_all) {	// 	全アバター
			foreach($users as $user) {
				$user['editable'] = AVATAR_NOT_EDITABLE;
				$user['hmregion'] = modlos_get_region_name($user['hmregion_id']);
				if (isGUID($user['hmregion'])) $user['hmregion'] = '';
				//
				$dat = $this->get_avatar_info($user, $colum); 

				if ($this->ownerloss) {
					if ($dat['editable']==AVATAR_OWNER_EDITABLE and !($dat['state']&AVATAR_STATE_INACTIVE)) {
						$this->db_data[$colum] = $dat;
						$colum++;
					}
				} 
				else {
					$this->db_data[$colum] = $dat;
					$colum++;
				}
			}
		}
		//
		else {					// パーソナル アバター
			foreach($users as $user) {
				$user['editable'] = AVATAR_NOT_EDITABLE;
				//
				$this->db_data[$colum] = $this->get_avatar_info($user, $colum); 
				$colum++;
			}
		}

		unset($users);

		////////////////////////////////////////////////////////////////////
		//
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
		$date_format	= DATE_FORMAT;

		$has_permit		= $this->hasPermit;
		$avatar_max		= $this->isAvatarMax;
		$use_currency	= $this->use_currency;
		$lnk_firstname	= $this->lnk_firstname;
		$lnk_lastname	= $this->lnk_lastname;
		$url_param		= $this->url_param;
		$plimit_amp		= "&amp;plimit=$this->plimit";
		$pstart_amp		= "&amp;pstart=$this->pstart";
		$order_amp		= "&amp;order=$this->order&amp;desc=$this->order_desc";
		$loss_amp		= "&amp;ownerloss=$this->ownerloss";
		$plimit_		= '&amp;plimit=';
		$pstart_		= '&amp;pstart=';
		$order_			= '&amp;order=';
		$loss_			= '&amp;ownerloss=';
		$action_url		= $this->action_url.$lnk_firstname.$lnk_lastname.$loss_amp;

        $desc_fname  	= "&amp;desc=$this->desc_fname";
        $desc_lname  	= "&amp;desc=$this->desc_lname";
        $desc_login    	= "&amp;desc=$this->desc_login";
        $desc_created 	= "&amp;desc=$this->desc_created";

		$number_ttl		= get_string('modlos_num',			 'block_modlos');
		$edit_ttl		= get_string('modlos_edit',			 'block_modlos');
		$show_ttl		= get_string('modlos_show',			 'block_modlos');
		$editable_ttl	= get_string('modlos_edit_ttl',		 'block_modlos');
		$lastlogin_ttl	= get_string('modlos_lastlogin',	 'block_modlos');
		$status_ttl		= get_string('modlos_status',		 'block_modlos');
		$crntregion_ttl	= get_string('modlos_crntregion',	 'block_modlos');
		$avatar_ttl		= get_string('modlos_avatar',		 'block_modlos');
		$owner_ttl		= get_string('modlos_owner',		 'block_modlos');
		$get_owner_ttl	= get_string('modlos_get_owner_ttl', 'block_modlos');
		$firstname_ttl	= get_string('modlos_firstname', 	 'block_modlos');
		$lastname_ttl 	= get_string('modlos_lastname', 	 'block_modlos');
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

		$avarars_list_url = CMS_MODULE_URL.'/actions/avatars_list.php'.$this->url_param;

		if ($this->show_all) {
			$avatars_list = get_string('modlos_avatars_list', 'block_modlos');
		}
		else if ($this->user_id==$USER->id) {
			$avatars_list = get_string('modlos_my_avatars', 'block_modlos');
		}
		else {
			$userinfo = get_userinfo_by_id($this->user_id);
			$username = get_display_username($userinfo->firstname, $userinfo->lastname);
			$userurl  = '<a href="'.$this->avatar_url.'&id='.$this->user_id.'" target="_blank">'.$username.'</a>';
			$avatars_list = get_string('modlos_personal_avatars', 'block_modlos', $userurl);
		}

		if ($this->show_all) {	// 	全アバター
			include(CMS_MODULE_PATH.'/html/avatars.html');
		}
		else {
			include(CMS_MODULE_PATH.'/html/personal_avatars.html');
		}
	}



	function  get_avatar_info($user, $colum) 
	{
		global $USER;

		$dat				= $user;
		$dat['num']			= $colum;
		$dat['region_id']	= $user['hmregion_id'];
		$dat['region']		= $user['hmregion'];
		$dat['state']		= $user['state'];
		$dat['editable']	= AVATAR_NOT_EDITABLE;

		$created = $dat['created'];
		if ($created==null or $created=='' or $created=='0') {
			$dat['born'] = ' - ';
		}
		else {
			$dat['born'] = date(DATE_FORMAT, $created);
		}

		$lastlogin = $dat['lastlogin'];
		if ($lastlogin==null or $lastlogin=='' or $lastlogin=='0') {
			$dat['lastin'] = ' - ';
		}
		else {
			$dat['lastin'] = date(DATE_FORMAT, $lastlogin);
		}

		// Agent Online Info
		$UUID = $dat['UUID'];
		$online = opensim_get_avatar_online($UUID);
		$dat['online'] = $online['online'];
		if ($online['online']) {
			$dat['region'] 	  = $online['region_name'];
			$dat['region_id'] = $online['region_id'];
		}

		$dat['uuid']	= str_replace('-', '', $UUID);
		$dat['rg_uuid'] = str_replace('-', '', $dat['region_id']);

		if ($this->hasPermit or $USER->id==$dat['uid']) {
			$dat['editable'] = AVATAR_EDITABLE;
		}
		elseif ($dat['uid']==0) {
			if (!$this->isAvatarMax and $this->ownerloss) {
				$dat['editable'] = AVATAR_OWNER_EDITABLE;
			}
		}

		return $dat;
	}
}
