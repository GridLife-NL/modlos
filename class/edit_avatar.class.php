<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');




class  EditAvatar
{
	var $regionNames = array();

	var $hasPermit	= false;
	var $isGuest	= true;
	var $action_url = '';
	var $delete_url = '';
	var $course_id	= 0;

	var $updated_avatar = false;

	var $use_sloodle = false;
	var $avatars_num = false;
	var $max_avatars = false;
	var $isAvatarMax = false;

	var $hasError	= false;
	var $errorMsg 	= array();

	// Moodle DB
	var $avatar		= null;
	var $UUID		= '';
	var $uid 	   	= 0;
	var $firstname 	= '';
	var $lastname  	= '';
	var $passwd  	= '';
	var $hmregion  	= '';
	var $state	 	= 0;
	var $ostate		= 0;
	var $ownername 	= '';



	function  EditAvatar($course_id) 
	{
		global $CFG, $USER;

		// for Guest
		$this->isGuest = isguestuser();
		if ($this->isGuest) {
			error(get_string('modlos_access_forbidden', 'block_modlos'), CMS_MODULE_URL);
		}

		// for HTTPS
		$use_https = $CFG->modlos_use_https;
		if ($use_https) {
			$https_url = $CFG->modlos_https_url;
			if ($https_url!='') $module_url = $https_url.'/'.CMS_DIR_NAME;
			else 				$module_url = preg_replace('/^http:/', 'https:', CMS_MODULE_URL);
		}
		else $module_url = CMS_MODULE_URL;

		//
		$course_param = '?course='.$course_id;
		$this->course_id  = $course_id;
		$this->action_url = $module_url.'/actions/edit_avatar.php';
		$this->delete_url = CMS_MODULE_URL.'/actions/delete_avatar.php'.$course_param;


		// get UUID from POST or GET
		$return_url = CMS_MODULE_URL.'/actions/avatars_list.php'. $course_param;
		$uuid = optional_param('uuid', '', PARAM_TEXT);
		if (!isGUID($uuid)) {
			error(get_string('modlos_invalid_uuid', 'block_modlos')." ($uuid)", $return_url);
		}
		$this->UUID = $uuid;
		$this->use_sloodle = $CFG->modlos_cooperate_sloodle;


		// get uid from Modlos and Sloodle DB
		$avatar = modlos_get_avatar_info($this->UUID, $this->use_sloodle);
		$this->uid	  	= $avatar['uid'];
		$this->ostate 	= (int)$avatar['state'];
		$this->firstname= $avatar['firstname'];
		$this->lastname = $avatar['lastname'];
		$this->avatar 	= $avatar;


		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit and $USER->id!=$this->uid) {
			error(get_string('modlos_access_forbidden', 'block_modlos'), $return_url);
		}

		$this->avatars_num = modlos_get_avatars_num($USER->id);
		$this->max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;
	}



	function  execute()
	{
		global $USER;

		// OpenSim DB
		$this->regionNames = opensim_get_regions_names('ORDER BY regionName ASC');

		// Form
		if (data_submitted()) {
			if (!confirm_sesskey()) { 
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
			}

			// Delete Avatar
			$del = optional_param('submit_delete', '', PARAM_TEXT);
			if ($del!='') {
				redirect($this->delete_url.'&amp;uuid='.$this->UUID, 'Please wait....', 0);
				exit('<h4>delete page open error!!</h4>');
			}

			// Sate (Active/Inactive)
			$state 	= optional_param('state', '', PARAM_INT);
			if ($state>0x80) $this->state = $this->ostate & $state;
			else 			 $this->state = $this->ostate | $state;

			// Sloodle
			$sloodle = optional_param('sloodle', '', PARAM_ALPHA);
			if ($sloodle!='') $this->state |= AVATAR_STATE_SLOODLE;
			else			  $this->state &= AVATAR_STATE_NOSLOODLE;

			//
			$this->hmregion = optional_param('hmregion', '', PARAM_TEXT);

			// password
			$confirm_pass = optional_param('confirm_pass','', PARAM_TEXT);
			$this->passwd = optional_param('passwd',   '', PARAM_TEXT);
			if ($this->passwd!=$confirm_pass) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_passwd_mismatch', 'block_modlos');
			}
			if ($this->passwd!='' and strlen($this->passwd)<AVATAR_PASSWD_MINLEN) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_passwd_minlength', 'block_modlos').' ('.AVATAR_PASSWD_MINLEN.')';
			}


			// Owner Name
			if ($this->hasPermit) {		// for admin
				$this->ownername = optional_param('ownername', '', PARAM_TEXT);
				if ($this->ownername!='') {
					$names = get_names_from_display_username($this->ownername);
					$user_info = get_userinfo_by_name($names['firstname'], $names['lastname']);				
					if ($user_info!=null) {
						$this->uid = $user_info->id;
					}
					else {
						$this->hasError = true;
						$this->errorMsg[] = get_string('modlos_ownername', 'block_modlos').' ('.$this->ownername.')';
						$this->errorMsg[] = get_string('modlos_nouser_found', 'block_modlos').' ('.$names['firstname'].' '.$names['lastname'].')';
						$this->ownername = get_display_username($USER->firstname, $USER->lastname);
						$this->uid = $USER->id;
					}
				}
				else {
					$this->uid = '0';
				}
			}
			else {	// user
				$nomanage = optional_param('nomanage', '', PARAM_ALPHA);
				if ($nomanage=='') {
					$this->ownername = get_display_username($USER->firstname, $USER->lastname);
					$this->uid = $USER->id;
				}
				else {
					$this->ownername = '';
					$this->uid = '0';
				}
			}


			// Home Region
 			$region_uuid = opensim_get_region_uuid($this->hmregion);
			if ($region_uuid==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_invalid_regionname', 'block_modlos')." ($this->hmregion)";
			}

			if ($this->hasError) return false;


			//////////
			$this->updated_avatar = $this->updateAvatar();
			if (!$this->updated_avatar) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_update_error', 'block_modlos');
				return false;
			}
		}

		// GET
		else {
			$this->passwd	= '';
			$this->hmregion = $this->avatar['hmregion'];
			$this->state  	= $this->avatar['state'];

			if ($this->hasPermit and $this->uid>0) {
				$user_info = get_userinfo_by_id($this->uid);
				$this->ownername = get_display_username($user_info->firstname, $user_info->lastname);
			}
			else $this->ownername = get_display_username($USER->firstname, $USER->lastname);
		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name = $CFG->modlos_grid_name;

		$avatar_edit   		= get_string('modlos_avatar_edit',	 'block_modlos');
		$firstname_ttl  	= get_string('modlos_firstname',	 'block_modlos');
		$lastname_ttl   	= get_string('modlos_lastname',		 'block_modlos');
		$passwd_ttl  		= get_string('modlos_password',	 	 'block_modlos');
		$confirm_pass_ttl  	= get_string('modlos_confirm_pass',	 'block_modlos');
		$home_region_ttl  	= get_string('modlos_home_region',	 'block_modlos');
		$status_ttl	 		= get_string('modlos_status',		 'block_modlos');
		$active_ttl	 		= get_string('modlos_active',		 'block_modlos');
		$inactive_ttl   	= get_string('modlos_inactive',	  	 'block_modlos');
		$owner_ttl	  		= get_string('modlos_owner',		 'block_modlos');
		$ownername_ttl	  	= get_string('modlos_ownername',	 'block_modlos');
		$update_ttl	  		= get_string('modlos_update_ttl', 	 'block_modlos');
		$delete_ttl	  		= get_string('modlos_delete', 	 	 'block_modlos');
		$reset_ttl	  		= get_string('modlos_reset_ttl', 	 'block_modlos');
		$avatar_updated	  	= get_string('modlos_avatar_updated','block_modlos');
		$uuid_ttl	  		= get_string('modlos_uuid',			 'block_modlos');
		$manage_avatar_ttl	= get_string('modlos_manage_avatar', 'block_modlos');
		$manage_out			= get_string('modlos_manage_out',	 'block_modlos');
		$sloodle_ttl		= get_string('modlos_sloodle_ttl',	 'block_modlos');
		$manage_sloodle		= get_string('modlos_manage_sloodle','block_modlos');

		include(CMS_MODULE_PATH.'/html/edit.html');
	}



	function updateAvatar()
	{
		// Update password of OpenSim DB
		if ($this->passwd!='') {
			$passwdsalt = make_random_hash();
			$passwdhash = md5(md5($this->passwd).':'.$passwdsalt);

			$ret = opensim_set_password($this->UUID, $passwdhash, $passwdsalt);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_passwd_update_error', 'block_modlos');
				return false;
			}
		}

		// update Home Region
		if ($this->hmregion!='') {
			$ret = opensim_set_home_region($this->UUID, $this->hmregion);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_hmrgn_update_error', 'block_modlos');
				return false;
			}
		}

		// State
		if ($this->state!=$this->ostate) {
			// Avtive -> InAcvtive
			if (!($this->ostate&AVATAR_STATE_INACTIVE) and $this->state&AVATAR_STATE_INACTIVE) {
				$ret = modlos_inactivate_avatar($this->UUID);
				if (!$ret) {
					$this->state &= AVATAR_STATE_ACTIVE;
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_inactivate_error', 'block_modlos');
					return false;
				}
			}
			// InActive -> Acvtive
			elseif ($this->ostate&AVATAR_STATE_INACTIVE and !($this->state&AVATAR_STATE_INACTIVE)) {
				$ret = modlos_activate_avatar($this->UUID);
				if (!$ret) {
					$this->state |= AVATAR_STATE_INACTIVE;
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_activate_error', 'block_modlos');
					return false;
				}
			}
		}


		// Modlos and Sloodle DB
		$update_user['id']	  	  = $this->avatar['id'];
		$update_user['UUID']	  = $this->UUID;
		$update_user['uid']		  = $this->uid;
		$update_user['firstname'] = $this->firstname;
		$update_user['lastname']  = $this->lastname;
		$update_user['hmregion']  = $this->hmregion;
		$update_user['state']	  = $this->state;
		$update_user['time']	  = time();

		$ret = modlos_set_avatar_info($update_user, $this->use_sloodle);
		return $ret;
	}

}

?>
