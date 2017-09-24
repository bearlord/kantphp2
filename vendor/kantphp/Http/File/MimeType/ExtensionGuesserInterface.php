<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http\File\MimeType;

/**
 * Guesses the file extension corresponding to a given mime type.
 */
interface ExtensionGuesserInterface
{

    /**
     * Makes a best guess for a file extension, given a mime type.
     *
     * @param string $mimeType
     *            The mime type
     *            
     * @return string The guessed extension or NULL, if none could be guessed
     */
    public function guess($mimeType);
}
