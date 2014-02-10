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

// Require the functions page
require_once("./functions.php");

// Start the session
session_start();
// Require the login function code
require('./login.function.php');

$url_id = $_GET['id'];

$url_id = safe_value($url_id);
$url_id = htmlentities($url_id);
$url_id = trim($url_id, " ");

$url_datetime = $_GET[datetime];

$url_datetime = safe_value($url_datetime);
$url_datetime = htmlentities($url_datetime);
$url_datetime = trim($url_datetime, " ");


// Start the header code and Title
html_start("Message Detail $url_id",0,false,false);

// Set the Memory usage
ini_set("memory_limit",MEMORY_LIMIT);

// Setting the yes and no variable
$yes = '<span class="yes">&nbsp;'._("Y").'&nbsp;</span>';
$no  = '<span class="no">&nbsp;'._("N").'&nbsp;</span>';

// Setting what Mail Transfer Agent is being used
$mta = get_conf_var('mta');

// The sql command to pull the data
$sql = "
 SELECT
  DATE_FORMAT(timestamp, '".DATE_FORMAT." ".TIME_FORMAT."') AS '"._('Received on:')."',
  hostname AS '"._('Received by:')."',
  clientip AS '"._('Received from:')."',
  headers '"._('Received Via:')."',
  id AS 'ID:',
  headers AS '"._('Message Headers:')."',
  from_address AS '"._('From:')."',
  to_address AS '"._('To:')."',
  subject AS '"._('Subject:')."',
  size AS '"._('Size:')."',
  archive AS '"._('Archive:')."',
  '"._("Anti-Virus/Dangerous Content Protection")."' AS '"._('HEADER')."',
  CASE WHEN virusinfected>0 THEN '$yes' ELSE '$no' END AS '"._('Virus:')."',
  CASE WHEN nameinfected>0 THEN '$yes' ELSE '$no' END AS '"._('Blocked File:')."',
  CASE WHEN otherinfected>0 THEN '$yes' ELSE '$no' END AS '"._('Other Infection:')."',
  report AS '"._('Report:')."',
  'SpamAssassin' AS '"._('HEADER')."',
  CASE WHEN isspam>0 THEN '$yes' ELSE '$no' END AS '"._('Spam:')."',
  CASE WHEN ishighspam>0 THEN '$yes' ELSE '$no' END AS '"._('High Scoring Spam:')."',
  CASE WHEN issaspam>0 THEN '$yes' ELSE '$no' END AS '"._('SpamAssassin Spam:')."',
  CASE WHEN isrblspam>0 THEN '$yes' ELSE '$no' END AS '"._('Listed in RBL:')."',
  CASE WHEN spamwhitelisted>0 THEN '$yes' ELSE '$no' END AS '"._('Spam Whitelisted:')."',
  CASE WHEN spamblacklisted>0 THEN '$yes' ELSE '$no' END AS '"._('Spam Blacklisted:')."',
  spamreport AS '"._('SpamAssassin Autolearn:')."',
  sascore AS '"._('SpamAssassin Score:')."',
  spamreport AS '"._('Spam Report:')."',
  '"._("Message Content Protection (MCP)")."' AS '"._('HEADER')."',
  CASE WHEN ismcp>0 THEN '$yes' ELSE '$no' END AS '"._('MCP:')."',
  CASE WHEN ishighmcp>0 THEN '$yes' ELSE '$no' END AS '"._('High Scoring MCP:')."',
  CASE WHEN issamcp>0 THEN '$yes' ELSE '$no' END AS '"._('SpamAssassin MCP:')."',
  CASE WHEN mcpwhitelisted>0 THEN '$yes' ELSE '$no' END AS '"._('MCP Whitelisted:')."',
  CASE WHEN mcpblacklisted>0 THEN '$yes' ELSE '$no' END AS '"._('MCP Blacklisted:')."',
  mcpsascore AS '"._('MCP Score:')."',
  mcpreport AS '"._('MCP Report:')."'
 FROM
  maillog
 WHERE
  ".$_SESSION['global_filter']."
 AND
  id = '".$url_id."'
";

if (isset($url_datetime) && $url_datetime != '') {
 $sql .= " AND
  timestamp = STR_TO_DATE('".$url_datetime."', '".DATE_FORMAT." ".TIME_FORMAT."')
";
}

// Pull the data back and put it in the the $result variable
$result = dbquery($sql);

// Check to make sure something was returned
if(mysql_num_rows($result) == 0) {
 die((sprintf(_("Message ID '%s' not found!"), $url_id))."\n </TABLE>");
} else {
 audit_log('Viewed message detail (id='.$url_id.')');
}

echo '<table class="maildetail" border="0" cellspacing="1" cellpadding="1" width="100%">'."\n";
while($row=mysql_fetch_array($result,MYSQL_BOTH)) {
 $listurl = "lists.php?host=".$row['Received from:']."&amp;from=".$row['From:']."&amp;to=".$row['To:'];
 for($f=0; $f<mysql_num_fields($result); $f++) {
  $fieldn = mysql_field_name($result,$f);
  if ($fieldn == _("Received from:")) {
   $output = "<table class=\"sa_rules_report\" width=\"100%\" cellspacing=0 cellpadding=0><tr><td>".$row[$f]."</td>";
   if(LISTS) { $output .= "<td align=\"right\">[<a href=\"$listurl&amp;type=h&amp;list=w\">"._("Add to Whitelist")."</a>&nbsp|&nbsp;<a href=\"$listurl&amp;type=h&amp;list=b\">"._("Add to Blacklist")."</a>]</td>";
   }
   $output .= "</tr></table>\n";
   $row[$f] = $output;
  }
  if ($fieldn == _("Received Via:")) {
   // Start Table
   $output = '<table width="100%" class="sa_rules_report">'."\n";
   $output .= ' <tr>'."\n";
   $output .= ' <th>'._("IP Address").'</th>'."\n";
   $output .= ' <th>'._("Hostname").'</th>'."\n";
   $output .= ' <th>'._("Country").'</th>'."\n";
   $output .= ' <th>RBL</th>'."\n";
   $output .= ' <th>'._("Spam").'</th>'."\n";
   $output .= ' <th>'._("Virus").'</th>'."\n";
   $output .= ' <th>'._("All").'</th>'."\n";
   $output .= ' </tr>'."\n";
   if(is_array(($relays = get_mail_relays($row[$f])))) {
    foreach($relays as $relay) {
     $output .= ' <tr>'."\n";
     $output .= ' <td>'.$relay.'</td>'."\n";
     // Reverse lookup on address. Possibly need to remove it.
     if(($host=gethostbyaddr($relay)) <> $relay) {
      $output .= ' <td>'.$host.'</td>'."\n";
     } else {
      $output .= ' <td>('._("Reverse Lookup Failed").')</td>'."\n";
     }
     // Do GeoIP lookup on address
     if($geoip_country=return_geoip_country($relay)) {
      $output .= ' <td>'.$geoip_country.'</td>'."\n";
     } else {
      $output .= ' <td>(GeoIP Lookup Failed)</td>'."\n";
     }
     // Link to RBL Lookup
     $output .= ' <td align="center">[<a href="http://www.mxtoolbox.com/SuperTool.aspx?action=blacklist:'.$relay.'">&nbsp;&nbsp;</a>]</td>'."\n";
     // Link to Spam Report for this relay
     $output .= ' <td align="center">[<a href="rep_message_listing.php?relay='.$relay.'&amp;isspam=1">&nbsp;&nbsp;</a>]</td>'."\n";
     // Link to Virus Report for this relay
     $output .= ' <td align="center">[<a href="rep_message_listing.php?relay='.$relay.'&amp;isvirus=1">&nbsp;&nbsp;</a>]</td>'."\n";
     // Link to All Messages Report for this relay
     $output .= ' <td align="center">[<a href="rep_message_listing.php?relay='.$relay.'">&nbsp;&nbsp;</a>]</td>'."\n";
     // Close table
     $output .= ' </tr>'."\n";
    }
    $output .= '</table>'."\n";
    $row[$f] = $output;
   } else {
    $row[$f] = "127.0.0.1";  // Must be local mailer (Exim)
   }
  }
  if ($fieldn == _("Report:")) {
   $row[$f] = nl2br(str_replace(",","<br>",htmlspecialchars($row[$f])));
   $row[$f] = preg_replace("/<br \/>/","<br>",$row[$f]);
  }
  if ($fieldn == "From:") {
   $row[$f] = htmlentities($row[$f]);
   $output = '<table class="sa_rules_report" cellspacing="0"><tr><td>'.$row[$f].'</td>'."\n";
   if(LISTS) { $output .= '<td align="right">[<a href="'.$listurl.'&amp;type=f&amp;list=w">'._("Add to Whitelist").'</a>&nbsp|&nbsp;<a href="'.$listurl.'&amp;type=f&amp;list=b">'._("Add to Blacklist").'</a>]</td>'."\n";
   }
   $output .= '</tr></table>'."\n";
   $row[$f] = $output;
  }
  if ($fieldn == _("To:") || $fieldn == _("Subject:")) {
   //090904 add -- begin
   /*
   if ($charset == "") {
    $row[$f] = mb_convert_encoding($row[$f], "UTF-8", check_locale());
   } else {
    $row[$f] = mb_convert_encoding($row[$f], "UTF-8", $charset);
   }
   */
   //090904 add -- end
   //$row[$f] = htmlspecialchars($row[$f]);
  }
  if ($fieldn == _("To:")) {
   $row[$f] = htmlspecialchars($row[$f]);
   $row[$f] = str_replace(",","<br>",$row[$f]);
  }
  if ($fieldn == _("Subject:")) {
    /*
   $row[$f] = decode_header($row[$f]);
   if (function_exists ('mb_check_encoding')) {
    if (! mb_check_encoding ($row[$f], 'UTF-8')) {
     $row[$f] = mb_convert_encoding($row[$f], 'UTF-8');
    }
    */
   if (strtolower($charset[0]) != "utf-8") {
    if ($charset[1] == 0) {
     $row[$f] = mb_convert_encoding($row[$f],"UTF-8",$charset[0]);
    } else {
     $row[$f] = mb_convert_encoding($row[$f],"UTF-8",check_locale());
    }
   } else {
    //$row[$f] = utf8_encode ($row[$f]);
    if ($charset[1] == 0) {
     $row[$f] = decode_header($fix_subject);//."test5";
    } else {
     $row[$f] = mb_convert_encoding($row[$f],"UTF-8",check_locale());//." test6";
    }
   }
   $row[$f] = htmlspecialchars($row[$f]);
  }
  if ($fieldn == "Spam Report:") {
   $row[$f] = format_spam_report($row[$f]);
  }
  if ($fieldn == "Size:") {
   $row[$f] = format_mail_size($row[$f]);
  }
  if ($fieldn == _("Message Headers:")) {
   //$row[$f] = nl2br(str_replace(array("\\n","\t"),array("<br>","&nbsp; &nbsp; &nbsp;"),htmlentities($row[$f])));
   //$row[$f] = preg_replace("/<br \/>/","<br>",$row[$f]);
   $charset = detect_charset($row[$f]);
   $fix_subject = fix_utf8_subject($row[$f]);
   if ($charset[0] == "") {
    $row[$f] = nl2br(str_replace(array("\\n","\t"),array("<BR>","&nbsp; &nbsp; &nbsp;"),htmlspecialchars($row[$f])));
    //$row[$f] = preg_replace("/<br \/>/","<BR>",mb_convert_encoding(htmlspecialchars($row[$f]),"UTF-8",check_locale()));
    $row[$f] = preg_replace("/<br \/>/","<BR>",mb_convert_encoding($row[$f],"UTF-8",check_locale()));
   } else {
    $row[$f] = nl2br(str_replace(array("\\n","\t"),array("<BR>","&nbsp; &nbsp; &nbsp;"),htmlspecialchars($row[$f])));
    //$row[$f] = preg_replace("/<br \/>/","<BR>",mb_convert_encoding(htmlspecialchars($row[$f]),"UTF-8",$charset[0]));
    $row[$f] = preg_replace("/<br \/>/","<BR>",mb_convert_encoding($row[$f],"UTF-8",$charset[0]));
   }
  }
  if ($fieldn == _("SpamAssassin Autolearn:")) {
   if(($autolearn = sa_autolearn($row[$f]))!==false) {
    $row[$f] = $yes." ($autolearn)";
   } else {
    $row[$f] = $no;
   }
  }
  if ($fieldn == _("Spam:") && !DISTRIBUTED_SETUP) {
   // Display actions if spam/not-spam
   if($row[$f] == $yes) {
    $row[$f] = $row[$f]."&nbsp;&nbsp;Action(s): ".str_replace(" ",", ",get_conf_var("SpamActions"));
   } else {
    $row[$f] = $row[$f]."&nbsp;&nbsp;Action(s): ".str_replace(" ",", ",get_conf_var("NonSpamActions"));
   }
  }
  if ($fieldn == _("High Scoring Spam:") && $row[$f] == $yes) {
   // Display actions if high-scoring
   $row[$f] = $row[$f]."&nbsp;&nbsp;Action(s): ".str_replace(" ",", ",get_conf_var("HighScoringSpamActions"));
  }
  if ($fieldn == _("MCP Report:")) {
   $row[$f] = format_mcp_report($row[$f]);
  }
  // Handle dummy header fields
  if(mysql_field_name($result,$f)==_('HEADER')) {
   // Display header
   echo '<tr><td class="heading" align="center" valign="top" colspan="2">'.$row[$f].'</td></tr>'."\n";
  } else {
   // Actual data
   if(!empty($row[$f])) {
    // Skip empty rows (notably Spam Report when SpamAssassin didn't run)
    echo '<tr><td class="heading-w175">'.mysql_field_name($result,$f).'</td><td class="detail">'.$row[$f].'</td></tr>'."\n";
   }
  }
 }
}

// Display the relay information only if there are matching
// rows in the relay table (maillog.id = relay.msg_id)...
  $sqlcheck = "Show tables like 'mtalog_ids'";
  $tablecheck = dbquery($sqlcheck);  
if ($mta == 'postfix' && mysql_num_rows($tablecheck) > 0){ //version for postfix
$sql1 = "
 SELECT
  DATE_FORMAT(m.timestamp,'".DATE_FORMAT." ".TIME_FORMAT."') AS 'Date/Time',
  m.host AS '"._("Relayed by")."',
  m.relay AS '"._("Relayed to")."',
  m.delay AS '"._("Delay")."',
  m.status AS '"._("Status")."'
 FROM
  mtalog AS m
	LEFT JOIN mtalog_ids AS i ON (i.smtp_id = m.msg_id)
 WHERE
  i.smtpd_id='".$url_id."'
 AND
  m.type='relay'
 ORDER BY
  m.timestamp DESC";
}else{ //version for sendmail
$sql1 = "
 SELECT
  DATE_FORMAT(timestamp,'".DATE_FORMAT." ".TIME_FORMAT."') AS 'Date/Time',
  host AS 'Relayed by',
  relay AS 'Relayed to',
  delay AS 'Delay',
  status AS 'Status'
 FROM
  mtalog
 WHERE
  msg_id='".$url_id."'
 AND
  type='relay'
 ORDER BY
  timestamp DESC";
}

$sth1 = dbquery($sql1);
if(mysql_num_rows($sth1) > 0) {
 // Display the relay table entries
 echo ' <tr><td class="heading-w175">'._("Relay Information").':</td><td class="detail">'."\n";
 echo '  <table class="sa_rules_report" width="100%">'."\n";
 echo '   <tr>'."\n";
 for($f=0;$f<mysql_num_fields($sth1);$f++) {
  echo '   <th>'.mysql_field_name($sth1, $f).'</th>'."\n";
 }
 echo "   </tr>\n";
 while($row=mysql_fetch_row($sth1)) {
  echo '    <tr>'."\n";
  echo '     <td class="detail" align="left">'.$row[0].'</td>'."\n"; // Date/Time
  echo '     <td class="detail" align="left">'.$row[1].'</td>'."\n"; // Relayed by
  if(($lhost = @gethostbyaddr($row[2])) <> $row[2]) {
   echo '     <td class="detail" align="left">'.$lhost.'</td>'."\n"; // Relayed to
  } else {
   echo '     <td class="detail" align="left">'.$row[2],'</td>'."\n";
  }
  echo '     <td class="detail">'.$row[3].'</td>'.	"\n"; // Delay
  echo '     <td class="detail">'.$row[4].'</td>'."\n"; // Status
  echo '    </tr>'."\n";
 }
 echo "  </table>\n";
 echo " </td></tr>\n";
}
echo "</table>\n";

flush();

$quarantinedir = get_conf_var('QuarantineDir');
$quarantined = quarantine_list_items($url_id,RPC_ONLY);
if((is_array($quarantined)) && (count($quarantined)>0)) {
 echo "<br>\n";

 if($_GET['submit'] == "Submit") {
  debug("submit branch taken");
  // Reset error status
  $error=0;
  // Release
  if(isset($_GET['release'])) {
   // Send to the original recipient(s) or to an alternate address
   if(($_GET['alt_recpt_yn'] == "y")) {
    $to = $_GET['alt_recpt'];
	$to = htmlentities($to);
   } else {
    $to = $quarantined[0]['to'];
   }
   $status[] = quarantine_release($quarantined,$_GET['release'],$to,RPC_ONLY);
  }
  // sa-learn
  if(isset($_GET['learn'])) {
  $status[] = quarantine_learn($quarantined,$_GET['learn'],$_GET['learn_type'],RPC_ONLY);
  }
  // Delete
  if(isset($_GET['delete'])) {
   $status[] = quarantine_delete($quarantined,$_GET['delete'],RPC_ONLY);
  }
  echo '<table border="0" cellpadding="1" cellspacing="1" width="100%" class="maildetail">'."\n";
  echo ' <tr>'."\n";
  echo '  <th colspan="2">'._("Quarantine Command Results").'</th>'."\n";
  echo ' </tr>'."\n";
  if(isset($status)) {
   echo '  <tr>'."\n";
   echo '  <td class="heading" width="150" align="right" valign="top">'._("Result Messages").':</td>'."\n";
   echo '  <td class="detail">'."\n";
   foreach($status as $key=>$val) {
    echo "  $val<br>\n";
   }
   echo "  </td>\n";
   echo " </tr>\n";
  }
  if(isset($errors)) {
   echo " <tr>\n";
   echo '  <td class="heading" width="150" align="right" valign="top">'._("Error Messages").':</td>'."\n";
   echo '  <td class="detail">'."\n";
   foreach($errors as $key=>$val) {
    echo "  $val<br>\n";
   }
   echo "  </td>\n";
   echo " <tr>\n";
  }
  echo " <tr>\n";
  echo '  <td class="heading" width="150" align="right" valign="top">'._("Error").':</td>'."\n";
  echo '  <td class="detail">'.($error?$yes:$no).'</td>'."\n";
  echo ' </tr>'."\n";
  echo '</table>'."\n";
 } else {
  echo '<form action="'.$_SERVER['PHP_SELF'].'" name="quarantine">'."\n";
  echo '<table cellspacing="1" width="100%" class="mail">'."\n";
  echo ' <tr>'."\n";
  echo '  <th colspan="7">'._("Quarantine").'</th>'."\n";
  echo ' </tr>'."\n";
  echo ' <tr>'."\n";
  echo '  <th>'._("Release").'</th>'."\n";
  echo '  <th>'._("Delete").'</th>'."\n";
  echo '  <th>'._("SA Learn").'</th>'."\n";
  echo '  <th>'._("File").'</th>'."\n";
  echo '  <th>'._("Type").'</th>'."\n";
  echo '  <th>'._("Path").'</th>'."\n";
  echo '  <th>'._("Dangerous").'?</th>'."\n";
  echo ' </tr>'."\n";
  foreach($quarantined as $item) {
   echo " <tr>\n";
   // Don't allow message to be released if it is marked as 'dangerous'
   // Currently this only applies to messages that contain viruses.
   if($item['dangerous'] !== "Y" || $_SESSION['user_type'] == 'A') {
    echo '  <td align="center"><input type="checkbox" name="release[]" value="'.$item['id'].'"></td>'."\n";
   } else {
    echo '<td>&nbsp;&nbsp;</td>'."\n";
   }
   echo '  <td align="center"><input type="checkbox" name="delete[]" value="'.$item['id'].'"></td>'."\n";
   // If the file is an rfc822 message then allow the file to be learnt
   // by SpamAssassin Bayesian learner as either spam or ham (sa-learn).
   if(preg_match('/message\/rfc822/',$item['type']) || $item['file'] == "message" && (strtoupper(get_conf_var("UseSpamAssassin")) == "YES")) {
    echo '   <td align="center"><input type="checkbox" name="learn[]" value="'.$item['id'].'"><select name="learn_type"><option value="ham">'._("As Ham").'</option><option value="spam">'._("As Spam").'</option><option value="forget">'._("Forget").'</option><option value="report">'._("As Spam+Report").'</option><option value="revoke">'._("As Ham+Revoke").'</option></select></td>'."\n";
   } else {
    echo '   <td>&nbsp&nbsp</td>'."\n";
   }
   //echo '  <td>'.$item['file'].'</td>'."\n";
   //110708 James -- begin
   if (strtolower($charset[0]) == "") {
    echo "  <TD>".mb_convert_encoding(urldecode($item['file']),"UTF-8",check_locale())."</TD>\n";
   } else {
    echo "  <TD>".mb_convert_encoding(urldecode($item['file']),"UTF-8",$charset[0])."</TD>\n";
   }
   //110708 James -- end
   echo '  <td>'.$item['type'].'</td>'."\n";
   // If the file is in message/rfc822 format and isn't dangerous - create a link to allow it to be viewed
   if(($item['dangerous'] == "N" || $_SESSION['user_type'] == 'A') && preg_match('!message/rfc822!',$item['type'])) {
    echo '  <td><a href="viewmail.php?id='.$item['msgid'].'&amp;filename='.substr($item['path'],strlen($quarantinedir)+1).'">'.substr($item['path'],strlen($quarantinedir)+1).'</a></td>'."\n";
   } else {
    //echo "  <td>".substr($item['path'],strlen($quarantinedir)+1)."</td>\n";
    //110708 James -- begin   
    if (strtolower($charset[0]) == "") {
     echo "  <TD>".mb_convert_encoding(urldecode(substr($item['path'],strlen($quarantinedir)+1)),"UTF-8",check_locale())."</TD>\n";
    } else {
     echo "  <TD>".mb_convert_encoding(urldecode(substr($item['path'],strlen($quarantinedir)+1)),"UTF-8",$charset[0])."</TD>\n";
    }
    //110708 James -- end
   }
   if($item['dangerous'] == "Y" && $_SESSION['user_type'] != 'A') {
    $dangerous = $yes;
   } else {
    $dangerous = $no;
   }
   echo '  <td align="center">'.$dangerous.'</td>'."\n";
   echo ' </tr>'."\n";
  }
  echo ' <tr>'."\n";
  if($item['dangerous'] == "Y" && $_SESSION['user_type'] != 'A') {
   echo '  <td colspan="6">&nbsp</td>'."\n";
  } else {
   echo '  <td colspan="6"><input type="checkbox" name="alt_recpt_yn" value="y">&nbsp;'._("Alternate Recipient(s)").':&nbsp;<input type="TEXT" name="alt_recpt" size="100"></td>'."\n";
  }
  echo '  <td align="right">'."\n";
  echo '<input type="HIDDEN" name="id" value="'.$quarantined[0]['msgid'].'">'."\n";
  echo '<input type="SUBMIT" name="submit" value="Submit">'."\n";
  echo '  </td></tr>'."\n";
  echo '</table>'."\n";
  echo '</form>'."\n";
 }
} else {
 
 // Error??
 if(!is_array($quarantined)) {
  echo '<br>'.$quarantined.'';
 }
}

// Add footer
html_end();
// Close any open db connections
dbclose();
