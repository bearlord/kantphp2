<?php

class HelloWidget extends Widget {
    
    public function foo() {
        echo 'hello world';
        echo $this->view->display('hello/foo');
    }
    //put your code here
}
