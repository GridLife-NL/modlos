<?php

require_once(realpath(dirname(__FILE__)."/../../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../../include/cms_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'convertdb';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/admin/class/convertdb.class.php");
$convertdb = new ConvertDataBase($course_id);

print_tabnav_manage($action, $course);

$convertdb->execute();
$convertdb->print_page();

print_footer($course);
	
?>	
