<?php


$settings->add(new admin_setting_configtext('mdlopnsm_grid_name', 
					get_string('mdlos_grid_name', 'block_mdlopensim'),
                   	get_string('mdlos_grid_desc', 'block_mdlopensim'), "Open Grid", PARAM_TEXT));

$settings->add(new admin_setting_configtext('mdlopnsm_sql_server_name', 
					get_string('mdlos_sql_server_name', 'block_mdlopensim'),
                   	get_string('mdlos_sql_server_desc', 'block_mdlopensim'), "localhost", PARAM_TEXT));

$settings->add(new admin_setting_configtext('mdlopnsm_sql_db_name', 
					get_string('mdlos_sql_db_name', 'block_mdlopensim'),
                   	get_string('mdlos_sql_db_desc', 'block_mdlopensim'), "opensim", PARAM_TEXT));

$settings->add(new admin_setting_configtext('mdlopnsm_sql_db_user', 
					get_string('mdlos_sql_user', 'block_mdlopensim'),
                   	get_string('mdlos_sql_user_desc', 'block_mdlopensim'), "opensim_user", PARAM_TEXT));

$settings->add(new admin_setting_configtext('mdlopnsm_sql_db_pass', 
					get_string('mdlos_sql_pass', 'block_mdlopensim'),
                   	get_string('mdlos_sql_pass_desc', 'block_mdlopensim'), "opensim_pass", PARAM_TEXT));

/*
$settings->add(new admin_setting_configtext('mdlopnsm_asset_uri', 
					get_string('mdlos_asset_uri', 'block_mdlopensim'),
                   	get_string('mdlos_asset_uri_desc', 'block_mdlopensim'), "http://opensim.jp:8003/", PARAM_URL));

$settings->add(new admin_setting_configtext('mdlopnsm_invent_uri', 
					get_string('mdlos_invent_uri', 'block_mdlopensim'),
                   	get_string('mdlos_invent_uri_desc', 'block_mdlopensim'), "http://opensim.jp:8003/", PARAM_URL));
*/

$settings->add(new admin_setting_configtext('mdlopnsm_currency_uri', 
					get_string('mdlos_crncy_uri', 'block_mdlopensim'),
                   	get_string('mdlos_crncy_uri_desc', 'block_mdlopensim'), "http://opensim.jp:8008/", PARAM_URL));

$settings->add(new admin_setting_configtext('mdlopnsm_map_start_x', 
					get_string('mdlos_map_stx', 'block_mdlopensim'),
                   	get_string('mdlos_map_stx_desc', 'block_mdlopensim'), "1000", PARAM_INT));

$settings->add(new admin_setting_configtext('mdlopnsm_map_start_y', 
					get_string('mdlos_map_sty', 'block_mdlopensim'),
                   	get_string('mdlos_map_sty_desc', 'block_mdlopensim'), "1000", PARAM_INT));

$options = array('16'=>16, '32'=>32, '64'=>64, '128'=>128, '256'=>256, '512'=>512);
$settings->add(new admin_setting_configselect('mdlopnsm_map_size', 
					get_string('mdlos_map_size', 'block_mdlopensim'),
                   	get_string('mdlos_map_size_desc', 'block_mdlopensim'), "128", $options));

$settings->add(new admin_setting_configtext('mdlopnsm_max_own_avatars', 
					get_string('mdlos_max_avatars', 'block_mdlopensim'),
                   	get_string('mdlos_max_avatars_desc', 'block_mdlopensim'), "1", PARAM_INT));

$settings->add(new admin_setting_configcheckbox('mdlopnsm_activate_lastname', 
					get_string('mdlos_lname_activate', 'block_mdlopensim'),
                   	get_string('mdlos_lname_desc', 'block_mdlopensim'), 0));

$settings->add(new admin_setting_configtext('mdlopnsm_home_region', 
					get_string('mdlos_dst_region_name', 'block_mdlopensim'),
                   	get_string('mdlos_dst_region_desc', 'block_mdlopensim'), "", PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('mdlopnsm_use_https', 
					get_string('mdlos_use_https', 'block_mdlopensim'),
                   	get_string('mdlos_use_https_desc', 'block_mdlopensim'), 0));

$settings->add(new admin_setting_configtext('mdlopnsm_https_url', 
					get_string('mdlos_https_url', 'block_mdlopensim'),
                   	get_string('mdlos_https_url_desc', 'block_mdlopensim'), "", PARAM_URL));

$settings->add(new admin_setting_configtext('mdlopnsm_groupdb_read_key', 
					get_string('mdlos_grpdb_rkey', 'block_mdlopensim'),
                   	get_string('mdlos_grpdb_rkey_desc', 'block_mdlopensim'), "1234", PARAM_TEXT));

$settings->add(new admin_setting_configtext('mdlopnsm_groupdb_write_key', 
					get_string('mdlos_grpdb_wkey', 'block_mdlopensim'),
                   	get_string('mdlos_grpdb_wkey_desc', 'block_mdlopensim'), "1234", PARAM_TEXT));

/*
$settings->add(new admin_setting_configtext('mdlopnsm_groupdb_currency_key', 
					get_string('mdlos_crncy_key', 'block_mdlopensim'),
                   	get_string('mdlos_crncy_key_desc', 'block_mdlopensim'), "1234", PARAM_TEXT));

$settings->add(new admin_setting_configtext('mdlopnsm_banker_avatar', 
					get_string('mdlos_banker', 'block_mdlopensim'),
                   	get_string('mdlos_banker_desc', 'block_mdlopensim'), "00000000-0000-0000-0000-000000000000", PARAM_TEXT));
*/

$settings->add(new admin_setting_configtext('mdlopnsm_date_format', 
					get_string('mdlos_date_format', 'block_mdlopensim'),
                   	get_string('mdlos_date_format_desc', 'block_mdlopensim'), "Y.m.d - H:i", PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('mdlopnsm_userinfo_link', 
					get_string('mdlos_userinfo_link', 'block_mdlopensim'),
                   	get_string('mdlos_userinfo_link_desc', 'block_mdlopensim'), 0));

$settings->add(new admin_setting_configcheckbox('mdlopnsm_cooperate_sloodle', 
					get_string('mdlos_cprt_sloodle', 'block_mdlopensim'),
                   	get_string('mdlos_cprt_sloodle_desc', 'block_mdlopensim'), 1));

$settings->add(new admin_setting_configcheckbox('mdlopnsm_priority_sloodle', 
					get_string('mdlos_prty_sloodle', 'block_mdlopensim'),
                   	get_string('mdlos_prty_sloodle_desc', 'block_mdlopensim'), 1));

$settings->add(new admin_setting_configtextarea('mdlopnsm_home_content',
					get_string('mdlos_home_cntnt', 'block_mdlopensim'),
                   	get_string('mdlos_home_cntnt_desc', 'block_mdlopensim'), "<h2><center>Welcome to Moodle OpenSim Interface</center></h2>", PARAM_RAW));

$settings->add(new admin_setting_configtextarea('mdlopnsm_regions_content', 
					get_string('mdlos_rg_cntnt', 'block_mdlopensim'),
                   	get_string('mdlos_rg_cntnt_desc', 'block_mdlopensim'), "", PARAM_RAW));

$settings->add(new admin_setting_configtextarea('mdlopnsm_avatars_content', 
					get_string('mdlos_avt_cntnt', 'block_mdlopensim'),
                   	get_string('mdlos_avt_cntnt_desc', 'block_mdlopensim'), "", PARAM_RAW));

$settings->add(new admin_setting_configtextarea('mdlopnsm_editable_content', 
					get_string('mdlos_edtbl_cntnt', 'block_mdlopensim'),
                   	get_string('mdlos_edtbl_cntnt_desc', 'block_mdlopensim'), "", PARAM_RAW));

$settings->add(new admin_setting_configcheckbox('mdlopnsm_activate_disclaimer', 
					get_string('mdlos_dsclmr', 'block_mdlopensim'),
                   	get_string('mdlos_dsclmr_desc', 'block_mdlopensim'), 0));

$settings->add(new admin_setting_configtextarea('mdlopnsm_disclaimer_content', 
					get_string('mdlos_dsclmr_cntnt', 'block_mdlopensim'),
                   	get_string('mdlos_dsclmr_cntnt_desc', 'block_mdlopensim'), "", PARAM_RAW));

?>
