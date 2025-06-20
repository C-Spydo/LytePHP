<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use LytePHP\Application;
use LytePHP\Config\Environment;

// Load environment variables
$env = new Environment(__DIR__ . '/../');

// Create and run the application
$app = new Application($env);
$app->run(); 