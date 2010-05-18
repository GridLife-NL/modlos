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
	$ban = get_record('block_mdlos_banned', 'UUID', $uuid);
	if (!$ban) return false;

	$ret = opensim_set_password($uuid, $ban->agentinfo);
	if (!$ret) return false;

	$ret = delete_records('block_mdlos_banned', 'UUID', $uuid);
	if (!$ret) return false;
	return true;
}


function  mdlopensim_inactivate_avatar($uuid)
{
	$passwd = opensim_get_password($uuid);
	if ($passwd==null) return false;

	$passwdhash = $passwd['passwordHash'];
	if ($passwdhash==null) return false;

	$insobj->UUID 	   = $uuid;
	$insobj->agentinfo = $passwdhash;
	$insobj->time 	   = time();
	$ret = insert_record('block_mdlos_banned', $insobj);
	if (!$ret) return false;

	$ret = opensim_set_password($uuid, "invalid password");
	if (!$ret) mdlopensim_delete_banneddb($uuid);

	return $ret;
}



function  mdlopensim_delete_banneddb($uuid)
{
	$ret = delete_records('block_mdlos_banned', 'UUID', $uuid);
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

	$insobj->UUID, 	   = $user['UUID'];
	$insobj->firstname = $firstname;
	$insobj->lastname  = $user['lastname'];

	if ($user['uid']!="") 	  $insobj->uid = $user['uid'];
	else                  	  $insobj->uid = 0;

	if ($user['state']!="")   $insobj->state = $user['state'];
	else					  $insobj->state = 1;

	if ($user['created']!="") $insobj->time = $user['created'];
	else 					  $insobj->time = time();

	$regionName = opensim_get_region_name_by_id($user['hmregion']);
	if ($regionName!=null)            $insobj->hmregion = $regionName;
	else if ($user['hmregion']!=null) $insobj->hmregion = $user['hmregion'];
	else                              $insobj->hmregion = "";

	$ret = insert_record('block_mdlos_users', $insobj);
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
	if ($regionName!=null)            $updobj->hmregion = $regionName;
	else if ($user['hmregion']!=null) $updobj->hmregion = $user['hmregion'];
	else                              $updobj->hmregion = "";

	if ($user['created']!="")  $updobj->time = $user['created'];
	else 					   $updobj->time = time();

	$ret = update_record('block_mdlos_users', $insobj);
	if (!$ret) return false;
	return true;
}



function  mdlopensim_delete_usertable($user)
{
	if (!isGUID($user['UUID'])) return false;
	if ($user['state']==AVATAR_STATE_ACTIVE) return false;		// active

	$ret = delete_records('block_mdlos_users', 'UUID', $user['UUID']);
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
		$criteria = new Criteria('FounderID', $uuid);
		$groupHandler = & xoops_getmodulehandler('grouplistdb');
		$groupobjs = & $groupHandler->getObjects($criteria);
		if ($groupobjs==null) return false;

		foreach($groupobjs as $groupdata) {
			$ret = mdlopensim_delete_groupdb_by_gpid($groupdata->get('GroupID'));
			if (!$ret) return false;
		}
	}

	return true;
}



function  mdlopensim_delete_groupdb_by_uuid($uuid)
{
	delete_records('block_mdlos_group_active', 'AgentID', $uuid);
	delete_records('block_mdlos_group_invite', 'AgentID', $uuid);
	delete_records('block_mdlos_group_membership', 'AgentID', $uuid);
	delete_records('block_mdlos_group_rolemembership', 'AgentID', $uuid);

	return true;
}



function  mdlopensim_delete_groupdb_by_gpid($gpid)
{
	delete_records('block_mdlos_group_active', 'ActiveGroupID', $gpid);
	delete_records('block_mdlos_group_invite', 'GroupID', $gpid);
	delete_records('block_mdlos_group_membership', 'GroupID', $gpid);
	delete_records('block_mdlos_group_notice', 'GroupID', $gpid);
	delete_records('block_mdlos_group_role', 'GroupID', $gpid);
	delete_records('block_mdlos_group_rolemembership', 'GroupID', $gpid);
	delete_records('block_mdlos_group_list', 'GroupID', $gpid);

	return true;
}



///////////////////////////////////////////////////////////////////////////////////////
//
//

// called from synchro.class.php
function  mdlopensim_set_profiles($profs, $ovwrite=true)
{
	$handler = & xoops_getmodulehandler('profuserprofiledb');
	if ($handler==null) return false;

	foreach($profs as $prof) {
		$profobj = $handler->get($prof['UUID']);
		if ($ovwrite or $profobj==null) {
			if ($profobj==null) $profobj = & $handler->create();
			if ($profobj!=null) {
				$profobj->assignVar('useruuid', 		 $prof['UUID']);
				$profobj->assignVar('profilePartnar', 	 $prof['Partnar']);
				$profobj->assignVar('profileWantToMask', $prof['SkillsMask']);
				$profobj->assignVar('profileSkillsMask', $prof['WantToMask']);
				$profobj->assignVar('profileAboutText',  $prof['AboutText']);
				$profobj->assignVar('profileFirstText',  $prof['FirstAboutText']);
				$profobj->assignVar('profileImage', 	 $prof['Image']);
				$profobj->assignVar('profileFirstImage', $prof['FirstImage']);
				$handler->insert($profobj);
			}
		}
	}

	$handler = & xoops_getmodulehandler('profusersettingsdb');
	if ($handler==null) return false;

	foreach($profs as $prof) {
		$profobj = $handler->get($prof['UUID']);

		if ($prof['Email']!="") {
			if ($ovwrite or $profobj==null) {
				if ($profobj==null) $profobj = & $handler->create();
				if ($profobj!=null) {
					$profobj->assignVar('useruuid', $prof['UUID']);
					$profobj->assignVar('email',    $prof['Email']);
					$handler->insert($profobj);
				}
			}
		}
	}

	return true;
}




function  mdlopensim_delete_profiles($uuid)
{
    $criteria = new Criteria('useruuid', $uuid);
	$handler = & xoops_getmodulehandler('profuserprofiledb');
	$handler->deleteAll($criteria);

    $criteria = new Criteria('useruuid', $uuid);
	$handler = & xoops_getmodulehandler('profusersettingsdb');
	$handler->deleteAll($criteria);

    $criteria = new Criteria('useruuid', $uuid);
	$handler = & xoops_getmodulehandler('profusernotesdb');
	$handler->deleteAll($criteria);

    $criteria = new Criteria('creatoruuid', $uuid);
	$handler = & xoops_getmodulehandler('profuserpicksdb');
	$handler->deleteAll($criteria);

    $criteria = new Criteria('creatoruuid', $uuid);
	$handler = & xoops_getmodulehandler('profclassifiedsdb');
	$handler->deleteAll($criteria);

    return;
}

?>


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
			$toprow[] = new tabobject('lastname', CMS_MODULE_URL.'/admin/settings.php?section=blocksettingmdlopensim', 
																	'<b>'.get_string('mdlos_lastnames_tab','block_mdlopensim').'</b>');
		}
		$toprow[] = new tabobject('syncdb', MDLOPNSIM_BLK_URL.'/admin/settings.php?section=blocksettingmdlopensim', 
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
