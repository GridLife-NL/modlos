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
		$this->hasPermit = hasPermit($course_id);
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

		// OpenSim DB
		$opsim_users = opensim_get_avatars_infos();

		// Modlos DB を読んで配列に変換
		$db_users = get_records('modlos_users');
		$modlos_users = array();
		foreach ($db_users as $user) {
			$modlos_uuid = $user->uuid;
			$modlos_users[$modlos_uuid]['id']		= $user->id;
			$modlos_users[$modlos_uuid]['UUID'] 	= $user->uuid;
			$modlos_users[$modlos_uuid]['uid']	   	= $user->user_id;
			$modlos_users[$modlos_uuid]['firstname']= $user->firstname;
			$modlos_users[$modlos_uuid]['lastname']	= $user->lastname;
			$modlos_users[$modlos_uuid]['hmregion']	= $user->hmregion;
			$modlos_users[$modlos_uuid]['state']	= $user->state;
			$modlos_users[$modlos_uuid]['time']		= $user->time;
		}

		// OpenSimにデータがある場合は，Modlos のデータを OpenSimにあわせる．
		foreach ($opsim_users as $opsim_user) {
			$opsim_user['uid']   = 0;
			$opsim_user['time']  = time();
			$opsim_user['state'] = AVATAR_STATE_SYNCDB | AVATAR_STATE_SLOODLE;

			if (array_key_exists($opsim_user['UUID'], $modlos_users)) {
				$opsim_user['id'] = $modlos_users[$opsim_user['UUID']]['id'];
				modlos_update_usertable($opsim_user);
			}
			else {
				modlos_insert_usertable($opsim_user);
			}
		}

		// OpenSimに対応データが無い場合はデータを消す．
		foreach ($modlos_users as $modlos_user) {
			$moodle_uuid = $modlos_user['UUID'];
			if (!array_key_exists($moodle_uuid, $opsim_users)) {
				$modlos_user['state'] &= AVATAR_STATE_INACTIVE;
				modlos_delete_usertable($modlos_user);
			}
		}


		// Sloodle連携
		if ($CFG->modlos_cooperate_sloodle) {
			$sloodles = get_records(MDL_SLOODLE_USERS_TBL);
			if (is_array($sloodles)) {
				foreach($sloodles as $sloodle) {
					$mdl = get_record('modlos_users', 'uuid', $sloodle->uuid);
					if ($mdl!=null) {
						if (($mdl->user_id>0 and $CFG->modlos_priority_sloodle) or ($mdl->user_id==0)) { 
							$mdl->user_id = $sloodle->userid;
							update_record('modlos_users', $mdl);
						}
					}
				}
			}
		}

		return true;
	}



	function  print_page() 
	{
        global $CFG;

        $grid_name  	  = $CFG->modlos_grid_name;
		$synchro_db_ttl   = get_string("modlos_synchro_db", 		 "block_modlos");
		$synchronized_msg = get_string("modlos_synchronized", 	 "block_modlos");
		$synchro_submit	  = get_string("modlos_synchro_submit", 	 "block_modlos");
		$content		  = "<center>".get_string("modlos_synchro_contents", "block_modlos")."</center>";

		include(CMS_MODULE_PATH."/admin/html/synchro.html");
	}
}

?>
