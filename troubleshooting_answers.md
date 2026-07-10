# Section F: Troubleshooting Written Responses

### Q1 — Slow Dashboard (4 pts)
**Dashboard widgets take 8+ seconds with 500K movements. Diagnose and fix. Provide suspected root cause, 3 diagnostic steps, and solution code.**

**Suspected Root Cause:**
The slow dashboard is likely caused by the widgets executing real-time aggregation queries (such as `COUNT()`, `SUM()`) directly against the `stock_movements` table, which contains 500K+ rows. Without appropriate pre-aggregation, computing these statistics on the fly results in full table scans or expensive index scans on large datasets on every page load.

**Diagnostic Steps:**
1. **Analyze Query Execution Time:** Capture slow SQL queries (Laravel Telescope in development or database slow query log in production), or Filament's query debugging features to capture the exact SQL queries executed during the dashboard load and identify which queries take the most time.
2. **Examine Execution Plans:** Extract the slow aggregate queries and run them with `EXPLAIN` or `EXPLAIN FORMAT=JSON` in MySQL to verify whether they are performing full table scans (`type: ALL`) or scanning hundreds of thousands of rows.
3. **Verify N+1 Issues:** Check the query logs for the N+1 query problem, especially in "Recent Movements" widgets, to ensure related models (like Product and Warehouse) are properly eager-loaded (`with(['product', 'warehouse'])`).

**Solution Code:**
Consistent with this project's Section D optimization strategy, the primary solution is to utilize a **Summary Table (Materialized View approach)** to pre-aggregate the required metrics instead of calculating them on the fly. 

*(Cache can be used as an alternative or supplementary strategy, but a summary table provides the most scalable approach for complex reporting at scale.)*

Update the widget's query to read from the summary table instead of `stock_movements`:

```php
// Inside your Filament Widget (e.g., StatsOverviewWidget.php)

use App\Models\WarehouseStockSummary; // Assuming this model maps to the summary table
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Fetch pre-aggregated data from the summary table instead of calculating it from stock_movements
        $totalStockValue = WarehouseStockSummary::sum('total_stock_value');
        $totalProductsInStock = WarehouseStockSummary::sum('total_products_in_stock');

        return [
            Stat::make('Total Stock Value', '$' . number_format($totalStockValue, 2)),
            Stat::make('Products In Stock', number_format($totalProductsInStock)),
        ];
    }
}
```

---

### Q2 — Git Conflict Resolution (3 pts)
**Two developers edited `ProductResource.php` simultaneously. Git shows a conflict between `numeric('unit_price')` and `money('unit_price')`. Explain your resolution process and how to prevent future conflicts.**

**Resolution Process:**
1. **Inspect the conflict:** Open `ProductResource.php` and locate the standard Git conflict markers (`<<<<<<< HEAD`, `=======`, `>>>>>>>`).
2. **Review both implementations:** Understand why each developer changed the code.
3. **Compare against requirements:** Compare both implementations against the project requirements and coding standards.
4. **Choose the implementation:** Choose the implementation that best satisfies the requirements.
5. **Resolve the conflict:** Manually edit the file to apply the chosen solution and remove the Git markers.
6. **Run tests:** Run the full test suite (`php artisan test`) to ensure everything works correctly.
7. **Commit the merge:** Stage the resolved file and commit the merge.

**Prevention Measure:**
To prevent future conflicts, the team should adopt smaller, more frequent commits and continuously rebase their feature branches against the main development branch. Additionally, active communication regarding task assignments (e.g., assigning specific features or resource files to avoid overlapping work) will drastically reduce merge conflicts.

---

### Q3 — 500 Error After Deployment (3 pts)
**Post-deployment, all API endpoints return 500. Log shows `Unknown column 'products.moq'`. Immediate fix, root cause, and prevention measure?**

**Root Cause:**
A developer deployed code (such as an updated Eloquent Model, Controller, or API Resource) that attempts to access or query a new Minimum Order Quantity (`moq`) column in the `products` table. However, the database schema on the production server was not updated because the corresponding database migration either wasn't run or failed during deployment.

**Immediate Fix:**
1. **Verify Migration Presence:** Verify that the migration containing the `moq` column is included in the deployed release before executing the migration.
2. **Apply Fix (if present):** If the migration is present, SSH into the production server and run `php artisan migrate --force` to create the missing column. This will immediately resolve the 500 errors.
3. **Apply Fix (if missing):** If the migration is missing from the deployed release entirely, running migrate will not help. The immediate action is to rollback the deployment to the previous stable release until the correct branch containing the migration can be redeployed.

**Prevention Measure:**
Database migrations must be fully automated as a mandatory step in the CI/CD deployment pipeline. Using a zero-downtime deployment strategy (like Laravel Envoyer or a custom bash script), the pipeline should:
1. Clone the new release into a temporary directory.
2. Run `composer install` and `php artisan migrate --force`.
3. Only if the migration and all other build steps succeed, swap the active symlink to the new release. 
This ensures that if a migration fails or is missing, the new code is never exposed to users, preventing 500 errors.
