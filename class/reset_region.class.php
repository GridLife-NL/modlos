<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');




class  ResetRegion
{
	var $hasPermit	= false;
	var $isGuest	= true;
	var $course_id	= 0;

	var $action_url = '';
	var $reset_url  = '';
	var $retun_url  = '';
	var $action 	= 'all';	// 'all', 'personal' 
	var $userid 	= 0;		// 0: my, >1: other

	var $use_sloodle = false;
	var $avatars_num = false;
	var $max_avatars = false;
	var $isAvatarMax = false;

	var $reseted    = false;
	var $hasError	= false;
	var $errorMsg 	= array();

	// Moodle DB
	var $UUID		= '';
	var $uid 	   	= 0;
	var $regionName = '';
	var $serverName = '';
	var $ownerName  = '';
	var $locX  		= 0;
	var $locY	 	= 0;
	var $sizeX  	= 0;
	var $sizeY	 	= 0;
	var $serverURI	= '';
	var $serverPort	= '';



	function  ResetRegion($course_id) 
	{
		global $CFG, $USER;

		// for Guest
		$this->isGuest = isguestuser();
		if ($this->isGuest) {
			print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
		}
		$this->hasPermit = hasModlosPermit($course_id);

		// for HTTPS
		$use_https = $CFG->modlos_use_https;
		if ($use_https) {
			$https_url = $CFG->modlos_https_url;
			if ($https_url!='') $module_url = $https_url.'/'.CMS_DIR_NAME;
			else 				$module_url = preg_replace('/^http:/', 'https:', CMS_MODULE_URL);
		}
		else $module_url = CMS_MODULE_URL;

		// Parameters
		$uuid   = required_param('region', PARAM_TEXT);
		$action = optional_param('action', 'all', PARAM_ALPHA);
		$userid = optional_param('userid', '0', PARAM_INT);

		$course_param = '?course='.$course_id;
		$option_param = '&amp;action='.$action.'&amp;userid='.$userid;

		$this->course_id   = $course_id;
		$this->action_url  = $module_url.'/actions/reset_region.php'.$course_param.$option_param;
		$this->reset_url   = $module_url.'/actions/reset_region.php'.$course_param.$option_param;
		$this->return_url  = $module_url.'/actions/regions_list.php'.$course_param.$option_param;
		$this->use_sloodle = $CFG->modlos_cooperate_sloodle;

		// get UUID from POST or GET
		if (!isGUID($uuid)) {
			$mesg = ' '.get_string('modlos_invalid_uuid', 'block_modlos')." ($uuid)";
			print_error($mesg, '', $return_url);
		}

		// get uid from Modlos and Sloodle DB
		$region = opensim_get_region_info($uuid);
		$avatar = modlos_get_avatar_info($region['owner_uuid'], $this->use_sloodle);

		$this->UUID 	  = $uuid;
		$this->uid	  	  = $avatar['uid'];
		$this->regionName = $region['regionName'];
		$this->serverName = $region['serverName'];
		$this->ownerName  = $region['fullname'];
		$this->locX       = (int)$region['locX']/256;
		$this->locY       = (int)$region['locY']/256;
		$this->sizeX      = (int)$region['sizeX'];
		$this->sizeY      = (int)$region['sizeY'];
		$this->serverURI  = $region['serverURI'];
		$this->serverPort = $region['serverHttpPort'];

		if (!$this->hasPermit and $USER->id!=$this->uid) {
			print_error('modlos_access_forbidden', 'block_modlos', $this->return_url);
		}
	}



	function  execute()
	{
		global $USER;

		//
		if (!$this->hasPermit and $USER->id!=$this->uid) {
			print_error('modlos_access_forbidden', 'block_modlos', $return_url);
		}

		// Form
		if (data_submitted()) {
			if (!confirm_sesskey()) { 
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
			}

			// Reset Region
			$reset = optional_param('reset_region', '', PARAM_TEXT);
			if ($reset!='') {
				redirect($this->reset_url.'&amp;region='.$this->UUID, 'Please wait....', 0);
				exit('<h4>reset page open error!!</h4>');
			}

			// Sate (Active/Inactive)
			$state 	= optional_param('state', '', PARAM_INT);
			if ($state>0x80) $this->state = $this->ostate & $state;
			else 			 $this->state = $this->ostate | $state;

			// Sloodle
			$sloodle = optional_param('sloodle', '', PARAM_ALPHA);
			if ($sloodle!='') $this->state |= AVATAR_STATE_SLOODLE;
			else			  $this->state &= AVATAR_STATE_NOSLOODLE;

			//
			$this->hmregion = optional_param('hmregion', '', PARAM_TEXT);
			$this->hmregion = addslashes($this->hmregion);

			// password
			$confirm_pass = optional_param('confirm_pass','', PARAM_TEXT);
			$this->passwd = optional_param('passwd',   '', PARAM_TEXT);
			if ($this->passwd!=$confirm_pass) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_passwd_mismatch', 'block_modlos');
			}
			if ($this->passwd!='' and strlen($this->passwd)<AVATAR_PASSWD_MINLEN) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_passwd_minlength', 'block_modlos', AVATAR_PASSWD_MINLEN);
			}


			// Owner Name
			if ($this->hasPermit) {		// for admin
				$this->ownername = optional_param('ownername', '', PARAM_TEXT);
				$this->ownername = addslashes($this->ownername);
				if ($this->ownername!='') {
					//$names = get_names_from_display_username(stripslashes($this->ownername));
					//$user_info = get_userinfo_by_name($names['firstname'], $names['lastname']);				
					$user_info = get_userinfo_by_username(stripslashes($this->ownername));				
					if ($user_info!=null) {
						$this->uid = $user_info->id;
					}
					else {
						$this->hasError = true;
						$this->errorMsg[] = get_string('modlos_ownername', 'block_modlos').' ('.stripslashes($this->ownername).')';
					//	$this->errorMsg[] = get_string('modlos_nouser_found', 'block_modlos').' ('.$names['firstname'].' '.$names['lastname'].')';
					//	$this->ownername  = get_display_username($USER->firstname, $USER->lastname);
						$this->uid = $USER->id;
					}
				}
				else {
					$this->uid = '0';
				}
			}
			else {	// user
				$nomanage = optional_param('nomanage', '', PARAM_ALPHA);
				if ($nomanage=='') {
					$this->ownername = $USER->username;	//get_display_username($USER->firstname, $USER->lastname);
					$this->uid = $USER->id;
				}
				else {
					$this->ownername = '';
					$this->uid = '0';
				}
			}


			// Home Region
 			$region_uuid = opensim_get_region_uuid($this->hmregion);
			if ($region_uuid==null) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_invalid_regionname', 'block_modlos')." ($this->hmregion)";
			}

			if ($this->hasError) return false;


			//////////
			$this->updated_avatar = $this->updateAvatar();
			if (!$this->updated_avatar) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_update_error', 'block_modlos');
				return false;
			}
		}

		// GET
		else {

		}

		return true;
	}



	function  print_page() 
	{
		global $CFG;

		$grid_name = $CFG->modlos_grid_name;

		$region_reset_ttl	= get_string('modlos_region_reset', 'block_modlos');
		$region_name_ttl	= get_string('modlos_region',	 	'block_modlos');
		$server_ttl			= get_string('modlos_server',       'block_modlos');
		$coordinates_ttl	= get_string('modlos_coordinates',  'block_modlos');
		$region_size_ttl	= get_string('modlos_region_size',  'block_modlos');
		$admin_user_ttl		= get_string('modlos_admin_user',   'block_modlos');
		$region_owner_ttl	= get_string('modlos_region_owner', 'block_modlos');
		$region_reseted 	= 'sxxx';
		$region_reseted_exp = 'explain';

		//
		$server = '';
		if ($this->serverURI!='') {
    		$dec = explode(':', $this->serverURI);
    		if (!strncasecmp($dec[0], 'http', 4)) $server = "$dec[0]:$dec[1]";
		}  
		if ($server=='') {
    		$server = "http://$serverName";
		}	
		$server = $server.':'.$this->serverPort;
		$guid = str_replace('-', '', $this->UUID);

		include(CMS_MODULE_PATH.'/html/reset_region.html');
	}



	function updateAvatar()
	{
		// Update password of OpenSim DB
		if ($this->passwd!='') {
			$passwdsalt = make_random_hash();
			$passwdhash = md5(md5($this->passwd).':'.$passwdsalt);

			$ret = opensim_set_password($this->UUID, $passwdhash, $passwdsalt);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_passwd_update_error', 'block_modlos');
				return false;
			}
		}

		// update Home Region
		if ($this->hmregion!='') {
			$ret = opensim_set_home_region($this->UUID, $this->hmregion);
			if (!$ret) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_hmrgn_update_error', 'block_modlos');
				return false;
			}
		}

		// State
		if ($this->state!=$this->ostate) {
			// Avtive -> InAcvtive
			if (!($this->ostate&AVATAR_STATE_INACTIVE) and $this->state&AVATAR_STATE_INACTIVE) {
				$ret = modlos_inactivate_avatar($this->UUID);
				if (!$ret) {
					$this->state &= AVATAR_STATE_ACTIVE;
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_inactivate_error', 'block_modlos');
					return false;
				}
			}
			// InActive -> Acvtive
			elseif ($this->ostate&AVATAR_STATE_INACTIVE and !($this->state&AVATAR_STATE_INACTIVE)) {
				$ret = modlos_activate_avatar($this->UUID);
				if (!$ret) {
					$this->state |= AVATAR_STATE_INACTIVE;
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_activate_error', 'block_modlos');
					return false;
				}
			}
		}


		// Modlos and Sloodle DB
		$update_user['id']	  	  = $this->avatar['id'];
		$update_user['UUID']	  = $this->UUID;
		$update_user['uid']		  = $this->uid;
		$update_user['firstname'] = $this->firstname;
		$update_user['lastname']  = $this->lastname;
		$update_user['hmregion']  = $this->hmregion;
		$update_user['state']	  = $this->state;
		$update_user['time']	  = time();

		$ret = modlos_set_avatar_info($update_user, $this->use_sloodle);
		return $ret;
	}
}
