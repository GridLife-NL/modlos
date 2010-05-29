<?php

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH."/include/mdlopensim.func.php");



class  CreateAvatar
{
	var $regionNames  = array();
	var $actvLastName = false;

	var $hasPermit 	= false;
	var $module_url	= "";
	var $action_url	= "";
	var $course_url	= "";

	var $created_avatar = false;

	var $course_id  = 0;
	var $use_sloodle= false;
	var $pri_sloodle= false;

	var $hasError   = false;
	var $errorMsg   = array();

	// Moodle DB
	var $UUID	   	= "";
	var $nx_UUID   	= "";
	var $uid	   	= 0;			// owner id of avatar
	var $firstname 	= "";
	var $lastname  	= "";
	var $passwd 	= "";
	var $hmregion  	= "";
	var $ownername 	= "";		// owner name of avatar



	function  CreateAction($courseid)
	{
		global $CFG;

		require_login($courseid);

		// for HTTPS
		$use_https = $CFG->mdlopnsm_use_https;
		if ($use_https) {
			$https_url = $CFG->mdlopnsm_https_url;

			if ($https_url!="") {
				$this->module_url = $https_url.CMS_DIR_NAME;
			}
			else {
				$this->module_url = ereg_replace('^http:', 'https:', CMS_MODULE_URL);
			}
		}
		else {
			$this->module_url = CMS_MODULE_URL;
		}

		$this->course_id   	= $course_id;
		$this->hasPermit	= hasPsermit($course_id);
		$this->action_url  	= $this->module_url."/actions/create_avatar.php";
		$this->course_url 	= $CFG->wwwroot;
		$this->use_sloodle 	= $CFG->mdlopnsm_cooperate_sloodle;
		$this->pri_sloodle 	= $CFG->mdlopnsm_priority_sloodle;
		$this->actvLastName	= $CFG->mdlopnsm_activate_lastname;

		if ($course_id>0) {
			$this->course_url.= "/course/view.php?id=".$course_id;
		}

		// Number of Avatars Check
		if (!$this->hasPermit) {
			$avatars_num = mdlopensim_get_avatars_num($USER->id);
			$max_avatars = $CFG->mdlopnsm_max_own_avatars;
			if ($max_avatars>=0 and $avatars_num>=$max_avatars) {
				error(get_string('mdlos_over_max_avatars', 'block_mdlopensim')." ($avatars_num >= $max_avatars)", $this->course_url);
			}
		}
	}



	function  execute()
	{
		// Region Name
		$this->regionNames = opensim_get_regions_names("ORDER BY regionName ASC");

		if (data_submitted()) {
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string("mdlos_sesskey_error", "block_mdlopensim");
				return false;
			}
		}

		if ($this->hasPermit) {
			do {
				$uuid = make_random_guid();
				$modobj = mdlopensim_get_avara_info($uuid);
			} while ($modobj!=null);
			$this->nx_UUID = $uuid;
		}


		if (data_submitted()) {
			$this->firstname= $optional_param('firstname', 	'', PARAM_TEXT);
			$this->lastname = $optional_param('lastname',  	'', PARAM_TEXT);
			$this->passwd	= $optional_param('passwd', 	'', PARAM_TEXT);
			$confirm_pass	= $optional_param('confirm_pass','',PARAM_TEXT);
			$this->hmregion = $optional_param('hmregion', 	'', PARAM_TEXT);
			if($this->hasPermit) {
				$this->ownername = $optional_param('ownername', '', PARAM_TEXT);
				$this->UUID		 = $optional_param('UUID', '', PARAM_TEXT);
			}
			if ($this->ownername=="") $this->ownername = get_display_username($USER->firstname, $USER->lastname);



			







			if (!$this->hasError) {
				$this->created_avatar = $this->createAvatar();
			}
		}
		else {
			$this->hmregion = $CFG->mdlopnsm_home_region;
			$this->UUID	  	= $this->nx_UUID;
			$this->ownername = get_display_username($USER->firstname, $USER->lastname);
		}
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name = $CFG->mdlopnsm_grid_name;
		$grid_name = $context->mModuleConfig['grid_name'];

		$render->setAttribute('grid_name',		$grid_name);
		$render->setAttribute('action_url', 	$this->action_url);
		$render->setAttribute('hasPermit',		$this->hasPermit);

		$render->setAttribute('actvLastName', 	$this->actvLastName);
		$render->setAttribute('lastNames',  	$this->mActionForm->lastNames);
		$render->setAttribute('regionNames', 	$this->regionNames);
		$render->setAttribute('created_avatar',	$this->created_avatar);
		$render->setAttribute('actionForm', 	$this->mActionForm);

		$render->setAttribute('firstname', 		$this->firstname);
		$render->setAttribute('lastname', 		$this->lastname);
		$render->setAttribute('passwd', 		$this->passwd);
		$render->setAttribute('hmregion', 		$this->hmregion);
		$render->setAttribute('ownername',		$this->ownername);
		$render->setAttribute('nx_UUID',	  	$this->nx_UUID);
		$render->setAttribute('UUID', 		  	$this->UUID);

		$render->setAttribute('isDisclaimer',	$context->mModuleConfig['activate_disclaimer']);
		$render->setAttribute('disclaimer',		$context->mModuleConfig['disclaimer_content']);

		// 
		$render->setAttribute('pv_ownername', $this->ownername);
		if ($this->created_avatar) {
			$render->setAttribute('pv_firstname', "");
			$render->setAttribute('pv_lastname',  "");
		}
		else {
			$render->setAttribute('pv_firstname', $this->firstname);
			$render->setAttribute('pv_lastname',  $this->lastname);
		}

		include(CMS_MODULE_PATH."/html/create.html");
	}



	function createAvatar()
	{
		$context = & XCube_Root::getSingleton()->mContext;

		// User Check
		$criteria  = & new CriteriaCompo();
		$criteria->add(new Criteria('firstname', $this->firstname));
		$criteria->add(new Criteria('lastname',  $this->lastname));

		$modobj = & $this->handler->getObjects($criteria);
		if ($modobj!=null) {
			$this->mActionForm->addErrorMessage(_MD_XPNSM_ALREADY_NAME_ERROR);
			return false;
		}

		// Create UUID
		if (!$this->hasPermit or !isGUID($this->UUID)) {
			do {
				$uuid = make_random_guid();
				$modobj = $this->handler->get($uuid);
			} while ($modobj!=null);
			$this->UUID = $uuid;
		}

		// OpenSim DB
		$rslt = opensim_create_avatar($this->UUID, $this->firstname, $this->lastname, $this->passwd, $this->hmregion);
		if (!$rslt) {
			$this->mActionForm->addErrorMessage(_MD_XPNSM_OPNSM_CREATE_ERROR);
			return false;
		}

		// Moodle DB
		if ($this->hasPermit) $this->uid = get_userid_by_name($this->ownername);
		else 				  $this->uid = $this->userid;

		// Xoopensim DB
		$new_user['UUID']	  = $this->UUID;
		$new_user['uid']	   = $this->uid;
		$new_user['firstname'] = $this->firstname;
		$new_user['lastname']  = $this->lastname;
		$new_user['hmregion']  = $this->hmregion;
		$new_user['state']	 = "";

		//$ret = xoopensim_insrt_usertable($modHandler, $new_user);
		$ret = xoopensim_insert_usertable($new_user);
		if (!$ret) $this->mActionForm->addErrorMessage(_MD_XPNSM_XOOPS_CREATE_ERROR);

		return $ret;
	}

}

?>
