<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';


class modlos_avatar_templ_form extends moodleform
{
	function definition() 
	{
		global $USER, $CFG;

		$mform = $this->_form;
		$mform->setDisableShortforms(true);
		//
		$mform->addElement('header', 'avatar_templ_add', get_string('modlos_avatar_templ_add', 'block_modlos'), '');

		$mform->addElement('text', 'title', get_string('modlos_templ_title','block_modlos'), array('size'=>'48'));
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', null, 'required', null, 'client');
		$mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, '');

		$mform->addElement('text', 'UUID', get_string('modlos_templ_uuid','block_modlos'), array('size'=>'36'));

		$mform->addElement('filemanager', 'picfile', get_string('modlos_templ_pic','block_modlos'), null, array('subdirs'=>0, 'accepted_types'=>'*'));


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

/// perform extra password change validation
	function validation($data, $files) {
		global $USER;
		$errors = parent::validation($data, $files);

		// ignore submitted username
		if (!$user = authenticate_user_login($USER->username, $data['password'], true)) {
			$errors['password'] = get_string('invalidlogin');
			return $errors;
		}

		if ($data['newpassword1'] <> $data['newpassword2']) {
			$errors['newpassword1'] = get_string('passwordsdiffer');
			$errors['newpassword2'] = get_string('passwordsdiffer');
			return $errors;
		}

		if ($data['password'] == $data['newpassword1']){
			$errors['newpassword1'] = get_string('mustchangepassword');
			$errors['newpassword2'] = get_string('mustchangepassword');
			return $errors;
		}

		if (user_is_previously_used_password($USER->id, $data['newpassword1'])) {
			$errors['newpassword1'] = get_string('errorpasswordreused', 'core_auth');
			$errors['newpassword2'] = get_string('errorpasswordreused', 'core_auth');
		}

		$errmsg = '';//prevents eclipse warnings
		if (!check_password_policy($data['newpassword1'], $errmsg)) {
			$errors['newpassword1'] = $errmsg;
			$errors['newpassword2'] = $errmsg;
			return $errors;
		}

		return $errors;
	}
}
