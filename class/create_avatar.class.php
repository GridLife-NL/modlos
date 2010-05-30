<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  CreateAvatar
{
	var $regionNames  = array();
	var $lastNames    = array();
	var $actvLastName = false;

	var $hasPermit 	= false;
	var $action_url	= "";
	var $created_avatar = false;

	var $course_id  = 0;
	var $use_sloodle= false;
	var $pri_sloodle= false;

	var $hasError	= false;
	var $errorMsg	= array();

	// Moodle DB
	var $UUID		= "";
	var $nx_UUID	= "";
	var $uid		= 0;			// owner id of avatar
	var $firstname 	= "";
	var $lastname  	= "";
	var $passwd 	= "";
	var $hmregion  	= "";
	var $ownername 	= "";			// owner name of avatar



	function  CreateAvatar($courseid)
	{
		global $CFG;

		require_login($courseid);

		// for HTTPS
		$use_https = $CFG->mdlopnsm_use_https;
		if ($use_https) {
			$https_url = $CFG->mdlopnsm_https_url;

			if ($https_url!="") {
				$module_url = $https_url.CMS_DIR_NAME;
			}
			else {
				$module_url = ereg_replace('^http:', 'https:', CMS_MODULE_URL);
			}
		}
		else {
			$module_url = CMS_MODULE_URL;
		}


		$this->course_id	= $course_id;
		$this->hasPermit	= hasPermit($course_id);
		$this->action_url  	= $module_url."/actions/create_avatar.php";
		$this->use_sloodle 	= $CFG->mdlopnsm_cooperate_sloodle;
		$this->pri_sloodle 	= $CFG->mdlopnsm_priority_sloodle;
		$this->actvLastName	= $CFG->mdlopnsm_activate_lastname;

		// Number of Avatars Check
		if (!$this->hasPermit) {
			$avatars_num = mdlopensim_get_avatars_num($USER->id);
			$max_avatars = $CFG->mdlopnsm_max_own_avatars;
			if ($max_avatars>=0 and $avatars_num>=$max_avatars) {
				$course_url = $CFG->wwwroot;
				if ($course_id>0) $course_url.= "/course/view.php?id=".$course_id;
				error(get_string('mdlos_over_max_avatars', 'block_mdlopensim')." ($avatars_num >= $max_avatars)", $course_url);
			}
		}
	}



	function  execute()
	{
		// Region Name
		$this->regionNames = opensim_get_regions_names("ORDER BY regionName ASC");
		$this->lastNames   = mdlopensim_get_lastnames();

		if (data_submitted()) {
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
			}
		}

		if ($this->hasPermit) {
			do {
				$uuid   = make_random_guid();
				$modobj = mdlopensim_get_avatar_info($uuid);
			} while ($modobj!=null);
			$this->nx_UUID = $uuid;
		}

		if (data_submitted()) {
			$this->firstname= optional_param('firstname', 	'', PARAM_TEXT);
			$this->lastname = optional_param('lastname',  	'', PARAM_TEXT);
			$this->passwd	= optional_param('passwd', 	'', PARAM_TEXT);
			$confirm_pass	= optional_param('confirm_pass','',PARAM_TEXT);
			$this->hmregion = optional_param('hmregion', 	'', PARAM_TEXT);
			if($this->hasPermit) {
				$this->ownername = optional_param('ownername', '', PARAM_TEXT);
				$this->UUID		 = optional_param('UUID', '', PARAM_TEXT);
			}
			if ($this->ownername=="") $this->ownername = get_display_username($USER->firstname, $USER->lastname);

			// Check
			if (!isGUID($this->UUID, true)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_invalid_uuid", "block_mdlopensim")." ($this->UUID)";
			}
			if (!isAlphabetNumericSpecial($this->firstname)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_invalid_firstname", "block_mdlopensim")." ($this->firstname)";
			}
			if (!isAlphabetNumericSpecial($this->lastname)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_invalid_last", "block_mdlopensim")." ($this->lastname)";
			}
			if (!isAlphabetNumericSpecial($this->passwd)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_invalid_passwd", "block_mdlopensim")." ($this->passwd)";
			}
			if ($this->passwd!=$confirm_pass) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_mismatch_passwd", "block_mdlopensim");
			}
			if (!isAlphabetNumericSpecial($this->hmregion)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_invalid_regionname", "block_mdlopensim")." ($this->hmregion)";
			}
			if (!isAlphabetNumericSpecial($this->ownername)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_invalid_ownername", "block_mdlopensim")." ($this->ownername)";
			}
			if ($this->hasError) return false;

			/////
			$this->created_avatar = $this->create_avatar();
			if (!$this->created_avatar) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_create_error", "block_mdlopensim");
				return false;
			}
		}
		else {
			$this->hmregion  = $CFG->mdlopnsm_home_region;
			$this->UUID		 = $this->nx_UUID;
			$this->ownername = get_display_username($USER->firstname, $USER->lastname);
		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name 	  = $CFG->mdlopnsm_grid_name;
		$isDisclaimer = $CFG->mdlopnsm_activate_disclaimer;
		$disclaimer	  = $CFG->mdlopnsm_disclaimer_content;

		$avatar_create_ttl	= get_string('mdlos_avatar_create',	'block_mdlopensim');
		$uuid_ttl			= get_string('mdlos_uuid',			'block_mdlopensim');
		$firstname_ttl		= get_string('mdlos_firstname',	  	'block_mdlopensim');
		$lastname_ttl		= get_string('mdlos_lastname',		'block_mdlopensim');
		$passwd_ttl			= get_string('mdlos_password',		'block_mdlopensim');
		$confirm_pass_ttl	= get_string('mdlos_confirm_pass',  'block_mdlopensim');
		$home_region_ttl	= get_string('mdlos_home_region',	'block_mdlopensim');
		$ownername_ttl		= get_string('mdlos_ownername',	  	'block_mdlopensim');
		$create_ttl			= get_string('mdlos_create_ttl',	'block_mdlopensim');
		$reset_ttl			= get_string('mdlos_reset_ttl',	  	'block_mdlopensim');
		$avatar_created		= get_string('mdlos_avatar_created','block_mdlopensim');

		$disclaimer_ttl		= get_string('mdlos_disclaimer',  	'block_mdlopensim');
		$disclaim_agree		= get_string('mdlos_disclaimer_agree', 'block_mdlopensim');
		$disclaim_need_agree= get_string('mdlos_need_agree_disclaimer',	'block_mdlopensim');

		// 
		$pv_ownername = $this->ownername;
		if ($this->created_avatar) {
			$pv_firstname = "";
			$pv_lastname  = "";
		}
		else {
			$pv_firstname = $this->firstname;
			$pv_lastname  = $this->lastname;
		}

		include(CMS_MODULE_PATH."/html/create.html");
	}



	function create_avatar()
	{
		global $USER;

		// User Check
		$avuuid = opensim_get_avatar_uuid($this->firstname." ".$this->lastname);
		if ($avuuid!=null) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_already_name_error", "block_mdlopensim")." ($this->firstname $this->lastname)";
			return false;
		}

		// Create UUID
		if (!$this->hasPermit or !isGUID($this->UUID)) {
			do {
				$uuid = make_random_guid();
				$modobj = $this->handler->get($uuid);
			} while ($modobj!=null);
			$this->UUID = $uuid;
		}

		// OpenSim DB
		$rslt = opensim_create_avatar($this->UUID, $this->firstname, $this->lastname, $this->passwd, $this->hmregion);
		if (!$rslt) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_opensim_create_error", "block_mdlopensim")." ($this->UUID)";
			return false;
		}

		// Moodle DB
		if ($this->hasPermit) {
			$names = get_names_from_display_username($this->ownername);
			$user_info = get_userinfo_by_name($names['firstname'], $names['lastname']);
			if ($user_info==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_nouser_found", "block_mdlopensim")." (".$names['firstname']." ".$names['lastname'].")";
				return false;
			}
			$this->uid = $user_info->id;
		}
		else {
			$this->uid = $USER->id;
		}

		$new_user['UUID']		= $this->UUID;
		$new_user['uid']			= $this->uid;
		$new_user['firstname'] 	= $this->firstname;
		$new_user['lastname']  	= $this->lastname;
		$new_user['hmregion']  	= $this->hmregion;
		$new_user['state']	 	= AVATAR_STATE_ACTIVE;;

		$ret = mdlopensim_set_avatar_info($new_user, $this->use_sloodle);
		return $ret;
	}

}

?>
