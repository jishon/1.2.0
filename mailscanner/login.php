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
?>
<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="images/favicon.png">
<?php
if (file_exists('conf.php')) {
    require_once("./functions.php");
?>
    <style type="text/css">
        table.center {
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <title>MailWatch Login Page</title>
</head>
<body>
    <table width="300" border="1" class="center" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center"><img src="images/mailwatch-logo.png" alt="Mailwatch Logo"></td>
        </tr>

        <tr>
            <td>
                <form name="form1" method="post" action="checklogin.php">
                    <table width="100%" border="0" cellpadding="3" cellspacing="1">
                        <tr>
                            <td colspan="3"><strong> <?php echo _("MailWatch Login");?></strong></td>
                        </tr>
                        <tr>
                            <td style="width:78px;"><?php echo _("Username");?></td>
                            <td style="width:6px;">:</td>
                            <td style="width:294px;"><input name="myusername" type="text" id="myusername"></td>
                        </tr>
                        <tr>
                            <td><?php echo _("Password");?></td>
                            <td>:</td>
                            <td><input name="mypassword" type="password" id="mypassword"></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <input type="submit" name="Submit" value=<?php echo _("Login");?>>
                                <input type="reset" value=<?php echo _("Reset");?>>
                                <input type="button" value=<?php echo _("Back");?> onClick="history.go(-1);return true;">
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
 </body>
 </html>
<?php
} else {
?>
    <title><?php echo _("MailWatch Login Page");?></title>
</head>
<body>
    <table width="300" border="1" style="text-align:center;" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center"><img src="images/mailwatch-logo.png" alt="MailWatch"></td>
        </tr>
        <tr>
            <td>
                <form name="form1" method="post" action="checklogin.php">
                    <table width="100%" border="0" cellpadding="3" cellspacing="1">
                        <tr>
                            <td colspan="3"><strong> <?php echo _("MailWatch Login");?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="3"> <?php echo _("Sorry the Server is missing config.conf. Please create the file by copying config.conf.example and making the required changes.");?>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td><input type="button" value=<?php echo _("Back");?> onClick="history.go(-1);return true;"></td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
</body>
</html>
<?php
}
?>
