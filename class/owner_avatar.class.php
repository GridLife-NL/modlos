<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  OwnerAvatar
{
	var $hashPermit = false;
	var $module_url = ""
	var $action_url = "";
	var $return_url = "";

	var $updated_owner = false;

	var $course_id	= 0;
	var $use_sloodle= false;
	var $pri_sloodle= false;

	var $hasError	= false;
	var $errorMsg	= array();

	// Moodle DB
	var $avatar	 	= null;
	var $UUID		= "";
	var $uid		= "";
var $firstname  = "";
var $lastname	= "";
	var $state		= "";
var $passwd	 	= "";
var $ownername 	= "";


	function  OwnerAvatar($course_id) 
	{
		global $CFG, $USER;

		require_login($course_id);

		// for HTTPS
		$use_https = $CFG->mdlopnsm_use_https;
		if ($use_https) {
			$https_url = $CFG->mdlopnsm_https_url;

			if ($https_url!="") {
				$this->module_url = $https_url.CMS_DIR_NAME;
			}
			else {
				$this->module_url = ereg_replace('^http:', 'https:', CMS_MODULE_URL);
			}
		}
		else {
			$this->module_url = CMS_MODULE_URL;
		}

		$course_param 		= "?course=".$course_id;
		$this->course_id		= $course_id;
		$this->hasPermit		= hasPsermit($course_id);
		$this->action_url  	= $this->module_url."/actions/owner_avatar.php";
		$this->return_url  	= CMS_MODULE_URL."/actions/avatars_list.php".$course_param;
		$this->use_sloodle 	= $CFG->mdlopnsm_cooperate_sloodle;
		$this->pri_sloodle 	= $CFG->mdlopnsm_priority_sloodle;

		// Number of Avatars Check
		if (!$this->hasPermit) {
			$avatars_num = mdlopensim_get_avatars_num($USER->id);
			$max_avatars = $CFG->mdlopnsm_max_own_avatars;
			if ($max_avatars>=0 and $avatars_num>=$max_avatars) {
				error(get_string('mdlos_over_max_avatars', 'block_mdlopensim')." ($avatars_num >= $max_avatars)", $this->return_url);
			}
		}

		// get UUID from POST or GET
		$this->UUID = optional_param('uuid', '', PARAM_TEXT);
		if (!isGUID($this->UUID)) {
			error(get_string('mdlos_invalid_uuid', 'block_mdlopensim')." ($this->UUID)", $this->return_url);
		}

		// check Mdlopensim DB
		$avatar = mdlopensim_get_avatar_info($this->UUID);
		if ($avatar==null) {
			error(get_string('mdlos_not_exist_uuid', 'block_mdlopensim')." ($this->UUID)", $this->return_url);
		}
		$this->uid = $avatar['uid'];
		if ($uid!=0) {
			error(get_string('mdlos_owner_forbidden', 'block_mdlopensim'), $this->return_url);
		}
		$this->state = $avatar['state'];
		if ($state!=AVATAR_STATE_ACTIVE) {
			error(get_string('mdlos_owner_forbidden', 'block_mdlopensim'), $this->return_url);
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
				 $this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
				 return false;
			}

			if (xoops_getenv("REQUEST_METHOD")=="POST" and  !$this->mActionForm->hasError()) {
				$this->passwd = $this->mActionForm->get('passwd');
				$postuid = $this->mActionForm->get('userid');
				$this->updated_owner = $this->updateOwner($postuid);
			}
		}
	}



	function  print_page() 
	{
		global $CFG;

		$this->execute();

		$grid_name = $CFG->mdlopnsm_grid_name;

		$avatar_edit		= get_string('mdlos_avatar_edit',	'block_mdlopensim');
		$firstname_ttl	  = get_string('mdlos_firstname',	 'block_mdlopensim');
		$lastname_ttl		= get_string('mdlos_lastname',	  'block_mdlopensim');
		$passwd_ttl		 = get_string('mdlos_password',	  'block_mdlopensim');
		$confirm_pass_ttl	= get_string('mdlos_confirm_pass',  'block_mdlopensim');
		$home_region_ttl	= get_string('mdlos_home_region',	'block_mdlopensim');
		$status_ttl		 = get_string('mdlos_status',		'block_mdlopensim');
		$active_ttl		 = get_string('mdlos_active',		'block_mdlopensim');
		$inactive_ttl		= get_string('mdlos_inactive',	  'block_mdlopensim');
		$owner_ttl		  = get_string('mdlos_owner',		 'block_mdlopensim');
		$ownername_ttl	  = get_string('mdlos_ownername',	 'block_mdlopensim');
		$update_ttl		 = get_string('mdlos_update_ttl',	'block_mdlopensim');
		$delete_ttl		 = get_string('mdlos_delete_ttl',	'block_mdlopensim');
		$reset_ttl		  = get_string('mdlos_reset_ttl',	 'block_mdlopensim');
		$avatar_updated	 = get_string('mdlos_avatar_updated','block_mdlopensim');
		$uuid_ttl			= get_string('mdlos_uuid',		  'block_mdlopensim');

		include(CMS_MODULE_PATH."/html/owner.html");
	}



	function updateOwner($uid)
	{
		if ($uid==0) return false;
		if ($uid!=$this->userid) {
			$this->mActionForm->addErrorMessage(_MD_XPNSM_MISMATCH_UID);
			return false;
		}

		$passwd = opensim_get_password($this->UUID);

		$chkpass = md5($this->passwd);
		if ($passwd['passwordSalt']=="") {
			if ($chkpass!=$passwd['passwordHash']) {
				$this->mActionForm->addErrorMessage(_MD_XPNSM_MISMATCH_PASSWD);
				return false;
			}
		}
		else {
			$chkpass = md5($chkpass.":".$passwd['passwordSalt']);
			if ($chkpass!=$passwd['passwordHash']) {
				$this->mActionForm->addErrorMessage(_MD_XPNSM_MISMATCH_PASSWD);
				return false;
			}
		}

		$this->avatar->assignVar('uid',  $this->userid);
		$this->avatar->assignVar('time', time());
		$this->dbhandler->insert($this->avatar);
		return true;
	}

}

?>
