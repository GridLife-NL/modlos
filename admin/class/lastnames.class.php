<?php
//////////////////////////////////////////////////////////////////////////////////////////////
// lastnames.class.php
//
//										by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");


define('SATE_OFF', '0');
define('SATE_ON',  '1');



class  LastNames
{
	var $action_url;
	var $courseid;

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

			$add = optional_param('submit_add',	   '', PARAM_TEXT);
			$lft = optional_param('submit_left',   '', PARAM_TEXT);
			$rgt = optional_param('submit_right',  '', PARAM_TEXT);
			$del = optional_param('submit_delete', '', PARAM_TEXT);

			$this->select_inactive = optional_param('select_left',  '', PARAM_TEXT);
			$this->select_active   = optional_param('select_right', '', PARAM_TEXT);
			$this->addname		   = optional_param('addname',		'', PARAM_TEXT);

			if	   ($add!="") $this->action_add();
			elseif ($lft!="") $this->action_move_active();
			elseif ($rgt!="") $this->action_move_inactive();
			elseif ($del!="") $this->action_delete();
		}
	}



	function  print_page() 
	{
		global $CFG;

		$this->execute();

		foreach ($this->lastnames as $lastname=>$state) {
			if ($state==STATE_ON) $this->lastnames_active[]   = $lastname;
			else 				  $this->lastnames_inactive[] = $lastname;
		}

		$grid_name		= $CFG->mdlopnsm_grid_name;
		$module_url		= CMS_MODULE_URL;
		$select1 		= $this->lastnames_active;
		$select2 		= $this->lastnames_inactive;

		$lastnames_ttl	= get_string('mdlos_lastnames', 'block_mdlopensim');
		$select1_title	= get_string('mdlos_active_list', 'block_mdlopensim');
		$select2_title	= get_string('mdlos_inactive_list', 'block_mdlopensim');

		include(CMS_MODULE_PATH."/admin/html/lastnames.html");
	}



	function  action_add()
	{
		if (!isAlphabetNemericSpecial($this->addname)) return;

		$ret = get_record("

		$obj = $this->mActionForm->dbhandler->create();
		$obj->assignVar('lastname', $this->addname);
		$obj->assignVar('state', STATE_ON);
		$this->mActionForm->dbhandler->insert($obj);

		$this->lastnames[$this->addname] = STATE_ON;
	}



	function  action_move_active()
	{
		foreach($this->select_active as $name) {
			$obj = $this->mActionForm->dbhandler->get($name);
			$obj->assignVar('state', STATE_ON);
			$this->mActionForm->dbhandler->insert($obj);
			$this->lastnames[$name] = STATE_ON;
		}
	}



	function  action_move_inactive()
	{
		foreach($this->select_inactive as $name) {
			$obj = $this->mActionForm->dbhandler->get($name);
			$obj->assignVar('state', STATE_OFF);
			$this->mActionForm->dbhandler->insert($obj);
			$this->lastnames[$name] = STATE_OFF;
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
