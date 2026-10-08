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

## Лабораторна робота №5 — Автентифікація та авторизація (JWT, 3 рівні доступу)

Завдання: додати JWT-автентифікацію та авторизацію з трьома рівнями доступу (Client, Manager, Admin) з контролем доступу до всіх CRUD-операцій з Lab 3/4, в обох фреймворках. Результат — в окремій гілці `lab-5-jwt-auth`, відгалуженій від `lab-4-filters-pagination`.

### Підхід

Додано окрему сутність/модель `User` (таблиця `users`) — облікові записи для автентифікації, відокремлені від `Customer` (клієнти ресторану як бізнес-сутність з Lab 3, без логіна/пароля). У `User` є поле `role` з трьома значеннями: `client`, `manager`, `admin`.

- **Реєстрація** (`POST /auth/register`) — публічний ендпойнт, завжди створює обліковий запис з роллю `client`, навіть якщо в тілі запиту передано інше значення `role` (запобігання підвищенню привілеїв при самостійній реєстрації).
- **Логін** (`POST /auth/login`) — приймає `email`/`password`, повертає JWT з claim'ом `role`.
- **Підвищення ролі** — лише Admin може змінити роль іншого користувача, через `PATCH /users/{id}/role` (`{"role": "manager"}`).

Рівні доступу ієрархічні (Admin ⊇ Manager ⊇ Client): дозвіл, виданий ролі нижчого рівня, автоматично доступний і вищим. У Symfony це стандартний механізм `role_hierarchy` у `security.yaml` (`ROLE_ADMIN` → `ROLE_MANAGER` → `ROLE_CLIENT`). У Laravel немає вбудованого аналога, тому реалізовано вручну: константа-мапа рангів (`client=1, manager=2, admin=3`) в моделі `User` і метод `hasAtLeastRole()`, який порівнює ранги.

### Матриця прав доступу

| Дія | Client | Manager | Admin |
|---|---|---|---|
| Перегляд меню/столиків/товарів (`GET /menu-items`, `/tables`, `/products`) | ✅ | ✅ | ✅ |
| Створення бронювання/замовлення (`POST /reservations`, `/orders`) | ✅ | ✅ | ✅ |
| CRUD меню/столиків/товарів (`POST/PUT/PATCH/DELETE /menu-items`, `/tables`, `/products`) | ❌ | ✅ | ✅ |
| CRUD клієнтів, перегляд/зміна бронювань і замовлень, order-items | ❌ | ✅ | ✅ |
| Видалення клієнта (`DELETE /customers/{id}`) | ❌ | ❌ | ✅ |
| Керування обліковими записами й ролями (`/users/*`) | ❌ | ❌ | ✅ |

(Повний перелік ендпойнтів — у `Symfony/config/packages/security.yaml` та атрибутах `#[IsGranted]` на контролерах, і в `Laravel/routes/web.php` (групи `role:manager` / `role:admin`).)

### Symfony — налаштування

1. Пакети: `composer require symfony/security-bundle lexik/jwt-authentication-bundle`
2. Реєстрація бандлів у `config/bundles.php`, конфіг `config/packages/security.yaml` і `config/packages/lexik_jwt_authentication.yaml`.
3. Генерація RSA-ключової пари (RS256):
   ```
   cd Symfony
   mkdir -p config/jwt
   openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
   openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
   ```
4. У `.env` — `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY`, `JWT_PASSPHRASE` (пароль, вказаний при генерації приватного ключа), `JWT_TOKEN_TTL`.
5. Міграція таблиці `users`: `php bin/console doctrine:migrations:migrate`.

`config/jwt/*.pem` додано в `.gitignore` — ключі не комітяться, кожен розробник генерує свої локально.

### Laravel — налаштування

1. Пакет: `composer require tymon/jwt-auth`
2. `php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"`
3. `php artisan jwt:secret` — генерує `JWT_SECRET` у `.env` (HS256).
4. Міграції: колонка `role` в `users` і — обов'язково — таблиця `cache` (`php artisan make:cache-table && php artisan migrate`), бо `tymon/jwt-auth` веде чорний список токенів через кеш, а `CACHE_STORE=database` в `.env` без цієї таблиці падає з 500-ю помилкою при будь-якому запиті з токеном.
5. `config/auth.php` — додано guard `api` (`driver: jwt`).
6. `bootstrap/app.php` — мідлвар-аліас `role` → `EnsureRole`, і **важливо**: `$middleware->redirectGuestsTo(fn () => null)` разом з `$exceptions->render(AuthenticationException::class, ...)`, щоб неавтентифікований запит без токена повертав чистий JSON 401, а не 500 (за замовчуванням Laravel намагається редіректити на іменований маршрут `login`, якого в цьому JSON-API немає, і падає з `RouteNotFoundException` ще до перевірки `expectsJson()`).

### Приклад сценарію (однаковий для обох фреймворків, порт — відповідно до `php -S`/`php artisan serve`)

```
# 1. Реєстрація (завжди роль client)
curl -X POST http://127.0.0.1:8000/auth/register -H "Content-Type: application/json" \
  -d '{"email":"client@example.com","password":"Secret123!"}'

# 2. Логін -> JWT
curl -X POST http://127.0.0.1:8000/auth/login -H "Content-Type: application/json" \
  -d '{"email":"client@example.com","password":"Secret123!"}'
# => {"token":"eyJ..."}

# 3. Запит з токеном
curl http://127.0.0.1:8000/menu-items -H "Authorization: Bearer eyJ..."

# 4. Без токена -> 401
curl http://127.0.0.1:8000/menu-items

# 5. Підвищення ролі (виконує Admin)
curl -X PATCH http://127.0.0.1:8000/users/1/role -H "Authorization: Bearer <ADMIN_JWT>" \
  -H "Content-Type: application/json" -d '{"role":"manager"}'
```

Перший Admin у системі створюється вручну (прямим записом у БД), оскільки жоден ендпойнт API не дозволяє призначити собі роль Admin самостійно — це свідоме обмеження, що запобігає ескалації привілеїв.

Увесь функціонал (обидва фреймворки, усі три ролі, увесь перелік ендпойнтів з Lab 3/4, підвищення ролі, заборона підвищення привілеїв при реєстрації, коди 401/403/404/409) перевірено end-to-end проти реального MySQL 8.0 перед комітом.

### Git

Результат лабораторної №5 — в окремій гілці `lab-5-jwt-auth`, відгалуженій від `lab-4-filters-pagination` (бо продовжує код і схему з Lab 3/4, а не `master`).
