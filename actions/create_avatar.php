<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/env_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'create_avatar';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/class/create_avatar.class.php");
$avatar = new CreateAvatar($course_id);

print_tabnav($action, $course, !$avatar->isAavatarMax);

$avatar->execute();
$avatar->print_page();

print_footer($course);
	
?>	
