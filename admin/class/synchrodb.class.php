<?php
///////////////////////////////////////////////////////////////////////////////
//	syncro.class.php
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
	var	$errorMsg = "";



	function  SyncroDataBase($courseid) 
	{
		require_login($courseid);

		$this->courseid  = $courseid;
		$this->hasPermit = hasPermit($courseid);
		if (!$hasPermit) {
			error(get_string('mdlos_access_forbidden', 'block_mdlopensim'));
		}

		$this->action_url = _OPENSIM_MODULE_URL."/admin/actions/syncrodb.php?action=synchro";
	}



	function  execute()
	{
		if ($this->hasPermit and data_submitted() and confirm_sesskey()) {
			$quest = optinal_param('quest', 'no', PARAM_ALPHA);
			if ($quest=="yes") {
				$ret = opensim_check_db();
				if (!$ret['grid_status']) {
					error(get_string('mdlos_db_connect_error', 'block_mdlopensim'));
				}

				opensim_supply_passwordSalt();
				opensim_succession_data(OPENSIM_HMREGION);
				opensim_recreate_presence();
				$profs = opensim_get_avatars_profiles();
				if ($profs!=null) mdlopensim_set_profiles($profs, false);		// not over write

				$this->synchroDB();
			}
		}
	}



	function synchroDB()
	{
		// OpenSim DB
		$opsim_users = opensim_get_avatars_infos();
		if ($opsim_users==null) return;

		// Moodle DB
		$modobj = & $handler->getObjects();
		$moodle_users = array();
		foreach ($modobj as $userobj) {
			$xoops_uuid = $userobj->getVar('UUID');
			$xoops_users[$xoops_uuid] = $userobj->gets();
		}

		// OpenSimにデータがある場合は，そちらにあわせる．
		foreach ($opsim_users as $opsim_user) {
			$opsim_user['uid']   = "";
			$opsim_user['state'] = "";
			if (array_key_exists($opsim_user['UUID'], $xoops_users)) {
				mdleopensim_update_usertable($opsim_user);
			}
			else {
				mdlopensim_insert_usertable($opsim_user);
			}
		}

		// OpenSimに対応データが無い場合はデータを消す．
		foreach ($xoops_users as $xoops_user) {
			$xoops_uuid = $xoops_user['UUID'];
			if (!array_key_exists($xoops_uuid, $opsim_users)) {
				$xoops_user['state'] = XOPNSIM_STATE_INACTIVE;
				mdlopensim_delete_usertable($xoops_user);
			}
		}

		$this->synchronized = true;
	}



	function  print_page() 
	{
        global $CFG;

        $this->execute();

        $grid_name  	= $CFG->mdlopnsm_grid_name;
		$syncro_db_ttl 	= get_string("mdlos_synchro_db", "block_mdlopensim");
		$syncronized_ttl= get_string("mdlos_synchronized", "block_mdlopensim");
		$content		= get_string("mdlos_synchro_contents'", "block_mdlopensim");
		$syncro_submit	= get_string("mdlos_synchro_submit", "block_mdlopensim");

		include(CMS_MODULE_PATH."/admin/html/syncrodb.html");
	}
}

?>
