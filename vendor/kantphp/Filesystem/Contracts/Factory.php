<?php
namespace Kant\Contracts\Filesystem;

interface Factory
{

    /**
     * Get a filesystem implementation.
     *
     * @param string $name            
     * @return \Kant\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
