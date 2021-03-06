<?php
declare(strict_types=1);

function router($path = null, $action = null, $methods = 'POST|GET',bool $directRequestDisabled = false) {
    static $routes = [];
    
    if(!$path){
        return $routes;
    }
    if(strpos($path, '..') !== false){
        return;
    }
    
    if ($action) {
        return $routes['(' . $methods . ')_' . $path] = [$action,$directRequestDisabled];
    }
    $originalPath = str_replace('?'.server('QUERY_STRING'), '', $path);
    $path = server('REQUEST_METHOD').'_'.$originalPath;
    foreach ($routes as $route => $data) {
        list($action,$currentDirectRequestIsDisabled) = $data;
        $regEx = "~^$route/?$~i";
       
        $matches = [];
        if (!preg_match($regEx, $path, $matches)) {
            continue;
        }
        if (!is_callable($action)) {
            return event(EVENT_404, [$path, 'Route not found']);
        }
        if($currentDirectRequestIsDisabled && server('REQUEST_URI') && server('REQUEST_URI') === $originalPath){
             return event(EVENT_404, [$path, 'Route not found']);
        }
        array_shift($matches);
        array_shift($matches);
        event(EVENT_BEFORE,$matches);
        $response = $action(...$matches);
        event(EVENT_AFTER,$matches);
        return $response;
    }
    return event(EVENT_404, [$path, 'Route not found']);
}
