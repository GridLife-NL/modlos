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
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


class  ManagementBase
{
	var $action_url;
	var $hashPermit;
	var $course_id = 0;
	var	$managed   = false;
	var	$hasError  = false;
	var	$errorMsg  = array();
	var	$command   = '';



	function  ManagementBase($course_id) 
	{
		$this->course_id = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
		$this->action_url = CMS_MODULE_URL.'/admin/actions/management.php';
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

			$quest   = optional_param('quest', 'no', PARAM_ALPHA);
			$command = optional_param('manage_command', '', PARAM_ALPHA);
			if ($quest=='yes' && $command!='') {
				$ret = opensim_check_db();
				if (!$ret['grid_status']) {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_db_connect_error', 'block_modlos');
					return false;
				}

				$this->command = $command;
				$this->managed = true;

				// Command
				if ($command=='cltexture') {
					$cachedir = CMS_MODULE_PATH.'/helper/texture_cache';
					$command  = "cd $cachedir && /bin/sh cache_clear.sh";
					exec($command);
				}
				else if ($command=='clpresence') {
					opensim_clear_login_table();
				}
				else if ($command=='convertdb') {
					opensim_succession_data(env_get_config('home_region'));
					opensim_recreate_presence();
					$profs = opensim_get_avatars_profiles_from_users();
					if ($profs!=null) modlos_set_profiles_from_users($profs, false);        // not over write
				}
				else if ($command=='debugcom') {
					opensim_debug_command();
				}
				else {
					$this->managed = false;
				}
			}
		}

		return $this->managed;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name	   = $CFG->modlos_grid_name;
		$manage_ttl    = get_string('modlos_manage_ttl', 	'block_modlos');
		$manage_msg    = get_string('modlos_manage_done', 	'block_modlos');
		$manage_submit = get_string('modlos_manage_submit', 'block_modlos');
		$select_cmd	   = get_string('modlos_manage_select', 'block_modlos');
		$command	   = $this->command;
		$content	   = '<center>'.get_string('modlos_manage_contents', 'block_modlos').'</center>';

		$course_param  = '';
		if ($this->course_id>0) $course_param = '?course='.$this->course_id;
		$manage_url    = CMS_MODULE_URL.'/admin/actions/management.php'.$course_param;
		$return_ttl	   = get_string('modlos_manage_return', 'block_modlos');

		$commands[0]['com'] = 'cltexture';
		$commands[0]['ttl'] = get_string('modlos_cltexture_ttl', 'block_modlos');
		$commands[1]['com'] = 'clpresence';
		$commands[1]['ttl'] = get_string('modlos_clpresence_ttl', 'block_modlos');
		//
		$commands[2]['com'] = 'debugcom';
		$commands[2]['ttl'] = get_string('modlos_debugcom_ttl', 'block_modlos');
		//$commands[2]['com'] = 'convertdb';
		//$commands[2]['ttl'] = get_string('modlos_convertdb_ttl', 'block_modlos');

		include(CMS_MODULE_PATH.'/admin/html/management.html');
	}

}

?>
