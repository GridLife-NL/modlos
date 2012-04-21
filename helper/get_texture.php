<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));


if (isguestuser()) {
    exit('<h4>guest user is not allowed to access this page!!</h4>');
}

$uuid      = required_param('uuid', PARAM_TEXT);
$course_id = optional_param('course', '0', PARAM_INT);
if (!isGUID($uuid)) exit('<h4>bad asset uuid!! ('.htmlspecialchars($uuid).')</h4>');

$xsize = optional_param('xsize', '0', PARAM_INT);
$ysize = optional_param('ysize', '0', PARAM_INT);

$prog  = env_get_config('image_processor_jp2');
//$path  = env_get_config('image_processor_path');
$cache = CMS_MODULE_PATH.'/helper/texture_cache';

$ret = opensim_display_texture_data($uuid, $prog, $xsize, $ysize, $cache, true);
if (!$ret) exit();
 
?>
