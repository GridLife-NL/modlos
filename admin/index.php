<?php

require_once(realpath(dirname(__FILE__)."/../../config.php"));
require_once(realpath(dirname(__FILE__)."/include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");


$courseid = optional_param('course', '0', PARAM_INT);
$action   = optional_param('action', 'world_map', PARAM_TEXT);

$course   = get_record('course', 'id', $courseid);


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


print_tabnav($action, $course);

//print(MDLOPNSM_BLK_PATH."/actions/".$action."_action.php?curse=".$courseid);
include(MDLOPNSM_BLK_PATH."/actions/".$action."_action.php?curse=".$courseid);

print_footer($course);
	
?>	
