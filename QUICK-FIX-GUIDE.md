# 🔧 Quick Fix: Authentication Endpoint 500 Error

## ✅ What Was Fixed

The login endpoint `/api/auth/test-customer` was returning **500 Internal Server Error** because it tried to create a User with an invalid 'role' field.

### Root Cause
```php
// ❌ BROKEN - route tried this:
$user = User::firstOrCreate(
    ['email' => $email],
    ['name' => $name, 'role' => $role, 'password' => Hash::make('password123')]
);
// ERROR: 'role' is not in User model's $fillable array
```

### What Changed
```php
// ✅ FIXED - now does this:
$user = User::firstOrCreate(
    ['email' => $email],
    ['name' => $name, 'password' => Hash::make('password123')]
);

if (!$user->hasRole($role)) {
    $user->syncRoles([$role]);
}
```

---

## 📝 Deployment Instructions

### For VPS (Production)
**SSH into your VPS and run:**
```bash
cd /root/wk-crm-microservices

# Pull the latest code
git pull origin main

# Clear cache and rebuild
docker compose down
docker compose build --no-cache
docker compose up -d

# Verify the fix
curl "https://api.consultoriawk.com/api/auth/test-customer?role=admin"
```

### For Local Development
```bash
# Clear Laravel cache
php artisan cache:clear
php artisan route:clear

# Restart your local server
php artisan serve

# Test the endpoint
curl "http://localhost:8000/api/auth/test-customer?role=admin"
```

---

## ✔️ Verification

After deployment, test these endpoints:

**Admin User:**
```bash
curl "https://api.consultoriawk.com/api/auth/test-customer?role=admin"
```

Expected Response (200 OK):
```json
{
  "success": true,
  "user": {
    "id": "...",
    "name": "Admin WK",
    "email": "admin-test@wkcrm.local"
  },
  "token": "..."
}
```

**Customer User:**
```bash
curl "https://api.consultoriawk.com/api/auth/test-customer?role=customer"
```

Expected Response (200 OK):
```json
{
  "success": true,
  "user": {
    "id": "...",
    "name": "Customer Test",
    "email": "customer-test@wkcrm.local"
  },
  "token": "..."
}
```

---

## 🧪 Frontend Testing

After deployment, login at: `https://app.consultoriawk.com/login`

- Click **"Entrar como ADMIN"** button → Should authenticate and redirect to dashboard
- Click **"Entrar como CLIENTE"** button → Should authenticate and redirect to dashboard

---

## 📊 Files Changed

| File | Change |
|------|--------|
| `wk-crm-laravel/routes/api.php` | Fixed test-customer endpoint to use assignRole() instead of direct role field |

**Commit:** `71b08cd`  
**Branch:** `main`  
**Status:** ✅ Pushed to GitHub

---

## 🐛 Technical Details

**Why This Happened:**
- Spatie Permission trait manages roles separately from User attributes
- The User model's `$fillable` array controls mass assignment
- Trying to set 'role' directly caused a MassAssignmentException

**Why This Fixes It:**
- Creates user with only valid fillable attributes
- Uses proper Spatie Permission API (`syncRoles()`)
- Checks if role already exists to avoid duplicates

---

## 🆘 If Issues Persist

1. **Check Docker logs:**
   ```bash
   docker logs wk_crm_laravel --tail 100
   ```

2. **Clear all caches:**
   ```bash
   docker exec wk_crm_laravel php artisan cache:clear
   docker exec wk_crm_laravel php artisan config:cache
   docker exec wk_crm_laravel php artisan route:clear
   ```

3. **Check if Spatie Permission is installed:**
   ```bash
   docker exec wk_crm_laravel composer list | grep spatie
   ```

4. **View recent git changes:**
   ```bash
   git log --oneline -5
   git show 71b08cd
   ```
