<?php
//////////////////////////////////////////////////////////////////////////////////////////////
// estates.class.php
//
//										by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');




class  Estates
{
	var $action_url;
	var $course_id = 0;
	var $page_size = 15;

	var $estates  = array();

	var $addname;

	var $hasError = false;
	var $errorMsg = array();



	function  Estates($course_id) 
	{
		$this->course_id  = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		$this->action_url = CMS_MODULE_URL.'/admin/actions/estates.php';
	}



	function  execute()
	{
		global $DB;

		$this->estates = opensim_get_estates_infos();
		if ($this->estates==null) return;

		// Form	
		if (data_submitted()) {
			if (!$this->hasPermit) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
				return false;
			}

			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}

			$add = optional_param('submit_add',	   '', PARAM_TEXT);
			$lft = optional_param('submit_left',   '', PARAM_TEXT);
			$rgt = optional_param('submit_right',  '', PARAM_TEXT);
			$del = optional_param('submit_delete', '', PARAM_TEXT);

			$this->select_inactive = optional_param('select_left',  '', PARAM_TEXT);
			$this->select_active   = optional_param('select_right', '', PARAM_TEXT);
			$this->addname		   = optional_param('addname',		'', PARAM_TEXT);

			if	   ($add!='') $this->action_add();
			elseif ($lft!='') $this->action_move_active();
			elseif ($rgt!='') $this->action_move_inactive();
			elseif ($del!='') $this->action_delete();
		}
	}



	function  print_page() 
	{
		global $CFG, $OUTPUT;

		$grid_name	  = $CFG->modlos_grid_name;
		$estates_ttl  = get_string('modlos_estate_ttl','block_modlos');

		include(CMS_MODULE_PATH.'/admin/html/estates.html');
	}



	function  show_table()
	{
		$table = new html_table();
		//
		$table->head [] = '#';
		$table->align[] = 'center';
		$table->size [] = '20px';
		$table->wrap [] = 'nowrap';

		$table->head [] = 'ID';
		$table->align[] = 'center';
		$table->size [] = '20px';
		$table->wrap [] = 'nowrap';

		$table->head [] = get_string('modlos_estate_name','block_modlos');
		$table->align[] = 'center';
		$table->size [] = '60px';
		$table->wrap [] = 'nowrap';

		$table->head [] = get_string('modlos_estate_owner','block_modlos');
		$table->align[] = 'center';
		$table->size [] = '60px';
		$table->wrap [] = 'nowrap';

		$table->head [] = get_string('delete');
		$table->align[] = 'center';
		$table->size [] = '60px';
		$table->wrap [] = 'nowrap';

		$table->head [] = '';
		$table->align[] = 'center';
		$table->size [] = '60px';
		$table->wrap [] = 'nowrap';
		//
		$i = 0;
		foreach($this->estates as $estate) {
			$estate_id = $estate['estate_id'];
			$estate_input = '<input type="hidden" name="estateids['.$i.']" value="'.$estate_id.'" />';
			$table->data[$i][] = $i + 1;
			$table->data[$i][] = $estate_id;
			$table->data[$i][] = '<input type="text" name="estatenames['.$i.']"  size="16" maxlength="32" value="'.$estate['estate_name'].'" />';
			$table->data[$i][] = '<input type="text" name="estateowners['.$i.']" size="16" maxlength="32" value="'.$estate['fullname'].'" />';
			$table->data[$i][] = '<input type="checkbox" name="estatedels['.$i.']" value="1" />'.$estate_input;

			if (($i+1)%$this->page_size==0) {
				$table->data[$i][] = '<input type="submit" name="updateestate" value="'.get_string('modlos_update','block_modlos').'" />';
			}
			else  {
				$table->data[$i][] = ' ';
			}
			$i++;
		}

		echo '<div align="center">';
		echo html_writer::table($table);
		echo '</div>';

		return $i;
	}



	function  action_add()
	{
		global $DB;

		if (!isAlphabetNumericSpecial($this->addname)) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_invalid_lastname', 'block_modlos')." ($this->addname)";
			return;
		}

		$obj = $DB->get_record('modlos_lastnames', array('lastname'=>$this->addname));
		if ($obj!=null) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_exist_lastname', 'block_modlos')." ($this->addname)";
			return;
		}

		$obj->lastname = $this->addname;
		$obj->state	= AVATAR_LASTN_ACTIVE;
		$DB->insert_record('modlos_lastnames', $obj);

		$this->lastnames[$this->addname] = AVATAR_LASTN_ACTIVE;
	}



	function  action_move_active()
	{
		global $DB;

		foreach($this->select_active as $name) {
			$obj = $DB->get_record('modlos_lastnames', array('lastname'=>$name));
			if ($obj==null) {
				if (!$this->hasError) $this->hasError = true;
				$this->errorMsg[] = get_string('modlos_not_exist_lastname', 'block_modlos')." ($name)";
			}
			else {
				$obj->state = AVATAR_LASTN_ACTIVE;
				$DB->update_record('modlos_lastnames', $obj);
				$this->lastnames[$name] = AVATAR_LASTN_ACTIVE;
			}
		}
	}



	function  action_move_inactive()
	{
		global $DB;

		foreach($this->select_inactive as $name) {
			$obj = $DB->get_record('modlos_lastnames', array('lastname'=>$name));
			if ($obj==null) {
				if (!$this->hasError) $this->hasError = true;
				$this->errorMsg[] = get_string('modlos_not_exist_lastname', 'block_modlos')." ($name)";
			}
			else {
				$obj->state = AVATAR_LASTN_INACTIVE;
				$DB->update_record('modlos_lastnames', $obj);
				$this->lastnames[$name] = AVATAR_LASTN_INACTIVE;
			}
		}
	}



	function  action_delete()
	{
		global $DB;

		foreach($this->select_active as $name) {
			$DB->delete_records('modlos_lastnames', array('lastname'=>$name));
			unset($this->lastnames[$name]);
		}

		foreach($this->select_inactive as $name) {
			$DB->delete_records('modlos_lastnames', array('lastname'=>$name));
			unset($this->lastnames[$name]);
		}
	}
}


?>
