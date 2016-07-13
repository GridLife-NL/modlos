<?php

if (!defined('ENV_HELPER_PATH')) require_once(realpath(dirname(__FILE__).'/../include/config.php'));
if (!defined('ENV_READ_DEFINE')) require_once(realpath(ENV_HELPER_PATH.'/../include/env_define.php'));
require_once(realpath(ENV_HELPER_PATH.'/../include/mysql.func.php'));



if (defined('CMS_DB_HOST')) 
{
	$DB_HOST = 	   CMS_DB_HOST;
	$DB_NAME = 	   CMS_DB_NAME;
	$DB_USER = 	   CMS_DB_USER;
	$DB_PASSWORD = CMS_DB_PASS;
	$DB_MYSQLI =   CMS_DB_MYSQLI;
}
else if (defined('OPENSIM_DB_HOST'))
{
	$DB_HOST = 	   OPENSIM_DB_HOST;
	$DB_NAME = 	   OPENSIM_DB_NAME;
	$DB_USER = 	   OPENSIM_DB_USER;
	$DB_PASSWORD = OPENSIM_DB_PASS;
	$DB_MYSQLI =   OPENSIM_DB_MYSQLI;
} 
else
{
	// if you donot have env_define.php, please set DB information by manual.
	$DB_HOST = 	   'localhost';
	$DB_NAME = 	   '';
	$DB_USER = 	   '';
	$DB_PASSWORD = '';
	$DB_MYSQLI =   false;
}



if (!defined('SEARCH_ALLPARCELS_TBL'))
{
	$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';

	define('SEARCH_ALLPARCELS_TBL',		'allparcels');
	define('SEARCH_CLASSIFIEDS_TBL',	'classifieds');
	define('SEARCH_EVENTS_TBL',			'events');
	define('SEARCH_HOSTSREGISTER_TBL',	'hostsregister');
	define('SEARCH_OBJECTS_TBL',		'objects');
	define('SEARCH_PARCELS_TBL',		'parcels');
	define('SEARCH_PARCELSALES_TBL',	'parcelsales');
	define('SEARCH_POPULARPLACES_TBL',	'popularplaces');
	define('SEARCH_REGIONS_TBL',		'search_regions');
}
