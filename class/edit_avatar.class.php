<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");




class  EditAvatar
{
	var $regionNames = array();

	var $hasPermit	= false;
	var $action_url = "";
	var $delete_url = "";

	var $updated_avatar = false;
	var $course_id	= 0;
	var $use_sloodle= false;
	var $pri_sloodle= false;

	var $hasError	= false;
	var $errorMsg 	= array();

	// Moodle DB
	var $avatar		= null;
	var $UUID		= "";
	var $uid 	   	= 0;
	var $firstname 	= "";
	var $lastname  	= "";
	var $passwd  	= "";
	var $hmregion  	= "";
	var $state	 	= 0;
	var $ostate		= 0;
	var $ownername 	= "";



	function  EditAvatar($course_id) 
	{
		global $CFG, $USER;

		require_login($course_id);

		// for HTTPS
		$use_https = $CFG->mdlopnsm_use_https;
		if ($use_https) {
			$https_url = $CFG->mdlopnsm_https_url;
			if ($https_url!="") $module_url = $https_url."/".CMS_DIR_NAME;
			else 				$module_url = ereg_replace('^http:', 'https:', CMS_MODULE_URL);
		}
		else $module_url = CMS_MODULE_URL;

		//
		$course_param = "?course=".$course_id;
		$this->course_id  = $course_id;
		$this->action_url = $module_url."/actions/edit_avatar.php";
		$this->delete_url = CMS_MODULE_URL."/actions/delete_avatar.php".$course_param;


		// get UUID from POST or GET
		$return_url = CMS_MODULE_URL."/actions/avatars_list.php". $course_param;
		$uuid = optional_param('uuid', '', PARAM_TEXT);
		if (!isGUID($uuid)) {
			error(get_string('mdlos_invalid_uuid', 'block_mdlopensim')." ($uuid)", $return_url);
		}
		$this->UUID = $uuid;


		$this->use_sloodle = $CFG->mdlopnsm_cooperate_sloodle;
		$this->pri_sloodle = $CFG->mdlopnsm_priority_sloodle;

		// get uid from Mdlopensim and Sloodle DB
		$avatar = mdlopensim_get_avatar_info($this->UUID, $this->use_sloodle, $this->pri_sloodle);
		$this->uid	  	= $avatar['uid'];
		$this->ostate 	= $avatar['state'];
		$this->firstname= $avatar['firstname'];
		$this->lastname = $avatar['lastname'];
		$this->avatar 	= $avatar;


		$this->hasPermit = hasPermit($course_id);
		if (!$this->hasPermit and $USER->id!=$this->uid) {
			error(get_string('mdlos_access_forbidden', 'block_mdlopensim'), $return_url);
		}
	}



	function  execute()
	{
		global $USER;

		// OpenSim DB
		$this->regionNames = opensim_get_regions_names("ORDER BY regionName ASC");

		// Form
		if (data_submitted()) {
			if (!confirm_sesskey()) { 
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
			}

			// Delete Avatar
			$del = optional_param('submit_delete', '', PARAM_TEXT);
			if ($del!="") redirect($this->delete_url."&amp;uuid=".$this->UUID, "Please wait....", 0);
			

			// Sate (Active/Inactive)
			$state 	= optional_param('state', '', PARAM_INT);
			if ($state>0x80) $this->state = $this->ostate & ($state^0x7f);
			else 			 $this->state = $this->ostate | $state;

			// Sloodle
			$sloodle = optional_param('sloodle', '', PARAM_ALPHA);
			if ($sloodle!="") $this->state = $this->state | AVATAR_STATE_SLOODLE;
			else			  $this->state = $this->state & (AVATAR_STATE_SLOODLE^0xff);

			//
			$this->hmregion = optional_param('hmregion', '', PARAM_TEXT);

			// password
			$confirm_pass = optional_param('confirm_pass','', PARAM_TEXT);
			$this->passwd = optional_param('passwd',   '', PARAM_TEXT);
			if ($this->passwd!=$confirm_pass) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_passwd_mismatch", "block_mdlopensim");
			}
			if ($this->passwd!="" and strlen($this->passwd)<AVATAR_PASSWD_MINLEN) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_passwd_minlength", "block_mdlopensim")." (".AVATAR_PASSWD_MINLEN.")";
			}


			// Owner Name
			if ($this->hasPermit) {		// for admin
				$this->ownername = optional_param('ownername', '', PARAM_TEXT);
				if ($this->ownername!="") {
					$names = get_names_from_display_username($this->ownername);
					$user_info = get_userinfo_by_name($names['firstname'], $names['lastname']);				
					if ($user_info!=null) {
						$this->uid = $user_info->id;
					}
					else {
						$this->hasError = true;
						$this->errorMsg[] = get_string("mdlos_nouser_found", "block_mdlopensim")." (".$names['firstname']." ".$names['lastname'].")";
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
				if ($nomanage=="") {
					$this->ownername = get_display_username($USER->firstname, $USER->lastname);
					$this->uid = $USER->id;
				}
				else {
					$this->ownername = "";
					$this->uid = '0';
				}
			}


			// Home Region
 			$region_uuid = opensim_get_region_uuid($this->hmregion);
			if ($region_uuid==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_invalid_regionname'", "block_mdlopensim")." ($this->hmregion)";
			}

			if ($this->hasError) return false;


			//////////
			$this->updated_avatar = $this->updateAvatar();
			if (!$this->updated_avatar) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_update_error", "block_mdlopensim");
				return false;
			}
		}
		else {
			$this->passwd	= "";
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

		$grid_name = $CFG->mdlopnsm_grid_name;

		$avatar_edit   		= get_string('mdlos_avatar_edit',	'block_mdlopensim');
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
		$uuid_ttl	  		= get_string('mdlos_uuid',			'block_mdlopensim');
		$manage_avatar_ttl	= get_string('mdlos_manage_avatar',	'block_mdlopensim');
		$manage_out			= get_string('mdlos_manage_out',	'block_mdlopensim');
		$sloodle_ttl		= get_string('mdlos_sloodle_ttl',	'block_mdlopensim');
		$manage_sloodle		= get_string('mdlos_manage_sloodle','block_mdlopensim');

		include(CMS_MODULE_PATH."/html/edit.html");
	}



	function updateAvatar()
	{
		// Update password of OpenSim DB
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

		// update Home Region
		if ($this->hmregion!="") {
			$ret = opensim_set_home_region($this->UUID, $this->hmregion);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_hmrgn_update_error", "block_mdlopensim");
				return false;
			}
		}

		// State
		if ($this->state!=$this->ostate) {
			// Avtive -> InAcvtive
			if (!($this->ostate&AVATAR_STATE_INACTIVE) and $this->state&AVATAR_STATE_INACTIVE) {
				$ret = mdlopensim_inactivate_avatar($this->UUID);
				if (!$ret) {
					$this->hasError = true;
					$this->errorMsg[] = get_string("mdlos_inactivate_error", "block_mdlopensim");
					return false;
				}
			}
			// InActive -> Acvtive
			elseif ($this->ostate&AVATAR_STATE_INACTIVE and !($this->state&AVATAR_STATE_INACTIVE)) {
				$ret = mdlopensim_activate_avatar($this->UUID);
				if (!$ret) {
					$this->hasError = true;
					$this->errorMsg[] = get_string("mdlos_activate_error", "block_mdlopensim");
					return false;
				}
			}
		}

		// Mdlopensim and Sloodle DB
		$update_user['id']	  	  = $this->avatar['id'];
		$update_user['UUID']	  = $this->UUID;
		$update_user['uid']		  = $this->uid;
		$update_user['firstname'] = $this->firstname;
		$update_user['lastname']  = $this->lastname;
		$update_user['hmregion']  = $this->hmregion;
		$update_user['state']	  = $this->state;
		$update_user['time']	  = time();

		$ret = mdlopensim_set_avatar_info($update_user, $this->use_sloodle);
		return $ret;
	}

}

?>
