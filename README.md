# Counselwise

A Laravel-based platform designed to provide HPCSA registered counsellors with novel therapeutic recommendations tailored to their clients' distinct needs.

## About

Counselwise connects mental health professionals with evidence-based therapeutic approaches, helping counsellors discover and implement effective treatment strategies for their clients.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: Vue.js 3 with Inertia.js
- **Styling**: Tailwind CSS 4
- **Database**: PostgreSQL
- **Authentication**: Laravel Breeze
- **Testing**: Pest
- **Build Tool**: Vite

## Requirements

- PHP 8.4+
- Composer
- Node.js 22+
- PostgreSQL
- Laravel Herd (recommended) or other local development environment

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd counsel-wise
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Configure your database in `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=counsel_wise
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

6. Generate application key:
```bash
php artisan key:generate
```

7. Run database migrations:
```bash
php artisan migrate
```

8. Seed the database (optional):
```bash
php artisan db:seed
```

9. Build frontend assets:
```bash
npm run build
```

## Development

Start the development environment:

```bash
# Using Herd (automatically available at counsel-wise.test)
npm run dev

# Or using the built-in development server
composer run dev
```

### Available Scripts

**PHP/Laravel:**
- `composer run dev` - Start development servers (Laravel + Vite + Queue)
- `php artisan test` - Run tests
- `vendor/bin/pint` - Format PHP code

**Frontend:**
- `npm run dev` - Start Vite development server
- `npm run build` - Build for production
- `npm run lint` - Lint JavaScript/Vue files
- `npm run format` - Format frontend code

## Testing

Run the test suite:

```bash
php artisan test
```

Run specific test files:
```bash
php artisan test tests/Feature/Auth/RegistrationTest.php
```

## User Account Types

The platform supports different user roles:

- **Free Counsellor** - Full platform access, but limited to one client profile creation (not editing) in a 30-day period.
- **Student RC** - Full platform access, but limited to five instances of client profile creation (not editing), in a one-year period.
- **Paid Counsellor** - Full platform access, unlimited client profile creation and editing.
- **Researcher** - Access to research updates and relationship configuration. 
- **Super Admin** - Access to administration dashboards for core CRUD operations and insights.

## Security

The application includes comprehensive security measures:

- Automated vulnerability scanning via GitHub Actions
- Dependency security checks
- Code analysis with CodeQL
- Regular security audits
