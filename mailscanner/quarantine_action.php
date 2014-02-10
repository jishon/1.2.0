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

require_once('./functions.php');

session_start();
require('login.function.php');

function simple_html_start()
{

    echo '<html>
<head>
<title>MailWatch for Mailscanner</title>
<link rel="shortcut icon" href="images/favicon.png">
<style type="text/css">

</style>
<body>';
}

function simple_html_end()
{
    echo '
</body>
</html>';
}

function simple_html_result($status)
{
    ?>
    <table class="box" width="100%" height="100%">
        <tr>
            <td valign="middle" align="center">
                <table border=0>
                    <tr>
                        <th><?php echo _("Result");?></th>
                    </tr>
                    <tr>
                        <td><?php echo $status; ?></td>
                    </tr>
                    <tr>
                        <td align="center"><b><a href="javascript:window.close()"><?php echo _("Close Window");?></a></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php
}

switch (false) {
    case (isset($_GET['id'])):
        die(_("Error: No Message ID"));
        break;
    case (isset($_GET['action'])):
        die(_("Error: No action"));
        break;
}


$list = quarantine_list_items($_GET['id']);
if (count($list) == 0) {
    die(_("Error: Message not found in quarantine"));
}


switch ($_GET['action']) {

    case 'release':
        if (count($list) == 1) {
            $to = $list[0]['to'];
            $result = quarantine_release($list, array(0), $to);
        } else {
            for ($i = 0; $i < count($list); $i++) {
                if (preg_match('/message\/rfc822/', $list[$i]['type'])) {
                    $result = quarantine_release($list, array($i), $list[$i]['to']);
                }
            }
        }

        if (isset($_GET['html'])) {
            // Display success
            simple_html_start();
            simple_html_result($result);
            simple_html_end();
        }
        break;

    case 'delete':
        if (isset($_GET['html'])) {
            if (!isset($_GET['confirm'])) {
                // Dislay an 'Are you sure' dialog
                simple_html_start();
                ?>
                <table width="100%" height="100%">
                    <tr>
                        <td align="center" valign="middle">
                            <table>
                                <tr>
                                    <th><?php echo _("Delete: Are you sure?");?></th>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $_GET['id']; ?>&action=delete&html=true&confirm=true"><?php echo _("Yes");?></a>
                                        &nbsp;&nbsp
                                        <a href="javascript:void(0)" onClick="javascript:window.close()"><?php echo _("No");?></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <?php
                simple_html_end();
            } else {
                simple_html_start();
                for ($i = 0; $i < count($list); $i++) {
                    $status[] = quarantine_delete($list, array($i));
                }
                $status = join('<br/>', $status);
                simple_html_result($status);
                simple_html_end();
            }
        } else {
            // Delete
            for ($i = 0; $i < count($list); $i++) {
                $status[] = quarantine_delete($list, array($i));
            }
        }
        break;

    case 'learn':
        break;

    default:
        die(_("Unknown action: ").$_GET['action']);
        break;
}

dbclose();
?>
</body>
</html>
