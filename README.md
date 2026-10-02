# Лабораторна робота №1 — Symfony & Laravel

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
