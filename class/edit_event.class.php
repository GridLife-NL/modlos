<?php

if (!defined('CMS_MODULE_PATH')) exit();

require_once(CMS_MODULE_PATH.'/include/modlos.func.php');



class  EditEvent
{
	var $hasPermit = false;
	var $isGuest   = true;
	var $pg_only   = true;
	var $userid	   = 0;			// owner id of this process
	var $uid	   = 0;			// first creator of this event
	var $date_frmt;
	var $use_utc_time;

	var $url_param = '';
	var $hasError  = false;
	var $errorMsg  = array();

	var $parcels  = array();
	var $owners   = array();
	var $creators = array();

	var $action_url;
	var $delete_url;

	var $course_id	  = '';
	var $isAvatarMax  = false;

	var $event_id	  = 0;
	var $global_pos   = '';
	var $region_uuid  = '';
	var $event_name   = '';
	var $category	  = 0;
	var $event_desc   = '';
	var $duration	  = 0;
	var $cover_charge = 0;
	var $cover_amount = 0;
	var $check_mature = 0;
	var $event_owner  = '';
	var $event_creator= '';
	var $owner_uuid   = '';
	var $creator_uuid = '';
	var $event_saved  = false;

	var $event_day;
	var $event_month;
	var $event_year;
	var $event_hour;
	var $event_minute;

	var $saved_event_name   = '';
	var $saved_global_pos   = '';
	var $saved_region_name  = '';
	var $saved_category	 	= 0;
	var $saved_cover_amount = 0;
	var $saved_event_type   = '';
	var $saved_event_date   = '';
	var $saved_event_owner  = '';
	var $saved_event_creator= '';



	function  EditEvent($course_id)
	{
		global $CFG, $USER;

		require_login($course_id);

		// for Guest
		$this->isGuest = isguest();
		if ($this->isGuest) {
			error(get_string('modlos_access_forbidden', 'block_modlos'), CMS_MODULE_URL);
		}

		$this->hasPermit = hasModlosPermit($course_id);
		$this->course_id = $course_id;
		$this->userid	 = $USER->id;
		$this->date_frmt = $CFG->modlos_date_format;
		$this->pg_only   = $CFG->modlos_pg_only;

		// GET eventid
		$this->event_id  = optional_param('eventid', '0', PARAM_INT);

		$this->use_utc_time = $CFG->modlos_use_utc_time;
		//if ($this->use_utc_time) date_default_timezone_set('UTC');
   
		$this->url_param = '?dmmy_param=';
		if ($course_id>0) $this->url_param .= '&amp;course='.$course_id;

		$this->action_url = CMS_MODULE_URL.'/actions/edit_event.php'.  $this->url_param;
		$this->delete_url = CMS_MODULE_URL.'/actions/delete_event.php'.$this->url_param.'&amp;eventid=';

		$avatars_num = modlos_get_avatars_num($USER->id);
		$max_avatars = $CFG->modlos_max_own_avatars;
		if (!$this->hasPermit and $max_avatars>=0 and $avatars_num>=$max_avatars) $this->isAvatarMax = true;

		if ($course_id>0) $this->course_patam = '?course='.$course_id;
	}




	function  execute()
	{
		// List of Parcels
		$modobj = get_records("modlos_search_parcels");
		$i = 0;		 
		foreach ($modobj as $mod) {
			//$this->parcels[$i]['uuid'] 		= $mod->parceluuid;
			$this->parcels[$i]['name'] 			= $mod->parcelname;
			$this->parcels[$i]['regionUUID'] 	= $mod->regionuuid;
			$this->parcels[$i]['landingpoint'] 	= $mod->landingpoint;
			$i++;
		}

		// List of Owners
		$this->owners = modlos_get_avatars();

		// List of Creators
		$this->creators = modlos_get_avatars($this->userid);
		if ($this->creators==null) {
			error(get_string('modlos_should_have_avatar', 'block_modlos'), CMS_MODULE_URL.'/actions/events_list.php');
		}
		foreach ($this->creators as $creator) {
			$this->event_owner = $creator['fullname'];
			break;
		}

		$event = array();


		// Post
		if (data_submitted()) {
			if (!confirm_sesskey()) { 
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_sesskey_error', 'block_modlos');
			}

			$this->uid 		= optional_param('uid', '0',  PARAM_INT);
			$this->event_id = optional_param('event_id', '0',  PARAM_INT);

			// Delete Event
			$del = optional_param('submit_delete', '', PARAM_TEXT);
			if ($del!='') {
				redirect($this->delete_url.$this->event_id, 'Please wait....', 0);
				exit('<h4>delete page open error!!</h4>');
			}
			
	
			$parcel = explode('|', optional_param('parcel_name', '|', PARAM_TEXT));
			$this->global_pos  = $parcel[0];
			$this->region_uuid = $parcel[1];

			$owner = explode('|', optional_param('owner_name', '|', PARAM_TEXT));
			$this->owner_uuid  = $owner[0];
			$this->event_owner = $owner[1];

			$creator = explode('|', optional_param('creator_name', '|', PARAM_TEXT));
			$this->creator_uuid  = $creator[0];
			$this->event_creator = $creator[1];

			$this->event_year  	= optional_param('event_year','2010', PARAM_INT);
			$this->event_month 	= optional_param('event_month', '1',  PARAM_INT);
			$this->event_day   	= optional_param('event_day', 	'1',  PARAM_INT);
			$this->event_hour 	= optional_param('event_hour', 	'0',  PARAM_INT);
			$this->event_minute	= optional_param('event_minute','0',  PARAM_INT);

			$this->event_name	= optional_param('event_name',	 '',  PARAM_TEXT);
			$this->event_desc	= optional_param('event_desc',	 '',  PARAM_TEXT);
			$this->category		= optional_param('category', 	 '0', PARAM_INT);

			$this->duration		= optional_param('duration', 	'10', PARAM_INT);
			$this->cover_charge = optional_param('cover_charge', '0', PARAM_INT);
			$this->cover_amount = optional_param('cover_amount', '0', PARAM_INT);
			$this->check_mature = optional_param('check_mature', '0', PARAM_INT);
			  
			if ($this->cover_charge==0) $this->cover_amount = 0;
			if (!isGUID($this->region_uuid)) $this->rgion_uuid = '00000000-0000-0000-0000-000000000000';


			// Error check
			if (!isGUID($this->creator_uuid)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_event_creator_required', 'block_modlos')." (UUID)";
			}
 			if (!isAlphabetNumericSpecial($this->event_creator)) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_event_creator_required', 'block_modlos')." (Name)";
			}

			if (!isGUID($this->owner_uuid)) {
				//$this->hasError = true;
				//$this->errorMsg[] = get_string('modlos_event_owner_required', 'block_modlos')." (UUID)";
				$this->owner_uuid = $this->creator_uuid;
			}
 			if (!isAlphabetNumericSpecial($this->event_owner)) {
				//$this->hasError = true;
				//$this->errorMsg[] = get_string('modlos_event_owner_required', 'block_modlos')." (Name)";
				$this->event_owner = $this->event_creator;
			}

			if ($this->event_name=='') {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_event_name_required', 'block_modlos');
			}
			if ($this->event_desc=='') {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_event_desc_required', 'block_modlos');
			}

			if ($this->pg_only and $this->check_mature==1) {
				$this->hasError = true;
				$this->errorMsg[] = get_string('modlos_pg_only_error', 'block_modlos');
			}

			$event_date = mktime($this->event_hour, $this->event_minute, 0, $this->event_month, $this->event_day, $this->event_year);
			if ($event_date<time()) {
				$this->hasError = true;
				$ftr = date($this->date_frmt, $event_date);
				$this->errorMsg[] = get_string('modlos_invalid_date_error', 'block_modlos')." ($ftr < ".get_string('modlos_time_now', 'block_modlos').')';
			}


			//
			if (!$this->hasError) {
				$event['id']	  	  = $this->event_id;
				$event['uid']		  = $this->uid;
				$event['eventid']	  = $this->event_id;
				$event['owneruuid']   = $this->owner_uuid;
				$event['name']		  = $this->event_name;
				$event['creatoruuid'] = $this->creator_uuid;
				$event['category']	  = $this->category;
				$event['description'] = $this->event_desc;
				$event['duration']	  = $this->duration;
				$event['covercharge'] = $this->cover_charge;
				$event['coveramount'] = $this->cover_amount;
				$event['dateutc']	  = $event_date;
				$event['simname']	  = $this->region_uuid;
				$event['globalpos']   = $this->global_pos;
				$event['eventflags']  = $this->check_mature;

				// save to DB
				$this->event_saved = modlos_set_event($event);


				// Saved Event
				if ($this->event_saved) {
					$this->saved_event_name   = $this->event_name;
					$this->saved_category	  = $this->category;
					$this->saved_duration	  = $this->duration;
					$this->saved_cover_amount = $this->cover_amount;
					$this->saved_cover_charge = $this->cover_charge;
					$this->saved_global_pos   = $this->global_pos;
					$this->saved_event_date   = date($this->date_frmt, $event_date);
					$this->saved_event_owner  = $this->event_owner;
					$this->saved_event_creator= $this->event_creator;
   
					$this->saved_region_name  = opensim_get_region_name($this->region_uuid);
					if ($this->saved_region_name=='') $this->saved_region_name = get_string('modlos_unknown_region', 'block_modlos');
   
					if ($this->check_mature) {
						$this->saved_event_type = "title='Mature Event' src=../images/events/blue_star.gif";
					}
					else {
						$this->saved_event_type = "title='PG Event' src=../images/events/pink_star.gif";
					}
   
					// clear valiable
					$this->event_name   = '';
					$this->event_desc   = '';
					$this->category	 	= 0;
					$this->duration	 	= 0;
					$this->cover_charge = 0;
					$this->cover_amount = 0;
					$this->check_mature = 0;
					$this->global_pos   = 0;
					$this->region_uuid  = '';
					$this->event_owner  = '';
					$this->owner_uuid   = '';
					$this->event_creator= '';
					$this->creator_uuid = '';
					$this->event_id	 	= 0;
   
					foreach ($this->creators as $creator) {
						$this->event_owner = $creator['fullname'];
						break;
					}

					$date = getdate();
					$this->event_year   = $date['year'];
					$this->event_month  = $date['mon'];
					$this->event_day	= $date['mday'];
					$this->event_hour   = $date['hours'];
					$this->event_minute = ((int)($date['minutes']/15))*15;
				}
				else {
					$this->hasError = true;
					$this->errorMsg[] = get_string('modlos_update_error', 'block_modlos');
				}
			}
		}

		// GET
		else {	  
			$date = getdate();
			$this->uid = $this->userid;
					
			if (isNumeric($this->event_id) and $this->event_id>0) {
				$event = modlos_get_event($this->event_id);
					
				if ($event!=null and ($event['uid']==$this->userid or $this->hasPermit)) {
					$this->uid			= $event['uid'];
					$this->event_name	= $event['name'];
					$this->owner_uuid	= $event['owneruuid'];
					$this->creator_uuid	= $event['creatoruuid'];
					$this->event_desc	= $event['description'];
					$this->category	 	= $event['category'];
					$this->duration	 	= $event['duration'];
					$this->cover_charge = $event['covercharge'];
					$this->cover_amount = $event['coveramount'];
					$this->check_mature = $event['eventflags'];
					$this->global_pos	= $event['globalpos'];
					$this->region_uuid	= $event['simname'];

					$owner_name = opensim_get_avatar_name($this->owner_uuid);
					$this->event_owner   = $owner_name['fullname'];
					$creator_name = opensim_get_avatar_name($this->creator_uuid);
					$this->event_creator = $creator_name['fullname'];

					$date = getdate($event['dateutc']);
				}
			}
					
			$this->event_year   = $date['year'];
			$this->event_month  = $date['mon'];
			$this->event_day	= $date['mday'];
			$this->event_hour   = $date['hours'];
			$this->event_minute = ((int)($date['minutes']/15))*15;
			if ($this->event_minute==60) {
				$this->event_hour++;
				$this->event_minute = 0;
			}
		}

		return true;
	}




	function  print_page() 
	{
		global $CFG;
		global $Categories;

		$grid_name	= $CFG->modlos_grid_name;
		$module_url	= CMS_MODULE_URL;

		$events_make_ttl 	= get_string('modlos_events_make_ttl',	  'block_modlos');
		$events_save 		= get_string('modlos_events_save',		  'block_modlos');
		$events_saved 		= get_string('modlos_events_saved',		  'block_modlos');

		$events_name 		= get_string('modlos_events_name',		  'block_modlos');
		$events_desc 		= get_string('modlos_events_desc',		  'block_modlos');
		$events_pick_parcel = get_string('modlos_events_pick_parcel', 'block_modlos');
		$events_date 		= get_string('modlos_events_date',		  'block_modlos');
		$events_starts 		= get_string('modlos_events_starts',	  'block_modlos');
		$events_duration 	= get_string('modlos_events_duration',	  'block_modlos');
		$events_location 	= get_string('modlos_events_location',	  'block_modlos');
		$events_owner 		= get_string('modlos_events_owner',		  'block_modlos');
		$events_creator 	= get_string('modlos_events_creator',	  'block_modlos');
		$events_category 	= get_string('modlos_events_category',	  'block_modlos');
		$events_charge 		= get_string('modlos_events_charge',	  'block_modlos');
		$events_amount 		= get_string('modlos_events_amount',	  'block_modlos');
		$events_type 		= get_string('modlos_events_type',		  'block_modlos');
		$events_type_ttl 	= get_string('modlos_events_type_ttl',	  'block_modlos');
		$events_mature_ttl 	= get_string('modlos_events_mature_ttl',  'block_modlos');

		$events_max 		= get_string('modlos_events_max',		  'block_modlos');
		$events_chars 		= get_string('modlos_events_chars',		  'block_modlos');
		$events_inputed 	= get_string('modlos_events_inputed',	  'block_modlos');

		$modlos_no 			= get_string('modlos_no',				  'block_modlos');
		$modlos_yes 		= get_string('modlos_yes',				  'block_modlos');
		$modlos_reset_ttl 	= get_string('modlos_reset_ttl',		  'block_modlos');
		$modlos_delete_ttl 	= get_string('modlos_delete_ttl',		  'block_modlos');
		$modlos_delete 		= get_string('modlos_delete',			  'block_modlos');


		$date_file = CMS_MODULE_PATH.'/lang/'.current_language().'/modlos_events_date.html';
		if (!file_exists($date_file)) {
			$date_file = CMS_MODULE_PATH.'/lang/en_utf8/modlos_events_date.html';
		}

		include(CMS_MODULE_PATH.'/html/edit_event.html');
	}
}

?>
