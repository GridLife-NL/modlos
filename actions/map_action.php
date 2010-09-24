<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/cms_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = "world_map";

print_modlos_header($action, $course);


global $CFG, $USER;
$grid_name = $CFG->modlos_grid_name;
$world_map = get_string("modlos_world_map", "block_modlos");

//
$avatars_num = modlos_get_avatars_num($USER->id);
$max_avatars = $CFG->modlos_max_own_avatars;
if (!hasModlosPermit($course_id) and $max_avatars>=0 and $avatars_num>=$max_avatars) $isAvatarMax = true;
else $isAvatarMax = false;

print_tabnav($action, $course, !$isAvatarMax);

$object_url = CMS_MODULE_URL.'/helper/world_map.php';
include(CMS_MODULE_PATH."/html/object.html");

print_footer($course);

?>
