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

### API documentation will be available at:
````
http://localhost:8080/api/doc
````

### API will be available at:
````
http://localhost:8080
````


### Run tests:
```
bin/phpunit
```

## Endpoints

### Create product

POST /api/products

Request body:
```json
{
    "name": "Name of the product",
    "sku": "sku",
    "price": "199.99",
    "currency": "PLN",
    "status": "active"
}
```

### List products

GET /api/products

Query parameters:
- status – optional (active, inactive, draft)
- page – optional, default 1
- limit – optional, default 10

Example:
```GET /api/products?status=active&page=1&limit=10```



### Get product

GET /api/products/{id}

Response:
```json
{
"id": 1,
"name": "Product name",
"sku": "SKU123",
"price": "99.99",
"currency": "PLN",
"status": "active",
"createdAt": "2022-06-01T12:00:00Z",
"updatedAt": "2024-06-01T12:00:00Z",
"version": 1,
"priceHistory": [
    {
        "id": 1,
        "price": "89.99",
        "currency": "PLN",
        "changedAt": "2024-05-01T12:00:00Z"
    },
    {
        "id": 2,
        "price": "99.99",
        "currency": "PLN",
        "changedAt": "2024-06-01T12:00:00Z"
    }
]
}
```

### Update product

PUT /api/products/{id}

Request body:
```json
{
    "name": "Updated product name",
    "sku": "UPDATEDSKU",
    "price": "249.99",
    "currency": "PLN",
    "status": "active",
    "version": 2
}
```
`version` is used for optimistic locking to prevent overwriting changes made by another request.

### Delete product

DELETE /api/products/{id}

Soft delete is implemented, so the product will be marked as deleted but not removed from the database.
