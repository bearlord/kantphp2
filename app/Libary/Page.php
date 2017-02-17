<?php

class Page extends Base {

    // First row
    public $firstRow;
    // Per page number
    public $listRows;
    // Page parameter
    public $parameter;
    // Total page Number
    protected $totalPages;
    // Total row number
    protected $totalRows;
    // now page
    protected $nowPage;
    // 分页显示定制
    public $config = array('preview' => 'Preview', 'next' => 'Next');
    //处理情况 Ajax分页 Html分页(静态化时) 普通get方式 
    protected $method = 'default';
    //ajax分页时函数
    protected $ajaxFunction = 'ajaxpage';
    // Per page number
    const PAGE_LISTROWS = 20;
    // Default page variable
    const VAR_PAGE = 'page';

    /**
     * Construct function
     * 
     * @param integer $totalRows
     * @param integer $listRows
     * @param string $parameter
     */
    public function __construct($totalRows, $listRows, $parameter = '') {
        parent::__construct();
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->listRows = !empty($listRows) ? $listRows : self::PAGE_LISTROWS; // 分页显示页数
        $this->totalPages = ceil($this->totalRows / $this->listRows);  //总页数
        $this->nowPage = !empty($_GET[self::VAR_PAGE]) ? $_GET[self::VAR_PAGE] : 1;
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows * ($this->nowPage - 1);
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setConfig($name, $value) {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 
     * @param type $name
     * @param type $value
     */
    protected function setParameter($value) {
        $this->parameter = $value;
    }

    /**
     * 
     * @param string $var
     */
    public function setMethod($var) {
        $this->method = $var;
    }

    /**
     * 
     * @param type $page
     * @param type $text
     * @return type
     */
    protected function getLink($page, $text) {
        switch ($this->method) {
            case 'ajax':
                $parameter = '';
                return '<li><a onclick="' . $this->ajaxFunction . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a></li>';
                break;
            case 'html':
                $url = str_replace('?', $page, $this->parameter);
                return '<li><a  href="' . $page . '">' . $text . '</a></li>';
                break;
            default:
                return '<li><a href="' . $this->getUrl($page) . '">' . $text . '</a></li>';
                break;
        }
    }

    /**
     * 设置当前页面链接
     */
    protected function getUrl($page) {
        $url = $_SERVER['REQUEST_URI'];
        $parse = parse_url($url);
        if (isset($parse['query'])) {
            parse_str($parse['query'], $params);
            $params[self::VAR_PAGE] = $page;
            if (!empty($params)) {
                $url = $parse['path'] . '?' . http_build_query($params);
            } else {
                $url = $parse['path'] . '?' . http_build_query($params);
            }
        } else {
            $params[self::VAR_PAGE] = $page;
            $url = $parse['path'] . '?' . http_build_query($params);
        }
        return $url;
    }

    /**
     * 
     */
    public function show($theme = 'default') {
        if (0 == $this->totalRows) {
            return '';
        }
        $methodName = "show" . ucfirst($theme);
        if (method_exists($this, $methodName)) {
            $show = call_user_func(array($this, $methodName));
            return $show;
        }
    }

    /**
     * 
     */
    protected function showDefault() {
        $pageString = "";
        $linkPage = "";
        if ($this->totalPages <= 1) {
            return false;
        }
        $linkPage .= $this->previewPage();
        for ($i = 1; $i <= $this->totalPages; $i++) {
            if ($i == $this->nowPage) {
                $linkPage .= "<li class='active'><a>$i</a></li>";
            } else {
                if ($this->nowPage - $i >= 4 && $i != 1) {
                    $linkPage .="<li>...</li>";
                    $i = $this->nowPage - 3;
                } else {
                    if ($i >= $this->nowPage + 5 && $i != $this->totalPages) {
                        $linkPage .="<li><span>...</span></li>";
                        $i = $this->totalPages;
                    }
                    $linkPage .= $this->getLink($i, $i);
                }
            }
        }
        $linkPage .= $this->nextPage();
        $pageString = $linkPage;
        return $pageString;
    }

    /**
     * 
     * @param type $name
     * @return string
     */
    protected function previewPage($name = '') {
        if ($name == '') {
            $name = $this->config['preview'];
        }
        if ($this->totalRows != 0) {
            return $this->getLink($this->nowPage - 1, $name);
        }
    }

    /**
     * 
     */
    protected function nextPage($name = '') {
        if ($name == '') {
            $name = $this->config['next'];
        }
        if ($this->nowPage < $this->totalPages) {
            return $this->getLink($this->nowPage + 1, $name);
        }
    }

}

?>