<?php
//
// CMS/LMS Interface for Moodle
//												by Fumi.Iseki
//
//

require_once(realpath(dirname(__FILE__).'/config.php'));

require_once(ENV_HELPER_PATH.'/../include/tools.func.php');
require_once(ENV_HELPER_PATH.'/../include/mysql.func.php');
require_once(ENV_HELPER_PATH.'/../include/env.mysql.php');
require_once(ENV_HELPER_PATH.'/../include/opensim.mysql.php');

require_once(CMS_MODULE_PATH.'/include/moodle.func.php');
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


//
//
//

$Categories[0]  = get_string('modlos_events_all_category',	'block_modlos');
$Categories[18] = get_string('modlos_events_discussion',	'block_modlos');
$Categories[19] = get_string('modlos_events_sports',		'block_modlos');
$Categories[20] = get_string('modlos_events_music',			'block_modlos');
$Categories[22] = get_string('modlos_events_commercial',	'block_modlos');
$Categories[23] = get_string('modlos_events_enteme',		'block_modlos');
$Categories[24] = get_string('modlos_events_games',			'block_modlos');
$Categories[25] = get_string('modlos_events_pageants',	  	'block_modlos');
$Categories[26] = get_string('modlos_events_edu',		   	'block_modlos');
$Categories[27] = get_string('modlos_events_arts',			'block_modlos');
$Categories[28] = get_string('modlos_events_charity',		'block_modlos');
$Categories[29] = get_string('modlos_events_misc',			'block_modlos');
if (!OPENSIM_PG_ONLY) $Categories[23] = get_string('modlos_events_nightlife', 'block_modlos').$Categories[23];


//
//
//
function  env_get_user_email($uid)
{                  
    return modlos_get_user_email($uid);
}


                   
function  env_get_config($name)
{                  
    global $CFG;
	
	$name = "modlos_".$name;
 
    return $CFG->$name;
}



//
if (!defined('ENV_READED_INTERFACE')) define('ENV_READED_INTERFACE', 'YES');

?>
