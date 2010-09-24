<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/cms_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $course_id);
$action = 'regions_list';

print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/class/regions_list.class.php");
$regions = new RegionsList($course_id);

print_tabnav($action, $course, !$regions->isAvatarMax);

$regions->set_condition();
$regions->execute();
$regions->print_page();

print_footer($course);

?>	
