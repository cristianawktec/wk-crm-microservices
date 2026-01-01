# ✅ Deployment Checklist

## Pre-Deployment Verification

- [x] Code fix applied to `routes/api.php`
- [x] All changes committed to git
- [x] Commits pushed to GitHub origin/main
- [x] Documentation created and committed
- [ ] VPS deployment scheduled

---

## VPS Deployment Steps

```bash
# Step 1: Connect to VPS
ssh root@72.60.254.100

# Step 2: Navigate to project directory
cd /root/wk-crm-microservices

# Step 3: Pull latest code from GitHub
git pull origin main

# Step 4: Stop running containers
docker compose down

# Step 5: Rebuild Docker image with no cache
docker compose build --no-cache

# Step 6: Start containers
docker compose up -d

# Step 7: Wait for containers to be ready
sleep 5

# Step 8: Clear Laravel cache
docker exec wk_crm_laravel php artisan cache:clear
docker exec wk_crm_laravel php artisan route:clear
```

### Estimated Time: 5-10 minutes

---

## Post-Deployment Verification

### ✅ Test 1: Admin Login Endpoint
```bash
curl -X GET "https://api.consultoriawk.com/api/auth/test-customer?role=admin" \
  -H "Content-Type: application/json"
```

**Expected Response:**
- Status: `200 OK`
- Body contains: `"success": true`
- Body contains: `"token": "..."`
- Body contains: `"name": "Admin WK"`

### ✅ Test 2: Customer Login Endpoint
```bash
curl -X GET "https://api.consultoriawk.com/api/auth/test-customer?role=customer" \
  -H "Content-Type: application/json"
```

**Expected Response:**
- Status: `200 OK`
- Body contains: `"success": true`
- Body contains: `"token": "..."`
- Body contains: `"name": "Customer Test"`

### ✅ Test 3: Frontend Login Page
1. Open browser: `https://app.consultoriawk.com/login`
2. Click **"Entrar como ADMIN"** button
3. **Expected:** Dashboard loads successfully
4. **Check:** Profile shows "Admin WK" name

### ✅ Test 4: Customer Frontend Login
1. Navigate to: `https://app.consultoriawk.com/login`
2. Click **"Entrar como CLIENTE"** button
3. **Expected:** Dashboard loads successfully
4. **Check:** Profile shows "Customer Test" name

---

## Troubleshooting

### If Endpoint Returns 500 Error

1. **Check Docker logs:**
   ```bash
   docker logs wk_crm_laravel --tail 50
   ```

2. **Check if deployment pulled correctly:**
   ```bash
   cd /root/wk-crm-microservices
   git log --oneline -3
   # Should show commits: 558371d, c2a035d, 71b08cd
   ```

3. **Verify Spatie Permission is installed:**
   ```bash
   docker exec wk_crm_laravel composer show | grep spatie
   ```

4. **Clear all caches:**
   ```bash
   docker exec wk_crm_laravel php artisan cache:clear
   docker exec wk_crm_laravel php artisan config:cache
   docker exec wk_crm_laravel php artisan route:clear
   ```

### If Frontend Still Shows Error

1. **Check browser console for API errors**
2. **Verify API base URL is correct:** Should be `https://api.consultoriawk.com`
3. **Check CORS settings** in Laravel config
4. **Clear browser cache:** Ctrl+Shift+Delete

### If Docker Build Fails

```bash
# Try with more details
docker compose build --no-cache --progress=plain wk_crm_laravel

# If that fails, check disk space
df -h

# If still fails, try stopping other containers
docker stop $(docker ps -q)
docker compose up -d
```

---

## Success Indicators

✅ All tests pass  
✅ Frontend login works  
✅ API endpoint returns 200 OK with token  
✅ No 500 errors in Docker logs  
✅ User profile shows correct role

---

## Rollback Plan (If Needed)

If something goes wrong after deployment:

```bash
# Revert to previous commit
cd /root/wk-crm-microservices
git checkout 015b943

# Rebuild with old code
docker compose down
docker compose build --no-cache
docker compose up -d

# Clear caches
docker exec wk_crm_laravel php artisan cache:clear
```

---

## Sign-Off

- [ ] Deployment completed successfully
- [ ] All tests passed
- [ ] No errors in logs
- [ ] Frontend login working
- [ ] Time: _____________
- [ ] Deployed by: _________________

---

**Need Help?** Refer to the documentation files:
- `QUICK-FIX-GUIDE.md` - Quick reference
- `AUTH-FIX-DEPLOYMENT.md` - Technical details
- `FIX-STATUS-REPORT.md` - Full status report
