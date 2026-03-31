# PhpFree — Фреймворк на чистом PHP 8.4

Минималистичный, быстрый и современный PHP-фреймворк, созданный с нуля без использования сторонних зависимостей (composer-free).

## 🚀 Философия проекта

- **No Vendor** — Никаких сторонних библиотек. Весь код написан вручную.
- **PHP 8.4 Ready** — Использование последних возможностей языка (instantiation without parentheses и др.).
- **Архитектурный порядок** — Проект следует строгим принципам SOLID и чистой архитектуры.

## ⚡️ Быстрый старт

### 1. Настройка окружения:
Скопируйте файл .env.example в .env и укажите параметры вашей базы данных:

    DB_DRIVER=mysql
    DB_NAME=phpfree_db

### 2. Подготовка фронтенда (Laravel Mix):
Для сборки стилей и скриптов через webpack.mix.js используйте стандартные команды npm:

    npm install
    npm run dev (разработка)
    npm run prod (сжатие и оптимизация)

### 3. Подготовка базы:
Выполните миграции через консольное ядро:

    php console migrate

### 4. Подготовка IDE (PHPStorm):
Сгенерируйте мета-данные для автодополнения ключей config() и функций vh_*:

    php console ide:helper

## 💻 Консольные команды
- `php console migrate` — выполнение новых миграций.
- `php console migrate:rollback` — откат последней партии (батча).
- `php console migrate:refresh` — полный перезапуск БД (откат всех и повторный накат).
- `php console make:migration name` — генерация шаблона новой миграции с таймстемпом.
- `php console ide:helper` — генерация .phpstorm.meta.php для подсказок в IDE.
- `php console ide:helper --watch` — фоновое отслеживание изменений в /configs и /app.

## 📂 Структура проекта
- `/app/Actions` — Одиночные классы логики (Invokable).
- `/app/Commands` — Классы консольных команд (Kernel, BaseCommand).
- `/app/Contracts` — Интерфейсы системы для реализации DI.
- `/app/Core` — Ядро фреймворка (Resolver, Router, App, View, Request, Env, Path, Autoloader).
- `/app/Databases` — Драйверы баз данных (MySQL, PostgreSQL).
- `/app/Middleware` — Посредники запросов (Auth, CSRF и др.).
- `/app/Migrations` — История изменений структуры базы данных.
- `/app/Models` — Модели данных с автоматическим маппингом типов.
- `/app/Repositories` — Слой доступа к данным и бизнес-логика БД.
- `/app/Requests` — Валидация и фильтрация входящих данных.
- `/app/ViewHelpers` — Классы динамических функций для шаблонов.
- `/configs` — Конфигурация проекта, маршруты и хелперы.
- `/assets/templates` — HTML шаблоны и макеты оформления.
- `/public` — Публичная директория, точка входа и статика.
- `webpack.mix.js` — Конфигурация сборки ассетов Laravel Mix.
