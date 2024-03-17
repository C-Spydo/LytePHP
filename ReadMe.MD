# LytePHP

### Lightweight PHP API framework
#### LytePHP is a fast way to spin up a PHP API based on your database schema. It is very easy to get started, requires little configuration, and automatically generates a Swagger documentation for your API.

## Use-Cases
- API prototypes
- Simple Application Backend

## Reference
- Based on the [PHP-CRUD-API](https://github.com/mevdschee/php-crud-api) project
- Other Key depencies include:
  - [Swagger](https://swagger.io/)

## Usages
- Download / clone the project
- Create a .env file in the project root
- Copy the parameters in .env.example into .env
- Configure the parameters as necessary
- Execute `composer install`
- Run your project with php -S localhost:PORT
- Your API can be accessed at APP_URL/api/records/
- Your API documentation can be accessed at APP_URL/docs
