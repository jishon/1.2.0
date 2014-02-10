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

// Include of nessecary functions
require_once("./functions.php");

// Authenication checking
session_start();
require('login.function.php');

html_start(_('Tools'),"0",false,false);

echo '<table width="100%" class="boxtable">
 <tr>
  <td>
   <p>'._("Tools").'</p>
   <ul>';
if (!MSEE) {
    echo '<li><a href="user_manager.php">'._("User Management").'</a>';
}
if (preg_match('/sophos/i', get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A') {
    echo '<li><a href="sophos_status.php">'._("Sophos Status").'</a>';
}
if (preg_match('/f-secure/i', get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A') {
    echo '<li><a href="f-secure_status.php">'._("F-Secure Status").'</a>';
}
if (preg_match('/clam/i', get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A') {
    echo '<li><a href="clamav_status.php">'._("ClamAV Status").'</a>';
}
if (preg_match('/mcafee/i', get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A') {
    echo '<li><a href="mcafee_status.php">'._("McAfee Status").'</a>';
}
if (preg_match('/f-prot/i', get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A') {
    echo '<li><a href="f-prot_status.php">'._("F-Prot Status").'</a>';
}
if ($_SESSION['user_type'] == 'A') {
    echo '<li><a href="mysql_status.php">'._("MySQL Database Status").'</a>';
    echo '<li><a href="msconfig.php">'._("View MailScanner Configuration").'</a>';
}
if (!DISTRIBUTED_SETUP
    && !in_array(strtolower(get_conf_var('UseSpamAssassin')), array('0', 'no', false))
    && $_SESSION['user_type'] == 'A'
) {
    echo '
     <li><a href="bayes_info.php">'._("SpamAssassin Bayes Database Info").'</a>
     <li><a href="sa_lint.php">'._("SpamAssassin Lint (Test)").'</a>
     <li><a href="ms_lint.php">'._("MailScanner Lint (Test)").'</a>
     <li><a href="sa_rules_update.php">'._("Update SpamAssasin Rule Descriptions").'</a>';
}
if (!DISTRIBUTED_SETUP && get_conf_truefalse('MCPChecks') && $_SESSION['user_type'] == 'A') {
    echo '<li><a href="mcp_rules_update.php">'._("Update MCP Rule Descriptions").'</a>';
}
if ($_SESSION['user_type'] == 'A') {
    echo '<li><a href="geoip_update.php">'._("Update GeoIP Database").'</a>';
}
echo '</ul>';
if ($_SESSION['user_type'] == 'A') {
    echo '
   <p>'._("Links").'</p>
   <ul>
    <li><a href="http://mailwatch.sourceforge.net">MailWatch for MailScanner</a>
    <li><a href="http://www.mailscanner.info">MailScanner</a>';

    if (get_conf_truefalse('UseSpamAssassin')) {
        echo '<li><a href="http://www.spamassassin.org">SpamAssassin</a>';
    }

    if (preg_match('/sophos/i', get_conf_var('VirusScanners'))) {
        echo '<li><a href="http://www.sophos.com">Sophos</a>';
    }

    if (preg_match('/clam/i', get_conf_var('VirusScanners'))) {
        echo '<li><a href="http://clamav.sourceforge.net">ClamAV</A>';
    }

    echo '
    <li><a href="http://www.dnsstuff.com">DNSstuff</a>
    <li><a href="http://mxtoolbox.com/NetworkTools.aspx">MXToolbox Network Tools</a>
    <li><a href="http://www.anti-abuse.org/multi-rbl-check/">Multi-RBL Check</a>
   </ul>';
}

echo '
   </td>
 </tr>
</table>';

// Add footer
html_end();
// Close any open db connections
dbclose();
