<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'owner_avatar';

print_tabheader($action, $course);

require_once(CMS_MODULE_PATH."/class/owner_avatar.class.php");
$avatar = new OwnerAvatar($course_id);
$avatar->execute();
$avatar->print_page();

print_footer($course);
	
?>	
