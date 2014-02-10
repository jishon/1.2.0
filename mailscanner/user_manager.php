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

require_once("./functions.php");

session_start();
require('login.function.php');

html_start(_("User Manager"),0,false,false);

// Don't run on DefenderMX
if(MSEE) {
 echo _("This utility is not used in DefenderMX")."\n";
 html_end();
 exit;
}

if($_SESSION['user_type'] == 'A')
{
?>
<script language="JavaScript" type="text/javascript">
<!--
function delete_user(id) {
var yesno = confirm("<?php echo _("Are you sure you want to delete user ");?>" + id + "?");
if(yesno == true) {
window.location = "?action=delete&id="+id;
} else {
return false;
}
}

function delete_filter(id,filter) {
var yesno = confirm("<?php echo _("Are you sure?");?>");
if(yesno == true) {
window.location="?action=filters&id="+id+"&filter="+filter+"&delete=true";
} else {
return false;
}
}

function change_state(id,filter) {
var yesno = confirm("<?php echo _("Are you sure?");?>");
if(yesno == true) {
window.location="?action=filters&id="+id+"&filter="+filter+"&change_state=true";
} else {
return false;
}
}
-->
</script>
<?php

switch($_GET['action']) {
case 'new':
if(!isset($_GET['submit'])) {
echo "<FORM METHOD=\"GET\" ACTION=\"user_manager.php\">\n";
echo "<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"new\">\n";
echo "<INPUT TYPE=\"HIDDEN\" NAME=\"submit\" VALUE=\"true\">\n";
echo "<TABLE CLASS=\"mail\" BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\">\n";
   print $_SESSION['use_pam']."\n";
   echo " <TR><TD CLASS=\"heading\" COLSPAN=\"2\" ALIGN=\"CENTER\">"._("New User")."  <br> ";
   if ($_SESSION['use_pam'] != '1') {
   echo _("For all users other than Administrator you must use an email address for the username");
   }
   echo "</TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Username").": <BR></TD><TD><INPUT TYPE=\"TEXT\" NAME=\"username\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Name").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"fullname\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password1\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("User Type").":</TD>
    <TD><SELECT NAME=\"type\">
         <OPTION VALUE=\"U\">"._("User")."
         <OPTION VALUE=\"D\">"._("Domain Administrator")."
         <OPTION VALUE=\"A\">"._("Administrator")."
        </SELECT></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Quarantine Report").":</TD><TD><INPUT TYPE=\"CHECKBOX\" NAME=\"quarantine_report\"> <font size=-2>"._("Send Daily Report?")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Quarantine Report Recipient").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"quarantine_rcpt\"><br><font size=\"-2\">"._("Override quarantine report recipient?<BR>(uses your username if blank)")."</font></TD>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Scan for Spam").":</TD><TD><INPUT TYPE=\"CHECKBOX\" NAME=\"noscan\" CHECKED> <font size=\"-2\">"._("Scan e-mail for Spam?")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Spam Score").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"spamscore\" VALUE=\"0\" size=\"4\"> <font size=\"-2\">0="._("Use Default")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("High Spam Score").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"highspamscore\" VALUE=\"0\" size=\"4\"> <font size=\"-2\">0="._("Use Default")."</font></TD></TR>\n";
   echo "<TR><TD CLASS=\"heading\">"._("Action").":</TD><TD><INPUT TYPE=\"RESET\" VALUE="._("Reset").">&nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" VALUE="._("Create")."></TD></TR>\n";
   echo "</TABLE></FORM><BR>\n";
  } else {
if($_GET['password'] != $_GET['password1']){

echo _("Passwords do not match");
}
else{

   $n_username    = mysql_real_escape_string($_GET['username']);
   $n_fullname    = mysql_real_escape_string($_GET['fullname']);
   $n_password    = mysql_real_escape_string(md5($_GET['password']));
   $n_type        = mysql_real_escape_string($_GET['type']);
   $spamscore     = mysql_real_escape_string($_GET['spamscore']);
   $highspamscore = mysql_real_escape_string($_GET['highspamscore']);
   if(!isset($_GET['quarantine_report'])) {
    $quarantine_report = '0';
   } else {
    $quarantine_report = '1';
   }
   if(!isset($_GET['noscan'])) {
    $noscan = '1';
   } else {
    $noscan = '0';
   }
   $quarantine_rcpt = mysql_real_escape_string($_GET['quarantine_rcpt']);
   $sql = "INSERT INTO users (username, fullname, password, type, quarantine_report, spamscore, highspamscore, noscan, quarantine_rcpt) VALUES ('$n_username','$n_fullname','$n_password','$n_type','$quarantine_report','$spamscore','$highspamscore','$noscan','$quarantine_rcpt')";
   dbquery($sql);
   switch($n_type) {
    case 'A':
     $n_typedesc = _("administrator");
     break;
    case 'D':
     $n_typedesc = _("domain administrator");
     break;
    default:
     $n_typedesc = _("user");
     break;
   }
   audit_log("New ".$n_typedesc." '".$n_username."' (".$n_fullname.") created");
  }
}
  break;
 case 'edit':
  if(!isset($_GET['submit'])) {
   $sql = "SELECT username, fullname, type, quarantine_report, quarantine_rcpt, spamscore, highspamscore, noscan FROM users WHERE username='".$_GET['id']."'";
   $result = dbquery($sql);
   $row = mysql_fetch_object($result);
   if($row->quarantine_report == 1) { $quarantine_report = "CHECKED"; }
   if($row->noscan == 0) { $noscan = "CHECKED"; }
   $s[$row->type] = "SELECTED";
   echo "<FORM METHOD=\"GET\" ACTION=\"user_manager.php\">\n";
   echo "<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"edit\">\n";
   echo "<INPUT TYPE=\"HIDDEN\" NAME=\"key\" VALUE=\"".$row->username."\">\n";
   echo "<INPUT TYPE=\"HIDDEN\" NAME=\"submit\" VALUE=\"true\">\n";
   echo "<TABLE CLASS=\"mail\" BORDER=0 CELLPADDING=1 CELLSPACING=1>\n";
   echo " <TR><TD CLASS=\"heading\" COLSPAN=2 ALIGN=\"CENTER\">"._("Edit User")." ".$row->username."</TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Username").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"username\" VALUE=\"".$row->username."\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Name").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"fullname\" VALUE=\"".$row->fullname."\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password\" VALUE=\"XXXXXXXX\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password1\" VALUE=\"XXXXXXXX\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("User Type").":</TD>
    <TD><SELECT NAME=\"type\">
         <OPTION ".$s["A"]." VALUE=\"A\">"._("Administrator")."
         <OPTION ".$s["D"]." VALUE=\"D\">"._("Domain Administrator")."
         <OPTION ".$s["U"]." VALUE=\"U\">"._("User")."
         <OPTION ".$s["R"]." VALUE=\"R\">"._("User (Regexp)")."
        </SELECT></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Quarantine Report").":</TD><TD><INPUT TYPE=\"CHECKBOX\" NAME=\"quarantine_report\" $quarantine_report> <font size=-2>"._("Send Daily Report?")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Quarantine Report Recipient").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"quarantine_rcpt\" VALUE=\"".$row->quarantine_rcpt."\"><br><font size=\"-2\">"._("Override quarantine report recipient?<br>(uses your username if blank)")."</font></TD>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Scan for Spam").":</TD><TD><INPUT TYPE=\"CHECKBOX\" NAME=\"noscan\" $noscan> <font size=\"-2\">"._("Scan eMail for Spam?")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Spam Score").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"spamscore\" VALUE=\"".$row->spamscore."\" size=\"4\"> <font size=\"-2\">0="._("Use Default")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("High Spam Score").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"highspamscore\" VALUE=\"".$row->highspamscore."\" size=\"4\"> <font size=\"-2\">0="._("Use Default")."</font></TD></TR>\n";
   echo "<TR><TD CLASS=\"heading\">"._("Action").":</TD><TD><INPUT TYPE=\"RESET\" VALUE="._("Reset").">&nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" VALUE="._("Update")."></TD></TR>\n";
   echo "</TABLE></FORM><BR>\n";
   $sql = "SELECT filter, active FROM user_filters WHERE username='".$row->username."'";
   $result = dbquery($sql);
  } else {
   // Do update
   if($_GET['password'] != $_GET['password1']){

   echo "Passwords do not match";
   }
   else{
   $do_pwd = false;
   $key        = mysql_real_escape_string($_GET['key']);
   $n_username = mysql_real_escape_string($_GET['username']);
   $n_fullname = mysql_real_escape_string($_GET['fullname']);
   $n_password = mysql_real_escape_string(md5($_GET['password']));
   $n_type     = mysql_real_escape_string($_GET['type']);
   $spamscore     = mysql_real_escape_string($_GET['spamscore']);
   $highspamscore = mysql_real_escape_string($_GET['highspamscore']);
   if(!isset($_GET['quarantine_report'])) {
    $n_quarantine_report = '0';
   } else {
    $n_quarantine_report = '1';
   }
   if(!isset($_GET['noscan'])) {
    $noscan = '1';
   } else {
    $noscan = '0';
   }
   $quarantine_rcpt = mysql_real_escape_string($_GET['quarantine_rcpt']);

   // Record old user type to audit user type promotion/demotion
   $o_type = mysql_result(dbquery("SELECT type FROM users WHERE username='$key'"),0);

   if($_GET['password'] !== 'XXXXXXXX') {
    // Password reset required
    $sql = "UPDATE users SET username='$n_username', fullname='$n_fullname', password='$n_password', type='$n_type', quarantine_report='$n_quarantine_report', spamscore='$spamscore', highspamscore='$highspamscore', noscan='$noscan', quarantine_rcpt='$quarantine_rcpt' WHERE username='$key'";
    dbquery($sql);
   } else {
    $sql = "UPDATE users SET username='$n_username', fullname='$n_fullname', type='$n_type', quarantine_report='$n_quarantine_report', spamscore='$spamscore', highspamscore='$highspamscore', noscan='$noscan', quarantine_rcpt='$quarantine_rcpt' WHERE username='$key'";
    dbquery($sql);
   }

   // Audit
   $type['A'] = "administrator";
   $type['D'] = "domain administrator";
   $type['U'] = "user";
   $type['R'] = "user";
   if($o_type <> $n_type) {
    audit_log("User type changed for user '".$n_username."' (".$n_fullname.") from ".$type[$o_type]." to ".$type[$n_type]);
   }
  }
  }
  break;
 case 'delete':
  if(isset($_GET['id'])) {
   $sql = "DELETE FROM users WHERE username='".$_GET['id']."'";
   dbquery($sql);
   audit_log("User '".$_GET['id']."' deleted");
  }
  break;
 case 'filters':
  if(isset($_GET['new'])) {
   $sql = "INSERT INTO user_filters (username, filter, active) VALUES ('".$_GET['id']."','".$_GET['filter']."','".$_GET['active']."')";
   dbquery($sql);
   if(DEBUG == 'true') echo $sql;
  }
  if(isset($_GET['delete'])) {
   $sql = "DELETE FROM user_filters WHERE username='".$_GET['id']."' AND filter='".$_GET['filter']."'";
   dbquery($sql);
   if(DEBUG == 'true') echo $sql;
  }
  if(isset($_GET['change_state'])) {
   $sql = "SELECT active FROM user_filters WHERE username='".$_GET['id']."' AND filter='".$_GET['filter']."'";
   $active = mysql_fetch_row(dbquery($sql));
   $active = $active[0];
   if($active == 'Y') {
    $sql = "UPDATE user_filters SET active='N' WHERE username='".$_GET['id']."' AND filter='".$_GET['filter']."'";
    dbquery($sql);
   } else {
    $sql = "UPDATE user_filters SET active='Y' WHERE username='".$_GET['id']."' AND filter='".$_GET['filter']."'";
    dbquery($sql);
   }
  }
  $sql = "SELECT filter, CASE WHEN active='Y' THEN 'Yes' ELSE 'No' END AS active, CONCAT('<a href=\"javascript:delete_filter\(\'".$_GET['id']."\',\'',filter,'\'\)\">"._("Delete")."</a>&nbsp;&nbsp<a href=\"javascript:change_state(\'".$_GET['id']."\',\'',filter,'\')\">"._("Activate/Deactivate")."</a>') AS actions FROM user_filters WHERE username='".$_GET['id']."'";
  $result = dbquery($sql);
  echo "<FORM METHOD=\"GET\" ACTION=\"user_manager.php\">\n";
  echo "<INPUT TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".$_GET['id']."\">\n";
  echo "<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"filters\">\n";
  echo "<TABLE CLASS=\"mail\" BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\">\n";
  echo " <TR><TH COLSPAN=3>".sprintf(_("User Filters for %s"), $_GET['id'])."</TH></TR>\n";
  echo " <TR><TH>"._("Filter")."</TH><TH>"._("Active")."</TH><TH>"._("Actions")."</TH></TR>\n";
  if(mysql_num_rows($result)>0) {
   while($row = mysql_fetch_object($result)) {
    echo " <TR><TD>$row->filter</TD><TD>"._("$row->active")."</TD><TD>"._("$row->actions")."</TD></TR>\n";
   }
  }
  echo " <TR><TD><INPUT TYPE=\"text\" NAME=\"filter\"></TD><TD><SELECT NAME=\"active\"><OPTION VALUE=\"Y\">"._("Yes")."<OPTION VALUE=\"N\">"._("No")."</SELECT></TD><TD><INPUT TYPE=\"hidden\" NAME=\"new\" VALUE=\"true\"><INPUT TYPE=\"submit\" VALUE=\"Add\"></TD></TR>\n";
  echo "</TABLE><BR>\n";
  echo "</FORM>\n";
  break;
}

$sql = "
SELECT
 username AS 'Username',
 fullname AS 'Full Name',
 CASE
  WHEN type = 'A' THEN 'Administrator'
  WHEN type = 'D' THEN 'Domain Administrator'
  WHEN type = 'U' THEN 'User'
  WHEN type = 'R' THEN 'User (Regexp)'
 ELSE
  'Unknown Type'
 END AS 'Type',
 CASE
  WHEN noscan = 1 THEN 'N'
  WHEN noscan = 0 THEN 'Y'
 ELSE
  'Y'
 END AS 'Spam Check',
  spamscore AS 'Spam Score',
  highspamscore AS 'High Spam Score',
 CONCAT('<a href=\"?action=edit&amp;id=',username,'\">"._("Edit")."</a>&nbsp;&nbsp;<a href=\"javascript:delete_user(\'',username,'\')\">"._("Delete")."</a>&nbsp;&nbsp;<a href=\"?action=filters&amp;id=',username,'\">"._("Filters")."</a>') AS 'Actions'
FROM
 users
ORDER BY
 username
";
dbtable($sql,_('User Management'),false,true);
echo "<br>\n";
echo "<a href=\"?action=new\">"._("New User")."</a>\n";
} else {

  if(!isset($_GET['submit'])) {
   $sql = "SELECT username, fullname, type, quarantine_report, spamscore, highspamscore, noscan, quarantine_rcpt FROM users WHERE username='".$_SESSION['myusername']."'";
   $result = dbquery($sql);
   $row = mysql_fetch_object($result);
   if($row->quarantine_report == 1) { $quarantine_report = "CHECKED"; }
   if($row->noscan == 0) { $noscan = "CHECKED"; }
   $s[$row->type] = "SELECTED";
   echo "<FORM METHOD=\"GET\" ACTION=\"user_manager.php\">\n";
   echo "<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\""._("edit")."\">\n";
   echo "<INPUT TYPE=\"HIDDEN\" NAME=\"key\" VALUE=\"".$row->username."\">\n";
   echo "<INPUT TYPE=\"HIDDEN\" NAME=\"submit\" VALUE=\"true\">\n";
   echo "<TABLE CLASS=\"mail\" BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\">\n";
   echo " <TR><TD CLASS=\"heading\" COLSPAN=2 ALIGN=\"CENTER\">"._("Edit User ").$row->username."</TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Username").":</TD><TD>".$_SESSION['myusername']."</TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Name").":</TD><TD>".$_SESSION['fullname']."</TD></TR>\n";
   if ($_SESSION['use_pam'] != '1') {
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password\" VALUE=\"XXXXXXXX\"></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password1\" VALUE=\"XXXXXXXX\"></TD></TR>\n";
   } else {
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password\" VALUE=\"XXXXXXXX\" DISABLED></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Password").":</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password1\" VALUE=\"XXXXXXXX\" DISABLED></TD></TR>\n";
   }

   echo " <TR><TD CLASS=\"heading\">"._("Quarantine Report").":</TD><TD><INPUT TYPE=\"CHECKBOX\" NAME=\"quarantine_report\" $quarantine_report> <font size=\"-2\">"._("Send Daily Report?")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Quarantine Report Recipient").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"quarantine_rcpt\" VALUE=\"".$row->quarantine_rcpt."\"><br><font size=\"-2\">"._("Override quarantine report recipient?<br>(uses your username if blank)")."</font></TD>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Scan for Spam").":</TD><TD><INPUT TYPE=\"CHECKBOX\" NAME=\"noscan\" $noscan> <font size=\"-2\">"._("Scan e-mail for Spam?")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("Spam Score").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"spamscore\" VALUE=\"".$row->spamscore."\" size=\"4\"> <font size=\"-2\">0="._("Use Default")."</font></TD></TR>\n";
   echo " <TR><TD CLASS=\"heading\">"._("High Spam Score").":</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"highspamscore\" VALUE=\"".$row->highspamscore."\" size=\"4\"> <font size=-2>0="._("Use Default")."</font></TD></TR>\n";
   echo "<TR><TD CLASS=\"heading\">"._("Action").":</TD><TD><INPUT TYPE=\"RESET\" VALUE=\"Reset\">&nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" VALUE=\"Update\"></TD></TR>\n";
   echo "</TABLE></FORM><BR>\n";
   $sql = "SELECT filter, active FROM user_filters WHERE username='".$row->username."'";
   $result = dbquery($sql);
  } else {
   // Do update
if($_GET['password'] != $_GET['password1']){

echo _("Passwords do not match");
}
else{
   $do_pwd = false;
   $username      = mysql_real_escape_string($_SESSION['myusername']);
   $n_password    = mysql_real_escape_string($_GET['password']);
   $spamscore     = mysql_real_escape_string($_GET['spamscore']);
   $highspamscore = mysql_real_escape_string($_GET['highspamscore']);
   if(!isset($_GET['quarantine_report'])) {
    $n_quarantine_report = '0';
   } else {
    $n_quarantine_report = '1';
   }
   if(!isset($_GET['noscan'])) {
    $noscan = '1';
   } else {
    $noscan = '0';
   }
   $quarantine_rcpt = mysql_real_escape_string($_GET['quarantine_rcpt']);

   if($_GET['password'] !== 'XXXXXXXX') {
    // Password reset required
    $sql = "UPDATE users SET password=md5('$n_password'), quarantine_report='$n_quarantine_report', spamscore='$spamscore', highspamscore='$highspamscore', noscan='$noscan', quarantine_rcpt='$quarantine_rcpt' WHERE username='$username'";
    dbquery($sql);
   } else {
    $sql = "UPDATE users SET quarantine_report='$n_quarantine_report', spamscore='$spamscore', highspamscore='$highspamscore', noscan='$noscan', quarantine_rcpt='$quarantine_rcpt' WHERE username='$username'";
    dbquery($sql);
   }

   // Audit
    audit_log("User [$username] updated their own account");
    echo "<center><h1><font color='green'>Update Completed</font></h1></center>";
    echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"3;user_manager.php\">";
  }
}
}
// Add footer
html_end();
// Close any open db connections
dbclose();
