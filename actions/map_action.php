<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = "map_action";


global $CFG, $USER;

$grid_name = $CFG->mdlopnsm_grid_name;
$world_map = get_string("mdlos_world_map", "block_mdlopensim");

//
$avatars_num = mdlopensim_get_avatars_num($USER->id);
$max_avatars = $CFG->mdlopnsm_max_own_avatars;
if (!hasPermit($course_id) and $max_avatars>=0 and $avatars_num>=$max_avatars) $isAvatarMax = true;
else $isAvatarMax = false;


$object_url = CMS_MODULE_URL.'/helper/world_map.php';

print_tabheader($action, $course, !$isAvatarMax);

include(CMS_MODULE_PATH."/html/object.html");

print_footer($course);

?>
