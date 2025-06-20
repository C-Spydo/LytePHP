<?php

declare(strict_types=1);

namespace LytePHP\Core;

use LytePHP\Config\Environment;

class Documentation
{
    private Environment $env;
    private array $config;

    public function __construct(Environment $env)
    {
        $this->env = $env;
        $this->config = $env->getDocsConfig();
    }

    public function renderSwaggerUI(): void
    {
        $title = $this->config['title'];
        $swaggerUrl = '/docs/swagger.json';
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin:0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '{$swaggerUrl}',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>
HTML;

        header('Content-Type: text/html');
        echo $html;
    }

    public function generateOpenAPISpec(): array
    {
        $appConfig = $this->env->getAll()['app'];
        $apiConfig = $this->env->getAll()['api'];
        
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $this->config['title'],
                'version' => $appConfig['version'],
                'description' => 'Auto-generated API documentation for LytePHP',
                'contact' => [
                    'name' => 'C-Spydo',
                    'email' => 'csamsonok@gmail.com',
                    'url' => 'https://github.com/C-Spydo'
                ]
            ],
            'servers' => [
                [
                    'url' => 'http://localhost:8000',
                    'description' => 'Development server'
                ]
            ],
            'paths' => $this->generatePaths(),
            'components' => [
                'schemas' => $this->generateSchemas(),
                'responses' => $this->generateResponses()
            ],
            'tags' => [
                [
                    'name' => 'Records',
                    'description' => 'CRUD operations for database records'
                ]
            ]
        ];

        return $spec;
    }

    private function generatePaths(): array
    {
        $paths = [];

        // Root endpoint
        $paths['/'] = [
            'get' => [
                'summary' => 'Get API information',
                'description' => 'Returns basic information about the API',
                'tags' => ['Info'],
                'responses' => [
                    '200' => [
                        'description' => 'API information',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'version' => ['type' => 'string'],
                                        'docs' => ['type' => 'string'],
                                        'api' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Health check
        $paths['/health'] = [
            'get' => [
                'summary' => 'Health check',
                'description' => 'Check API health status',
                'tags' => ['Health'],
                'responses' => [
                    '200' => [
                        'description' => 'Health status',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string'],
                                        'timestamp' => ['type' => 'string'],
                                        'database' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Generic table endpoints
        $paths['/api/records/{table}'] = [
            'get' => [
                'summary' => 'List records',
                'description' => 'Get a list of records from the specified table',
                'tags' => ['Records'],
                'parameters' => [
                    [
                        'name' => 'table',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Table name'
                    ],
                    [
                        'name' => 'page',
                        'in' => 'query',
                        'schema' => ['type' => 'integer'],
                        'description' => 'Page number'
                    ],
                    [
                        'name' => 'size',
                        'in' => 'query',
                        'schema' => ['type' => 'integer'],
                        'description' => 'Page size'
                    ],
                    [
                        'name' => 'order',
                        'in' => 'query',
                        'schema' => ['type' => 'string'],
                        'description' => 'Order by (column,direction)'
                    ],
                    [
                        'name' => 'filter',
                        'in' => 'query',
                        'schema' => ['type' => 'string'],
                        'description' => 'Filter (column,operator,value)'
                    ],
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'schema' => ['type' => 'string'],
                        'description' => 'Search term'
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'List of records',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'records' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'object']
                                        ],
                                        'total' => ['type' => 'integer']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'summary' => 'Create record',
                'description' => 'Create a new record in the specified table',
                'tags' => ['Records'],
                'parameters' => [
                    [
                        'name' => 'table',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Table name'
                    ]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'description' => 'Record data'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Record created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'message' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $paths['/api/records/{table}/{id}'] = [
            'get' => [
                'summary' => 'Get record',
                'description' => 'Get a specific record by ID',
                'tags' => ['Records'],
                'parameters' => [
                    [
                        'name' => 'table',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Table name'
                    ],
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Record ID'
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Record data',
                        'content' => [
                            'application/json' => [
                                'schema' => ['type' => 'object']
                            ]
                        ]
                    ],
                    '404' => [
                        'description' => 'Record not found',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'error' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'put' => [
                'summary' => 'Update record',
                'description' => 'Update a specific record by ID',
                'tags' => ['Records'],
                'parameters' => [
                    [
                        'name' => 'table',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Table name'
                    ],
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Record ID'
                    ]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'description' => 'Updated record data'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Record updated',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'string'],
                                        'message' => ['type' => 'string'],
                                        'affected_rows' => ['type' => 'integer']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'delete' => [
                'summary' => 'Delete record',
                'description' => 'Delete a specific record by ID',
                'tags' => ['Records'],
                'parameters' => [
                    [
                        'name' => 'table',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Table name'
                    ],
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                        'description' => 'Record ID'
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Record deleted',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'string'],
                                        'message' => ['type' => 'string'],
                                        'affected_rows' => ['type' => 'integer']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $paths;
    }

    private function generateSchemas(): array
    {
        return [
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'error' => ['type' => 'string'],
                    'code' => ['type' => 'integer']
                ]
            ],
            'Success' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                    'data' => ['type' => 'object']
                ]
            ]
        ];
    }

    private function generateResponses(): array
    {
        return [
            'NotFound' => [
                'description' => 'Resource not found',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Error'
                        ]
                    ]
                ]
            ],
            'ServerError' => [
                'description' => 'Internal server error',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Error'
                        ]
                    ]
                ]
            ]
        ];
    }
} 