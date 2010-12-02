<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  CreateAvatar
{
	var $regionNames  = array();
	var $lastNames    = array();
	var $actvLastName = false;

	var $hasPermit 	= false;
	var $isGuest 	= true;
	var $action_url	= '';
	var $created_avatar = false;

	var	$avatars_num = 0;
	var	$max_avatars = 0;
	var	$isAvatarMax = false;

	var $course_id  = 0;
	var $use_sloodle= false;
	var	$isDisclaimer=false;

	var $hasError	= false;
	var $errorMsg	= array();

	// Moodle DB
	var $UUID		= '';
	var $nx_UUID	= '';
	var $uid		= 0;			// owner id of avatar
	var $firstname 	= '';
	var $lastname  	= '';
	var $passwd 	= '';
	var $hmregion  	= '';
	var $ownername 	= '';			// owner name of avatar



	function  CreateAvatar($course_id)
	{
		global $CFG, $USER;

		require_login($course_id);

		// for Guest
		$this->isGuest = isguest();
		if ($this->isGuest) {
			error(get_string('modlos_access_forbidden', 'block_modlos'), CMS_MODULE_URL);
		}

		// for HTTPS
		$use_https = $CFG->modlos_use_https;
		if ($use_https) {
			$https_url = $CFG->modlos_https_url;
			if ($https_url!='') $module_url = $https_url.CMS_DIR_NAME;
			else 				$module_url = ereg_replace('^http:', 'https:', CMS_MODULE_URL);
		}
		else $module_url = CMS_MODULE_URL;

		$this->course_id	= $course_id;
		$this->hasPermit	= hasModlosPermit($course_id);
		$this->action_url  	= $module_url.'/actions/create_avatar.php';
		$this->use_sloodle 	= $CFG->modlos_cooperate_sloodle;
		$this->actvLastName	= $CFG->modlos_activate_lastname;
		$this->isDisclaimer = $CFG->modlos_activate_disclaimer;

		$this->avatars_num = modlos_get_avatars_num($USER->id);
		$this->max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;

		// Number of Avatars Check
		if ($this->isAvatarMax) {
			$course_url = $CFG->wwwroot;
			if ($course_id>0) $course_url.= '/course/view.php?id='.$course_id;
			error(get_string('modlos_over_max_avatars', 'block_modlos')." ($this->avatars_num >= $this->max_avatars)", $course_url);
		}
	}



	function  execute()
	{
		global $CFG, $USER;

		// Region Name
		$this->regionNames = opensim_get_regions_names('ORDER BY regionName ASC');
		$this->lastNames   = modlos_get_lastnames();

		if (data_submitted()) {
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
			}
		}

		if ($this->hasPermit) {
			do {
				$uuid   = make_random_guid();
				$modobj = modlos_get_avatar_info($uuid);
			} while ($modobj!=null);
			$this->nx_UUID = $uuid;
		}

		if (data_submitted()) {
			$this->firstname= optional_param('firstname', 	'', PARAM_TEXT);
			$this->lastname = optional_param('lastname',  	'', PARAM_TEXT);
			$this->passwd	= optional_param('passwd', 	'', 	PARAM_TEXT);
			$confirm_pass	= optional_param('confirm_pass', '',PARAM_TEXT);
			$this->hmregion = optional_param('hmregion', 	'', PARAM_TEXT);
			if($this->hasPermit) {
				$this->ownername = optional_param('ownername', '', PARAM_TEXT);
				$this->UUID		 = optional_param('UUID', 	   '', PARAM_TEXT);
			}
			else $this->ownername = get_display_username($USER->firstname, $USER->lastname);

			// Check
			if (!isGUID($this->UUID, true)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_invalid_uuid', 'block_modlos')." ($this->UUID)";
			}
			if (!isAlphabetNumericSpecial($this->firstname)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_invalid_firstname', 'block_modlos')." ($this->firstname)";
			}
			if (!isAlphabetNumericSpecial($this->lastname)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_invalid_lastname', 'block_modlos')." ($this->lastname)";
			}
			if (!isAlphabetNumericSpecial($this->passwd)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_invalid_passwd', 'block_modlos')." ($this->passwd)";
			}
			if (strlen($this->passwd)<AVATAR_PASSWD_MINLEN) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_passwd_minlength', 'block_modlos').' ('.AVATAR_PASSWD_MINLEN.')';
			}
			if ($this->passwd!=$confirm_pass) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_mismatch_passwd', 'block_modlos');
			}
			if (!isAlphabetNumericSpecial($this->hmregion)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_invalid_regionname', 'block_modlos')." ($this->hmregion)";
				$this->errorMsg[] = get_string('modlos_or_notconnect_db', 'block_modlos');
			}
			if ($this->isDisclaimer and !$this->hasPermit) {
				$agree = optional_param('agree', '', PARAM_ALPHA);
				if ($agree!='agree') {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_need_agree_disclaimer', 'block_modlos');
				}
			}
			if ($this->hasError) return false;

			/////
			$this->created_avatar = $this->create_avatar();
			if (!$this->created_avatar) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_create_error', 'block_modlos');
				return false;
			}
		}
		else {
			$this->hmregion  = $CFG->modlos_home_region;
			$this->UUID		 = $this->nx_UUID;
			$this->ownername = get_display_username($USER->firstname, $USER->lastname);
		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name 	  = $CFG->modlos_grid_name;
		$disclaimer	  = $CFG->modlos_disclaimer_content;

		$avatar_create_ttl  = get_string('modlos_avatar_create', 'block_modlos');
		$uuid_ttl			= get_string('modlos_uuid',			 'block_modlos');
		$firstname_ttl		= get_string('modlos_firstname',	 'block_modlos');
		$lastname_ttl		= get_string('modlos_lastname',		 'block_modlos');
		$passwd_ttl			= get_string('modlos_password',		 'block_modlos');
		$confirm_pass_ttl	= get_string('modlos_confirm_pass',  'block_modlos');
		$home_region_ttl	= get_string('modlos_home_region',	 'block_modlos');
		$ownername_ttl		= get_string('modlos_ownername',	 'block_modlos');
		$create_ttl			= get_string('modlos_create_ttl',	 'block_modlos');
		$reset_ttl			= get_string('modlos_reset_ttl',	 'block_modlos');
		$avatar_created		= get_string('modlos_avatar_created','block_modlos');
		$sloodle_ttl 		= get_string('modlos_sloodle_ttl',	 'block_modlos');
		$manage_sloodle		= get_string('modlos_manage_sloodle','block_modlos');

		$disclaimer_ttl		= get_string('modlos_disclaimer',  	 'block_modlos');
		$disclaim_agree		= get_string('modlos_disclaimer_agree','block_modlos');
		$disclaim_need_agree= get_string('modlos_need_agree_disclaimer','block_modlos');

		// 
		$pv_ownername = $this->ownername;
		if ($this->created_avatar) {
			$pv_firstname = '';
			$pv_lastname  = '';
		}
		else {
			$pv_firstname = $this->firstname;
			$pv_lastname  = $this->lastname;
		}

		include(CMS_MODULE_PATH.'/html/create.html');
	}



	function create_avatar()
	{
		global $USER;

		// User Check
		$avuuid = opensim_get_avatar_uuid($this->firstname.' '.$this->lastname);
		if ($avuuid!=null) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_already_name_error', 'block_modlos')." ($this->firstname $this->lastname)";
			return false;
		}

		// Create UUID
		if (!$this->hasPermit or !isGUID($this->UUID)) {
			do {
				$uuid   = make_random_guid();
				$modobj = modlos_get_avatar_info($uuid);
			} while ($modobj!=null);
			$this->UUID = $uuid;
		}

		// OpenSim DB
		$rslt = opensim_create_avatar($this->UUID, $this->firstname, $this->lastname, $this->passwd, $this->hmregion);
		if (!$rslt) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_opensim_create_error', 'block_modlos')." ($this->UUID)";
			return false;
		}

		// User ID of Moodle
		if ($this->hasPermit) {
			if ($this->ownername!='') {
				$names = get_names_from_display_username($this->ownername);
				$user_info = get_userinfo_by_name($names['firstname'], $names['lastname']);
				if ($user_info==null) {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_ownername', 'block_modlos').' ('.$this->ownername.')';
					$this->errorMsg[] = get_string('modlos_nouser_found', 'block_modlos').' ('.$names['firstname'].' '.$names['lastname'].')';
					$this->ownername = '';
					$this->uid = '0';
					//return false;
				}
				else $this->uid = $user_info->id;
			}
			else $this->uid = '0';
		}
		else $this->uid = $USER->id;

		// Sloodle
		$state   = AVATAR_STATE_SYNCDB;
		$sloodle = optional_param('sloodle', '', PARAM_ALPHA);
		if ($sloodle!='' and $this->use_sloodle) $state = (int$state | AVATAR_STATE_SLOODLE;


		$new_user['UUID']		= $this->UUID;
		$new_user['uid']		= $this->uid;
		$new_user['firstname'] 	= $this->firstname;
		$new_user['lastname']  	= $this->lastname;
		$new_user['hmregion']  	= $this->hmregion;
		$new_user['state']	 	= $state;

		$ret = modlos_set_avatar_info($new_user, $this->use_sloodle);
		return $ret;
	}

}

?>
