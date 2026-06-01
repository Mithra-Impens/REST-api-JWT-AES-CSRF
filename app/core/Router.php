<?php

class Router
{
    private $routes = [];

    public function add($method, $path, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action
        ];
    }

    public function dispatch($method, $uri)
    {
        foreach ($this->routes as $route) {

            $pattern = preg_replace(
                '#\{id\}#',
                '([0-9]+)',
                $route['path']
            );

            $pattern = "#^$pattern$#";

            if (
                $route['method'] === $method &&
                preg_match($pattern, $uri, $matches)
            ) {

                array_shift($matches);

                list($controller, $function) = explode('@', $route['action']);

                $controllerObject = new $controller();

                return call_user_func_array(
                    [$controllerObject, $function],
                    $matches
                );
            }
        }

        Response::json(false, "Route not found", [], 404);
    }
}