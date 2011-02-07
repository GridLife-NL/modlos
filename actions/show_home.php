<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/env_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'show_home';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/class/show_home.class.php");
$showhome = new ShowHome($course_id);

print_tabnav($action, $course, !$showhome->isAvatarMax);

$showhome->execute();
$showhome->print_page();

print_footer($course);
	
?>	
