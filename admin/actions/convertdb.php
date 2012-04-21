<?php

require_once(realpath(dirname(__FILE__)."/../../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../../include/env_interface.php"));


$course_id = optional_param('course', '0', PARAM_INT);

if ($course_id) $urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/admin/actions/convertdb.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'convertdb';

require_login($course->id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH."/admin/class/convertdb.class.php");
$convertdb = new ConvertDataBase($course_id);

print_tabnav_manage($action, $course);

$convertdb->execute();
$convertdb->print_page();

echo $OUTPUT->footer($course);
?>	
