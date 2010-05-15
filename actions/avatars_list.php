<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('MDLOPNSM_BLK_PATH')) exit();
require_once(MDLOPNSM_BLK_PATH."/include/mdlopensim.func.php");


$courseid = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $courseid);
$action = 'avatars_list';


print_tabheader($action, $course);

require_once(MDLOPNSM_BLK_PATH."/class/avatars_list.class.php");
$regions = new AvatarsList($courseid);
$regions->print_page();

print_footer($course);
	
?>	
