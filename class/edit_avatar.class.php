<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

require_once(CMS_MODULE_PATH."/include/xoopensim.func.php");
require_once(CMS_MODULE_PATH."/class/AbstructAction.class.php");
require_once(CMS_MODULE_PATH."/class/xoopensimEditForm.class.php");



class  editAction extends Abstruct_Action
{
	var $mController;
	var $regionNames = array();

	var $isAdmin    = false;
	var $action_url = "";
	var $delete_url = "";
	var $userid	    = 0;			// owner id of this process
	var $updated_avatar = false;

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



	function  editAction($controller) 
	{
		$this->mController = $controller;

		$root = & XCube_Root::getSingleton();
		if ($root->mContext->mUser->isInRole('Site.GuestUser')) {
			$controller->executeRedirect(CMS_MODULE_URL, 2, _MD_XPNSM_ACCESS_FORBIDDEN);
		}

		// for HTTPS
		$use_https = $root->mContext->mModuleConfig['use_https'];
		if ($use_https) {
			$https_url = $root->mContext->mModuleConfig['https_url'];
			if ($https_url!="") {
				$module_url = $https_url.'/'.CMS_DIR_NAME;
			}
			else {
				$module_url = ereg_replace('^http:', 'https:', XOOPS_MODULE_URL).'/'.CMS_DIR_NAME;
			}
		}
		else {
			//$module_url = XOOPS_MODULE_URL.'/'.CMS_DIR_NAME;
			$module_url = CMS_MODULE_URL;
		}

		$this->action_url  	= $module_url."/?action=edit";
		$this->mActionForm 	= & new Xoopensim_EditForm();
		$this->isAdmin	   	= $this->mActionForm->isAdmin;
		$this->userid 	   	= $this->mActionForm->uid;

		// get UUID from POST or GET
		$this->UUID = $root->mContext->mRequest->getRequest('uuid');
		if (!isGUID($this->UUID)) {
			$controller->executeRedirect(CMS_MODULE_URL, 3, _MD_XPNSM_BAD_UUID);
		}
		$this->delete_url = CMS_MODULE_URL."/?action=delete&uuid=".$this->UUID;

		// get uid from Xoops DB
		$usersdbHandler = & xoops_getmodulehandler('usersdb');
		$avatardata = & $usersdbHandler->get($this->UUID);
		if ($avatardata==null) {
			$controller->executeRedirect(CMS_MODULE_URL, 3, _MD_XPNSM_NOT_EXIST_UUID);
		}
		$this->uid    = $avatardata->get('uid');				// uid of avatar in editing from DB
		$this->ostate = $avatardata->get('state');

		if (!$this->isAdmin and $this->userid!=$this->uid) {
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
			if ($this->isAdmin) $this->ownername = $this->mActionForm->get('ownername');
			if ($this->ownername=="") $this->ownername = $this->mActionForm->uname;

			$this->updated_avatar = $this->updateAvatar();
		}
		else {
			$this->passwd	 = "";
			$this->hmregion  = $this->avatar->get('hmregion');
			$this->state  	 = $this->avatar->get('state');
			if ($this->isAdmin) $this->ownername = get_username_by_id($this->uid);
			if ($this->ownername=="") $this->ownername = $this->mActionForm->uname;
		}
	}



	function  executeView($render) 
	{
		$render->setTemplateName('xoopensim_edit.html');
		$grid_name = $this->mController->mRoot->mContext->mModuleConfig['grid_name'];

		$render->setAttribute('grid_name',		$grid_name);
		$render->setAttribute('action_url', 	$this->action_url);
		$render->setAttribute('delete_url', 	$this->delete_url);
		$render->setAttribute('isAdmin',		$this->isAdmin);

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
		if ($this->isAdmin) $this->uid = get_userid_by_name($this->ownername);
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
