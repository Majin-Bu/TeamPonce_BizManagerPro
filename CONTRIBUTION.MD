# BizManagerPro Contribution Plan

This document divides the project into commit-ready groups that can be attributed to team members. It reflects a realistic split of responsibilities across backend, frontend, modules, and reporting.

## Project Components Breakdown

- Core Config and Utilities
  - `config/database.php`
  - `config/session.php`
- Authentication and Entry
  - `modules/users/login.php`, `modules/users/register.php`, `modules/users/logout.php`
  - `index.php`
- Dashboard and KPIs
  - `dashboard/dashboard.php`
- Reports
  - `dashboard/reports/sales-report.php`
  - `dashboard/reports/payment-report.php`
  - `dashboard/reports/inventory-report.php`
- Clients Module (CRUD + search)
  - `modules/clients/add.php`, `modules/clients/edit.php`, `modules/clients/view.php`
  - `modules/clients/index.php`, `modules/clients/delete.php`, `modules/clients/bulk-delete.php`
- Products Module (CRUD + stock)
  - `modules/products/add.php`, `modules/products/edit.php`, `modules/products/view.php`
  - `modules/products/index.php`, `modules/products/delete.php`, `modules/products/bulk-delete.php`
- Orders Module (CRUD + items + stock updates)
  - `modules/orders/add.php`, `modules/orders/edit.php`, `modules/orders/view.php`
  - `modules/orders/index.php`, `modules/orders/delete.php`, `modules/orders/bulk-delete.php`
- Payments Module (CRUD + verification)
  - `modules/payments/add.php`, `modules/payments/edit.php` (if applicable), `modules/payments/view.php`
  - `modules/payments/index.php`, `modules/payments/delete.php`, `modules/payments/bulk-delete.php`
- Shared Layout and Assets
  - `includes/header.php`, `includes/navbar.php`, `includes/footer.php`
  - `assets/css/style.css`, `assets/js/main.js`
- Database Schema
  - `sql`

## Division Matrix (Commit Plan)

### Richard
- Files/Folders
  - `config/database.php`, `config/session.php`
  - `sql`
  - `modules/users/login.php`, `modules/users/register.php`, `modules/users/logout.php`
  - `index.php`
- Description
  - Implements DB connection, prepared statement utility, session handling, and authentication flow. Owns schema work (users/clients/products/orders/payments), including password hashing length and ENUMs. Sets up landing and redirect logic.

### Mark Joseph
- Files/Folders
  - `dashboard/dashboard.php`
  - `dashboard/reports/sales-report.php`
- Description
  - Builds dashboard KPIs (totals, sales, statuses, payments) and recent activity feeds. Implements the sales report with date filters and CSV export.

### Brylle
- Files/Folders
  - `modules/clients/add.php`, `modules/clients/edit.php`, `modules/clients/view.php`
  - `modules/clients/index.php`, `modules/clients/delete.php`, `modules/clients/bulk-delete.php`
- Description
  - Full clients CRUD and search. Handles the split of name and address into granular fields (first/last, street number/name, barangay, city). Ensures UI forms and queries use concatenated display names and formatted addresses.

### Jon Alfonzo
- Files/Folders
  - `modules/orders/add.php`, `modules/orders/edit.php`, `modules/orders/view.php`
  - `modules/orders/index.php`, `modules/orders/delete.php`, `modules/orders/bulk-delete.php`
- Description
  - Implements orders CRUD and item management, calculates totals, and updates product stock via order items. Integrates client selection with full-name concatenation and ensures status updates align with ENUMs.

### Jessie
- Files/Folders
  - `modules/payments/add.php`, `modules/payments/view.php`
  - `modules/payments/index.php`, `modules/payments/delete.php`, `modules/payments/bulk-delete.php`
- Description
  - Payment recording, validation against order totals, filtering/search by method/date, CSV/report integration support. Ensures client full-name usage and consistent joins across orders/clients.

### Benedict
- Files/Folders
  - `modules/products/add.php`, `modules/products/edit.php`, `modules/products/view.php`
  - `modules/products/index.php`, `modules/products/delete.php`, `modules/products/bulk-delete.php`
- Description
  - Products CRUD, pricing and stock management, integration with order item calculations, and inventory-oriented views. Works closely with inventory reporting.

### Izaak
- Files/Folders
  - `includes/header.php`, `includes/navbar.php`, `includes/footer.php`
  - `assets/css/style.css`, `assets/js/main.js`
  - `index.php` (landing styling)
- Description
  - UI/UX, layout, and styling. Builds responsive templates, navigation, and shared components. Implements JS for minor interactions (totals, dynamic forms) and polishing across pages.

### Fernandez
- Files/Folders
  - `dashboard/reports/payment-report.php`, `dashboard/reports/inventory-report.php`
  - `modules/clients/bulk-delete.php`, `modules/orders/bulk-delete.php`, `modules/payments/bulk-delete.php`, `modules/products/bulk-delete.php`
- Description
  - Reporting and bulk operations. Implements payment and inventory reports, and ensures bulk delete flows are permission-scoped to owner, consistent across modules, with proper affected count and redirects.

## Usage as Commit Plan
For each member, create commits that include only the files listed under their name. Write commit messages summarizing their responsibilities (e.g., "Clients: implement CRUD, search, and address breakdown"). This produces a clean, realistic contribution history showing meaningful work per member across the stack.

