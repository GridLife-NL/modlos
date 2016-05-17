<?php
///////////////////////////////////////////////////////////////////////////////
//	convertdb.class.php
//
//	OpenSimのDB 0.6.x を 0.7に変換する
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


class  ConvertDataBase
{
	var $action_url;
	var $hashPermit;
	var $course_id = 0;
	var	$dbconverted= false;
	var	$hasError  = false;
	var	$errorMsg  = array();



	function  ConvertDataBase($course_id) 
	{
		$this->course_id = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		$this->action_url = CMS_MODULE_URL.'/admin/actions/convertdb.php';
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
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}

			$quest = optional_param('quest', 'no', PARAM_ALPHA);
			if ($quest=='yes') {
				$ret = opensim_check_db();
				if (!$ret['grid_status']) {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_db_connect_error', 'block_modlos');
					return false;
				}

				//opensim_supply_passwordSalt();
				opensim_succession_data(env_get_config('home_region'));
				opensim_recreate_presence();
				$profs = opensim_get_avatars_profiles_from_users();
				if ($profs!=null) {		// 0.6.x
					modlos_set_profiles_from_users($profs, false);		// not over write
				}

				$this->dbconverted = true;
			}
		}

		return $this->dbconverted;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name		  = $CFG->modlos_grid_name;
		$convertdb_ttl 	  = get_string('modlos_convertdb_ttl', 	    'block_modlos');
		$convertdb_msg    = get_string('modlos_convertdb_convrted',	'block_modlos');
		$convertdb_submit = get_string('modlos_convertdb_submit', 	'block_modlos');
		$content		  = '<center>'.get_string('modlos_convertdb_contents', 'block_modlos').'</center>';

		include(CMS_MODULE_PATH.'/admin/html/convertdb.html');
	}
}
