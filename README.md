# Skill Check Assessment Backend API

Automotive technician assessment system backend built with Laravel 11 and Sanctum authentication.

## Features

- Multi-skill assessment tracking for automotive technician students
- Guru (instructor) role-based access with class roster management
- Auto-LULUS (pass) status when all indicators checked for a skill
- Assessment heatmap and gap analysis reporting
- Remediation support (reset assessments for retake)
- Sanctum token-based API authentication
- Docker Compose setup for local development

## Architecture

**Database Schema (10 tables):**
1. `users` - Guru accounts (admin/instructor role)
2. `students` - Student records per class
3. `class_rooms` - Class groups
4. `skill_areas` - Assessment categories (Engine, Electrical, Transmission, Brake, Suspension)
5. `skills` - Individual skills (5 seeded)
6. `indicators` - Assessment checklist items per skill (4 per skill = 20 total)
7. `assessments` - Assessment instances per student-skill pair
8. `assessment_indicators` - Individual indicator check status
9. `assessment_histories` - Audit log for assessment changes
10. `personal_access_tokens` - Sanctum API tokens

**Key Models:**
- User (guru) → has many Students (roster) + Assessments
- Student → has many Assessments
- Skill → has many Indicators
- Assessment → has many AssessmentIndicators + isComplete() method
- AssessmentIndicator → tracks checked status + notes per indicator

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login with email/password, returns token
- `POST /api/auth/logout` - Revoke current token

### Student Management
- `GET /api/students` - Guru's class roster
- `GET /api/students/{id}/skills` - Student's skill status (pending/completed, progress %)

### Assessments
- `POST /api/assessments` - Submit indicator checks (auto-marks LULUS if all checked)
- `GET /api/assessments/{id}` - Retrieve submitted assessment form
- `POST /api/assessments/{id}/reset` - Reset assessment for remediation

### Analytics
- `GET /api/analytics/heatmap` - Class-wide skill completion matrix
- `GET /api/analytics/gap-analysis` - Pass rates and gaps per skill

All endpoints except login require `Authorization: Bearer {token}` header.

## Setup Instructions

### Prerequisites
- Docker Desktop installed
- 8GB RAM available
- Port 8000, 3306 available

### Quick Start

```bash
# Clone or enter project directory
cd skill-check-backend

# Copy environment file
cp .env.example .env

# Start Docker services
docker-compose up -d

# Wait for services to be healthy (15-30 seconds)
docker-compose ps

# Run inside app container
docker-compose exec app bash

# Inside container, run migrations and seeders
php artisan migrate:fresh --seed

# Exit container
exit

# API is now live at http://localhost:8000
```

### Verify Installation

```bash
# Health check
curl http://localhost:8000/up

# Login (returns token)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "guru@skillcheck.test",
    "password": "password"
  }'

# Get student roster (use token from login response)
curl http://localhost:8000/api/students \
  -H "Authorization: Bearer {token}"
```

## Seeded Data

**Users:**
- Email: `guru@skillcheck.test` / Password: `password` (Role: guru)

**Skills & Indicators (5 skills × 4 indicators = 20 total):**
1. Engine Mechanics - Assembly/Disassembly
2. Electrical Systems - Battery & Charging
3. Transmission - Manual Maintenance
4. Brake Systems - Pad & Rotor Replacement
5. Suspension - Component Service

**Students (10):**
- NIS 001–010 in class TKA1 (Teknik Kendaraan Ringan 1)
- All assigned to seeded guru

## Development

### Running Artisan Commands

```bash
docker-compose exec app php artisan {command}
```

Examples:
```bash
# Create migration
docker-compose exec app php artisan make:model SkillHistory -m

# Tinker REPL
docker-compose exec app php artisan tinker

# Clear caches
docker-compose exec app php artisan cache:clear
```

### Database Access

```bash
# MySQL CLI
docker-compose exec mysql mysql -u skill_user -ppassword skill_check

# Or via Laravel
docker-compose exec app php artisan tinker
>>> DB::table('students')->count()
```

### Logs

```bash
# Application logs
docker-compose logs -f app

# MySQL logs
docker-compose logs -f mysql

# Nginx logs
docker-compose logs -f nginx
```

## Production Readiness

- [x] Migrations versioned with timestamps
- [x] Seeders for initial data
- [x] Sanctum token authentication
- [x] Input validation + error handling
- [x] Eager-loaded relationships (no N+1)
- [x] Audit logging (assessment_histories)
- [x] Role-based access control (guru owns students)
- [x] Auto-LULUS logic (isComplete() method)

To deploy:
1. Build production image with optimized Dockerfile
2. Export `APP_KEY` from seeded container
3. Run migrations on target MySQL
4. Mount logs volume
5. Configure reverse proxy (nginx/Traefik)
6. Set `APP_DEBUG=false`, `APP_ENV=production`

## Troubleshooting

**Containers won't start:**
```bash
docker-compose down -v  # Remove volumes
docker-compose up --build
```

**Migration errors:**
```bash
docker-compose exec app php artisan migrate:reset
docker-compose exec app php artisan migrate:fresh --seed
```

**Port already in use:**
Edit docker-compose.yml:
```yaml
nginx:
  ports:
    - "8001:80"  # Change host port
```

**Database connection timeout:**
Wait 10s and retry. MySQL needs initialization time. Check:
```bash
docker-compose exec mysql mysql -u root -proot -e "SELECT 1"
```

## API Response Format

All endpoints return JSON:

```json
{
  "success": true,
  "message": "Success message",
  "data": { /* payload */ }
}
```

Errors:

```json
{
  "success": false,
  "message": "Error message",
  "errors": { /* validation errors */ }
}
```

## License

MIT
