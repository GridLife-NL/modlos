<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'show_home';


require_once(CMS_MODULE_PATH."/class/show_home.class.php");
$showhome = new ShowHome($course_id);

print_tabheader($action, $course, !$showhome->isAvatarMax);

$showhome->execute();
$showhome->print_page();

print_footer($course);
	
?>	
