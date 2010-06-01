<?php
/****************************************************************
 *	mdlopensim.func.php by Fumi.Iseki for Mdlopensim
 *
 *
 * function  mdlopensim_get_avatar_info($uuid, $use_sloodle=false, $pri_sloodle=false)
 * function  mdlopensim_set_avatar_info($avatar, $use_sloodle=false)
 * function  mdlopensim_delete_avatar_info($avatar, $use_sloodle=false)
 * function  mdlopensim_get_avatars_num($uuid, $use_sloodle=false)
 *
 * function  mdlopensim_insert_usertable($user)
 * function  mdlopensim_update_usertable($user)
 * function  mdlopensim_delete_usertable($user)
 *
 * function  mdlopensim_get_lastnames($sort="")
 *
 * function  mdlopensim_delete_groupdb($uuid, $delallgrp=false)
 * function  mdlopensim_delete_groupdb_by_gpid($gpid)
 * function  mdlopensim_delete_groupdb_by_uuid($uuid)
 *
 * function  mdlopensim_set_profiles($profs, $ovwrite=true)
 * function  mdlopensim_delete_profiles($uuid)
 *
 * function  mdlopensim_activate_avatar($uuid)
 * function  mdlopensim_inactivate_avatar($uuid)
 * function  mdlopensim_delete_banneddb($uuid)
 *
 * function  print_tabnav($currenttab, $course)
 * function  print_tabheader($currenttab, $course)
 *
 ****************************************************************/


if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH."/include/config.php");
require_once(CMS_MODULE_PATH."/include/tools.func.php");
require_once(CMS_MODULE_PATH."/include/moodle.func.php");
require_once(CMS_MODULE_PATH."/include/opensim.mysql.php");



//
// for Mdlopensim and Sloodle
//

function  mdlopensim_get_avatar_info($uuid, $use_sloodle=false, $pri_sloodle=false)
{
	if (!isGUID($uuid)) return null;

	$avatar = get_record('mdlos_users', 'uuid', $uuid);		

	$sloodle = null;
	if ($use_sloodle) {
		$sloodle = get_record(MDL_SLOODLE_USERS_TBL, 'uuid', $uuid);
		if ($sloodle!=null) {
			$names = null;
			if ($sloodle->avname!="") $names = explode(" ", $sloodle->avname);

			if ($pri_sloodle) {		// Sloodle優先
				if ($sloodle->userid>0) $avatar->user_id = $sloodle->userid;
				if (is_array($names)) {
					$avatar->firstname = $names[0];
					$avatar->lastname  = $names[1];
				}
			}
			else {
				if ($avatar->user_id=="" and $sloodle->userid>0) $avatar->user_id = $sloodle->userid;
				if (is_array($names)) {
					if ($avatar->firstname=="") $avatar->firstname = $names[0];
					if ($avatar->lastname=="")  $avatar->firstname = $names[1];
				}
			}
		}
	}
	
	if ($avatar==null and $sloodle==null) return null;
	if ($avatar->firstname=="" or $avatar->lastname=="") return null;


	//
	$avatar_info['UUID'] 	  = $uuid;
	$avatar_info['firstname'] = $avatar->firstname;
	$avatar_info['lastname']  = $avatar->lastname;

	if ($avatar->id>0) 			$avatar_info['id'] 		  = $avatar->id;
	else 						$avatar_info['id'] 		  = "";
	if ($avatar->user_id!="")  	$avatar_info['uid'] 	  = $avatar->user_id;
	else                       	$avatar_info['uid'] 	  = '0';
	if ($avatar->hmregion!="")	$avatar_info['hmregion']  = $avatar->hmregion;
	else					   	$avatar_info['hmregion']  = opensim_get_home_region($uuid);
	if ($avatar->state!="")    	$avatar_info['state'] 	  = $avatar->state;
	else					   	$avatar_info['state'] 	  = AVATAR_STATE_NOSTATE;
	if ($avatar->time!="")     	$avatar_info['time'] 	  = $avatar->time;
	else						$avatar_info['time']	  = time();

	return $avatar_info;
}




function  mdlopensim_set_avatar_info($avatar, $use_sloodle=false)
{
	if (!isGUID($avatar['UUID'])) return false;

	// Mdlopensim
	$obj = get_record('mdlos_users', 'uuid', $avatar['UUID']);		
	if ($obj==null) {
		$ret = mdlopensim_insert_usertable($avatar);
	}
	else {
		$ret = mdlopensim_update_usertable($avatar, $obj);
	}

	// Sloodle
	if ($use_sloodle and $ret) {
		$updobj = get_record(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);
		if ($updobj==null) {
			if ($avatar['state']&AVATAR_STATE_SLOODLE) {
				$insobj->userid = $avatar['uid'];
				$insobj->uuid 	= $avatar['UUID'];
				$insobj->avname = $avatar['firstname']." ".$avatar['lastname'];
				if ($insobj->avname==" ") $insobj->avname = "";
				$insobj->lastactive = time();
				$ret = insert_record(MDL_SLOODLE_USERS_TBL, $insobj);
			}
		}
		else {
			if ($avatar['state']&AVATAR_STATE_SLOODLE) {
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




function  mdlopensim_delete_avatar_info($avatar, $use_sloodle=false)
{
	if (!isGUID($avatar['UUID'])) return false;

	$ret = mdlopensim_delete_usertable($avatar);

	// Sloodle
	if ($use_sloodle and $ret) $ret = delete_records(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);

	if (!$ret) return false;
	return true;
}



function  mdlopensim_get_avatars_num($id, $use_sloodle=false)
{
	if (!isNumeric($id)) return null;

	$avatars = get_records('mdlos_users', 'user_id', $id);
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
//	UUID, firstname, lastname, uid, state, time, hmregion are setted in $user[]
//		hmregion is UUID or name of region
//

function  mdlopensim_insert_usertable($user)
{
	if (!isGUID($user['UUID'])) return false;

	$insobj->uuid 	   = $user['UUID'];
	$insobj->firstname = $user['firstname'];
	$insobj->lastname  = $user['lastname'];

	if ($user['uid']!="") 	$insobj->user_id = $user['uid'];
	else                  	$insobj->user_id = 0;
	if ($user['state']!="")	$insobj->state 	 = $user['state'];
	else				 	$insobj->state 	 = AVATAR_STATE_SYNCDB;
	if ($user['time']!="") 	$insobj->time 	 = $user['time'];
	else 					$insobj->time 	 = time();

	if (isGUID($user['hmregion'])) {
		$regionName = opensim_get_region_name($user['hmregion']);
		if ($regionName!="")$insobj->hmregion = $regionName;
		else 				$insobj->hmregion = $user['hmregion'];
	}
	else {
		$insobj->hmregion = $user['hmregion'];
	}

	$ret = insert_record('mdlos_users', $insobj);

	return $ret;
}



//
// update (Moodle's)uid, hmregion, state, time of users (Moodle DB).
//
function  mdlopensim_update_usertable($user, $updobj=null)
{
	if (!isGUID($user['UUID'])) return false;

	if ($updobj==null) {
		$updobj = get_record('mdlos_users', 'uuid', $user['UUID']);		
		if ($updobj==null) return false;
	}

	// Update
	if ($user['uid']!="") 	$updobj->user_id = $user['uid'];
	if ($user['state']!="")	$updobj->state   = $user['state'];
	if ($user['time']!="")	$updobj->time 	 = $user['time'];
	else 					$updobj->time 	 = time();

	if (isGUID($user['hmregion'])) {
		$regionName = opensim_get_region_name($user['hmregion']);
		if ($regionName!="")$updobj->hmregion = $regionName;
		else 				$updobj->hmregion = $user['hmregion'];
	}
	else if ($user['hmregion']!="") {
		$updobj->hmregion = $user['hmregion'];
	}

	$ret = update_record('mdlos_users', $updobj);

	return $ret;
}
	



function  mdlopensim_delete_usertable($user)
{
	if ($user['id']=="" and !isGUID($user['UUID'])) return false;
	if (!($user['state']&AVATAR_STATE_INACTIVE)) return false;		// active

	if ($user['id']!="") {
		$ret = delete_records('mdlos_users',   'id', $user['id']);
	}
	else {
		$ret = delete_records('mdlos_users', 'uuid', $user['UUID']);
	}

	if (!$ret) return false;
	return true;
}




//
// Last Names
//

function  mdlopensim_get_lastnames($sort="")
{
	$lastnames = array();

	$lastns = get_records("mdlos_lastnames", 'state', AVATAR_LASTN_ACTIVE, $sort, 'lastname');
	foreach ($lastns as $lastn) {
		$lastnames[] = $lastn->lastname;
	}

	return $lastnames;
}





//
// Group DB
//

function  mdlopensim_delete_groupdb($uuid, $delallgrp=false)
{
	$ret = mdlopensim_delete_groupdb_by_uuid($uuid);
	if (!$ret) return false;

	if ($delallgrp) {
		$groupobjs = get_records(MDL_XMLGROUP_LIST_TBL, 'founderid', $uuid);
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
	delete_records(MDL_XMLGROUP_ACTIVE_TBL, 	'agentid', $uuid);
	delete_records(MDL_XMLGROUP_INVITE_TBL, 	'agentid', $uuid);
	delete_records(MDL_XMLGROUP_MEMBERSHIP_TBL, 'agentid', $uuid);
	delete_records(MDL_XMLGROUP_ROLE_MEMBER_TBL,'agentid', $uuid);

	return true;
}



function  mdlopensim_delete_groupdb_by_gpid($gpid)
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

// called from synchro.class.php
function  mdlopensim_set_profiles($profs, $ovwrite=true)
{
	foreach($profs as $prof) {
		if ($prof['UUID']!="") {
			$insert = false;
			$prfobj = get_record(MDL_PROFILE_USERPROFILE_TBL, 'useruuid', $prof['UUID']);
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
				$rslt = insert_record(MDL_PROFILE_USERPROFILE_TBL, $prfobj);
			}
			else if ($ovwrite) {
				$rslt = update_record(MDL_PROFILE_USERPROFILE_TBL, $prfobj);
			}
		}
	}


	foreach($profs as $prof) {
		if ($prof['UUID']!="") {
			$insert = false;
			$setobj = get_record(MDL_PROFILE_USERSETTINGS_TBL, 'useruuid', $prof['UUID']);
			if (!$setobj) $insert = true;

			$setobj->useruuid = $prof['UUID'];

			if ($prof['ImviaEmail']!="")$setobj->imviaemail = $prof['ImviaEmail'];
			if ($prof['Visible']!="")	$setobj->visible	= $prof['Visible'];
			if ($prof['Email']!="")		$setobj->email    	= $prof['Email'];

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




function  mdlopensim_delete_profiles($uuid)
{
	delete_records(MDL_PROFILE_USERPROFILE_TBL,	'useruuid',    $uuid);
	delete_records(MDL_PROFILE_USERSETTINGS_TBL,'useruuid',    $uuid);
	delete_records(MDL_PROFILE_USERNOTES_TBL, 	'useruuid',    $uuid);
	delete_records(MDL_PROFILE_USERPICKS_TBL, 	'creatoruuid', $uuid);
	delete_records(MDL_PROFILE_CLASSIFIEDS_TBL, 'creatoruuid', $uuid);

    return;
}



//
// Bann List
//

// Active/Inactive Avatar
function  mdlopensim_activate_avatar($uuid)
{
	$ban = get_record('mdlos_banned', 'uuid', $uuid);
	if (!$ban) return false;

	$ret = opensim_set_password($uuid, $ban->agentinfo);
	if (!$ret) return false;

	$ret = delete_records('mdlos_banned', 'uuid', $uuid);
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
	$ret = insert_record('mdlos_banned', $insobj);
	if (!$ret) return false;

	$ret = opensim_set_password($uuid, "invalid_password");
	if (!$ret) mdlopensim_delete_banneddb($uuid);

	return $ret;
}



function  mdlopensim_delete_banneddb($uuid)
{
	$ret = delete_records('mdlos_banned', 'uuid', $uuid);
	if (!$ret) return false;
	return true;
}




//
// Tab Header
//

function print_tabnav($currenttab, $course)
{
	global $CFG;

	if (empty($currenttab)) $currenttab = 'world_map';
	if (empty($course)) $course_id = 0;
	else 				$course_id = $course->id;

	$hasPermit = hasPermit($course_id);

	$course_param = "";
	if ($course_id>0) $course_param = '?course='.$course_id;

	///////
	$toprow = array();
	$toprow[] = new tabobject('show_home', CMS_MODULE_URL.'/actions/show_home.php'.$course_param, 
																	'<b>'.get_string('mdlos_show_home','block_mdlopensim').'</b>');
	$toprow[] = new tabobject('map_action', CMS_MODULE_URL.'/actions/map_action.php'.$course_param, 
																	'<b>'.get_string('mdlos_world_map','block_mdlopensim').'</b>');
	$toprow[] = new tabobject('regions_list', CMS_MODULE_URL.'/actions/regions_list.php'.$course_param, 
																	'<b>'.get_string('mdlos_regions_list','block_mdlopensim').'</b>');
	if (!isGuest()) {
		$toprow[] = new tabobject('avatars_list', CMS_MODULE_URL.'/actions/avatars_list.php'.$course_param, 
																	'<b>'.get_string('mdlos_avatars_list','block_mdlopensim').'</b>');
		$toprow[] = new tabobject('create_avatar', CMS_MODULE_URL.'/actions/create_avatar.php'. $course_param, 
																	'<b>'.get_string('mdlos_avatar_create','block_mdlopensim').'</b>');
	}

	if ($hasPermit) {
		if ($CFG->mdlopnsm_activate_lastname) {
			$toprow[] = new tabobject('lastnames', CMS_MODULE_URL.'/admin/actions/lastnames.php'.$course_param, 
																	'<b>'.get_string('mdlos_lastnames_tab','block_mdlopensim').'</b>');
		}
		$toprow[] = new tabobject('synchrodb', CMS_MODULE_URL.'/admin/actions/synchrodb.php'.$course_param, 
																	'<b>'.get_string('mdlos_synchro_tab','block_mdlopensim').'</b>');
		if (isadmin()) {
			$toprow[] = new tabobject('settings', $CFG->wwwroot.'/admin/settings.php?section=blocksettingmdlopensim', 
																	'<b>'.get_string('mdlos_general_setting_tab','block_mdlopensim').'</b>');
		}
	}

	if ($course_id>0) {
		$toprow[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$course_id, '<b>'.get_string('mdlos_return_tab', 'block_mdlopensim').'</b>');
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
