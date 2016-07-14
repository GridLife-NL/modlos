<?php

//
$SRCH_DB_HOST   = 'localhost';
$SRCH_DB_NAME   = '';
$SRCH_DB_USER   = '';
$SRCH_DB_PASS   = '';
$SRCH_DB_MYSQLI = true;

//
if ($SRCH_DB_HOST=='' or $SRCH_DB_NAME=='' or $SRCH_DB_USER=='' or $SRCH_DB_PASS=='')
{
	if (defined('CMS_DB_HOST')) 
	{
		$SRCH_DB_HOST   = CMS_DB_HOST;
		$SRCH_DB_NAME   = CMS_DB_NAME;
		$SRCH_DB_USER   = CMS_DB_USER;
		$SRCH_DB_PASS   = CMS_DB_PASS;
		$SRCH_DB_MYSQLI = CMS_DB_MYSQLI;
	}
	else if (defined('OPENSIM_DB_HOST'))
	{
		$SRCH_DB_HOST   = OPENSIM_DB_HOST;
		$SRCH_DB_NAME   = OPENSIM_DB_NAME;
		$SRCH_DB_USER   = OPENSIM_DB_USER;
		$SRCH_DB_PASS   = OPENSIM_DB_PASS;
		$SRCH_DB_MYSQLI = OPENSIM_DB_MYSQLI;
	}
} 


define('SEARCH_ALLPARCELS_TBL_BASE',    'search_allparcels');
define('SEARCH_EVENTS_TBL_BASE',        'search_events');
define('SEARCH_HOSTSREGISTER_TBL_BASE', 'search_hostsregister');
define('SEARCH_OBJECTS_TBL_BASE',       'search_objects');
define('SEARCH_PARCELS_TBL_BASE',       'search_parcels');
define('SEARCH_PARCELSALES_TBL_BASE',   'search_parcelsales');
define('SEARCH_POPULARPLACES_TBL_BASE', 'search_popularplaces');
define('SEARCH_REGIONS_TBL_BASE',       'search_regions');

