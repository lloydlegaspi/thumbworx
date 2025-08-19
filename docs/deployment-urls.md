# Deployment URLs and Credentials

This file tracks live deployment URLs and demo credentials for the Thumbworx system.

## Live Demo URLs

### Production Deployment

| Service | Platform | URL | Status |
|---------|----------|-----|--------|
| Frontend | Vercel | `https://thumbworx.vercel.app` | ✅ Live |
| Laravel API | Railway | `https://thumbworx-production.up.railway.app` | ✅ Live |
| Flask API | Render | `https://thumbworx.onrender.com` | ✅ Live |
| Database | Railway | `postgresql://...` | ✅ Live |
| Redis | Railway | `redis://...` | 🟡 Pending |

### Staging Environment

| Service | Platform | URL | Status |
|---------|----------|-----|--------|
| Frontend | Vercel | `https://thumbworx-staging.vercel.app` | 🟡 Pending |
| Laravel API | Railway | `https://thumbworx-backend-staging.up.railway.app` | 🟡 Pending |
| Flask API | Render | `https://thumbworx-flask-staging.onrender.com` | 🟡 Pending |

## Demo Credentials

### Traccar Demo Server
- **URL**: `https://demo.traccar.org`
- **Username**: `demo_user`
- **Password**: `demo_pass`
- **Note**: Public demo server with sample devices (configure in environment variables)

### Database Access (Development)
- **Host**: `localhost` (via docker-compose)
- **Port**: `5432`
- **Database**: `thumbworx`
- **Username**: `thumb_user`
- **Password**: `thumb_pass`

### Redis Access (Development)
- **Host**: `localhost` (via docker-compose)
- **Port**: `6379`
- **Database**: `0`
- **Password**: None

## API Endpoints Reference

### Production Endpoints

**Frontend**
```
GET https://thumbworx.vercel.app
```

**Laravel Backend**
```
GET https://thumbworx-production.up.railway.app/api/traccar/devices
GET https://thumbworx-production.up.railway.app/api/traccar/positions
```

**Flask Microservice**
```
GET https://thumbworx.onrender.com/api/traccar/devices
GET https://thumbworx.onrender.com/api/traccar/positions
GET https://thumbworx.onrender.com/api/positions_cached
POST https://thumbworx.onrender.com/api/predict_eta
```

### Local Development Endpoints

**Frontend**
```
GET http://localhost:3000
```

**Laravel Backend**
```
GET http://localhost:8000/api/traccar/devices
GET http://localhost:8000/api/traccar/positions
```

**Flask Microservice**
```
GET http://localhost:5000/api/traccar/devices
GET http://localhost:5000/api/traccar/positions
GET http://localhost:5000/api/positions_cached
POST http://localhost:5000/api/predict_eta
```

## Sample API Requests

### Get Devices
```bash
curl -X GET "https://thumbworx-backend.up.railway.app/api/traccar/devices"
```

### Get Positions
```bash
curl -X GET "https://thumbworx-backend.up.railway.app/api/traccar/positions"
```

### Predict ETA
```bash
curl -X POST "https://thumbworx-flask.onrender.com/api/predict_eta" \
  -H "Content-Type: application/json" \
  -d '{
    "current_lat": 14.5995,
    "current_lng": 120.9842,
    "dropoff_lat": 14.6042,
    "dropoff_lng": 120.9822
  }'
```

## Deployment Status

### ✅ Completed
- [x] Docker Compose configuration
- [x] Flask microservice implementation
- [x] Laravel backend implementation
- [x] Next.js frontend implementation
- [x] Database migrations
- [x] API endpoints
- [x] Documentation

### 🟡 In Progress
- [ ] Production deployment to Render (Flask)
- [ ] Production deployment to Railway (Laravel)
- [ ] Production deployment to Vercel (Next.js)
- [ ] Database setup on Railway
- [ ] Redis setup on Railway
- [ ] Environment variables configuration
- [ ] SSL certificate setup
- [ ] Domain configuration

### 📋 Pending
- [ ] Performance testing
- [ ] Security audit
- [ ] Monitoring setup
- [ ] Backup configuration
- [ ] CI/CD pipeline
- [ ] Load testing
- [ ] Documentation review

## Environment Variables Checklist

### Flask (Render)
- [ ] `DATABASE_URL`
- [ ] `REDIS_URL`
- [ ] `TRACCAR_BASE_URL`
- [ ] `TRACCAR_USER`
- [ ] `TRACCAR_PASS`

### Laravel (Railway)
- [ ] `APP_KEY`
- [ ] `DB_HOST`
- [ ] `DB_DATABASE`
- [ ] `DB_USERNAME`
- [ ] `DB_PASSWORD`
- [ ] `TRACCAR_BASE_URL`
- [ ] `TRACCAR_USER`
- [ ] `TRACCAR_PASS`

### Next.js (Vercel)
- [ ] `NEXT_PUBLIC_API_URL`

## Testing Endpoints

Use these commands to test deployed services:

### Health Checks
```bash
# Flask health
curl -f https://thumbworx-flask.onrender.com/api/traccar/devices || echo "Flask service down"

# Laravel health
curl -f https://thumbworx-backend.up.railway.app/api/traccar/devices || echo "Laravel service down"

# Frontend health
curl -f https://thumbworx.vercel.app || echo "Frontend down"
```

### Performance Tests
```bash
# Response time test
time curl -s https://thumbworx-backend.up.railway.app/api/traccar/positions > /dev/null

# Load test (requires siege)
siege -c 10 -t 30s https://thumbworx-backend.up.railway.app/api/traccar/positions
```

## Monitoring

### Platform Dashboards
- **Render**: https://dashboard.render.com
- **Railway**: https://railway.app/dashboard
- **Vercel**: https://vercel.com/dashboard

### Application Metrics
- Response times
- Error rates
- Database connections
- Memory usage
- CPU usage

### Alerts
- Service downtime
- High error rates
- Database connection issues
- Memory/CPU spikes

## Backup Strategy

### Database Backups
- Daily automated backups via Railway
- Weekly full database exports
- Point-in-time recovery available

### Code Backups
- Git repository (primary)
- Platform-specific backups
- Configuration backups

## Security Notes

### Production Security
- HTTPS enforced on all services
- Environment variables secured
- Database connections encrypted
- Regular security updates

### Access Control
- Platform-specific access controls
- Database user permissions
- API rate limiting
- CORS configuration

## Support Information

### Platform Support
- **Render**: Support via dashboard
- **Railway**: Discord community
- **Vercel**: Support via dashboard

### Application Support
- GitHub Issues: Create issues for bugs
- Documentation: Check docs/ folder
- Logs: Available via platform dashboards

## Changelog

| Date | Change | Deployer |
|------|--------|----------|
| TBD | Initial deployment setup | Developer |
| TBD | Flask service deployed to Render | Developer |
| TBD | Laravel service deployed to Railway | Developer |
| TBD | Frontend deployed to Vercel | Developer |
| TBD | Database configured on Railway | Developer |
| TBD | Redis configured on Railway | Developer |

---

**Note**: Update this file with actual URLs and credentials after deployment. Keep sensitive information secure and use environment variables for production credentials.
