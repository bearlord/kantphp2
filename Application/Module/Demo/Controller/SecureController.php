<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;

class SecureController extends BaseController {

    public function __construct() {
        require_once KANT_PATH . 'Secure/phpseclib/bootstrap.php';
    }

    public function IndexAction() {
        $this->AesController();
    }

    public function AesController() {
        $crypt = new Crypt_AES();
        $crypt->setKey('abcdefghijklmnop');
        $plaintext = "老徐爱吃鱼";
        $securetext = base64_encode($crypt->encrypt($plaintext));
        $decodetext = $crypt->decrypt(base64_decode($securetext));
        var_dump($plaintext);
        var_dump($securetext);
        var_dump($decodetext);
        highlight_file(__FILE__);
    }

}

?>
