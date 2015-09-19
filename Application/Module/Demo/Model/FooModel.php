<?php

namespace Demo\Model;

use Kant\Model\BaseModel;

/**
 * Description of FooDemoModel
 *
 * @author zhangzhenqiang
 */
class FooModel extends BaseModel {

    public function loop() {
        for ($i = 0; $i < 100; $i++) {
            $this->doit();
            sleep(10);
        }
    }

    public function doit() {
        $FootwoModel = new FootwoModel();
        $FootwoModel->write();
    }

}
