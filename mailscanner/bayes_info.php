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

// Start the header code and Title
html_start(_("SpamAssassin Bayes Database Info"), 0, false, false);

// Enter the Action in the Audit log
audit_log('Viewed SpamAssasin Bayes Database Info');

// Create the table
echo '<table align="center" class="boxtable" border="0" cellspacing="1" cellpadding="1" width="600">';
// Add a Header to the table
echo '<tr><th colspan="2">'._("Bayes Database Information").'</th></tr>';

// Open the spamassassin file
if (get_sa_conf_var(bayes_store_module) == "Mail::SpamAssassin::BayesStore::SQL") {
 $sql = "
 SELECT SUM(spam_count) AS 'nspam',
        SUM(ham_count) AS 'nham',
        SUM(token_count) AS 'ntokens',
        MIN(oldest_token_age) AS 'oldest atime',
        MAX(newest_token_age) AS 'newest atime',
        MAX(last_expire) AS 'last expiry atime',
        SUM(last_expire_reduce) AS 'last expire reduction count'
   FROM bayes_vars
 ";
 $sth = sa_dbquery($sql);
 $fields = mysql_num_fields($sth);
 $rows = mysql_num_rows($sth);
 if ($rows > 0) {
  setlocale(LC_TIME, $_ENV['LANG']);
  $row = mysql_fetch_row($sth);
  for ($f=0; $f<$fields; $f++) {   
   switch($filename[$f]=mysql_field_name($sth,$f)) {
    case 'nspam':
     echo '<tr><td class="heading">'._("Number of Spam Messages").':</td><td align="right">'.number_format($row[$f]).'</td></tr>';
     break;
    case 'nham':
     echo '<tr><td class="heading">'._("Number of Ham Messages").':</td><td align="right">'.number_format($row[$f]).'</td></tr>';
     break;
    case 'ntokens':
     echo '<tr><td class="heading">'._("Number of Tokens").':</td><td align="right">'.number_format($row[$f]).'</td></tr>';
     break;
    case 'oldest atime':
     echo '<tr><td class="heading">'._("Oldest Token").':</td><td align="right">'.strftime('%c',$row[$f]).'</td></tr>';
     break;
    case 'newest atime':
     echo '<tr><td class="heading">'._("Newest Token").':</td><td align="right">'.strftime('%c',$row[$f]).'</td></tr>';
     break;
    case 'last expiry atime':
     echo '<tr><td class="heading">'._("Last Expiry").':</td><td align="right">'.strftime('%c',$row[$f]).'</td></tr>';
     break;
    case 'last expire reduction count':
     echo '<tr><td class="heading">'._("Last Expiry Reduction Count").':</td><td align="right">'.number_format($row[$f]).' tokens</td></tr>';
     break;
   }
  }
  $database_type = get_sa_sql_dsn(bayes_sql_dsn);
  echo "<TR><TD CLASS=\"heading\">"._("Database Type").":</TD><TD ALIGN=\"RIGHT\">".$database_type[2]."</TD></TR>\n";
 }
} else {
$fh = popen(SA_DIR . 'sa-learn -p ' . SA_PREFS . ' --dump magic', 'r');


 while (!feof($fh)) {

  $line = rtrim(fgets($fh,4096));

  debug("line: ".$line."\n");

  if(preg_match('/(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+non-token data: (.+)/', $line, $regs)) {

  switch($regs[5]) {
		
	    case 'nspam':
		    echo '<tr><td class="heading">'._("Number of Spam Messages").':</td><td align="right">'.number_format($regs[3]).'</td></tr>';
         break;
		
		case 'nham':
		    echo '<tr><td class="heading">'._("Number of Ham Messages").':</td><td align="right">'.number_format($regs[3]).'</td></tr>';
		break;

		case 'ntokens':
		    echo '<tr><td class="heading">'._("Number of Tokens").':</td><td align="right">'.number_format($regs[3]).'</td></tr>';
		break;

		case 'oldest atime':
			echo '<tr><td class="heading">'._("Oldest Token").':</td><td align="right">'.date('r',$regs[3]).'</td></tr>';
		break;
		
		case 'newest atime':
			echo '<tr><td class="heading">'._("Newest Token").':</td><td align="right">'.date('r',$regs[3]).'</td></tr>';
		break;
		
		case 'last journal sync atime':
			echo '<tr><td class="heading">'._("Last Journal Sync").':</td><td align="right">'.date('r',$regs[3]).'</td></tr>';
		break;
		
		case 'last expiry atime':
			echo '<tr><td class="heading">'._("Last Expiry").':</td><td align="right">'.date('r',$regs[3]).'</td></tr>';
		break;
		
		case 'last expire reduction count':
			echo '<tr><td class="heading">'._("Last Expiry Reduction Count").':</td><td align="right">'.number_format($regs[3]).' tokens</td></tr>';
		break;
        }
    }
 }

 // Close the file
 echo '<tr><td class="heading">'._("Database Type").':</td><td align="right">'._("Spamassassin Build-In").'</td></tr>';
 pclose($fh);
}

// End the table html tag
echo '</table>';

// Add footer
html_end();

// Close any open db connections
dbclose();
