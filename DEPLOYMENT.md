# Deployment Guide

This guide will walk you through deploying the Thumbworx application to production using the recommended stack.

## Architecture Overview

- **Flask API** → Render (AI/ML services, Traccar integration)
- **Laravel Backend** → Railway (Main API, database interactions)
- **Next.js Frontend** → Vercel (User interface)
- **PostgreSQL Database** → Railway (Managed database)
- **Redis Cache** → Railway (Managed cache)

## Prerequisites

1. GitHub account with your code pushed
2. Accounts on:
   - [Render.com](https://render.com)
   - [Railway.app](https://railway.app)
   - [Vercel.com](https://vercel.app)

## Step 1: Deploy Flask to Render

### 1.1 Push Flask Code
Ensure your `ai-flask/` directory is committed to GitHub.

### 1.2 Deploy on Render
1. Go to [render.com](https://render.com) → Create account → **New Web Service**
2. Connect your GitHub repository
3. Select the repository containing your Flask app
4. Configure:
   - **Name**: `thumbworx-flask-api`
   - **Region**: Choose closest to your users
   - **Branch**: `main`
   - **Root Directory**: `ai-flask`
   - **Runtime**: `Python 3`
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `gunicorn app:app -b 0.0.0.0:$PORT`

### 1.3 Set Environment Variables
In Render dashboard, add these environment variables:
```
TRACCAR_BASE_URL=https://your-traccar-server.com
TRACCAR_USER=your-traccar-username
TRACCAR_PASS=your-traccar-password
DATABASE_URL=postgresql://user:password@host:port/database  # Will be provided by Railway
REDIS_URL=redis://user:password@host:port/database          # Will be provided by Railway
```

### 1.4 Deploy
Click **Create Web Service**. Note your Flask app URL (e.g., `https://thumbworx-flask-api.onrender.com`)

## Step 2: Deploy Laravel to Railway

### 2.1 Deploy on Railway
1. Go to [railway.app](https://railway.app) → **New Project** → **Deploy from GitHub**
2. Select your repository
3. Railway will auto-detect PHP/Laravel

### 2.2 Add PostgreSQL Database
1. In your Railway project → **Add Service** → **Database** → **PostgreSQL**
2. Railway will automatically set `DATABASE_URL` environment variable

### 2.3 Add Redis (Optional)
1. **Add Service** → **Database** → **Redis**
2. Railway will automatically set `REDIS_URL` environment variable

### 2.4 Set Environment Variables
In Railway dashboard, add these variables:
```
APP_NAME=Thumbworx
APP_ENV=production
APP_KEY=                              # Generate with: php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app
DB_CONNECTION=pgsql
CORS_ALLOWED_ORIGINS=https://your-nextjs-app.vercel.app  # Will be updated after Vercel deployment
TRACCAR_BASE_URL=https://your-traccar-server.com
TRACCAR_USER=your-traccar-username
TRACCAR_PASS=your-traccar-password
FLASK_API_URL=https://thumbworx-flask-api.onrender.com   # Your Flask app URL from Step 1
```

### 2.5 Generate APP_KEY
Run locally to generate an app key:
```bash
cd backend-laravel
php artisan key:generate --show
```
Copy the output and set it as `APP_KEY` in Railway.

### 2.6 Run Migrations
In Railway Console (or use the web terminal):
```bash
php artisan migrate --force
```

### 2.7 Deploy
Note your Laravel app URL (e.g., `https://thumbworx-backend.up.railway.app`)

## Step 3: Deploy Next.js to Vercel

### 3.1 Deploy on Vercel
1. Go to [vercel.com](https://vercel.com) → **New Project**
2. Import your GitHub repository
3. Select the `frontend-next` directory as the root
4. Vercel will auto-detect Next.js

### 3.2 Set Environment Variables
In Vercel dashboard, add:
```
NEXT_PUBLIC_API_URL=https://thumbworx-backend.up.railway.app  # Your Laravel app URL from Step 2
```

### 3.3 Deploy
Click **Deploy**. Note your Next.js app URL (e.g., `https://thumbworx.vercel.app`)

## Step 4: Update Cross-Service Configuration

### 4.1 Update Laravel CORS
In Railway, update the `CORS_ALLOWED_ORIGINS` environment variable:
```
CORS_ALLOWED_ORIGINS=https://thumbworx.vercel.app  # Your actual Vercel URL
```

### 4.2 Update Flask Database URLs
In Render, update the Flask environment variables with the actual Railway database URLs:
- Copy `DATABASE_URL` from Railway PostgreSQL service
- Copy `REDIS_URL` from Railway Redis service (if added)

## Step 5: Test the Deployment

### 5.1 Test API Endpoints
```bash
# Test Flask health
curl https://thumbworx-flask-api.onrender.com/health

# Test Laravel API
curl https://thumbworx-backend.up.railway.app/api/health

# Test Next.js frontend
curl https://thumbworx.vercel.app
```

### 5.2 Test Integration
1. Open your Next.js app in a browser
2. Check browser console for any CORS errors
3. Test API calls from the frontend
4. Verify data flows: Frontend → Laravel → Flask → Traccar

## Troubleshooting

### Common Issues

1. **CORS Errors**
   - Ensure `CORS_ALLOWED_ORIGINS` in Laravel includes your Vercel URL
   - Check that Flask has `flask-cors` enabled

2. **Database Connection Errors**
   - Verify `DATABASE_URL` is correctly set in both Flask and Laravel
   - Ensure Railway PostgreSQL service is running

3. **Build Failures**
   - Check build logs in each platform's dashboard
   - Ensure all dependencies are listed in requirements.txt/package.json/composer.json

4. **Environment Variables**
   - Double-check all env vars are set correctly
   - Restart services after changing environment variables

### Monitoring

- Use Railway logs for Laravel debugging
- Use Render logs for Flask debugging
- Use Vercel function logs for Next.js debugging

## Production Considerations

1. **Security**
   - Use strong passwords for Traccar
   - Enable HTTPS for all services
   - Regularly update dependencies

2. **Performance**
   - Monitor Redis cache hit rates
   - Set up database connection pooling
   - Enable CDN for static assets

3. **Monitoring**
   - Set up health checks
   - Monitor API response times
   - Set up alerts for service failures

4. **Backups**
   - Enable Railway database backups
   - Regular data exports for critical data

## URLs Summary

After deployment, you'll have:
- **Flask API**: `https://thumbworx-flask-api.onrender.com`
- **Laravel Backend**: `https://thumbworx-backend.up.railway.app`
- **Next.js Frontend**: `https://thumbworx.vercel.app`
- **PostgreSQL**: Railway managed (internal URL)
- **Redis**: Railway managed (internal URL)
