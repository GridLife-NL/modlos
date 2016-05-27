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
		$this->hasPermit = hasModlosPermit($course_id);
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
