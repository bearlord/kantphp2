<?php

namespace Kant\Http\File\MimeType;

use Kant\Http\File\Exception\FileNotFoundException;
use Kant\Http\File\Exception\AccessDeniedException;

/**
 * Guesses the mime type using the PECL extension FileInfo.
 *
 */
class FileinfoMimeTypeGuesser implements MimeTypeGuesserInterface
{
    private $magicFile;

    /**
     * Constructor.
     *
     * @param string $magicFile A magic file to use with the finfo instance
     *
     * @link http://www.php.net/manual/en/function.finfo-open.php
     */
    public function __construct($magicFile = null)
    {
        $this->magicFile = $magicFile;
    }

    /**
     * Returns whether this guesser is supported on the current OS/PHP setup.
     *
     * @return bool
     */
    public static function isSupported()
    {
        return function_exists('finfo_open');
    }

    /**
     * {@inheritdoc}
     */
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }

        if (!self::isSupported()) {
            return;
        }

        if (!$finfo = new \finfo(FILEINFO_MIME_TYPE, $this->magicFile)) {
            return;
        }

        return $finfo->file($path);
    }
}
