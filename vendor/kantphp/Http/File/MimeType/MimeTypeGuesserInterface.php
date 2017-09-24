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
 * Guesses the mime type of a file.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface MimeTypeGuesserInterface
{

    /**
     * Guesses the mime type of the file with the given path.
     *
     * @param string $path
     *            The path to the file
     *            
     * @return string The mime type or NULL, if none could be guessed
     *        
     * @throws FileNotFoundException If the file does not exist
     * @throws AccessDeniedException If the file could not be read
     */
    public function guess($path);
}
