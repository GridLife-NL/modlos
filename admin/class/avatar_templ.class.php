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
	var $context;

	var $action_url;
    var $url_param;
	var $course_id  = 0;
	var $hasPermit  = false;

//	var $editAvatar = false;
	var $editAvatar = true;

	var $mfrom		= null;

	var	$hasError   = false;
	var	$errorMsg   = array();



	function  AvatarTempl($course_id) 
	{
		$this->course_id = $course_id;
		$this->hasPermit = hasModlosPermit($this->course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
	
		$this->url_param  = '?course='.$this->course_id;
		$this->action_url = CMS_MODULE_URL.'/admin/actions/avatar_templ.php';
		$this->context    = jbxl_get_course_context($course_id);
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
			
			$title = required_param('title', PARAM_TEXT);
			$uuid  = required_param('uuid',  PARAM_TEXT);

			if ($title==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_invalid_ttl', 'block_modlos');
			}
			if (isGuid($uuid)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_templ_invalid_uuid', 'block_modlos');
			}
			if ($this->hasError) return false;


			// File Manager. see lib/filelib.php
			$picid = file_get_submitted_draft_itemid('picfile');
			file_save_draft_area_files($picid, $this->context->id, 'block_modlos', 'templ_picture', $picid, array('maxfiles'=>1));
print_r($ret);
echo "<br />";

			// Editor
			$explain = required_param_array('explain', PARAM_RAW);












/*
//    file_get_submitted_draft_itemid()
//    file_prepare_draft_area()
//    file_save_draft_area_files()


$editor = file_get_submitted_draft_itemid('explain');

$text = file_prepare_draft_area($editor, $this->context->id, 'block_modlos', 'templ_explain', null, null);
print_r($editor);
echo "<br />";
echo "------------------------------------------------------------------- <br />";
print_r($text);
echo "<br />";
echo "------------------------------------------------------------------- <br />";
 
			$expid = file_get_submitted_draft_itemid('explain');														// see lib/filelib.php
print_r($xxxx);
echo "<br />";

			file_save_draft_area_files($expid, $this->context->id, 'block_modlos', 'templ_explain', $expid, $maxfile_option);	// see lib/filelib.php
*/

/*
			$explain = required_param_array('explain', PARAM_RAW);
//			$data = new stdClass();
//			$data->explain_editor = $explain;

			$data = null;
			$data = file_prepare_standard_editor   ($data, 'explain', array('maxfiles'=>100), null, 'block_modlos', 'templ_explain', null);
			$data = file_postupdate_standard_editor($data, 'explain', $maxfile_option, $this->context, 'block_modlos', 'templ_explain', $explain['itemid']);
*/


/*



$course_id = optional_param('course', SITEID, PARAM_INT);
			//$expid = file_get_submitted_draft_itemid('explain');


print_r($_POST);

/*
Array ( [course] => 95 [sesskey] => sLKVyrYHFl [_qf__modlos_avatar_templ_form] => 1 [title] => sss [uuid] => ssss [explain] => Array ( [text] => [format] => 1 [itemid] => 147255135 ) [picfile] => 876368646 [submitbutton] => 変更を保存する ) 


			$text  = file_save_draft_area_files($expid, $this->context->id, 'block_modlos', 'templ_explain', $expid, array());

//			redirect($this->action_url.$this->url_param, 'Please wait....', 0);


			$data = file_prepare_standard_editor($data, 'textfield', $textfieldoptions, $context, 'mod_somemodule', 'somearea', $data->id);
*/
			


/*
			//file_prepare_draft_area   ($picid, $this->context->id, 'block_modlos', 'templ_picture', $entry->id, array());
			$expid = file_get_submitted_draft_itemid('explain');
			$text = file_prepare_draft_area($expid, $this->context->id, 'block_modlos', 'templ_explain', $entry->id, array());

			$entry->attachments = $picid;
			$entry->entry = array('text'=>$text, 'format'=>FORMAT_HTML, 'itemid'=>$expid);

			$this->mform->set_data(array('id'=>$this->course_id));

/*

			$maxfiles  = 1;
			$maxbytes  = $this->course->maxbytes;


//			$fmoption = array('subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes);
			$entry = file_prepare_standard_editor($entry, 'explain', array(), $this->context->id, 'block_modlos', 'templ_explain', $entry->id);
			$entry = file_prepare_standard_filemanager($entry, 'picfile', array(), null, 'block_modlos', 'templ_picture', $entry->id);
print_r($_POST);
echo "$this->context->id<br />";
			$entry = file_postupdate_standard_editor($entry, 'explain', array(), null, 'block_modlos', 'templ_explain', $entry->id);
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
//$draftitemid = file_get_submitted_draft_itemid('explain');
//echo "XXX => $draftitemid<br />";
//$draftitemid = file_get_submitted_draft_itemid('picfile');
//echo "XXX => $draftitemid<br />";


/*
			$definitionoptions = array('subdirs'=>false, true, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes, 'trusttext'=>true);
			$attachmentoptions = array('subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes);

			$entry = file_prepare_standard_editor($entry, 'definition', $definitionoptions, $context, 'block_modlos', 'entry', $entry->id);
			$entry = file_prepare_standard_filemanager($entry, 'attachment', $attachmentoptions, $context, 'block_modlos', 'attachment', $entry->id);

			$entry->cmid = $cm->id;
*/









		}

		require_once(CMS_MODULE_PATH.'/admin/lib/modlos_avatar_templ_form.php');
		$this->mform = new modlos_avatar_templ_form(true);		// clear POST
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
	}
}
