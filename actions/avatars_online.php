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
$PAGE->set_url('/blocks/modlos/actions/avatars_online.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$tab_action = 'avatars_online';

require_login($course_id);
print_modlos_header($tab_action, $course);

require_once(CMS_MODULE_PATH.'/class/avatars_online.class.php');
$avatars = new AvatarsOnline($course_id, $instance_id);

print_tabnav($tab_action, $course_id, $instance_id, !$avatars->isAvatarMax);

$avatars->set_condition();
$avatars->execute();
$avatars->print_page();

echo $OUTPUT->footer($course);
