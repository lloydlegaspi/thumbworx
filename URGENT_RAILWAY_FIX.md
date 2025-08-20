# URGENT: Railway Deployment Fix

## Problem Summary
Your Laravel backend on Railway is returning 404 for all API endpoints and has incorrect CORS origins.

## Step-by-Step Fix

### 1. Update Railway Environment Variables

In your Railway dashboard (https://railway.app):

1. Go to your Laravel project
2. Click on "Variables" tab
3. Update/Add these variables:

```
APP_NAME=Thumbworx
APP_ENV=production
APP_DEBUG=false
APP_URL=https://thumbworx-production.up.railway.app
APP_KEY=base64:YOUR_GENERATED_KEY_HERE

# CORS Configuration - THIS IS CRITICAL
CORS_ALLOWED_ORIGINS=https://thumbworx.vercel.app,https://thumbworx-git-main-john-lloyd-legaspis-projects.vercel.app,http://localhost:3000

# Database (Railway should auto-provide this)
DB_CONNECTION=pgsql
DATABASE_URL=${{Postgres.DATABASE_URL}}

# Traccar Configuration
TRACCAR_BASE_URL=https://demo.traccar.org
TRACCAR_USER=demo
TRACCAR_PASS=demo

# Flask Service
FLASK_SERVICE_URL=https://thumbworx.onrender.com
```

### 2. Generate and Set APP_KEY

Run this locally to generate a new app key:
```bash
cd backend-laravel
php artisan key:generate --show
```

Copy the output (something like `base64:ABC123...`) and set it as `APP_KEY` in Railway.

### 3. Force Redeploy

After updating environment variables:
1. Go to "Deployments" tab in Railway
2. Click on the latest deployment
3. Click "Redeploy" or make a small commit to trigger redeploy

### 4. Verify Deployment

After redeployment, test:
```bash
curl https://thumbworx-production.up.railway.app/api/health
```

Should return JSON instead of 404.

## Alternative: Quick Deploy Fix

If the above doesn't work, the issue might be with the Dockerfile. Create a `.dockerignore` file:

```
.env.local
.env.*.local
node_modules/
npm-debug.log*
.DS_Store
*.log
```

And update the startup command in Railway:
- Go to Settings → Start Command
- Set to: `bash startup.sh php artisan serve --host=0.0.0.0 --port=$PORT`

## Testing After Fix

Run the test script again:
```powershell
.\test_cors_fix.ps1
```

All endpoints should return success instead of 404.

## If Still Failing

Check Railway logs:
1. Go to Railway dashboard
2. Click on your Laravel service
3. Check "Logs" tab for errors
4. Look for PHP errors or routing issues

Common issues:
- APP_KEY not set (causes Laravel to fail)
- Database connection errors
- Missing .env variables
- Routing cache issues
