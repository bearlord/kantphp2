<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Log;

use Kant\Kant;
use Kant\Log\Target;
use Kant\Helper\DirHelper;
use Kant\Exception\InvalidConfigException;

class FileTarget extends Target
{

    /**
     *
     * @var string log file path or path alias. If not set, it will use the "@runtime/logs/app.log" file.
     *      The directory containing the log files will be automatically created if not existing.
     */
    public $logFile;

    /**
     *
     * @var bool whether log files should be rotated when they reach a certain [[maxFileSize|maximum size]].
     *      Log rotation is enabled by default. This property allows you to disable it, when you have configured
     *      an external tools for log rotation on your server.
     * @since 2.0.3
     */
    public $enableRotation = true;

    /**
     *
     * @var integer maximum log file size, in kilo-bytes. Defaults to 10240, meaning 10MB.
     */
    public $maxFileSize = 10240;
 // in KB
    /**
     *
     * @var integer number of log files used for rotation. Defaults to 5.
     */
    public $maxLogFiles = 5;

    /**
     *
     * @var integer the permission to be set for newly created log files.
     *      This value will be used by PHP chmod() function. No umask will be applied.
     *      If not set, the permission will be determined by the current environment.
     */
    public $fileMode;

    /**
     *
     * @var integer the permission to be set for newly created directories.
     *      This value will be used by PHP chmod() function. No umask will be applied.
     *      Defaults to 0775, meaning the directory is read-writable by owner and group,
     *      but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     *
     * @var boolean Whether to rotate log files by copy and truncate in contrast to rotation by
     *      renaming files. Defaults to `true` to be more compatible with log tailers and is windows
     *      systems which do not play well with rename on open files. Rotation by renaming however is
     *      a bit faster.
     *     
     *      The problem with windows systems where the [rename()](http://www.php.net/manual/en/function.rename.php)
     *      function does not work with files that are opened by some process is described in a
     *      [comment by Martin Pelletier](http://www.php.net/manual/en/function.rename.php#102274) in
     *      the PHP documentation. By setting rotateByCopy to `true` you can work
     *      around this problem.
     */
    public $rotateByCopy = true;

    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        if ($this->logFile === null) {
            $this->logFile = Kant::getAlias('@log_path/') . date("Y-m-d") . '/app.log';
        }
        
        $logPath = dirname($this->logFile);
        if (!is_dir($logPath)) {
            DirHelper::create($logPath, $this->dirMode, true);
        }
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
    }

    /**
     * Writes log messages to a file.
     * 
     * @throws InvalidConfigException if unable to open the log file for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([
            $this,
            'formatMessage'
        ], $this->messages)) . "\n";
        
        if (($fp = @fopen($this->logFile, 'a')) === false) {
            throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
        }
        @flock($fp, LOCK_EX);
        if ($this->enableRotation) {
            // clear stat cache to ensure getting the real current file size and not a cached one
            // this may result in rotating twice when cached file size is used on subsequent calls
            clearstatcache();
        }
        if ($this->enableRotation && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            $this->rotateFiles();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
        } else {
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    /**
     * Rotates log files.
     */
    protected function rotateFiles()
    {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i >= 0; -- $i) {
            // $i == 0 is the original log file
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            if (is_file($rotateFile)) {
                // suppress errors because it's possible multiple processes enter into this section
                if ($i === $this->maxLogFiles) {
                    @unlink($rotateFile);
                } else {
                    if ($this->rotateByCopy) {
                        @copy($rotateFile, $file . '.' . ($i + 1));
                        if ($fp = @fopen($rotateFile, 'a')) {
                            @ftruncate($fp, 0);
                            @fclose($fp);
                        }
                    } else {
                        @rename($rotateFile, $file . '.' . ($i + 1));
                    }
                }
            }
        }
    }
}
