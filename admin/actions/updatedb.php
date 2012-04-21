<?php

require_once(realpath(dirname(__FILE__).'/../../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../../include/modlos.func.php'));


$course_id = optional_param('course', '0', PARAM_INT);

$urlparams = array();
if ($course_id) $urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/admin/actions/updatedb.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'updatedb';

require_login($course->id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/admin/class/updatedb.class.php');
$updatedb = new UpdateDataBase($course_id);

print_tabnav_magage($action, $course);

$updatedb->execute();
$updatedb->print_page();

echo $OUTPUT->footer($course);
?>	
