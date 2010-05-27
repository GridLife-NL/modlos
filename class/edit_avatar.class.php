<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");




class  EditAvatar
{
	global $CFG;

	var $regionNames = array();

	var $hasPermit  = false;
	var $courseid 	= 0;
	var $module_url = "";
	var $action_url = "";

	var $delete_url = "";
	var $userid	    = 0;			// owner id of this process
	var $updated_avatar = false;

	var $hasError	= false;
	var $errorMsg 	= array();


	// Xoops DB
	var $avatar    = null;
	var $UUID	   = "";
	var $uid 	   = 0;				// owner id of avatar
	var $firstname = "";
	var $lastname  = "";
	var $hmregion  = "";
	var $state     = 0;
	var $ostate    = 0;
	var $ownername = "";			// owner name of avatar



	function  EditAvatar($courseid) 
	{
		global $CFG;

		require_login($courseid);
	
		$this->hasPermit = hasPermit($courseid);
		if (!$this->hasPermit) {
			error('<h4>'.get_string('mdlos_access_forbidden', 'block_mdlopensim').'</h4>');
		}

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
		$this->action_url = $this->module_url."/actions/edit_avatar.php";


		// get UUID from POST or GET
		$this->UUID = optional_param('uuid', '', PARAM_TEXT);
		if (!isGUID($this->UUID)) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('mdlos_invalid_uuid', 'block_mdlopensim')." ($this->addname)";
			return;
		}

//////////////////
		$this->delete_url = CMS_MODULE_URL."/?action=delete&uuid=".$this->UUID;
//////////////////

		// get uid from Moodle DB
		$this->uid    = $avatardata->get('uid');				// uid of avatar in editing from DB
		$this->ostate = $avatardata->get('state');

		if (!$this->hasPermit and $this->userid!=$this->uid) {
			$controller->executeRedirect(CMS_MODULE_URL, 3, _MD_XPNSM_ACCESS_FORBIDDEN);
		}

		// get User Info from Xoops DB
		$this->avatar = $avatardata;
	}



	function  execute()
	{


















		// OpenSim DB
        $this->regionNames = opensim_get_regions_names("ORDER BY regionName ASC");

		// Form
		$this->mActionForm->prepare();
		if (xoops_getenv("REQUEST_METHOD")=="POST") {
			$this->mActionForm->fetch();
			$this->mActionForm->validate();
		}
		$this->mActionForm->load();

		$this->firstname = $this->avatar->get('firstname');
		$this->lastname  = $this->avatar->get('lastname');

		if (xoops_getenv("REQUEST_METHOD")=="POST" and  !$this->mActionForm->hasError()) {
			$del = $this->mActionForm->get('submit_delete');
			if ($del!="") {
				$this->mController->executeForward($this->delete_url);
				exit("<h4>delete page open error!!</h4>");
			}

			$this->passwd	 = $this->mActionForm->get('passwd');
			$this->hmregion  = $this->mActionForm->get('hmregion');
			$this->state 	 = $this->mActionForm->get('state');
			if ($this->hasPermit) $this->ownername = $this->mActionForm->get('ownername');
			if ($this->ownername=="") $this->ownername = $this->mActionForm->uname;

			$this->updated_avatar = $this->updateAvatar();
		}
		else {
			$this->passwd	 = "";
			$this->hmregion  = $this->avatar->get('hmregion');
			$this->state  	 = $this->avatar->get('state');
			if ($this->hasPermit) $this->ownername = get_username_by_id($this->uid);
			if ($this->ownername=="") $this->ownername = $this->mActionForm->uname;
		}
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name		= $CFG->mdlopnsm_grid_name;

		$render->setAttribute('action_url', 	$this->action_url);
		$render->setAttribute('delete_url', 	$this->delete_url);
		$render->setAttribute('hasPermit',		$this->hasPermit);

		$render->setAttribute('regionNames', 	$this->regionNames);
		$render->setAttribute('actionForm', 	$this->mActionForm);
		$render->setAttribute('updated_avatar',	$this->updated_avatar);

		$render->setAttribute('UUID', 			$this->UUID);
		$render->setAttribute('firstname', 		$this->firstname);
		$render->setAttribute('lastname', 		$this->lastname);
		$render->setAttribute('passwd', 		$this->passwd);
		$render->setAttribute('hmregion', 		$this->hmregion);
		$render->setAttribute('ownername', 		$this->ownername);
		$render->setAttribute('state',			$this->state);

		include(CMS_MODULE_PATH."/html/edit.html");
	}



	function updateAvatar()
	{
		// OpenSim DB
		if ($this->passwd!="") {
			$passwdsalt = make_random_hash();
			$passwdhash = md5(md5($this->passwd).":".$passwdsalt);

			$ret = opensim_set_password($this->UUID, $passwdhash, $passwdsalt);
			if (!$ret) {
				$this->mActionForm->addErrorMessage(_MD_XPNSM_PASSWD_UPDATE_ERROR);
				return false;
			}
		}

		if ($this->hmregion!="") {
			$ret = opensim_set_home_region($this->UUID, $this->hmregion);
			if (!$ret) {
				$this->mActionForm->addErrorMessage(_MD_XPNSM_HMRGN_UPDATE_ERROR);
				return false;
			}
		}


		// State
		$errno = 0;
		if ($this->state!=$this->ostate) {
			// XXXXXX -> InAcvtive
			if ($this->state==AVATAR_STATE_INACTIVE) {
				$ret = xoopensim_inactivate_avatar($this->UUID);
				if (!$ret) {
					$this->mActionForm->addErrorMessage(_MD_XPNSM_INACTIVATE_ERROR);
					return false;
				}
			}
			// InActive -> Acvtive
			elseif ($this->ostate==AVATAR_STATE_INACTIVE and $this->state==AVATAR_STATE_ACTIVE) {
				$ret = xoopensim_activate_avatar($this->UUID);
				if (!$ret) {
					$this->mActionForm->addErrorMessage(_MD_XPNSM_ACTIVATE_ERROR);
					return false;
				}
			}
		}

		// Xoops DB
		if ($this->hasPermit) $this->uid = get_userid_by_name($this->ownername);
		else                $this->uid = $this->userid;

		$update_user['UUID']      = $this->UUID;
		$update_user['uid']		  = $this->uid;
		$update_user['firstname'] = $this->firstname;
		$update_user['lastname']  = $this->lastname;
		$update_user['hmregion']  = $this->hmregion;
		$update_user['state']	  = $this->state;
		$update_user['time']	  = time();

		$ret = xoopensim_update_usertable($update_user);
		if (!$ret) $this->mActionForm->addErrorMessage(_MD_XPNSM_XOOPS_UPDATE_ERROR);

		return $ret;
	}

}

?>
