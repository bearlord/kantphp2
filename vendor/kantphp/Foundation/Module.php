<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Foundation;

use Kant\Kant;
use Kant\Di\ServiceLocator;

class Module extends ServiceLocator {

    /**
     * @var string the root directory of the module.
     */
    private $_basePath;
    private $_vendorPath;

    /**
     * Returns the root directory of the module.
     * It defaults to the directory containing the module class file.
     * @return string the root directory of the module.
     */
    public function getBasePath() {
        if ($this->_basePath === null) {
            $class = new \ReflectionClass($this);
            $this->_basePath = dirname($class->getFileName());
        }
        return $this->_basePath;
    }

    /**
     * Returns the directory that stores vendor files.
     * @return string the directory that stores vendor files.
     * Defaults to "vendor" directory under [[basePath]].
     */
    public function getVendorPath() {
        if ($this->_vendorPath === null) {
            $this->setVendorPath(dirname(dirname($this->getBasePath())) . '/vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     * @param string $path the directory that stores vendor files.
     */
    public function setVendorPath($path) {
        $this->_vendorPath = Kant::getAlias($path);
        Kant::setAlias('@vendor', $this->_vendorPath);
        Kant::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower');
    }

}
