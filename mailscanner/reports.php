<?php

/*
 MailWatch for MailScanner
 Copyright (C) 2003-2011  Steve Freegard (steve@freegard.name)
 Copyright (C) 2011  Garrod Alwood (garrod.alwood@lorodoes.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//Require files
require_once('./functions.php');
require_once('./filter.inc');

// verify login
session_start();
require('login.function.php');

// Checking to see if there are any filters
if(!is_object($_SESSION["filter"])) {
 $filter = new Filter;
 $_SESSION["filter"] = $filter;
} else {
 $filter = $_SESSION["filter"];
}

// add the header information such as the logo, search, menu, ....
html_start(_("Reports"),"0",false,false);

// Set directory varible
$dirname = "".MAILWATCH_HOME."/".CACHE_DIR."";

// Add filters and save them
switch(strtolower($_GET["action"])) {
 case _("add"):
  $filter->Add($_GET["column"], $_GET["operator"], $_GET["value"]);
  break;
 case "remove":
  $filter->Remove($_GET["column"]);
  break;
 case "destroy":
  session_destroy();
  echo "Session destroyed\n";
  exit;
 case _("save"):
  if(isset($_GET['save_as'])) {
   $name = $_GET['save_as'];
  }
  if(isset($_GET['filter']) && $_GET['filter'] != "_none_") {
   $name = $_GET['filter'];
  }
  if(!empty($name)) { $filter->Save($name); }
  break;
 case _("load"):
  $filter->Load($_GET['filter']);
  break;
 case _("delete"):
  $filter->Delete($_GET['filter']);
  break;
}

// add the session filters to the variables
$_SESSION["filter"] = $filter;

$filter->AddReport("rep_message_listing.php", _("Message Listing"));
$filter->AddReport("rep_message_ops.php", _("Message Operations"));

$filter->AddReport("rep_total_mail_by_date.php", _("Total Messages by Date"));
$filter->AddReport("rep_top_mail_relays.php", _("Top Mail Relays"));

$filter->AddReport("rep_top_viruses.php", _("Top Viruses"));
$filter->AddReport("rep_viruses.php", _("Virus Report"));

$filter->AddReport("rep_top_senders_by_quantity.php", _("Top Senders by Quantity"));
$filter->AddReport("rep_top_senders_by_volume.php", _("Top Senders by Volume"));
$filter->AddReport("rep_top_recipients_by_quantity.php", _("Top Recipients by Quantity"));
$filter->AddReport("rep_top_recipients_by_volume.php", _("Top Recipients by Volume"));

//$filter->AddReport("rep_mrtg_style.php", _("MRTG Style Report"));

$filter->AddReport("rep_top_sender_domains_by_quantity.php", _("Top Sender Domains by Quantity"));
$filter->AddReport("rep_top_sender_domains_by_volume.php", _("Top Sender Domains by Volume"));
$filter->AddReport("rep_top_recipient_domains_by_quantity.php", _("Top Recipient Domains by Quantity"));
$filter->AddReport("rep_top_recipient_domains_by_volume.php", _("Top Recipient Domains by Volume"));

if(get_conf_truefalse('UseSpamAssassin')){
$filter->AddReport("rep_sa_score_dist.php", _("SpamAssassin Score Distribution"));
$filter->AddReport("rep_sa_rule_hits.php", _("SpamAssassin Rule Hits"));
}
if(get_conf_truefalse('MCPChecks')){
$filter->AddReport("rep_mcp_score_dist.php", _("MCP Score Distribution"));
$filter->AddReport("rep_mcp_rule_hits.php", _("MCP Rule Hits"));
}

$filter->AddReport("rep_audit_log.php", _("Audit Log"));
$filter->Display();

delete_dir($dirname);

// Add footer
html_end();
// Close any open db connections
dbclose();
