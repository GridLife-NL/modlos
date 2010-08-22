<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once(realpath(dirname(__FILE__).'/../include/config.php'));

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


if (isguest()) {
	exit('<h4>guest user is not allowed to access this page!!</h4>');
}


$uuid = required_param('uuid', PARAM_TEXT);
if (!isGUID($uuid)) exit("<h4>bad asset uuid!! ($uuid)</h4>");


global $CFG;
$prog = $CFG->modlos_image_processor_jp2;
$path = $CFG->modlos_image_processor_path;
$tempfile = '/tmp/'.$uuid;

if ($path=="") {
	if (file_exists('/usr/local/bin/'.$prog)) $path = '/usr/local/bin/';
	else if (file_exists('/usr/bin/'.$prog))  $path = '/usr/bin/';
}
if (!file_exists($path.$prog)) {
	exit("<h4>program ".$path.$prog." is not found!!</h4>");
}


if ($prog=="convert")     $prog = $path.'convert '.$tempfile.' jpeg:-';
else if ($prog=="jasper") $prog = $path.'jasper -f '.$tempfile.' -T jpg';

$imgdata = '';


// for MySQL Server
$asset = opensim_get_asset_data($uuid);
if ($asset) {
    if ($asset['type']==0) {
        $imgdata = $asset['data'];
    }
}
else {
	exit("<h4>asset uuid nt found!! ($uuid)</h4>");
}


/*
// for Asset Server
$asset_url = 'http://202.26.159.196:8003/assets/'.$uuid;
$fp = fopen($asset_url, "rb");
stream_set_timeout($fp, 5);
$content = stream_get_contents($fp);
fclose($fp);
if (!$content) exit("<h4>asset uuid nt found!! ($uuid)</h4>");

$xml = new SimpleXMLElement($content);
$imgdata = base64_decode($xml->Data);
*/


$fp = fopen($tempfile, 'wb');
fwrite($fp, $imgdata);
fclose($fp);

header("Content-type: image/jpeg"); 
passthru($prog);

unlink($tempfile);
 
?>
