<?php
/*
 * Copyright (c) 2007, 2008 Contributors, http://opensimulator.org/
 * See CONTRIBUTORS for a full list of copyright holders.
 *
 * See LICENSE for the full licensing terms of this file.
 *
*/

##################### System #########################
define("SYSNAME","Opensim Webinterface Redux");
define("SYSURL","http://www.nsl.tuis.ac.jp/SL/opensimwi");
define("SYSMAIL","iseki@solar-system.tuis.ac.jp");


############ Delete Unconfirmed accounts ################
// e.g. 24 for 24 hours  leave empty for no timed delete
$unconfirmed_deltime="24";

###################### Money Settings ####################

// Key of the account that all fees go to:
$economy_sink_account="00000000-0000-0000-0000-000000000000";

// Key of the account that all purchased currency is debited from:
$economy_source_account="00000000-0000-0000-0000-000000000000";

// Minimum amount of real currency (in CENTS!) to allow purchasing:
$minimum_real=1;

// Error message if the amount is not reached:
$low_amount_error="You tried to buy less than the minimum amount of currency. You cannot buy currency for less than US$ %.2f.";

// Sets wich Pageeditor should be used:
$editor_to_use='standard';
//$editor_to_use='fckeditor';


################ Database Tables #########################
define("C_ADMIN_TBL","wi_admin");
define("C_WIUSR_TBL","wi_users");
define("C_USRBAN_TBL","wi_banned");
define("C_CODES_TBL","wi_codetable");
define("C_ADM_TBL","wi_adminsetting");
define("C_COUNTRY_TBL","wi_country");
define("C_NAMES_TBL","wi_lastnames");
define("C_CURRENCY_TBL","wi_economy_money");
define("C_TRANSACTION_TBL","wi_economy_transactions");
define("C_INFOWINDOW_TBL","wi_startscreen_infowindow");
define("C_NEWS_TBL","wi_startscreen_news");
define("C_PAGE_TBL","wi_pagemanager");
?>
