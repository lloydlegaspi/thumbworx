# Thumbworx - Live Vehicle Tracking System

A comprehensive vehicle tracking system built with Flask microservice, Laravel backend, and Next.js frontend, featuring real-time position tracking, caching, and analytics.

## Architecture Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│                 │    │                 │    │                 │
│   Next.js       │◄───┤   Laravel       │◄───┤   Flask         │
│   Frontend      │    │   Backend       │    │   Microservice  │
│   (Port 3000)   │    │   (Port 8000)   │    │   (Port 5000)   │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │                       │
                                │                       │
                                ▼                       ▼
                    ┌─────────────────┐    ┌─────────────────┐
                    │                 │    │                 │
                    │   PostgreSQL    │    │     Redis       │
                    │   Database      │    │     Cache       │
                    │   (Port 5432)   │    │   (Port 6379)   │
                    │                 │    │                 │
                    └─────────────────┘    └─────────────────┘
                                ▲
                                │
                    ┌─────────────────┐
                    │                 │
                    │   Traccar       │
                    │   GPS Server    │
                    │  (External API) │
                    │                 │
                    └─────────────────┘
```

## Features

- **Real-time GPS tracking** - Live vehicle positions from Traccar
- **Caching layer** - Redis for fast position retrieval
- **Data persistence** - PostgreSQL for historical data
- **Live map interface** - Interactive map with auto-refresh
- **ETA prediction** - Distance-based travel time estimation
- **RESTful APIs** - Clean API endpoints for integration
- **Docker containerization** - Easy deployment and scaling

## Tech Stack

### Frontend
- **Next.js 15** - React framework with TypeScript
- **React Leaflet** - Interactive maps
- **SWR** - Data fetching with caching
- **Tailwind CSS** - Styling

### Backend
- **Laravel 11** - PHP framework for API orchestration
- **Flask** - Python microservice for GPS data processing
- **PostgreSQL** - Primary database
- **Redis** - Caching and session storage

### DevOps
- **Docker & Docker Compose** - Containerization
- **Gunicorn** - Python WSGI server
- **Nginx** - Reverse proxy (production)

## Quick Start

### Prerequisites
- Docker and Docker Compose
- Git

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/lloydlegaspi/thumbworx.git
   cd thumbworx
   ```

2. **Start all services**
   ```bash
   cd infra
   docker-compose up --build
   ```

3. **Access the application**
   - Frontend: http://localhost:3000
   - Laravel API: http://localhost:8000
   - Flask API: http://localhost:5000
   - PostgreSQL: localhost:5432
   - Redis: localhost:6379

### Environment Variables

The docker-compose.yml includes all necessary environment variables for local development:

#### Flask Service
- `DATABASE_URL`: PostgreSQL connection string
- `REDIS_URL`: Redis connection string
- `TRACCAR_BASE_URL`: Traccar server URL
- `TRACCAR_USER`: Traccar username
- `TRACCAR_PASS`: Traccar password

#### Laravel Service
- `DB_HOST`: Database host
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database user
- `DB_PASSWORD`: Database password
- `TRACCAR_BASE_URL`: Traccar server URL
- `TRACCAR_USER`: Traccar username
- `TRACCAR_PASS`: Traccar password

#### Next.js Service
- `NEXT_PUBLIC_API_URL`: Laravel backend URL

## API Endpoints

### Flask Microservice (Port 5000)
- `GET /api/traccar/devices` - Get all tracked devices
- `GET /api/traccar/positions` - Get latest positions (with caching)
- `GET /api/positions_cached` - Get cached positions from Redis
- `POST /api/predict_eta` - Calculate ETA between two points

### Laravel Backend (Port 8000)
- `GET /api/traccar/devices` - Proxy to Flask devices endpoint
- `GET /api/traccar/positions` - Proxy to Flask positions endpoint

## Data Flow

1. **GPS Data Collection**: Traccar server collects GPS data from devices
2. **Data Processing**: Flask microservice polls Traccar API every 30 seconds
3. **Caching**: Position data cached in Redis with 30-second TTL
4. **Persistence**: Historical data stored in PostgreSQL
5. **API Layer**: Laravel provides unified API for frontend
6. **Visualization**: Next.js displays real-time positions on interactive map

## Development Workflow

### Adding New Features

1. **Backend Changes**: Update Flask app.py or Laravel controllers
2. **Frontend Changes**: Update Next.js components
3. **Database Changes**: Create Laravel migrations
4. **Testing**: Use docker-compose for integration testing

### Database Migrations

```bash
# Run Laravel migrations
docker-compose exec laravel php artisan migrate

# Create new migration
docker-compose exec laravel php artisan make:migration create_new_table
```

### Logs and Debugging

```bash
# View all service logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f flask
docker-compose logs -f laravel
docker-compose logs -f next
```

## Production Deployment

### Recommended Platforms
- **Flask**: Render, Railway, or Heroku
- **Laravel**: Railway, Heroku, or AWS Elastic Beanstalk
- **Next.js**: Vercel, Netlify, or AWS Amplify
- **Database**: Railway PostgreSQL, AWS RDS, or DigitalOcean Managed Database
- **Cache**: Railway Redis, AWS ElastiCache, or DigitalOcean Managed Redis

### Production Environment Variables

Ensure these are set in your production environment:

```env
# Flask
DATABASE_URL=postgresql://user:pass@host:port/dbname
REDIS_URL=redis://host:port/0
TRACCAR_BASE_URL=https://your-traccar-server.com
TRACCAR_USER=your_username
TRACCAR_PASS=your_password

# Laravel
DB_HOST=your-db-host
DB_DATABASE=thumbworx
DB_USERNAME=your_username
DB_PASSWORD=your_password
APP_KEY=base64:your-generated-key

# Next.js
NEXT_PUBLIC_API_URL=https://your-laravel-backend.com
```

## Security Considerations

- Use HTTPS in production
- Secure Traccar credentials
- Implement rate limiting
- Add authentication/authorization
- Regular security updates
- Database connection encryption

## Analytics with Metabase

Connect Metabase to your PostgreSQL database for analytics:

1. **Run Metabase**
   ```bash
   docker run -d -p 3000:3000 --name metabase metabase/metabase
   ```

2. **Connect to Database**: Use your PostgreSQL credentials
3. **Create Dashboards**: Build visualizations from positions table

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 3000, 5000, 5432, 6379, 8000 are available
2. **Docker permissions**: Run docker commands with appropriate permissions
3. **Database connection**: Check PostgreSQL service is running
4. **Traccar connection**: Verify Traccar credentials and URL

### Debugging Steps

1. Check service status: `docker-compose ps`
2. View logs: `docker-compose logs [service-name]`
3. Restart services: `docker-compose restart [service-name]`
4. Rebuild containers: `docker-compose up --build`

## License

MIT License - see LICENSE file for details

## Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the logs for error messages
