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

The application uses Pest for feature testing. All critical business rules are tested at the Domain Model layer to guarantee universal data integrity.
```bash
php artisan test
# or
./vendor/bin/pest
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
| **B** | REST API + Integration | Pending |
| **C** | Livewire + Alpine.js | Pending |
| **D** | Database & SQL (Performance) | Pending |
| **E** | DevOps & Deployment | Pending |
| **F** | Troubleshooting Written Answers | Pending |

---

## Known Limitations
- The current implementation covers Section A only. Sections B–F are pending.
- Form inputs in Filament do not yet automatically calculate available stock for real-time validation without submitting, though server-side enforcement prevents invalid data.

## Bonus Completed
- None yet.