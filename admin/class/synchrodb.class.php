<?php
///////////////////////////////////////////////////////////////////////////////
//	synchro.class.php
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


class  SynchroDataBase
{
	var $action_url;
	var $hashPermit;
	var $course_id = 0;
	var	$synchronized = false;
	var	$hasError = false;
	var	$errorMsg = array();



	function  SynchroDataBase($course_id) 
	{
		require_login($course_id);

		$this->course_id  = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		$this->action_url = CMS_MODULE_URL."/admin/actions/synchrodb.php";
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

				$this->synchronized = $this->synchroDB();
			}
		}

		return $this->synchronized;
	}



	function synchroDB()
	{
		global $CFG;

		$opnsim_users = opensim_get_avatars_infos();	// OpenSim DB
		$modlos_users = modlos_get_userstable(); 		// Modlos DB

		// OpenSimに対応データが無い場合はデータを消す．
		foreach ($modlos_users as $modlos_user) {
			$moodle_uuid = $modlos_user['UUID'];
			if (!array_key_exists($moodle_uuid, $opnsim_users)) {
				$modlos_user['state'] = (int)$modlos_user['state']|AVATAR_STATE_INACTIVE;
				modlos_delete_userstable($modlos_user);
			}
		}

		// OpenSimにデータがある場合は，Modlos のデータを OpenSimにあわせる．
		foreach ($opnsim_users as $opnsim_user) {
			$opnsim_user['time'] = time();
			if (array_key_exists($opnsim_user['UUID'], $modlos_users)) {
				//$opnsim_user['id'] = $modlos_users[$opnsim_user['UUID']]['id'];
				$opnsim_user['uid']   = $modlos_users[$opnsim_user['UUID']]['uid'];
				$opnsim_user['state'] = $modlos_users[$opnsim_user['UUID']]['state'];
				modlos_update_userstable($opnsim_user);
			}
			else {
				$opnsim_user['uid']   = 0;
				$opnsim_user['state'] = AVATAR_STATE_SYNCDB;
				modlos_insert_userstable($opnsim_user);
			}
		}


		//
		// Sloodle連携
		//
		if ($CFG->modlos_cooperate_sloodle) {
			modlos_sync_sloodle_users();
		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name  	  = $CFG->modlos_grid_name;
		$synchro_db_ttl   = get_string("modlos_synchro_db", 	"block_modlos");
		$synchronized_msg = get_string("modlos_synchronized", 	"block_modlos");
		$synchro_submit	  = get_string("modlos_synchro_submit", "block_modlos");
		$content		  = "<center>".get_string("modlos_synchro_contents", "block_modlos")."</center>";

		include(CMS_MODULE_PATH."/admin/html/synchro.html");
	}
}

?>
