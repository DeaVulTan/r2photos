<?php
preg_match('/((.*)?admin\/)(.*)?/', $_SERVER['REQUEST_URI'], $m);
if (file_exists($_SERVER['DOCUMENT_ROOT'].$m[1].'author.php')) include($_SERVER['DOCUMENT_ROOT'].$m[1].'author.php');
else die('NOT AUTHORIZE');
$PATH = $m[2];

/** This file is part of KCFinder project
  *
  *      @desc Base configuration file
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

$_CONFIG = array(

    'disabled' => false,
    'readonly' => false,
    'denyZipDownload' => true,

    'theme' => "oxygen",

    'uploadURL' => $PATH."data",
    'uploadDir' => "",

    'dirPerms' => 0755,
    'filePerms' => 0644,

    'deniedExts' => "exe com msi bat php cgi pl htm html ico phtml shtml sql js css xml dtd ini vbs dcu pas dpr cfg",

    'types' => array(

        // CKEditor & FCKEditor types
        'files'   =>  "",
        'flash'   =>  "swf flv fla mov mp3 mp4 mpc mpeg mpg avi wmv asf rm qt",
        'images'  =>  "",

        // TinyMCE types
        'file'    =>  "",
        'media'   =>  "swf flv fla mov mp3 mp4 mpc mpeg mpg avi wmv asf rm qt",
        'image'   =>  "",
    ),

    'mime_magic' => "",

    'maxImageWidth' => 0,
    'maxImageHeight' => 0,

    'thumbWidth' => 100,
    'thumbHeight' => 100,

    'thumbsDir' => ".thumbs",

    'jpegQuality' => 90,

    'cookieDomain' => "",
    'cookiePath' => "",
    'cookiePrefix' => 'KCFINDER_',

    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION

    '_check4htaccess' => true,
    //'_tinyMCEPath' => "/tiny_mce",

    '_sessionVar' => &$_SESSION['KCFINDER'],
    //'_sessionLifetime' => 30,
    //'_sessionDir' => "/full/directory/path",

    //'_sessionDomain' => ".mysite.com",
    //'_sessionPath' => "/my/path",
);

?>