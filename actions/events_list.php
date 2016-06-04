<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../include/modlos.func.php'));


$course_id = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/events_list.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'events_list';

require_login($course_id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/class/events_list.class.php');
$events = new EventsList($course_id);

print_tabnav($action, $course_id, !$events->isAvatarMax);

$events->execute();
$events->print_page();

echo $OUTPUT->footer($course);
