<?php

namespace Kant\Http\File\Exception;

/**
 * Thrown when the access on a file was denied.
 *
 */
class AccessDeniedException extends FileException
{
    /**
     * Constructor.
     *
     * @param string $path The path to the accessed file
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('The file %s could not be accessed', $path));
    }
}
