<?php

require_once(realpath(dirname(__FILE__).'/../../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../../include/modlos.func.php'));


$course_id = optional_param('course', '0', PARAM_INT);

$urlparams = array();
if ($course_id) $urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/admin/actions/synchrodb.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'synchrodb';

require_login($course->id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/admin/class/synchrodb.class.php');
$synchro = new SynchroDataBase($course_id);

print_tabnav_magage($action, $course);

$synchro->execute();
$synchro->print_page();

echo $OUTPUT->footer($course);
?>	
