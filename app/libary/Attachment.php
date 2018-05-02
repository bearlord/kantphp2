<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
class Attachment {

    //field name
    protected $field;
    //error
    public $error;
    //allow ext
    public $allowExts = 'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|tar.gz|tar.bz2|swf';
    //maxsize
    public $maxFilesize = 2048000;
    //savePath
    public $savePath;
    //save url
    public $saveUri;
    //uploadfiles' number
    private $uploads = 0;
    //uploadedfiles' number
    private $uploadeds;
    public $files;

    public function __construct() {
        $this->createSavePath();
    }

    /**
     * Upload files
     * 
     * @param type $field
     * @param type $overwrite
     * @return boolean
     */
    public function upload($field, $overwrite = 0) {
        $uploadfiles = $this->getUploadFiles($field);
        if ($uploadfiles == false) {
            return;
        }
        $i = 0;
        foreach ($uploadfiles as $key => $file) {
            $fileext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if ($file['error'] != 0) {
                $this->error = $file['error'];
                return false;
            }
            if (!preg_match("/^(" . $this->allowExts . ")$/", $fileext)) {
                $this->error = '10';
                return false;
            }
            if ($this->maxFilesize && $file['size'] > $this->maxFilesize) {
                $this->error = '11';
                return false;
            }
            if (is_uploaded_file($file['tmp_name']) == false) {
                $this->error = '12';
                return false;
            }
            $savefilename = date('Ymdhis') . rand(100, 999) . '.' . $fileext;
            $savefile = $this->savePath . $savefilename;
            if (!$overwrite && file_exists($savefile)) {
                continue;
            }
            if (@copy($file['tmp_name'], $savefile)) {
                $this->uploadeds++;
                @chmod($savefile, 0644);
                @unlink($file['tmp_name']);
                $files = $savefile;
                $this->files[$i]['path'] = $savefile;
                $this->files[$i]['uri'] = $this->saveUri . $savefilename;
            }
            $i++;
        }
        return $files;
    }

    public function getUploadFiles($field) {
        $uploadfiles = null;
        if (!isset($_FILES[$field])) {
            $this->error = UPLOAD_ERR_OK;
            return false;
        } elseif (is_array($_FILES[$field]['error'])) {
            $this->uploads = count($_FILES[$field]['error']);
            foreach ($_FILES[$field]['error'] as $key => $error) {
                if ($error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($error !== UPLOAD_ERR_OK) {
                    $this->error = $error;
                    return false;
                }
                $_key = substr(md5($_FILES[$field]['tmp_name'][$key]), 0, 6);
                $uploadfiles[$_key] = array(
                    'tmp_name' => $_FILES[$field]['tmp_name'][$key],
                    'name' => $_FILES[$field]['name'][$key],
                    'type' => $_FILES[$field]['type'][$key],
                    'size' => $_FILES[$field]['size'][$key],
                    'error' => $_FILES[$field]['error'][$key]
                );
            }
        } else {
            $this->uploads++;
            $error = $_FILES[$field]['error'];
            if ($error !== UPLOAD_ERR_OK) {
                $this->error = $error;
                return false;
            }
            $_key = substr(md5($_FILES[$field]['tmp_name']), 0, 6);
            $uploadfiles[$_key] = array(
                'tmp_name' => $_FILES[$field]['tmp_name'],
                'name' => $_FILES[$field]['name'],
                'type' => $_FILES[$field]['type'],
                'size' => $_FILES[$field]['size'],
                'error' => $_FILES[$field]['error']
            );
        }
        return $uploadfiles;
    }

    public function getUploaded() {
        return $this->uploadeds;
    }

    /**
     * Create save path;
     * 
     * @return boolean
     */
    public function createSavePath() {
        require_once KANT_PATH . '/Help/Dir.php';
        if ($this->savePath == '') {
            $dirSetting = 'uploads/' . date('Ym') . '/';
            $this->savePath = PUBLIC_PATH . $dirSetting;
            $this->saveUri = PUBLIC_URL . $dirSetting;
        }
        if (!Dir::create($this->savePath)) {
            $this->error = '8';
            return false;
        }
        if (!is_dir($this->savePath)) {
            $this->error = '8';
            return false;
        }
        @chmod($this->savePath, 0777);
        if (!is_writeable($this->savePath)) {
            $this->error = '9';
            return false;
        }
        return true;
    }

}

?>
