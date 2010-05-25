<?php

require_once(realpath(dirname(__FILE__)."/../../config.php"));
require_once(realpath(dirname(__FILE__)."/include/config.php"));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.mysql.php");


$courseid = optional_param('course', '0', PARAM_INT);
$course = get_record('course', 'id', $courseid);
$action = 'show_db';


print_tabheader($action, $course);

require_once(CMS_MODULE_PATH."/class/show_db.class.php");
$regions = new ShowDataBase($courseid);
$regions->print_page();

print_footer($course);
	
?>	
