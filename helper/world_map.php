<?php

require_once(realpath(dirname(__FILE__)."/../../../config.php"));
require_once(realpath(dirname(__FILE__)."/../include/config.php"));


$world_map_url = MDLOPNSM_BLK_URL."/helper/world_map.php";
$allow_zoom = true;

$grid_name = $CFG->mdlopnsm_grid_name;
$mapstartX = $CFG->mdlopnsm_map_start_x;
$mapstartY = $CFG->mdlopnsm_map_start_y;

$centerX = optional_param('ctX', $mapstartX, PARAM_INT);
$centerY = optional_param('ctY', $mapstartY, PARAM_INT);
$tsize   = optional_param('size', $CFG->mdlopnsm_map_size, PARAM_INT);

$size = $CFG->mdlopnsm_map_size;
if ($allow_zoom) {
	if($tsize==16 or $tsize==32 or $tsize==64 or $tsize==128 or $tsize==256 or $tsize==512) {
		$size = $tsize;
	}
}

ob_start();
require(MDLOPNSM_BLK_PATH."/include/map_script.php");
$map_script = ob_get_contents();
ob_end_clean();
 
?>

<head>
	<title><?php print $grid_name?></title>
    <link rel="stylesheet" href="<?php print MDLOPNSM_BLK_URL?>/include/world_map.css" type="text/css" media="all">
	<script src="<?php print MDLOPNSM_BLK_URL?>/js/prototype.js" type=text/javascript></script>
	<script src="<?php print MDLOPNSM_BLK_URL?>/js/effects.js"   type=text/javascript></script>
	<script src="<?php print MDLOPNSM_BLK_URL?>/js/mapapi.js"    type=text/javascript></script>
	<script type=text/javascript><?php print $map_script?></script>
</head>

<body onload=loadmap()>

<div id=map-container style="z-index: 0;"></div>

<div id=map-nav>
	<div id=map-nav-up style="z-index: 1;">
		<a href="javascript: mapInstance.panUp();"><img alt=Up src="<?php print MDLOPNSM_BLK_URL?>/images/pan_up.gif"></a>
	</div>
	<div id=map-nav-down style="z-index: 1;">
		<a href="javascript: mapInstance.panDown();"><img alt=Down src="<?php print MDLOPNSM_BLK_URL?>/images/pan_down.gif"></a>
	</div>
	<div id=map-nav-left style="z-index: 1;">
		<a href="javascript: mapInstance.panLeft();"><img alt=Left src="<?php print MDLOPNSM_BLK_URL?>/images/pan_left.gif"></a>
	</div>
	<div id=map-nav-right style="z-index: 1;">
		<a href="javascript: mapInstance.panRight();"><img alt=Right src="<?php print MDLOPNSM_BLK_URL?>/images/pan_right.gif"></a>
	</div>
	<div id=map-nav-center style="z-index: 1;">
		<a href="javascript: mapInstance.panOrRecenterToWORLDCoord(new XYPoint(<?php print $mapstartX?>,<?php print $mapstartY?>), true);">
			<img alt=Center src="<?php print MDLOPNSM_BLK_URL?>/images/center.gif"></a>
	</div>

	<!-- START ZOOM PANEL-->
	<?php if ($allow_zoom) {?>
		<div id=map-zoom-plus>
			<?php if ($pluszoom==0) {?>
				<img alt="Zoom In" src="<?php print MDLOPNSM_BLK_URL?>/images/zoom_in_grey.gif">
			<?php } else {?>
				<a href="javascript: setZoom(<?php print $pluszoom?>);"><img alt="Zoom In" src="<?php print MDLOPNSM_BLK_URL?>/images/zoom_in.gif"></a>
			<?php }?>
		</div>

		<div id=map-zoom-minus>
			<?php if ($minuszoom==0) {?>
				<img alt="Zoom In" src="<?php print MDLOPNSM_BLK_URL?>/images/zoom_out_grey.gif">
			<?php } else {?>
				<a href="javascript: setZoom(<?php print $minuszoom?>);"><img alt="Zoom Out" src="<?php print MDLOPNSM_BLK_URL?>/images/zoom_out.gif"></a>
			<?php }?>
		</div>
	<?php }?>
	<!-- END ZOOM PANEL-->

</div>
</body>

