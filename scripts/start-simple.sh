#!/bin/bash

# LytePHP Simple Startup Script
# This script starts LytePHP without Docker

echo "🚀 Starting LytePHP (Simple Mode)..."

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
REQUIRED_VERSION="8.1.0"

if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then
    echo "❌ PHP version $PHP_VERSION is too old. Please install PHP 8.1 or higher."
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install
fi

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "⚙️  Creating .env file from template..."
    cp env.example .env
    echo "📝 Please edit .env file with your database configuration"
fi

# Create logs directory
mkdir -p logs

# Start the development server
echo "🌐 Starting development server on http://localhost:8000"
echo "📚 API Documentation: http://localhost:8000/docs"
echo "💚 Health Check: http://localhost:8000/health"
echo ""
echo "Press Ctrl+C to stop the server"

php -S localhost:8000 -t public 