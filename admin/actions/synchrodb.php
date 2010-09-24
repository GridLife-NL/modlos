<?php

require_once(realpath(dirname(__FILE__)."/../../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../../include/cms_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'synchrodb';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/admin/class/synchrodb.class.php");
$synchro = new SynchroDataBase($course_id);

print_tabnav_magage($action, $course);

$synchro->execute();
$synchro->print_page();

print_footer($course);
	
?>	
