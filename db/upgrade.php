<?php

function xmldb_block_modlos_upgrade($oldversion=0)
{
	global $CFG, $THEME, $db;

	$result = true;


	// 2010083024
	if ($result && $oldversion < 2010083024) {
		$table = new XMLDBTable('modlos_mute_list');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('agentid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('muteid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('mutename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('mutetype',  XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
		$table->addFieldInfo('muteflags', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
		$table->addFieldInfo('timestamp', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$result = $result && create_table($table);
	}



	// 2010090100
	if ($result && $oldversion < 2010090100) {
		$table = new XMLDBTable('modlos_login_screen');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('information', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('bordercolor', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, null, 'white');
		$table->addFieldInfo('timestamp', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

		$table->addKeyInfo('id', XMLDB_KEY_PRIMARY, array('id'));
		$result = $result && create_table($table);
	}



	//  2010092000
	if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_allparcels');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parcelname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('owneruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->addFieldInfo('groupuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->addFieldInfo('landingpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->addFieldInfo('infouuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->addFieldInfo('parcelarea', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->addKeyInfo('parceluuid', XMLDB_KEY_UNIQUE, array('parceluuid'));
		$result = $result && create_table($table);
	}

	//if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_events');
 		drop_table($table);

		$table->addFieldInfo('uid', XMLDB_TYPE_INTEGER, '8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
		$table->addFieldInfo('owneruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('eventid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('creatoruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('category', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('description', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('dateutc', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('duration', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('covercharge', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('coveramount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('simname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('globalpos', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('eventflags', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->addKeyInfo('eventid', XMLDB_KEY_PRIMARY, array('eventid'));
		$result = $result && create_table($table);
	//}

	if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_hostsregister');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('host', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('port', XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('register', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('nextcheck', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('checked', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('failcounter', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->addKeyInfo('host', XMLDB_KEY_UNIQUE, array('host', 'port'));
		$result = $result && create_table($table);
	}

	if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_objects');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('objectuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('location', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('description', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->addKeyInfo('uuid', XMLDB_KEY_UNIQUE, array('objectuuid', 'parceluuid'));
		$result = $result && create_table($table);
	}

	if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_parcels');
 		drop_table($table);

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parcelname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('landingpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('description', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('searchcategory', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('build',  XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, XMLDB_ENUM, array('true', 'false'), 'false');
		$table->addFieldInfo('script', XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, XMLDB_ENUM, array('true', 'false'), 'false');
		$table->addFieldInfo('public', XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, XMLDB_ENUM, array('true', 'false'), 'false');
		$table->addFieldInfo('dwell', XMLDB_TYPE_NUMBER, '20, 8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('infouuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('mature', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, null, 'PG');

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->addKeyInfo('uuid', XMLDB_KEY_UNIQUE, array('regionuuid', 'parceluuid'));
		$result = $result && create_table($table);
	}

	if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_parcelsales');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parcelname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('area', XMLDB_TYPE_INTEGER, '6', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('saleprice', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('landingpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('infouuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->addFieldInfo('dwell', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('parentestate', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '1');
		$table->addFieldInfo('mature', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, null, 'PG');

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->addKeyInfo('uuid', XMLDB_KEY_UNIQUE, array('regionuuid', 'parceluuid'));
		$result = $result && create_table($table);
	}

	if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_popularplaces');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('dwell', XMLDB_TYPE_NUMBER, '20, 8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('infouuid', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('has_picture', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('mature', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$result = $result && create_table($table);
	}

	if ($result && $oldversion < 2010092000) {
		$table = new XMLDBTable('modlos_search_regions');

		$table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->addFieldInfo('regionname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('regionhandle', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('owner', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->addFieldInfo('owneruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);

		$table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->addKeyInfo('regionuuid', XMLDB_KEY_UNIQUE, array('regionuuid'));
		$result = $result && create_table($table);
	}


	return $result;
}

?>
