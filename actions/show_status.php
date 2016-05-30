<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../include/modlos.func.php'));


$course_id   = optional_param('course',   '1', PARAM_INT);
$instance_id = optional_param('instance', '0', PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course']   = $course_id;
$urlparams['instance'] = $instance_id;
$PAGE->set_url('/blocks/modlos/actions/show_status.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'show_status';

require_login($course_id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/class/show_status.class.php');
$showstatus = new ShowStatus($course_id, $instance_id);

print_tabnav($action, $course_id, $instance_id, !$showstatus->isAvatarMax);

$showstatus->execute();
$showstatus->print_page();

echo $OUTPUT->footer($course);
