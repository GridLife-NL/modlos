<?php
///////////////////////////////////////////////////////////////////////////////
//	management.class.php
//
//	管理
//
//
//
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


class  ManagementBase
{
	var $action_url;
	var $hashPermit;
	var $course_id = 0;
	var	$managed   = false;
	var	$hasError  = false;
	var	$errorMsg  = array();



	function  ManagementBase($course_id) 
	{
		require_login($course_id);

		$this->course_id  = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		$this->action_url = CMS_MODULE_URL."/admin/actions/management.php";
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
			if ($quest=="yes") {
				$ret = opensim_check_db();
				if (!$ret['grid_status']) {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_db_connect_error', 'block_modlos');
					return false;
				}


				$this->managed = true;
			}
		}

		return $this->managed;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name	   = $CFG->modlos_grid_name;
		$manage_ttl    = get_string("modlos_manage_ttl", 	"block_modlos");
		$manage_msg    = get_string("modlos_manage_done", 	"block_modlos");
		$manage_submit = get_string("modlos_manage_submit", "block_modlos");
		$content	   = "<center>".get_string("modlos_manage_contents", "block_modlos")."</center>";

		include(CMS_MODULE_PATH."/admin/html/management.html");
	}

}

?>
