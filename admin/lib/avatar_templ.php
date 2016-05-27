<?php

require_once(realpath(dirname(__FILE__).'/../../../config.php'));
require_once($CFG->dirroot.'/user/lib.php');
require_once('../lib/modlos_avatar_templ_form.php');

require_once(realpath(dirname(__FILE__).'/../include/env_interface.php'));
require_once(realpath(dirname(__FILE__).'/../include/modlos.func.php'));


$course_id = optional_param('course', 0, PARAM_INT);
if (!$course_id) $course_id = optional_param('id', SITEID, PARAM_INT); /

$urlparams = array();
$urlparams['course'] = $course_id;
$PAGE->set_url('/blocks/modlos/actions/avatar_templ.php', $urlparams);

$course = $DB->get_record('course', array('id'=>$course_id));

//
$tab_action = '';
require_login($course_id);
print_modlos_header($tab_action, $course);


/*
require_once(CMS_MODULE_PATH.'/class/avatars_list.class.php');
$avatars = new AvatarsList($course_id, true);
*/

print_tabnav($tab_action, $course, !$avatars->isAvatarMax);

/*
$avatars->set_condition();
$avatars->execute();
$avatars->print_page();
*/


$mform = new modlos_avatar_templ_form();
$mform->set_data(array('id'=>$course->id));
$mform->display();

echo $OUTPUT->footer($course);






/*
$systemcontext = context_system::instance();
$PAGE->set_url('/login/change_password.php', array('id'=>$id));
$PAGE->set_context($systemcontext);

if ($return) {
    // this redirect prevents security warning because https can not POST to http pages
    if (empty($SESSION->wantsurl)
            or stripos(str_replace('https://', 'http://', $SESSION->wantsurl), str_replace('https://', 'http://', $CFG->wwwroot.'/login/change_password.php')) === 0) {
        $returnto = "$CFG->wwwroot/user/preferences.php?userid=$USER->id&course=$id";
    } else {
        $returnto = $SESSION->wantsurl;
    }
    unset($SESSION->wantsurl);

    redirect($returnto);
}

$strparticipants = get_string('participants');

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

// require proper login; guest user can not change password
if (!isloggedin() or isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->httpswwwroot.'/login/change_password.php';
    }
    redirect(get_login_url());
}

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_pagelayout('admin');
$PAGE->set_course($course);

// do not require change own password cap if change forced
if (!get_user_preferences('auth_forcepasswordchange', false)) {
    require_capability('moodle/user:changeownpassword', $systemcontext);
}

// do not allow "Logged in as" users to change any passwords
if (\core\session\manager::is_loggedinas()) {
    print_error('cannotcallscript');
}

if (is_mnet_remote_user($USER)) {
    $message = get_string('usercannotchangepassword', 'mnet');
    if ($idprovider = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid))) {
        $message .= get_string('userchangepasswordlink', 'mnet', $idprovider);
    }
    print_error('userchangepasswordlink', 'mnet', '', $message);
}

// load the appropriate auth plugin
$userauth = get_auth_plugin($USER->auth);

if (!$userauth->can_change_password()) {
    print_error('nopasswordchange', 'auth');
}

if ($changeurl = $userauth->change_password_url()) {
    // this internal scrip not used
    redirect($changeurl);
}

$mform = new login_change_password_form();
$mform->set_data(array('id'=>$course->id));

$navlinks = array();
$navlinks[] = array('name' => $strparticipants, 'link' => "$CFG->wwwroot/user/index.php?id=$course->id", 'type' => 'misc');

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot.'/user/preferences.php?userid='.$USER->id.'&amp;course='.$course->id);
} else if ($data = $mform->get_data()) {

    if (!$userauth->user_update_password($USER, $data->newpassword1)) {
        print_error('errorpasswordupdate', 'auth');
    }

    user_add_password_history($USER->id, $data->newpassword1);

    if (!empty($CFG->passwordchangelogout)) {
        \core\session\manager::kill_user_sessions($USER->id, session_id());
    }

    // Reset login lockout - we want to prevent any accidental confusion here.
    login_unlock_account($USER);

    // register success changing password
    unset_user_preference('auth_forcepasswordchange', $USER);
    unset_user_preference('create_password', $USER);

    $strpasswordchanged = get_string('passwordchanged');

    $fullname = fullname($USER, true);

    $PAGE->set_title($strpasswordchanged);
    $PAGE->set_heading(fullname($USER));
    echo $OUTPUT->header();

    notice($strpasswordchanged, new moodle_url($PAGE->url, array('return'=>1)));

    echo $OUTPUT->footer();
    exit;
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();

$strchangepassword = get_string('changepassword');

$fullname = fullname($USER, true);

$PAGE->set_title($strchangepassword);
$PAGE->set_heading($fullname);
echo $OUTPUT->header();

if (get_user_preferences('auth_forcepasswordchange')) {
    echo $OUTPUT->notification(get_string('forcepasswordchangenotice'));
}
$mform->display();
echo $OUTPUT->footer();
*/
