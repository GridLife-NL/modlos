
-- --------------------------------------------------------
-- for Basic
-- --------------------------------------------------------

CREATE TABLE `block_modlos_users` (
	`UUID` 		varchar(36)  NOT NULL default '',
	`uid`       mediumint(8) unsigned NOT NULL default 0,
	`firstname` varchar(32)  NOT NULL,
	`lastname` 	varchar(32)  NOT NULL,
	`hmregion` 	varchar(255) NOT NULL default '',
	`state` 	varchar(5)   NOT NULL default '0',
	`time` 		varchar(255) NOT NULL,
	PRIMARY KEY (`UUID`),
	UNIQUE KEY 	`username` (`firstname`,`lastname`)
) TYPE=MyISAM;


CREATE TABLE `block_modlos_banned` (
	`UUID` 		varchar(36)  NOT NULL,
	`agentinfo` varchar(255) NOT NULL,
	`time` 		varchar(255) NOT NULL,
	PRIMARY KEY  (`UUID`)
) TYPE=MyISAM;


CREATE TABLE `block_modlos_codetable` (
	`UUID` 	varchar(36)  NOT NULL,
	`code` 	varchar(255) NOT NULL,
	`info` 	varchar(255) NOT NULL,
	`time` 	varchar(255) NOT NULL,
	PRIMARY KEY  (`UUID`)
) TYPE=MyISAM;


CREATE TABLE `block_modlos_lastnames` (
	`lastname` 	varchar(255) NOT NULL,
	`state` 	varchar(5)   NOT NULL default '1',
	PRIMARY KEY  (`lastname`)
) TYPE=MyISAM;


INSERT INTO `block_modlos_lastnames` (`lastname`, `state`) VALUES 
('Infosys', '1'),
('Manage', '1'),
('Env', '1'),
('Media', '1'),
('NSL', '1'),
('TUIS', '1'),
('Visitor', '1');



-- --------------------------------------------------------
-- for Economy
-- --------------------------------------------------------

CREATE TABLE `block_modlos_economy_money` (
	`id` 				INT AUTO_INCREMENT NOT NULL,
	`CentsPerMoneyUnit` INT NOT NULL DEFAULT '0',
	PRIMARY KEY (id)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


INSERT INTO `block_modlos_economy_money` (`id`, `CentsPerMoneyUnit`) values (1, 0.5);


CREATE TABLE `block_modlos_economy_transactions` (
	`id` 		int(11) NOT NULL auto_increment,
	`sourceId` 	varchar(36) NOT NULL,
	`destId` 	varchar(36) NOT NULL,
	`amount` 	int(11) NOT NULL default '0',
	`flags` 	int(11) NOT NULL default '0',
	`aggregatePermInventory` int(11) NOT NULL default '0',
	`aggregatePermNextOwner` int(11) NOT NULL default '0',
	`description` 		varchar(256) default NULL,
	`transactionType` 	int(11) NOT NULL default '0',
	`timeOccurred` 		int(11) NOT NULL,
	`RegionGenerated` 	varchar(36) NOT NULL,
	`IPGenerated` 		varchar(64) NOT NULL,
	PRIMARY KEY  (`id`)
) TYPE=MyISAM  AUTO_INCREMENT=1;



-- --------------------------------------------------------
-- for XMLRPCGROUPS
-- --------------------------------------------------------
-- 
-- Table structure for table `group_active`
-- 

CREATE TABLE `block_modlos_group_active` (
	`AgentID` 		varchar(64) NOT NULL default '',
	`ActiveGroupID` varchar(64) NOT NULL default '',
	PRIMARY KEY  	(`AgentID`)
) TYPE=MyISAM;


-- 
-- Table structure for table `group_list`
-- 

CREATE TABLE `block_modlos_group_list` (
	`GroupID` 		varchar(64) NOT NULL default '',
	`Name` 			varchar(255) NOT NULL default '',
	`Charter` 		text NOT NULL,
	`InsigniaID` 	varchar(64) NOT NULL default '',
	`FounderID` 	varchar(64) NOT NULL default '',
	`MembershipFee` int(11) NOT NULL default '0',
	`OpenEnrollment` varchar(255) NOT NULL default '',
	`ShowInList` 	tinyint(1) NOT NULL default '0',
	`AllowPublish` 	tinyint(1) NOT NULL default '0',
	`MaturePublish` tinyint(1) NOT NULL default '0',
	`OwnerRoleID` 	varchar(128) NOT NULL default '',
	PRIMARY KEY  (`GroupID`),
	UNIQUE KEY `Name` (`Name`),
	FULLTEXT KEY `Name_2` (`Name`)
) TYPE=MyISAM;


-- 
-- Table structure for table `group_invite`
-- 

CREATE TABLE `block_modlos_group_invite` (
	`InviteID` 	varchar(64) NOT NULL default '',
	`GroupID` 	varchar(64) NOT NULL default '',
	`RoleID` 	varchar(64) NOT NULL default '',
	`AgentID` 	varchar(64) NOT NULL default '',
	`TMStamp` 	timestamp(14) NOT NULL,
	PRIMARY KEY  (`InviteID`),
	UNIQUE KEY `GroupID` (`GroupID`,`RoleID`,`AgentID`)
) TYPE=MyISAM;


-- 
-- Table structure for table `group_membership`
-- 

CREATE TABLE `block_modlos_group_membership` (
	`GroupID` 		 varchar(64) NOT NULL default '',
	`AgentID` 		 varchar(64) NOT NULL default '',
	`SelectedRoleID` varchar(64) NOT NULL default '',
	`Contribution` 	int(11) NOT NULL default '0',
	`ListInProfile` int(11) NOT NULL default '1',
	`AcceptNotices` int(11) NOT NULL default '1',
	PRIMARY KEY  (`GroupID`,`AgentID`)
) TYPE=MyISAM;


-- 
-- Table structure for table `group_notice`
-- 

CREATE TABLE `block_modlos_group_notice` (
	`GroupID` 	varchar(64) NOT NULL default '',
	`NoticeID` 	varchar(64) NOT NULL default '',
	`Timestamp` int(10) unsigned NOT NULL default '0',
	`FromName`	varchar(255) NOT NULL default '',
	`Subject` 	varchar(255) NOT NULL default '',
	`Message` 	text NOT NULL,
	`BinaryBucket` text NOT NULL,
	PRIMARY KEY  (`GroupID`,`NoticeID`),
	KEY `Timestamp` (`Timestamp`)
) TYPE=MyISAM;


-- 
-- Table structure for table `group_rolemembership`
-- 

CREATE TABLE `block_modlos_group_rolemembership` (
	`GroupID` 	varchar(64) NOT NULL default '',
	`RoleID` 	varchar(64) NOT NULL default '',
	`AgentID` 	varchar(64) NOT NULL default '',
	PRIMARY KEY  (`GroupID`,`RoleID`,`AgentID`)
) TYPE=MyISAM;


-- 
-- Table structure for table `role`
-- 

CREATE TABLE `block_modlos_group_role` (
	`GroupID` 	  varchar(64)  NOT NULL default '',
	`RoleID` 	  varchar(64)  NOT NULL default '',
	`Name` 		  varchar(255) NOT NULL default '',
	`Description` varchar(255) NOT NULL default '',
	`Title` 	  varchar(255) NOT NULL default '',
	`Powers` 	  bigint(20) unsigned NOT NULL default '0',
	PRIMARY KEY  (`GroupID`,`RoleID`)
) TYPE=MyISAM;



-- --------------------------------------------------------
-- for Offline Message
-- --------------------------------------------------------

CREATE TABLE `block_modlos_offline_message` (
	`to_uuid`		varchar(36) NOT NULL,
	`from_uuid`		varchar(36) NOT NULL,
	`message`		text NOT NULL,
	KEY `to_uuid` (`to_uuid`)
) TYPE=MyISAM;



-- --------------------------------------------------------
-- for Mute List
-- --------------------------------------------------------

CREATE TABLE `block_modlos_mute_list` (
	`agentID`		varchar(36)  NOT NULL,
	`muteID`		varchar(36)  NOT NULL,
	`muteName`		varchar(255) NOT NULL,
	`muteType`		int(10) unsigned NOT NULL default '0',
	`muteFlags`		int(10) unsigned NOT NULL default '0',
	`timestamp` 	int(11) unsigned NOT NULL default '0',
	PRIMARY KEY  (`AgentID`,`MuteID`,`MuteName`)
) TYPE=MyISAM;



-- --------------------------------------------------------
-- for Login Screen
-- --------------------------------------------------------

CREATE TABLE `block_modlos_login_screen` (
	`id`       		int(10) unsigned NOT NULL auto_increment,
	`title`			varchar(255) NOT NULL default '',
	`information`	text NOT NULL default '',
	`boderColor`  	varchar(20) NOT NULL default 'white', 
	`timestamp` 	int(11) unsigned NOT NULL default '0',
	PRIMARY KEY (`id`)
) TYPE=MyISAM;



-- --------------------------------------------------------
-- for Profile
-- --------------------------------------------------------

CREATE TABLE `block_modlos_prof_classifieds` (
	`classifieduuid` 	char(36) NOT NULL,
	`creatoruuid` 		char(36) NOT NULL,
	`creationdate` 		int(20)  NOT NULL,
	`expirationdate` 	int(20)  NOT NULL,
	`category` 			varchar(20) NOT NULL,
	`name` 				varchar(255) NOT NULL,
	`description` 		text NOT NULL,
	`parceluuid` 		char(36) NOT NULL,
	`parentestate` 		int(11)  NOT NULL,
	`snapshotuuid` 		char(36) NOT NULL,
	`simname` 			varchar(255) NOT NULL,
	`posglobal` 		varchar(255) NOT NULL,
	`parcelname` 		varchar(255) NOT NULL,
	`classifiedflags` 	int(8) NOT NULL,
	`priceforlisting` 	int(5) NOT NULL,
 	 PRIMARY KEY 	(`classifieduuid`)
) TYPE=MyISAM;


CREATE TABLE `block_modlos_prof_usernotes` (
	`id`        		int(11) NOT NULL auto_increment,
	`useruuid` 			varchar(36) NOT NULL,
	`targetuuid` 		varchar(36) NOT NULL,
	`notes` 			text NOT NULL,
	PRIMARY KEY 	(`id`),
	UNIQUE KEY 	`useruuid` (`useruuid`, `targetuuid`)
) TYPE=MyISAM;


CREATE TABLE `block_modlos_prof_userpicks` (
	`pickuuid` 			varchar(36) NOT NULL,
	`creatoruuid` 		varchar(36) NOT NULL,
	`toppick` 			enum('true','false') NOT NULL,
	`parceluuid` 		varchar(36)  NOT NULL,
	`name` 				varchar(255) NOT NULL,
	`description` 		text NOT NULL,
	`snapshotuuid` 		varchar(36)  NOT NULL,
	`user` 				varchar(255) NOT NULL,
	`originalname` 		varchar(255) NOT NULL,
	`simname` 			varchar(255) NOT NULL,
	`posglobal` 		varchar(255) NOT NULL,
	`sortorder` 		int(2) NOT NULL,
	`enabled` 			enum('true','false') NOT NULL,
	PRIMARY KEY 	(`pickuuid`)
) TYPE=MyISAM;


CREATE TABLE `block_modlos_prof_userprofile` (
	`useruuid` 			 	varchar(36) NOT NULL,
	`profilePartner` 		varchar(36) NOT NULL,
	`profileImage` 		 	varchar(36) NOT NULL,
	`profileAboutText` 	 	text NOT NULL,
	`profileAllowPublish`  	binary(1) NOT NULL,
	`profileMaturePublish` 	binary(1) NOT NULL,
	`profileURL` 			varchar(255) NOT NULL,
	`profileWantToMask` 	int(3) NOT NULL,
	`profileWantToText` 	text NOT NULL,
	`profileSkillsMask` 	int(3) NOT NULL,
	`profileSkillsText` 	text NOT NULL,
	`profileLanguagesText` 	text NOT NULL,
	`profileFirstImage`		varchar(36) NOT NULL,
	`profileFirstText` 	 	text NOT NULL,
	PRIMARY KEY 	(`useruuid`)
) TYPE=MyISAM;


CREATE TABLE `block_modlos_prof_usersettings` (
	`useruuid` 			varchar(36) NOT NULL,
	`imviaemail` 		enum('true','false') NOT NULL,
	`visible` 			enum('true','false') NOT NULL,
	`email` 			varchar(254) NOT NULL,
	PRIMARY KEY 	(`useruuid`)
) TYPE=MyISAM;



-- --------------------------------------------------------
-- for Search
-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `allparcels`
-- 

CREATE TABLE `block_modlos_search_allparcels` (
	`regionUUID` 	char(36)  NOT NULL,
	`parcelname` 	varchar(255) NOT NULL,
	`ownerUUID` 	char(36) NOT NULL default '00000000-0000-0000-0000-000000000000',
	`groupUUID` 	char(36) NOT NULL default '00000000-0000-0000-0000-000000000000',
	`landingpoint` 	varchar(255) NOT NULL,
	`parcelUUID` 	char(36) NOT NULL default '00000000-0000-0000-0000-000000000000',
	`infoUUID` 		char(36) NOT NULL default '00000000-0000-0000-0000-000000000000',
	`parcelarea` 	int(11)  NOT NULL,
	PRIMARY KEY  (`parcelUUID`),
	KEY `regionUUID` (`regionUUID`)
) TYPE=MyISAM;


-- 
-- Tabel structuur voor tabel `classifieds`
-- 

CREATE TABLE `block_modlos_search_classifieds` (
	`classifieduuid` 	char(36) NOT NULL,
	`creatoruuid` 		char(36) NOT NULL,
	`creationdate` 		int(20)  NOT NULL,
	`expirationdate` 	int(20)  NOT NULL,
	`category` 			varchar(20) NOT NULL,
	`name` 				varchar(255) NOT NULL,
	`description` 		text NOT NULL,
	`parceluuid` 		char(36) NOT NULL,
	`parentestate` 		int(11)  NOT NULL,
	`snapshotuuid` 		char(36) NOT NULL,
	`simname` 			varchar(255) NOT NULL,
	`posglobal` 		varchar(255) NOT NULL,
	`parcelname` 		varchar(255) NOT NULL,
	`classifiedflags`	int(8) NOT NULL,
	`priceforlisting` 	int(5) NOT NULL,
	PRIMARY KEY  (`classifieduuid`)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


-- 
-- Tabel structuur voor tabel `events`
-- 

CREATE TABLE `block_modlos_search_events` (
	`owneruuid` 	char(36) NOT NULL,
	`name` 			varchar(255) NOT NULL,
	`eventid` 		int(11) NOT NULL,
	`creatoruuid` 	char(36) NOT NULL,
	`category` 		int(2)  NOT NULL,
	`description` 	text NOT NULL,
	`dateUTC` 		int(10) NOT NULL,
	`duration` 		int(10) NOT NULL,
	`covercharge` 	int(10) NOT NULL,
	`coveramount` 	int(10) NOT NULL,
	`simname` 		varchar(255) NOT NULL,
	`globalPos` 	varchar(255) NOT NULL,
	`eventflags` 	int(10) NOT NULL,
	`mature` 		enum('true','false') NOT NULL,
	PRIMARY KEY  (`eventid`)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


-- 
-- Tabel structuur voor tabel `hostsregister`
-- 

CREATE TABLE `block_modlos_search_hostsregister` (
	`host` 			varchar(255) NOT NULL,
	`port` 			int(5)  NOT NULL,
	`register` 		int(10) NOT NULL,
	`nextcheck` 	int(10) NOT NULL,
	`checked` 		tinyint(1) NOT NULL,
	`failcounter` 	int(10) NOT NULL,
	PRIMARY KEY (`host`,`port`)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


-- 
-- Tabel structuur voor tabel `objects`
-- 

CREATE TABLE `block_modlos_search_objects` (
	`objectuuid` 	char(36)  NOT NULL,
	`parceluuid` 	char(36)  NOT NULL,
	`location` 		varchar(255) NOT NULL,
	`name` 			varchar(255) NOT NULL,
	`description` 	varchar(255) NOT NULL,
	`regionuuid` 	char(36)  NOT NULL default '',
PRIMARY KEY  (`objectuuid`,`parceluuid`)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


-- 
-- Tabel structuur voor tabel `parcels`
-- 

CREATE TABLE `block_modlos_search_parcels` (
	`regionUUID` 		char(36) NOT NULL,
	`parcelname` 		varchar(255) NOT NULL,
	`parcelUUID` 		char(36) NOT NULL,
	`landingpoint` 		varchar(255) NOT NULL,
	`description` 		varchar(255) NOT NULL,
	`searchcategory` 	varchar(50)  NOT NULL,
	`build` 			enum('true','false') NOT NULL,
	`script` 			enum('true','false') NOT NULL,
	`public` 			enum('true','false') NOT NULL,
	`dwell` 			float NOT NULL default '0',
	`infouuid` 			char(36)  NOT NULL default '',
	PRIMARY KEY  (`regionUUID`,`parcelUUID`),
	KEY `name` (`parcelname`),
	KEY `description` (`description`),
	KEY `searchcategory` (`searchcategory`),
	KEY `dwell` (`dwell`)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


-- 
-- Tabel structuur voor tabel `parcelsales`
-- 

CREATE TABLE `block_modlos_search_parcelsales` (
	`regionUUID` 	char(36)  NOT NULL,
	`parcelname` 	varchar(255) NOT NULL,
	`parcelUUID` 	char(36)  NOT NULL,
	`area` 			int(6)  NOT NULL,
	`saleprice` 	int(11) NOT NULL,
	`landingpoint` 	varchar(255) NOT NULL,
	`infoUUID` 		char(36)  NOT NULL default '00000000-0000-0000-0000-000000000000',
	`dwell` 		int(11) NOT NULL,
	`parentestate` 	int(11) NOT NULL default '1',
	`mature` 		varchar(32) NOT NULL default 'PG',
	PRIMARY KEY  (`regionUUID`,`parcelUUID`)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


-- 
-- Tabel structuur voor tabel `popularplaces`
-- 

CREATE TABLE `block_modlos_search_popularplaces` (
	`parcelUUID` 	char(36) NOT NULL,
	`name` 			varchar(255) NOT NULL,
	`dwell` 		float NOT NULL,
	`infoUUID` 		char(36) NOT NULL,
	`has_picture` 	tinyint(4) NOT NULL,
	`mature` 		tinyint(4) NOT NULL
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;


-- 
-- Tabel structuur voor tabel `regions`
-- 

CREATE TABLE `block_modlos_search_regions` (
	`regionname` 	varchar(255) NOT NULL,
	`regionuuid` 	char(36)  NOT NULL,
	`regionhandle` 	varchar(255) NOT NULL,
	`url` 			varchar(255) NOT NULL,
	`owner` 		varchar(255) NOT NULL,
	`owneruuid` 	char(36)  NOT NULL,
	PRIMARY KEY  (`regionuuid`)
) ENGINE=InnoDB ROW_FORMAT=DEFAULT;



