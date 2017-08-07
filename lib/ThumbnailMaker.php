<?php

class ThumbnailMaker {

  static function getDummy($width = false) {
    switch($width) {
      case 'wide':
        return '/image/no-photo-370.png';
        break;
      case 'tiny':
      default:
        return '/image/no-photo.gif';
    }
  }

  static $pathToCropImg = 'data/image/catalog/';
  static function cropAndResize($filename, $width, $height, $offsetY = 0) {
    $width_ = $width;
    $height_ = $height;
    $offsetY = (int)$offsetY;
    if($filename == '') return null;
    $absPath = Site::GetParms('absolutePath');
    $absPathSource = $absPath;
    $absPathSourceFileName = $absPathSource.'/'.ltrim($filename, '/');
    $md5Filename = md5($filename)."-$width-$height-$offsetY-v1-";
    preg_match('/\.(jpg|jpeg|gif|png|bmp)$/i', $filename, $_matches);
    $imgExt = strtolower($_matches[1]);

    if (file_exists($absPath.self::$pathToCropImg.$md5Filename.'.'.$imgExt)) return self::$pathToCropImg.$md5Filename.'.'.$imgExt;

    if (!file_exists($absPathSourceFileName)) return null;

    list($width_orig, $height_orig) = getimagesize($absPathSourceFileName);
    $width_orig_  = $width_orig;
    $height_orig_ = $height_orig;

    /*//////////// определение пропорций, под которые подгонять картинку.
    $k = $width_orig / $height_orig;
    $k_out = $width / $height;
    if ( $k > $k_out) $width_orig = $height_orig * $k_out ;  else $height_orig = $width_orig / $k_out;

    ///////////////////////// если картинка не попадает под пропорции, то часть оригинала нужно обрезать.
    $ratio_orig = $width_orig/$height_orig;
    if ($width/$height > $ratio_orig) {$width = ceil($height*$ratio_orig);} else {$height = ceil($width/$ratio_orig);}*/

    list($width, $height) = self::getNewSize2($width, $height, $width_orig_, $height_orig_, false);

    //// отцентровать будущую картинку отцентровать по центру оригинала
    $x_shift =  ($width < $width_ ? round(($width_ - $width) /2) : 0);
    $y_shift =  ($height < $height_ ? round(($height_ - $height) /2 + $offsetY) : 0);

    $image_p = imagecreatetruecolor($width_, $height_);
    $black = imagecolorallocate ($image_p, 0, 0, 0);
    imagefill($image_p, 0, 0, $black);
    switch (exif_imagetype($absPathSourceFileName)) {
      case IMAGETYPE_JPEG:
        $image = ImageCreateFromJPEG($absPathSourceFileName);
        imagecopyresampled($image_p, $image, $x_shift, $y_shift, 0, 0, $width, $height, $width_orig, $height_orig);
        ImageJPEG($image_p, $absPath.self::$pathToCropImg.$md5Filename.'.'.$imgExt, 87);
        return self::$pathToCropImg.$md5Filename.'.'.$imgExt;
      case IMAGETYPE_GIF:
        $image = ImageCreateFromGIF($absPathSourceFileName);
        imagecopyresampled($image_p, $image, $x_shift, $y_shift, 0, 0, $width, $height, $width_orig, $height_orig);
        ImageGIF($image_p, $absPath.self::$pathToCropImg.$md5Filename.'.'.$imgExt);
        return self::$pathToCropImg.$md5Filename.'.'.$imgExt;
      case IMAGETYPE_PNG:
        $image = ImageCreateFromPNG($absPathSourceFileName);
        imagecopyresampled($image_p, $image, $x_shift, $y_shift, 0, 0, $width, $height, $width_orig, $height_orig);
        ImagePNG($image_p, $absPath.self::$pathToCropImg.$md5Filename.'.'.$imgExt);
        return self::$pathToCropImg.$md5Filename.'.'.$imgExt;
      case IMAGETYPE_WBMP:
        $image = ImageCreateFromWBMP($absPathSourceFileName);
        imagecopyresampled($image_p, $image, $x_shift, $y_shift, 0, 0, $width, $height, $width_orig, $height_orig);
        ImageWBMP($image_p, $absPath.self::$pathToCropImg.$md5Filename.'.'.$imgExt);
        return self::$pathToCropImg.$md5Filename.'.'.$imgExt;
      default: return $filename;
      }
    }

  static $pathToResizeImg = 'data/image/catalog/';
  static function getOrCreateImg($filename, $seriaWidth, $seriaHeight, $crop = false, $fillColor = false, $border = false) {
    if (empty($filename)) return null;
    $absPath = Site::GetParms('absolutePath');
    $absPathSource = $absPath;
    $md5Filename = md5($filename)."-$seriaWidth-$seriaHeight".($crop ? '-crop' : null).($fillColor ? '-r'.$fillColor[0].'-b'.$fillColor[1].'-g'.$fillColor[2] : null);
    $absPathSourceFileName = $absPathSource.'/'.ltrim($filename, '/');
    preg_match('/\.(jpg|jpeg|gif|png|bmp)$/i', $filename, $_matches);
    $imgType = strtolower($_matches[1]);
    if (!file_exists($absPath.self::$pathToResizeImg.$md5Filename.'.'.$imgType)) {
      if (!file_exists($absPathSourceFileName)) return null;
      list($width, $height) = getimagesize($absPathSourceFileName);
      list($new_width, $new_height) = self::getNewSize($seriaWidth, $seriaHeight, $width, $height, $crop);
      $image_p = imagecreatetruecolor($seriaWidth, $seriaHeight);
      if ($fillColor) {
        $white = imagecolorallocate ($image_p, $fillColor[0], $fillColor[1], $fillColor[2]);
          } else {
        $white = imagecolorallocate ($image_p, 255, 255, 255);
        }
      imagefill($image_p, 0, 0, $white);

      if ($fillColor) {
        $ink = imagecolorallocate ($image_p, $fillColor[0], $fillColor[1], $fillColor[2]);
        imagerectangle($image_p, 0, 0, 0, $seriaHeight, $ink);
        imagerectangle($image_p, ($seriaWidth - 1), 0, ($seriaWidth - 1), $seriaHeight, $ink);
      }
      if ($border) {
        $grey = imagecolorallocate ($image_p, 201, 201, 201);
      }

      $newCoordX = ($new_width < $seriaWidth ? round(($seriaWidth - $new_width) / 2) : 0);
      $newCoordY = ($new_height < $seriaHeight ? round(($seriaHeight - $new_height) / 2) : 0);
      switch (exif_imagetype($absPathSourceFileName)) {
        case IMAGETYPE_JPEG:
          $out = ImageCreateFromJPEG($absPathSourceFileName);
          imagecopyresampled($image_p, $out, $newCoordX, $newCoordY, 0, 0, $new_width, $new_height, $width, $height);
          if ($border) {
            imagerectangle($image_p, $newCoordX, $newCoordY, ($newCoordX + $new_width), ($newCoordY + $new_height), $grey);
          }
          ImageJPEG($image_p, $absPath.self::$pathToResizeImg.$md5Filename.'.'.$imgType, 87);
          return self::$pathToResizeImg.$md5Filename.'.'.$imgType;
        case IMAGETYPE_GIF:
          $out = ImageCreateFromGIF($absPathSourceFileName);
          imagecopyresampled($image_p, $out, $newCoordX, $newCoordY, 0, 0, $new_width, $new_height, $width, $height);
          if ($border) {
            imagerectangle($image_p, $newCoordX, $newCoordY, ($newCoordX + $new_width), ($newCoordY + $new_height), $grey);
          }
          ImageGIF($image_p, $absPath.self::$pathToResizeImg.$md5Filename.'.'.$imgType);
          return self::$pathToResizeImg.$md5Filename.'.'.$imgType;
        case IMAGETYPE_PNG:
          $out = ImageCreateFromPNG($absPathSourceFileName);
          imagecopyresampled($image_p, $out, $newCoordX, $newCoordY, 0, 0, $new_width, $new_height, $width, $height);
          if ($border) {
            imagerectangle($image_p, $newCoordX, $newCoordY, ($newCoordX + $new_width), ($newCoordY + $new_height), $grey);
          }
          ImagePNG($image_p, $absPath.self::$pathToResizeImg.$md5Filename.'.'.$imgType);
          return self::$pathToResizeImg.$md5Filename.'.'.$imgType;
        default:
          return $filename;

        }
      } else return self::$pathToResizeImg.$md5Filename.'.'.$imgType;
    }

  static function getNewSize($wReal, $hReal, $width, $height, $crop) {
    $ratio = $width/$height;
    $r_w = $wReal/$width;
    $r_h = $hReal/$height;
    $zoom = min($r_w, $r_h);
    if (!$crop) {
      if ($r_w > $r_h) return array(round($zoom * $width), $hReal);
      else return array($wReal, round($zoom * $height));
     }
    return array($wReal, $hReal);


    }

  function getNewSize2($wReal, $hReal, $width, $height, $flag)
  {
    if ($flag) {
      if ($width > $wReal || $height > $hReal) {
        if ($width > $height and $wReal/$width < $hReal/$height) {
          $new_height = $hReal;
          $new_width = round($width*($new_height/$height));
        } else {
          $new_width = $wReal;
          $new_height = round($height*($new_width/$width));
        }
        return array($new_width, $new_height);
      }

      return array($width, $height);
    } else {
      if ($width > $wReal || $height > $hReal) {
         if ($height >= $width) {
            $new_height = $hReal;
            $new_width = round($width*($new_height/$height));
         }
         else {
            $new_width = $wReal;
            $new_height = round($height*($new_width/$width));
         }
         return array($new_width, $new_height);
      }
      else return array($width, $height);
    }
  }
}