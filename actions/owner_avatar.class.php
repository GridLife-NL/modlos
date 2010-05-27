<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

require_once(CMS_MODULE_PATH."/include/config.php");
require_once(CMS_MODULE_PATH."/include/xoopensim.func.php");
require_once(CMS_MODULE_PATH."/class/AbstructAction.class.php");
require_once(CMS_MODULE_PATH."/class/xoopensimOwnerForm.class.php");



class  ownerAction extends Abstruct_Action
{
	var $mController;

	var $isAdmin 	= false;
	var $action_url = "";
	var $userid	    = '0';
	var $uname	    = "";
	var $updated_owner = false;

	// Xoops DB
	var $dbhandler  = null;
	var $avatar     = null;
	var $UUID	    = "";
	var $firstname  = "";
	var $lastname   = "";
	var $passwd     = "";



	function  ownerAction($controller) 
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
			$module_url = CMS_MODULE_URL;
		}

		$this->action_url  	= $module_url."/?action=owner";
		$this->mActionForm 	= & new Xoopensim_OwnerForm();
		$this->userid 	   	= $this->mActionForm->uid;			// execute user id
		$this->uname 	   	= $this->mActionForm->uname;		// execute user name
		$this->isAdmin	   	= $this->mActionForm->isAdmin;

        // Number of Avatars Check
		if (!$this->isAdmin) {
			$usersdbHandler = & xoops_getmodulehandler('usersdb');
			$criteria = & new CriteriaCompo();
			$criteria->add(new Criteria('uid', $root->mContext->mXoopsUser->get('uid')));
			$avatars_num = $usersdbHandler->getCount($criteria);
			$max_avatars = $controller->mRoot->mContext->mModuleConfig['max_own_avatars'];
			if ($max_avatars>=0 and $avatars_num>=$max_avatars) {
				$controller->executeRedirect(CMS_MODULE_URL."/?action=avatars", 3, _MD_XPNSM_OVER_MAX_AVATARS);
			}
		}

		// get UUID from POST or GET
		$this->UUID = $root->mContext->mRequest->getRequest('uuid');
		if (!isGUID($this->UUID)) {
			$controller->executeRedirect(CMS_MODULE_URL, 3, _MD_XPNSM_BAD_UUID);
		}

		// check Xoops DB
		$this->dbhandler = & xoops_getmodulehandler('usersdb');
		$avatardata = & $this->dbhandler->get($this->UUID);
		if ($avatardata==null) {
			$controller->executeRedirect(CMS_MODULE_URL, 3, _MD_XPNSM_NOT_EXIST_UUID);
		}

		$uid = $avatardata->get('uid');							// uid of avatar in editing from DB
		if ($uid!=0) {
			$controller->executeRedirect(CMS_MODULE_URL, 3, _MD_XPNSM_ACCESS_FORBIDDEN);
		}

		$state = $avatardata->get('state');
		if ($state!=AVATAR_STATE_ACTIVE) {
			$controller->executeRedirect(CMS_MODULE_URL, 3, _MD_XPNSM_STATE_INVALID);
		}

		// get User Info from Xoops DB
		$this->avatar = $avatardata;
	}



	function  execute()
	{
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
			$this->passwd = $this->mActionForm->get('passwd');
			$postuid = $this->mActionForm->get('userid');
			$this->updated_owner = $this->updateOwner($postuid);
		}
	}



	function  executeView($render) 
	{
		$render->setTemplateName('xoopensim_owner.html');
		$grid_name = $this->mController->mRoot->mContext->mModuleConfig['grid_name'];

		$render->setAttribute('grid_name',		$grid_name);
		$render->setAttribute('action_url', 	$this->action_url);
		$render->setAttribute('actionForm', 	$this->mActionForm);
		$render->setAttribute('updated_owner',	$this->updated_owner);
		$render->setAttribute('firstname', 		$this->firstname);
		$render->setAttribute('lastname', 		$this->lastname);

		$render->setAttribute('userid',			$this->userid);
		$render->setAttribute('ownername',		$this->uname);
		$render->setAttribute('UUID', 			$this->UUID);
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
