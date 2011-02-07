<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/env_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'avatars_list';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/class/avatars_list.class.php");
$avatars = new AvatarsList($course_id);

print_tabnav($action, $course, !$avatars->isAvatarMax);

$avatars->set_condition();
$avatars->execute();
$avatars->print_page();

print_footer($course);
	
?>	
