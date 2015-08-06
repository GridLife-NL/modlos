<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../include/modlos.func.php'));


$course_id = optional_param('course', '1', PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/my_avatars.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'my_avatars';

require_login($course_id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/class/avatars_list.class.php');
$avatars = new AvatarsList($course_id, false);

print_tabnav($action, $course, !$avatars->isAvatarMax);

$avatars->set_condition();
$avatars->execute();
$avatars->print_page();

echo $OUTPUT->footer($course);
?>	
