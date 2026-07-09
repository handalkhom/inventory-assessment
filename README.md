# Inventory Assessment

## Requirements

PHP 8.2

Composer

Node.js

MySQL

---

## Installation

composer install

npm install

cp .env.example .env

php artisan key:generate

Configure database

php artisan migrate:fresh --seed

npm run build

php artisan serve

---

## Current Progress

✅ Foundation Complete

✅ Section A: Filament CRUD

⬜ Section B: REST API

⬜ Section C: Livewire

⬜ Section D: Database Performance

⬜ Section E: DevOps

⬜ Section F: Troubleshooting

---

## Section A: Laravel + Filament CRUD

**Implemented Features:**
- **Foundation Layer:** Full schema migrations, strictly typed Eloquent Models, Enums, Factories, and a comprehensive `DemoSeeder`.
- **Domain-Driven Enforcement:** Core business rules (BR1 SKU immutability, BR2 warehouse stock checks, BR3 transfer limits, BR5 non-zero quantities) are enforced within Eloquent Model events (`booted()`). Filament acts strictly as a presentation layer.
- **Warehouse Resource:** Full CRUD with active status filters, capacity sorting, and a `ProductsRelationManager` to view stock.
- **Product Resource:** Full CRUD with global search, category filters, and a `WarehousesRelationManager` with editable pivot stock values.
- **StockMovement Resource:** Full CRUD featuring date range/type/warehouse filters, and a standard Filament CSV Export action for filtered records.
- **Dashboard & Stats:** Implemented widgets for total active products, top 5 warehouses, recent stock movements, today's movements, and inbound vs outbound quantities.

**Implementation Assumptions:**
- **Capacity Utilization Widget:** Since the provided `products` schema lacks a physical volume (m³) metric, the "Top 5 Warehouses by Capacity Utilization" widget compares the raw item count (sum of `quantity_on_hand`) directly against the warehouse `capacity_m3`. No arbitrary density formulas were introduced.
- **CSV Export:** Relies on the native Filament v3/v4 Exporter (`ExportAction`), exporting filtered records optimally via background jobs.