<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/moodle.func.php');
require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  CurrencyLog 
{
	var $db_data 	 = array();
	var $trans_types = array();
	var $icon 		 = array();
	var $pnum 		 = array();

	var $course_id   = 0;
	var $agent_id	 = '';
	var $user_id	 = 0;
	var $action_url;
	var $owner_url;
	var $url_param   = '';

	var $use_sloodle = false;
	var $isAvatarMax = false;

	var $hasPermit 	 = false;
	var $isGuest   	 = true;

	// Page Control
	var $Cpstart 	 = 0;
	var $Cplimit 	 = 25;
	var $pstart;
	var $plimit;
	var $number;
	var $sitemax;
	var $sitestart;

	var $order		 = '';
	var $order_desc	 = 0;
	var $desc_next   = 0;
 
	// SQL
	var $sql_order   = '';
	var $sql_limit   = '';



	function  CurrencyLog($course_id, $agent_id)
	{
		global $CFG, $USER;

		// for Guest
		$this->isGuest = isguestuser();
		if ($this->isGuest or !$CFG->modlos_use_currency_server) {
			print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
		}

		$this->hasPermit   = hasModlosPermit($course_id);
		$this->use_sloodle = $CFG->modlos_cooperate_sloodle;
		$this->course_id   = $course_id;
		$this->agent_id	   = $agent_id;

		$this->url_param  = '?agent='.$agent_id.'&amp;course='.$course_id;
		$this->action_url = CMS_MODULE_URL.'/actions/currency_log.php'. $this->url_param;
		$this->owner_url  = $CFG->wwwroot.'/user/view.php'.$this->url_param;

		$my_avatars  = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $my_avatars>=$max_avatars) $this->isAvatarMax = true;

		$this->trans_types['1002']  = 'GroupCreate';
		$this->trans_types['1004']  = 'GroupJoin';
		$this->trans_types['1101']  = 'UploadCharge';
		$this->trans_types['1102']  = 'LandAuction';
		$this->trans_types['1103']  = 'ClassifiedCharge';
		$this->trans_types['2003']  = 'ParcelDirFee';
		$this->trans_types['2005']  = 'ClassifiedRenew';
		$this->trans_types['2900']  = 'ScheduledFee';
		$this->trans_types['3000']  = 'GiveInventory';
		$this->trans_types['5000']  = 'ObjectSale';
		$this->trans_types['5001']  = 'Gift';
		$this->trans_types['5002']  = 'LandSale';
		$this->trans_types['5003']  = 'ReferBonus';
		$this->trans_types['5004']  = 'InvntorySale';
		$this->trans_types['5005']  = 'RefundPurchase';  
		$this->trans_types['5006']  = 'LandPassSale';
		$this->trans_types['5007']  = 'DwellBonus';
		$this->trans_types['5008']  = 'PayObject';
		$this->trans_types['5009']  = 'ObjectPays';      
		$this->trans_types['5010']  = 'BuyMoney';
		$this->trans_types['5011']  = 'MoveMoney';
		$this->trans_types['6003']  = 'GroupLiability';
		$this->trans_types['6004']  = 'GroupDividend';
		$this->trans_types['10000'] = 'StipendBasic';
	}


	// 検索条件
	function  set_condition() 
	{
		global $CFG, $USER;

		$this->order = optional_param('order', 'time', PARAM_TEXT);
		$this->order_desc = optional_param('desc', '1', PARAM_INT);
		if (!isAlphabetNumeric($this->order)) $this->order = '';

		// Post Check
		if (data_submitted()) {
			if (!confirm_sesskey()) {
				print_error('modlos_sesskey_error', 'block_modlos', $this->action_url);
			}
		}

		// ORDER
		$sql_order = '';
		if ($this->order=='time') {
			$sql_order = 'ORDER BY time';
			if (!$this->order_desc) $this->desc_next = 1;
		}
		//
		if ($sql_order!='') {
			if ($this->order_desc) {
				$sql_order .= ' DESC';
			}
			else {
				$sql_order .= ' ASC';
			}
		}

		// pstart & plimit
		$this->pstart = optional_param('pstart', "$this->Cpstart", PARAM_INT);
		$this->plimit = optional_param('plimit', "$this->Cplimit", PARAM_INT);
		$this->sql_limit = " LIMIT $this->pstart, $this->plimit ";

		// SQL Condition
		$this->sql_order = ' '.$sql_order;

		return true;
	}


	function  execute()
	{
		global $CFG, $USER;

		// auto synchro
		modlos_sync_opensimdb();
		if ($this->use_sloodle) modlos_sync_sloodle_users();

		//
		$this->user_id = 0;
		$avatardata = modlos_get_avatar_info($this->agent_id, $this->use_sloodle);
		if ($avatardata!=null) $this->user_id = $avatardata['uid'];

		if (!$this->hasPermit) {
			if ($USER->id==$this->user_id) $this->hasPermit = true;
		}
		if (!$this->hasPermit) {
			print_error('modlos_access_forbidden', 'block_modlos', CMS_MODULE_URL);
		}

		////////////////////////////////////////////////////////////////////
		//
		$this->number = opensim_get_currency_amounts_num($this->agent_id);
		//
		$logs = opensim_get_currency_amounts_log($this->agent_id, $this->sql_order.$this->sql_limit);

		$colum = 0;
		foreach ($logs as $log) {
			$this->db_data[$colum] = $log;
			$this->db_data[$colum]['num']  = $colum;
			$this->db_data[$colum]['uuid'] = $this->agent_id;
			$this->db_data[$colum]['date'] = date(DATE_FORMAT, $log['time']);

			$this->db_data[$colum]['trans_type'] = ' - ';
			if (array_key_exists($log['type'], $this->trans_types)) {
				$this->db_data[$colum]['trans_type'] = $this->trans_types[$log['type']];
			}
	
			if ($this->agent_id==$log['sender']) {
				$this->db_data[$colum]['Iama']    = 'Sender';
				$this->db_data[$colum]['pay']     = number_format($log['amount']);
				$this->db_data[$colum]['income']  = $this->db_data[$colum]['trans_type'];
				$this->db_data[$colum]['balance'] = number_format($log['senderBalance']);
				$this->db_data[$colum]['oppuuid'] = $log['receiver'];
			}
			else {
				$this->db_data[$colum]['Iama']    = 'Receiver';
				$this->db_data[$colum]['pay']     = $this->db_data[$colum]['trans_type'];
				$this->db_data[$colum]['income']  = number_format($log['amount']);
				$this->db_data[$colum]['balance'] = number_format($log['receiverBalance']);
				$this->db_data[$colum]['oppuuid'] = $log['sender'];
			}

			if ($this->db_data[$colum]['balance']==-1) {
				$this->db_data[$colum]['balance'] = ' - ';
			}

			if (!$this->db_data[$colum]['objectName']) $this->db_data[$colum]['objectName'] = ' - ';

			$oppname = opensim_get_avatar_name($this->db_data[$colum]['oppuuid']);
			$this->db_data[$colum]['opponent'] = $oppname['fullname'];

			$colum++;
		}

		////////////////////////////////////////////////////////////////////
		// Paging
		$this->sitemax   = ceil ($this->number/$this->plimit);
		$this->sitestart = floor(($this->pstart+$this->plimit-1)/$this->plimit) + 1;
		if ($this->sitemax==0) $this->sitemax = 1;

		// back more and back one
		if ($this->pstart==0) {
			$this->icon[0] = 'off';
			$this->pnum[0] = 0;
		}
		else {
			$this->icon[0] = 'on';
			$this->pnum[0] = $this->pstart - $this->plimit;
			if ($this->pnum[0]<0) $this->pnum[0] = 0;
		}

		// forward one
		if ($this->number <= ($this->pstart + $this->plimit)) {
			$this->icon[1] = 'off'; 
			$this->pnum[1] = 0; 
		}
		else {
			$this->icon[1] = 'on'; 
			$this->pnum[1] = $this->pstart + $this->plimit;
		}

		// forward more
		if (($this->number-$this->plimit) < 0) {
			$this->icon[2] = 'off';
			$this->pnum[2] = 0;
		}
		else {
			$this->icon[2] = 'on';
			$this->pnum[2] = $this->number - $this->plimit;
		}

		$this->icon[3] = $this->icon[4] = $this->icon[5] = $this->icon[6] = 'icon_limit_off';
		if ($this->plimit != 10)  $this->icon[3] = 'icon_limit_10_on'; 
		if ($this->plimit != 25)  $this->icon[4] = 'icon_limit_25_on';
		if ($this->plimit != 50)  $this->icon[5] = 'icon_limit_50_on';
		if ($this->plimit != 100) $this->icon[6] = 'icon_limit_100_on';

		return true;
	}


	function  print_page() 
	{
		global $CFG, $USER;

		$grid_name 		= $CFG->modlos_grid_name;
		$money_unit 	= $CFG->modlos_currency_unit;
		$date_format	= DATE_FORMAT;

		$has_permit		= $this->hasPermit;
		$url_param		= $this->url_param;
		$action_url		= $this->action_url;
		$desc_amp		= "&amp;desc=$this->desc_next";

		$plimit_amp		= "&amp;plimit=$this->plimit";
		$pstart_amp		= "&amp;pstart=$this->pstart";
		$order_amp		= "&amp;order=$this->order&amp;desc=$this->order_desc";
		$plimit_		= '&amp;plimit=';
		$pstart_		= '&amp;pstart=';
		$order_			= '&amp;order=';

		$number_ttl		= get_string('modlos_num',			  'block_modlos');
		$currency_log	= get_string('modlos_my_currency',    'block_modlos');
		$currency_found	= get_string('modlos_currency_found', 'block_modlos');
		$currency_date	= get_string('modlos_currency_date',  'block_modlos');
		$currency_type	= get_string('modlos_currency_type',  'block_modlos');
		$currency_amount= get_string('modlos_currency_amount','block_modlos');
		$currency_object= get_string('modlos_currency_object','block_modlos');
		$currency_pay   = get_string('modlos_currency_pay',   'block_modlos');
		$currency_income= get_string('modlos_currency_income','block_modlos');
		$currency_opponent = get_string('modlos_currency_opponent','block_modlos');

		$page_num		= get_string('modlos_page',			  'block_modlos');
		$page_num_of	= get_string('modlos_page_of',		  'block_modlos');

/*
		$edit_ttl		= get_string('modlos_edit',			 'block_modlos');
		$show_ttl		= get_string('modlos_show',			 'block_modlos');
		$editable_ttl	= get_string('modlos_edit_ttl',		 'block_modlos');
		$lastlogin_ttl	= get_string('modlos_login_time',	 'block_modlos');
		$status_ttl		= get_string('modlos_status',		 'block_modlos');
		$crntregion_ttl	= get_string('modlos_crntregion',	 'block_modlos');
		$owner_ttl		= get_string('modlos_owner',		 'block_modlos');
		$get_owner_ttl	= get_string('modlos_get_owner_ttl', 'block_modlos');
		$firstname_ttl	= get_string('modlos_firstname', 	 'block_modlos');
		$lastname_ttl 	= get_string('modlos_lastname', 	 'block_modlos');
		$avatarname_ttl = get_string('modlos_avatar_name', 	 'block_modlos');
		$not_syncdb_ttl = get_string('modlos_not_syncdb',	 'block_modlos');
		$online_ttl	 	= get_string('modlos_online_ttl',	 'block_modlos');
		$active_ttl		= get_string('modlos_active',		 'block_modlos');
		$inactive_ttl	= get_string('modlos_inactive',		 'block_modlos');
		$reset_ttl		= get_string('modlos_reset_ttl',	 'block_modlos');
		$find_owner_ttl	= get_string('modlos_find_owner_ttl','block_modlos');
		$unknown_status	= get_string('modlos_unknown_status','block_modlos');
		$user_search	= get_string('modlos_avatar_search', 'block_modlos');
		$users_found  	= get_string('modlos_avatars_found', 'block_modlos');
		$sloodle_ttl  	= get_string('modlos_sloodle_short', 'block_modlos');
		$currency_ttl  	= get_string('modlos_currency_ttl',  'block_modlos');
*/

		if ($this->user_id!=$USER->id) {
			$userinfo = get_userinfo_by_id($this->user_id);
			$username = get_display_username($userinfo->firstname, $userinfo->lastname);
			$userurl  = '<a href="'.$this->owner_url.'&id='.$this->user_id.'" target="_blank">'.$username.'</a>';
			$currency_log = get_string('modlos_peraonal_currency', 'block_modlos', $userurl);
		}

		include(CMS_MODULE_PATH.'/html/currency_log.html');
	}
}
