<?php

if (!defined('ENV_HELPER_PATH')) require_once(realpath(dirname(__FILE__).'/../../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/modlos.func.php'));


$user_id    = optional_param('userid',   '0', PARAM_INT);
$course_id  = optional_param('course',   '1', PARAM_INT);
if (!$course_id) $course_id = 1;

$urlparams = array();
$urlparams['course'] = $course_id;
$urlparams['userid'] = $user_id;
$PAGE->set_url('/blocks/modlos/admin/actions/hg_avatars.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));

$tab_action = 'hg_avatars';
require_login($course_id);
print_modlos_header($tab_action, $course);

require_once(CMS_MODULE_PATH.'/admin/class/hg_avatars.class.php');
$avatars = new HgAvatars($course_id);

print_tabnav($tab_action, $course_id, !$avatars->isAvatarMax);

$avatars->set_condition();
$avatars->execute();
$avatars->print_page();

echo $OUTPUT->footer($course);
