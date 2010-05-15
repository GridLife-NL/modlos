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




?>
