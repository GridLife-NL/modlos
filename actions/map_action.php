<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");


$courseid = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $courseid);
$action = "map_action";


global $CFG;

$grid_name = $CFG->mdlopnsm_grid_name;
$world_map = get_string("mdlos_world_map", "block_mdlopensim");
$object_url = MDLOPNSM_BLK_URL.'/helper/world_map.php';


print_tabheader($action, $course);

include(MDLOPNSM_BLK_PATH."/html/object.html");

print_footer($course);

?>
