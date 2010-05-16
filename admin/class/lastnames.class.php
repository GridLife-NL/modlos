<?php
//////////////////////////////////////////////////////////////////////////////////////////////
// lastnames.class.php
//
//										by Fumi.Iseki
//

if (!defined('XOOPS_ROOT_PATH')) exit();

require_once _OPENSIM_MODULE_PATH.'/class/AbstructAction.class.php';
require_once _OPENSIM_MODULE_PATH.'/class/xoopensimLastnamesForm.class.php';
require_once _OPENSIM_MODULE_PATH.'/include/xoopensim.func.php';



class  LastnamesProc
{
	var $action_url;
	var $dbhandler;

	var $lastnames			= array();
	var $lastnames_active	= array();
	var $lastnames_inactive = array();

	var $select_active		= array();		// move to active
	var $select_inactive   	= array();		// move to inactive
	var $addname;



	function  lastnamesAction($controller) 
	{
		$this->mActionForm = & new Xoopensim_LastnamesForm();
		if (!$this->mActionForm->isAdmin) {
			$controller->executeRedirect(_OPENSIM_MODULE_URL, 2, _AM_XPNSM_ACCESS_FORBIDDEN);
		}

		$this->action_url = _OPENSIM_MODULE_URL."/admin/?action=lastnames";
		$this->lastnames  = $this->mActionForm->lastNames;
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

		if (xoops_getenv("REQUEST_METHOD")=="POST" and  !$this->mActionForm->hasError()) {
			$add = $this->mActionForm->get('submit_add');
			$lft = $this->mActionForm->get('submit_left');
			$rgt = $this->mActionForm->get('submit_right');
			$del = $this->mActionForm->get('submit_delete');

			$this->select_inactive = $this->mActionForm->get('select_left');
			$this->select_active   = $this->mActionForm->get('select_right');
			$this->addname         = $this->mActionForm->get('addname');

			if     ($add!="") $this->action_add();
			elseif ($lft!="") $this->action_move_active();
			elseif ($rgt!="") $this->action_move_inactive();
			elseif ($del!="") $this->action_delete();
		}
	}



	function  executeView($render) 
	{
		$root = & XCube_Root::getSingleton();
		$grid_name  = $root->mContext->mModuleConfig['grid_name'];
		$admin_menu = $root->mContext->mModule->getAdminMenu();

		foreach ($this->lastnames as $lastname=>$state) {
			if ($state=='1') $this->lastnames_active[]   = $lastname;
			else 			 $this->lastnames_inactive[] = $lastname;
		}

		$render->setTemplateName('xoopensim_lastnames.html');

		$render->setAttribute('grid_name', 		$grid_name);
		$render->setAttribute('admin_menu',		$admin_menu);
		$render->setAttribute('action_url', 	$this->action_url);
		$render->setAttribute('actionForm', 	$this->mActionForm);

		$render->setAttribute('select1_title',	_MD_XPNSM_ACTIVE_LIST);
		$render->setAttribute('select2_title',	_MD_XPNSM_INACTIVE_LIST);
		$render->setAttribute('select1', 		$this->lastnames_active);
		$render->setAttribute('select2', 		$this->lastnames_inactive);
	}



	function  action_add()
	{
		if ($this->addname=="") return;

		$obj = $this->mActionForm->dbhandler->create();
		$obj->assignVar('lastname', $this->addname);
		$obj->assignVar('state', '1');
		$this->mActionForm->dbhandler->insert($obj);

		$this->lastnames[$this->addname] = '1';
	}



	function  action_move_active()
	{
		foreach($this->select_active as $name) {
			$obj = $this->mActionForm->dbhandler->get($name);
			$obj->assignVar('state', '1');
			$this->mActionForm->dbhandler->insert($obj);
			$this->lastnames[$name] = '1';
		}
	}


	function  action_move_inactive()
	{
		foreach($this->select_inactive as $name) {
			$obj = $this->mActionForm->dbhandler->get($name);
			$obj->assignVar('state', '0');
			$this->mActionForm->dbhandler->insert($obj);
			$this->lastnames[$name] = '0';
		}
	}



	function  action_delete()
	{
		foreach($this->select_active as $name) {
			$obj = $this->mActionForm->dbhandler->get($name);
			$this->mActionForm->dbhandler->delete($obj);
			unset($this->lastnames[$name]);
		}

		foreach($this->select_inactive as $name) {
			$obj = $this->mActionForm->dbhandler->get($name);
			$this->mActionForm->dbhandler->delete($obj);
			unset($this->lastnames[$name]);
		}
	}
}


?>
