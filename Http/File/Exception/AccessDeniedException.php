<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http\File\Exception;

/**
 * Thrown when the access on a file was denied.
 */
class AccessDeniedException extends FileException
{

    /**
     * Constructor.
     *
     * @param string $path
     *            The path to the accessed file
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('The file %s could not be accessed', $path));
    }
}
