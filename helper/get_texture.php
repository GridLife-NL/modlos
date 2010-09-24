<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/cms_interface.php'));

if (isguest()) {
	exit('<h4>guest user is not allowed to access this page!!</h4>');
}


$uuid = required_param('uuid', PARAM_TEXT);
if (!isGUID($uuid)) exit('<h4>bad asset uuid!! ('.htmlspecialchars($uuid).')</h4>');

$prog  = cms_get_config('modlos_image_processor_jp2');
$path  = cms_get_config('modlos_image_processor_path');
$cache = CMS_MODULE_PATH.'/helper/texture_cache';


$ret = opensim_display_texture_data($uuid, $prog, $path, $cache);
if (!$ret) exit();
 
?>
