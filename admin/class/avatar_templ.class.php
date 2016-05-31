<?php
///////////////////////////////////////////////////////////////////////////////
//	avatar_templ.class.php
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');
require_once(CMS_MODULE_PATH.'/admin/lib/modlos_avatar_templ_form.php');


class  AvatarTempl
{
	var $db_data 	= array();
	var $context;

	var $course_id   = 0;
	var $instance_id = 0;

	var $action_url;
	var $add_url;
	var $edit_url;
	var $delete_url;

    var $url_params;
	var $hasPermit   = false;

	var	$hasError    = false;
	var	$errorMsg    = array();



	function  AvatarTempl($course_id, $instance_id) 
	{
		$this->course_id   = $course_id;
		$this->instance_id = $instance_id;

		$this->hasPermit = hasModlosPermit($this->course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
	
		//
		if ($instance_id==0) {
			$ids = jbxl_block_instance_ids('modlos', $course_id);
			foreach ($ids as $id) {
				$instance_id = $id->id;
				break;
			}
		}
		$this->context = context_block::instance($instance_id); 

		$this->url_params = '?course='.$course_id.'&amp;instance='.$instance_id;
		$this->add_url    = CMS_MODULE_URL.'/admin/actions/avatar_templ_add.php'.$this->url_params;
		$this->edit_url   = CMS_MODULE_URL.'/admin/actions/avatar_templ_edit.php'.$this->url_params.'&amp;avatar=';
		$this->delete_url = CMS_MODULE_URL.'/admin/actions/avatar_templ_delete.php'.$this->url_params.'&amp;avatar=';
	}


	function  execute()
	{
		global $CFG, $DB;

		if (!$this->hasPermit) return false;

		$num = 0;
		$templates = $DB->get_records('modlos_template_avatars', array(), 'num ASC');
		foreach($templates as $template) {
			$this->db_data[$num]['id']  	 = $template->id;
			$this->db_data[$num]['num']  	 = $template->num;
			$this->db_data[$num]['title'] 	 = $template->title;
			$this->db_data[$num]['uuid'] 	 = $template->uuid;
			$this->db_data[$num]['text'] 	 = $template->text;
			$this->db_data[$num]['format'] 	 = $template->format;
			$this->db_data[$num]['filename'] = $template->filename;
			$this->db_data[$num]['text']     = $template->text;
			$this->db_data[$num]['html']     = htmlspecialchars_decode($template->text);
			$this->db_data[$num]['fullname'] = '';
			$this->db_data[$num]['url'] 	 = '';

			$name = opensim_get_avatar_name($template->uuid);
			if ($name) $this->db_data[$num]['fullname'] = $name['fullname'];

			if ($template->filename) {
				$path = '@@PLUGINFILE@@/'.$template->filename;
				$this->db_data[$num]['url'] = file_rewrite_pluginfile_urls($path, 'pluginfile.php', $this->context->id, 'block_modlos', 'templ_picture', $template->itemid);
			}

			$num++;
		}

		return true;
	}


	function  print_page() 
	{
		global $CFG;

		$grid_name  = $CFG->modlos_grid_name;

		$avatars    = $this->db_data;
		$url_params = $this->url_params;
		$action_url = $this->action_url;
		$add_url    = $this->add_url;
		$edit_url   = $this->edit_url;
		$delete_url = $this->delete_url;

		$avatar_templ_ttl = get_string('modlos_templ_ttl', 'block_modlos');
		$modlos_edit      = get_string('modlos_edit_ttl',  'block_modlos');
		$modlos_delete    = get_string('modlos_delete_ttl','block_modlos');
		$content          = get_string('modlos_templ_ttl', 'block_modlos');
		$add_avatar		  = get_string('modlos_templ_add_ttl', 'block_modlos');

		include(CMS_MODULE_PATH.'/admin/html/avatar_templ.html');
	}
}
