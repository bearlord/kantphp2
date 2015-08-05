<?php

class ImageController extends BaseController {

    public function __construct() {
        parent::__construct();
        require_once APP_PATH . 'Libary/Image.php';
    }

    public function indexAction() {

        $img = PUBLIC_PATH . 'images/kantphp-2.png';
        $image = new Image();
        $image->w_pct = 60;
        $image->thumb($img, '', 200, 200, '_s');
    }

    public function waterAction() {
        $img = PUBLIC_PATH . 'images/u9.jpeg';
        $image = new Image();
        $image->waterImage = PUBLIC_PATH . 'images/kantphp-2_s.png';
        $image->waterPostion = 5;
        $target = PUBLIC_PATH . 'images/' . date("His") . ".png";
        $_c = $image->watermark($img, $target);
    }

    public function waterTextAction() {
        $img = PUBLIC_PATH . 'images/2.png';
        $image = new Image($img);
        $image->waterText = "Only ABC 123";
        $image->waterFontsize = 18;
        $image->waterPostion = 6;
        $image->waterQuality = 60;
        $target = PUBLIC_PATH . 'images/' . date("His") . ".png";
        $_c = $image->watermark($img, $target);
    }

}

?>
