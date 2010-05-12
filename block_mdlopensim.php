<?php

require_once(realpath(dirname(__FILE__)."/../../config.php"));
require_once(realpath(dirname(__FILE__)."/include/config.php"));



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
		$this->version = 2010032810;
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

		$this->content->text = '<a href="'.MDLOPNSM_BLK_URL.'/actions/world_map.php?course='.$id.'">'.get_string('mdlos_world_map','block_mdlopensim').'</a><br />';
		$this->content->text.= '<a href="'.MDLOPNSM_BLK_URL.'/actions/regions_list.php?course='.$id.'">'.get_string('mdlos_regions_list','block_mdlopensim').'</a><br />';
		if (!isguest()) {
			$this->content->text.= '<a href="'.MDLOPNSM_BLK_URL.'/actions/avatars_list.php?course='.$id.'">'.get_string('mdlos_avatars_list','block_mdlopensim').'</a><br />';
			$this->content->text.= '<a href="'.MDLOPNSM_BLK_URL.'/actions/avatar_make.php?course='.$id.'">'.get_string('mdlos_avatar_make','block_mdlopensim').'</a><br />';
/*
			if (isadmin()) {
				$this->content->text.= '<hr />';
				$this->content->text.= '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=blocksettingmdlopensim">'.
										get_string('mdlos_general_setting_menu','block_mdlopensim').'</a><br />';
			}
*/
		}
		$this->content->text.= "<hr />";		

		$this->check_opensim_db();
		$this->content->text.= "<center><b>".$this->grid_name."</b></center>";		
		$this->content->text.= get_string('mdlos_db_status','block_mdlopensim').": ";		
		if ($this->grid_status) $this->content->text.= get_string('mdlos_online_ttl', 'block_mdlopensim')."<br />";		
		else					$this->content->text.= get_string('mdlos_offline_ttl','block_mdlopensim')."<br />";		
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



	function check_opensim_db()
	{
		require_once(MDLOPNSM_BLK_PATH."/include/opensim_mysql.php");

		$DbLink = new DB;
   
		$DbLink->query("SELECT COUNT(*) FROM agents".
					   " WHERE agentOnline = 1 AND logintime > (unix_timestamp(from_unixtime(unix_timestamp(now()) - 86400)))");
		if ($DbLink->Errno==0) {
			list($this->now_online) = $DbLink->next_record();

			$DbLink->query("SELECT COUNT(*) FROM agents".
						   " WHERE logintime > unix_timestamp(from_unixtime(unix_timestamp(now()) - 2419200))");
			list($this->lastmonth_online) = $DbLink->next_record();

			$DbLink->query("SELECT COUNT(*) FROM users");
			list($this->user_count) = $DbLink->next_record();

			$DbLink->query("SELECT COUNT(*) FROM regions");
			list($this->region_count) = $DbLink->next_record();

			$this->grid_status = true;
		}
		$DbLink->close();
	}

}

?>
