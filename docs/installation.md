# Installation Guide

This guide will help you set up the Thumbworx tracking system locally and in production.

## Prerequisites

### Local Development
- **Docker** (v20.10+) and **Docker Compose** (v2.0+)
- **Git** for version control
- **Node.js** (v18+) for frontend development (optional)
- **PHP** (v8.2+) and **Composer** for Laravel development (optional)
- **Python** (v3.11+) for Flask development (optional)

### Production Deployment
- Cloud platform accounts (Render, Railway, Vercel)
- Domain name (optional)
- SSL certificate (automatic with platforms)

## Local Installation

### 1. Clone Repository

```bash
git clone https://github.com/lloydlegaspi/thumbworx.git
cd thumbworx
```

### 2. Environment Configuration

The `docker-compose.yml` includes all necessary environment variables for local development. No additional configuration needed for basic setup.

### 3. Start Services

```bash
cd infra
docker-compose up --build
```

This will:
- Pull required Docker images
- Build custom images for Flask, Laravel, and Next.js
- Start PostgreSQL and Redis
- Run database migrations
- Start all services

### 4. Verify Installation

Check that all services are running:

```bash
docker-compose ps
```

Expected output:
```
NAME                COMMAND                  SERVICE             STATUS              PORTS
infra-flask-1       "gunicorn app:app -b…"   flask               running             0.0.0.0:5000->5000/tcp
infra-laravel-1     "sh -c 'php artisan …"   laravel             running             0.0.0.0:8000->8000/tcp
infra-next-1        "docker-entrypoint.s…"   next                running             0.0.0.0:3000->3000/tcp
infra-postgres-1    "docker-entrypoint.s…"   postgres            running             0.0.0.0:5432->5432/tcp
infra-redis-1       "docker-entrypoint.s…"   redis               running             0.0.0.0:6379->6379/tcp
```

### 5. Access Applications

- **Frontend**: http://localhost:3000
- **Laravel API**: http://localhost:8000
- **Flask API**: http://localhost:5000

## Individual Service Setup

If you prefer to run services individually (for development):

### Flask Service

```bash
cd ai-flask

# Create virtual environment
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Set environment variables
export DATABASE_URL="postgresql://thumb_user:thumb_pass@localhost:5432/thumbworx"
export REDIS_URL="redis://localhost:6379/0"
export TRACCAR_BASE_URL="https://demo.traccar.org"
export TRACCAR_USER="demo_user"
export TRACCAR_PASS="demo_pass"

# Run development server
python app.py
```

### Laravel Service

```bash
cd backend-laravel

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=thumbworx
DB_USERNAME=thumb_user
DB_PASSWORD=thumb_pass

# Run migrations
php artisan migrate

# Start development server
php artisan serve --host=0.0.0.0 --port=8000
```

### Next.js Service

```bash
cd frontend-next

# Install dependencies
npm install

# Set environment variable
echo "NEXT_PUBLIC_API_URL=http://localhost:8000" > .env.local

# Start development server
npm run dev
```

## Database Setup

### PostgreSQL

If using external PostgreSQL:

```sql
-- Create database
CREATE DATABASE thumbworx;

-- Create user
CREATE USER thumb_user WITH PASSWORD 'thumb_pass';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE thumbworx TO thumb_user;
```

### Redis

For external Redis, ensure it's accessible and configured in environment variables.

## Troubleshooting

### Common Issues

1. **Port Conflicts**
   ```bash
   # Check if ports are in use
   netstat -an | grep :3000
   netstat -an | grep :5000
   netstat -an | grep :8000
   ```

2. **Docker Issues**
   ```bash
   # Remove all containers and start fresh
   docker-compose down -v
   docker-compose up --build
   ```

3. **Database Connection Issues**
   ```bash
   # Check PostgreSQL logs
   docker-compose logs postgres
   
   # Connect to database manually
   docker-compose exec postgres psql -U thumb_user -d thumbworx
   ```

4. **Redis Connection Issues**
   ```bash
   # Check Redis logs
   docker-compose logs redis
   
   # Test Redis connection
   docker-compose exec redis redis-cli ping
   ```

### Log Files

View logs for debugging:

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f flask
docker-compose logs -f laravel
docker-compose logs -f next
```

### Performance Issues

1. **Increase Docker Resources**
   - Memory: 4GB minimum
   - CPU: 2 cores minimum

2. **Optimize Database**
   ```sql
   -- Check database size
   SELECT pg_size_pretty(pg_database_size('thumbworx'));
   
   -- Analyze performance
   EXPLAIN ANALYZE SELECT * FROM positions LIMIT 10;
   ```

## Development Environment

### Code Editor Setup

**VS Code Extensions:**
- PHP Intelephense
- Python
- TypeScript and JavaScript Language Features
- Docker
- Laravel Blade Snippets

### Git Hooks

Set up pre-commit hooks for code quality:

```bash
# Install pre-commit
pip install pre-commit

# Set up hooks
pre-commit install
```

### Testing

```bash
# Laravel tests
cd backend-laravel
./vendor/bin/phpunit

# Flask tests (if implemented)
cd ai-flask
python -m pytest

# Next.js tests
cd frontend-next
npm test
```

## Production Considerations

### Environment Variables

Secure the following in production:
- Database passwords
- Traccar credentials
- API keys
- Application secrets

### Security

- Enable HTTPS
- Use strong passwords
- Configure firewalls
- Regular security updates
- Monitor access logs

### Performance

- Enable caching
- Optimize database queries
- Use CDN for static assets
- Implement rate limiting
- Monitor performance metrics

### Backup

- Database backups
- Redis persistence
- Application code backups
- Configuration backups

## Next Steps

After installation:

1. **Configure Traccar**: Set up your Traccar server or use demo
2. **Add Devices**: Register GPS devices in Traccar
3. **Customize**: Modify the interface for your needs
4. **Deploy**: Follow deployment guide for production
5. **Monitor**: Set up monitoring and alerting
6. **Scale**: Configure auto-scaling for high traffic

## Support

For installation issues:
- Check logs first
- Review this troubleshooting section
- Create GitHub issue with details
- Include system information and error messages
