# Security & Isolation Audit Report

## Executive Summary
The application demonstrates a strong foundation for Multi-Tenancy using the `stancl/tenancy` package with a **Multi-Database** architecture. This provides the highest level of data isolation. However, a critical architectural flaw was identified where **Tenant Controllers are being reused in the Central Application context**, which compromises logic isolation and leads to potential data integrity issues or confusion between "Global" and "Tenant" data.

## 1. Critical Findings

### 1.1. Cross-Context Controller Contamination
**Severity:** High
**Location:** `routes/web.php`
**Description:**
The Central Application routes (accessible by Super Admins) are defined to use Controllers explicitly designed for the Tenant Context.
```php
// routes/web.php
Route::resource('customers', \App\Http\Controllers\Tenant\CustomerController::class)->names('central.customers');
```
**Impact:**
-   **Wrong Database Usage:** When accessed via the Central Domain, `Tenant\CustomerController` executes using the **Central Database** connection.
-   **Security Risk:** Use of tenant-specific logic (e.g., `authorize('customers manage')`) against the Central User/Permission tables, which may not be synchronized.
-   **Data Model Confusion:** Mixing "Global Customers" (if they exist) with "Tenant Customers" logic handles.
**Recommendation:**
Create dedicated Controllers for the Central Context (e.g., `App\Http\Controllers\Central\CustomerController`) even if the logic is currently similar. This ensures strict separation of concerns and allows for divergent evolution of Global vs. Tenant features.

## 2. Security Improvements

### 2.1. Middleware Redundancy
**Severity:** Low
**Location:** `App\Http\Middleware\ValidateTenantAccess.php`
**Description:**
The middleware performs a secondary check for user existence in the DB: `User::where('id', $user->id)->exists()`.
**Observation:**
Since `Authenticate` middleware runs *after* Tenancy Initialization (database switch), the user is already loaded from the Tenant DB. Querying existence again is redundant but harmless.
**Recommendation:**
Keep it as a "Defense in Depth" measure, but ensure `InitializeTenancyByDomain` always has higher priority than `Authenticate` (which is correctly configured in `TenancyServiceProvider`).

### 2.2. Tenant Identification
**Severity:** Info
**Description:**
`config/tenancy.php` identifies tenants by Domain.
**Recommendation:**
Ensure `central_domains` list in `.env` is strictly managed in production to prevent a Tenant blocking the Central App by claiming a reserved subdomain.

## 3. Isolation Analysis

-   **Database:** ✅ Verified Multi-DB approach (Separate DB per tenant).
-   **Storage:** ✅ Verified `suffix_base` configuration for filesystem isolation.
-   **Cache:** ✅ Verified `tag_base` usage.
-   **Sessions:** ✅ Cookies are domain-scoped by default in browsers.
-   **API:** ✅ Sanctum tokens are stored in Tenant DBs (verified migrations).

## 4. Action Plan for Fixes

1.  **Refactor Routes**: Update `routes/web.php` to point to `Central` namespace controllers.
2.  **Duplicate/Stub Controllers**: Create `App\Http\Controllers\Central\CustomerController` to handle Global Customers (or remove the feature if unintended).
3.  **Review Permissions**: Ensure Central Admin permissions (`customers manage`) exist in the Central DB seeding.
