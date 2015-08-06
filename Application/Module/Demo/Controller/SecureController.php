<?php

namespace Demo\Controller;

use Kant\Controller\BaseController;
use Kant\Secure\Crypt\Crypt_AES;

class SecureController extends BaseController {

    public function IndexAction() {
        require_once KANT_PATH . 'Secure/bootstrap.php';
        include(KANT_PATH . 'Secure/Crypt/Crypt_Random.php');
        $bb = \Kant\Secure\Crypt\crypt_random_string(44);
        echo $bb;
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
