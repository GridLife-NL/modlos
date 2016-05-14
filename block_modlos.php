<?php

require_once(realpath(dirname(__FILE__)."/../../config.php"));
require_once(realpath(dirname(__FILE__)."/include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH."/include/opensim.mysql.php");
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


class block_modlos extends block_base 
{
	var $grid_name;
	var $grid_status;
	var $user_count;
	var $region_count;
	var $lastmonth_online;
	var $now_online;


	function init()
	{
		global $CFG;

		if (empty($plugin)) $plugin = new stdClass();
		include($CFG->dirroot.'/blocks/modlos/version.php');

		$this->title   = get_string('modlos_menu', 'block_modlos');
		$this->version = $plugin->version;
		$this->release = $plugin->release;

		//
		$this->grid_name 		= $CFG->modlos_grid_name;
		$this->grid_status 		= false;
		$this->now_online 		= '0';
		$this->lastmonth_online = '0';
		$this->user_count 		= '0';
		$this->region_count 	= '0';
		$this->cron 			= '1';

		if (!isset($CFG->sloodle_update)) set_config('sloodle_update', 0);
		if (!isset($CFG->opensim_update)) set_config('opensim_update', 0);
	}


	function get_content()
	{
		global $CFG, $USER;

		if ($this->content!=NULL) {
			return $this->content;
		}
		$id = optional_param('id', 0, PARAM_INT);
	   
		$db_ver = opensim_get_db_version();

		$this->content = new stdClass;
		$this->content->text = '<a href="'.CMS_MODULE_URL.'/actions/show_status.php?course='.$id.'">'. get_string('modlos_status','block_modlos').'</a><br />';
		$this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/map_action.php?course='.$id.'">'.  get_string('modlos_world_map','block_modlos').'</a><br />';
		$this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/regions_list.php?course='.$id.'&order=name">'.get_string('modlos_regions_list','block_modlos').'</a><br />';

		$isguest = isguestuser();
		if (!$isguest and $USER->id!=0) {
			$this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/avatars_list.php?course='.$id.'&order=login&desc=1">'.get_string('modlos_avatars_list','block_modlos').'</a><br />';
			$this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/personal_avatars.php?course='.$id.'">'.get_string('modlos_my_avatars','block_modlos').'</a><br />';

			$isAvatarMax = false;
			if ($db_ver!=null) { 
				$avatars_num = modlos_get_avatars_num($USER->id);
				$max_avatars = $CFG->modlos_max_own_avatars;
				if (!hasModlosPermit($id) and $max_avatars>=0 and $avatars_num>=$max_avatars) $isAvatarMax = true;
			}
			if (!$isAvatarMax) {
				$this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/create_avatar.php?course='.$id.'">'.get_string('modlos_avatar_create','block_modlos').'</a><br />';
			}
			if ($CFG->modlos_activate_events) {
				$this->content->text.= '<a href="'.CMS_MODULE_URL.'/actions/events_list.php?course='.$id.'">'.get_string('modlos_events_list','block_modlos').'</a><br />';
			}
			if (hasModlosPermit($id)) {
				$this->content->text.= '<a href="'.CMS_MODULE_URL.'/admin/actions/management.php?course='.$id.'">'.get_string('modlos_manage_menu','block_modlos').'</a><br />';
			}
		}
		$this->content->text.= "<hr />";		

		if ($db_ver!=null and $CFG->modlos_connect_db) { 
			$db_state = opensim_check_db();
			$this->grid_status 		= $db_state['grid_status'];
			$this->now_online 	 	= $db_state['now_online'];
			$this->lastmonth_online = $db_state['lastmonth_online'];
			$this->user_count  		= $db_state['user_count'];
			$this->region_count		= $db_state['region_count'];
		}
		else {
			$this->grid_status 		= false;
			$this->now_online 	 	= 0;
			$this->lastmonth_online = 0;
			$this->user_count  		= 0;
			$this->region_count		= 0;
		}
		if (!$this->grid_status) set_config('modlos_connect_db', 0);

		$this->content->text.= "<center><b>".$this->grid_name."</b></center>";		
		$this->content->text.= get_string('modlos_db_status','block_modlos').": ";		
		if ($this->grid_status) $this->content->text.= "<b><font color=\"#129212\">ONLINE</font></b><br />";		
		else					$this->content->text.= "<b><font color=\"#ea0202\">OFFLINE</font></b><br />";		
		$this->content->text.= get_string('modlos_total_users','block_modlos').": <b>".$this->user_count."</b><br />";		
		$this->content->text.= get_string('modlos_total_regions','block_modlos').": <b>".$this->region_count."</b><br />";		
		$this->content->text.= get_string('modlos_visitors_last30days','block_modlos').": <b>".$this->lastmonth_online."</b><br />";		
		$this->content->text.= get_string('modlos_online_now','block_modlos').": <b>".$this->now_online."</b><br />";		

		$this->content->footer = '<hr /><a href="http://www.nsl.tuis.ac.jp/xoops/modules/xpwiki/?Modlos" target="_blank"><i>Modlos '.$this->release.'</i></a>';

		return $this->content;
	}


	// setting of instance block. need config_instance.html
	function instance_allow_config()
	{
		return false;
	}


	// setting block. need settings.php
	function has_config()
	{
		return true;
	}


	// hide block header?
	function hide_header() 
	{
		return false;
	}


	// exec parser
	function cron(){
		global $CFG;
       
		require($CFG->dirroot.'/blocks/modlos/include/cron.php');
	}
}

?>
