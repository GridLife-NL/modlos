<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));


$course_id = optional_param('course', '0', PARAM_INT);

if ($course_id) $urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/delete_avatar.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'delete_avatar';

require_login($course->id);
print_modlos_header($action, $course);

require_once(CMS_MODULE_PATH.'/class/delete_avatar.class.php');
$avatar = new DeleteAvatar($course_id);

print_tabnav($action, $course, !$avatar->isAvatarMax);

$avatar->execute();
$avatar->print_page();

echo $OUTPUT->footer($course);
?>	
