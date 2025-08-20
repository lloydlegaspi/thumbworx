## Railway Deployment Troubleshooting

### 1. Check Railway Logs
```bash
railway logs --follow
```

### 2. Check Required Environment Variables
Make sure these are set in Railway:

#### Essential Variables:
- `APP_KEY` - Laravel application key (generate with: `php artisan key:generate --show`)
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://thumbworx-production.up.railway.app`

#### Database Variables (Railway should provide these automatically):
- `DATABASE_URL` or individual DB variables
- `DB_CONNECTION=pgsql`

#### Service URLs:
- `TRACCAR_BASE_URL=https://your-traccar-server.com`
- `TRACCAR_USER=your-username` 
- `TRACCAR_PASS=your-password`
- `FLASK_SERVICE_URL=https://thumbworx.onrender.com`

### 3. Test Locally First
```bash
# In backend-laravel directory
php artisan serve --host=0.0.0.0 --port=8000

# Test endpoints:
# http://localhost:8000/api/debug
# http://localhost:8000/api/health
```

### 4. Check Railway Service Status
1. Go to Railway dashboard
2. Check if the service is "Running" or "Failed"
3. Look at the build logs for errors
4. Check if the deployment completed successfully

### 5. Common Railway Issues:

#### Missing APP_KEY:
```bash
railway run php artisan key:generate --show
# Copy the output and set as APP_KEY in Railway
```

#### Database Connection:
- Railway should automatically provide DATABASE_URL
- Make sure `config/database.php` is configured for PostgreSQL

#### Startup Script Permissions:
- Make sure `startup.sh` is executable
- Check if the startup script completes without errors

### 6. Quick Fix Commands:
```bash
# If you have Railway CLI installed:
railway login
railway environment
railway variables set APP_KEY=base64:your-generated-key-here
railway redeploy
```

### 7. Alternative Deployment Test:
Try deploying without our new changes to see if the basic app works, then gradually add our fixes back.
