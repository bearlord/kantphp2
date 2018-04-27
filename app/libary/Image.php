<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
class Image {

    //interlace
    public $interlace = 0;
    //watermark image path;
    public $waterImage;
    //watermark text
    public $waterText = "";
    //wartermark font
    public $waterFont;
    //watermark fontsize
    public $waterFontsize = 12;
    //watermark color
    public $waterColor = '#ff0000';
    //watermark postion
    public $waterPostion = 9;
    //wartermark minimum width
    public $waterMinwidth = 300;
    //wartermark minimum height
    public $waterMinheight = 300;
    //watermark quality
    public $waterQuality = 80;
    //wartermark imagecopymerge percent
    public $waterPct = 100;

    public function __construct() {
        
    }

    public function thumb($image, $filename = '', $maxwidth = 200, $maxheight = 200, $suffix = '', $autocut = 0) {
        if (!$this->check($image)) {
            return false;
        }
        $info = $this->info($image);
        if ($info === false) {
            return false;
        }

        $srcwidth = $info['width'];
        $srcheight = $info['height'];
        $type = $info['type'];

        $creat_arr = $this->getPercent($srcwidth, $srcheight, $maxwidth, $maxheight);
        $createwidth = $width = $creat_arr['w'];
        $createheight = $height = $creat_arr['h'];
        $psrc_x = $psrc_y = 0;
        if ($autocut && $maxwidth > 0 && $maxheight > 0) {
            if ($maxwidth / $maxheight < $srcwidth / $srcheight && $maxheight >= $height) {
                $width = $maxheight / $height * $width;
                $height = $maxheight;
            } elseif ($maxwidth / $maxheight > $srcwidth / $srcheight && $maxwidth >= $width) {
                $height = $maxwidth / $width * $height;
                $width = $maxwidth;
            }
            $createwidth = $maxwidth;
            $createheight = $maxheight;
        }

        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
        $srcimg = $createfun($image);

        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $thumbimg = imagecreatetruecolor($createwidth, $createheight);
        } else {
            $thumbimg = imagecreate($width, $height);
        }

        if ($type == 'gif') {
            $background_color = imagecolorallocate($thumbimg, 0, 255, 0);
            imagecolortransparent($thumbimg, $background_color);
        } elseif ($type == 'png') {
            imagealphablending($thumbimg, false);
            imagesavealpha($thumbimg, true);
        }

        if (function_exists('imagecopyresampled')) {
            imagecopyresampled($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        } else {
            imagecopyresized($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        }

        if ($type == 'jpg' || $type == 'jpeg') {
            imageinterlace($thumbimg, $this->interlace);
        }

        $imagefun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
        if (empty($filename)) {
            $filename = substr($image, 0, strrpos($image, '.')) . $suffix . '.' . $type;
        }

        $imagefun($thumbimg, $filename);
        imagedestroy($thumbimg);
        imagedestroy($srcimg);
        return $filename;
    }

    public function thumbOutput($image, $maxwidth = 200, $maxheight = 200, $autocut = 0) {
        if (!$this->check($image)) {
            return false;
        }
        $info = $this->info($image);
        if ($info === false) {
            return false;
        }
        $srcwidth = $info['width'];
        $srcheight = $info['height'];
        $type = $info['type'];

        $creat_arr = $this->getPercent($srcwidth, $srcheight, $maxwidth, $maxheight);
        $createwidth = $width = $creat_arr['w'];
        $createheight = $height = $creat_arr['h'];
        $psrc_x = $psrc_y = 0;
        if ($autocut && $maxwidth > 0 && $maxheight > 0) {
            if ($maxwidth / $maxheight < $srcwidth / $srcheight && $maxheight >= $height) {
                $width = $maxheight / $height * $width;
                $height = $maxheight;
            } elseif ($maxwidth / $maxheight > $srcwidth / $srcheight && $maxwidth >= $width) {
                $height = $maxwidth / $width * $height;
                $width = $maxwidth;
            }
            $createwidth = $maxwidth;
            $createheight = $maxheight;
        }

        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
        $srcimg = $createfun($image);

        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $thumbimg = imagecreatetruecolor($createwidth, $createheight);
        } else {
            $thumbimg = imagecreate($width, $height);
        }
        if ($type == 'gif') {
            $background_color = imagecolorallocate($thumbimg, 0, 255, 0);
            imagecolortransparent($thumbimg, $background_color);
        } elseif ($type == 'png') {
            imagealphablending($thumbimg, false);
            imagesavealpha($thumbimg, true);
        }

        if (function_exists('imagecopyresampled')) {
            imagecopyresampled($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        } else {
            imagecopyresized($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        }
        if ($type == 'jpg' || $type == 'jpeg') {
            imageinterlace($thumbimg, $this->interlace);
        }
        header("content-type:image/$type\r\n");
        $imagefun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
        
        $imagefun($thumbimg);
        imagedestroy($thumbimg);
        imagedestroy($srcimg);
    }
    
    /**
     *  Check image
     * 
     * @param string $image
     * @return boolean
     */
    public function check($image) {
        if (!extension_loaded('gd')) {
            return false;
        }
        if (!file_exists($image)) {
            return false;
        }
        if (!preg_match("/\.(jpg|jpeg|gif|png)/i", $image, $m)) {
            return false;
        }
        if (!function_exists('imagecreatefromjpeg')) {
            return false;
        }
        return true;
    }

    /**
     * Image infomation
     * 
     * @param string $img
     * @return boolean
     */
    public function info($img) {
        $imageinfo = getimagesize($img);
        if ($imageinfo === false) {
            return false;
        }
        $imagetype = strtolower(substr(image_type_to_extension($imageinfo[2]), 1));
        $imagesize = filesize($img);
        $info = array(
            'width' => $imageinfo[0],
            'height' => $imageinfo[1],
            'type' => $imagetype,
            'size' => $imagesize,
            'mime' => $imageinfo['mime']
        );
        return $info;
    }

    /**
     *  Get percent
     * 
     * @param type $srcwidth
     * @param type $srcheight
     * @param type $dstw
     * @param type $dsth
     * @return type
     */
    public function getPercent($srcwidth, $srcheight, $dstw, $dsth) {
        if (empty($srcwidth) || empty($srcheight) || ($srcwidth <= $dstw && $srcheight <= $dsth)) {
            $w = $srcwidth;
            $h = $srcheight;
        } elseif ((empty($dstw) || $dstw == 0) && $dsth > 0 && $srcheight > $dsth) {
            $h = $dsth;
            $w = round($dsth / $srcheight * $srcwidth);
        } elseif ((empty($dsth) || $dsth == 0) && $dstw > 0 && $srcwidth > $dstw) {
            $w = $dstw;
            $h = round($dstw / $srcwidth * $srcheight);
        } elseif ($dstw > 0 && $dsth > 0) {
            if (($srcwidth / $dstw) < ($srcheight / $dsth)) {
                $w = round($dsth / $srcheight * $srcwidth);
                $h = $dsth;
            } elseif (($srcwidth / $dstw) > ($srcheight / $dsth)) {
                $w = $dstw;
                $h = round($dstw / $srcwidth * $srcheight);
            } else {
                $h = $dstw;
                $w = $dsth;
            }
        }
        $array['w'] = $w;
        $array['h'] = $h;
        return $array;
    }

    /**
     * Watermark image
     * 
     * @param type $target
     * @param type $w_pos
     * @return boolean
     */
    public function watermark($image, $target = '', $w_pos = '') {
        $w_pos = $w_pos ? $w_pos : $this->waterPostion;
        if (!$this->check($image)) {
            return false;
        }

        if (!$target) {
            $target = $image;
        }

        $source_info = getimagesize($image);
        $source_w = $source_info[0];
        $source_h = $source_info[1];
        if ($source_w < $this->waterMinwidth || $source_h < $this->waterMinheight) {
            return false;
        }
        switch ($source_info[2]) {
            case 1 :
                $source_img = imagecreatefromgif($image);
                break;
            case 2 :
                $source_img = imagecreatefromjpeg($image);
                break;
            case 3 :
                $source_img = imagecreatefrompng($image);
                break;
            default :
                return false;
        }
        if (!empty($this->waterImage) && file_exists($this->waterImage)) {
            $ifwaterimage = 1;
            $water_info = getimagesize($this->waterImage);
            $width = $water_info[0];
            $height = $water_info[1];
            switch ($water_info[2]) {
                case 1 :
                    $water_img = imagecreatefromgif($this->waterImage);
                    break;
                case 2 :
                    $water_img = imagecreatefromjpeg($this->waterImage);
                    break;
                case 3 :
                    $water_img = imagecreatefrompng($this->waterImage);
                    break;
                default :
                    return;
            }
        } else {
            $ifwaterimage = 0;
            $this->waterFont = !empty($this->waterFont) ? $this->waterFont : dirname(__FILE__) . '/fonts/FreeSans.ttf';
            $temp = imagettfbbox(ceil($this->waterFontsize * 2.5), 0, $this->waterFont, $this->waterText);

            $width = $temp[2] - $temp[6];
            $height = $temp[3] - $temp[7];
            unset($temp);
        }
        switch ($w_pos) {
            case 1:
                $wx = 5;
                $wy = 5;
                break;
            case 2:
                $wx = ($source_w - $width) / 2;
                $wy = 0;
                break;
            case 3:
                $wx = $source_w - $width;
                $wy = 0;
                break;
            case 4:
                $wx = 0;
                $wy = ($source_h - $height) / 2;
                break;
            case 5:
                $wx = ($source_w - $width) / 2;
                $wy = ($source_h - $height) / 2;
                break;
            case 6:
                $wx = $source_w - $width;
                $wy = ($source_h - $height) / 2;
                break;
            case 7:
                $wx = 0;
                $wy = $source_h - $height;
                break;
            case 8:
                $wx = ($source_w - $width) / 2;
                $wy = $source_h - $height;
                break;
            case 9:
                $wx = $source_w - $width;
                $wy = $source_h - $height;
                break;
            case 10:
                $wx = rand(0, ($source_w - $width));
                $wy = rand(0, ($source_h - $height));
                break;
            default:
                $wx = rand(0, ($source_w - $width));
                $wy = rand(0, ($source_h - $height));
                break;
        }
        if ($ifwaterimage) {
            if ($water_info[2] == 3) {
                imagecopy($source_img, $water_img, $wx, $wy, 0, 0, $width, $height);
            } else {
                imagecopymerge($source_img, $water_img, $wx, $wy, 0, 0, $width, $height, $this->waterPct);
            }
        } else {
            if (!empty($this->waterColor) && (strlen($this->waterColor) == 7)) {
                $r = hexdec(substr($this->waterColor, 1, 2));
                $g = hexdec(substr($this->waterColor, 3, 2));
                $b = hexdec(substr($this->waterColor, 5));
            } else {
                return;
            }
            imagestring($source_img, $this->waterFontsize, $wx, $wy, $this->waterText, imagecolorallocate($source_img, $r, $g, $b));
        }

        switch ($source_info[2]) {
            case 1 :
                imagegif($source_img, $target);
                break;
            case 2 :
                imagejpeg($source_img, $target, $this->waterQuality);
                break;
            case 3 :
                imagepng($source_img, $target);
                break;
            default :
                return;
        }

        if (isset($water_info)) {
            unset($water_info);
        }
        if (isset($water_img)) {
            imagedestroy($water_img);
        }
        unset($source_info);
        imagedestroy($source_img);
        return true;
    }

}

?>
