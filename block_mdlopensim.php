<?php

require_once(realpath(dirname(__FILE__)."/../../config.php"));
require_once(realpath(dirname(__FILE__)."/include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();

require_once(MDLOPNSM_BLK_PATH."/include/opensim.func.php");


class block_mdlopensim extends block_base 
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

		$this->title   = get_string('mdlopensim', 'block_mdlopensim');
		$this->version = 2010051222;
		$this->release = '1.0.0';

		$this->grid_name = $CFG->mdlopnsm_grid_name;
		$this->grid_status = false;
		$this->now_online = '0';
		$this->lastmonth_online = '0';
		$this->user_count = '0';
		$this->region_count = '0';
	}


	function get_content()
	{
		global $CFG;

		if ($this->content!=NULL) {
			return $this->content;
		}
		$id = optional_param('id', 0, PARAM_INT);
	   

		$this->content = new stdClass;

		$this->content->text = '<a href="'.MDLOPNSM_BLK_URL.'/actions/db_status.php&course='.$id.'">'.get_string('mdlos_db_status','block_mdlopensim').'</a><br />';
		$this->content->text.= '<a href="'.MDLOPNSM_BLK_URL.'/actions/map_action.php?course='.$id.'">'.get_string('mdlos_world_map','block_mdlopensim').'</a><br />';
		$this->content->text.= '<a href="'.MDLOPNSM_BLK_URL.'/actions/regions_list.php?course='.$id.'">'.get_string('mdlos_regions_list','block_mdlopensim').'</a><br />';
		if (!isguest()) {
			$this->content->text.= '<a href="'.MDLOPNSM_BLK_URL.'/actions/avatars_list?course='.$id.'">'.get_string('mdlos_avatars_list','block_mdlopensim').'</a><br />';
			$this->content->text.= '<a href="'.MDLOPNSM_BLK_URL.'/actions/avatar_make?course='.$id.'">'. get_string('mdlos_avatar_make','block_mdlopensim').'</a><br />';
/*
			if (isadmin()) {
				$this->content->text.= '<hr />';
				$this->content->text.= '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=blocksettingmdlopensim">'.
										get_string('mdlos_general_setting_menu','block_mdlopensim').'</a><br />';
			}
*/
		}
		$this->content->text.= "<hr />";		

		$db_state = opensim_check_db();
		$this->grid_status 		= $db_state['grid_status'];
		$this->now_online 	 	= $db_state['now_online'];
		$this->lastmonth_online = $db_state['lastmonth_online'];
		$this->user_count  		= $db_state['user_count'];
		$this->region_count		= $db_state['region_count'];

		$this->content->text.= "<center><b>".$this->grid_name."</b></center>";		
		$this->content->text.= get_string('mdlos_db_status','block_mdlopensim').": ";		
		if ($this->grid_status) $this->content->text.= "<b><font color=\"#129212\">".get_string('mdlos_online_ttl', 'block_mdlopensim')."</font></b><br />";		
		else					$this->content->text.= "<b><font color=\"#ea0202\">".get_string('mdlos_offline_ttl','block_mdlopensim')."</font></b><br />";		
		$this->content->text.= get_string('mdlos_total_users','block_mdlopensim').": ".$this->user_count."<br />";		
		$this->content->text.= get_string('mdlos_total_regions','block_mdlopensim').": ".$this->region_count."<br />";		
		$this->content->text.= get_string('mdlos_visitors_last30days','block_mdlopensim').": ".$this->lastmonth_online."<br />";		
		$this->content->text.= get_string('mdlos_online_now','block_mdlopensim').": ".$this->now_online."<br />";		

		$this->content->footer = '<hr /><i>Moodle OpenSim</i>';

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

}

?>
