<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http\File\MimeType;

use Kant\Http\File\Exception\FileNotFoundException;
use Kant\Http\File\Exception\AccessDeniedException;

/**
 * Guesses the mime type with the binary "file" (only available on *nix).
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FileBinaryMimeTypeGuesser implements MimeTypeGuesserInterface
{

    private $cmd;

    /**
     * Constructor.
     *
     * The $cmd pattern must contain a "%s" string that will be replaced
     * with the file name to guess.
     *
     * The command output must start with the mime type of the file.
     *
     * @param string $cmd
     *            The command to run to get the mime type of a file
     */
    public function __construct($cmd = 'file -b --mime %s 2>/dev/null')
    {
        $this->cmd = $cmd;
    }

    /**
     * Returns whether this guesser is supported on the current OS.
     *
     * @return bool
     */
    public static function isSupported()
    {
        return '\\' !== DIRECTORY_SEPARATOR && function_exists('passthru') && function_exists('escapeshellarg');
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function guess($path)
    {
        if (! is_file($path)) {
            throw new FileNotFoundException($path);
        }
        
        if (! is_readable($path)) {
            throw new AccessDeniedException($path);
        }
        
        if (! self::isSupported()) {
            return;
        }
        
        ob_start();
        
        // need to use --mime instead of -i. see #6641
        passthru(sprintf($this->cmd, escapeshellarg($path)), $return);
        if ($return > 0) {
            ob_end_clean();
            
            return;
        }
        
        $type = trim(ob_get_clean());
        
        if (! preg_match('#^([a-z0-9\-]+/[a-z0-9\-\.]+)#i', $type, $match)) {
            // it's not a type, but an error message
            return;
        }
        
        return $match[1];
    }
}
