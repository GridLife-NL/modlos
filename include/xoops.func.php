<?php
/****************************************************************
	xoops.func.php  by Fumi.Iseki


function  get_userid_by_name($username)
function  get_username_by_id($uid)

****************************************************************/


if (!defined('XOOPS_ROOT_PATH')) exit();




function  get_userid_by_name($username)
{
	$uid = 0;
	if ($username==null or $username=="") return $uid;

	$userHandler =& xoops_getmodulehandler('users', 'user');
	$criteria =& new CriteriaCompo();
	$criteria->add(new Criteria('uname', $username));
	$userArr =& $userHandler->getObjects($criteria);
	if (count($userArr)!=0) $uid = $userArr[0]->get('uid');

	return $uid;
}



function  get_username_by_id($uid)
{
	$uname = "";
	if ($uid==null or $uid=="" or $uid=='0') return $uname;

	$userHandler =& xoops_getmodulehandler('users', 'user');
	$criteria =& new CriteriaCompo();
	$criteria->add(new Criteria('uid', $uid));
	$userArr =& $userHandler->getObjects($criteria);
	if (count($userArr)!=0) $uname = $userArr[0]->get('uname');

	return $uname;
}


?>
