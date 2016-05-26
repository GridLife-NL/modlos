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

	var $getPage    = true;
	var	$date_format= 'd/m/Y';
	var $date_time  = '01/01/1970';
	var $unix_time  = 0;

	var $transfer   = false;
	var $remake     = false;
	var $display    = false;

//	var $errAvatars = array();
//	var $errNum     = 0;

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
		if (!USE_CURRENCY_SERVER) return false;

		$this->errNum = 0;
		$this->transfered = false;
		//
		$this->date_format = get_string('modlos_date_dmY', 'block_modlos');
		$this->unix_time   = strtotime($this->date_time);
		$this->date_time   = date($this->date_format, $this->unix_time);

		if ($formdata = data_submitted()) {	// POST
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}
			$this->getPage = false;

			// Send Money
			if (isset($formdata->send_moneya))
			{
				$send_money = (int)optional_param('send_money', '0', PARAM_INT);
				if ($send_money>0) {
					$regionserver = $CFG->modlos_currency_regionserver;
        			if ($regionserver=='http://123.456.78.90:9000/' or $regionserver=='') $regionserver = null;
					//
					$num = 0;
					require_once(CMS_MODULE_PATH.'/helper/helpers.php');
					$avatars = opensim_get_userinfos();
					foreach ($avatars as $avatar) {
						$ret = send_money($avatar['UUID'], $send_money, $regionserver);
						if (!$ret) {
							$this->errorMsg[$num] = $avatar;
							$this->errorMsg[$num]['fullname'] = $avatar['firstname'].' '.$avatar['lastname'];
							$num++;
						}
					}
					if ($num>0) $this->hasError = true;
					$this->transfer = true;
				}
			}

			// Remake Total Sales DB
			else if (isset($formdata->sales_limit))	
			{
				$sales_limit = optional_param('sales_limit', $this->date_time, PARAM_TEXT);
				$this->unix_time = strtotime($sales_limit);
				$this->date_time = date($this->date_format, $this->unix_time);

				$ret = opensim_regenerate_totalsales($this->unix_time);
				if (!$ret) $this->hasError = true;
				$this->remake = true;
			}

			// Show Total Sales DB
			else if (isset($formdata->sales_condition))	
			{
				$sales_cndtn = optional_param('sales_condition', '', PARAM_TEXT);
				$sales_order = optional_param('sales_order',     '', PARAM_TEXT);

				$sales_cndtn = preg_replace('/[\'";#&\$\\]/', '', $sales_cndtn);
				$sales_order = preg_replace('/[\'";#&\$\\]/', '', $sales_order);
				$this->display = true;
			}
		}

		return true;
	}


	function  print_page() 
	{
		global $CFG;

		$grid_name  = $CFG->modlos_grid_name;

//		$errNum      = $this->errNum;
//		$errAvatars  = $this->errAvatars;
		$transfer    = $this->transfer;
		$remake      = $this->remake;
		$display     = $this->display;

		$getPage	 = $this->getPage;
		$date_time   = $this->date_time;
		$date_format = $this->date_format;

		$url_param   = $this->url_param;
		$action_url  = $this->action_url;

		$currency_ttl  	 = get_string('modlos_currency_ttl',    	'block_modlos');
		$currency_send 	 = get_string('modlos_currency_send',   	'block_modlos');
		$currency_return = get_string('modlos_currency_return', 	'block_modlos');
		$currency_trans  = get_string('modlos_currency_transfered',	'block_modlos');
		$currency_mis    = get_string('modlos_currency_mistrans', 	'block_modlos');

		$sales_limit 	 = get_string('modlos_sales_remake_limit', 	'block_modlos');
		$sales_remaked 	 = get_string('modlos_sales_remaked', 		'block_modlos');
		$sales_condition = get_string('modlos_sales_show_cndtn',	'block_modlos');
		$sales_order     = get_string('modlos_sales_show_order',	'block_modlos');
		$sales_results   = get_string('modlos_sales_show_results',	'block_modlos');

		$content_trans   = get_string('modlos_currency_trans_ttl',  'block_modlos');
		$content_remake  = get_string('modlos_sales_remake_ttl',    'block_modlos');
		$content_display = get_string('modlos_sales_show_ttl',      'block_modlos');


		include(CMS_MODULE_PATH.'/admin/html/currency.html');
	}
}
