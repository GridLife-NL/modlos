<?php
/****************************************************************
 *  moodle.func.php by Fumi.Iseki for Mdlopensim
 *
 *
 * function hasPermit($courseid)
 *
 ****************************************************************/



function hasPermit($courseid)
{
    if (isguest()) return false;
    if (isadmin()) return true;
    if (isteacher($courseid)) return true;

    return false;
}



function getUserName($firstname, $lastname)
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





?>
