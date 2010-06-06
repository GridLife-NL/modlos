<?php

require_once(realpath(dirname(__FILE__)."/../../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../../include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/modlos.func.php");


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'updatedb';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/admin/class/updatedb.class.php");
$update = new UpdateDataBase($course_id);

print_tabnav($action, $course);

$synchro->execute();
$synchro->print_page();

print_footer($course);
	
?>	
