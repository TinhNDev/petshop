<?php
class Router
{
    private $db;
    private $routes = [];

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addRoute($method, $pattern, $controller, $action)
    {
        $this->routes[] = compact('method', 'pattern', 'controller', 'action');
    }

    public function dispatch()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $allowed_origins = [
            'http://localhost:8080',
            'http://127.0.0.1:8080',
            'https://9ad9-116-110-40-129.ngrok-free.app'
        ];
        if (in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: {$allowed_origins[0]}");
        }
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Credentials: true');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = '/api/v1';
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        if ($requestUri === '')
            $requestUri = '/';

        foreach ($this->routes as $route) {
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $route['pattern']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                require_once __DIR__ . '/../controllers/' . $route['controller'] . '.php';
                $controller = new $route['controller']($this->db);
                $body = json_decode(file_get_contents('php://input'), true);
                $params = array_merge($matches, [$body]);
                $result = call_user_func_array([$controller, $route['action']], $params);
                http_response_code($result['status'] ?? 200);
                echo json_encode($result['data'] ?? $result);
                return;
            }
        }
        http_response_code(404);
        echo json_encode(['message' => 'Not Found']);
    }
}