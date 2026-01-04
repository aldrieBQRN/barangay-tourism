# 🌴 Barangay Tourism Management System (BTMS)

A robust, enterprise-grade tourism portal developed with **Laravel 11** and **Filament v3**. This system provides an end-to-end solution for local governments and resort owners to manage guest traffic, environmental impact fees, and accommodation logistics through a highly secure, role-based architecture.

## 🚀 Key Modules & Features

### 📊 Intelligence Dashboards
* **Administrative Analytics:** Provides the Super Admin with a high-level overview of revenue streams, payment method distribution, and resort popularity.
* **Operational Staff View:** A streamlined, "clutter-free" dashboard for frontline staff (e.g., Clarisse) focusing on immediate tasks: Guests Today, Expected Arrivals, and Pending Check-ins.

### 🏨 Destination & Lodging Management
* **Resort Directory:** A centralized registry for all beach resorts, including metadata for locations and direct contact information.
* **Accommodation Inventory:** Granular management of available lodging options—from luxury rooms to beachfront cottages—linked directly to their parent resorts.
* **Dynamic Booking Engine:** Real-time guest reservation tracking featuring automated status badges for "Paid" and "Unpaid" arrivals to ensure revenue integrity.

### 💰 Global Fee Configuration
* **Unified Settings Resource:** A dedicated administrative control panel to standardize local tourism costs across the entire barangay:
    * **Ecological Fee:** Automated environmental conservation tax applied per guest.
    * **Parking Fee:** Fixed-rate vehicle entry management.
    * **Boat Fee:** Standardized transport and island-hopping logistics pricing.

## 🔐 Advanced Security & RBAC
The system utilizes **Filament Shield** to implement a strict "Least Privilege" security model:

* **Logic Separation:** Management resources (Users, Roles, System Settings) are strictly isolated from the Staff role to prevent unauthorized configuration changes.
* **Widget Security:** Front-end components are conditionally rendered based on user roles, ensuring sensitive financial charts never appear on staff terminals.

## 🛠️ Technical Stack & Setup

1.  **Core Framework:** Laravel 11 (PHP 8.2+)
2.  **Admin UI:** Filament v3 (TALL Stack)
3.  **Permissions:** Spatie Permission / Filament Shield
4.  **Database:** MySQL/MariaDB

### Quick Installation
```bash
# Install and Build
composer install
npm install && npm run build

# Setup Database and Security
php artisan migrate
php artisan shield:generate --panel=admin

# Clear Caches for UI Updates
php artisan permission:cache-reset
php artisan filament:clear-cached-components
