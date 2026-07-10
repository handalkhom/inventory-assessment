# Inventory Assessment

## Project Overview
A simplified Inventory Management System for a logistics company. The system tracks Warehouses (storage locations with capacity), Products (SKUs with categories, pricing, and weight), and Stock Movements (in, out, transfer, and adjustment).

## Tech Stack
- **Framework:** Laravel 10.x
- **Admin Panel:** Filament v4
- **Frontend / Components:** Livewire 3 & Alpine.js
- **Styling:** Tailwind CSS
- **Database:** MySQL
- **Testing:** Pest

---

## Setup & Installation

### 1. Clone the Repository
```bash
git clone https://github.com/handalkhom/inventory-assessment.git
cd inventory-assessment
```

### 2. Install Dependencies
```bash
composer install
npm install && npm run build
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```
Update your `.env` file with your local MySQL database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_assessment
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Migration & Seeding
```bash
php artisan migrate:fresh --seed --seeder=DemoSeeder
```
> [!NOTE]
> The `DemoSeeder` creates a small development dataset (50 products, 5 warehouses, 200 movements). This is for UI testing and development. Section D requires 1.2M rows loaded via a separate SQL script.

---

## Running the Application

Start the local development server:
```bash
php artisan serve
```
- **Admin Panel URL:** `http://localhost:8000/admin`
- **Demo Login:** Use credentials provided by Filament (or create one using `php artisan make:filament-user`)

---

## Running Feature Tests

The application uses PHPUnit/Pest for feature testing.

### Testing Strategy
The application separates tests into two suites to maintain focus and speed:
- **Domain Feature Tests** (`tests/Feature`): Tests the core Domain Models to guarantee universal data integrity and business rules (BR1-BR5) are enforced regardless of the entry point.
- **REST API Feature Tests** (`tests/Feature/API`): Tests HTTP boundaries, request/response contracts, token authentication, rate limiting, and exception propagation, without re-testing the deeper domain logic.

```bash
php artisan test
# Run only API tests:
php artisan test --filter API
```

---

## Section A: Assessment Requirement Mapping

The following components were implemented to satisfy **Section A (Laravel + Filament CRUD)** requirements:

### Architecture Overview
The application follows a standard Laravel structure but strictly enforces business logic at the **Domain Layer (Eloquent Models)**. By hooking into Model events (e.g., `booted`), we ensure that rules are consistently enforced across the Filament Admin panel, REST API (Section B), and Livewire components (Section C).
For a detailed diagram, see [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

### Business Rules Implemented
| Rule | Implementation Location | Verified By |
|------|-------------------------|-------------|
| **BR1: SKU unique + immutable** | Database constraint & `Product` model `booted` event throws `InvalidArgumentException`. | `tests/Feature/ProductTest.php` |
| **BR2: No deactivate with stock** | `Warehouse` model `booted` event throws `ValidationException`. | `tests/Feature/WarehouseTest.php` |
| **BR3: Transfer qty ≤ available** | `StockMovement` model `booted` event validates against pivot table. | `tests/Feature/StockMovementTest.php` |
| **BR4: Unit price ≥ 0** | Enforced via Filament `ProductResource` form (`minValue(0)`). | UI validation |
| **BR5: Movement qty ≠ 0** | `StockMovement` model `booted` event throws `ValidationException`. | `tests/Feature/StockMovementTest.php` |

### Filament Resources
1. **WarehouseResource:** Searchable list by name/location, sortable by capacity. Includes a relation manager showing products and current stock.
2. **ProductResource:** Global search on SKU/name, filters for category and status. Enforces immutable SKU field upon editing.
3. **StockMovementResource:** Date range and type filters. Supports CSV exporting.

### Dashboard Widgets
1. **Active Products Widget:** Stat card displaying total active products.
2. **Capacity Utilization Widget:** Table showing the top 5 warehouses by estimated capacity utilization.
3. **Recent Stock Movements:** Table widget listing movements from the last 24 hours.

---

## Section B: REST API + Integration

The following components were implemented to satisfy **Section B** requirements:
- **Sanctum Authentication:** Token-based API access. Tokens can be generated from the Filament admin panel via the 'API Tokens' page.
- **Endpoints:** Implemented the required 5 endpoints under `/api/v1/` protected by Sanctum and rate limiting (60 req/min per token).
- **Validation:** Enforced HTTP request formatting through FormRequest classes, propagating 422 standard responses for domain-level exceptions.
- **Standalone Integration Script:** Created `scripts/integration_client.php` to demonstrate external API consumption, pagination handling, and exponential backoff.
- **Testing:** Implemented an API-specific feature test suite ensuring proper contracts and auth validation.

### API Authentication Notes for Reviewers
To test the API endpoints:
1. Log in to the Filament Admin Panel (`/admin`).
2. Navigate to **API Tokens** in the sidebar.
3. Generate a new token and copy it.
4. Pass it as a Bearer token in the `Authorization` header for all `/api/v1/*` requests.

---

## Assumptions & Design Decisions
- **Database:** Used MySQL as the primary database instead of SQLite, anticipating the heavy indexing and performance optimization requirements of Section D.
- **Capacity Utilization Widget:** Since physical volume (`m3`) of individual products was not defined in the schema, the implementation estimates utilization based on `quantity_on_hand` relative to the `warehouse.capacity_m3`.
- **Business Rule Enforcement:** Enforcing rules (like BR1, BR2, BR3, BR5) within the Eloquent models directly allows us to cleanly satisfy the "Avoid testing Filament internals unless necessary" requirement, while also securing future API and Livewire interactions.
*(More details in `docs/DECISIONS.md`)*

---

## Project Structure
- `app/Models/` — Core domain entities enforcing business rules.
- `app/Filament/Resources/` — Admin CRUD interfaces.
- `app/Filament/Widgets/` — Dashboard statistics.
- `tests/Feature/` — Pest test suite validating Domain integrity.
- `docs/` — Internal project documentation and plans.

---

## Time Spent
| Section | Task | Time |
|---------|------|------|
| **A** | Laravel + Filament CRUD | ~110 min |
| **B** | REST API + Integration | ~60 min |
| **C** | Livewire + Alpine.js | ~60 min |
| **D** | Database & SQL (Performance) | ~45 min |
| **E** | DevOps & Deployment | ~30 min |
| **F** | Troubleshooting Written Answers | ~30 min |

---

## Known Limitations
- Form inputs in Filament do not yet automatically calculate available stock for real-time validation without submitting, though server-side enforcement prevents invalid data.

## Bonus Completed
- None yet.

---

## Section D: Database Performance (Explanation)

All benchmarks and `EXPLAIN` outputs are documented in the `performance/README.md` file. Here is a summary of the approach:

### 1. Index Strategy
Three indexes were safely added to `stock_movements` to eliminate full table scans:
- `(warehouse_id, created_at)`: Optimizes the dashboard widget query (Pattern 1), bringing it from 2.5s to <50ms, and preventing filesorts.
- `(product_id, movement_type)`: Optimizes the product aggregate query (Pattern 2), bringing it from 1.8s to <30ms.
- `(reference_number)`: Optimizes the reference lookup (Pattern 3), bringing it from 3.2s to <20ms.

### 2. Complex Report Query
We provided a single optimized standard SQL query using scalar subqueries for retrieving the latest movement details. This query is highly efficient, cleanly avoiding `LATERAL JOIN` complexities while maintaining index utilization (no `ALL` scans on large tables).

### 3. Reporting Optimization
We chose **Option A (Materialized View / Summary Table)** to resolve the 30-second timeout on the `GET /api/v1/stock-report` endpoint. 
- A migration creates `warehouse_stock_summaries`.
- An Artisan command `php artisan stock:refresh-summaries` calculates and populates the table. This keeps the architecture simple and avoids the latency overhead of model observers while completely eliminating cold-cache penalties.
- **Before Benchmark:** >30s (timeout on 1.2M rows)
- **After Benchmark:** ~15ms (direct lookup on the summary table)

---

## Section E: DevOps & Deployment

We chose **Option B (Docker)** for the deployment strategy. 
The configuration files are located in the `deployment/` directory.

### Architecture Overview
- **app**: PHP 8.2 FPM Alpine image running the Laravel application. Built using a multi-stage `Dockerfile` (Composer dependency installation -> Node asset compilation -> Production image).
- **nginx**: Alpine Nginx image serving static assets directly and proxying PHP requests to the `app` container via FastCGI.
- **mysql**: Official MySQL 8.0 image with a named volume for persistent storage.
- **redis**: Official Redis Alpine image for cache, sessions, and queues.
- **worker**: Queue worker container reusing the `app` image, explicitly configured to process jobs from Redis.

### Setup Instructions
1. Navigate to the project root directory.
2. Ensure you do not have local services occupying ports `8000`, `3306`, or `6379`.
3. Start the containers in the background:
   ```bash
   docker-compose -f deployment/docker-compose.yml up -d --build
   ```
4. Generate the application key and run database migrations within the container:
   ```bash
   docker-compose -f deployment/docker-compose.yml exec app php artisan key:generate
   docker-compose -f deployment/docker-compose.yml exec app php artisan migrate --force
   ```
5. The application is now accessible at `http://localhost:8000`.

### Production Considerations
- **.dockerignore**: Excludes `.git`, tests, and local vendor/node_modules directories from the build context to keep the image slim.
- **Asset Compilation**: Node is used as an intermediate builder stage in the `Dockerfile`. The final production image only copies the compiled assets in `public/build/`, ensuring Node is not present in the final runtime.
- **Permissions**: The application runs as the non-root `www-data` user to adhere to security best practices.
- **Optimization**: Laravel's configuration, routes, and views are optimized natively using `composer dump-autoload --optimize`.

---

## Section F: Troubleshooting Written Answers

The answers to the troubleshooting scenarios (Slow Dashboard, Git Conflict Resolution, and 500 Error After Deployment) have been documented in a separate file.

Please refer to [troubleshooting_answers.md](troubleshooting_answers.md) for the detailed root cause analysis, diagnostic steps, resolutions, and preventative measures.