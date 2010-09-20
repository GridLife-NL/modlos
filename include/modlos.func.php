<?php
/****************************************************************
 *	modlos.func.php by Fumi.Iseki for Modlos
 *
 *
 ****************************************************************/


/****************************************************************
 * Functions

 // Tools
 function  hasModlosPermit($course_id=0)

 // DB
 function  modlos_get_update_time($fullname_table)

 // Avatars (with Sloodle)
 function  modlos_get_avatars($uid=0, $use_sloodle=false)
 function  modlos_get_avatars_num($uid=0, $use_sloodle=false)

 function  modlos_get_avatar_info($uuid, $use_sloodle=false)
 function  modlos_set_avatar_info($avatar, $use_sloodle=false)
 function  modlos_delete_avatar_info($avatar, $use_sloodle=false)

 // User Table
 function  modlos_get_userstable()
 function  modlos_insert_userstable($user)
 function  modlos_update_userstable($user, $updobj=null)
 function  modlos_delete_userstable($user)

 // Last Names
 function  modlos_get_lastnames($sort='')

 // Group
 function  modlos_delete_groupdb($uuid, $delallgrp=false)
 function  modlos_delete_groupdb_by_uuid($uuid)
 function  modlos_delete_groupdb_by_gpid($gpid)

 // Profile
 function  modlos_get_profile($uuid)
 function  modlos_delete_profiles($uuid)
 function  modlos_set_profiles_from_users($profs, $ovwrite=true)

 // Events
 function  modlos_get_events($uid=0, $start=0, $limit=25, $pg_only=false, $tm=0)
 function  modlos_get_events_num($uid=0, $pg_only=false, $tm=0)
 function  modlos_get_event($eventid)
 function  modlos_set_event($event)


 // Login Screen
 function  modlos_get_loginscreen_alert()
 function  modlos_set_loginscreen_alert($alert)

 // Bann Avatar
 function  modlos_activate_avatar($uuid)
 function  modlos_inactivate_avatar($uuid)
 function  modlos_delete_banneddb($uuid)

 // Synchro DB
 function  modlos_sync_opensimdb($timecheck=true)
 function  modlos_sync_sloodle_users($timecheck=true)

 // Tab Menu
 function  print_tabnav($currenttab, $course, $create_tab=true)
 function  print_tabnav_manage($currenttab, $course)
 function  print_modlos_header($currenttab, $course)

 ****************************************************************/



/****************************************************************
 Reference: Moodle DB Functions from lib/dmllib.php  (Memo)

 function record_exists($table, $field1='', $value1='', $field2='', $value2='', $field3='', $value3='')
 function count_records($table, $field1='', $value1='', $field2='', $value2='', $field3='', $value3='')

 function get_record($table, $field1, $value1, $field2='', $value2='', $field3='', $value3='', $fields='*')
 function get_records($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='')
 function get_field($table, $return, $field1, $value1, $field2='', $value2='', $field3='', $value3='')
 function set_field($table, $newfield, $newvalue, $field1, $value1, $field2='', $value2='', $field3='', $value3='')

 function count_records_select($table, $select='', $countitem='COUNT(*)')
 function get_recordset_select($table, $select='', $sort='', $fields='*', $limitfrom='', $limitnum='')

 function delete_records($table, $field1='', $value1='', $field2='', $value2='', $field3='', $value3='')
 function delete_records_select($table, $select='')

 function insert_record($table, $dataobject, $returnid=true, $primarykey='id')
 function update_record($table, $dataobject)

 ****************************************************************/



if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/config.php');
require_once(CMS_MODULE_PATH.'/include/tools.func.php');
require_once(CMS_MODULE_PATH.'/include/moodle.func.php');
require_once(CMS_MODULE_PATH.'/include/opensim.mysql.php');



///////////////////////////////////////////////////////////////////////////////
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




///////////////////////////////////////////////////////////////////////////////
//
// DB
//

function  modlos_get_update_time($fullname_table)
{
	if ($fullname_table=="") return 0;

	$db = new DB(CMS_DB_HOST, CMS_DB_NAME, CMS_DB_USER, CMS_DB_PASS);
	$update = $db->get_update_time($fullname_table);

	return $update;
}




///////////////////////////////////////////////////////////////////////////////
//
// Avatars with Sloodle
//

function  modlos_get_avatars($uid=0, $use_sloodle=false)
{
	if (!isNumeric($uid)) return null;

	if ($uid==0) $users = get_records('modlos_users');
	else 		 $users = get_records('modlos_users', 'user_id', $uid);

	$avatars = array();
	if ($users!=null) {
		foreach ($users as $user) {
			$uuid = $user->uuid;
			$avatars[$uuid]['UUID'] 	= $user->uuid;
			$avatars[$uuid]['user_id'] 	= $user->user_id;
			$avatars[$uuid]['firstname']= $user->firstname;
			$avatars[$uuid]['lastname'] = $user->lastname;
			$avatars[$uuid]['hmregion'] = $user->hmregion;
			$avatars[$uuid]['state'] 	= $user->state;
			$avatars[$uuid]['time'] 	= $user->time;
			$avatars[$uuid]['fullname'] = $avatars[$uuid]['firstname']." ".$avatars[$uuid]['lastname'];
		}
	}

	if ($use_sloodle) {
		if ($uid==0) $sloodles = get_records(MDL_SLOODLE_USERS_TBL);
		else 		 $sloodles = get_records(MDL_SLOODLE_USERS_TBL, 'userid', $uid);

		foreach ($sloodles as $sloodle) {
			$match = false;
			foreach ($users as $user) {
				if ($sloodle->uuid==$user->uuid) {
					$match = true;
					break;
				}
			}
			if (!$match) {
				$uuid = $sloodle->uuid;
				$avatars[$uuid]['UUID'] 	= $sloodle->uuid;
				$avatars[$uuid]['user_id'] 	= $sloodle->userid;
				$avatars[$uuid]['fullname'] = $sloodle->avname;
				$avname 					= explod(" ", $sloodle->avname);
				$avatars[$uuid]['firstname']= $avname[0];
				$avatars[$uuid]['lastname'] = $avname[1];
				$avatars[$uuid]['hmregion'] = '';
				$avatars[$uuid]['state'] 	= '';
				$avatars[$uuid]['time'] 	= $sloodle->alastactive;
			}
		}
	}

	return $avtars;
}



function  modlos_get_avatars_num($uid=0, $use_sloodle=false)
{
	if (!isNumeric($uid)) return null;

	if ($uid==0) $users = get_records('modlos_users');
	else 		 $users = get_records('modlos_users', 'user_id', $uid);

	if (is_array($users)) $num = count($users);
	else $num = 0;

	if ($use_sloodle) {
		if ($uid==0) $sloodles = get_records(MDL_SLOODLE_USERS_TBL);
		else 		 $sloodles = get_records(MDL_SLOODLE_USERS_TBL, 'userid', $uid);

		foreach ($sloodles as $sloodle) {
			$match = false;
			foreach ($users as $user) {
				if ($sloodle->uuid==$user->uuid) {
					$match = true;
					break;
				}
			}
			if (!$match) $num++;
		}
	}

	return $num;
}




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
	if ($avatar->state!='')		$avatar_info['state'] 	 = (int)$avatar->state;
	else					   	$avatar_info['state'] 	 = AVATAR_STATE_NOSTATE;
	if ($avatar->time!='')	 	$avatar_info['time'] 	 = $avatar->time;
	else						$avatar_info['time']	 = time();

	return $avatar_info;
}




function  modlos_set_avatar_info($avatar, $use_sloodle=false)
{
	if (!isGUID($avatar['UUID'])) return false;

	// Sloodle
	if ($use_sloodle) {
		$updobj = get_record(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);
		if ($updobj==null) {
			if ((int)$avatar['state']&AVATAR_STATE_SLOODLE) {
				$insobj->userid = $avatar['uid'];
				$insobj->uuid 	= $avatar['UUID'];
				$insobj->avname = $avatar['firstname'].' '.$avatar['lastname'];
				if ($insobj->avname==' ') $insobj->avname = '';
				$insobj->lastactive = time();
				$ret = insert_record(MDL_SLOODLE_USERS_TBL, $insobj);
			}
		}
		else {
			if ((int)$avatar['state']&AVATAR_STATE_SLOODLE and $avatar['uid']!=0) {
				$updobj->userid = $avatar['uid'];
				$updobj->lastactive = time();
				$ret = update_record(MDL_SLOODLE_USERS_TBL, $updobj);
			}
			else {
				$ret = delete_records(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);
			}
		}
	}


	// Modlos
	$obj = get_record('modlos_users', 'uuid', $avatar['UUID']);		
	if ($obj==null) {
		$ret = modlos_insert_userstable($avatar);
	}
	else {
		$ret = modlos_update_userstable($avatar, $obj);
	}

	return $ret;
}




function  modlos_delete_avatar_info($avatar, $use_sloodle=false)
{
	if (!isGUID($avatar['UUID'])) return false;

	// Sloodle
	if ($use_sloodle) $ret = delete_records(MDL_SLOODLE_USERS_TBL, 'uuid', $avatar['UUID']);

	$ret = modlos_delete_userstable($avatar);

	if (!$ret) return false;
	return true;
}




///////////////////////////////////////////////////////////////////////////////
//
// Users Table DB
//
//

function  modlos_get_userstable()
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

function  modlos_insert_userstable($user)
{
	if (!isGUID($user['UUID'])) return false;

	$insobj->uuid 	   = $user['UUID'];
	$insobj->firstname = $user['firstname'];
	$insobj->lastname  = $user['lastname'];

	if ($user['uid']!='') 	$insobj->user_id = $user['uid'];
	else				  	$insobj->user_id = '0';
	if ($user['state']!='')	$insobj->state 	 = (int)$user['state'];
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
function  modlos_update_userstable($user, $updobj=null)
{
	if (!isGUID($user['UUID'])) return false;

	if ($updobj==null) {
		$updobj = get_record('modlos_users', 'uuid', $user['UUID']);		
		if ($updobj==null) return false;
	}

	// Update
	if ($user['uid']!='') 	$updobj->user_id = $user['uid'];
	if ($user['state']!='')	$updobj->state   = (int)$user['state'];
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
	



function  modlos_delete_userstable($user)
{
	if ($user['id']=='' and !isGUID($user['UUID'])) return false;
	if (!((int)$user['state']&AVATAR_STATE_INACTIVE)) return false;		// active

	if ($user['id']!='') {
		$ret = delete_records('modlos_users',   'id', $user['id']);
	}
	else {
		$ret = delete_records('modlos_users', 'uuid', $user['UUID']);
	}

	if (!$ret) return false;
	return true;
}




///////////////////////////////////////////////////////////////////////////////
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





///////////////////////////////////////////////////////////////////////////////
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
// Profile
//

function  modlos_get_profile($uuid)
{
	$prof = array();

	$prfobj = get_record(MDL_PROFILE_USERPROFILE_TBL, 'useruuid', $uuid);
	if ($prfobj) {
		$prof['UUID'] 			= $prfobj->useruuid;
		$prof['Partnar'] 		= $prfobj->profilepartnar;
		$prof['Image']	 		= $prfobj->profileimage;
		$prof['AboutText']		= $prfobj->profileabouttext;
		$prof['AllowPublish']	= $prfobj->profileallowpublish;
		$prof['MaturePublish']	= $prfobj->profilematurepublish;
		$prof['URL']			= $prfobj->profileurl;
		$prof['WantToMask']		= $prfobj->profilewanttomask;
		$prof['SkillsMask']		= $prfobj->profileskillsmask;
		$prof['WantToText']		= $prfobj->profilewanttotext;
		$prof['SkillsText']		= $prfobj->profileskillstext;
		$prof['LanguagesText']	= $prfobj->profilelanguagestext;
		$prof['FirstAboutText'] = $prfobj->profilefirsttext;
		$prof['FirstImage']		= $prfobj->profilefirstimag;
	}

	return $prof;
}




// called from updatedb.class.php
function  modlos_set_profiles_from_users($profs, $ovwrite=true)
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
			//if ($prof['AllowPublish']!='')	$prfobj->profileallowpublish  = $prof['AllowPublish'];
			//if ($prof['MaturePublish']!='')	$prfobj->profilematurepublish = $prof['MaturePublish'];
			//if ($prof['URL']!='')			$prfobj->profileurl 		  = $prof['URL'];
			if ($prof['WantToMask']!='')	$prfobj->profilewanttomask 	  = $prof['WantToMask'];
			if ($prof['SkillsMask']!='')	$prfobj->profileskillsmask 	  = $prof['SkillsMask'];
			//if ($prof['WantToText']!='')	$prfobj->profilewanttotext 	  = $prof['WantToText'];
			//if ($prof['SkillsText']!='')	$prfobj->profileskillstext 	  = $prof['SkillsText'];
			//if ($prof['LanguagesText']!='')	$prfobj->profilelanguagestext = $prof['LanguagesText'];
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

			//if ($prof['ImviaEmail']!='')$setobj->imviaemail = $prof['ImviaEmail'];
			//if ($prof['Visible']!='')	$setobj->visible	= $prof['Visible'];
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




///////////////////////////////////////////////////////////////////////////////////////
// 
// Events
//

function  modlos_get_events($uid=0, $start=0, $limit=25, $pg_only=false, $tm=0)
{
	$events = array();
	if ($tm==0) $tm = time();

	$select = "dateUTC > '$tm'";
	if ($pg_only) $select .= " AND eventflags='0'";
	if ($uid>0)   $select .= " AND uid='$uid'";

	$rets = get_recordset_select('modlos_search_events', $select, 'dateUTC', '*', $start, $limit);
   
	if ($rets!=null) {
		$num = 0;
		foreach ($rets as $event) {
			$events[$num]['uid']		 = $event->uid;
			$events[$num]['owneruuid']   = $event->owneruuid;
			$events[$num]['name']		 = $event->name;
			$events[$num]['eventid']	 = $event->eventid;
			$events[$num]['creatoruuid'] = $event->creatoruuid;
			$events[$num]['category']	 = $event->category;
			$events[$num]['description'] = $event->description;
			$events[$num]['dateUTC']	 = $event->dateUTC;
			$events[$num]['duration']	 = $event->duration;
			$events[$num]['covercharge'] = $event->covercharge;
			$events[$num]['coveramount'] = $event->coveramount;
			$events[$num]['simname']	 = $event->simname;
			$events[$num]['globalPos']   = $event->globalPos;
			$events[$num]['eventflags']  = $event->eventflags;
			$num++;
		}
	}
   
	return $events;
}




function  modlos_get_events_num($uid=0, $pg_only=false, $tm=0)
{  
	if ($tm==0) $tm = time();
   
	$select = "dateUTC > '$tm'";
	if ($pg_only) $select .= " AND eventflags='0'";
	if ($uid>0)   $select .= " AND uid='$uid'";

	$events_num = count_records_select('modlos_search_events', $select);
   
	return $events_num;
}


   
function  modlos_get_event($eventid)
{  
	$event = get_record('modlos_search_events', 'eventid', $eventid);
   
	$ret = array();
	if ($event!=null) {
		$ret['uid']		 	= $event->uid;
		$ret['owneruuid']   = $event->owneruuid;
		$ret['name']		= $event->name;
		$ret['eventid']	 	= $event->eventid;
		$ret['creatoruuid'] = $event->creatoruuid;
		$ret['category']	= $event->category;
		$ret['description'] = $event->description;
		$ret['dateUTC']	 	= $event->dateUTC;
		$ret['duration']	= $event->duration;
		$ret['covercharge'] = $event->covercharge;
		$ret['coveramount'] = $event->coveramount;
		$ret['simname']	 	= $event->simname;
		$ret['globalPos']   = $event->globalPos;
		$ret['eventflags']  = $event->eventflags;
	}
   
	return $ret;
}




function  modlos_set_event($event)
{
	$dbobj->eventid = 0;

	if ($event['eventid']>0) {
		$dbobj = get_record('modlos_search_events', 'eventid', $event['eventid']);
		if ($dbobj==null) $dbobj->eventid = 0;
	}
   
	$dbobj->uid		 	= $event['uid'];
	$dbobj->owneruuid	= $event['owneruuid'];
	$dbobj->name		= $event['name'];
	$dbobj->creatoruuid	= $event['creatoruuid'];
	$dbobj->category	= $event['category'];
	$dbobj->description	= $event['description'];
	$dbobj->dateUTC		= $event['dateUTC'];
	$dbobj->duration	= $event['duration'];
	$dbobj->covercharge = $event['covercharge'];
	$dbobj->coveramount = $event['coveramount'];
	$dbobj->simname		= $event['simname'];
	$dbobj->globalPos 	= $event['globalPos'];
	$dbobj->eventflags 	= $event['eventflags'];
 
	if ($dbobj->eventid>0) { 
		$ret = insert_record('searcheventsdb', $dbobj, true, 'eventid');
	}
	else {
		$ret = update_record('searcheventsdb', $dbobj);
	}
	return $ret;
}




///////////////////////////////////////////////////////////////////////////////////////
// 
// Login Screen
//

function  modlos_get_loginscreen_alert()
{
	$ret = array();

	$alerts = get_records('modlos_login_screen');

	if ($alerts!=null) {
		foreach($alerts as $alert) {
			if ($alert->id!=null) break;
		}
		if ($alert!=null and $alert->id!=null) {
			$ret['id'] 			= $alert->id;
			$ret['title'] 		= $alert->title;
			$ret['information'] = $alert->information;
			$ret['bordercolor'] = $alert->bordercolor;
			$ret['timestamp'] 	= $alert->timestamp;
		}
	}

	return $ret;
}



function  modlos_set_loginscreen_alert($alert)
{
	$obj->title 	  = '';
	$obj->information = '';
	$obj->bordercolor = 'white';
	$obj->timestamp   = time();

	if ($alert['title']!=null) 		 $obj->title = $alert['title'];
	if ($alert['information']!=null) $obj->information = $alert['information'];
	if ($alert['bordercolor']!=null) $obj->bordercolor = $alert['bordercolor'];

	$getobj = modlos_get_loginscreen_alert();
	if ($getobj!=null and $getobj['id']!=null) {
		// update
		$obj->id = $getobj['id'];
		$ret = update_record('modlos_login_screen', $obj);
	}
	else {
		// insert;
		$ret = insert_record('modlos_login_screen', $obj);
	}

	return $ret;
}




///////////////////////////////////////////////////////////////////////////////////////
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





//////////////////////////////////////////////////////////////////////////////////
//
// Synchro DB
//

function  modlos_sync_opensimdb($timecheck=true)
{
	if ($timecheck) {
		$opensim_up = opensim_users_update_time();
		$modlos_up  = modlos_get_update_time(MDL_DB_PREFIX.'modlos_users');
		if ($modlos_up>$opensim_up) return;
	}


	$opnsim_users = opensim_get_avatars_infos();	// OpenSim DB
	$modlos_users = modlos_get_userstable(); 		// Modlos DB

	// OpenSimに対応データが無い場合はデータを消す．
	foreach ($modlos_users as $modlos_user) {
		$moodle_uuid = $modlos_user['UUID'];
		if (!array_key_exists($moodle_uuid, $opnsim_users)) {
			$modlos_user['state'] = (int)$modlos_user['state']|AVATAR_STATE_INACTIVE;
			modlos_delete_userstable($modlos_user);
		}
	}

	// OpenSimにデータがある場合は，Modlos のデータを OpenSimにあわせる．
	foreach ($opnsim_users as $opnsim_user) {
		$opnsim_user['time'] = time();
		if (array_key_exists($opnsim_user['UUID'], $modlos_users)) {
			//$opnsim_user['id'] = $modlos_users[$opnsim_user['UUID']]['id'];
			$opnsim_user['uid']   = $modlos_users[$opnsim_user['UUID']]['uid'];
			$opnsim_user['state'] = $modlos_users[$opnsim_user['UUID']]['state'];
			modlos_update_userstable($opnsim_user);
		}
		else {
			$opnsim_user['uid']   = 0;
			$opnsim_user['state'] = AVATAR_STATE_SYNCDB;
			modlos_insert_userstable($opnsim_user);
		}
	}

	return true;
}



function  modlos_sync_sloodle_users($timecheck=true)
{
	if ($timecheck) {
		$sloodle_up = modlos_get_update_time(MDL_DB_PREFIX.MDL_SLOODLE_USERS_TBL);
		$modlos_up  = modlos_get_update_time(MDL_DB_PREFIX.'modlos_users');
		if ($modlos_up>$sloodle_up) return;
	}


	$sloodles = get_records(MDL_SLOODLE_USERS_TBL);
	$modloses = get_records('modlos_users');

	if (is_array($sloodles) and is_array($modloses)) {
		foreach ($modloses as $modlos) {
			$with_sloodle = false;
			foreach($sloodles as $sloodle) {
				if ($modlos->uuid==$sloodle->uuid) {
					$modlos->user_id = $sloodle->userid;
					$modlos->state = (int)$modlos->state|AVATAR_STATE_SLOODLE;
					$with_sloodle = true;
					break;
				}
			}

			if ($with_sloodle) {
				update_record('modlos_users', $modlos);
			}
			else if ((int)$modlos->state&AVATAR_STATE_SLOODLE) {
				$modlos->user_id = '0';
				$modlos->state = (int)$modlos->state & AVATAR_STATE_NOSLOODLE;
				update_record('modlos_users', $modlos);
			}
		}
	}
}




//////////////////////////////////////////////////////////////////////////////////
//
// Tab Menu
//

function  print_tabnav($currenttab, $course, $show_create_tab=true)
{
	global $CFG;

	if (empty($currenttab)) $currenttab = 'show_home';
	if (empty($course)) $course_id = 0;
	else 				$course_id = $course->id;

	$hasPermit = hasModlosPermit($course_id);

	$course_param = '';
	if ($course_id>0) $course_param = '?course='.$course_id;

	///////
	$toprow = array();
	$toprow[] = new tabobject('show_home', CMS_MODULE_URL.'/actions/show_home.php'.$course_param, 
																	'<b>'.get_string('modlos_showhome_tab','block_modlos').'</b>');
	$toprow[] = new tabobject('world_map', CMS_MODULE_URL.'/actions/map_action.php'.$course_param, 
																	'<b>'.get_string('modlos_world_map','block_modlos').'</b>');
	$toprow[] = new tabobject('regions_list', CMS_MODULE_URL.'/actions/regions_list.php'.$course_param, 
																	'<b>'.get_string('modlos_regions_list','block_modlos').'</b>');
	if (!isGuest()) {
		$toprow[] = new tabobject('avatars_list', CMS_MODULE_URL.'/actions/avatars_list.php'.$course_param, 
																	'<b>'.get_string('modlos_avatars_list','block_modlos').'</b>');
		if ($show_create_tab) {
			$toprow[] = new tabobject('create_avatar', CMS_MODULE_URL.'/actions/create_avatar.php'. $course_param, 
																	'<b>'.get_string('modlos_avatar_create','block_modlos').'</b>');
		}
		if ($CFG->modlos_use_events) {
			$toprow[] = new tabobject('events_list', CMS_MODULE_URL.'/actions/events_list.php'. $course_param, 
																	'<b>'.get_string('modlos_events_tab','block_modlos').'</b>');
		}
	}

	if ($hasPermit) {
		$toprow[] = new tabobject('management', CMS_MODULE_URL.'/admin/actions/management.php'.$course_param, 
																	'<b>'.get_string('modlos_manage_tab','block_modlos').'</b>');
	}

	if ($course_id>0) {
		$toprow[] = new tabobject('', $CFG->wwwroot.'/course/view.php?id='.$course_id, '<b>'.get_string('modlos_return_tab', 'block_modlos').'</b>');
	}
	else {
		$toprow[] = new tabobject('', $CFG->wwwroot, '<b>'.get_string('modlos_return_sitetop_tab', 'block_modlos').'</b>');
	}

	$tabs = array($toprow);

	print_tabs($tabs, $currenttab, NULL, NULL);
}



function  print_tabnav_manage($currenttab, $course)
{
	global $CFG;

	if (empty($currenttab)) $currenttab = 'management';
	if (empty($course)) $course_id = 0;
	else 				$course_id = $course->id;

	$hasPermit = hasModlosPermit($course_id);

	$course_param = '';
	if ($course_id>0) $course_param = '?course='.$course_id;

	///////
	$toprow = array();
	$toprow[] = new tabobject('show_home', CMS_MODULE_URL.'/actions/show_home.php'.$course_param, 
																	'<b>'.get_string('modlos_showhome_tab','block_modlos').'</b>');
	if ($hasPermit) {
		$toprow[] = new tabobject('management', CMS_MODULE_URL.'/admin/actions/management.php'.$course_param, 
																	'<b>'.get_string('modlos_manage_cmnd_tab','block_modlos').'</b>');
		$toprow[] = new tabobject('loginscreen', CMS_MODULE_URL.'/admin/actions/loginscreen.php'.$course_param, 
																	'<b>'.get_string('modlos_lgnscrn_tab','block_modlos').'</b>');
		if ($CFG->modlos_activate_lastname) {
			$toprow[] = new tabobject('lastnames', CMS_MODULE_URL.'/admin/actions/lastnames.php'.$course_param, 
																	'<b>'.get_string('modlos_lastnames_tab','block_modlos').'</b>');
		}
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
		$toprow[] = new tabobject('', $CFG->wwwroot, '<b>'.get_string('modlos_return_sitetop_tab', 'block_modlos').'</b>');
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

