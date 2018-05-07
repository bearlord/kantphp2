<?php

namespace app\middleware;

class Stack
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    public $middleware = [
		
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    public $middlewareGroups = [
        'index' => [
			
        ],
		
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    public $routeMiddleware = [
        'checkage' => CheckAge::class
    ];
}
