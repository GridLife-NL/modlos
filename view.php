<?php

require_once(realpath(dirname(__FILE__)."/../../config.php"));
require_once(realpath(dirname(__FILE__)."/include/config.php"));

require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");


$courseid = optional_param('course', '0', PARAM_INT);
$action   = optional_param('action', 'world_map', PARAM_TEXT);

$course  = get_record('course', 'id', $courseid);


// Print Navi Header
if (empty($course)) {
	// TOP Page
	print_header(get_string('mdlopensim','block_mdlopensim'), " ", get_string('mdlopensim','block_mdlopensim'), "", "", true, "&nbsp;", navmenu(NULL));
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

if ($action=="world_map") {
	//print_world_map();
include(MDLOPNSM_BLK_PATH."/actions/world_map_action.php");
}
else if ($action=="regions_list") {
	require("./class/regions_list.class.php");
	$x = new RegionsList();
	//$x->set_condition();
	//$x->execute();
	$x->print_regions();
}
else if ($action=="avatars_list") {
	print_avatars_list();
}

print_footer($course);
	
?>	
