<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  CreateAvatar
{
	var $regionNames  = array();
	var $actvLastName = 0;

	var $hasPermit 	= false;
	var $module_url	= "";
	var $action_url = "";
	var $userid	 = 0;		// owner id of this process
	var $created_avatar = false;

	// Xoops DB
	var $UUID	   = "";
	var $nx_UUID   = "";
	var $uid	   = 0;			// owner id of avatar
	var $firstname = "";
	var $lastname  = "";
	var $hmregion  = "";
	var $ownername = "";		// owner name of avatar



	function  CreateAction($courseid)
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
		$this->action_url = $this->module_url."/actions/create_avatar.php";



		$this->actvLastName	= $this->mActionForm->actvLastName;

		// Number of Avatars Check
		if (!$this->hasPermit) {
			$usersdbHandler = & xoops_getmodulehandler('usersdb');
			$criteria = & new CriteriaCompo();
			$criteria->add(new Criteria('uid', $root->mContext->mXoopsUser->get('uid')));
			$avatars_num = $usersdbHandler->getCount($criteria);

			$max_avatars = $controller->mRoot->mContext->mModuleConfig['max_own_avatars'];
			if ($max_avatars>=0 and $avatars_num>=$max_avatars) {
				$controller->executeRedirect(_OPENSIM_MODULE_URL, 3, _MD_XPNSM_OVER_MAX_AVATARS);
			}
		}
	}



	function  execute()
	{
		// Region Name
		$this->regionNames = opensim_get_regions_names("ORDER BY regionName ASC");

		// Form
		$this->mActionForm->prepare();
		if (xoops_getenv("REQUEST_METHOD")=="POST") {
			$this->mActionForm->fetch();
			$this->mActionForm->validate();
		}
		$this->mActionForm->load();

		$this->handler = & xoops_getmodulehandler('usersdb');
		if ($this->hasPermit) {
			do {
				$uuid = make_random_guid();
				$modobj = $this->handler->get($uuid);
			} while ($modobj!=null);
			$this->nx_UUID = $uuid;
		}

		if (xoops_getenv("REQUEST_METHOD")=="POST") {
			$this->firstname = $this->mActionForm->get('firstname');
			$this->lastname  = $this->mActionForm->get('lastname');
			$this->passwd	 = $this->mActionForm->get('passwd');
			$this->hmregion  = $this->mActionForm->get('hmregion');
			if($this->hasPermit) $this->ownername = $this->mActionForm->get('ownername');
			if($this->hasPermit) $this->UUID		= $this->mActionForm->get('UUID');
		}
		else {
			$this->hmregion  = $this->mController->mRoot->mContext->mModuleConfig['home_region'];
			$this->UUID	  = $this->nx_UUID;
		}

		if ($this->ownername=="") $this->ownername = $this->mActionForm->uname;

		if (xoops_getenv("REQUEST_METHOD")=="POST" and  !$this->mActionForm->hasError()) {
			$this->created_avatar = $this->createAvatar();
		}
	}



	function  print_page() 
	{
		$context = & XCube_Root::getSingleton()->mContext;

		$render->setTemplateName('xoopensim_create.html');
		$grid_name = $context->mModuleConfig['grid_name'];

		$render->setAttribute('grid_name',		$grid_name);
		$render->setAttribute('action_url', 	$this->action_url);
		$render->setAttribute('hasPermit',		$this->hasPermit);

		$render->setAttribute('actvLastName', 	$this->actvLastName);
		$render->setAttribute('lastNames',  	$this->mActionForm->lastNames);
		$render->setAttribute('regionNames', 	$this->regionNames);
		$render->setAttribute('created_avatar',	$this->created_avatar);
		$render->setAttribute('actionForm', 	$this->mActionForm);

		$render->setAttribute('firstname', 		$this->firstname);
		$render->setAttribute('lastname', 		$this->lastname);
		$render->setAttribute('passwd', 		$this->passwd);
		$render->setAttribute('hmregion', 		$this->hmregion);
		$render->setAttribute('ownername',		$this->ownername);
		$render->setAttribute('nx_UUID',	  	$this->nx_UUID);
		$render->setAttribute('UUID', 		  	$this->UUID);

		$render->setAttribute('isDisclaimer',	$context->mModuleConfig['activate_disclaimer']);
		$render->setAttribute('disclaimer',		$context->mModuleConfig['disclaimer_content']);

		// 
		$render->setAttribute('pv_ownername', $this->ownername);
		if ($this->created_avatar) {
			$render->setAttribute('pv_firstname', "");
			$render->setAttribute('pv_lastname',  "");
		}
		else {
			$render->setAttribute('pv_firstname', $this->firstname);
			$render->setAttribute('pv_lastname',  $this->lastname);
		}

		include(CMS_MODULE_PATH."/html/create.html");
	}



	function createAvatar()
	{
		$context = & XCube_Root::getSingleton()->mContext;

		// User Check
		$criteria  = & new CriteriaCompo();
		$criteria->add(new Criteria('firstname', $this->firstname));
		$criteria->add(new Criteria('lastname',  $this->lastname));

		$modobj = & $this->handler->getObjects($criteria);
		if ($modobj!=null) {
			$this->mActionForm->addErrorMessage(_MD_XPNSM_ALREADY_NAME_ERROR);
			return false;
		}

		// Create UUID
		if (!$this->hasPermit or !isGUID($this->UUID)) {
			do {
				$uuid = make_random_guid();
				$modobj = $this->handler->get($uuid);
			} while ($modobj!=null);
			$this->UUID = $uuid;
		}

		// OpenSim DB
		$rslt = opensim_create_avatar($this->UUID, $this->firstname, $this->lastname, $this->passwd, $this->hmregion);
		if (!$rslt) {
			$this->mActionForm->addErrorMessage(_MD_XPNSM_OPNSM_CREATE_ERROR);
			return false;
		}

		// Xoops DB
		if ($this->hasPermit) $this->uid = get_userid_by_name($this->ownername);
		else 				$this->uid = $this->userid;

		// Xoopensim DB
		$new_user['UUID']	  = $this->UUID;
		$new_user['uid']	   = $this->uid;
		$new_user['firstname'] = $this->firstname;
		$new_user['lastname']  = $this->lastname;
		$new_user['hmregion']  = $this->hmregion;
		$new_user['state']	 = "";

		//$ret = xoopensim_insrt_usertable($modHandler, $new_user);
		$ret = xoopensim_insert_usertable($new_user);
		if (!$ret) $this->mActionForm->addErrorMessage(_MD_XPNSM_XOOPS_CREATE_ERROR);

		return $ret;
	}

}

?>
