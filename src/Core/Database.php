<?php

declare(strict_types=1);

namespace LytePHP\Core;

use LytePHP\Config\Environment;
use PDO;
use PDOException;

class Database
{
    private Environment $env;
    private ?PDO $connection = null;
    private array $config;

    public function __construct(Environment $env)
    {
        $this->env = $env;
        $this->config = $env->getDatabaseConfig();
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = $this->buildDsn();
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            if ($this->env->isDevelopment()) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
            throw new \Exception("Database connection failed");
        }
    }

    private function buildDsn(): string
    {
        $driver = $this->config['driver'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'];

        switch ($driver) {
            case 'mysql':
                return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
            case 'pgsql':
                return "pgsql:host={$host};port={$port};dbname={$database}";
            case 'sqlite':
                return "sqlite:{$database}";
            default:
                throw new \Exception("Unsupported database driver: {$driver}");
        }
    }

    public function isConnected(): bool
    {
        try {
            $this->connection?->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getRecords(string $table, array $params = []): array
    {
        $sql = "SELECT * FROM {$table}";
        $conditions = [];
        $values = [];

        // Handle filters
        if (isset($params['filter'])) {
            $filters = is_array($params['filter']) ? $params['filter'] : [$params['filter']];
            foreach ($filters as $filter) {
                $parts = explode(',', $filter);
                if (count($parts) >= 3) {
                    $column = $parts[0];
                    $operator = $parts[1];
                    $value = $parts[2];
                    $conditions[] = "{$column} {$operator} ?";
                    $values[] = $value;
                }
            }
        }

        // Handle search
        if (isset($params['search'])) {
            $search = $params['search'];
            $columns = $this->getSearchableColumns($table);
            $searchConditions = [];
            foreach ($columns as $column) {
                $searchConditions[] = "{$column} LIKE ?";
                $values[] = "%{$search}%";
            }
            if (!empty($searchConditions)) {
                $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
            }
        }

        // Add WHERE clause if conditions exist
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Handle ordering
        if (isset($params['order'])) {
            $order = $params['order'];
            $parts = explode(',', $order);
            if (count($parts) >= 2) {
                $column = $parts[0];
                $direction = strtoupper($parts[1]) === 'DESC' ? 'DESC' : 'ASC';
                $sql .= " ORDER BY {$column} {$direction}";
            }
        }

        // Handle pagination
        if (isset($params['page'])) {
            $page = (int)$params['page'];
            $size = (int)($params['size'] ?? 20);
            $offset = ($page - 1) * $size;
            $sql .= " LIMIT {$size} OFFSET {$offset}";
        } elseif (isset($params['limit'])) {
            $limit = (int)$params['limit'];
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);
        $records = $stmt->fetchAll();

        return [
            'records' => $records,
            'total' => $this->getTotalCount($table, $conditions, $values)
        ];
    }

    public function getRecord(string $table, string $id): ?array
    {
        $sql = "SELECT * FROM {$table} WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);
        $record = $stmt->fetch();

        return $record ?: null;
    }

    public function createRecord(string $table, array $data): array
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array_values($data));
        
        $id = $this->connection->lastInsertId();
        
        return [
            'id' => $id,
            'message' => 'Record created successfully'
        ];
    }

    public function updateRecord(string $table, string $id, array $data): array
    {
        $columns = array_keys($data);
        $setClause = implode(' = ?, ', $columns) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE id = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);
        
        return [
            'id' => $id,
            'message' => 'Record updated successfully',
            'affected_rows' => $stmt->rowCount()
        ];
    }

    public function deleteRecord(string $table, string $id): array
    {
        $sql = "DELETE FROM {$table} WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);
        
        return [
            'id' => $id,
            'message' => 'Record deleted successfully',
            'affected_rows' => $stmt->rowCount()
        ];
    }

    private function getSearchableColumns(string $table): array
    {
        $sql = "SHOW COLUMNS FROM {$table}";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        $searchable = [];
        foreach ($columns as $column) {
            $type = strtolower($column['Type']);
            if (str_contains($type, 'varchar') || str_contains($type, 'text') || str_contains($type, 'char')) {
                $searchable[] = $column['Field'];
            }
        }
        
        return $searchable;
    }

    private function getTotalCount(string $table, array $conditions = [], array $values = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch();
        
        return (int)($result['total'] ?? 0);
    }

    public function getTables(): array
    {
        $sql = "SHOW TABLES";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $tables;
    }

    public function getTableSchema(string $table): array
    {
        $sql = "DESCRIBE {$table}";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        return $columns;
    }
} 