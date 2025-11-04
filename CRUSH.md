# CRUSH.md - Agent Guide for IONBEC Codebase

## Project Overview

IONBEC is a Computer-Based Testing (CBT) platform built with Laravel 9 and Vue.js 3. It's a multi-tenant examination system with a Rust microservice for high-performance scoring and CSV processing.

## Core Architecture

- **Backend**: Laravel 9 (PHP 8.1+)
- **Frontend**: Vue.js 3 with Inertia.js and Tailwind CSS
- **Database**: PostgreSQL (external)
- **Queue/Cache**: Redis (external)
- **Real-time**: Soketi WebSocket server
- **Microservice**: Rust service for scoring/CSV processing
- **Deployment**: Docker with Coolify on cf.avolut.com

## Development Environment Setup

### Prerequisites
- Docker & Docker Compose
- Node.js (specified in `.tool-versions` with asdf)
- PHP 8.1+

### Commands

**Docker Services:**
```bash
# Start all services
./nge compose up -d

# Stop services
./nge compose down

# View logs
./nge compose logs -f [service-name]
```

**Laravel Development:**
```bash
# Install dependencies
./nge composer install
npm install

# Database setup
./nge artisan migrate:fresh --seed
# Default login: admin / password

# Development server
npm run watch
# Access: http://localhost:8000
```

**Asset Building:**
```bash
# Development
npm run dev

# Production build
npm run production

# Watch for changes
npm run watch
```

**Testing:**
```bash
# Run Pest tests
./vendor/bin/pest

# Run PHPUnit (legacy)
./vendor/bin/phpunit

# Run specific test
./vendor/bin/pest tests/Feature/AuthenticationTest.php
```

**Code Style:**
```bash
# Fix PHP code style
./vendor/bin/php-cs-fixer fix

# Check for issues
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Key Services in Docker

1. **app**: Laravel application (port 3000)
2. **queue**: Laravel queue worker
3. **scheduler**: Laravel task scheduler
4. **soketi**: WebSocket server (port 6000)
5. **rust-service**: Scoring and CSV processing (port 3001)

## Database & External Services

- **PostgreSQL**: Hosted externally (107.155.75.50:5986)
- **Redis**: Hosted externally (107.155.75.50:9675)
- Use `psql` with credentials from `.env` for direct DB access
- Use local MySQL for legacy queries when needed

## Frontend Architecture

**Key Patterns:**
- Uses Inertia.js for SPA-like navigation
- Pinia for state management (navigation stores)
- Vue 3 Composition API
- Tailwind CSS for styling
- Ziggy for Laravel route integration

**Important Files:**
- `resources/js/app.js`: Main entry point
- `resources/js/Libs/ziggy.js`: Custom route helper (forces relative URLs)
- `resources/js/Store/`: Pinia stores
- `resources/js/Pages/`: Vue page components

## Backend Architecture

**Multi-tenant Structure:**
- Clients table for tenant isolation
- User management per client
- Role-based permissions using Laravel permissions

**Key Controllers:**
- `Exam/`: Exam-taking flow
- `BackOffice/`: Admin interface
- `Taker/`: Student/test-taker interface
- `Interview/`: Live interview functionality

**Services:**
- `RustService`: Interface to Rust microservice
- `ClientStorageService`: Multi-tenant storage handling
- `TraefikDomainService`: Dynamic domain configuration

## Route Generation

**Critical Issue Fixed:**
- Custom Ziggy helper in `resources/js/Libs/ziggy.js` forces relative URLs
- Prevents localhost:8000 URLs in production
- Always verify route generation in browser console

## Testing Strategy

- **Primary**: Pest PHP tests in `tests/Feature/` and `tests/Unit/`
- Uses `RefreshDatabase` trait for test isolation
- Test database uses array drivers for speed

## Rust Microservice

**Location:** `rust-service/`
**Purpose:** High-performance CSV processing and exam scoring
**Build:**
```bash
cd rust-service
cargo build --release
```

**Key Features:**
- Direct database access
- Redis caching
- CSV batch processing
- Scoring algorithms

## Deployment

**Production Server:** cf.avolut.com
**Container:** app-okksscs4w0s8oc0go0k4cg8k
**App Path:** `/var/www/` (NOT `/var/www/html/`)

**Deploy Route Fixes:**
```bash
# Copy custom ziggy.js
scp resources/js/Libs/ziggy.js root@cf.avolut.com:/tmp/ziggy.js
ssh root@cf.avolut.com "docker cp /tmp/ziggy.js app-okksscs4w0s8oc0go0k4cg8k:/var/www/resources/js/Libs/ziggy.js"

# Rebuild assets
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && npm run production'"

# Clear caches
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear'"
```

## Code Conventions

**PHP:**
- PSR-12 compliance enforced by php-cs-fixer
- Use Laravel conventions (snake_case DB, camelCase PHP)
- Route attributes using `Dentro\Yalr\Attributes`

**JavaScript:**
- Vue 3 Composition API preferred
- Pinia stores for state management
- Tailwind utility classes for styling

**Database:**
- UUID primary keys for some tables
- Hash columns for data integrity
- Soft deletes where appropriate

## Common Gotchas

1. **Multi-tenant**: Always consider client_id when querying
2. **Route URLs**: Use relative URLs, never assume localhost
3. **Asset Building**: Always run `npm run production` for deployment
4. **Database**: Use external Postgres, not local Docker
5. **Redis**: External Redis, configure timeouts appropriately
6. **Rust Service**: Ensure service is running before CSV operations

## Debugging Tools

**Laravel Debug:**
```bash
# View logs
./nge artisan log:show

# Clear caches
./nge artisan cache:clear

# Test routes
./nge artisan route:list
```

**Frontend Debug:**
- Browser console for Ziggy route verification
- Vue devtools for component state
- Network tab for Inertia requests

## Migration & Seeding

**Important Seeders:**
- `DatabaseSeeder`: Main seeder orchestration
- `SampleClientSeeder`: Creates sample client data
- `UsersTableSeeder`: Creates admin users

**Custom Migrations:**
- Hash columns added to major tables
- Client multi-tenancy support
- Delivery snapshots for scoring

## Performance Considerations

- Rust service handles heavy CSV processing
- Redis caching for frequently accessed data
- Queue system for background processing
- Database indexes optimized for tenant queries

## Security Notes

- Multi-tenant data isolation critical
- All routes protected by appropriate permissions
- Hash-based verification for data integrity
- CSRF protection enabled