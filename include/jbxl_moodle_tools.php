<?php
//
// by Fumi.Iseki 2012/04/12
//

//
// About Capabilities
//    please see http://docs.moodle.org/dev/Roles#Capability-locality_changes_in_v1.9
//

defined('MOODLE_INTERNAL') || die();

if (!defined('_JBXL_MOODLE_TOOLS')) {

define('_JBXL_MOODLE_TOOLS', 'jbxl_moodle_tools_v1.0');


/*******************************************************************************
//
// cntxt: id or context of course
//

// function  jbxl_is_admin($uid)
// function  jbxl_is_teacher($uid, $cntxt)
// function  jbxl_is_assistant($uid, $cntxt)
// function  jbxl_is_student($uid, $cntxt)
// function  jbxl_has_role($uid, $cntxt, $rolename)
//
// function  jbxl_get_course_students($cntxt, $sort='')
// function  jbxl_get_course_tachers($cntxt, $sort='')
// function  jbxl_get_course_assistants($cntxt, $sort='')
//
// function  jbxl_get_user_first_grouping($courseid, $userid)
//
// function  jbxl_db_exist_table($table, $lower_case=true)
//

*******************************************************************************/



function  jbxl_is_admin($uid)
{
	$admins = get_admins();
    foreach ($admins as $admin) {
        if ($uid==$admin->id) return true;
    }
	return false;
}



function  jbxl_is_teacher($uid, $cntxt, $inc_admin=true)
{
	global $DB;

	if (!$cntxt) return false;
	if (!is_object($cntxt)) $cntxt = get_context_instance(CONTEXT_COURSE, $cntxt);

	$role = $DB->get_field('role', 'id', array('shortname'=>'editingteacher'));
	$ret  = user_has_role_assignment($uid, $role, $cntxt->id);

	if (!$ret && $inc_admin) $ret = jbxl_is_admin($uid); 
	return $ret;
}



function  jbxl_is_assistant($uid, $cntxt)
{
	global $DB;

	if (!$cntxt) return false;
	if (!is_object($cntxt)) $cntxt = get_context_instance(CONTEXT_COURSE, $cntxt);

	$role = $DB->get_field('role', 'id', array('shortname'=>'teacher'));
	$ret  = user_has_role_assignment($uid, $role, $cntxt->id);

	return $ret;
}



function  jbxl_is_student($uid, $cntxt)
{
	global $DB;

	if (!$cntxt) return false;
	if (!is_object($cntxt)) $cntxt = get_context_instance(CONTEXT_COURSE, $cntxt);

	$role = $DB->get_field('role', 'id', array('shortname'=>'student'));
	$ret  = user_has_role_assignment($uid, $role, $cntxt->id);

	return $ret;
}



function  jbxl_has_role($uid, $cntxt, $rolename)
{
	global $DB;

	if (!$cntxt) return '';
	if (!is_object($cntxt)) $cntxt = get_context_instance(CONTEXT_COURSE, $cntxt);

	$role = $DB->get_field('role', 'id', array('shortname'=>$rolename));
	$ret  = user_has_role_assignment($uid, $role, $cntxt->id);

	return $ret;
}



function jbxl_get_course_students($cntxt, $sort='')
{
	global $DB;

	if (!$cntxt) return '';
	if (!is_object($cntxt)) $cntxt = get_context_instance(CONTEXT_COURSE, $cntxt);

	$roleid = $DB->get_field('role', 'id', array('shortname'=>'student'));
	if ($sort) $sort = " ORDER BY u.".$sort;

	$sql = "SELECT u.* FROM {role_assignments} r, {user} u ".
					 " WHERE r.contextid = ? AND r.roleid = ? AND r.userid = u.id  $sort";
    $users = $DB->get_records_sql($sql, array($cntxt->id, $roleid));

	return $users;
}



function jbxl_get_course_tachers($cntxt, $sort='')
{
	global $DB;

	if (!$cntxt) return '';
	if (!is_object($cntxt)) $cntxt = get_context_instance(CONTEXT_COURSE, $cntxt);

	$roleid = $DB->get_field('role', 'id', array('shortname'=>'editingteacher'));
	if ($sort) $sort = " ORDER BY u.".$sort;

	$sql = "SELECT u.* FROM {role_assignments} r, {user} u ".
					 " WHERE r.contextid = ? AND r.roleid = ? AND r.userid = u.id  $sort";
    $users = $DB->get_records_sql($sql, array($cntxt->id, $roleid));

	return $users;
}


function jbxl_get_course_assistants($cntxt, $sort='')
{
	global $DB;

	if (!$cntxt) return '';
	if (!is_object($cntxt)) $cntxt = get_context_instance(CONTEXT_COURSE, $cntxt);

	$roleid = $DB->get_field('role', 'id', array('shortname'=>'teacher'));
	if ($sort) $sort = " ORDER BY u.".$sort;

	$sql = "SELECT u.* FROM {role_assignments} r, {user} u ".
					 " WHERE r.contextid = ? AND r.roleid = ? AND r.userid = u.id  $sort";
    $users = $DB->get_records_sql($sql, array($cntxt->id, $roleid));

	return $users;
}




function jbxl_get_user_first_grouping($courseid, $userid)
{
	/////////////////////////////////
	return 0;	// for DEBUG
	/////////////////////////////////


	if (!$courseid or !$userid) return 0;

    $groupings = groups_get_user_groups($courseid, $userid);
	if (!is_array($groupings)) return 0;

	$keys = array_keys($groupings);
	if (count($keys)>1 && $keys[0]==0) return $keys[1];
	else return $keys[0];
}




//
// Moodle DB
//

function jbxl_db_exist_table($table, $lower_case=true)
{
	global $DB;

	$ret = false;

	$results = $DB->get_records_sql('SHOW TABLES');
	if (is_array($results)) {
		$db_tbls = array_keys($results);
		foreach($db_tbls as $db_tbl) {
			if ($lower_case) $db_tbl = strtolower($db_tbl);
			if ($db_tbl==$table) {
				$ret = true;
				break;
			}
		}
	}

	return $ret;
}			








}		// !defined('_JBXL_MOODLE_TOOLS')
?>
