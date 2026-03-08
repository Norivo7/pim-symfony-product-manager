# pim-symfony-product-manager


Simple REST API for managing products.

The project was created as a recruitment task.  
It exposes endpoints for creating, updating, retrieving and deleting products.  
Each price change is tracked and stored in price history.

---

# Features

- Create product
- Update product details
- Change product price
- Automatic price history tracking
- Optimistic locking
- Soft delete
- Pagination and filtering
- OpenAPI documentation
- Automated tests

---

# Tech stack

- PHP 8.4
- Symfony
- PostgreSQL
- Doctrine ORM
- NelmioApiDocBundle (Swagger / OpenAPI)
- PHPUnit
- Docker

---

# Running the project

### Start containers:

```bash
docker compose up -d
```
### Go to php container:

```bashbash
docker compose exec php bash
```

### Install dependencies:

```bash
composer install
```

### Run migrations:
```
bin/console doctrine:migrations:migrate
```

API will be available at:
````
http://localhost:8080
````

API documentation will be available at:
````
http://localhost:8080/api/doc
````

### Run tests:
```
bin/phpunit
```

