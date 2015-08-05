<?php

class FileSystemConsole {

    private static $_fsHandle;
    private $_fsConfig = array();

    public static function getInstance($fsConfig = '') {
        if ($fsConfig == '') {
            $environment = KantRegistry::get('environment');
            $fsConfig = require CFG_PATH . $environment . DIRECTORY_SEPARATOR . 'FsConfig.php';
        }
        if (self::$_fsHandle == '') {
            self::$_fsHandle = new self();
        }
        if ($fsConfig != '' && $fsConfig != self::$_fsHandle->_fsConfig) {
            self::$_fsHandle->_fsConfig = array_merge($fsConfig, self::$_fsHandle->_fsConfig);
        }
        return self::$_fsHandle;
    }

    public function choosePlatform($fsName) {
        switch ($this->_fsConfig[$fsName]['type']) {
            case 'host':
                require_once 'Attachment.php';
                $object = new Attachment();
                break;
            case 'baidubcs':
                require_once 'BaiduBCS/KantBCS.php';
                $bcsConfig = array(
                    'ak' => $this->_fsConfig[$fsName]['ak'],
                    'sk' => $this->_fsConfig[$fsName]['sk'],
                    'host' => 'bcs.duapp.com',
                    'bucket' => $this->_fsConfig[$fsName]['bucket']
                );
                $object = new KantBCS($bcsConfig);
                break;
            case 'sinasae':
                break;
            case 'default' :
                require_once 'Attachment.php';
                $object = new Attachment();
                break;
        }
        return $object;
    }

}

?>
