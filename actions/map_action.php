<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../include/modlos.func.php'));


$course_id = optional_param('course', '0', PARAM_INT);

$urlparams = array();
if ($course_id) $urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/map_action.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'world_map';

require_login($course->id);
print_modlos_header($action, $course);

$grid_name = $CFG->modlos_grid_name;
$world_map = get_string('modlos_world_map', 'block_modlos');

//
$avatars_num = modlos_get_avatars_num($USER->id);
$max_avatars = $CFG->modlos_max_own_avatars;
if (!hasModlosPermit($course_id) and $max_avatars>=0 and $avatars_num>=$max_avatars) $isAvatarMax = true;
else $isAvatarMax = false;

print_tabnav($action, $course, !$isAvatarMax);

$object_url = CMS_MODULE_URL.'/helper/world_map.php';
//$object_url = 'http://www.nsl.tuis.ac.jp/xoops/modules/xoopensim/helper/world_map.php';
include(CMS_MODULE_PATH.'/html/object.html');

echo $OUTPUT->footer($course);
?>
