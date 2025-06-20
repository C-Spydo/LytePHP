#!/bin/bash

# LytePHP Docker Startup Script
# This script starts LytePHP with Docker

echo "🐳 Starting LytePHP (Docker Mode)..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose."
    exit 1
fi

# Check if Docker is running
if ! docker info &> /dev/null; then
    echo "❌ Docker is not running. Please start Docker."
    exit 1
fi

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "⚙️  Creating .env file from template..."
    cp env.example .env
    echo "📝 Please edit .env file with your database configuration"
fi

# Create logs directory
mkdir -p logs

# Build and start containers
echo "🔨 Building and starting containers..."
docker-compose up --build -d

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 10

# Check if services are running
if docker-compose ps | grep -q "Up"; then
    echo ""
    echo "✅ LytePHP is running!"
    echo ""
    echo "🌐 Application: http://localhost:8000"
    echo "📚 API Documentation: http://localhost:8000/docs"
    echo "💚 Health Check: http://localhost:8000/health"
    echo "🗄️  phpMyAdmin: http://localhost:8080"
    echo "🔴 Redis Commander: http://localhost:8081"
    echo ""
    echo "📋 Available commands:"
    echo "  docker-compose logs -f app    # View application logs"
    echo "  docker-compose down           # Stop all services"
    echo "  docker-compose restart        # Restart all services"
    echo ""
else
    echo "❌ Failed to start services. Check logs with: docker-compose logs"
    exit 1
fi 