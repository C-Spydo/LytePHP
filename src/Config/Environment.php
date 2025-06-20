<?php

declare(strict_types=1);

namespace LytePHP\Config;

use Dotenv\Dotenv;

class Environment
{
    private array $config;
    private bool $isDocker;

    public function __construct(string $rootPath)
    {
        $this->isDocker = $this->detectDocker();
        $this->loadEnvironment($rootPath);
        $this->setDefaults();
    }

    private function detectDocker(): bool
    {
        return file_exists('/.dockerenv') || 
               getenv('DOCKER_CONTAINER') === 'true' ||
               file_exists('/proc/1/cgroup') && str_contains(file_get_contents('/proc/1/cgroup'), 'docker');
    }

    private function loadEnvironment(string $rootPath): void
    {
        // Load .env file if it exists
        if (file_exists($rootPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($rootPath);
            $dotenv->load();
        }

        // Set configuration from environment variables or defaults
        $this->config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'LytePHP',
                'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'port' => (int)($_ENV['APP_PORT'] ?? 8000),
            ],
            'database' => [
                'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'] ?? ($this->isDocker ? 'mysql' : 'localhost'),
                'port' => (int)($_ENV['DB_PORT'] ?? 3306),
                'database' => $_ENV['DB_DATABASE'] ?? 'lytephp',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            ],
            'api' => [
                'prefix' => $_ENV['API_PREFIX'] ?? '/api',
                'cors' => filter_var($_ENV['API_CORS'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'rate_limit' => (int)($_ENV['API_RATE_LIMIT'] ?? 1000),
            ],
            'docs' => [
                'enabled' => filter_var($_ENV['DOCS_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'path' => $_ENV['DOCS_PATH'] ?? '/docs',
                'title' => $_ENV['DOCS_TITLE'] ?? 'LytePHP API Documentation',
            ]
        ];
    }

    private function setDefaults(): void
    {
        // Set default timezone
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');
        
        // Set error reporting based on debug mode
        if ($this->config['app']['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function isDocker(): bool
    {
        return $this->isDocker;
    }

    public function isDevelopment(): bool
    {
        return $this->config['app']['debug'];
    }

    public function getDatabaseConfig(): array
    {
        return $this->config['database'];
    }

    public function getApiConfig(): array
    {
        return $this->config['api'];
    }

    public function getDocsConfig(): array
    {
        return $this->config['docs'];
    }

    public function getAll(): array
    {
        return $this->config;
    }
} 