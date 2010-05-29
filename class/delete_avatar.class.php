<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  DeleteAvatar
{
	var $hasPermit	= false;
	var $action_url = "";
	var $cancel_url = "";
	var $return_url = "";
	var $deleted_avatar = false;

	var $course_id	= 0;

	var $hasError  	= false;
	var $errorMsg  	= array();

	// Moodle DB
	var $avatar	   	= null;
	var $UUID	   	= "";
	var $uid 	   	= 0;			// owner of avatar
	var $firstname 	= "";
	var $lastname  	= "";
	var $hmregion  	= "";
	var $state	   	= -1;
	var $ownername 	= "";



	function  DeleteAvatar($course_id) 
	{
		global $CFG, $USER;

		require_login($course_id);

		$this->course_param = "?course=".$course_id;
		$this->course_id  = $course_id;
		$this->action_url = CMS_MODULE_URL."/actions/delete_avatar.php".$course_param;
		$this->cancel_url = CMS_MODULE_URL."/actions/avatars_list.php". $course_param;
		$this->return_url = CMS_MODULE_URL."/actions/avatars_list.php". $course_param;


		// get UUID from POST or GET
		$uuid = optional_param('uuid', '', PARAM_TEXT);
		if (!isGUID($uuid)) {
			error(get_string('mdlos_invalid_uuid', 'block_mdlopensim')." ($uuid)", $this->return_url);
		}
		$this->UUID	= $uuid;

		// get uid from Mdlopensim DB
		$avatar = get_record('mdlos_users', 'uuid', $this->UUID);
		$this->uid	  = $avatar->user_id;				// uid of avatar in editing from DB
		$this->state  = $avatar->state;
		$this->avatar = $avatar;

		$this->hasPermit = hasPermit();
		if (!$this->hasPermit and $USER->id!=$this->uid) {
			error(get_string('mdlos_access_forbidden', 'block_mdlopensim'), $this->return_url);
		}
		if ($this->state==AVATAR_STATE_ACTIVE) {
			error(get_string('mdlos_active_avatar', 'block_mdlopensim'),  $this->return_url);
		}
	}



	function  execute()
	{
		$this->firstname  = $this->avatar->firstname;
		$this->lastname   = $this->avatar->lastname;

		if (data_submitted()) {
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
				return false;
			}

			$del = optional_param('submit_delete', '', PARAM_TEXT);
			if ($del!="") {
				$this->deleted_avatar = $this->del_avatar();
			}
			else {
				redirect($this->cancel_url, get_string('mdlos_avatar_dlt_canceled', 'block_mdlopensim'), 2);
			}
		}
	}



	function  print_page() 
	{
		global $CFG;

		$this->execute();

		$grid_name = $CFG->mdlopnsm_grid_name;

		$avatar_delete_ttl	= get_string("mdlos_avatar_delete",  	"block_mdlopensim");
		$firstname_ttl		= get_string("mdlos_firstname",  		"block_mdlopensim");
		$lastname_ttl		= get_string("mdlos_lastname",  		"block_mdlopensim");
		$home_region_ttl	= get_string("mdlos_home_region",  		"block_mdlopensim");
		$status_ttl			= get_string("mdlos_status", 	 		"block_mdlopensim");
		$not_syncdb_ttl 	= get_string("mdlos_not_syncdb",		"block_mdlopensim");
		$active_ttl			= get_string("mdlos_active",			"block_mdlopensim");
		$inactive_ttl		= get_string("mdlos_inactive",			"block_mdlopensim");
		$unknown_status		= get_string("mdlos_unknown_status",	"block_mdlopensim");
		$ownername_ttl		= get_string("mdlos_ownername",			"block_mdlopensim");
		$delete_ttl			= get_string("mdlos_delete_ttl",		"block_mdlopensim");
		$cancel_ttl			= get_string("mdlos_cancel_ttl",		"block_mdlopensim");
		$return_ttl			= get_string("mdlos_return_ttl",		"block_mdlopensim");
		$avatar_deleted		= get_string("mdlos_avatar_deleted", 	"block_mdlopensim");
		$avatar_dlt_confrm	= get_string("mdlos_avatar_dlt_confrm", "block_mdlopensim");

		include(CMS_MODULE_PATH."/html/delete.html");
	}




	function del_avatar()
	{
		global $CFG;

		if (!isGUID($this->UUID)) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_invalid_uuid", "block_mdlopensim");
			return false;
		}

		// Moodle DB
		$delete_user['UUID']  = $this->UUID;
		$delete_user['state'] = $this->state;

		$ret = mdlopensim_delete_usertable($delete_user);
		if (!$ret) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_user_delete_error", "block_mdlopensim");
		}

		mdlopensim_delete_banneddb($this->UUID);
		mdlopensim_delete_groupdb ($this->UUID, false);
		mdlopensim_delete_profiles($this->UUID);

		// OpenSim
		$ret = opensim_delete_avatar($this->UUID);
		if (!$ret) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_opensim_delete_error", "block_mdlopensim");
		}

		// Sloodle
		if ($CFG->mdlopnsm_cooperate_sloodle) {
			$ret = delete_records(MDL_SLOODLE_USERS_TBL, 'uuid', $this->UUID);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sloodle_delete_error", "block_mdlopensim");
			}
		}

		if ($this->hasError) return false;
		return true;
	}
}

?>
