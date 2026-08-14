# ☁️ How to Deploy the Platform & Database to Cloudflare

This complete guide walks you through deploying the **Vue Frontend**, **Laravel API**, and **Database** to **Cloudflare** for global edge performance, zero server downtime, and free SSL.

```
                    ┌────────────────────────────────────────────────────────┐
                    │               CLOUDFLARE GLOBAL EDGE CDN               │
                    └───────────────────────────┬────────────────────────────┘
                                                │
                 ┌──────────────────────────────┴──────────────────────────────┐
                 ▼                                                             ▼
     [ Cloudflare Pages ]                                            [ Cloudflare Tunnel / DNS ]
  (Vue Frontend Single Page App)                                     (Laravel REST API Backend)
   URL: https://fawaz.pages.dev                                        URL: https://api.yourdomain.com
                 │                                                             │
                 │                                                             ▼
                 └───────────────────────────────► [ Cloudflare Hyperdrive / Cloud DB ]
                                                   (MySQL / PostgreSQL / Supabase / Neon)
```

---

## 🚀 Step 1: Deploy Frontend to Cloudflare Pages (100% Free)

1. Log into your [Cloudflare Dashboard](https://dash.cloudflare.com/).
2. In the left sidebar, click **Workers & Pages** → **Create application** → **Pages** → **Connect to Git**.
3. Select your GitHub repository: `Fawaz_ALharbi_test`.
4. Configure Build Settings:
   - **Project Name:** `fawaz-platform`
   - **Production branch:** `main` (or `master`)
   - **Framework preset:** `Vue.js`
   - **Build command:** `npm run build`
   - **Build output directory:** `dist`
   - **Root directory:** `/`
5. In **Environment Variables**, add:
   ```env
   VUE_APP_API_URL=https://api.yourdomain.com/api
   NODE_VERSION=18
   ```
6. Click **Save and Deploy**.
   > ✅ We have already added [`public/_redirects`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/public/_redirects) (`/* /index.html 200`) and [`public/_headers`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/public/_headers) so your SPA routes and dark mode will load cleanly with 0 errors!

---

## 🗄️ Step 2: Set Up the Database with Cloudflare

Cloudflare provides two ways to connect your database:

### Option A: Cloudflare Hyperdrive (Accelerates Any MySQL / Postgres DB)
1. In Cloudflare Dashboard, go to **Storage & Databases** → **Hyperdrive**.
2. Click **Create Hyperdrive configuration**.
3. Input your Cloud Database details (e.g. from **Supabase**, **Neon Postgres**, **PlanetScale MySQL**, or **Railway DB**):
   - **Host:** e.g. `aws.connect.psdb.cloud` or `db.yourhost.com`
   - **Port:** `3306` (MySQL) or `5432` (PostgreSQL)
   - **Database Name:** `fawaz_platform`
   - **User & Password:** Your database credentials.
4. Hyperdrive pools connections globally, caching queries and accelerating database response times by **up to 10x**!

### Option B: Cloudflare D1 (Serverless SQLite at the Edge)
1. Go to **Storage & Databases** → **D1 SQL Database** → **Create Database**.
2. Run database migration schema directly in the Cloudflare D1 web console.

---

## 🔌 Step 3: Connect Laravel Backend to Cloudflare

### Method 1: Instant Cloudflare Tunnel (`cloudflared`) (Zero Open Ports)
If running Laravel on your server / local VPS / Docker:
1. In Cloudflare Dashboard, go to **Zero Trust** → **Networks** → **Tunnels** → **Create a Tunnel**.
2. Name the tunnel: `fawaz-api-tunnel`.
3. Choose your OS (Windows, Linux, or Docker) and run the 1-command installer provided by Cloudflare:
   ```bash
   # Windows PowerShell:
   winget install Cloudflare.cloudflared
   cloudflared.exe service install <TOKEN_FROM_CLOUDFLARE>
   ```
4. In the **Public Hostnames** tab:
   - **Subdomain:** `api`
   - **Domain:** `yourdomain.com`
   - **Service Type:** `HTTP`
   - **URL:** `localhost:8000` (or `127.0.0.1:8000`)
5. Click **Save**. Now your Laravel API is live securely worldwide at `https://api.yourdomain.com/api`!

---

### Method 2: Cloudflare Orange Cloud Proxy
If hosting Laravel on a cloud host (e.g. Railway, Render, DigitalOcean):
1. In Cloudflare Dashboard, go to **DNS** → **Records**.
2. Add a **CNAME** or **A record**:
   - **Type:** `CNAME`
   - **Name:** `api`
   - **Target:** `your-backend-service.up.railway.app` (or your VPS IP)
   - **Proxy status:** 🟠 **Proxied** (Orange Cloud turned ON for DDoS protection and free SSL).

---

## 🌐 Step 4: Custom Domain Configuration

In Cloudflare Dashboard:
1. **Frontend:** In **Workers & Pages** → `fawaz-platform` → **Custom domains** → Add `yourdomain.com` and `www.yourdomain.com`.
2. **Backend:** Routed via Cloudflare Tunnel or DNS Proxy to `api.yourdomain.com`.
3. In **SSL/TLS** tab, set Encryption mode to **Full (strict)**.

---

## ✅ Summary of Added Cloudflare Config Files
- **[`public/_redirects`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/public/_redirects)**: Handles Vue Router client routing on Cloudflare Pages.
- **[`public/_headers`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/public/_headers)**: Caching and security headers at Cloudflare edge.
- **[`docker-compose.yml`](file:///c:/Users/PC/Documents/GitHub/Fawaz_ALharbi_test/docker-compose.yml)**: Instant containerized deployment.
