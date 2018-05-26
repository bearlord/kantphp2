<?php
namespace Kant\Exception;

use Kant\Exception\Exception;

class UrlGenerationException extends Exception
{

    /**
     * Create a new exception for missing route parameters.
     *
     * @param \Kant\Routing\Route $route            
     * @return static
     */
    public static function forMissingParameters($route)
    {
        return new static("Missing required parameters for [Route: {$route->getName()}] [URI: {$route->uri()}].");
    }
}
