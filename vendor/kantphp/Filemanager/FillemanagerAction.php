<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Filemanager;

use FilesystemIterator;
use Kant\Action\Action;
use Kant\Http\Cookie;
use Kant\Kant;

class FillemanagerAction extends Action
{

    public $viewPath = __DIR__ . '/View/';
    public $layout = __DIR__ . '/View/main.php';
    public $title = "File Manager";

    /**
     * @var Resource base path
     */
    public $basePath;

    /**
     * @var Base url
     */
    public $baseUrl;

    public $thumbUrl;

    /**
     * @var Thumb base path
     */
    public $thumBasePath;

    /**
     * @var Current path;
     */
    public $currentPath;

    /**
     * @var Current folder number
     */
    public $currentFoldersNumber = 0;

    /**
     * @var Current files number
     */
    public $currentFilesNumber = 0;


    /**
     * @var sub path
     */
    protected $subPath;

    public $i18n;


    /**
     * @var array $allowedExtension allowed extensions
     */
    public $convention = [
        'allowedExtensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff',
            'svg', 'doc', 'docx', 'rtf', 'pdf', 'xls', 'xlsx', 'txt', 'csv',
            'ppt', 'pptx', 'odt', 'ots', 'ott', 'odb', 'odg', 'otp', 'otg', 'odf', 'ods', 'odp',
            'html', 'xhtml', 'css',
            'psd', 'sql', 'log', 'fla', 'xml', 'ade', 'adp', 'mdb', 'accdb',
            'ai', 'kmz', 'dwg', 'dxf', 'hpgl', 'plt', 'spl', 'step', 'stp', 'iges', 'igs', 'sat', 'cgm',
            'zip', 'rar', 'gz', 'tar', 'iso', 'dmg',
            'mov', 'mpeg', 'm4v', 'mp4', 'avi', 'mpg', 'wma', 'flv', 'webm',
            'mp3', 'mpga', 'm4a', 'ac3', 'aiff', 'mid', 'ogg', 'wav'
        ],
        'maxFilesize' => 10,
        'copyCutFilesAllowed' => true,
        'copyCutDirsAllowed' => true,
        'chmodFilesAllowed' => true,
        'chmodDirsAllowed' => true
    ];

    /**
     * @var Extensions
     */
    public $exts = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg'], //Images
        'file' => ['doc', 'docx', 'rtf', 'pdf', 'xls', 'xlsx', 'txt', 'csv', 'html', 'xhtml', 'psd', 'sql', 'log', 'fla', 'xml', 'ade', 'adp', 'mdb', 'accdb', 'ppt', 'pptx', 'odt', 'ots', 'ott', 'odb', 'odg', 'otp', 'otg', 'odf', 'ods', 'odp', 'css', 'ai', 'kmz', 'dwg', 'dxf', 'hpgl', 'plt', 'spl', 'step', 'stp', 'iges', 'igs', 'sat', 'cgm'], //Files
        'video' => ['mov', 'mpeg', 'm4v', 'mp4', 'avi', 'mpg', 'wma', "flv", "webm"], //Video
        'music' => ['mp3', 'mpga', 'm4a', 'ac3', 'aiff', 'mid', 'ogg', 'wav'], //Audio
        'misc' => ['zip', 'rar', 'gz', 'tar', 'iso', 'dmg'], //Archives
    ];

    /**
     * @var Filter extensions
     */
    public $filterExts;

    /**
     * @var Filter buttons visible
     */
    public $filterButtnsVisible;

    public $clientOptions = [

    ];

    /**
     * Entrance
     *
     * @return mixed
     */
    public function run()
    {
        $this->controller->view->layout = $this->layout;
        $this->controller->view->i18n = Kant::createObject(I18n::class);

        $this->clientOptions = array_merge($this->convention, $this->clientOptions);
        $path = $this->getSubPath();
        
        $back = str_replace("\\", "/", dirname($path));
        $nav = $this->setNavLi($path);
        
        $currentPath = $this->getFullPath($path);
        $this->setCurrentPath($currentPath);

        $action = Kant::$app->request->get('action');
        if (!empty($action)) {
            return call_user_func(['self', $action]);
        }

        $fieldid = Kant::$app->request->get('fieldid');
        $type = Kant::$app->request->get('type');
        
        $apply = $this->setApply($type, $fieldid);
        $this->setFilterType($type);

        $files = $this->getAllFilesByDirectory($currentPath);
        return $this->controller->view->render($this->viewPath . 'index.php', [
            'title' => $this->title,
            'homeUrl' => Kant::$app->request->getPathInfo(),
            'fieldid' => $fieldid,
            'type' => $type,
            'apply' => $apply,
            'path' => $path,
            'nav' => $nav,
            'back' => $back,
            'currentFoldersNumber' => $this->currentFoldersNumber,
            'currentFilesNumber' => $this->currentFilesNumber,
            'filterButtnsVisible' => $this->filterButtnsVisible,
            'files' => $files,
            'clientOptions' => $this->clientOptions,
            'exts' => $this->exts,
            'allowedExtensions' => $this->clientOptions['allowedExtensions']
        ]);
    }


    /**
     * Get sub path
     */
    protected function getSubPath()
    {
        $path = rawurldecode(Kant::$app->request->get('path', '/'));
        if (!empty($path) && strpos($path, "../") == false
            && strpos($path, "./") == false
            && strpos($path, "..\\") == false
            && strpos($path, ".\\") == false) {
            $subPath = "/" . trim(strip_tags(rawurldecode($path)), "/");
        } else {
            $subPath = "";
        }

        if (empty($subPath)) {
            $subPath = $this->getLastPosition();
        }
        $this->subPath = $subPath;
        return $subPath;
    }

    /**
     * Get full path of subPath
     * @param $subPath
     */
    public function getFullPath($subPath)
    {
        return file_exists($this->basePath . "/" . $subPath) ? $this->basePath . "/" . $subPath : $this->basePath;
    }

    /**
     * Set current path
     */
    public function setCurrentPath($path)
    {
        $this->currentPath = $path;
    }
    
    /**
     * Set apply function
     * @param type $field
     */
    public function setApply($type = "", $fieldid = "") {
        if (in_array($type, ['image', 'media', 'files', 'all'])) {
            $apply = "apply" . ucfirst($type);
        } else {
            $apply = "applyAny";
        }
        return $apply;
    }

    /**
     * Set filter extions
     */
    public function setFilterType($type)
    {
        if (!empty($type)) {
            if (in_array($type, array_keys($this->exts))) {
                $this->filterButtnsVisible = false;
                $this->filterExts = $this->exts[$type];
            } elseif ($type == 'media') {
                $this->filterButtnsVisible = false;
                $this->filterExts = array_merge($this->exts['video'], $this->exts['music']);
            } elseif ($type == 'files') {
                $this->filterButtnsVisible = true;
                $this->filterExts  = [];
            }
        } else {
            $this->filterButtnsVisible = true;
            $this->filterExts  = [];
        }
    }

    /**
     * Get Last Position
     */
    public function getLastPosition()
    {
        $lastPosition = Kant::$app->request->cookie('last_position');

    }

    /**
     * Set Last Position
     *
     * @param $path
     */
    public function setLastPosition($path)
    {
        Kant::$app->response->cookie(new Cookie(
            'last_position', $path
        ));
    }

    /**
     * Get all files of directrory
     * @param $path
     */
    public function getAllFilesByDirectory($path)
    {
        if (file_exists($path) === false) {
            return;
        }
        $result = [];

        $items = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

        $k = 0;
        foreach ($items as $item) {
            if (!empty($this->filterExts)) {
                if (!in_array($item->getExtension(), $this->filterExts)) {
                    continue;
                }
            }

            /**
             * @var $item \SplFileInfo
             */
            if ($item->isDir()) {
                $this->currentFoldersNumber++;
            } else {
                $this->currentFilesNumber++;
            }

            $result[$k] = [
                'isDir' => $item->isDir(),
                'file' => $item->getBasename(),
                'fileLowcase' => strtolower($item->getBasename()),
                'date' => $item->getMTime(),
                'size' => $item->getSize(),
                'extension' => $item->getExtension(),
                'type' => $this->getType($item->getExtension()),
                'fileUrl' => $this->baseUrl . $item->getFilename(),
                'relativePath' => $this->subPath . "/" . $item->getFilename() . "/",
                'thumb' => $this->getThumb($item)
            ];
            $k++;
        }
        return $result;

    }

    /**
     * Get Item type form extension
     * @param $ext
     */
    public function getType($ext)
    {
        foreach ($this->exts as $k => $v) {
            if (in_array($ext, $v)) {
                return $k;
            }
        }
    }

    /**
     * @param $item \SplFileInfo
     */
    protected function getThumb($item)
    {
        if ($this->isImage($item->getExtension())) {
            if (!file_exists($this->thumBasePath . $item->getFilename())) {
                return $this->baseUrl . $item->getFilename();
            }
        }
        if ($item->isDir()) {
            return $this->thumbUrl . "ico/folder.png";
        }

        return $this->thumbUrl . "ico/" . $item->getExtension() . ".jpg";
    }


    protected static function filenameSort($x, $y)
    {
        return $x['file_lcase'] < $y['file_lcase'];
    }

    protected static function dateSort($x, $y)
    {
        return $x['date'] < $y['date'];
    }

    protected static function sizeSort($x, $y)
    {
        return $x['size'] < $y['size'];
    }

    protected static function extensionSort($x, $y)
    {
        return $x['extension'] < $y['extension'];
    }


    /**
     * Check is image
     *
     * @param $ext
     * @return bool
     */
    protected function isImage($ext)
    {
        return in_array($ext, $this->exts['image']);
    }

    /**
     * Check is file
     *
     * @param $ext
     * @return bool
     */
    protected function isFile($ext)
    {
        return in_array($ext, $this->exts['file']);
    }

    /**
     * Check is file
     *
     * @param $ext
     * @return bool
     */
    protected function isVideo($ext)
    {
        return in_array($ext, $this->exts['video']);
    }

    /**
     * Check is file
     *
     * @param $ext
     * @return bool
     */
    protected function isMusic($ext)
    {
        return in_array($ext, $this->exts['music']);
    }

    /**
     * Check is file
     *
     * @param $ext
     * @return bool
     */
    protected function isMisc($ext)
    {
        return in_array($ext, $this->exts['misc']);
    }

    /**
     *
     */
    public function upload()
    {
        $file = Kant::$app->request->file('file');
        if (empty($file)) {
            return;
        }

        $fileSavePath = $this->basePath;

        $fileName = $file->getClientOriginalName();

        $fileExtension = $file->getClientOriginalExtension();

        $fileBaseName = $file->getClientOriginalBaseName();


        if (in_array($fileExtension, $this->clientOptions['allowedExtensions'])) {
            if (file_exists($fileSavePath . "/" . $fileName)) {

                $i = 1;
                while (file_exists($fileSavePath . "/" . $fileBaseName . "_" . $i . "." . $fileExtension)) {
                    $i++;
                }
                $fileName = $fileBaseName . "_" . $i . "." . $fileExtension;

            }
            $file->move($fileSavePath, $fileName);
        }

        return $fileName;
    }

    public function setNavLi($path)
    {
        if (empty($path)) {
            return;
        }
        if ($path == '/') {
            return;
        }
        $parr = explode("/", trim($path, "/"));

        $sarr[] = "<li class=\"active\">" . array_pop($parr) . "</li>";          
        while(!empty($parr)) {
            $end = end($parr);
            $sarr[] = "<li><a href=\"" . \Kant\Helper\Url::current(['path' => implode("/", $parr)]) . "\">" . $end . "</a></li>";
            array_pop($parr);
        }
        rsort($sarr);
        
        $res = implode("", $sarr);
        return $res;
    }

}
