<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'delete_avatar';


require_once(CMS_MODULE_PATH."/class/delete_avatar.class.php");
$avatar = new DeleteAvatar($course_id);

print_tabheader($action, $course, !$avatar->isAvatarMax);

$avatar->execute();
$avatar->print_page();

print_footer($course);
	
?>	
