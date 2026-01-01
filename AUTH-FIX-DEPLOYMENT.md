# Authentication Endpoint Fix - Deployment Guide

## Problem Identified
The `/api/auth/test-customer` endpoint was returning **500 Internal Server Error** on the VPS.

### Root Cause
In `routes/api.php`, the test-customer route was attempting to create a User with a `role` field directly:
```php
$user = User::firstOrCreate(
    ['email' => $email],
    [
        'name' => $name,
        'role' => $role,  // ❌ 'role' is NOT in User model's $fillable array
        'password' => Hash::make('password123')
    ]
);
```

The User model (app/Models/User.php) only has these fillable fields:
```php
protected $fillable = [
    'id',
    'name', 
    'email',
    'password',
];
```

This caused a **MassAssignmentException** when Laravel tried to assign the 'role' attribute.

## Solution Applied
**Commit Hash:** `71b08cd`

Changed the route to:
1. Create the user WITHOUT the 'role' field
2. Use the proper `assignRole()` method from Spatie Permission trait
3. Check if user already has the role to avoid duplicates

### Code Change
```php
Route::get('/auth/test-customer', function () {
    $role = request()->query('role', 'customer');
    $email = $role === 'admin' ? 'admin-test@wkcrm.local' : 'customer-test@wkcrm.local';
    $name = $role === 'admin' ? 'Admin WK' : 'Customer Test';
    
    $user = User::firstOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => Hash::make('password123')
        ]
    );
    
    // ✅ Assign role if user was just created or doesn't have the role
    if (!$user->hasRole($role)) {
        $user->syncRoles([$role]);
    }
    
    // ... rest of the code
});
```

## Files Modified
- `wk-crm-laravel/routes/api.php` (lines 59-74)

## Deployment Steps

### Option 1: Manual SSH Deployment (Recommended if SSH is working)
```bash
ssh root@72.60.254.100
cd /root/wk-crm-microservices
git pull origin main
docker compose down
docker compose build --no-cache
docker compose up -d

# Verify the fix
curl https://api.consultoriawk.com/api/auth/test-customer?role=admin
```

### Option 2: Local Testing First (Development)
```bash
cd wk-crm-laravel
php artisan migrate  # If needed
php artisan route:clear
php artisan cache:clear

# Test locally at http://localhost:8000/api/auth/test-customer?role=admin
```

## Expected Result After Fix
✅ GET `/api/auth/test-customer?role=admin` should return:
```json
{
  "success": true,
  "user": {
    "id": "uuid",
    "name": "Admin WK",
    "email": "admin-test@wkcrm.local"
  },
  "token": "jwt-token-here"
}
```

✅ Frontend login at `app.consultoriawk.com/login` should work:
- Admin button: Creates/retrieves admin user and logs in
- Customer button: Creates/retrieves customer user and logs in

## Testing Checklist
After deployment:
- [ ] Test `/api/auth/test-customer?role=admin` returns 200 + token
- [ ] Test `/api/auth/test-customer?role=customer` returns 200 + token
- [ ] Login page shows both admin and customer quick-login buttons work
- [ ] User is redirected to dashboard after successful login
- [ ] Profile shows correct role (admin or customer)

## Git Status
```
Branch: main
Latest Commit: 71b08cd (Fix: Correcting test-customer endpoint...)
Pushed to: origin/main ✓
```

## Notes
- The fix uses Spatie Permission's `syncRoles()` which is safer than `assignRole()` for bulk operations
- The `hasRole()` check prevents reassigning the same role
- No database migration needed - just code change in the route
- All other authentication endpoints (login, me, logout, etc.) remain unchanged
