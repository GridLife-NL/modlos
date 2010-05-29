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
	var $course_id = 0;

	var $lastnames			= array();
	var $lastnames_active	= array();
	var $lastnames_inactive = array();

	var $select_active		= array();		// move to active
	var $select_inactive   	= array();		// move to inactive
	var $addname;

	var $hasError = false;
	var $errorMsg = array();



	function  LastNames($course_id) 
	{
		require_login($course_id);

		$this->course_id  = $course_id;
		$this->hasPermit = hasPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('mdlos_access_forbidden', 'block_mdlopensim');
			return;
		}
		$this->action_url = CMS_MODULE_URL."/admin/actions/lastnames.php";
	}



	function  execute()
	{
		$objs = get_records('mdlos_lastnames');
		if (is_array($objs)) {
			foreach($objs as $name) {
				$this->lastnames[$name->lastname] = $name->state;
			}
		}


		// Form	
		if (data_submitted()) {
			if (!$this->hasPermit) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('mdlos_access_forbidden', 'block_mdlopensim');
				return false;
			}

			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
				return false;
			}

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
			if ($state==AVATAR_LASTN_ACTIVE) $this->lastnames_active[]   = $lastname;
			else							 $this->lastnames_inactive[] = $lastname;
		}

		$grid_name		= $CFG->mdlopnsm_grid_name;
		$select1 		= $this->lastnames_active;
		$select2 		= $this->lastnames_inactive;

		$lastnames_ttl	= get_string('mdlos_lastnames', 	'block_mdlopensim');
		$select1_title	= get_string('mdlos_active_list', 	'block_mdlopensim');
		$select2_title	= get_string('mdlos_inactive_list', 'block_mdlopensim');

		include(CMS_MODULE_PATH."/admin/html/lastnames.html");
	}



	function  action_add()
	{
		if (!isAlphabetNumericSpecial($this->addname)) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('mdlos_invalid_lastname', 'block_mdlopensim')." ($this->addname)";
			return;
		}

		$obj = get_record('mdlos_lastnames', 'lastname', $this->addname);
		if ($obj!=null) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('mdlos_exist_lastname', 'block_mdlopensim')." ($this->addname)";
			return;
		}

		$obj->lastname = $this->addname;
		$obj->state    = AVATAR_LASTN_ACTIVE;
		insert_record('mdlos_lastnames', $obj);

		$this->lastnames[$this->addname] = AVATAR_LASTN_ACTIVE;
	}



	function  action_move_active()
	{
		foreach($this->select_active as $name) {
			$obj = get_record('mdlos_lastnames', 'lastname', $name);
			if ($obj==null) {
				if (!$this->hasError) $this->hasError = true;
				$this->errorMsg[] = get_string('mdlos_not_exist_lastname', 'block_mdlopensim')." ($name)";
			}
			else {
				$obj->state = AVATAR_LASTN_ACTIVE;
				update_record('mdlos_lastnames', $obj);
				$this->lastnames[$name] = AVATAR_LASTN_ACTIVE;
			}
		}
	}



	function  action_move_inactive()
	{
		foreach($this->select_inactive as $name) {
			$obj = get_record('mdlos_lastnames', 'lastname', $name);
			if ($obj==null) {
				if (!$this->hasError) $this->hasError = true;
				$this->errorMsg[] = get_string('mdlos_not_exist_lastname', 'block_mdlopensim')." ($name)";
			}
			else {
				$obj->state = AVATAR_LASTN_INACTIVE;
				update_record('mdlos_lastnames', $obj);
				$this->lastnames[$name] = AVATAR_LASTN_INACTIVE;
			}
		}
	}



	function  action_delete()
	{
		foreach($this->select_active as $name) {
			delete_records('mdlos_lastnames', 'lastname', $name);
			unset($this->lastnames[$name]);
		}

		foreach($this->select_inactive as $name) {
			delete_records('mdlos_lastnames', 'lastname', $name);
			unset($this->lastnames[$name]);
		}
	}
}


?>
