<?php

class ListController extends BaseController {

    public function indexAction() {
        $CategoryModel = $this->model('Category');
//        var_dump($CategoryModel);
//        $row = $CategoryModel->read("category_title, category_description", "", 15);
        $row = $CategoryModel->getinfo();
        var_dump($row);
    }

}
