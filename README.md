# Barangay Tourism Management System

A comprehensive tourism portal built with **Laravel 11** and **Filament v3** designed to streamline guest arrivals, beach resort bookings, and local fee collections.

## 🚀 Key Features

### 📊 Role-Based Dashboards
* **Admin:** Full access to financial analytics, payment methods, and system configurations.
* **Staff:** Operational view limited to guest arrivals and booking lists, hiding all sensitive financial data.

### 🏨 Beach Resort & Accommodation Management
* **Resorts:** A dedicated resource to manage beach resort directories, including location and contact details.
* **Accommodations:** Manage various room types, cottages, and lodging availability linked to specific resorts.
* **Bookings:** Integrated guest reservation system with real-time tracking of visit dates and payment statuses (Paid/Unpaid).

### 💰 Fee Configuration (System Settings)
* **Manage Settings:** A specialized resource for Admins to configure standard port fees:
    * **Ecological Fee:** Standard environmental fee per guest.
    * **Parking Fee:** Standard fee for vehicle arrivals.
    * **Boat Fee:** Standard transport/island hopping fees.

## 🛠️ Installation & Setup

1. **Clone and Install:**
   ```bash
   git clone <your-repo-url>
   composer install
   npm install && npm run build
