<?php

declare(strict_types=1);

namespace LytePHP;

use LytePHP\Config\Environment;
use LytePHP\Core\Router;
use LytePHP\Core\Database;
use LytePHP\Core\Documentation;

class Application
{
    private Environment $env;
    private Router $router;
    private Database $database;
    private Documentation $docs;

    public function __construct(Environment $env)
    {
        $this->env = $env;
        $this->initialize();
    }

    private function initialize(): void
    {
        // Initialize core components
        $this->database = new Database($this->env);
        $this->router = new Router();
        $this->docs = new Documentation($this->env);
        
        // Setup routes
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        // API routes
        $this->router->get('/api/records/{table}', [$this, 'handleRecords']);
        $this->router->get('/api/records/{table}/{id}', [$this, 'handleRecord']);
        $this->router->post('/api/records/{table}', [$this, 'handleCreate']);
        $this->router->put('/api/records/{table}/{id}', [$this, 'handleUpdate']);
        $this->router->delete('/api/records/{table}/{id}', [$this, 'handleDelete']);
        
        // Documentation routes
        $this->router->get('/docs', [$this, 'handleDocs']);
        $this->router->get('/docs/swagger.json', [$this, 'handleSwaggerJson']);
        
        // Health check
        $this->router->get('/health', [$this, 'handleHealth']);
        
        // Root route
        $this->router->get('/', [$this, 'handleRoot']);
    }

    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    // Route handlers
    public function handleRoot(): void
    {
        $this->sendJson([
            'message' => 'Welcome to LytePHP',
            'version' => '1.0.0',
            'docs' => '/docs',
            'api' => '/api/records'
        ]);
    }

    public function handleHealth(): void
    {
        $this->sendJson([
            'status' => 'healthy',
            'timestamp' => date('c'),
            'database' => $this->database->isConnected() ? 'connected' : 'disconnected'
        ]);
    }

    public function handleDocs(): void
    {
        $this->docs->renderSwaggerUI();
    }

    public function handleSwaggerJson(): void
    {
        $this->sendJson($this->docs->generateOpenAPISpec());
    }

    public function handleRecords(string $table): void
    {
        $result = $this->database->getRecords($table, $_GET);
        $this->sendJson($result);
    }

    public function handleRecord(string $table, string $id): void
    {
        $result = $this->database->getRecord($table, $id);
        $this->sendJson($result);
    }

    public function handleCreate(string $table): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->database->createRecord($table, $data);
        $this->sendJson($result, 201);
    }

    public function handleUpdate(string $table, string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->database->updateRecord($table, $id, $data);
        $this->sendJson($result);
    }

    public function handleDelete(string $table, string $id): void
    {
        $result = $this->database->deleteRecord($table, $id);
        $this->sendJson($result);
    }

    private function sendJson(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function handleError(\Exception $e): void
    {
        $this->sendJson([
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ], 500);
    }
} 