<?php
declare(strict_types=1);

function router($path, $action = null, $methods = 'POST|GET') {
    static $routes = [];
    
    if(strpos($path, '..') !== false){
        return;
    }
    if ($action) {
        return $routes['(' . $methods . ')_' . $path] = $action;
    }
    $path = server('REQUEST_METHOD').'_'.$path;
    foreach ($routes as $route => $action) {
        $regEx = "~^$route/?$~i";
       
        $matches = [];
        if (!preg_match($regEx, $path, $matches)) {
            continue;
        }
        if (!is_callable($action)) {
            return event(EVENT_404, [$path, 'Route not found']);
        }
        array_shift($matches);
        array_shift($matches);
        $response = $action(...$matches);
        return $response;
    }
    return event(EVENT_404, [$path, 'Route not found']);
}
