@echo off
echo 🚀 Deploying CORS and API fixes to Railway...

:: Check if we're in the right directory
if not exist "artisan" (
    echo ❌ Please run this script from the backend-laravel directory
    exit /b 1
)

:: Clear Laravel caches
echo 🧹 Clearing Laravel caches...
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

:: Optimize for production
echo ⚡ Optimizing for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ✅ Fixes deployed! Test endpoints:
echo    - Health: https://thumbworx-production.up.railway.app/api/health
echo    - Debug: https://thumbworx-production.up.railway.app/api/debug
echo    - Devices: https://thumbworx-production.up.railway.app/api/traccar/devices
echo    - Positions: https://thumbworx-production.up.railway.app/api/traccar/positions-cached

echo.
echo 🔍 To check logs on Railway:
echo    railway logs --follow

echo.
echo 📋 Environment variables to check on Railway:
echo    - APP_ENV=production
echo    - APP_DEBUG=false
echo    - CORS_ALLOWED_ORIGINS=https://thumbworx.vercel.app
echo    - TRACCAR_BASE_URL=your-traccar-server
echo    - TRACCAR_USER=your-username
echo    - TRACCAR_PASS=your-password
echo    - FLASK_SERVICE_URL=https://thumbworx.onrender.com

pause
