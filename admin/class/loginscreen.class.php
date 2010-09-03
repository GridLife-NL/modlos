<?php
///////////////////////////////////////////////////////////////////////////////
//	loginscreen.class.php
//
//	Login Screen Message
//
//
//
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


class  LoginScreen
{
	var $action_url;
	var $hashPermit;
	var $course_id = 0;
	var	$updated   = false;
	var	$hasError  = false;
	var	$errorMsg  = array();

	var	$lgnscrn_ckey    = 1;
	var	$lgnscrn_infobox = '';


	function  LoginScreen($course_id) 
	{
		require_login($course_id);

		$this->course_id  = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		$this->action_url = CMS_MODULE_URL."/admin/actions/loginscreen.php";
	}



	function  execute()
	{
		if (data_submitted()) {		// POST
			if (!$this->hasPermit) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
				return false;
			}

			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("modlos_sesskey_error", "block_modlos");
				return false;
			}

			$quest = optional_param('quest', 'no', PARAM_ALPHA);
			$this->lgnscrn_ckey    = optional_param('lgnscrn_color',  '1', PARAM_INT);
			$this->lgnscrn_infobox = optional_param('lgnscrn_infobox', '', PARAM_TEXT);

			if ($quest=="yes") {
				$ret = opensim_check_db();
				if (!$ret['grid_status']) {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_db_connect_error', 'block_modlos');
					return false;
				}


			//		opensim_succession_data(OPENSIM_HMREGION);
			//		opensim_recreate_presence();
			//		$profs = opensim_get_avatars_profiles_from_users();
			//		if ($profs!=null) modlos_set_profiles_from_users($profs, false);        // not over write


				$this->updated = true;
			}
		}

		return $this->updated;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name	   	= $CFG->modlos_grid_name;
		$lgnscrn_ttl    = get_string('modlos_lgnscrn_ttl', 	  'block_modlos');
		$lgnscrn_msg    = get_string('modlos_lgnscrn_done',   'block_modlos');
		$lgnscrn_submit = get_string('modlos_lgnscrn_submit', 'block_modlos');
		$select_color	= get_string('modlos_lgnscrn_color',  'block_modlos');
		$edit_infobox	= get_string('modlos_lgnscrn_infobox','block_modlos');
		$colors			= array(0=>'white', 1=>'green', 2=>'yellow', 3=>'red');
		$content		= '<center>'.get_string('modlos_lgnscrn_contents', 'block_modlos').'</center>';
		$updated		= $this->updated;

		$lgnscrn_color  = $colors[$this->lgnscrn_ckey];
		$lgnscrn_infobox= $this->lgnscrn_infobox;




		$course_amp    	= '';
		if ($this->course_id>0) $course_param = '?course='.$this->course_id;
		$lgnscrn_url	= CMS_MODULE_URL.'/admin/actions/loginscreen.php'.$course_param;
		$return_ttl	   	= get_string('modlos_lgnscrn_return', 'block_modlos');

		include(CMS_MODULE_PATH."/admin/html/loginscreen.html");
	}

}

?>
