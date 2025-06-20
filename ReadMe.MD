# LytePHP - Lightweight Modern PHP API Framework

> **⚠️ WORK IN PROGRESS** - This project is currently in active development and not yet production-ready.

LytePHP is a lightweight PHP API framework that automatically generates REST APIs and Swagger documentation from a database schema. It provides a modern, developer-friendly experience for rapid API development in PHP.

## 🚧 Current Status

### What's Working
- ✅ Basic project structure and CLI tool
- ✅ Multiple startup options (Simple PHP server, Docker)
- ✅ Interactive CLI with Docker detection
- ✅ Environment configuration system
- ✅ Basic routing and application framework
- ✅ Swagger UI integration
- ✅ OpenAPI specification generation

### Known Issues
- 🔴 **Database Connection**: MySQL connection issues in local development
- 🔴 **Environment Parsing**: Some .env file parsing edge cases
- 🔴 **Missing Core Features**: CRUD operations not yet implemented
- 🔴 **Documentation**: API documentation generation needs refinement
- 🔴 **Testing**: No test suite implemented yet

### Immediate Blockers
1. **Database Connectivity**: Need to resolve MySQL connection issues for local development
2. **Environment Setup**: Fix .env file parsing and configuration loading
3. **Core CRUD**: Implement basic database operations

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- MySQL (for database operations)
- Docker (optional, for containerized development)

### Installation

```bash
# Clone the repository
git clone https://github.com/C-Spydo/LytePHP.git
cd LytePHP

# Install dependencies
composer install

# Copy environment file
cp env.example .env

# Edit .env file with your database settings
# For local MySQL: DB_HOST=127.0.0.1
# For Docker: DB_HOST=mysql
```

### Starting the Application

```bash
# Interactive startup (recommended)
php lytephp start

# Force simple mode
php lytephp start --simple

# Force Docker mode
php lytephp start --docker
```

## 📋 Feature Roadmap

### Phase 1: Core Foundation (In Progress)
- [ ] Fix database connection issues
- [ ] Implement basic CRUD operations
- [ ] Add proper error handling
- [ ] Complete environment configuration
- [ ] Add basic authentication

### Phase 2: API Features
- [ ] Automatic API generation from database schema
- [ ] Request/response validation
- [ ] Middleware system
- [ ] Rate limiting
- [ ] CORS handling

### Phase 3: Developer Experience
- [ ] Type-safe request/response models
- [ ] Async support (PHP 8.1+)
- [ ] Dependency injection container
- [ ] Hot reloading for development
- [ ] CLI commands for scaffolding

### Phase 4: Advanced Features
- [ ] WebSocket support
- [ ] GraphQL integration
- [ ] Advanced caching (Redis)
- [ ] Background job processing
- [ ] API versioning

### Phase 5: Production Ready
- [ ] Comprehensive test suite
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Deployment guides
- [ ] Monitoring and logging

## 🛠️ Development

### Project Structure
```
LytePHP/
├── public/              # Web server entry point
├── src/                 # Application source code
│   ├── Config/         # Configuration classes
│   └── Core/           # Core framework classes
├── scripts/            # Startup scripts
├── tests/              # Test files (planned)
├── docker-compose.yml  # Docker services
├── Dockerfile          # Docker configuration
└── lytephp            # CLI tool
```

### Contributing

This project is in early development. Contributions are welcome! Please:

1. Check the current issues and roadmap
2. Fork the repository
3. Create a feature branch
4. Make your changes
5. Add tests (when test framework is ready)
6. Submit a pull request

## 📝 TODO List

### High Priority
- [ ] **Fix Database Connection**: Resolve MySQL connectivity issues
- [ ] **Environment Setup**: Complete .env parsing and validation
- [ ] **Basic CRUD**: Implement create, read, update, delete operations
- [ ] **Error Handling**: Add proper exception handling and logging
- [ ] **API Routes**: Implement basic REST endpoints

### Medium Priority
- [ ] **Validation**: Add request/response validation
- [ ] **Authentication**: Basic auth system
- [ ] **Documentation**: Improve API documentation generation
- [ ] **Testing**: Set up PHPUnit and add tests
- [ ] **CLI Commands**: Add more CLI utilities

### Low Priority
- [ ] **Performance**: Optimize database queries
- [ ] **Caching**: Add Redis integration
- [ ] **Monitoring**: Add health checks and metrics
- [ ] **Deployment**: Docker production setup
- [ ] **Examples**: Add example applications

## 🤝 Support

- **Author**: C-Spydo (csamsonok@gmail.com)
- **GitHub**: https://github.com/C-Spydo
- **Issues**: Please report bugs and feature requests on GitHub

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

**Note**: This is an experimental project aiming to bring FastAPI-like developer experience to PHP. The framework is not yet stable and should not be used in production environments.
