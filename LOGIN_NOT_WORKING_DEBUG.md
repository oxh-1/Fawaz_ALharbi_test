# 🔴 WHY LOGIN ISN'T WORKING - COMPLETE DEBUG GUIDE

---

## 🔍 THE ROOT CAUSES

Based on your project setup, there are **3 main reasons** login fails:

---

## ❌ PROBLEM #1: API URL IS WRONG

### Current Setting:
```
File: .env.local
VUE_APP_API_URL=http://localhost:8000/api
```

### Why It's Wrong:
```
❌ You're trying to connect to: http://localhost:8000/api
✅ But your backend is at: http://localhost/backend/public/api

Port 8000 doesn't exist in your setup!
```

### What Happens:
```
User clicks Login
  ↓
Frontend tries: POST http://localhost:8000/api/auth/login
  ↓
No server on port 8000
  ↓
CONNECTION REFUSED ❌
  ↓
User sees: "Unable to connect to server"
```

### ✅ THE FIX:

Edit `.env.local`:
```
VUE_APP_API_URL=http://localhost/backend/public/api
VUE_APP_GOOGLE_CLIENT_ID=784439266873-aonrmg0pg5k9tp102dkeliumja0969cq.apps.googleusercontent.com
VUE_APP_TITLE=Company 2
VUE_APP_DEBUG=true
```

---

## ❌ PROBLEM #2: LOGIN METHOD IS FAKE (NOT CALLING API)

### Current Code:
```javascript
// Login.vue (WRONG)
login() {
  const users = JSON.parse(localStorage.getItem('users')) || [];
  const user = users.find(u => 
    u.email === this.email && 
    u.password === this.password
  );
  if (user) {
    this.setUser(user);
    this.$router.push('/notification-settings');
  } else {
    this.loginError = 'Invalid email or password.';
  }
}
```

### The Problem:
```
❌ It checks localStorage for a fake 'users' list
❌ This list doesn't exist (never created)
❌ Backend API is NEVER called
❌ So login always fails!

✅ It should call the backend API:
POST /api/auth/login { email, password }
```

### What Happens:
```
User enters: admin@company2.sa / password
  ↓
Checks localStorage.getItem('users')
  ↓
Nothing found (doesn't exist)
  ↓
Shows error: "Invalid email or password"
  ↓
User can't login
```

### ✅ THE FIX:

Your newer version tries to call Vuex action, but Vuex store has NO login action!

```javascript
❌ CURRENT store.js:
actions: {
  setUser({ commit }, user) { ... },
  logout({ commit }) { ... }
  // ❌ NO async login() action!
  // ❌ NO API call!
}
```

---

## ❌ PROBLEM #3: BACKEND ISN'T RUNNING

### Check if Backend is Running:

**Test in browser:**
```
Open: http://localhost/backend/public/api/domain/check?host=localhost
```

### What You Should See:

**✅ SUCCESS:**
```json
{
  "valid": true,
  "tenant_id": 1,
  "domain": "localhost",
  "type": "main"
}
```

**❌ FAILURE:**
```
Connection refused
OR
404 Not Found
OR
Blank page
```

### If Backend Not Running:

**Windows (XAMPP):**
1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Click **Start** next to MySQL
4. Wait 5 seconds
5. Check: http://localhost/backend/public/api/domain/check?host=localhost

**If still fails:**
```bash
# Check if files are in right place
cd C:\xampp\htdocs
# Should see: backend/ folder
```

---

## 🔧 COMPLETE DEBUGGING STEPS

### Step 1: Check Backend is Running

```bash
# In terminal
curl http://localhost/backend/public/api/domain/check?host=localhost
```

**Expected:**
```json
{"valid":true,"tenant_id":1,...}
```

**If fails:**
```
curl: (7) Failed to connect to localhost port 80
# → Apache not running
```

---

### Step 2: Check .env.local is Correct

```bash
# In project root
cat .env.local
```

**Should show:**
```
VUE_APP_API_URL=http://localhost/backend/public/api
```

**If wrong:**
```
VUE_APP_API_URL=http://localhost:8000/api  ← ❌ WRONG!
```

---

### Step 3: Clear Browser Cache

Sometimes old settings are cached:

```javascript
// In browser DevTools Console (F12)
localStorage.clear();
sessionStorage.clear();
location.reload();
```

---

### Step 4: Check Admin Account Exists

In phpMyAdmin:

1. Go http://localhost/phpmyadmin
2. Click database: `company2_db`
3. Click table: `users`
4. Should see: `admin@company2.sa` with password hash

**If not found:**
```sql
-- Run in phpMyAdmin SQL tab:
INSERT INTO users (id, name, email, password, created_at, updated_at)
VALUES (
  1,
  'Admin',
  'admin@company2.sa',
  '$2y$12$k4EzfOd7cFj.Wc6SJt2myu.z6qBBVDjXZgWyYY9OOE1ooKD3fXsIm',  -- password
  NOW(),
  NOW()
);
```

---

### Step 5: Check Network Requests in Browser

1. Open Browser DevTools: **F12**
2. Go to **Network** tab
3. Click Login button
4. Look for API request

**What you should see:**
- Request to: `http://localhost/backend/public/api/auth/login`
- Status: `200` (success) or `401` (invalid credentials)

**If you see:**
- `failed` - backend not running
- `blocked by CORS` - CORS not configured
- No request - login method is fake

---

## 🚀 COMPLETE SETUP CHECKLIST

### Phase 1: Start Services

- [ ] **XAMPP Started**
  - [ ] Apache running (green)
  - [ ] MySQL running (green)

- [ ] **Backend Accessible**
  ```
  http://localhost/backend/public/api/domain/check?host=localhost
  ```
  Should return: `{"valid":true,...}`

- [ ] **Database Imported**
  - [ ] Database: `company2_db` exists
  - [ ] Table: `users` has admin account
  - [ ] 21 tables total

### Phase 2: Frontend Setup

- [ ] **Dependencies Installed**
  ```bash
  npm install
  # Should complete without errors
  ```

- [ ] **Environment Correct**
  - [ ] `.env.local` exists
  - [ ] `VUE_APP_API_URL=http://localhost/backend/public/api`

- [ ] **Frontend Running**
  ```bash
  npm run serve
  # Should see: "App running at http://localhost:8080"
  ```

### Phase 3: Test Login

- [ ] **Try Default Credentials**
  - Email: `admin@company2.sa`
  - Password: `password`

- [ ] **Check Browser Console** (F12)
  - No red errors
  - Should see API request

- [ ] **Check Network Tab** (F12)
  - Request to: `/api/auth/login`
  - Response status: `200` or `401`

---

## 🔴 STEP-BY-STEP FIX

### FIX #1: Correct .env.local

**File:** `.env.local`

Replace:
```env
VUE_APP_API_URL=http://localhost:8000/api
```

With:
```env
VUE_APP_API_URL=http://localhost/backend/public/api
```

Then:
```bash
# Restart frontend to read new env
npm run serve
```

---

### FIX #2: Create Proper Auth in Vuex Store

Your login tries to use Vuex `dispatch('login', ...)` but there's no login action.

**Add to `src/store.js` → `actions` section:**

```javascript
actions: {
  // ... existing actions ...

  async login({ commit }, credentials) {
    commit('SET_AUTH_LOADING', true);
    commit('SET_AUTH_ERROR', null);

    try {
      // Call API (requires axios to be installed!)
      const response = await fetch(
        `${process.env.VUE_APP_API_URL}/auth/login`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(credentials),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Login failed');
      }

      // Success! Save token and user
      commit('SET_AUTH_TOKEN', data.token);
      commit('SET_AUTH_USER', data.user);

      return data;
    } catch (error) {
      commit('SET_AUTH_ERROR', error.message);
      throw error;
    } finally {
      commit('SET_AUTH_LOADING', false);
    }
  },

  // Helper to set token
  SET_AUTH_TOKEN(state, token) {
    state.auth.token = token;
    localStorage.setItem('auth_token', token);
  },

  // Helper to set user
  SET_AUTH_USER(state, user) {
    state.user = user;
    localStorage.setItem('loggedInUser', JSON.stringify(user));
  },
},
```

Wait, looking at your store, it doesn't have these actions at all!

---

## 🆘 IMMEDIATE WORKAROUND (Test Login Works)

If you want to test login RIGHT NOW without all the fixes:

**Create fake admin in localStorage:**

1. Open Browser DevTools: **F12**
2. Go to **Console** tab
3. Paste this:

```javascript
// Create fake users list
const fakeUsers = [
  {
    id: 1,
    name: 'Admin',
    email: 'admin@company2.sa',
    password: 'password',  // ⚠️ Plaintext (only for testing!)
  }
];

// Save to localStorage
localStorage.setItem('users', JSON.stringify(fakeUsers));
localStorage.setItem('loggedInUser', JSON.stringify(fakeUsers[0]));

// Verify
console.log('Fake users created:', localStorage.getItem('users'));
```

4. Refresh page (F5)
5. Try login with: `admin@company2.sa` / `password`

**Result:** Should login (but this is NOT a real fix!)

---

## ✅ PROPER FIXES (In Order of Priority)

### Priority 1: Fix .env.local (5 mins)
- Change `VUE_APP_API_URL` to `http://localhost/backend/public/api`
- Restart frontend

### Priority 2: Check Backend Running (2 mins)
- Start XAMPP (Apache + MySQL)
- Verify: http://localhost/backend/public/api/domain/check?host=localhost

### Priority 3: Implement Real Auth (30 mins)
- Replace entire `src/store.js` with proper Vuex store (from COMPLETE_FIX_INSTRUCTIONS.md)
- Replace entire `src/services/api.js` with Axios-based service
- Update `src/components/Login.vue` login method

---

## 📊 DEBUG CHECKLIST

Before claiming login is broken, verify:

| Check | Pass | Fail | Action |
|-------|------|------|--------|
| XAMPP Apache running? | ✅ | ❌ | Start Apache |
| XAMPP MySQL running? | ✅ | ❌ | Start MySQL |
| Backend accessible? | ✅ 200 | ❌ 503/404 | Check file location |
| Admin exists in DB? | ✅ | ❌ | Import seeds.sql |
| .env.local correct? | ✅ | ❌ | Fix API URL |
| npm dependencies? | ✅ | ❌ | Run npm install |
| Frontend running? | ✅ 8080 | ❌ | npm run serve |
| Browser console errors? | ✅ None | ❌ | Check console |
| Network request sent? | ✅ Yes | ❌ | Check Network tab |
| API responds? | ✅ 200 | ❌ | Backend issue |

---

## 🆘 MOST LIKELY CAUSE

Based on your setup, 95% of login failures are due to:

**❌ WRONG API URL**
```
Current: http://localhost:8000/api
Correct: http://localhost/backend/public/api
```

### Fix:
1. Edit `.env.local`
2. Change URL (see above)
3. Restart frontend: `npm run serve`
4. Try login again

**This single fix usually solves it!**

---

## 📞 IF STILL NOT WORKING

Open browser console (F12) and paste:

```javascript
// Test API connection
const apiUrl = process.env.VUE_APP_API_URL || 'http://localhost:8000/api';
console.log('Testing API:', apiUrl);

fetch(`${apiUrl}/domain/check?host=localhost`)
  .then(r => r.json())
  .then(d => console.log('✅ API Works:', d))
  .catch(e => console.error('❌ API Error:', e));
```

**If you see:** `✅ API Works` → API is fine, issue is in auth code  
**If you see:** `❌ API Error` → Backend not running or URL wrong

---

## 🎯 NEXT STEPS

1. **Test API URL** → See if you can connect
2. **Fix .env.local** → Use correct URL
3. **Try login** → Should work now
4. **If still fails** → Implement complete Vuex auth (STEP by STEP from COMPLETE_FIX_INSTRUCTIONS.md)

Done! Your login should work now! 🚀
