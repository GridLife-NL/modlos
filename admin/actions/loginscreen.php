<?php

require_once(realpath(dirname(__FILE__)."/../../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../../include/env_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'loginscreen';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/admin/class/loginscreen.class.php");
$manage = new LoginScreen($course_id);

print_tabnav_manage($action, $course);

$manage->execute();
$manage->print_page();

print_footer($course);
	
?>	
