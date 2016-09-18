<?php
///////////////////////////////////////////////////////////////////////////////
//	avatar_templ_edit.class.php
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(realpath(CMS_MODULE_PATH.'/include/modlos.func.php'));
require_once(realpath(CMS_MODULE_PATH.'/admin/lib/modlos_avatar_templ_form.php'));


class  AvatarTemplEdit
{
	var $db_data  	 = array();

	var $course_id   = 0;
	var $isPost      = false;

	var $return_url;
	var $add_url;
	var $edit_url;
	var $delete_url;

    var $url_params;
	var $hasPermit   = false;

	var $mform		 = null;

	var	$hasError    = false;
	var	$errorMsg    = array();



	function  __construct($course_id) 
	{
		$this->course_id = $course_id;
		$this->hasPermit = hasModlosPermit($this->course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		//
		$this->url_params = '?course='.$course_id;
		$this->return_url = CMS_MODULE_URL.'/admin/actions/avatar_templ.php'.$this->url_params;
		$this->add_url    = CMS_MODULE_URL.'/admin/actions/avatar_templ_add.php'.$this->url_params;
		$this->edit_url   = CMS_MODULE_URL.'/admin/actions/avatar_templ_edit.php'.$this->url_params.  '&amp;templid=';
		$this->delete_url = CMS_MODULE_URL.'/admin/actions/avatar_templ_delete.php'.$this->url_params.'&amp;templid=';
	}


	function  execute()
	{
		global $CFG, $DB, $USER;

		if (!$this->hasPermit) return false;

		// Cancel
		$cancel = optional_param('cancel', null, PARAM_TEXT);
		if ($cancel) redirect($this->return_url, 'Please wait ...', 0);

		$templ_id = required_param('templid', PARAM_INT);	// Primary Key
		$template = $DB->get_record('modlos_template_avatars', array('id'=>$templ_id));
		if (!$template) redirect($this->return_url, get_string('modlos_templ_uuid_mis', 'block_modlos'), 2);

		//
		// POST
		if ($formdata = data_submitted()) {	
			//
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}

			$title  = trim(required_param('title', PARAM_TEXT));
			$uuid   = trim(required_param('uuid',  PARAM_TEXT));
			$order  = optional_param('order', '0', PARAM_INT);
			$status = optional_param('valid', '0', PARAM_INT);

			// Check
			if ($title==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_title_invalid', 'block_modlos');
			}
			$info = opensim_get_avatar_info($uuid);
			if ($info==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_uuid_invalid', 'block_modlos');
			}
			else if ($info['fullname']==null or $info['hgURI']!=null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_uuid_mis', 'block_modlos');
			}
			//
			if ($uuid!=$template->uuid) {
				$ret = $DB->get_record('modlos_template_avatars', array('uuid'=>$uuid));
				if ($ret) {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_templ_uuid_dup', 'block_modlos');
				}
			}
			if ($this->hasError) return false;
			//
			if ($order<=0) {
				$num = 0;
				$query_str = 'SELECT max(num) FROM '.$CFG->prefix.'modlos_template_avatars';
				$obj_nums = $DB->get_records_sql($query_str);
				foreach ($obj_nums as $obj_num) {
					$num = $obj_num->{'max(num)'};
					break;
				}
				$order = $num + 1;
			}

			// Editor
			$explain = required_param_array('explain', PARAM_RAW);

			$update = array();
			$update['id']        = $template->id;	// $templ_id
			$update['num']       = $order;
			$update['title']     = $title;
			$update['uuid']      = $uuid;
			$update['text']      = htmlspecialchars($explain['text']); // htmlspecialchars_decode
			$update['format']    = $explain['format'];
			$update['fileid']    = 0;
			$update['filename']  = '';
			$update['itemid']    = 0;
			$update['status']    = $status;
			$update['timestamp'] = time();

			// File Manager. see lib/filelib.php
			$context_id = jbxl_get_block_id('modlos');
			$picid = file_get_submitted_draft_itemid('picfile');
			file_save_draft_area_files($picid, $context_id, 'block_modlos', 'templ_picture', $picid, array('maxfiles'=>1));

			$condition = "itemid=$picid AND contextid=$context_id AND component='block_modlos' AND filearea='templ_picture' AND ".
                         "filename!='\\.' AND filesize!='0' AND source!='NULL'";
			$query_str = 'SELECT id,filename FROM '.$CFG->prefix.'files WHERE '.$condition;

			if ($files = $DB->get_records_sql($query_str)) {
				foreach($files as $file) {
					$update['fileid']   = $file->id;
					$update['filename'] = $file->filename;
					break;
				}
			}
			$update['itemid'] = $picid;

			// insert to DB
			$ret = $DB->update_record('modlos_template_avatars', $update);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_db_fail', 'block_modlos').' (update)';
				return false;
			}

			// for Display
			$this->db_data             = $update;
			$this->db_data['html']     = $explain['text'];
			$this->db_data['fullname'] = '';
			$this->db_data['url']      = '';

			$name = opensim_get_avatar_name($update['uuid']);
			if ($name) $this->db_data['fullname'] = $name['fullname'];

			$usercontext = context_user::instance($USER->id);   // dummy. see lib.php
			if ($update['filename']) {
				$path = '@@PLUGINFILE@@/'.$update['filename'];
				$this->db_data['url'] = file_rewrite_pluginfile_urls($path, 'pluginfile.php', $usercontext->id, 'block_modlos', 'templ_picture', $update['itemid']);
			}

			$this->isPost = true;
		}

		//
		// GET
		else {
			$data = array();
			$data['templid'] = $template->id;
			$data['order']   = $template->num;
			$data['title'] 	 = $template->title;
			$data['uuid'] 	 = $template->uuid;
			$data['explain'] = array();
			$data['explain']['text']   = htmlspecialchars_decode($template->text);
			$data['explain']['format'] = $template->format;
			$data['picfile'] = $template->itemid;
			$data['valid']   = $template->status;

			$this->mform = new modlos_avatar_templ_form();
			$this->mform->set_data($data);
		}

		return true;
	}


	function  print_page() 
	{
		global $CFG;

		if (!$this->isPost and $this->mform==null) {
			$this->mform = new modlos_avatar_templ_form();	// Error
		}

		$grid_name  = $CFG->modlos_grid_name;

		$avatar     = $this->db_data;
		$mform      = $this->mform;
		$isPost		= $this->isPost;

		$url_params = $this->url_params;
		$return_url = $this->return_url;

		$avatar_templ_ttl = get_string('modlos_templ_ttl', 'block_modlos');
		$edit_avatar      = get_string('modlos_templ_edit_ttl', 'block_modlos');
		$edit_success     = get_string('modlos_templ_edit_ok',  'block_modlos');
		$edit_fail        = get_string('modlos_templ_edit_fail','block_modlos');
        $invalid_ttl      = get_string('modlos_invalid',    'block_modlos');
		$modlos_return    = get_string('modlos_return_ttl', 'block_modlos');
		$modlos_valid     = get_string('modlos_valid_ttl',  'block_modlos');
		$modlos_invalid   = get_string('modlos_invalid_ttl','block_modlos');

		include(CMS_MODULE_PATH.'/admin/html/avatar_templ_edit.html');
	}
}
