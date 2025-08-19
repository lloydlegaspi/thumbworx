# Deployment Guide

This guide covers deploying the Thumbworx system to various cloud platforms.

## Quick Deployment Overview

| Component | Recommended Platform | Alternative |
|-----------|---------------------|-------------|
| Flask API | Render | Railway, Heroku |
| Laravel Backend | Railway | Heroku, AWS |
| Next.js Frontend | Vercel | Netlify, AWS |
| PostgreSQL | Railway | AWS RDS, DigitalOcean |
| Redis | Railway | AWS ElastiCache |

## 1. Deploy Flask to Render

### Prerequisites
- GitHub repository
- Render account

### Steps

1. **Push code to GitHub**
   ```bash
   git add .
   git commit -m "Initial commit"
   git push origin main
   ```

2. **Create Web Service on Render**
   - Go to [render.com](https://render.com)
   - Click "New" → "Web Service"
   - Connect your GitHub repository
   - Select the `ai-flask` directory

3. **Configure Build Settings**
   - **Root Directory**: `ai-flask`
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `gunicorn app:app -b 0.0.0.0:$PORT --workers=3`

4. **Set Environment Variables**
   ```
   DATABASE_URL=postgresql://user:pass@host:port/dbname
   REDIS_URL=redis://host:port/0
   TRACCAR_BASE_URL=https://demo.traccar.org
   TRACCAR_USER=your_username
   TRACCAR_PASS=your_password
   ```

5. **Deploy**
   - Click "Create Web Service"
   - Note the public URL (e.g., `https://thumbworx-flask.onrender.com`)

## 2. Deploy Laravel to Railway

### Prerequisites
- GitHub repository
- Railway account

### Steps

1. **Create Railway Project**
   - Go to [railway.app](https://railway.app)
   - Click "Start a New Project"
   - Select "Deploy from GitHub repo"
   - Connect your repository

2. **Configure Service**
   - Railway will auto-detect Laravel
   - Root directory: `backend-laravel`

3. **Add PostgreSQL Plugin**
   - In Railway dashboard, click "New"
   - Select "Database" → "PostgreSQL"
   - Note the connection details

4. **Set Environment Variables**
   ```
   APP_KEY=base64:your-generated-key
   DB_HOST=your-postgres-host
   DB_DATABASE=railway
   DB_USERNAME=postgres
   DB_PASSWORD=your-postgres-password
   TRACCAR_BASE_URL=https://demo.traccar.org
   TRACCAR_USER=your_username
   TRACCAR_PASS=your_password
   ```

5. **Generate APP_KEY**
   ```bash
   # Run locally
   php artisan key:generate --show
   ```

6. **Deploy & Migrate**
   - Push changes to trigger deployment
   - In Railway console, run: `php artisan migrate --force`

## 3. Deploy Next.js to Vercel

### Prerequisites
- GitHub repository
- Vercel account

### Steps

1. **Create Vercel Project**
   - Go to [vercel.com](https://vercel.com)
   - Click "New Project"
   - Import your GitHub repository

2. **Configure Build Settings**
   - **Root Directory**: `frontend-next`
   - Vercel auto-detects Next.js settings

3. **Set Environment Variables**
   ```
   NEXT_PUBLIC_API_URL=https://your-laravel-url.railway.app
   ```

4. **Deploy**
   - Click "Deploy"
   - Note the public URL (e.g., `https://thumbworx.vercel.app`)

## 4. Alternative: Deploy All to Railway

You can deploy all services to Railway for simplicity:

### Steps

1. **Create Railway Project**
2. **Add Services**:
   - Flask service (`ai-flask` directory)
   - Laravel service (`backend-laravel` directory)
   - Next.js service (`frontend-next` directory)
   - PostgreSQL database
   - Redis database

3. **Configure Environment Variables** for each service
4. **Connect Services** using Railway's internal networking

## Environment Variables Reference

### Flask Service
```env
DATABASE_URL=postgresql://username:password@host:port/database
REDIS_URL=redis://host:port/0
TRACCAR_BASE_URL=https://demo.traccar.org
TRACCAR_USER=demo_user
TRACCAR_PASS=demo_password
```

### Laravel Service
```env
APP_NAME=Thumbworx
APP_ENV=production
APP_KEY=base64:your-key-here
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://your-laravel-url.com

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

TRACCAR_BASE_URL=https://demo.traccar.org
TRACCAR_USER=demo_user
TRACCAR_PASS=demo_password
```

### Next.js Service
```env
NEXT_PUBLIC_API_URL=https://your-laravel-backend-url.com
```

## Security Checklist

- [ ] Use HTTPS for all services
- [ ] Set strong database passwords
- [ ] Configure CORS properly
- [ ] Use environment variables for secrets
- [ ] Enable database SSL
- [ ] Set up monitoring and logging
- [ ] Configure rate limiting
- [ ] Regular security updates

## Post-Deployment Testing

1. **Test API Endpoints**
   ```bash
   # Test Flask
   curl https://your-flask-url.com/api/traccar/devices
   
   # Test Laravel
   curl https://your-laravel-url.com/api/traccar/positions
   ```

2. **Test Frontend**
   - Visit your Next.js URL
   - Verify map loads
   - Check live position updates

3. **Test Database Connection**
   - Check Laravel logs for database errors
   - Verify migrations ran successfully

## Monitoring and Maintenance

### Logging
- Enable application logging on all platforms
- Monitor error rates and performance
- Set up alerts for critical issues

### Database Maintenance
- Regular backups
- Monitor connection pools
- Optimize queries as needed

### Updates
- Keep dependencies updated
- Monitor security advisories
- Test updates in staging first

## Troubleshooting

### Common Issues

1. **CORS Errors**
   - Configure Laravel CORS middleware
   - Add frontend domain to allowed origins

2. **Database Connection Errors**
   - Verify connection strings
   - Check firewall rules
   - Ensure SSL configuration

3. **Environment Variable Issues**
   - Double-check variable names
   - Verify values are properly set
   - Restart services after changes

4. **Build Failures**
   - Check dependency versions
   - Verify build commands
   - Review platform-specific requirements
