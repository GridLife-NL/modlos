<?php

//
$PROF_DB_HOST   = 'localhost';
$PROF_DB_NAME   = '';
$PROF_DB_USER   = '';
$PROF_DB_PASS   = '';
$PROF_DB_MYSQLI = true;

//
if ($PROF_DB_HOST=='' or $PROF_DB_NAME=='' or $PROF_DB_USER=='' or $PROF_DB_PASS=='') 
{
	if (defined('CMS_DB_HOST')) 
	{
		$PROF_DB_HOST   = CMS_DB_HOST;
		$PROF_DB_NAME   = CMS_DB_NAME;
		$PROF_DB_USER   = CMS_DB_USER;
		$PROF_DB_PASS   = CMS_DB_PASS;
		$PROF_DB_MYSQLI = CMS_DB_MYSQLI;
	}
	else if (defined('OPENSIM_DB_HOST')) 
	{
    	$PROF_DB_HOST   = OPENSIM_DB_HOST;
    	$PROF_DB_NAME   = OPENSIM_DB_NAME;
    	$PROF_DB_USER   = OPENSIM_DB_USER;
    	$PROF_DB_PASS   = OPENSIM_DB_PASS;
    	$PROF_DB_MYSQLI = OPENSIM_DB_MYSQLI;
	}
}


// Table Base Name
define('PROFILE_CLASSIFIEDS_TBL_BASE',  'profile_classifieds');
define('PROFILE_USERNOTES_TBL_BASE',    'profile_usernotes');
define('PROFILE_USERPICKS_TBL_BASE',    'profile_userpicks');
define('PROFILE_USERPROFILE_TBL_BASE',  'profile_userprofile');
define('PROFILE_USERSETTINGS_TBL_BASE', 'profile_usersettings');


