<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/env_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'delete_event';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/class/delete_event.class.php");
$event = new DeleteEvent($course_id);

print_tabnav($action, $course, !$event->isAvatarMax);

$event->execute();
$event->print_page();

print_footer($course);

?>	
