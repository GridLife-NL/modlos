<?php

require_once(realpath(dirname(__FILE__).'/../../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../../include/modlos.func.php'));


$course_id = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1; 

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/admin/actions/management.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'management';

require_login($course_id);
print_modlos_header($action, $course);
$permit = hasModlosPermit($course_id);
if (!$permit) print_error('modlos_access_forbidden', 'block_modlos');

require_once(CMS_MODULE_PATH.'/admin/class/management.class.php');
$manage = new ManagementBase($course_id);

print_tabnav_manage($action, $course_id);

$manage->execute();
$manage->print_page();

echo $OUTPUT->footer($course);
