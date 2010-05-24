<?php
/****************************************************************
 *	mdlopensim.func.php by Fumi.Iseki for Mdlopensim
 *
 *
 * function  mdlopensim_activate_avatar($uuid)
 * function  mdlopensim_inactivate_avatar($uuid)
 * function  mdlopensim_delete_banneddb($uuid)
 *
 * function  mdlopensim_insert_usertable($user)
 * function  mdlopensim_update_usertable($user)
 * function  mdlopensim_delete_usertable($user)
 *
 * function  mdlopensim_delete_groupdb($uuid, $delallgrp=false)
 * function  mdlopensim_delete_groupdb_by_gpid($gpid)
 * function  mdlopensim_delete_groupdb_by_uuid($uuid)
 *
 * function  mdlopensim_set_profiles($profs, $ovwrite=true)
 * function  mdlopensim_delete_profiles($uuid)
 *
 * function  print_tabnav($currenttab, $course)
 * function  print_tabheader($currenttab, $course)
 *
 ****************************************************************/


if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH."/include/config.php");
require_once(CMS_MODULE_PATH."/include/tools.func.php");
require_once(CMS_MODULE_PATH."/include/moodle.func.php");
require_once(CMS_MODULE_PATH."/include/opensim.func.php");



//
// for Mdlopensim
//


// Active/Inactive Avatar
function  mdlopensim_activate_avatar($uuid)
{
	$ban = get_record(CMS_DB_PREFIX.'banned', 'uuid', $uuid);
	if (!$ban) return false;

	$ret = opensim_set_password($uuid, $ban->agentinfo);
	if (!$ret) return false;

	$ret = delete_records(CMS_DB_PREFIX.'banned', 'uuid', $uuid);
	if (!$ret) return false;
	return true;
}


function  mdlopensim_inactivate_avatar($uuid)
{
	$passwd = opensim_get_password($uuid);
	if ($passwd==null) return false;

	$passwdhash = $passwd['passwordHash'];
	if ($passwdhash==null) return false;

	$insobj->uuid 	   = $uuid;
	$insobj->agentinfo = $passwdhash;
	$insobj->time 	   = time();
	$ret = insert_record(CMS_DB_PREFIX.'banned', $insobj);
	if (!$ret) return false;

	$ret = opensim_set_password($uuid, "invalid password");
	if (!$ret) mdlopensim_delete_banneddb($uuid);

	return $ret;
}



function  mdlopensim_delete_banneddb($uuid)
{
	$ret = delete_records(CMS_DB_PREFIX.'banned', 'uuid', $uuid);
	if (!$ret) return false;
	return true;
}




//
// usertable DB
//
// called by synchro.class.php : UUID, username,  lastname, homeRegion, created    are setted in user[]
// called by create.class.php  : UUID, firstname, lastname, hmregion, state, uid   are setted in user[]
//
function  mdlopensim_insert_usertable($user)
{
	if (array_key_exists('firstname', $user))     $firstname = $user['firstname'];
	else if (array_key_exists('username', $user)) $firstname = $user['username'];
	else return false;

	$insobj->UUID 	   = $user['UUID'];
	$insobj->firstname = $firstname;
	$insobj->lastname  = $user['lastname'];

	if ($user['uid']!="") 	  $insobj->uid = $user['uid'];
	else                  	  $insobj->uid = 0;

	if ($user['state']!="")   $insobj->state = $user['state'];
	else					  $insobj->state = 1;

	if ($user['created']!="") $insobj->time = $user['created'];
	else 					  $insobj->time = time();

	$regionName = opensim_get_region_name_by_id($user['hmregion']);
	if ($regionName!="")              $insobj->hmregion = $regionName;
	else if ($user['hmregion']!="")   $insobj->hmregion = $user['hmregion'];
	else if ($user['homeRegion']!="") $insobj->hmregion = $user['homeRegion'];
	else                              $insobj->hmregion = "";

	$ret = insert_record(CMS_DB_PREFIX.'users', $insobj);
	if (!$ret) return false;
	return true;
}



//
// update (Moodle's)uid, hmregion, state, time of users (Moodle DB).
//
function  mdlopensim_update_usertable($user)
{
	if ($user['uid']!="") 	 $updobj->uid   = $user['uid'];
	if ($user['state']!="")  $updobj->state = $user['state'];

	$regionName = opensim_get_region_name_by_id($user['hmregion']);
	if ($regionName!="")            $updobj->hmregion = $regionName;
	else if ($user['hmregion']!="") $updobj->hmregion = $user['hmregion'];
	else                            $updobj->hmregion = "";

	if ($user['created']!="")  $updobj->time = $user['created'];
	else 					   $updobj->time = time();

	$ret = update_record(CMS_DB_PREFIX.'users', $insobj);
	if (!$ret) return false;
	return true;
}



function  mdlopensim_delete_usertable($user)
{
	if (!isGUID($user['UUID'])) return false;
	if ($user['state']==AVATAR_STATE_ACTIVE) return false;		// active

	$ret = delete_records(CMS_DB_PREFIX.'users', 'uuid', $user['UUID']);
	if (!$ret) return false;
	return true;
}




//
// Group DB
//

function  mdlopensim_delete_groupdb($uuid, $delallgrp=false)
{
	$ret = mdlopensim_delete_groupdb_by_uuid($uuid);
	if (!$ret) return false;

	if ($delallgrp) {
		$groupobjs = get_records(XMLGROUP_LIST_TBL, 'founderid', $uuid);
		if ($groupobjs==null) return false;

		foreach($groupobjs as $groupdata) {
			$ret = mdlopensim_delete_groupdb_by_gpid($groupdata->GroupID);
			if (!$ret) return false;
		}
	}

	return true;
}



function  mdlopensim_delete_groupdb_by_uuid($uuid)
{
	delete_records(XMLGROUP_ACTIVE_TBL, 	'agentid', $uuid);
	delete_records(XMLGROUP_INVITE_TBL, 	'agentid', $uuid);
	delete_records(XMLGROUP_MEMBERSHIP_TBL, 'agentid', $uuid);
	delete_records(XMLGROUP_ROLE_MEMBER_TBL,'agentid', $uuid);

	return true;
}



function  mdlopensim_delete_groupdb_by_gpid($gpid)
{
	delete_records(XMLGROUP_ACTIVE_TBL, 	'activegroupid', $gpid);
	delete_records(XMLGROUP_LIST_TBL, 		'groupid', $gpid);
	delete_records(XMLGROUP_INVITE_TBL, 	'groupid', $gpid);
	delete_records(XMLGROUP_MEMBERSHIP_TBL,	'groupid', $gpid);
	delete_records(XMLGROUP_NOTICE_TBL, 	'groupid', $gpid);
	delete_records(XMLGROUP_ROLE_TBL, 		'groupid', $gpid);
	delete_records(XMLGROUP_ROLE_MEMBER_TBL,'groupid', $gpid);

	return true;
}



///////////////////////////////////////////////////////////////////////////////////////
//
//

// called from synchro.class.php
function  mdlopensim_set_profiles($profs, $ovwrite=true)
{
	foreach($profs as $prof) {
		if ($prof['UUID']!="") {
			$insert = false;
			$prfobj = get_record(PROFILE_USERPROFILE_TBL, 'useruuid', $prof['UUID']);
			if (!$prfobj) $insert = true;

			$prfobj->useruuid = $prof['UUID'];

			if ($prof['Partnar']!="")		$prfobj->profilepartnar		  = $prof['Partnar'];
			if ($prof['Image']!="")			$prfobj->profileimage		  = $prof['Image'];
			if ($prof['AboutText']!="")		$prfobj->profileabouttext	  =	$prof['AboutText'];
			if ($prof['AllowPublish']!="")	$prfobj->profileallowpublish  = $prof['AllowPublish'];
			if ($prof['MaturePublish']!="")	$prfobj->profilematurepublish = $prof['MaturePublish'];
			if ($prof['URL']!="")			$prfobj->profileurl 		  = $prof['URL'];
			if ($prof['WantToMask']!="")	$prfobj->profilewanttomask 	  = $prof['WantToMask'];
			if ($prof['WantToText']!="")	$prfobj->profilewanttotext 	  = $prof['WantToText'];
			if ($prof['SkillsMask']!="")	$prfobj->profileskillsmask 	  = $prof['SkillsMask'];
			if ($prof['SkillsText']!="")	$prfobj->profileskillstext 	  = $prof['SkillsText'];
			if ($prof['LanguagesText']!="")	$prfobj->profilelanguagestext = $prof['LanguagesText'];
			if ($prof['FirstAboutText']!="")$prfobj->profilefirsttext 	  = $prof['FirstAboutText'];
			if ($prof['FirstImage'])		$prfobj->profilefirstimag 	  = $prof['FirstImage'];
	
			if ($insert) {
				$rslt = insert_record(PROFILE_USERPROFILE_TBL, $prfobj);
			}
			else if ($ovwrite) {
				$rslt = update_record(PROFILE_USERPROFILE_TBL, $prfobj);
			}
		}
	}


	foreach($profs as $prof) {
		if ($prof['UUID']!="") {
			$insert = false;
			$setobj = get_record(PROFILE_USERSETTINGS_TBL, 'useruuid', $prof['UUID']);
			if (!$setobj) $insert = true;

			$setobj->useruuid = $prof['UUID'];

			if ($prof['ImviaEmail']!="")$setobj->imviaemail = $prof['ImviaEmail'];
			if ($prof['Visible']!="")	$setobj->visible	= $prof['Visible'];
			if ($prof['Email']!="")		$setobj->email    	= $prof['Email'];

			if ($insert) {
				$rslt = insert_record(PROFILE_USERSETTINGS_TBL, $setobj);
			}
			else if ($ovwrite) {
				$rslt = update_record(PROFILE_USERSETTINGS_TBL, $setobj);
			}
		}
	}

	return true;
}




function  mdlopensim_delete_profiles($uuid)
{
	delete_records(PROFILE_USERPROFILE_TBL,	'useruuid',    $uuid);
	delete_records(PROFILE_USERSETTINGS_TBL,'useruuid',    $uuid);
	delete_records(PROFILE_USERNOTES_TBL, 	'useruuid',    $uuid);
	delete_records(PROFILE_USERPICKS_TBL, 	'creatoruuid', $uuid);
	delete_records(PROFILE_CLASSIFIEDS_TBL, 'creatoruuid', $uuid);

    return;
}



function print_tabnav($currenttab, $course)
{
	global $CFG;

	if (empty($currenttab)) {
		$currenttab = 'world_map';
	}

	if (empty($course)) {
		$courseid = 0;
	}
	else {
		$courseid = $course->id;
	}
	$hasPermit = hasPermit($courseid);


	///////
	$toprow = array();
	$toprow[] = new tabobject('show_db', CMS_MODULE_URL.'/actions/show_db.php?course='.$courseid, 
																	'<b>'.get_string('mdlos_show_db','block_mdlopensim').'</b>');
	$toprow[] = new tabobject('map_action', CMS_MODULE_URL.'/actions/map_action.php?course='.$courseid, 
																	'<b>'.get_string('mdlos_world_map','block_mdlopensim').'</b>');
	$toprow[] = new tabobject('regions_list', CMS_MODULE_URL.'/actions/regions_list.php?course='.$courseid, 
																	'<b>'.get_string('mdlos_regions_list','block_mdlopensim').'</b>');
	if (!isGuest()) {
		$toprow[] = new tabobject('avatars_list', CMS_MODULE_URL.'/actions/avatars_list.php?course='.$courseid, 
																	'<b>'.get_string('mdlos_avatars_list','block_mdlopensim').'</b>');
		$toprow[] = new tabobject('avatar_create', CMS_MODULE_URL.'/actions/avatar_create.php?course='. $courseid, 
																	'<b>'.get_string('mdlos_avatar_create','block_mdlopensim').'</b>');
	}

	if ($hasPermit) {
		if ($CFG->mdlopnsm_activate_lastname) {
			$toprow[] = new tabobject('lastnames', CMS_MODULE_URL.'/admin/settings.php?section=blocksettingmdlopensim', 
																	'<b>'.get_string('mdlos_lastnames_tab','block_mdlopensim').'</b>');
		}
		$toprow[] = new tabobject('synchrodb', CMS_MODULE_URL.'/admin/settings.php?section=blocksettingmdlopensim', 
																	'<b>'.get_string('mdlos_synchro_tab','block_mdlopensim').'</b>');
		if (isadmin()) {
			$toprow[] = new tabobject('settings', $CFG->wwwroot.'/admin/settings.php?section=blocksettingmdlopensim', 
																	'<b>'.get_string('mdlos_general_setting_tab','block_mdlopensim').'</b>');
		}
	}

	if ($courseid!=0) {
		$toprow[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$courseid, '<b>'.get_string('mdlos_return_tab', 'block_mdlopensim').'</b>');
	}
	else {
		$toprow[] = new tabobject('', $CFG->wwwroot, '<b>'.get_string('mdlos_return_tab', 'block_mdlopensim').'</b>');
	}

	$tabs = array($toprow);

	print_tabs($tabs, $currenttab, NULL, NULL);
}



function print_tabheader($currenttab, $course)
{
	global $CFG;

	// Print Navi Header
	if (empty($course)) {
		// TOP Page
		print_header(get_string('mdlopensim','block_mdlopensim'), " ",
					 get_string('mdlopensim','block_mdlopensim'), "", "", true, "&nbsp;", navmenu(NULL));
	}
	else {
		if ($course->category) {
			print_header("$course->shortname: ".get_string('mdlopensim','block_mdlopensim'), $course->fullname,
					 '<a href="'.$CFG->wwwroot."/course/view.php?id={$course->id}\">$course->shortname</a> -> ".
					 get_string('mdlopensim','block_mdlopensim'), "", "", true, "&nbsp;", navmenu($course));
		}
		else {
			print_header("$course->shortname: ".get_string('mdlopensim','block_mdlopensim'), $course->fullname,
					 get_string('mdlopensim','block_mdlopensim'), "", "", true, "&nbsp;", navmenu($course));
		}
	}


	print_tabnav($currenttab, $course);
}


?>
