# Enterprise Multi-Tenant ERP

A premium, production-ready multi-tenant ERP system built on Laravel 12, featuring glassmorphic UI, robust RBAC, and atomic inventory management.

## 🚀 Key Features

- **Multi-Tenancy**: Domain-based isolation using Stancl Tenancy.
- **Premium Design**: Modern, responsive UI with Glassmorphism and Tailwind CSS 4.
- **Security**: Strict Role-Based Access Control (RBAC) across all modules (CRM, Inventory, Orders).
- **Data Integrity**: Atomic database transactions for complex inventory and fiscal operations.
- **Modules**:
    - **CRM**: Comprehensive customer management and profiling.
    - **Inventory**: Multi-warehouse stock tracking, movements, and suppliers.
    - **Orders**: Full order lifecycle (Sales, Purchase, Returns, Invoicing).
    - **API**: Versioned REST API with Sanctum authentication for mobile/external integrations.

## 🛠 Tech Stack

- **Framework**: Laravel 12 (Strict Types)
- **Database**: SQLite (Central), Multi-DB support for Tenants.
- **Frontend**: Blade, Alpine.js, Tailwind CSS 4.
- **Packages**: Spatie Permission, Spatie ActivityLog, Stancl Tenancy.

## 📦 Installation

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd laravel-app
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Prepare Database**:
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Run Development Server**:
   ```bash
   php artisan dev
   ```

## 🔐 Security & Permissions

This application uses a strict permission-based model. Management of core entities (Users, Roles, Inventory) requires specific management permissions (e.g., `inventory manage`, `users manage`).

---
Built with ❤️ by your AI companion.
