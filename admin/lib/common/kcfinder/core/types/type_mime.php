<?php

/** This file is part of KCFinder project
  *
  *      @desc MIME type detection class
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class type_mime {

    public function checkFile($file, array $config) {
        if (!class_exists("finfo"))
            return "Fileinfo PECL extension is missing.";

        if (!isset($config['params']))
            return "Undefined MIME types.";

        $finfo = mb_strlen($config['mime_magic'])
            ? new finfo(FILEINFO_MIME, $config['mime_magic'])
            : new finfo(FILEINFO_MIME);
        if (!$finfo)
            return "Opening fileinfo database failed.";

        $type = $finfo->file($file);
        $type = mb_substr($type, 0, mb_strrpos($type, ";"));

        $mimes = $config['params'];
        if (mb_substr($mimes, 0, 1) == "!") {
            $mimes = trim(mb_substr($mimes, 1));
            return in_array($type , explode(" ", $mimes))
                ? "You can't upload such files."
                : true;
        }

        return !in_array($type , explode(" ", $mimes))
            ? "You can't upload such files."
            : true;
    }
}

?>
