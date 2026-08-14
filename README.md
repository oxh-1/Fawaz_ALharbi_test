# Fawaz Enterprise Platform (Company 2)

## 📌 Project Overview
This project represents a full-stack, enterprise-grade monolithic application consisting of a **Vue.js 2 Options API Frontend** and a **Laravel REST API Backend**. The system features real-time notifications, unified reporting, seamless multi-tenancy, and advanced RBAC capabilities.

## 🚀 Setup Instructions

### 1️⃣ Database Setup & Seeding
1. Make sure your local **MySQL/XAMPP server** is running.
2. Ensure you have created a database called `company2_db` (or whatever your `.env` DB_DATABASE is set to).
3. Navigate to `backend-laravel/`:
```bash
cd backend-laravel
cp .env.example .env
php artisan key:generate
```
4. Reset and intelligently seed the massive database generated from the AI prompt:
```bash
php artisan migrate:fresh --seed --force
```
*Note: This command generates thousands of realistic data points across 10+ relational modules.*

### 2️⃣ Running the Backend (Laravel)
```bash
php artisan serve
```
*Ensure it runs on `http://localhost:8000` to avoid CORS issues with the frontend.*

### 3️⃣ Running the Frontend (Vue)
```bash
npm install
npm run serve
```
*Ensure the App connects successfully. The frontend should run on `http://localhost:8080` or `8081`.*

---

## 🔐 Admin Credentials
- **Email**: `admin@company2.com`
- **Password**: `Admin123!`
- **Role**: `super_admin`

---

## 🏗️ Supported Modules

The system's modular architecture uses a highly abstract controller, **`C2PlatformController.php`**, that handles dynamic generation and CRUD manipulation across the architecture.

| Module | URL Slug | Database Hook | Status |
|--------|---------|--------------|--------|
| **Merchants** | `/c2/merchant` | `merchants` table | ✅ Fully Live |
| **Categories** | `/c2/categories` | `categories` table | ✅ Fully Live |
| **Services** | `/c2/services` | `services` table | ✅ Fully Live |
| **Booking** | `/c2/booking` | `bookings` table | ✅ Fully Live |
| **Reviews** | `/c2/reviews` | `reviews` table | ✅ Fully Live |
| **Contact Us** | `/c2/contact` | `contact_messages` | ✅ Fully Live |
| **Pricing** | `/c2/pricing` | `pricing_plans` | ✅ Fully Live |
| **Ads** | `/c2/ads` | `ads` table | ✅ Fully Live |
| **Content** | `/c2/content` | `content_pages` | ✅ Fully Live |
| **Settlement** | `/c2/settlement` | `settlements` table | ✅ Fully Live |
| **Settings** | `/c2/setting` | `c2_settings` table | ✅ Fully Live |

---

## 📡 API Endpoints 
The highly dynamic API controller supports generic mapping to any database table defined in the model allowed list (`backend-laravel/routes/api.php`):

```http
GET    /api/c2/{type}          - Fetch listing of any module
POST   /api/c2/{type}          - Create new record dynamically
PUT    /api/c2/{type}/{id}     - Update parameters instantly
DELETE /api/c2/{type}/{id}     - Hard-delete entity

GET    /api/c2/settings        - Fetch master platform settings
PUT    /api/c2/settings        - Push modifications across configs
```

---

## 🛠️ Troubleshooting

**1. "Network Error" on Frontend**
Check `backend-laravel/.env`. Ensure `CORS_ALLOWED_ORIGINS` accurately reflects your Vue CLI local port (e.g. `http://localhost:8081`). Ensure `php artisan serve` is actually running.

**2. "Target class [ControllerName] does not exist"**
Depending on caching, you may need to clear routes. 
Run: `php artisan route:clear` and `php artisan cache:clear`.

**3. Module Data Not Showing Up**
We injected a unified `DemoDataSeeder`. Ensure you ran `php artisan migrate:fresh --seed`. Check the target URLs in the network inspector to make sure `apiClient` fired properly.
