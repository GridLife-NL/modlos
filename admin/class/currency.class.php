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

	var $send_money = 0;
	var	$date_format= 'd/m/Y';
	var $date_time  = '01/01/1970';
	var $unix_time  = 0;
	var $since      = '...';

	var $transfer   = false;
	var $remake     = false;
	var $display    = false;

	var	$hasError   = false;
	var	$errorMsg   = array();
	var	$results    = array();



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
		$this->unix_time = strtotime($this->date_time);
		$this->date_time = date($this->date_format, $this->unix_time);

		if ($formdata = data_submitted()) {	// POST
			if (!confirm_sesskey()) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
				return false;
			}
			$this->getPage = false;

			// Send Money
			if (isset($formdata->send_money))
			{
				$this->send_money = (int)optional_param('send_money', '0', PARAM_INT);
				if ($this->send_money>0) {
					$regionserver = $CFG->modlos_currency_regionserver;
        			if ($regionserver=='http://123.456.78.90:9000/' or $regionserver=='') $regionserver = null;
					//
					$num = 0;
					require_once(CMS_MODULE_PATH.'/helper/helpers.php');
					$avatars = opensim_get_userinfos();
					foreach ($avatars as $avatar) {
						$ret = send_money($avatar['UUID'], $this->send_money, $regionserver);
						if (!$ret) {
							$this->results[$num] = $avatar;
							$this->results[$num]['fullname'] = $avatar['avatar'];
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
				$since = strtotime($sales_limit);
				//
				$ret = opensim_regenerate_totalsales($since);
				$this->unix_time = $since;
				$this->date_time = date($this->date_format, $since);

				if (!$ret) $this->hasError = true;
				$this->remake = true;
			}

			// Display Total Sales DB
			else if (isset($formdata->sales_condition))	
			{
				$sales_cndtn = optional_param('sales_condition', '', PARAM_TEXT);
				$sales_order = optional_param('sales_order',     '', PARAM_TEXT);
				$sales_cndtn = preg_replace('/[\'";#&\$\\\\]/', '', $sales_cndtn);
				$sales_order = preg_replace('/[\'";#&\$\\\\]/', '', $sales_order);

				$sales = opensim_get_totalsales($sales_cndtn, $sales_order);
				if ($sales==null) {
					$this->hasError = true;
				}
				else {
					$this->since = date($this->date_format, $sales[0]['time']);
					$num = 0;
					foreach($sales as $sale) {
						$this->results[$num] = $sale;
						$this->results[$num]['num'] = $num;
						$num++;
					}
				}
				$this->display = true;
			}
		}

		return true;
	}


	function  print_page() 
	{
		global $CFG;

		$grid_name   = $CFG->modlos_grid_name;

		$transfer    = $this->transfer;
		$remake      = $this->remake;
		$display     = $this->display;

		$results     = $this->results;

		$date_time   = $this->date_time;
		$date_format = $this->date_format;

		$getPage	 = $this->getPage;
		$url_param   = $this->url_param;
		$action_url  = $this->action_url;
		$send_money  = $CFG->modlos_currency_unit.' '.number_format($this->send_money);

		$currency_ttl  	 = get_string('modlos_currency_ttl',    	'block_modlos');
		$transfer_ttl  	 = get_string('modlos_currency_trans_ttl',  'block_modlos');
		$remake_ttl 	 = get_string('modlos_sales_remake_ttl',    'block_modlos');
		$display_ttl	 = get_string('modlos_sales_disp_ttl',      'block_modlos');
		$currency_return = get_string('modlos_currency_return', 	'block_modlos');

		$currency_send 	 = get_string('modlos_currency_send',   	'block_modlos');
		$currency_trans  = get_string('modlos_currency_transfered',	'block_modlos', $send_money);
		$currency_mis    = get_string('modlos_currency_mistrans', 	'block_modlos');

		$sales_limit 	 = get_string('modlos_sales_remake_limit', 	'block_modlos');
		$sales_remaked 	 = get_string('modlos_sales_remaked', 		'block_modlos', $this->date_time);
		$sales_remake_mis= get_string('modlos_sales_remake_mis', 	'block_modlos');

		$sales_condition = get_string('modlos_sales_disp_cndtn',	'block_modlos');
		$sales_order     = get_string('modlos_sales_disp_order',	'block_modlos');
		$sales_displayed = get_string('modlos_sales_displayed',		'block_modlos', $this->since);
		$sales_disp_mis  = get_string('modlos_sales_disp_mis',		'block_modlos');

		$result_ttl = '';
		if ($transfer) {
			$result_ttl = $transfer_ttl;
			$result_msg = $currency_trans;
		}
		else if ($remake) {
			$result_ttl = $remake_ttl;
			$result_msg = $sales_remaked;
		}
		else if ($display) {
			$result_ttl = $display_ttl;
			$result_msg = $sales_displayed;
		}

		include(CMS_MODULE_PATH.'/admin/html/currency.html');
	}
}
