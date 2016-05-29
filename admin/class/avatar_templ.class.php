<?php
///////////////////////////////////////////////////////////////////////////////
//	avatar_templ.class.php
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


class  AvatarTempl
{
	var $course;
	var $action_url;
    var $url_param;
	var $course_id  = 0;
	var $hasPermit  = false;

//	var $editAvatar = false;
	var $editAvatar = true;

	var $mfrom		= null;

	var	$hasError   = false;
	var	$errorMsg   = array();



	function  AvatarTempl($course) 
	{
		$this->course_id = $course->id;
		$this->course = $course;
		$this->hasPermit = hasModlosPermit($this->course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
	
		$this->url_param  = '?course='.$this->course_id;
		$this->action_url = CMS_MODULE_URL.'/admin/actions/avatar_templ.php';
	}


	function  execute()
	{
		global $CFG;

		if (!$this->hasPermit) return false;

		if ($formdata = data_submitted()) {	// POST
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}

			$entry = new stdClass;
			$entry->id = null;
			$maxfiles  = 1;
			$maxbytes  = $this->course->maxbytes;


			$definitionoptions = array('subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes, 'trusttext'=>true);
			$attachmentoptions = array('subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes);
			$entry = file_prepare_standard_editor($entry, 'explain', $definitionoptions, null, 'block_modlos', 'exntry', $entry->id);
print_r($entry);
echo "<br />";

/*
			if ($data = $formdata->get_data()) {
    			// ... store or update $entry
				$option = array('subdirs'=>0, 'maxbytes'=>$maxbytes, 'maxfiles'=>50);
//				file_save_draft_area_files($data->attachments, $context->id, 'block_modlos', 'attachment', $entry->id, $option);
print_r($data);
			}
*/

//print_r($_POST);
//$draftitemid = file_get_submitted_draft_itemid('picfile');
//echo "XXX => $draftitemid";


/*
			$definitionoptions = array('subdirs'=>false, true, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes, 'trusttext'=>true);
			$attachmentoptions = array('subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes);

			$entry = file_prepare_standard_editor($entry, 'definition', $definitionoptions, $context, 'block_modlos', 'entry', $entry->id);
			$entry = file_prepare_standard_filemanager($entry, 'attachment', $attachmentoptions, $context, 'block_modlos', 'attachment', $entry->id);

			$entry->cmid = $cm->id;
*/









		}





		require_once(CMS_MODULE_PATH.'/admin/lib/modlos_avatar_templ_form.php');
		$this->mform = new modlos_avatar_templ_form();
		$this->mform->set_data(array('id'=>$this->course_id));

		return true;
	}


	function  print_page() 
	{
		global $CFG;

		$grid_name  = $CFG->modlos_grid_name;

		$url_param  = $this->url_param;
		$action_url = $this->action_url;
		$mform      = $this->mform;

		$avatar_templ_ttl = get_string('modlos_avatar_templ_ttl', 'block_modlos');
		$content          = get_string('modlos_avatar_templ_ttl', 'block_modlos');

		include(CMS_MODULE_PATH.'/admin/html/avatar_templ.html');

//		$mform->display();

	}
}
