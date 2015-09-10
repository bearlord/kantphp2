<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

class ThumbController extends BaseController {
    
    public function indexAction() {
        $filename = PUBLIC_PATH . "demo/20130531021105729.png";
        $this->library("Image");
        $ImageObj = new \Image();
        $width = $this->input->get('w', 'intval', 200);
        $height = $this->input->get('h', 'intval', 200);
        $autocut = $this->input->get('c', '', 0);
        $ImageObj->thumbOutput($filename, $width, $height, $autocut);
    }
}
