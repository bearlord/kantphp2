<?php
namespace Kant\Routing\Matching;

use Kant\Http\Request;
use Kant\Routing\Route;

class SchemeValidator implements ValidatorInterface
{

    /**
     * Validate a given rule against a route and request.
     *
     * @param \Kant\Routing\Route $route            
     * @param \Kant\Http\Request $request            
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        if ($route->httpOnly()) {
            return ! $request->secure();
        } elseif ($route->secure()) {
            return $request->secure();
        }
        
        return true;
    }
}
