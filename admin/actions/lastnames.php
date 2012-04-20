<?php

require_once(realpath(dirname(__FILE__)."/../../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../../include/env_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'lastnames';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/admin/class/lastnames.class.php");
$lastnames = new LastNames($course_id);

print_tabnav_manage($action, $course);

$lastnames->execute();
$lastnames->print_page();

print_footer($course);
	
?>	
