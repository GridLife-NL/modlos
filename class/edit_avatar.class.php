<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");




class  EditAvatar
{
	var $regionNames = array();

	var $hasPermit	= false;
	var $action_url = "";
	var $delete_url = "";
	var $return_url = "";
	var $module_url = "";
	var $updated_avatar = false;

	var $course_id 	= 0;
	var $course_param = "";

	var $hasError	= false;
	var $errorMsg 	= array();

	// Moodle DB
	var $avatar		= null;
	var $UUID		= "";
	var $uid 	   	= 0;				// owner id of avatar
	var $firstname 	= "";
	var $lastname  	= "";
	var $passwd  	= "";
	var $hmregion  	= "";
	var $state	 	= 0;
	var $ostate		= 0;
	var $ownername 	= "";			// owner name of avatar



	function  EditAvatar($courseid) 
	{
		global $CFG, $USER;

		require_login($courseid);

		// for HTTPS
		$use_https = $CFG->mdlopnsm_use_https;
		if ($use_https) {
			$https_url = $CFG->mdlopnsm_https_url;
			if ($https_url!="") {
				$this->module_url = $https_url."/".CMS_DIR_NAME;
			}
			else {
				$this->module_url = ereg_replace('^http:', 'https:', CMS_MODULE_URL);
			}
		}
		else {
			$this->module_url = CMS_MODULE_URL;
		}

		if ($course_id>0) $this->course_param = "&amp;course=".$course_id;
		$this->course_id  = $course_id;
		$this->action_url = CMS_MODULE_URL."/actions/edit_avatar.php".  $this->course_param;
		$this->delere_url = CMS_MODULE_URL."/actions/delete_avatar.php".$this->course_param;
		$this->return_url = CMS_MODULE_URL."/actions/avatars_list.php". $this->course_param;


		// get UUID from POST or GET
		$uuid = required_param('UUID', PARAM_TEXT);
		if (!isGUID($uuid)) {
			error(get_string('mdlos_invalid_uuid', 'block_mdlopensim')." ($uuid)", $this->return_url);
		}
		$this->UUID = $uuid;

		// get uid from Mdlopensim DB
		$avatar = get_record('mdlos_users', 'uuid', $this->UUID);
		$this->uid	  = $avatar->user_id;				// uid of avatar in editing from DB
		$this->ostate = $avatar->state;
		$this->avatar = $avatar;

		$this->hasPermit = hasPermit();
		if (!$this->hasPermit and $USER->id!=$this->uid) {
			error(get_string('mdlos_access_forbidden', 'block_mdlopensim'), $this->return_url);
		}
	}



	function  execute()
	{
		global $USER;

		// OpenSim DB
		$this->regionNames = opensim_get_regions_names("ORDER BY regionName ASC");

		$this->firstname = $this->avatar->firstname;
		$this->lastname  = $this->avatar->lastname;

		// Form
		if (data_submitted()) {
			if (!confirm_sesskey()) { 
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
				return false;
			}

			$del = optional_param('submit_delete', '', PARAM_TEXT);
			if ($del!="") {
				redirect($this->delete_url);
				$this->hasError = true;
				$this->errorMsg[] = "delete page open error!!";
				return false;
			}

			$this->passwd	= optional_param('passwd',   '', PARAM_TEXT);
			$this->hmregion = optional_param('hmregion', '', PARAM_TEXT);
			$this->state 	= optional_param('state',	  '', PARAM_TEXT);

			if ($this->hasPermit) {
				$this->ownername = optional_param('ownername', '', PARAM_TEXT);
				$user_info = get_userinfo_by_name($this->ownername);				
				if ($user_info!=null) $this->uid = $user_info->id;
			}
			if ($this->ownername=="") {
				$this->ownername = get_local_user_name($USER->firstname, $USER->lastname);
				$this->uid = $USER->id;
			}

			//////////
			$this->updated_avatar = $this->updateAvatar();
			//////////
		}
		else {
			$this->passwd	= "";
			$this->hmregion = $this->avatar->hmregion;
			$this->state  	= $this->avatar->state;

			if ($this->hasPermit) {
				$user_info = get_userinfo_by_id($this->uid);
				if ($user_info!=null) {
					$this->ownername = get_local_user_name($user_info->firstname, $user_info->lastname);
					$this->uid = $user_info->id;
				}
			}
			if ($this->ownername=="") {
				$this->ownername = get_User_Name($USER->firstname, $USER->lastname);
				$this->uid = $USER->id;
			}
		}
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name = $CFG->mdlopnsm_grid_name;

		$avatar_dit   		= get_string('mdlos_avatar_edit',	'block_mdlopensim');
		$firstname_ttl  	= get_string('mdlos_firstname',	 	'block_mdlopensim');
		$lastname_ttl   	= get_string('mdlos_lastname',		'block_mdlopensim');
		$passwd_ttl  		= get_string('mdlos_password',	 	'block_mdlopensim');
		$confirm_pass_ttl  	= get_string('mdlos_confirm_pass',	'block_mdlopensim');
		$home_region_ttl  	= get_string('mdlos_home_region',	'block_mdlopensim');
		$status_ttl	 		= get_string('mdlos_status',		'block_mdlopensim');
		$active_ttl	 		= get_string('mdlos_active',		'block_mdlopensim');
		$inactive_ttl   	= get_string('mdlos_inactive',	  	'block_mdlopensim');
		$owner_ttl	  		= get_string('mdlos_owner',		 	'block_mdlopensim');
		$ownername_ttl	  	= get_string('mdlos_ownername',	 	'block_mdlopensim');
		$update_ttl	  		= get_string('mdlos_update_ttl', 	'block_mdlopensim');
		$delete_ttl	  		= get_string('mdlos_delete_ttl', 	'block_mdlopensim');
		$reset_ttl	  		= get_string('mdlos_reset_ttl', 	'block_mdlopensim');
		$avatar_updated	  	= get_string('mdlos_avatar_updated','block_mdlopensim');
		$uuid_title	  		= get_string('mdlos_uuid',			'block_mdlopensim');

		include(CMS_MODULE_PATH."/html/edit.html");
	}



	function updateAvatar()
	{
		global $USER;

		// OpenSim DB
		if ($this->passwd!="") {
			$passwdsalt = make_random_hash();
			$passwdhash = md5(md5($this->passwd).":".$passwdsalt);

			$ret = opensim_set_password($this->UUID, $passwdhash, $passwdsalt);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_passwd_update_error", "block_mdlopensim");
				return false;
			}
		}

		if ($this->hmregion!="") {
			$ret = opensim_set_home_region($this->UUID, $this->hmregion);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_hmrgn_update_error", "block_mdlopensim");
				return false;
			}
		}


		// State
		$errno = 0;
		if ($this->state!=$this->ostate) {
			// XXXXXX -> InAcvtive
			if ($this->state==AVATAR_STATE_INACTIVE) {
				$ret = mdlopensim_inactivate_avatar($this->UUID);
				if (!$ret) {
					$this->hasError = true;
					$this->errorMsg[] = get_string("mdlos_inactivate_error", "block_mdlopensim");
					return false;
				}
			}
			// InActive -> Acvtive
			elseif ($this->ostate==AVATAR_STATE_INACTIVE and $this->state==AVATAR_STATE_ACTIVE) {
				$ret = mdlopensim_activate_avatar($this->UUID);
				if (!$ret) {
					$this->hasError = true;
					$this->errorMsg[] = get_string("mdlos_activate_error", "block_mdlopensim");
					return false;
				}
			}
		}

		// Mdlopensim DB
		$update_user['id']	  	  = $this->avatar->id;
		$update_user['UUID']	  = $this->UUID;
		$update_user['uid']		  = $this->uid;
		$update_user['firstname'] = $this->firstname;
		$update_user['lastname']  = $this->lastname;
		$update_user['hmregion']  = $this->hmregion;
		$update_user['state']	  = $this->state;
		$update_user['time']	  = time();

		$ret = mdlopensim_update_usertable($update_user);
		if (!$ret) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_update_error", "block_mdlopensim");
		}

		// Sloodle
		if ($CFG->mdlopnsm_cooperate_sloodle) {
			$ret = delete_records(MDL_SLOODLE_USERS_TBL, 'uuid', $this->UUID);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sloodle_update_error", "block_mdlopensim");
			}
		}

		return $this->hasError;
	}

}

?>
