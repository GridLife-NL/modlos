<?php
/****************************************************************
 *	modlos.func.php by Fumi.Iseki for Modlos
 *
 *
 * function  hasModlosPermit($course_id);
 *
 * function  modlos_get_avatar_info($uuid, $use_sloodle=false)
 * function  modlos_set_avatar_info($avatar, $use_sloodle=false)
 * function  modlos_delete_avatar_info($avatar, $use_sloodle=false)
 * function  modlos_get_avatars_num($uuid, $use_sloodle=false)
 *
 * function  modlos_get_users()
 * function  modlos_insert_usertable($user)
 * function  modlos_update_usertable($user)
 * function  modlos_delete_usertable($user)
 *
 * function  modlos_get_lastnames($sort='')
 *
 * function  modlos_delete_groupdb($uuid, $delallgrp=false)
 * function  modlos_delete_groupdb_by_gpid($gpid)
 * function  modlos_delete_groupdb_by_uuid($uuid)
 *
 * function  modlos_set_profiles($profs, $ovwrite=true)
 * function  modlos_delete_profiles($uuid)
 *
 * function  modlos_activate_avatar($uuid)
 * function  modlos_inactivate_avatar($uuid)
 * function  modlos_delete_banneddb($uuid)
 *
 * function  modlos_sync_sloodle_users()
 *
 * function  print_tabnav($currenttab, $course, $create_tab=true)
 * function  print_modlos_header($currenttab, $course)
 *
 ****************************************************************/


if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/config.php');
require_once(CMS_MODULE_PATH.'/include/tools.func.php');
require_once(CMS_MODULE_PATH.'/include/moodle.func.php');
require_once(CMS_MODULE_PATH.'/include/opensim.mysql.php');




//
// Tools
//

function  hasModlosPermit($course_id=0)
{
	global $CFG;

	if ($CFG->modlos_teacher_admin) $ret = hasPermit($course_id);
	else $ret = hasPermit();

	return $ret;
}





//
// for Modlos and Sloodle
//

function  modlos_get_avatar_info($uuid, $use_sloodle=false)
{
	if (!isGUID($uuid)) return null;

	$avatar = get_record('modlos_users', 'uuid', $uuid);		

	$sloodle = null;
	if ($use_sloodle) {
		$sloodle = get_record(MDL_SLOODLE_USERS_TBL, 'uuid', $uuid);
		if ($sloodle!=null) {
			$names = null;
			if ($sloodle->avname!='') $names = explode(' ', $sloodle->avname);

			if ($sloodle->userid>0) $avatar->user_id = $sloodle->userid;
			if (is_array($names)) {
				$avatar->firstname = $names[0];
				$avatar->lastname  = $names[1];
			}

			/*	if ($avatar->user_id=='' and $sloodle->userid>0) $avatar->user_id = $sloodle->userid;
				if (is_array($names)) {
					if ($avatar->firstname=='') $avatar->firstname = $names[0];
					if ($avatar->lastname=='')  $avatar->firstname = $names[1];
				}*/
		}
	}
	
	if ($avatar==null and $sloodle==null) return null;
	if ($avatar->firstname=='' or $avatar->lastname=='') return null;


	//
	$avatar_info['UUID'] 	  = $uuid;
	$avatar_info['firstname'] = $avatar->firstname;
	$avatar_info['lastname']  = $avatar->lastname;

	if ($avatar->id>0) 			$avatar_info['id'] 		 = $avatar->id;
	else 						$avatar_info['id'] 		 = '';
	if ($avatar->user_id!='')  	$avatar_info['uid'] 	 = $avatar->user_id;
	else					   	$avatar_info['uid'] 	 = '0';
	if ($avatar->hmregion!='')	$avatar_info['hmregion'] = $avatar->hmregion;
	else					   	$avatar_info['hmregion'] = opensim_get_home_region($uuid);
	if ($avatar->state!='')		$avatar_info['state'] 	 = $avatar->state;
	else					   	$avatar_info['state'] 	 = AVATAR_STATE_NOSTATE;
	if ($avatar->time!='')	 	$avatar_info['time'] 	 = $avatar->time;
	else						$avatar_info['time']	 = time();

	return $avatar_info;
}



function  modlos_set_avatar_info($avatar, $use_sloodle=false)
{
	if (!isGUID($avatar['UUID'])) return false;

	// Modlos
	$obj = get_record('modlos_users', 'uuid', $avatar['UUID']);		
	if ($obj==null) {
		$ret = modlos_insert_usertable($avatar);
	}
	else {
		$ret = modlos_update_usertable($avatar, $obj);
	}

	// Sloodle
	if ($use_sloodle and $ret) {
		$updobj = get_record(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);
		if ($updobj==null) {
			if ($avatar['state']&AVATAR_STATE_SLOODLE) {
				$insobj->userid = $avatar['uid'];
				$insobj->uuid 	= $avatar['UUID'];
				$insobj->avname = $avatar['firstname'].' '.$avatar['lastname'];
				if ($insobj->avname==' ') $insobj->avname = '';
				$insobj->lastactive = time();
				$ret = insert_record(MDL_SLOODLE_USERS_TBL, $insobj);
			}
		}
		else {
			if ($avatar['state']&AVATAR_STATE_SLOODLE and $avatar['uid']!=0) {
				$updobj->userid = $avatar['uid'];
				$updobj->lastactive = time();
				$ret = update_record(MDL_SLOODLE_USERS_TBL, $updobj);
			}
			else {
				$ret = delete_records(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);
			}
		}
	}

	return $ret;
}



function  modlos_delete_avatar_info($avatar, $use_sloodle=false)
{
	if (!isGUID($avatar['UUID'])) return false;

	$ret = modlos_delete_usertable($avatar);

	// Sloodle
	if ($use_sloodle and $ret) $ret = delete_records(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);

	if (!$ret) return false;
	return true;
}



function  modlos_get_avatars_num($id, $use_sloodle=false)
{
	if (!isNumeric($id)) return null;

	$avatars = get_records('modlos_users', 'user_id', $id);
	if (is_array($avatars)) $num = count($avatars);
	else $num = 0;

	if ($use_sloodle) {
		$sloodles = get_records(MDL_SLOODLE_USERS_TBL, 'userid', $id);
		foreach ($sloodle as $sloodle) {
			$match = false;
			foreach ($avatas as $avatar) {
				if ($sloodle->uuid==$avatar->uuid) {
					$match = true;
					break;
				}
			}
			if (!$match) $num++;
		}
	}

	return $num;
}




//
// usertable DB
//
//

function  modlos_get_users()
{
	// Modlos DB を読んで配列に変換
	$db_users = get_records('modlos_users');
	$modlos_users = array();
	foreach ($db_users as $user) {
		$modlos_uuid = $user->uuid;
		$modlos_users[$modlos_uuid]['id']		= $user->id;
		$modlos_users[$modlos_uuid]['UUID']	 	= $user->uuid;
		$modlos_users[$modlos_uuid]['uid']		= $user->user_id;
		$modlos_users[$modlos_uuid]['firstname']= $user->firstname;
		$modlos_users[$modlos_uuid]['lastname'] = $user->lastname;
		$modlos_users[$modlos_uuid]['hmregion'] = $user->hmregion;
		$modlos_users[$modlos_uuid]['state']	= $user->state;
		$modlos_users[$modlos_uuid]['time']		= $user->time;
	}
	
	return $modlos_users;
}




//
//	UUID, firstname, lastname, uid, state, time, hmregion are setted in $user[]
//		hmregion is UUID or name of region

function  modlos_insert_usertable($user)
{
	if (!isGUID($user['UUID'])) return false;

	$insobj->uuid 	   = $user['UUID'];
	$insobj->firstname = $user['firstname'];
	$insobj->lastname  = $user['lastname'];

	if ($user['uid']!='') 	$insobj->user_id = $user['uid'];
	else				  	$insobj->user_id = '0';
	if ($user['state']!='')	$insobj->state 	 = $user['state'];
	else				 	$insobj->state 	 = AVATAR_STATE_SYNCDB;
	if ($user['time']!='') 	$insobj->time 	 = $user['time'];
	else 					$insobj->time 	 = time();

	if (isGUID($user['hmregion'])) {
		$regionName = opensim_get_region_name($user['hmregion']);
		if ($regionName!='')$insobj->hmregion = $regionName;
		else 				$insobj->hmregion = $user['hmregion'];
	}
	else {
		if ($user['hmregion']==null) {
			$insobj->hmregion = '';
		}
		else {
			$insobj->hmregion = $user['hmregion'];
		}
	}
	
	$ret = insert_record('modlos_users', $insobj);

	return $ret;
}



//
// update (Moodle's)uid, hmregion, state, time of users (Moodle DB).
//
function  modlos_update_usertable($user, $updobj=null)
{
	if (!isGUID($user['UUID'])) return false;

	if ($updobj==null) {
		$updobj = get_record('modlos_users', 'uuid', $user['UUID']);		
		if ($updobj==null) return false;
	}

	// Update
	if ($user['uid']!='') 	$updobj->user_id = $user['uid'];
	if ($user['state']!='')	$updobj->state   = $user['state'];
	if ($user['time']!='')	$updobj->time 	 = $user['time'];
	else 					$updobj->time 	 = time();

	if (isGUID($user['hmregion'])) {
		$regionName = opensim_get_region_name($user['hmregion']);
		if ($regionName!='')$updobj->hmregion = $regionName;
		else 				$updobj->hmregion = $user['hmregion'];
	}
	else if ($user['hmregion']!='') {
		$updobj->hmregion = $user['hmregion'];
	}

	$ret = update_record('modlos_users', $updobj);

	return $ret;
}
	



function  modlos_delete_usertable($user)
{
	if ($user['id']=='' and !isGUID($user['UUID'])) return false;
	if (!($user['state']&AVATAR_STATE_INACTIVE)) return false;		// active

	if ($user['id']!='') {
		$ret = delete_records('modlos_users',   'id', $user['id']);
	}
	else {
		$ret = delete_records('modlos_users', 'uuid', $user['UUID']);
	}

	if (!$ret) return false;
	return true;
}




//
// Last Names
//

function  modlos_get_lastnames($sort='')
{
	$lastnames = array();

	$lastns = get_records('modlos_lastnames', 'state', AVATAR_LASTN_ACTIVE, $sort, 'lastname');
	foreach ($lastns as $lastn) {
		$lastnames[] = $lastn->lastname;
	}

	return $lastnames;
}





//
// Group DB
//

function  modlos_delete_groupdb($uuid, $delallgrp=false)
{
	$ret = modlos_delete_groupdb_by_uuid($uuid);
	if (!$ret) return false;

	if ($delallgrp) {
		$groupobjs = get_records(MDL_XMLGROUP_LIST_TBL, 'founderid', $uuid);
		if ($groupobjs==null) return false;

		foreach($groupobjs as $groupdata) {
			$ret = modlos_delete_groupdb_by_gpid($groupdata->GroupID);
			if (!$ret) return false;
		}
	}

	return true;
}



function  modlos_delete_groupdb_by_uuid($uuid)
{
	delete_records(MDL_XMLGROUP_ACTIVE_TBL, 	'agentid', $uuid);
	delete_records(MDL_XMLGROUP_INVITE_TBL, 	'agentid', $uuid);
	delete_records(MDL_XMLGROUP_MEMBERSHIP_TBL, 'agentid', $uuid);
	delete_records(MDL_XMLGROUP_ROLE_MEMBER_TBL,'agentid', $uuid);

	return true;
}



function  modlos_delete_groupdb_by_gpid($gpid)
{
	delete_records(MDL_XMLGROUP_ACTIVE_TBL, 	'activegroupid', $gpid);
	delete_records(MDL_XMLGROUP_LIST_TBL, 		'groupid', $gpid);
	delete_records(MDL_XMLGROUP_INVITE_TBL, 	'groupid', $gpid);
	delete_records(MDL_XMLGROUP_MEMBERSHIP_TBL,	'groupid', $gpid);
	delete_records(MDL_XMLGROUP_NOTICE_TBL, 	'groupid', $gpid);
	delete_records(MDL_XMLGROUP_ROLE_TBL, 		'groupid', $gpid);
	delete_records(MDL_XMLGROUP_ROLE_MEMBER_TBL,'groupid', $gpid);

	return true;
}



///////////////////////////////////////////////////////////////////////////////////////
//
//

// called from updatedb.class.php
function  modlos_set_profiles($profs, $ovwrite=true)
{
	foreach($profs as $prof) {
		if ($prof['UUID']!='') {
			$insert = false;
			$prfobj = get_record(MDL_PROFILE_USERPROFILE_TBL, 'useruuid', $prof['UUID']);
			if (!$prfobj) $insert = true;

			$prfobj->useruuid = $prof['UUID'];

			if ($prof['Partnar']!='')		$prfobj->profilepartnar		  = $prof['Partnar'];
			if ($prof['Image']!='')			$prfobj->profileimage		  = $prof['Image'];
			if ($prof['AboutText']!='')		$prfobj->profileabouttext	  =	$prof['AboutText'];
			if ($prof['AllowPublish']!='')	$prfobj->profileallowpublish  = $prof['AllowPublish'];
			if ($prof['MaturePublish']!='')	$prfobj->profilematurepublish = $prof['MaturePublish'];
			if ($prof['URL']!='')			$prfobj->profileurl 		  = $prof['URL'];
			if ($prof['WantToMask']!='')	$prfobj->profilewanttomask 	  = $prof['WantToMask'];
			if ($prof['WantToText']!='')	$prfobj->profilewanttotext 	  = $prof['WantToText'];
			if ($prof['SkillsMask']!='')	$prfobj->profileskillsmask 	  = $prof['SkillsMask'];
			if ($prof['SkillsText']!='')	$prfobj->profileskillstext 	  = $prof['SkillsText'];
			if ($prof['LanguagesText']!='')	$prfobj->profilelanguagestext = $prof['LanguagesText'];
			if ($prof['FirstAboutText']!='')$prfobj->profilefirsttext 	  = $prof['FirstAboutText'];
			if ($prof['FirstImage'])		$prfobj->profilefirstimag 	  = $prof['FirstImage'];
	
			if ($insert) {
				$rslt = insert_record(MDL_PROFILE_USERPROFILE_TBL, $prfobj);
			}
			else if ($ovwrite) {
				$rslt = update_record(MDL_PROFILE_USERPROFILE_TBL, $prfobj);
			}
		}
	}


	foreach($profs as $prof) {
		if ($prof['UUID']!='') {
			$insert = false;
			$setobj = get_record(MDL_PROFILE_USERSETTINGS_TBL, 'useruuid', $prof['UUID']);
			if (!$setobj) $insert = true;

			$setobj->useruuid = $prof['UUID'];

			if ($prof['ImviaEmail']!='')$setobj->imviaemail = $prof['ImviaEmail'];
			if ($prof['Visible']!='')	$setobj->visible	= $prof['Visible'];
			if ($prof['Email']!='')		$setobj->email		= $prof['Email'];

			if ($insert) {
				$rslt = insert_record(MDL_PROFILE_USERSETTINGS_TBL, $setobj);
			}
			else if ($ovwrite) {
				$rslt = update_record(MDL_PROFILE_USERSETTINGS_TBL, $setobj);
			}
		}
	}

	return true;
}




function  modlos_delete_profiles($uuid)
{
	delete_records(MDL_PROFILE_USERPROFILE_TBL,	'useruuid',	$uuid);
	delete_records(MDL_PROFILE_USERSETTINGS_TBL,'useruuid',	$uuid);
	delete_records(MDL_PROFILE_USERNOTES_TBL, 	'useruuid',	$uuid);
	delete_records(MDL_PROFILE_USERPICKS_TBL, 	'creatoruuid', $uuid);
	delete_records(MDL_PROFILE_CLASSIFIEDS_TBL, 'creatoruuid', $uuid);

	return;
}



//
// Bann List
//

// Active/Inactive Avatar
function  modlos_activate_avatar($uuid)
{
	$ban = get_record('modlos_banned', 'uuid', $uuid);
	if (!$ban) return false;

	$ret = opensim_set_password($uuid, $ban->agentinfo);
	if (!$ret) return false;

	$ret = delete_records('modlos_banned', 'uuid', $uuid);
	if (!$ret) return false;
	return true;
}



function  modlos_inactivate_avatar($uuid)
{
	$passwd = opensim_get_password($uuid);
	if ($passwd==null) return false;

	$passwdhash = $passwd['passwordHash'];
	if ($passwdhash==null) return false;

	$insobj->uuid 	   = $uuid;
	$insobj->agentinfo = $passwdhash;
	$insobj->time 	   = time();
	$ret = insert_record('modlos_banned', $insobj);
	if (!$ret) return false;

	$ret = opensim_set_password($uuid, 'invalid_password');
	if (!$ret) modlos_delete_banneddb($uuid);

	return $ret;
}



function  modlos_delete_banneddb($uuid)
{
	$ret = delete_records('modlos_banned', 'uuid', $uuid);
	if (!$ret) return false;
	return true;
}





//
// for Sloodle
//

function  modlos_sync_sloodle_users()
{
	$sloodles = get_records(MDL_SLOODLE_USERS_TBL);
	$modloses = get_records('modlos_users');

	if (is_array($sloodles) and is_array($modloses)) {
		foreach ($modloses as $modlos) {
			$updated = false;
			foreach($sloodles as $sloodle) {
				if ($modlos->uuid==$sloodle->uuid) {
					$modlos->user_id = $sloodle->userid;
					$modlos->state |= AVATAR_STATE_SLOODLE;
					$updated = true;
					break;
				}
			}
			if ($updated) {
				update_record('modlos_users', $modlos);
			}
		}
	}
}




//
// Tab Header
//

function  print_tabnav($currenttab, $course, $create_tab=true)
{
	global $CFG;

	if (empty($currenttab)) $currenttab = 'world_map';
	if (empty($course)) $course_id = 0;
	else 				$course_id = $course->id;

	$hasPermit = hasModlosPermit($course_id);

	$course_param = '';
	if ($course_id>0) $course_param = '?course='.$course_id;

	///////
	$toprow = array();
	$toprow[] = new tabobject('show_home', CMS_MODULE_URL.'/actions/show_home.php'.$course_param, 
																	'<b>'.get_string('modlos_show_home','block_modlos').'</b>');
	$toprow[] = new tabobject('map_action', CMS_MODULE_URL.'/actions/map_action.php'.$course_param, 
																	'<b>'.get_string('modlos_world_map','block_modlos').'</b>');
	$toprow[] = new tabobject('regions_list', CMS_MODULE_URL.'/actions/regions_list.php'.$course_param, 
																	'<b>'.get_string('modlos_regions_list','block_modlos').'</b>');
	if (!isGuest()) {
		$toprow[] = new tabobject('avatars_list', CMS_MODULE_URL.'/actions/avatars_list.php'.$course_param, 
																	'<b>'.get_string('modlos_avatars_list','block_modlos').'</b>');
		if ($create_tab) {
			$toprow[] = new tabobject('create_avatar', CMS_MODULE_URL.'/actions/create_avatar.php'. $course_param, 
																	'<b>'.get_string('modlos_avatar_create','block_modlos').'</b>');
		}
	}

	if ($hasPermit) {
		if ($CFG->modlos_activate_lastname) {
			$toprow[] = new tabobject('lastnames', CMS_MODULE_URL.'/admin/actions/lastnames.php'.$course_param, 
																	'<b>'.get_string('modlos_lastnames_tab','block_modlos').'</b>');
		}
		$toprow[] = new tabobject('updatedb', CMS_MODULE_URL.'/admin/actions/updatedb.php'.$course_param, 
																	'<b>'.get_string('modlos_updatedb_tab','block_modlos').'</b>');
		if (isadmin()) {
			$course_amp = '';
			if ($course_id>0) $course_amp = '&amp;course='.$course_id;
			$toprow[] = new tabobject('settings', $CFG->wwwroot.'/admin/settings.php?section=blocksettingmodlos'.$course_amp, 
																	'<b>'.get_string('modlos_general_setting_tab','block_modlos').'</b>');
		}
	}

	if ($course_id>0) {
		$toprow[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$course_id, '<b>'.get_string('modlos_return_tab', 'block_modlos').'</b>');
	}
	else {
		$toprow[] = new tabobject('', $CFG->wwwroot, '<b>'.get_string('modlos_return_tab', 'block_modlos').'</b>');
	}

	$tabs = array($toprow);

	print_tabs($tabs, $currenttab, NULL, NULL);
}




function  print_modlos_header($currenttab, $course)
{
	global $CFG;


	// Print Navi Header
	if (empty($course)) {
		// TOP Page
		if (empty($CFG->langmenu)) {
			$langmenu = '';
		}
		else {
			$currlang = current_language();
			$langs = get_list_of_languages();
			$langlabel = get_accesshide(get_string('language'));
			$langmenu = popup_form('?lang=', $langs, 'chooselang', $currlang, '', '', '', true, 'self', $langlabel);
		}

		print_header(get_string('modlos', 'block_modlos'), get_string('modlos_menu', 'block_modlos'), 
					 get_string('modlos', 'block_modlos'), '', '', true, '&nbsp;', user_login_string($SITE).$langmenu);
	}
	else {
		if ($course->category) {
			print_header("$course->shortname: ".get_string('modlos','block_modlos'), $course->fullname,
					 '<a href="'.$CFG->wwwroot."/course/view.php?id={$course->id}\">$course->shortname</a> -> ".
					 get_string('modlos','block_modlos'), '', '', true, '&nbsp;', navmenu($course));
		}
		else {
			print_header("$course->shortname: ".get_string('modlos','block_modlos'), $course->fullname,
					 get_string('modlos','block_modlos'), '', '', true, '&nbsp;', navmenu($course));
		}
	}
}


?>
