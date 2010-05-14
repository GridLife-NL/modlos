<?php
/****************************************************************
 *	mdlopensim.func.php by Fumi.Iseki for Mdlopensim
 *
 *
 * function print_tabnav($currenttab, $course)
 * function print_tabheader($currenttab, $courseid)
 *
 ****************************************************************/


if (!defined('MDLOPNSM_BLK_PATH')) exit();

require_once(MDLOPNSM_BLK_PATH."/include/config.php");
require_once(MDLOPNSM_BLK_PATH."/include/tools.func.php");
require_once(MDLOPNSM_BLK_PATH."/include/moodle.func.php");
require_once(MDLOPNSM_BLK_PATH."/include/opensim.func.php");




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
	$toprow[] = new tabobject('show_db', MDLOPNSM_BLK_URL.'/actions/show_db.php?course='.$courseid, 
																	get_string('mdlos_show_db','block_mdlopensim'));
	$toprow[] = new tabobject('map_action', MDLOPNSM_BLK_URL.'/actions/map_action.php?course='.$courseid, 
																	get_string('mdlos_world_map','block_mdlopensim'));
	$toprow[] = new tabobject('regions_list', MDLOPNSM_BLK_URL.'/actions/regions_list.php?course='.$courseid, 
																	get_string('mdlos_regions_list','block_mdlopensim'));
	if (!isGuest()) {
		$toprow[] = new tabobject('avatars_list', MDLOPNSM_BLK_URL.'/actions/avatars_list.php?course='.$courseid, 
																	get_string('mdlos_avatars_list','block_mdlopensim'));
		$toprow[] = new tabobject('avatar_create', MDLOPNSM_BLK_URL.'/actions/avatar_create.php?course='. $courseid, 
																	get_string('mdlos_avatar_create','block_mdlopensim'));
		if (isadmin()) {
			$toprow[] = new tabobject('settings', $CFG->wwwroot.'/admin/settings.php?section=blocksettingmdlopensim', 
																	get_string('mdlos_general_setting_menu','block_mdlopensim'));
		}
	}
	$tabs = array($toprow);

	print_tabs($tabs, $currenttab, NULL, NULL);
}



function print_tabheader($currenttab, $course)
{
	global $CFG;

	// Print Navi Header
	if (empty($course)) {
		// TOP Page
		print_header(get_string('mdlopensim','block_mdlopensim'), " ",
					 get_string('mdlopensim','block_mdlopensim'), "", "", true, "&nbsp;", navmenu(NULL));
	}
	else {
		if ($course->category) {
			print_header("$course->shortname: ".get_string('mdlopensim','block_mdlopensim'), $course->fullname,
					 '<a href="'.$CFG->wwwroot."/course/view.php?id={$course->id}\">$course->shortname</a> -> ".
					 get_string('mdlopensim','block_mdlopensim'), "", "", true, "&nbsp;", navmenu($course));
		}
		else {
			print_header("$course->shortname: ".get_string('mdlopensim','block_mdlopensim'), $course->fullname,
					 get_string('mdlopensim','block_mdlopensim'), "", "", true, "&nbsp;", navmenu($course));
		}
	}


	print_tabnav($currenttab, $course);
}


?>

