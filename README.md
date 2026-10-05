# Лабораторні роботи — Symfony & Laravel

Один репозиторій для всіх лабораторних робіт курсу.

## Структура

- `Symfony/` — Symfony 6.4 (skeleton + maker-bundle)
  - Контролер: `src/Controller/TestController.php` → маршрут `/test`
  - Запуск: `symfony server:start` (або `php -S 127.0.0.1:8000 -t public`)
- `Laravel/` — Laravel 11
  - Контролер: `app/Http/Controllers/TestController.php` → маршрут `/test` (див. `routes/web.php`)
  - Запуск: `composer run dev` (або `php artisan serve`)

## Середовище

- PHP 8.3 (для Symfony 6.4 і Laravel 11 потрібен PHP ≥ 8.1/8.2 відповідно — версія 7.1 з методички застаріла і несумісна з цими фреймворками)
- Composer 2.x
- Xdebug (mode=debug, port 9003)

Перед комітом в обох середовищах виконано:
```
git config core.fileMode false
```

## Лабораторна робота №2 — CRUD операції

Реалізовано `ProductController` (CRUD над сутністю `Product`: id, name, description, price) в обох фреймворках. Дані зберігаються в константі `PRODUCTS` в пам'яті контролера (без реальної БД — місця для підключення БД позначені коментарями `// TODO insert/update/delete to db`), за прикладом з методички.

- `Symfony/` — `src/Controller/ProductController.php`, маршрути оголошені атрибутами `#[Route]`:
  - `GET /products` — список товарів
  - `GET /products/{id}` — товар за id (404 `Response::HTTP_NOT_FOUND`, якщо не знайдено)
  - `POST /products` — створення товару (тіло запиту — JSON, повертає 201)
  - `PUT /products/{id}` / `PATCH /products/{id}` — оновлення товару
  - `DELETE /products/{id}` — видалення товару
- `Laravel/` — `app/Http/Controllers/ProductController.php`, маршрути в `routes/web.php`:
  - `GET /products`, `GET /products/{id}`, `POST /products`, `PUT /products/{id}`, `PATCH /products/{id}`, `DELETE /products/{id}` — аналогічно Symfony

### Перевірка вручну

```
# Symfony (symfony server:start або php -S 127.0.0.1:8000 -t public)
curl http://127.0.0.1:8000/products
curl http://127.0.0.1:8000/products/1
curl -X POST http://127.0.0.1:8000/products -H "Content-Type: application/json" -d '{"name":"Webcam","description":"HD webcam","price":29.99}'
curl -X PUT http://127.0.0.1:8000/products/1 -H "Content-Type: application/json" -d '{"price":39.99}'
curl -X DELETE http://127.0.0.1:8000/products/1

# Laravel (composer run dev або php artisan serve)
curl http://127.0.0.1:8000/products
```

