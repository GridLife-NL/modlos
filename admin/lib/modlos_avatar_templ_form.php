<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';


class modlos_avatar_templ_form extends moodleform
{
	var $clear = false;


	function modlos_avatar_templ_form($clear=false, $action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true)
	{
		$this->clear = $clear;

		parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
	}


	function definition() 
	{
		global $USER, $CFG;

		$mform = $this->_form;
		$mform->setDisableShortforms(true);
		//
        // hidden elements
		$courseid = optional_param('course', 1, PARAM_INT);
		$mform->addElement('hidden', 'course', $courseid);
		$mform->setType('course', PARAM_INT);
		
		if ($this->clear) {
			$_POST = array();
		}

		$mform->addElement('header', 'add_templ', get_string('modlos_avatar_templ_add', 'block_modlos'), null);

		$mform->addElement('text', 'title', get_string('modlos_templ_title','block_modlos'), array('size'=>'48'));
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', null, 'required', null, '');

		$mform->addElement('text', 'uuid', get_string('modlos_templ_uuid','block_modlos'), array('size'=>'36'));
		$mform->setType('uuid', PARAM_TEXT);
		$mform->addRule('uuid', null, 'required', null, '');
		$mform->addHelpButton('uuid', 'modlos_templ_uuid', 'block_modlos');

		$edoption = array('subdirs'=>0, 'maxfiles'=>1);
		$mform->addElement('editor', 'explain', get_string('modlos_templ_text','block_modlos'), null, $edoption);
		$mform->setType('explain', PARAM_RAW);

		$fmoption = array('subdirs'=>0, 'maxfiles'=>1, 'accepted_types'=>array('.jpg','.jpeg','.png','.tif','.tiff','.gif'));
		$mform->addElement('filemanager', 'picfile', get_string('modlos_templ_pic','block_modlos'), null, $fmoption);
		$mform->addHelpButton('picfile', 'modlos_templ_pic', 'block_modlos');


        // buttons
//		$mform->addElement('submit', 'add_item', get_string('add_item', 'apply'));
		$this->add_action_buttons();


/*
		$policies = array();
		if (!empty($CFG->passwordpolicy)) {
			$policies[] = print_password_policy();
		}
		if (!empty($CFG->passwordreuselimit) and $CFG->passwordreuselimit > 0) {
			$policies[] = get_string('informminpasswordreuselimit', 'auth', $CFG->passwordreuselimit);
		}
		if ($policies) {
			$mform->addElement('static', 'passwordpolicyinfo', '', implode('<br />', $policies));
		}
		$mform->addElement('password', 'password', get_string('oldpassword'));
		$mform->addRule('password', get_string('required'), 'required', null, 'client');
		$mform->setType('password', PARAM_RAW);

		$mform->addElement('password', 'newpassword1', get_string('newpassword'));
		$mform->addRule('newpassword1', get_string('required'), 'required', null, 'client');
		$mform->setType('newpassword1', PARAM_RAW);

		$mform->addElement('password', 'newpassword2', get_string('newpassword').' ('.get_String('again').')');
		$mform->addRule('newpassword2', get_string('required'), 'required', null, 'client');
		$mform->setType('newpassword2', PARAM_RAW);


		// hidden optional params
		$mform->addElement('hidden', 'id', 0);
		$mform->setType('id', PARAM_INT);

		// buttons
		if (get_user_preferences('auth_forcepasswordchange')) {
			$this->add_action_buttons(false);
		} else {
			$this->add_action_buttons(true);
		}
*/
	}
}
