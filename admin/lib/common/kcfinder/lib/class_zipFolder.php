<?php
preg_match('/((.*)?admin\/)(.*)?/', $_SERVER['REQUEST_URI'], $m);
if (file_exists($_SERVER['DOCUMENT_ROOT'].$m[1].'author.php')) include($_SERVER['DOCUMENT_ROOT'].$m[1].'author.php');
else die('NOT AUTHORIZE');

/** This file is part of KCFinder project. The class are taken from
  * http://www.php.net/manual/en/function.ziparchive-addemptydir.php
  *
  *      @desc Directory to ZIP file archivator
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class zipFolder {
    protected $zip;
    protected $root;
    protected $ignored;

    function __construct($file, $folder, $ignored=null) {
        $this->zip = new ZipArchive();

        $this->ignored = is_array($ignored)
            ? $ignored
            : ($ignored ? array($ignored) : array());

        if ($this->zip->open($file, ZIPARCHIVE::CREATE) !== TRUE)
            throw new Exception("cannot open <$file>\n");

        $folder = rtrim($folder, '/');

        if (mb_strstr($folder, '/')) {
            $this->root = mb_substr($folder, 0, mb_strrpos($folder, '/') + 1);
            $folder = mb_substr($folder, mb_strrpos($folder, '/') + 1);
        }

        $this->zip($folder);
        $this->zip->close();
    }

    function zip($folder, $parent=null) {
        $full_path = "{$this->root}$parent$folder";
        $zip_path = "$parent$folder";
        $this->zip->addEmptyDir($zip_path);
        $dir = new DirectoryIterator($full_path);
        foreach ($dir as $file)
            if (!$file->isDot()) {
                $filename = $file->getFilename();
                if (!in_array($filename, $this->ignored)) {
                    if ($file->isDir())
                        $this->zip($filename, "$zip_path/");
                    else
                        $this->zip->addFile("$full_path/$filename", "$zip_path/$filename");
                }
            }
    }
}

?>
