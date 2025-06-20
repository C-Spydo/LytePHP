<?php

declare(strict_types=1);

namespace LytePHP\Core;

class Router
{
    private array $routes = [];
    private string $method;
    private string $uri;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->pathToPattern($path)
        ];
    }

    private function pathToPattern(string $path): string
    {
        return '#^' . preg_replace('#\{([^}]+)\}#', '([^/]+)', $path) . '$#';
    }

    public function dispatch(): void
    {
        // Handle OPTIONS for CORS
        if ($this->method === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            http_response_code(200);
            return;
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $this->method && preg_match($route['pattern'], $this->uri, $matches)) {
                array_shift($matches); // Remove the full match
                
                // Extract parameters from path
                $params = $this->extractParams($route['path'], $matches);
                
                // Call the handler
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        // No route found
        $this->handleNotFound();
    }

    private function extractParams(string $path, array $matches): array
    {
        preg_match_all('#\{([^}]+)\}#', $path, $paramNames);
        $paramNames = $paramNames[1];
        
        $params = [];
        foreach ($paramNames as $index => $name) {
            $params[] = $matches[$index] ?? null;
        }
        
        return $params;
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Route not found',
            'method' => $this->method,
            'uri' => $this->uri
        ]);
    }
} 