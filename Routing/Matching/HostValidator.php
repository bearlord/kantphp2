<?php
namespace Kant\Routing\Matching;

use Kant\Http\Request;
use Kant\Routing\Route;

class HostValidator implements ValidatorInterface
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
        if (is_null($route->getCompiled()->getHostRegex())) {
            return true;
        }
        
        return preg_match($route->getCompiled()->getHostRegex(), $request->getHost());
    }
}
