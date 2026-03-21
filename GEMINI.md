# Project Overview

This is a Laravel 10 web application for creating and managing digital business cards. It features a public-facing website, a comprehensive admin panel with role-based access control, and integration with the LINE messaging app and OpenAI for AI-powered content generation.

## Key Technologies

*   **Backend:** PHP 8.1, Laravel 10
*   **Frontend:** Vite, JavaScript, Bootstrap, jQuery, Sass
*   **Database:** (Not explicitly specified, but likely MySQL, MariaDB, or PostgreSQL, as supported by Laravel)
*   **Integrations:**
    *   LINE Bot SDK (`linecorp/line-bot-sdk`)
    *   OpenAI API (`openai-php/client`)
    *   Excel for report generation (`maatwebsite/excel`)
    *   DataTables for admin panel tables (`yajra/laravel-datatables-oracle`)
    *   Admin panel scaffolding (`infyomlabs/laravel-generator`)

## Architecture

The application follows a standard Laravel project structure.

*   **`app/`**: Contains the core application logic, including models, controllers, services, and repositories.
*   **`routes/`**: Defines the application's web and API routes.
    *   `web.php`: Contains routes for the public website and the admin panel. It includes role-based middleware for access control.
    *   `api.php`: Contains API routes.
*   **`resources/`**: Contains frontend assets (CSS, JS, views).
*   **`public/`**: The web server's document root.
*   **`config/`**: Application configuration files.
*   **`database/`**: Database migrations and seeders.

## Building and Running

### Backend

1.  **Install dependencies:**
    ```bash
    composer install
    ```
2.  **Set up environment:**
    *   Copy `.env.example` to `.env`.
    *   Configure database and other services in `.env`.
3.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```
4.  **Start the development server:**
    ```bash
    php artisan serve
    ```

### Frontend

1.  **Install dependencies:**
    ```bash
    npm install
    ```
2.  **Run the development server (with hot-reloading):**
    ```bash
    npm run dev
    ```
3.  **Build for production:**
    ```bash
    npm run build
    ```

## Development Conventions

*   The project uses the `infyomlabs/laravel-generator` for scaffolding admin resources, which suggests a standardized structure for controllers, views, and routes in the admin panel.
*   The use of `yajra/laravel-datatables-oracle` indicates that admin tables are expected to be implemented as server-side DataTables.
*   The presence of `pint` in `composer.json` suggests that the project uses Laravel Pint for code style.
