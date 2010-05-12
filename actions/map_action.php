<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");

$courseid = optional_param('course', '0', PARAM_INT);
$action = "map_action";

global $CFG;

$title = $CFG->mdlopnsm_grid_name." : World Map";
$object_url = MDLOPNSM_BLK_URL.'/helper/world_map.php';


print_tabheader($action, $courseid);

include(MDLOPNSM_BLK_PATH."/html/object.html");

print_footer($course);

?>
