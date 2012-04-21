<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));


$course_id = optional_param('course', '0', PARAM_INT);

if ($course_id) $urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/events_list.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'events_list';

require_login($course->id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/class/events_list.class.php');
$events = new EventsList($course_id);

print_tabnav($action, $course, !$events->isAvatarMax);

$events->execute();
$events->print_page();

echo $OUTPUT->footer($course);
?>	
