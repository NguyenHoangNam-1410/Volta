<?php
// app/helpers/Router.php

class Router {
    private $routes = [];
    private $path;
    private $method;

    public function __construct() {
        $this->path = $this->parsePath();
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    private function parsePath() {
        $request = $_SERVER['REQUEST_URI'];
        $path = parse_url($request, PHP_URL_PATH);
        return str_replace('/volta/public', '', $path);
    }

    public function get($pattern, $callback) {
        $this->registerRoute('GET', $pattern, $callback);
    }

    public function post($pattern, $callback) {
        $this->registerRoute('POST', $pattern, $callback);
    }

    public function put($pattern, $callback) {
        $this->registerRoute('PUT', $pattern, $callback);
    }

    public function delete($pattern, $callback) {
        $this->registerRoute('DELETE', $pattern, $callback);
    }

    private function registerRoute($method, $pattern, $callback) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }

    public function dispatch() {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $this->method) {
                continue;
            }

            if ($this->matchPattern($route['pattern'], $matches)) {
                return $this->executeCallback($route['callback'], $matches);
            }
        }

        // No route found
        http_response_code(404);
        include '../app/views/public/404.php';
        exit;
    }

    private function matchPattern($pattern, &$matches) {
        // Exact match
        if ($pattern === $this->path) {
            $matches = [];
            return true;
        }

        // Regex match
        if (preg_match($pattern, $this->path, $matches)) {
            array_shift($matches);
            return true;
        }

        return false;
    }

    private function executeCallback($callback, $params) {
        if (is_string($callback)) {
            // 'ControllerName@methodName' format
            list($controller, $method) = explode('@', $callback);
            require_once "../app/controllers/{$controller}.php";
            $instance = new $controller();
            return $instance->$method(...$params);
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
    }
}
