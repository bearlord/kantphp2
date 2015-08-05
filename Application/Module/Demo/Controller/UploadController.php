<?php

class UploadController extends BaseController {

    public function indexAction() {
//		var_dump($_FILES);
        require_once APP_PATH . 'Libary/Attachment.php';
        $attach = new Attachment();
        $attach->savePath = PUBLIC_PATH . 'uploads/attach/';
        $a = $attach->upload('ifile');
        $b = $attach->upload('wfile');
        $_a = str_replace(PUBLIC_PATH, '', $a);
        $_b = str_replace(PUBLIC_PATH, '', $b);
        var_dump($a);
        var_dump($b);
        var_dump($_a);
        var_dump($_b);
        $this->view->display();
    }

    public function uploadThumbAction() {
        require_once APP_PATH . 'Libary/Attachment.php';
        require_once APP_PATH . 'Libary/Image.php';
        $attachObj = new Attachment();
        $attachObj->savePath = PUBLIC_PATH . 'uploads/pic/';
        $attachObj->upload("ipic");

        //上传后图
        $souceImg = $attachObj->files[0];
        $imgObj = new Image();
        //缩略图
        $thumb = $imgObj->thumb($souceImg, '', 800, 800, '_s');
        //原图打水印	
        $imgObj->w_img = PUBLIC_PATH . 'images/kantphp-2_s.png';
        $imgObj->w_pos = 9;
        $target = PUBLIC_PATH . 'uploads/pic/' . date("His") . ".png";
        $imgObj->watermark($souceImg, $target);
        echo sprintf("上传原图：%s<br /> 缩略图: %s<br />原图水印：%s<br />", $souceImg, $thumb, $target);
        $this->view->display();
    }

}

?>
