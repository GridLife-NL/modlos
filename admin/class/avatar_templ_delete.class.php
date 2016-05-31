<?php
///////////////////////////////////////////////////////////////////////////////
//	avatar_templ_delete.class.php
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');
require_once(CMS_MODULE_PATH.'/admin/lib/modlos_avatar_templ_form.php');


class  AvatarTemplDelete
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

	var $mform		 = null;

	var	$hasError    = false;
	var	$errorMsg    = array();



	function  AvatarTemplDelete($course_id, $instance_id) 
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

		//
		$this->url_params = '?course='.$course_id.'&amp;instance='.$instance_id;
		$this->add_url    = CMS_MODULE_URL.'/admin/actions/avatar_templ_edit.php'.  $this->url_params.'&amp;action=add';
		$this->edit_url   = CMS_MODULE_URL.'/admin/actions/avatar_templ_edit.php'.  $this->url_params.'&amp;action=';
		$this->delete_url = CMS_MODULE_URL.'/admin/actions/avatar_templ_delete.php'.$this->url_params.'&amp;action=';
	}


	function  execute()
	{
		global $CFG, $DB;

		if (!$this->hasPermit) return false;

		if ($formdata = data_submitted()) {	// POST
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}
			
			$context_id = $this->context->id;
			$title = required_param('title', PARAM_TEXT);
			$uuid  = required_param('uuid',  PARAM_TEXT);

			if ($title==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_invalid_ttl', 'block_modlos');
			}
			//if (!isGuid($uuid)) {
			if (isGuid($uuid)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_invalid_uuid', 'block_modlos');
			}
			if ($this->hasError) return false;

			// Editor
			$explain = required_param_array('explain', PARAM_RAW);

			$template_avatar = array();
			$template_avatar['num']       = 0;
			$template_avatar['title']     = $title;
			$template_avatar['uuid']      = $uuid;
			$template_avatar['text']      = htmlspecialchars($explain['text']); // htmlspecialchars_decode
			$template_avatar['format']    = $explain['format'];
			$template_avatar['fileid']    = 0;
			$template_avatar['filename']  = '';
			$template_avatar['itemid'] 	  = 0;
			$template_avatar['timestamp'] = time();

			// File Manager. see lib/filelib.php
			$picid = file_get_submitted_draft_itemid('picfile');
			file_save_draft_area_files($picid, $context_id, 'block_modlos', 'templ_picture', $picid, array('maxfiles'=>1));

			$condition = "itemid=$picid AND contextid=$context_id AND component='block_modlos' AND filearea='templ_picture' AND ".
                         "filename!='\\.' AND filesize!='0' AND source!='NULL'";
			$query_str = 'SELECT id,filename FROM '.$CFG->prefix.'files WHERE '.$condition;

			if ($files = $DB->get_records_sql($query_str)) {
				foreach($files as $file) {
					$template_avatar['fileid']   = $file->id;
					$template_avatar['filename'] = $file->filename;
					break;
				}
			}
			$template_avatar['itemid'] = $picid;

			$query_str = 'SELECT max(num) FROM '.$CFG->prefix.'modlos_template_avatars';
			$obj_nums = $DB->get_records_sql($query_str);
			foreach ($obj_nums as $obj_num) {
				$num = $obj_num->{'max(num)'};
				break;
			}
			$template_avatar['num'] = $num + 1;
			//
			$DB->insert_record('modlos_template_avatars', $template_avatar);

			//
			$this->mform = new modlos_avatar_templ_form(true);		// clear POST
			$this->mform->set_data(array('id'=>$this->course_id));
		}

		// GET
		else {
			$this->mform = new modlos_avatar_templ_form();
			$this->mform->set_data(array('id'=>$this->course_id));
		}

		$num = 0;
		$templates = $DB->get_records('modlos_template_avatars', array(), 'num ASC');
		foreach($templates as $template) {
			$this->db_data[$num]['num']  	 = $template->num;
			$this->db_data[$num]['title'] 	 = $template->title;
			$this->db_data[$num]['uuid'] 	 = $template->uuid;
			$this->db_data[$num]['text'] 	 = $template->text;
			$this->db_data[$num]['format'] 	 = $template->format;
			$this->db_data[$num]['filehash'] = $template->filename;
			$this->db_data[$num]['text']     = $template->text;

			$path = '@@PLUGINFILE@@/'.$template->filename;
			$this->db_data[$num]['url'] = file_rewrite_pluginfile_urls($path, 'pluginfile.php', $this->context->id, 'block_modlos', 'templ_picture', $template->itemid);
			$this->db_data[$num]['html'] = htmlspecialchars_decode($template->text);

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
		$mform      = $this->mform;

		$avatar_templ_ttl = get_string('modlos_templ_ttl', 'block_modlos');
		$content          = get_string('modlos_templ_ttl', 'block_modlos');

		include(CMS_MODULE_PATH.'/admin/html/avatar_templ_delete.html');
	}
}
