<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'events_list';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/class/events_list.class.php");
$events = new EventsList($course_id);

print_tabnav($action, $course, !$events->isAvatarMax);

$events->set_condition();
$events->execute();
$events->print_page();

print_footer($course);
	
?>	
