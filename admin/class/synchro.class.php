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
		if (!empty($_POST)) {
			$quest = optinal_param('quest', 'no', PARAM_ALPHA);
			if ($quest=="yes") {
				$ret = opensim_check_db();
				if (!$ret['grid_status']) {
					error(get_string('mdlos_db_connect_error', 'block_mdlopensim'));
				}

				opensim_supply_passwordSalt();
				opensim_succession_presence(XOPNSIM_HMREGION);
				$profs = opensim_get_avatar_profiles();
				xoopensim_set_profiles($profs, false);		// not over write

				$this->synchroDB();
			}
		}
	}



	function synchroDB()
	{
		// OpenSim DB
		$opsim_users = opensim_get_avatar_infos();
		if ($opsim_users==null) return;

		// Moodle DB
		$handler = & xoops_getmodulehandler('usersdb');
		$modobj  = & $handler->getObjects();
		$xoops_users = array();
		foreach ($modobj as $userobj) {
			$xoops_uuid = $userobj->getVar('UUID');
			$xoops_users[$xoops_uuid] = $userobj->gets();
		}

		// OpenSimにデータがある場合は，そちらにあわせる．
		foreach ($opsim_users as $opsim_user) {
			$opsim_user['uid']   = "";
			$opsim_user['state'] = "";
			if (array_key_exists($opsim_user['UUID'], $xoops_users)) {
				xoopensim_update_usertable($opsim_user);
			}
			else {
				xoopensim_insert_usertable($opsim_user);
			}
		}

		// OpenSimに対応データが無い場合はデータを消す．
		foreach ($xoops_users as $xoops_user) {
			$xoops_uuid = $xoops_user['UUID'];
			if (!array_key_exists($xoops_uuid, $opsim_users)) {
				$xoops_user['state'] = XOPNSIM_STATE_INACTIVE;
				xoopensim_delete_usertable($xoops_user);
			}
		}

		$this->synchronized = true;
	}



	function  executeView($render) 
	{
		$grid_name  = $CFG->mslod_grid_name;


		$render->setAttribute('admin_menu',   $admin_menu);
		$render->setAttribute('grid_name',	  $grid_name);
		$render->setAttribute('synchronized', $this->synchronized);
		$render->setAttribute('action_url',   $this->action_url);
		$render->setAttribute('actionForm',   $this->mActionForm);

		include(CMS_MODULE_PATH.'/admin/html/synchro.html');
	}
}

?>
