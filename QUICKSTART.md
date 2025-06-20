# 🚀 LytePHP Quick Start Guide

Get your API running in under 5 minutes!

## Option 1: One-Command Start (Recommended)

```bash
# Clone and start in one go
git clone https://github.com/your-username/lytephp.git my-api
cd my-api
php lytephp start
```

That's it! Your API is now running at http://localhost:8000

## Option 2: Step by Step

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/lytephp.git
cd lytephp
```

### 2. Choose Your Startup Method

#### Simple Mode (No Docker)
```bash
./scripts/start-simple.sh
```

#### Docker Mode (Full Stack)
```bash
./scripts/start-docker.sh
```

#### Manual Mode
```bash
composer install
cp env.example .env
# Edit .env with your database settings
php -S localhost:8000 -t public
```

### 3. Configure Your Database

Edit the `.env` file:
```env
DB_HOST=localhost
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Test Your API

Visit these URLs:
- **API Documentation**: http://localhost:8000/docs
- **Health Check**: http://localhost:8000/health
- **API Base**: http://localhost:8000/api

## 🎯 What You Get

✅ **Automatic CRUD API** for all your database tables  
✅ **Interactive Documentation** with Swagger UI  
✅ **Search & Filter** capabilities  
✅ **Pagination** support  
✅ **CORS** enabled  
✅ **Health monitoring**  

## 📝 Example Usage

### Create a User
```bash
curl -X POST http://localhost:8000/api/records/users \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com"}'
```

### Get All Users
```bash
curl http://localhost:8000/api/records/users
```

### Search Users
```bash
curl "http://localhost:8000/api/records/users?search=john"
```

### Paginated Results
```bash
curl "http://localhost:8000/api/records/users?page=1&size=10"
```

## 🐳 Docker Services (if using Docker mode)

- **Application**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
- **Redis Commander**: http://localhost:8081

## 🛠️ Next Steps

1. **Explore the Documentation**: Visit http://localhost:8000/docs
2. **Add Your Tables**: Create database tables and they'll automatically become API endpoints
3. **Customize**: Add custom routes in `src/Application.php`
4. **Deploy**: Use the production Docker image or traditional hosting

## 🆘 Need Help?

- 📖 **Full Documentation**: [README.md](README.md)
- 🐛 **Issues**: [GitHub Issues](https://github.com/your-username/lytephp/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/your-username/lytephp/discussions)

---

**Happy coding! 🎉** 