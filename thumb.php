<?php
$pathScript = dirname(__FILE__);
$pathLib = $pathScript . '/lib/thumb/';

$file = preg_replace( '/\?.+$/', '', $_SERVER['REQUEST_URI'] );
$pathInfo = pathinfo( $file );
$fileName = $pathInfo['filename'] . '.' . $pathInfo['extension'];
$fileDirs = explode('/', $pathInfo['dirname']);

$realFileDirs = array();
$thumbSize = array();
foreach ($fileDirs as $v) {
    if (!preg_match('/^(thumbs|\d+x\d+(x\d+)?)$/', $v) && $v) {
        $realFileDirs[] = $v;
    } else if (preg_match('/(\d+)x(\d+)(x(\d+))?/', $v, $m)) {
        $thumbSize['w'] = $m[1];
        $thumbSize['h'] = $m[2];
        $thumbSize['q'] = ( int ) ( $m[4] > 0 ? $m[4] : 90 );
    }
}

$realFileDirsStr = implode('/', $realFileDirs);
$file = $pathScript . '/' . $realFileDirsStr . '/' . $fileName;
$file = urldecode($file);

if (file_exists($file)) {
    require_once $pathLib . 'ThumbLib.inc.php';
    if (!is_dir($pathScript . '/' . $pathInfo['dirname'])) mkdir($pathScript . '/' . $pathInfo['dirname'], 0777, true);
    $options = array('resizeUp' => true, 'jpegQuality' => $thumbSize['q']);
    set_time_limit( 60 * 5 );
    $destFileName = $pathScript . '/data' . $pathInfo['dirname'] . '/' . $fileName;
    $thumb = PhpThumbFactory::create($file, $options);
    //$thumb->resize($thumbSize['w'], $thumbSize['h'])->addWatermark($pathScript.'/image/paw_water'.($thumbSize['w'] < 100 || $thumbSize['h'] < 100 ? '-s' : '').'.png')->save($destFileName)->show();
    $thumb->resize($thumbSize['w'], $thumbSize['h'])->save($destFileName)->show();
}
die;

