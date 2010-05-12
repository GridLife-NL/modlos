<?php

if (!defined('MDLOPNSM_BLK_PATH')) exit();


function  print_world_map()
{
    global $CFG;

	include(MDLOPNSM_BLK_PATH."/html/world_map.html");
}



function print_tabnav($currenttab, $course)
{
	global $CFG;

	if (empty($currenttab)) {
		$currenttab = 'world_map';
	}

	if (empty($course)) {
		$courseid = 0;
	}
	else {
		$courseid = $course->id;
	}

	$toprow = array();
	$toprow[] = new tabobject('world_map',   MDLOPNSM_BLK_URL.'/actions/world_map.php?course='.$courseid,get_string('mdlos_world_map','block_mdlopensim'));
	$toprow[] = new tabobject('regions_list',MDLOPNSM_BLK_URL.'/actions/regions_list.php?course='.$courseid,get_string('mdlos_regions_list','block_mdlopensim'));
	if (!isGuest()) {
		$toprow[] = new tabobject('avatars_list',MDLOPNSM_BLK_URL.'/actions/avatars_list.php?course='.$courseid,get_string('mdlos_avatars_list','block_mdlopensim'));
		$toprow[] = new tabobject('avatar_make', MDLOPNSM_BLK_URL.'/actions/avatar_make.php?course='. $courseid,get_string('mdlos_avatar_make','block_mdlopensim'));
		if (isadmin()) {
			$toprow[] = new tabobject('settings', $CFG->wwwroot.'/actions/admin/settings.php?section=blocksettingmdlopnsm',get_string('mdlos_general_setting_menu','block_mdlopensim'));
		}
	}
	$tabs = array($toprow);

	print_tabs($tabs, $currenttab, NULL, NULL);
}



?>

