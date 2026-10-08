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

## Лабораторна робота №3 — MySQL та інформаційна система ресторанів (варіант 11)

Варіант 11: **Інформаційна система ресторанів** — база даних для управління меню, замовленнями, клієнтами та бронюванням столів. Схема продубльована окремо на Symfony (Doctrine ORM) та на Laravel (Eloquent) — по 6 таблиць на кожен фреймворк (мінімум 5 за завданням):

- `customers` — клієнти (name, phone, email)
- `restaurant_tables` — столики залу (table_number, seats)
- `menu_items` — позиції меню (name, description, price, category)
- `reservations` — бронювання столика клієнтом (customer_id → customers, restaurant_table_id → restaurant_tables, reserved_for, guests_count, status)
- `orders` — замовлення (customer_id → customers, restaurant_table_id → restaurant_tables, status, ordered_at)
- `order_items` — позиції замовлення, зв'язок many-to-many між `orders` і `menu_items` (order_id, menu_item_id, quantity, unit_price)

### 1–2) Встановлення MySQL і створення користувача

На macOS (Homebrew):
```
brew install mysql
brew services start mysql
mysql -uroot
```

У консолі `mysql`:
```sql
CREATE DATABASE symfony_restaurant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE laravel_restaurant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'restaurant_app'@'localhost' IDENTIFIED WITH mysql_native_password BY 'RestaurantApp123!';
GRANT ALL PRIVILEGES ON symfony_restaurant.* TO 'restaurant_app'@'localhost';
GRANT ALL PRIVILEGES ON laravel_restaurant.* TO 'restaurant_app'@'localhost';
FLUSH PRIVILEGES;
```

(Схему й CRUD нижче перевірено саме з цим користувачем і цими двома базами — одна на кожен фреймворк.)

### 3) Підключення Symfony/Laravel до бази

- `Symfony/.env` — додано `DATABASE_URL` (доктрина, mysql, база `symfony_restaurant`); пакети `doctrine/orm`, `doctrine/doctrine-bundle`, `doctrine/doctrine-migrations-bundle` додані в `composer.json`. Після `composer install`:
  ```
  cd Symfony
  php bin/console doctrine:migrations:migrate
  ```
- `Laravel/.env.example` — оновлено `DB_CONNECTION=mysql` і `DB_DATABASE=laravel_restaurant` (скопіюй у свій `.env`: `cp .env.example .env && php artisan key:generate`). Після цього:
  ```
  cd Laravel
  php artisan migrate
  ```

**Важливо (виправлення багу з Lab 2):** під час тестування Lab 3 виявилось, що `POST/PUT/PATCH/DELETE` на `/products` у Laravel повертали `419 Page Expired`, бо `routes/web.php` проходить через middleware-групу `web` з CSRF-перевіркою, а наш JSON API не передає CSRF-токен. Виправлено в `bootstrap/app.php` — усі JSON-роути (`products`, `customers`, `tables`, `menu-items`, `reservations`, `orders`, `order-items`) явно виключені з CSRF-перевірки через `$middleware->validateCsrfTokens(except: [...])`. Це стосується і Lab 2 (тепер `POST /products` у Laravel теж працює).

### 4) Сутності/моделі та зв'язки

- `Symfony/src/Entity/*.php` + `Symfony/src/Repository/*.php` — 6 Doctrine-сутностей з атрибутами (`#[ORM\Entity]`, `#[ORM\ManyToOne]`, `#[ORM\OneToMany]`), міграція в `Symfony/migrations/`.
- `Laravel/app/Models/*.php` — 6 Eloquent-моделей (`belongsTo`/`hasMany`), міграції в `Laravel/database/migrations/2026_10_05_*`.

### 5) CRUD-контролери (окремий контролер на кожну таблицю)

Обидва фреймворки: `GET /<ресурс>` (список), `GET /<ресурс>/{id}`, `POST /<ресурс>`, `PUT`/`PATCH /<ресурс>/{id}`, `DELETE /<ресурс>/{id}`:

- `/customers` — `CustomerController`
- `/tables` — `RestaurantTableController`
- `/menu-items` — `MenuItemController`
- `/reservations` — `ReservationController` (приймає `customerId`, `restaurantTableId`)
- `/orders` — `OrderController` — приймає вкладений масив `items: [{menuItemId, quantity}, ...]` і одразу створює пов'язані `order_items` в одній транзакції
- `/order-items` — `OrderItemController` — самостійний CRUD для позицій замовлення (окремо від вкладеного створення через `/orders`)

### Перевірка вручну (приклад для Symfony; для Laravel — ті самі шляхи на порту `php artisan serve`)

```
curl -X POST http://127.0.0.1:8000/customers -H "Content-Type: application/json" -d '{"name":"Ivan Kuzmin","phone":"+380671234567","email":"ivan@example.com"}'
curl -X POST http://127.0.0.1:8000/tables -H "Content-Type: application/json" -d '{"tableNumber":7,"seats":2}'
curl -X POST http://127.0.0.1:8000/menu-items -H "Content-Type: application/json" -d '{"name":"Піца Маргарита","price":180,"category":"Основні страви"}'
curl -X POST http://127.0.0.1:8000/reservations -H "Content-Type: application/json" -d '{"customerId":1,"restaurantTableId":1,"reservedFor":"2026-10-11T20:00:00+03:00","guestsCount":2}'
curl -X POST http://127.0.0.1:8000/orders -H "Content-Type: application/json" -d '{"customerId":1,"restaurantTableId":1,"items":[{"menuItemId":1,"quantity":1}]}'
curl http://127.0.0.1:8000/orders
```

Увесь функціонал (обидва фреймворки, усі 6 таблиць, включно зі зв'язками) перевірено end-to-end проти реального MySQL 8.0 перед комітом.

### Git

Результат лабораторної №3 — в окремій гілці `lab-3-restaurant` (не в `master`), як вимагає завдання.

## Лабораторна робота №4 — Фільтрація та пагінація

Завдання: для всіх сутностей (6 таблиць з Lab 3 — customers, tables, menu-items, reservations, orders, order-items — на кожному з двох фреймворків) додати фільтрацію по кожному полю таблиці та пагінацію з можливістю змінювати кількість елементів на сторінці (`itemsPerPage`). Результат — в окремій гілці `lab-4-filters-pagination` (відгалужена від `lab-3-restaurant`).

### Підхід до фільтрації

Для кожного поля обрано один із трьох типів фільтра залежно від його природи:

- **exact** — точний збіг: `id`, зовнішні ключі (`customerId`, `restaurantTableId`, `orderId`, `menuItemId`), коди-перелічення (`status`, `tableNumber`)
- **like** — частковий регістронезалежний пошук підрядка: `name`, `description`, `phone`, `email`, `category`
- **range** — діапазон через `?поле_min=&поле_max=`: `price`, `seats`, `guestsCount`, `quantity`, `unitPrice`, `createdAt`, `reservedFor`, `orderedAt`

У Symfony логіка винесена у спільний трейт `App\Repository\Filter\FilterablePaginationTrait`, підключений у всі 6 репозиторіїв (метод `search()` будує Doctrine `QueryBuilder` за декларативною картою полів). У Laravel — аналогічний трейт `App\Http\Controllers\Concerns\FiltersAndPaginates`, підключений у всі 6 контролерів (будує Eloquent `Builder`). Назви query-параметрів в обох фреймворках однакові (camelCase) і збігаються з іменами полів у JSON-відповідях/тілах запитів (наприклад, `customerId`, а не `customer_id`, навіть у Laravel, де в БД колонка `customer_id`).

### Пагінація

Query-параметри `page` (за замовчуванням 1) та `itemsPerPage` (за замовчуванням 10, максимум 100 — захист від надто великих вибірок за один запит). Відповідь списку тепер має вигляд:
```json
{
  "data": [ ... ],
  "meta": { "page": 1, "itemsPerPage": 10, "totalItems": 23, "totalPages": 3 }
}
```

### Приклади

```
curl "http://127.0.0.1:8000/customers?name=iva&itemsPerPage=5&page=1"
curl "http://127.0.0.1:8000/menu-items?price_min=100&price_max=200&category=dessert"
curl "http://127.0.0.1:8000/reservations?customerId=1&status=confirmed"
curl "http://127.0.0.1:8000/orders?restaurantTableId=2&orderedAt_min=2026-10-01T00:00:00%2B00:00"
curl "http://127.0.0.1:8000/order-items?orderId=1&quantity_min=2"
```

Усі поля всіх 6 таблиць (Customer, RestaurantTable, MenuItem, Reservation, Order, OrderItem) в обох фреймворках перевірено end-to-end проти реального MySQL 8.0 перед комітом: пагінація (включно з обрізанням `itemsPerPage` до 100 і `page` до мінімум 1), partial/exact/range фільтри, фільтри по зовнішніх ключах через асоціації, та регресійна перевірка, що старі CRUD-ендпойнти (включно з Lab 2 `/products`) й далі працюють.

Ендпойнт `/products` (Lab 2, in-memory масив, не сутність БД) залишено без змін — фільтрація й пагінація додані лише для 6 персистентних сутностей з Lab 3.

### Git

Результат лабораторної №4 — в окремій гілці `lab-4-filters-pagination`, відгалуженій від `lab-3-restaurant` (бо залежить від схеми й контролерів Lab 3, а не від `master`).
