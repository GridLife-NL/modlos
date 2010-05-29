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
	var $use_sloodle= false;
	var $pri_sloodle= false;

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

		$course_param = "?course=".$course_id;
		$this->course_id  = $course_id;
		$this->action_url = CMS_MODULE_URL."/actions/delete_avatar.php";
		$this->cancel_url = CMS_MODULE_URL."/actions/avatars_list.php". $course_param;
		$this->return_url = CMS_MODULE_URL."/actions/avatars_list.php". $course_param;


		// get UUID from POST or GET
		$uuid = optional_param('uuid', '', PARAM_TEXT);
		if (!isGUID($uuid)) {
			error(get_string('mdlos_invalid_uuid', 'block_mdlopensim')." ($uuid)", $this->return_url);
		}
		$this->UUID	= $uuid;


		$this->use_sloodle = $CFG->mdlopnsm_cooperate_sloodle;
		$this->pri_sloodle = $CFG->mdlopnsm_priority_sloodle;

		// get uid from Mdlopensim and Sloodle DB
		$avatar = mdlopensim_get_avatar_info($this->UUID, $this->use_sloodle, $this->pri_sloodle);
		$this->uid	  	= $avatar['uid'];
		$this->state  	= $avatar['state'];
		$this->hmregion = $avatar['hmregion'];
		$this->firstname= $avatar['firstname'];
		$this->lastname = $avatar['lastname'];
		$this->avatar 	= $avatar;

		$user_info = get_userinfo_by_id($this->uid);
		if ($user_info!=null) {
			$this->ownername = get_display_username($user_info->firstname, $user_info->lastname);
		}

		$this->hasPermit = hasPermit($course_id);
		if (!$this->hasPermit and $USER->id!=$this->uid) {
			error(get_string('mdlos_access_forbidden', 'block_mdlopensim'), $this->return_url);
		}

		if ($this->state==AVATAR_STATE_ACTIVE) {
			error(get_string('mdlos_active_avatar', 'block_mdlopensim'),  $this->return_url);
		}
	}



	function  execute()
	{
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
				redirect($this->cancel_url, get_string('mdlos_avatar_dlt_canceled', 'block_mdlopensim'), 0);
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
		if (!isGUID($this->UUID)) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_invalid_uuid", "block_mdlopensim");
			return false;
		}

		// delete from Mdlopensim and Sloodle DB
		$delete_user['UUID']  = $this->UUID;
		$delete_user['state'] = $this->state;

		$ret = mdlopensim_delete_avatar_info($delete_user, $this->use_sloodle);
		if (!$ret) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_user_delete_error", "block_mdlopensim");
		}

		// delete from Mdlopensim Group DB
		mdlopensim_delete_banneddb($this->UUID);
		mdlopensim_delete_groupdb ($this->UUID, false);
		mdlopensim_delete_profiles($this->UUID);

		// delete form OpenSim
		$ret = opensim_delete_avatar($this->UUID);
		if (!$ret) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_opensim_delete_error", "block_mdlopensim");
		}

		if ($this->hasError) return false;
		return true;
	}
}

?>
