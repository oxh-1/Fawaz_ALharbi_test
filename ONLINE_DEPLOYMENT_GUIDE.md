# 🌐 Fawaz Enterprise Platform: Online & Cloud Deployment Guide

This guide walks you through making the **Fawaz Platform (Web Frontend + Laravel REST API + Database)** online and publicly accessible on the internet.

---

## ⚡ Option 1: Instant Local Network / Intranet Online Access (Already Active!)

Your development servers are currently listening on `0.0.0.0`, allowing any phone, laptop, or tablet on your local network/Wi-Fi to access the application immediately:

- **Web Application URL:** `http://192.168.0.178:8080/`
- **Backend API URL:** `http://192.168.0.178:8000/api`
- **Public Tunneling (Optional for Public Internet):**
  If you want anyone in the world to access your local machine right now without deploying to the cloud:
  ```bash
  # Using ngrok or localtunnel
  npx localtunnel --port 8080
  # Or:
  # ngrok http 8080
  ```

---

## ☁️ Option 2: 1-Click Free Cloud Deployment (Railway / Render / Fly.io)

### Method A: Deploy on [Railway.app](https://railway.app) (Recommended)
1. Sign up on [Railway.app](https://railway.app) using GitHub.
2. Click **New Project** → **Deploy from GitHub repo** → select `Fawaz_ALharbi_test`.
3. Add a **MySQL** or **PostgreSQL** Database service from Railway with 1 click.
4. Set the environment variables in Railway:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:4yH3g25Ua/QhZ8r9h3ZkGq1l0Vn+8J2mYw=
   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   ```
5. Railway will automatically build and deploy both your API and database online with a free `*.up.railway.app` SSL domain!

---

### Method B: Deploy on [Render.com](https://render.com)
1. Push your repository to GitHub.
2. Log into [Render.com](https://render.com) and create:
   - **PostgreSQL Database** (Free Tier).
   - **Web Service** for `backend-laravel` using Docker (`backend-laravel/Dockerfile`).
   - **Static Site** for the Frontend pointing to the `dist/` directory or root `Dockerfile`.
3. Add your database connection string to Environment settings.

---

## 🐳 Option 3: Containerized Docker Deployment on any VPS (AWS, DigitalOcean, Hetzner, Oracle)

We have created [`docker-compose.yml`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/docker-compose.yml), [`Dockerfile`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/Dockerfile), and [`backend-laravel/Dockerfile`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/backend-laravel/Dockerfile).

On any server or Linux VPS with Docker installed, simply run:

```bash
# 1. Clone repo
git clone https://github.com/your-repo/Fawaz_ALharbi_test.git
cd Fawaz_ALharbi_test

# 2. Launch MySQL Database, Laravel API & Vue Web App in 1 command
docker compose up -d --build

# 3. Check running services
docker compose ps
```

Your web app will be live on port `80` (`http://your-server-ip`), connected to the live MySQL database container and Laravel API!

---

## 🛡️ Production Default Credentials
- **Super Admin:** `admin@company2.com` / `Admin123!`
- **Accounting & Finance:** `accountant@company2.com` / `Accountant123!`
- **Customer Support:** `support@company2.com` / `Support123!`
- **Operations:** `operations@company2.com` / `Operations123!`
