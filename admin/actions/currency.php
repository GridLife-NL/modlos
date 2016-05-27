<?php

require_once(realpath(dirname(__FILE__).'/../../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../../include/modlos.func.php'));


$course_id = optional_param('course', '1', PARAM_INT);
if (!$course_id) $course_id = 1; 
if (!USE_CURRENCY_SERVER) exit("<h4>Money Server is not used!!</h4>");

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/admin/actions/currency/.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));
$action = 'currency';

require_login($course_id);
print_modlos_header($action, $course);

$permit = hasModlosPermit($course_id);
if (!$permit) print_error('modlos_access_forbidden', 'block_modlos');

require_once(CMS_MODULE_PATH.'/admin/class/currency.class.php');
$currency = new CurrencyManage($course_id);

print_tabnav_manage($action, $course);

$currency->execute();
$currency->print_page();

echo $OUTPUT->footer($course);
