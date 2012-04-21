<?php
/****************************************************************
 *  moodle.func.php by Fumi.Iseki for Modlos
 *
 *
 * function  hasPermit($courseid)
 * function  get_display_username($firstname, $lastname)
 * function  get_names_from_display_username($username)
 *
 * function  get_userinfo_by_name($firstname, $lastname='')
 * function  get_userinfo_by_id($id)
 *
 ****************************************************************/


require_once(CMS_MODULE_PATH.'/include/jbxl_moodle_tools.php');


function  hasPermit($course_id=0)
{
	global $USER;

	if (isguestuser($USER->id)) return false;
	if (jbxl_is_admin($USER->id)) return true;
	if ($course_id==0 or $course_id==null) return false;

	$cntxt = get_context_instance(CONTEXT_COURSE, $course_id);
    if (jbxl_is_teacher($USER->id, $cntxt, false)) return true;
    if (jbxl_is_assistant($USER->id, $cntxt)) return true;

    return false;
}



//
// only use to display
//

function  get_display_username($firstname, $lastname)
{
	global $CFG;

	if ($CFG->fullnamedisplay=='lastname firstname') {
		$username = $lastname.' '.$firstname;
	}
	else if ($CFG->fullnamedisplay=='language' and current_language()=='ja_utf8') {
		$username = $lastname.' '.$firstname;
	}
	else {
		$username = $firstname.' '.$lastname;
	}

	if ($username==' ') $username = '';

	return $username;
}



function  get_names_from_display_username($username)
{
	global $CFG;

	//$names = explode(' ', $username);
	$names = preg_split("/ /", $username, 0, PREG_SPLIT_NO_EMPTY);
	if ($names==null) return null;

	if ($CFG->fullnamedisplay=='lastname firstname') {
		$firstN = $names[1];
		$lastN  = $names[0];
	}
	else if ($CFG->fullnamedisplay=='language' and current_language()=='ja_utf8') {
		$firstN = $names[1];
		$lastN  = $names[0];
	}
	else {
		$firstN = $names[0];
		$lastN  = $names[1];
	}
	
	$retname['firstname'] = $firstN;
	$retname['lastname']  = $lastN;

	return $retname;
}




function  get_userinfo_by_name($firstname, $lastname='')
{
	global $DB;

	if ($lastname=='') {
		//$names = explode(' ', $firstname);
		$names = preg_split("/ /", $firstname, 0, PREG_SPLIT_NO_EMPTY);
		$firstname = $names[0];
		$lastname  = $names[1];
	}

	$user_info = $DB->get_record('user', array('firstname'=>$firstname, 'lastname'=>$lastname, 'deleted'=>'0'));
	return $user_info;
}




function  get_userinfo_by_id($id)
{
	global $DB;

	if ($id<=0) return null;

	$user_info = $DB->get_record('user', array('id'=>$id, 'deleted'=>'0'));
	return $user_info;
}


?>
