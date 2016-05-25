<?php
///////////////////////////////////////////////////////////////////////////////
//	currency.class.php
//
//                                   			by Fumi.Iseki
//

if (!defined('CMS_MODULE_PATH')) exit();
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');


class  CurrencyManage
{
	var $action_url;
    var $url_param;
	var $course_id  = 0;
	var $hasPermit  = false;

	var $errAvatars = array();
	var $errNum     = 0;
	var $transfered = true;

	var	$hasError   = false;
	var	$errorMsg   = array();



	function  CurrencyManage($course_id) 
	{
		$this->course_id = $course_id;
		$this->hasPermit = hasModlosPermit($course_id);
		if (!$this->hasPermit) {
			$this->hasError = true;
			$this->errorMsg[] = get_string('modlos_access_forbidden', 'block_modlos');
			return;
		}
	
		$this->url_param  = '?course='.$this->course_id;
		$this->action_url = CMS_MODULE_URL.'/admin/actions/currency.php';
	}


	function  execute()
	{
		global $CFG;
		if (!$this->hasPermit) return false;

		$this->errNum = 0;
		$this->transfered = false;

		if (data_submitted()) {		// POST
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}

			$money = (int)optional_param('send_money', '0', PARAM_INT);
			if ($money>0) {
				$regionserver = $CFG->modlos_currency_regionserver;
        		if ($regionserver=='http://123.456.78.90:9000/' or $regionserver=='') $regionserver = null;
				//
				require_once(CMS_MODULE_PATH.'/helper/helpers.php');
				$avatars = opensim_get_userinfos();
				foreach ($avatars as $avatar) {
					$ret = send_money($avatar['UUID'], $money, $regionserver);
					if (!$ret) {
						$this->errAvatars[$this->errNum] = $avatar;
						$this->errAvatars[$this->errNum]['fullname'] = $avatar['firstname'].' '.$avatar['lastname'];
						$this->errNum++;
					}
				}
				$this->transfered = true;
			}
		}

		return true;
	}


	function  print_page() 
	{
		global $CFG;

		$grid_name  = $CFG->modlos_grid_name;
		$errNum     = $this->errNum;
		$errAvatars = $this->errAvatars;
		$transfered = $this->transfered;

		$url_param  = $this->url_param;
		$action_url = $this->action_url;

		$currency_ttl  	 = get_string('modlos_currency_ttl',    	'block_modlos');
		$currency_send 	 = get_string('modlos_currency_send',   	'block_modlos');
		$currency_return = get_string('modlos_currency_return', 	'block_modlos');
		$currency_trans  = get_string('modlos_currency_transfered',	'block_modlos');
		$currency_mis    = get_string('modlos_currency_mistrans', 	'block_modlos');

		$content = '<center>'.get_string('modlos_currency_contents', 'block_modlos').'</center>';

		include(CMS_MODULE_PATH.'/admin/html/currency.html');
	}
}
