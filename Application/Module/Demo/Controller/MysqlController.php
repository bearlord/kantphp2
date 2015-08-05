<?php

class MysqlController extends BaseController {

    protected $mdM;

    public function __construct() {
        parent::__construct();
        $this->mdM = $this->model("MysqlDemo");
    }

    /**
     * 所有信息
     */
    public function indexAction() {
        var_dump($this->get);
        var_dump($_GET);
        $row = $this->mdM->readAll("", "", 1);
        //如果readAll第三个参数为true,返回$row[0]为结果集，$row[1]为个数
        //如果readAll第三个参数为false,返回$row[0]为结果集
        $this->view->result = $row;
        $this->view->display();
    }

    /**
     * 单条记录信息
     */
    public function infoAction() {
        $id = get('id');
        $row = $this->mdM->getUserByid($id);
        $this->view->result = $row[0];
        $this->view->display();
    }

    /*
     * 分页显示
     */

    public function pageAction() {
        //每页5条记录
        $perNum = 5;
        //当前页
        $page = get('page');
        //获取数据
        //pagelist参数分别为select,where,orderby,NowPage,PerNum
        $data = $this->mdM->pageList("", "", "", $page, $perNum);
        $this->library("Page");
        $pageObj = new Page($data[1], $perNum);
        $pages = $pageObj->show();
        $this->view->result = $data[0];
        $this->view->pages = $pages;
        $this->view->display();
    }

}

?>
