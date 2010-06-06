<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  OwnerAvatar
{
	var $hashPermit = false;
	var $action_url = '';
	var $return_url = '';
	var $updated_owner = false;

	var $course_id	= 0;
	var $user_id	= 0;

	var $use_sloodle = false;
	var $avatars_num = 0;
	var $max_avatars = 0;
	var $isAvatarMax = false;

	var $hasError	= false;
	var $errorMsg	= array();

	// Moodle DB
	var $avatar	 	= null;
	var $UUID		= '';
	var $firstname	= '';
	var $lastname	= '';
	var $passwd	 	= '';
	var $ownername 	= '';


	function  OwnerAvatar($course_id) 
	{
		global $CFG, $USER;

		require_login($course_id);

		// for HTTPS
		$use_https = $CFG->modlos_use_https;
		if ($use_https) {
			$https_url = $CFG->modlos_https_url;
			if ($https_url!='') $module_url = $https_url.CMS_DIR_NAME;
			else 				$module_url = ereg_replace('^http:', 'https:', CMS_MODULE_URL);
		}
		else $module_url = CMS_MODULE_URL;

		//
		$course_param 	   = '?course='.$course_id;
		$this->return_url  = CMS_MODULE_URL.'/actions/avatars_list.php'.$course_param;
		$this->course_id   = $course_id;
		$this->hasPermit   = hasPermit($course_id);
		$this->action_url  = $module_url.'/actions/owner_avatar.php';
		$this->use_sloodle = $CFG->modlos_cooperate_sloodle;
		$this->user_id	   = $USER->id;
		$this->ownername   = get_display_username($USER->firstname, $USER->lastname);

		// Number of Avatars Check
		$this->avatars_num = modlos_get_avatars_num($USER->id);
		$this->max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $this->max_avatars>=0 and $this->avatars_num>=$this->max_avatars) $this->isAvatarMax = true;

		if ($isAvatarMax) {
			error(get_string('modlos_over_max_avatars', 'block_modlos')." ($this->avatars_num >= $this->max_avatars)", $this->return_url);
		}


		// get UUID from POST or GET
		$this->UUID = optional_param('uuid', '', PARAM_TEXT);
		if (!isGUID($this->UUID)) {
			error(get_string('modlos_invalid_uuid', 'block_modlos')." ($this->UUID)", $this->return_url);
		}

		// check Modlos DB
		$avatar = modlos_get_avatar_info($this->UUID);
		if ($avatar==null) {
			error(get_string('modlos_not_exist_uuid', 'block_modlos')." ($this->UUID)", $this->return_url);
		}
		if ($avatar['uid']!=0) {
			error(get_string('modlos_owner_forbidden', 'block_modlos').' (User ID is not 0)', $this->return_url);
		}
		if (!($avatar['state']&AVATAR_STATE_SYNCDB)) {
			error(get_string('modlos_owner_forbidden', 'block_modlos').' (not Acrive)', $this->return_url);
		}
		$this->firstname = $avatar['firstname'];
		$this->lastname  = $avatar['lastname'];

		// get User Info from Moodle DB
		$this->avatar = $avatar;
	}



	function  execute()
	{
		if (data_submitted()) {
			if (!confirm_sesskey()) {
				 $this->hasError = true;
				 $this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
			}

			$this->passwd  = optional_param('passwd', '', PARAM_TEXT);
			if (!isAlphabetNumericSpecial($this->passwd)) {
				 $this->hasError = true;
				 $this->errorMsg[] = get_string('modlos_invalid_passwd', 'block_modlos')." ($this->passwd)";
			}
			$posted_uid = optional_param('userid', '', PARAM_INT);
			if (!isNumeric($posted_uid)) {
				 $this->hasError = true;
				 $this->errorMsg[] = get_string('modlos_invalid_uid', 'block_modlos')." ($posted_uid)";
			}
			if ($this->hasError) return false;

			/////
			$this->updated_owner = $this->updateOwner($posted_uid);
			if (!$this->updated_owner) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_avatar_gotted_error', 'block_modlos');
				return false;
			}
		}
		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name 	  = $CFG->modlos_grid_name;
		$showPostForm = !$this->updated_owner or $this->hasError;

		$avatar_own_ttl	= get_string('modlos_avatar_own',	 'block_modlos');
		$firstname_ttl	= get_string('modlos_firstname',	 'block_modlos');
		$lastname_ttl	= get_string('modlos_lastname',		 'block_modlos');
		$passwd_ttl		= get_string('modlos_password',	  	 'block_modlos');
		$avatar_own_ttl	= get_string('modlos_avatar_own_ttl','block_modlos');
		$reset_ttl		= get_string('modlos_reset_ttl',	 'block_modlos');
		$return_ttl		= get_string('modlos_return_ttl',	 'block_modlos');
		$uuid_ttl		= get_string('modlos_uuid',			 'block_modlos');
		$ownername_ttl	= get_string('modlos_ownername',	 'block_modlos');
		$avatar_get		= get_string('modlos_avatar_gotted', 'block_modlos');

		include(CMS_MODULE_PATH.'/html/owner.html');
	}



	function updateOwner($posted_uid)
	{
		if ($posted_uid==0) return false;
		if ($posted_uid!=$this->user_id) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_mismatch_uid', 'block_modlos')." ($posted_uid != $this->user_id)";
			return false;
		}

		$passwd = opensim_get_password($this->UUID);

		$chkpass = md5($this->passwd);
		if ($passwd['passwordSalt']=='') {
			if ($chkpass!=$passwd['passwordHash']) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_mismatch_passwd', 'block_modlos').' (....passwordSalt is null)';
				return false;
			}
		}
		else {
			$chkpass = md5($chkpass.':'.$passwd['passwordSalt']);
			if ($chkpass!=$passwd['passwordHash']) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_mismatch_passwd', 'block_modlos');
				return false;
			}
		}

		$this->avatar['uid']  = $this->user_id;
		$this->avatar['time'] = time();

		/////
		$ret = modlos_set_avatar_info($this->avatar, $this->use_sloodle);
		return $ret;
	}

}

?>
