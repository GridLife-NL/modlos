<?php

require_once(realpath(dirname(__FILE__).'/../../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../../include/modlos.func.php'));


$course_id = optional_param('course', SITEID, PARAM_INT);

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/avatar_templ_edit.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));

//
require_login($course_id);
$permit = hasModlosPermit($course_id);
if (!$permit) print_error('modlos_access_forbidden', 'block_modlos');

$tab_action = ' ';
print_modlos_header($tab_action, $course);

require_once(CMS_MODULE_PATH.'/admin/class/avatar_templ_edit.class.php');
$avatar  = new AvatarTemplEdit($course_id);

print_tabnav_manage($tab_action, $course_id);

$avatar->execute();
$avatar->print_page();

echo $OUTPUT->footer($course);
