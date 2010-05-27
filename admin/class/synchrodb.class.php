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
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");


class  SynchroDataBase
{
	var $action_url;
	var $hashPermit;
	var $courseid;
	var	$synchronized = false;
	var	$hasError = false;
	var	$errorMsg = array();



	function  SynchroDataBase($courseid) 
	{
		require_login($courseid);

		$this->courseid  = $courseid;
		$this->hasPermit = hasPermit($courseid);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('mdlos_access_forbidden', 'block_mdlopensim');
			return;
		}
		$this->action_url = CMS_MODULE_URL."/admin/actions/synchrodb.php";
	}



	function  execute()
	{
		if (data_submitted()) {		// POST
			if (!$this->hasPermit) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('mdlos_access_forbidden', 'block_mdlopensim');
				return false;
			}

			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
				return false;
			}

			$quest = optional_param('quest', 'no', PARAM_ALPHA);
			if ($quest=="yes") {
				$ret = opensim_check_db();
				if (!$ret['grid_status']) {
					$this->hasError = true;
					$this->errorMsg[] = get_string('mdlos_db_connect_error', 'block_mdlopensim');
					return false;
				}

				opensim_supply_passwordSalt();
				opensim_succession_data(OPENSIM_HMREGION);
				opensim_recreate_presence();
				$profs = opensim_get_avatars_profiles_from_users();
				if ($profs!=null) mdlopensim_set_profiles($profs, false);		// not over write

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

		// Mdlopensim DB を読んで配列に変換
		$db_users = get_records('mdlos_users');
		$mdlos_users = array();
		foreach ($db_users as $user) {
			$mdlos_uuid = $user->uuid;
			$mdlos_users[$mdlos_uuid]['id'] 	   = $user->id;
			$mdlos_users[$mdlos_uuid]['UUID'] 	   = $user->uuid;
			$mdlos_users[$mdlos_uuid]['uid']	   = $user->user_id;
			$mdlos_users[$mdlos_uuid]['firstname'] = $user->firstname;
			$mdlos_users[$mdlos_uuid]['lastname']  = $user->lastname;
			$mdlos_users[$mdlos_uuid]['hmregion']  = $user->hmregion;
			$mdlos_users[$mdlos_uuid]['state']     = $user->state;
			$mdlos_users[$mdlos_uuid]['time']  	   = $user->time;
		}

		// OpenSimにデータがある場合は，Mdlopensim のデータを OpenSimにあわせる．
		foreach ($opsim_users as $opsim_user) {
			$opsim_user['uid']   = 0;
			$opsim_user['time']  = time();
			$opsim_user['state'] = AVATAR_STATE_ACTIVE;

			if (array_key_exists($opsim_user['UUID'], $mdlos_users)) {
				$opsim_user['id'] = $mdlos_users[$opsim_user['UUID']]['id'];
				mdlopensim_update_usertable($opsim_user);
			}
			else {
				mdlopensim_insert_usertable($opsim_user);
			}
		}

		// OpenSimに対応データが無い場合はデータを消す．
		foreach ($mdlos_users as $mdlos_user) {
			$moodle_uuid = $mdlos_user['UUID'];
			if (!array_key_exists($moodle_uuid, $opsim_users)) {
				$mdlos_user['state'] = AVATAR_STATE_INACTIVE;
				mdlopensim_delete_usertable($mdlos_user);
			}
		}


		// Sloodle連携
		if ($CFG->mdlopnsm_cooperate_sloodle) {
			$sloodles = get_records(MDL_SLOODLE_USERS_TBL);
			if (is_array($sloodles)) {
				foreach($sloodles as $sloodle) {
					$mdl = get_record('mdlos_users', 'uuid', $sloodle->uuid);
					if ($mdl!=null) {
						if (($mdl->user_id>0 and $CFG->mdlopnsm_priority_sloodle) or
							($mdl->user_id==0)) { 
							$mdl->user_id = $sloodle->userid;
							update_record('mdlos_users', $mdl);
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

        $this->execute();

        $grid_name  	  = $CFG->mdlopnsm_grid_name;
		$synchro_db_ttl   = get_string("mdlos_synchro_db", 		 "block_mdlopensim");
		$synchronized_msg = get_string("mdlos_synchronized", 	 "block_mdlopensim");
		$synchro_submit	  = get_string("mdlos_synchro_submit", 	 "block_mdlopensim");
		$content		  = get_string("mdlos_synchro_contents", "block_mdlopensim");
		$content		  = "<center>".$content."</center>";

		include(CMS_MODULE_PATH."/admin/html/synchro.html");
	}
}

?>
