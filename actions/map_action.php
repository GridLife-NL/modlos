<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = "map_action";

global $CFG;
$grid_name = $CFG->mdlopnsm_grid_name;
$world_map = get_string("mdlos_world_map", "block_mdlopensim");
$object_url = CMS_MODULE_URL.'/helper/world_map.php';

print_tabheader($action, $course);

include(CMS_MODULE_PATH."/html/object.html");

print_footer($course);

?>
