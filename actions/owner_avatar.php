<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/cms_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'owner_avatar';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/class/owner_avatar.class.php");
$avatar = new OwnerAvatar($course_id);

print_tabnav($action, $course, !$avatar->isAvatarMax);

$avatar->execute();
$avatar->print_page();

print_footer($course);
	
?>	
