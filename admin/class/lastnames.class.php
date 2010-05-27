<?php
//////////////////////////////////////////////////////////////////////////////////////////////
// lastnames.class.php
//
//										by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  LastNames
{
	var $action_url;
	var $dbhandler;

	var $lastnames			= array();
	var $lastnames_active	= array();
	var $lastnames_inactive = array();

	var $select_active		= array();		// move to active
	var $select_inactive   	= array();		// move to inactive
	var $addname;



	function  LastNames($controller) 
	{
		require_login($courseid);

		$this->courseid  = $courseid;
		$this->hasPermit = hasPermit($courseid);
		if (!$this->hasPermit) {
			error(get_string('mdlos_access_forbidden', 'block_mdlopensim'));
		}

		$this->action_url = CMS_MODULE_URL."/admin/actions/lastnames.php";
	}



	function  execute()
	{
		// Form	
		if (data_submitted()) {
			if (!$this->hasPermit) {
				$this->hasError = true;
				$this->errorMsg = get_string('mdlos_access_forbidden', 'block_mdlopensim');
				return false;
			}

			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg = get_string("mdlos_sesskey_error", "block_mdlopensim");
				return false;
			}

			$quest = optional_param('quest', 'no', PARAM_ALPHA);


			$add = optional_param('submit_add',	   '', );
			$lft = optional_param('submit_left',   '', );
			$rgt = optional_param('submit_right',  '', );
			$del = optional_param('submit_delete', '', );

			$this->select_inactive = optional_param('select_left'i, '',);
			$this->select_active   = optional_param('select_right', '',);
			$this->addname		   = optional_param('addname',		'',);

			if	   ($add!="") $this->action_add();
			elseif ($lft!="") $this->action_move_active();
			elseif ($rgt!="") $this->action_move_inactive();
			elseif ($del!="") $this->action_delete();
		}
	}



	function  print_page() 
	{
		global $CFG;

		$this->exexute();

		foreach ($this->lastnames as $lastname=>$state) {
			if ($state=='1') $this->lastnames_active[]   = $lastname;
			else 			 $this->lastnames_inactive[] = $lastname;
		}

		$grid_name			= $CFG->mdlopnsm_grid_name;
		$module_url			= CMS_MODULE_URL;


		$lastnames_ttl		= get_string('mdlos_lastnames', 'block_mdlopensim');
		$lastnames_ttl		= get_string('mdlos_lastnames', 'block_mdlopensim');


		$render->setAttribute('grid_name', 		$grid_name);
		$render->setAttribute('admin_menu',		$admin_menu);
		$render->setAttribute('action_url', 	$this->action_url);
		$render->setAttribute('actionForm', 	$this->mActionForm);

		$render->setAttribute('select1_title',	_MD_XPNSM_ACTIVE_LIST);
		$render->setAttribute('select2_title',	_MD_XPNSM_INACTIVE_LIST);
		$render->setAttribute('select1', 		$this->lastnames_active);
		$render->setAttribute('select2', 		$this->lastnames_inactive);

		include(CMS_MODULE_PATH."/admin/html/lastnames.html");
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
