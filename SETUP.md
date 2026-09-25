# Setup Instructions

## System Requirements

- Docker Desktop (Windows/Mac) or Docker Engine (Linux) with Docker Compose
- 8GB RAM minimum
- Ports available: 8000 (Nginx), 3306 (MySQL)
- Bash shell (Git Bash on Windows)

## Local Development Setup

### 1. Start Containers

```bash
cd skill-check-backend
docker-compose up --build -d
```

This will:
- Build PHP 8.3 image with Laravel dependencies
- Start MySQL 8.0 database
- Start Nginx reverse proxy
- Automatically run migrations and seeders

### 2. Verify Services

```bash
# Check container status
docker-compose ps

# Expected output:
# skill-check-mysql  mysql:8.0       Up
# skill-check-app    php:8.3-fpm     Up
# skill-check-nginx  nginx:alpine    Up
```

### 3. Health Check

```bash
# API health endpoint
curl http://localhost:8000/up

# Expected response:
# {"status":"ok"}
```

## First Login

```bash
# Login with seeded credentials
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "guru@skillcheck.test",
    "password": "password"
  }'

# Response (save token):
# {
#   "success": true,
#   "message": "Login successful",
#   "data": {
#     "user": { "id": 1, "name": "Budi Santoso", "email": "guru@skillcheck.test", "role": "guru" },
#     "token": "1|abc123xyz..."
#   }
# }
```

## Database Access

### Via Laravel Tinker REPL

```bash
docker-compose exec app php artisan tinker

>>> User::all()
>>> Student::count()
>>> Assessment::where('status', 'completed')->count()
```

### Via MySQL CLI

```bash
docker-compose exec mysql mysql -u skill_user -ppassword skill_check

mysql> SELECT * FROM students LIMIT 5;
mysql> SELECT skill_id, COUNT(*) FROM assessments GROUP BY skill_id;
```

## Running Commands

```bash
# Artisan commands
docker-compose exec app php artisan {command}

# Examples:
docker-compose exec app php artisan migrate:status
docker-compose exec app php artisan db:seed --class=StudentSeeder
docker-compose exec app php artisan cache:clear
```

## Logs

```bash
# Application logs
docker-compose logs -f app

# MySQL logs
docker-compose logs -f mysql

# Nginx logs
docker-compose logs -f nginx

# All services
docker-compose logs -f
```

## Troubleshooting

### Containers fail to start

```bash
# Clean and rebuild
docker-compose down -v
docker-compose up --build -d
```

### Migration errors

```bash
# Reset database and re-seed
docker-compose exec app php artisan migrate:reset
docker-compose exec app php artisan migrate:fresh --seed
```

### Port already in use

Edit `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8001:80"  # Change host port from 8000
```

Then restart:
```bash
docker-compose restart nginx
```

### MySQL connection timeout

Wait 15 seconds and retry. MySQL initialization takes time:

```bash
# Check MySQL readiness
docker-compose exec mysql mysql -u root -proot -e "SELECT 1"
```

## Stopping Services

```bash
# Stop (containers remain)
docker-compose stop

# Stop and remove containers
docker-compose down

# Stop, remove containers, and delete volumes (data deleted)
docker-compose down -v
```

## Production Deployment

### Build Optimized Image

```bash
docker build --target=production \
  -t skill-check:latest \
  -f Dockerfile.prod .
```

### Environment Setup

```bash
# Copy .env.example and configure for production
cp .env.example .env.production

# Generate production key
php artisan key:generate --env=production

# Export secure values
export APP_KEY=$(grep APP_KEY .env.production | cut -d '=' -f2)
```

### Database Migration

```bash
# On production server
php artisan migrate --force --env=production
php artisan db:seed --force --env=production
```

### Nginx Configuration

Use a reverse proxy (Traefik, HAProxy, or cloud load balancer):

```nginx
upstream skill_check_app {
    server app:9000;
}

server {
    listen 80;
    server_name api.skillcheck.local;
    client_max_body_size 64M;

    location ~ \.php$ {
        fastcgi_pass skill_check_app;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /app/public$fastcgi_script_name;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

## Verification Checklist

- [ ] `docker-compose up --build -d` completes without errors
- [ ] `docker-compose ps` shows all 3 services as "Up"
- [ ] `curl http://localhost:8000/up` returns 200 OK
- [ ] Login request returns token
- [ ] `GET /api/students` returns student list (requires token)
- [ ] Database contains 10 students + 5 skills + 20 indicators
