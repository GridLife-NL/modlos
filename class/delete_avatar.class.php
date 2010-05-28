<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  DeleteAvatar
{
	var $hasPermit    = false;
	var $action_url = "";
	var $cancel_url = "";
	var $return_url = "";
	var $userid	    = 0;		// owner of this process
	var $deleted_avatar = false;

	var $hasError  = false;
	var $errorMsg  = array();

	// Moodle DB
	var $avatar    = null;
	var $UUID	   = "";
	var $uid 	   = 0;			// owner of avatar
	var $firstname = "";
	var $lastname  = "";
	var $hmregion  = "";
	var $state     = -1;
	var $ownername = "";


	function  DeleteAvatar($courseid) 
	{
		global $CFG, $USER;

		require_login($courseid);

		// get UUID from POST or GET
		$uuid = required_param('UUID', PARAM_TEXT);
		if (!isGUID($uuid)) {
			error('<h4>'.get_string('mdlos_invalid_uuid', 'block_mdlopensim')." ($uuid)</h4>";
		}
		$this->UUID 	= $uuid;
		$this->courseid = $courseid;

		// get uid from Moodle DB
		$avatar = get_record('mdlos_users', 'uuid', $this->UUID);
		$this->uid	  = $avatar->user_id;				// uid of avatar in editing from DB
		$this->state  = $avatar->state;
		$this->avatar = $avatar;

		if (!$this->hasPermit and $USER->id!=$this->uid) {
			error('<h4>'.get_string('mdlos_access_forbidden', 'block_mdlopensim').'</h4>');
		}
		if ($this->state==AVATAR_STATE_ACTIVE) {
			error('<h4>'.get_string('mdlos_active_avatar', 'block_mdlopensim').'</h4>';
		}

		$this->firstname  = optional_param('firstname', '', PARAM_TEXT);
		$this->lastname   = optional_param('lastname',  '', PARAM_TEXT);
		$this->hmregion	  = optional_param('hmregion',  '', PARAM_TEXT));
		$this->ownername  = getUserName($USER->firstname, $USER->lastname);

		$this->action_url = CMS_MODULE_URL."/delete_avatar.php?course=$this->courseid";
		$this->cancel_url = CMS_MODULE_URL."/avatars_list.php?course=$this->courseid";
		$this->return_url = CMS_MODULE_URL."/avatars_list.php?course=$this->courseid";
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
				redirect($this->cancel_url, 1);
            }
		}
	}



	function  print_page() 
	{
		global $CFG;

		$this->execute();

		$grid_name = $CFG->mdlopnsm_grid_name;

		include(CMS_MODULE_PATH."/html/delete.html");
	}




	function del_avatar()
	{
		if (!isGUID($this->UUID)) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_invalid_uuid", "block_mdlopensim");
			return false;
		}

		// Xoops DB
		$delete_user['UUID']  = $this->UUID;
		$delete_user['state'] = $this->state;

		$ret = mdlopensim_delete_usertable($delete_user);
		if (!$ret) {
			$this->mActionForm->addErrorMessage(_MD_XPNSM_XOOPS_DELETE_ERROR);
			return false;
		}

		mdlopensim_delete_banneddb($this->UUID);
		mdlopensim_delete_groupdb($this->UUID, false);
		mdlopensim_delete_profiles($this->UUID);

		// OpenSim
		$ret = opensim_delete_avatar($this->UUID);
		if (!$ret) {
			$this->hasError = true;
			$this->errorMsg[] = get_string("mdlos_opensim_delete_error", "block_mdlopensim");
		}

		return $ret;
	}
}

?>
