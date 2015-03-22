<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));

require_once(ENV_HELPER_PATH.'/../include/modlos.func.php');

$world_map_url = CMS_MODULE_URL.'/helper/world_map.php';
$allow_zoom = true;


global $CFG;

$grid_name = $CFG->modlos_grid_name;
$mapstartX = $CFG->modlos_map_start_x;
$mapstartY = $CFG->modlos_map_start_y;

$centerX = optional_param('ctX',  $mapstartX, PARAM_INT);
$centerY = optional_param('ctY',  $mapstartY, PARAM_INT);
$tsize   = optional_param('size', $CFG->modlos_map_size, PARAM_INT);

$size = $CFG->modlos_map_size;
if ($allow_zoom) {
	if($tsize==4 or $tsize==8 or $tsize==16 or $tsize==32 or $tsize==64 or $tsize==128 or $tsize==256 or $tsize==512) {
		$size = $tsize;
	}
}

ob_start();

$course_id = optional_param('course', '1', PARAM_INT);
require(CMS_MODULE_PATH.'/include/map_script.php');
$map_script = ob_get_contents();
ob_end_clean();
 
include(CMS_MODULE_PATH.'/html/world_map.html');

