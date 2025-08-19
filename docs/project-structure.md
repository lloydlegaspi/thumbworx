# Project Structure Documentation

## Overview

Thumbworx is a comprehensive vehicle tracking system built with microservices architecture using Docker containers. The system consists of five main components working together to provide real-time GPS tracking, data caching, and analytics.

## Directory Structure

```
thumbworx/
├── README.md                     # Main project documentation
├── test_api.py                   # Python API test script
├── test_api.ps1                  # PowerShell API test script
├── 
├── ai-flask/                     # Flask microservice
│   ├── app.py                    # Main Flask application
│   ├── Dockerfile                # Docker configuration
│   └── requirements.txt          # Python dependencies
│
├── backend-laravel/              # Laravel backend
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── TraccarController.php  # Traccar API proxy
│   │   ├── Models/
│   │   └── Providers/
│   ├── config/
│   │   └── services.php          # Traccar configuration
│   ├── database/
│   │   └── migrations/
│   │       └── *_create_positions_table.php  # Database schema
│   ├── routes/
│   │   └── api.php               # API routes
│   ├── Dockerfile                # Docker configuration
│   ├── composer.json             # PHP dependencies
│   └── .env.example              # Environment variables template
│
├── frontend-next/                # Next.js frontend
│   ├── src/
│   │   ├── app/
│   │   │   └── page.tsx          # Main dashboard page
│   │   └── components/
│   │       └── Map.tsx           # Interactive map component
│   ├── public/                   # Static assets
│   ├── Dockerfile                # Docker configuration
│   ├── package.json              # Node.js dependencies
│   ├── next.config.ts            # Next.js configuration
│   └── .env.local                # Environment variables
│
├── infra/                        # Infrastructure
│   └── docker-compose.yml        # Container orchestration
│
└── docs/                         # Documentation
    ├── deployment.md             # Deployment instructions
    ├── installation.md           # Installation guide
    ├── demo-script.md            # Presentation guide
    └── deployment-urls.md        # Live URLs tracking
```

## Component Details

### 1. Flask Microservice (`ai-flask/`)

**Purpose**: GPS data processing and caching layer

**Key Files**:
- `app.py`: Main application with API endpoints
- `requirements.txt`: Python dependencies (Flask, Redis, PostgreSQL, etc.)
- `Dockerfile`: Container configuration

**Endpoints**:
- `GET /api/traccar/devices`: Get all tracked devices
- `GET /api/traccar/positions`: Get latest positions with caching
- `GET /api/positions_cached`: Get cached positions from Redis
- `POST /api/predict_eta`: Calculate estimated time of arrival

**Technologies**:
- Flask (Python web framework)
- SQLAlchemy (Database ORM)
- Redis (Caching)
- PostgreSQL (Database)
- Gunicorn (WSGI server)

### 2. Laravel Backend (`backend-laravel/`)

**Purpose**: API orchestration and application logic

**Key Files**:
- `app/Http/Controllers/TraccarController.php`: Proxy to Flask/Traccar
- `database/migrations/*_create_positions_table.php`: Database schema
- `routes/api.php`: API route definitions
- `config/services.php`: Traccar service configuration

**Endpoints**:
- `GET /api/traccar/devices`: Proxy to devices endpoint
- `GET /api/traccar/positions`: Proxy to positions endpoint

**Technologies**:
- Laravel 11 (PHP framework)
- PostgreSQL (Database)
- PHP 8.2
- Composer (Package manager)

### 3. Next.js Frontend (`frontend-next/`)

**Purpose**: User interface and real-time dashboard

**Key Files**:
- `src/app/page.tsx`: Main dashboard with auto-refreshing map
- `src/components/Map.tsx`: Interactive map component
- `package.json`: Dependencies including React Leaflet

**Features**:
- Real-time position updates (5-second intervals)
- Interactive map with device markers
- Responsive design
- TypeScript support

**Technologies**:
- Next.js 14 (React framework)
- React Leaflet (Interactive maps)
- SWR (Data fetching)
- TypeScript

### 4. Infrastructure (`infra/`)

**Purpose**: Container orchestration and service configuration

**Services Defined**:
- PostgreSQL 15 (Database)
- Redis 7 (Cache)
- Flask API (Port 5000)
- Laravel API (Port 8000)
- Next.js Frontend (Port 3000)

**Network Configuration**:
- Internal Docker network for service communication
- Exposed ports for external access
- Volume persistence for database

### 5. Documentation (`docs/`)

**Purpose**: Comprehensive project documentation

**Files**:
- `installation.md`: Local setup and troubleshooting
- `deployment.md`: Cloud deployment instructions
- `demo-script.md`: Presentation and demo guide
- `deployment-urls.md`: Live URL tracking

## Data Flow

```
Traccar GPS Server
        ↓
Flask Microservice (Port 5000)
        ↓ (Caches in Redis, Persists to PostgreSQL)
        ↓
Laravel Backend (Port 8000)
        ↓ (API Gateway)
        ↓
Next.js Frontend (Port 3000)
        ↓
User Browser
```

### Detailed Flow:

1. **Data Collection**: Traccar server collects GPS data from devices
2. **Processing**: Flask microservice polls Traccar API every 30 seconds
3. **Caching**: Position data cached in Redis with 30-second TTL
4. **Persistence**: Historical data stored in PostgreSQL positions table
5. **API Layer**: Laravel provides unified API for frontend consumption
6. **Visualization**: Next.js displays real-time positions on interactive map
7. **User Interaction**: Users view live tracking dashboard

## Database Schema

### Positions Table
```sql
CREATE TABLE positions (
    id SERIAL PRIMARY KEY,
    device_id INTEGER,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    speed INTEGER,
    device_time TIMESTAMP,
    attributes JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Environment Variables

### Development (Docker Compose)
All environment variables are pre-configured in `docker-compose.yml` for local development.

### Production
Each service requires specific environment variables:

**Flask**:
- `DATABASE_URL`: PostgreSQL connection string
- `REDIS_URL`: Redis connection string
- `TRACCAR_BASE_URL`: Traccar server URL
- `TRACCAR_USER`: Traccar username
- `TRACCAR_PASS`: Traccar password

**Laravel**:
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database config
- `TRACCAR_BASE_URL`, `TRACCAR_USER`, `TRACCAR_PASS`: Traccar config
- `APP_KEY`: Laravel application key

**Next.js**:
- `NEXT_PUBLIC_API_URL`: Laravel backend URL

## Port Configuration

| Service | Internal Port | External Port | Purpose |
|---------|---------------|---------------|---------|
| PostgreSQL | 5432 | 5432 | Database access |
| Redis | 6379 | 6379 | Cache access |
| Flask | 5000 | 5000 | Microservice API |
| Laravel | 80 | 8000 | Backend API |
| Next.js | 3000 | 3000 | Frontend web app |

## Development Workflow

### Local Development
1. Clone repository
2. Run `docker-compose up --build` in `infra/` directory
3. Access services at respective ports
4. Use test scripts to verify functionality

### Making Changes
1. Edit source code in respective service directories
2. Rebuild specific service: `docker-compose build [service-name]`
3. Restart services: `docker-compose up`
4. Test changes using provided scripts

### Debugging
1. View logs: `docker-compose logs [service-name]`
2. Access container: `docker-compose exec [service-name] bash`
3. Check database: `docker-compose exec postgres psql -U thumb_user -d thumbworx`
4. Check Redis: `docker-compose exec redis redis-cli`

## Testing

### Automated Tests
- `test_api.py`: Python script for endpoint testing
- `test_api.ps1`: PowerShell script for Windows users

### Manual Testing
- Frontend: http://localhost:3000
- API endpoints via browser or Postman
- Database queries via PostgreSQL client

## Security Considerations

### Development
- Default credentials are safe for local development
- All services run in isolated Docker network
- No sensitive data in repository

### Production
- Use secure environment variables
- Enable HTTPS on all services
- Implement proper authentication
- Regular security updates
- Database connection encryption

## Performance Optimization

### Caching Strategy
- Redis caching with 30-second TTL
- Client-side caching with SWR
- Database query optimization

### Scaling Options
- Horizontal scaling of Flask workers
- Laravel queue workers for background jobs
- Database read replicas
- CDN for frontend assets

## Monitoring and Maintenance

### Health Checks
- API endpoint availability
- Database connection status
- Redis connectivity
- Service response times

### Logging
- Application logs via Docker
- Database query logs
- Error tracking and alerting
- Performance monitoring

This structure provides a solid foundation for a production-ready vehicle tracking system with clear separation of concerns, scalability options, and comprehensive documentation.
