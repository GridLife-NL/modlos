<?php
/****************************************************************
 *  moodle.func.php by Fumi.Iseki for Mdlopensim
 *
 *
 * function  hasPermit($courseid)
 * function  get_local_user_name($firstname, $lastname)
 *
 * function  get_userinfo_by_name($firstname, $lastname="")
 * function  get_userinfo_by_id($id)
 *
 ****************************************************************/



function  hasPermit($courseid)
{
    if (isguest()) return false;
    if (isadmin()) return true;
    if (isteacher($courseid)) return true;

    return false;
}



function  get_local_user_name($firstname, $lastname)
{
	global $CFG;


	if ($CFG->fullnamedisplay=='lastname firstname') {
		$username = $lastname." ".$firstname;
	}
	else if ($CFG->fullnamedisplay=='firstname') {
		$username = $firstname;
	}
	else {
		$username = $firstname." ".$lastname;
	}

	if ($username==" ") $username = "";

	return $username;
}



function  get_userinfo_by_name($firstname, $lastname="")
{
	if ($lastname=="") {
		$names = explode(" ", $firstname);
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
