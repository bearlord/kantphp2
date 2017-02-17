<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kant\Exception;

use Kant\Exception\KantException;

class UnknownPropertyException extends KantException {

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName() {
        return 'Exception';
    }

}
