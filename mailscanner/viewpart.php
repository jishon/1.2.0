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

require_once './functions.php';
require_once 'Mail/mimeDecode.php';

session_start();
require 'login.function.php';

ini_set("memory_limit", MEMORY_LIMIT);

if (!isset($_GET['id'])) {
 die(_("No input Message ID"));
} else {
    // See if message is local
    dbconn(); // required db link for mysql_real_escape_string
    if (!($host = @mysql_result(dbquery("SELECT hostname FROM maillog WHERE id='" . mysql_real_escape_string($_GET['id']) . "' AND " . $_SESSION["global_filter"] . ""),0))) {
        die(sprintf(_("Message %s not found\n"), $_GET['id']));
    }
    if (!is_local($host) || RPC_ONLY) {
        // Host is remote - use XML-RPC
        //$client = new xmlrpc_client(constant('RPC_RELATIVE_PATH').'/rpcserver.php', $host, 80);
        $input = new xmlrpcval($_GET['id']);
        $parameters = array($input);
        $msg = new xmlrpcmsg('return_quarantined_file', $parameters);
        //$rsp = $client->send($msg);
        $rsp = xmlrpc_wrapper($host, $msg);
        if ($rsp->faultcode() == 0) {
            $response = php_xmlrpc_decode($rsp->value());
        } else {
            die("Error: " . $rsp->faultstring());
        }
        $file = base64_decode($response);
    } else {
        $date = @mysql_result(
            dbquery(
                "SELECT DATE_FORMAT(date,'%Y%m%d') FROM maillog where id='" . mysql_real_escape_string(
                    $_GET['id']
                ) . "' AND " . $_SESSION["global_filter"] . ""
            ),
            0
        );
        $qdir = get_conf_var('QuarantineDir');
        switch (true) {
            case (file_exists($qdir . '/' . $date . '/nonspam/' . $_GET['id'])):
                $_GET['filename'] = $date . '/nonspam/' . $_GET['id'];
                break;
            case (file_exists($qdir . '/' . $date . '/spam/' . $_GET['id'])):
                $_GET['filename'] = $date . '/spam/' . $_GET['id'];
                break;
            case (file_exists($qdir . '/' . $date . '/mcp/' . $_GET['id'])):
                $_GET['filename'] = $date . '/mcp/' . $_GET['id'];
                break;
            case (file_exists($qdir . '/' . $date . '/' . $_GET['id'] . '/message')):
                $_GET['filename'] = $date . '/' . $_GET['id'] . '/message';
                break;
        }

        // File is local
        if (!isset($_GET['filename'])) {
            die("No input filename");
        } else {
            // SECURITY - strip off any potential nasties
            $_GET['filename'] = preg_replace('[\.\/|\.\.\/]', '', $_GET['filename']);
            $filename = get_conf_var('QuarantineDir') . "/" . $_GET['filename'];
            if (!@file_exists($filename)) {
                die(_("Error: file not found")."\n");
            }
            $file = file_get_contents($filename);
        }
    }
}

$params['include_bodies'] = true;
$params['decode_bodies'] = true;
//$params['decode_headers'] = true;
$params['decode_headers'] = false;
$params['input'] = $file;

$structure = Mail_mimeDecode::decode($params);
$mime_struct = Mail_mimeDecode::getMimeNumbers($structure);

// Make sure that part being requested actually exists
if (isset($_GET['part'])) {
    if (!isset($mime_struct[$_GET['part']])) {
        die(sprintf(_("Part %s not found\n"), $_GET['part']));
    }
}

function decode_structure($structure)
{
    $type = $structure->ctype_primary . "/" . $structure->ctype_secondary;
    //Using mimeDecode to identify charset in MIME part.. If all is null,just set to "".
    $charset = ck_mbstring_encoding($structure->ctype_parameters['charset']);
    //echo "GLOBAL CHARSET :".$_GET['charset']." CHARSET:".$charset."\n";
    switch ($type) {
        case "text/plain":
                /*
            if (isset ($structure->ctype_parameters['charset']) && strtolower($structure->ctype_parameters['charset']) == 'utf-8') {
                $structure->body = utf8_decode($structure->body);
            }
            */
            echo '<html>
 <head>
 <link rel="shortcut icon" href="images/favicon.png">
 <title>'._("Quarantined E-Mail Viewer").'</title>
 </head>
 <body>
 <pre>';
   //' . htmlentities(wordwrap($structure->body)) . '
   //Convet the body using charset we found.If none,convert as default locale.
   if ($charset == "") {
    if (!isset($_GET['charset'])) {
     echo mb_convert_encoding(htmlspecialchars(wordwrap($structure->body)), "UTF-8", check_locale());
    } else {
     echo mb_convert_encoding(htmlspecialchars(wordwrap($structure->body)), "UTF-8", $_GET['charset']);
    }
   } elseif (strtolower($charset) != 'utf-8'){
    echo mb_convert_encoding(htmlspecialchars(wordwrap($structure->body)), "UTF-8", $charset);
   } else {
    echo htmlspecialchars(wordwrap($structure->body));
   }
 echo '</pre>
 </body>
 </html>'."\n";
   break;
  case "text/html":
   /*
   if (isset ($structure->ctype_parameters['charset']) && strtolower($structure->ctype_parameters['charset']) != 'utf-8') {
    $structure->body = utf8_encode ($structure->body);
   }
   */
   //echo $structure->body;
   preg_match('/charset=([-a-z0-9_]+)/i',$structure->body,$body_charset);
   if (STRIP_HTML) {    
    //echo "BODY CHARSET:"."\n";
    //foreach ($body_charset as $value) {
    // echo "LANG: ".$value." \n";
    //}
    //echo "BODY Content:".$structure->body."\n";
    //Convet the body using charset we found.If none,convert as default locale.
    if ($charset == "") {
     // ctype_parameters is not always correct. We can search the meta in html before striping.
     if ($body_charset[1] == NULL) {
      echo mb_convert_encoding(strip_tags($structure->body, ALLOWED_TAGS),"UTF-8",check_locale());
     } else {
      if (strtolower($body_charset[1]) != "utf-8") {
       echo mb_convert_encoding(strip_tags($structure->body, ALLOWED_TAGS),"UTF-8",ck_mbstring_encoding($body_charset[1]));
      } else {
       echo strip_tags($structure->body, ALLOWED_TAGS);
      }
     }
    } else {
     if ($body_charset[1] == NULL) {
      if (strtolower($charset) != "utf-8") {
       echo mb_convert_encoding(strip_tags($structure->body, ALLOWED_TAGS),"UTF-8",ck_mbstring_encoding($charset));
      } else {
       echo strip_tags($structure->body, ALLOWED_TAGS);
      }
     } else {
      echo mb_convert_encoding(strip_tags($structure->body, ALLOWED_TAGS),"UTF-8",$body_charset[1]);
     }
    }
   } else {
    if ($charset == "") {
     if ($body_charset[1] == NULL) {
      echo mb_convert_encoding($structure->body,"UTF-8",check_locale());
     } else {
      echo $structure->body;
     }
    } else {
     if ($body_charset[1] == NULL) {
      echo mb_convert_encoding($structure->body,"UTF-8",$charset);
     } else {
      echo $structure->body;
     }
    }
   }
   break;
  case "multipart/alternative":
   break;
  default:
   header("Content-type: ".$structure->headers['content-type']);
   header("Content-Disposition: ".$structure->headers['content-disposition']);
   //Convet the body using charset we found.If none,convert as default locale.
   if ($charset == "") {
    if (defined('UTF8SUBJECT') && UTF8SUBJECT) {
     echo $structure->body;
    } else {
     echo mb_convert_encoding($structure->body,"UTF-8",check_locale());
    }
   } elseif (strtolower($charset) != "utf-8") {
    echo mb_convert_encoding($structure->body,"UTF-8",$charset);
   } else {
    echo $structure->body;
   }
   //echo $structure->body;
   break;
 }
}

decode_structure($mime_struct[$_GET['part']]);

// Close any open db connections
dbclose();
