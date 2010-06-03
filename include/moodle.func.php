<?php
/****************************************************************
 *  moodle.func.php by Fumi.Iseki for Mdlopensim
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



function  hasPermit($course_id)
{
	//return false;

    if (isguest()) return false;
    if (isadmin()) return true;
	if ($course_id==null) return false;
    if (isteacher($course_id)) return true;

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
	/*
	else if ($CFG->fullnamedisplay=='firstname') {
		$username = $firstname;
	}*/
	else {
		$username = $firstname.' '.$lastname;
	}

	if ($username==' ') $username = '';

	return $username;
}



function  get_names_from_display_username($username)
{
	global $CFG;

	$names = explode(' ', $username);
	if ($names==null) return null;

	if ($CFG->fullnamedisplay=='lastname firstname') {
		$firstN = $names[1];
		$lastN  = $names[0];
	}
	/*
	else if ($CFG->fullnamedisplay=='firstname') {
		$fisetN = $names[0];
		$lastN  = '';
	}*/
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
	if ($lastname=='') {
		$names = explode(' ', $firstname);
		$firstname = $names[0];
		$lastname  = $names[1];
	}

	$user_info = get_record('user', 'firstname', $firstname, 'lastname', $lastname, 'deleted', '0');
	return $user_info;
}




function  get_userinfo_by_id($id)
{
	if ($id<=0) return null;

	$user_info = get_record('user', 'id', $id, 'deleted', '0');
	return $user_info;
}


?>
