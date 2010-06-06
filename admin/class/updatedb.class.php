<?php
///////////////////////////////////////////////////////////////////////////////
//	updatedb.class.php
//
//	OpenSimのDBと MoodleのDBの同期をとる．
//
// 		OpenSimにデータがある場合は，Moodleのデータを OpenSimに合わせる．
// 		OpenSimに対応データが無い場合は，Moodleのデータを消す．
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


class  UpdateDataBase
{
	var $action_url;
	var $hashPermit;
	var $course_id = 0;
	var	$updateddb = false;
	var	$hasError  = false;
	var	$errorMsg  = array();



	function  UpdateDataBase($course_id) 
	{
		require_login($course_id);

		$this->course_id  = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		$this->action_url = CMS_MODULE_URL."/admin/actions/updatedb.php";
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

				opensim_supply_passwordSalt();
				opensim_succession_data(OPENSIM_HMREGION);
				opensim_recreate_presence();
				$profs = opensim_get_avatars_profiles_from_users();
				if ($profs!=null) modlos_set_profiles($profs, false);		// not over write
			}
		}

		return $this->updateddb;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name	   = $CFG->modlos_grid_name;
		$update_db_ttl = get_string("modlos_updatedb_db", 		"block_modlos");
		$updated_msg   = get_string("modlos_updatedb_executed",	"block_modlos");
		$update_submit = get_string("modlos_updatedb_submit", 	"block_modlos");
		$content	   = "<center>".get_string("modlos_updatedb_contents", "block_modlos")."</center>";

		include(CMS_MODULE_PATH."/admin/html/updatedb.html");
	}

}

?>
