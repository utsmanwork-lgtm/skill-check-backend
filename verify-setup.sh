#!/bin/bash

echo "=== Skill Check Backend Setup Verification ==="
echo ""

# Check project structure
echo "[1/5] Checking project structure..."
REQUIRED_DIRS=(
    "app/Models"
    "app/Http/Controllers/Api"
    "database/migrations"
    "database/seeders"
    "routes"
    "docker"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "  ✓ $dir"
    else
        echo "  ✗ $dir MISSING"
    fi
done

echo ""
echo "[2/5] Checking key files..."
REQUIRED_FILES=(
    "composer.json"
    "docker-compose.yml"
    "Dockerfile"
    "docker-entrypoint.sh"
    ".env.example"
    "README.md"
    "routes/api.php"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file"
    else
        echo "  ✗ $file MISSING"
    fi
done

echo ""
echo "[3/5] Checking models..."
MODELS=(
    "app/Models/User.php"
    "app/Models/Student.php"
    "app/Models/Skill.php"
    "app/Models/Indicator.php"
    "app/Models/Assessment.php"
    "app/Models/AssessmentIndicator.php"
    "app/Models/ClassRoom.php"
    "app/Models/SkillArea.php"
)

for model in "${MODELS[@]}"; do
    if [ -f "$model" ]; then
        echo "  ✓ $(basename $model)"
    else
        echo "  ✗ $(basename $model) MISSING"
    fi
done

echo ""
echo "[4/5] Checking migrations..."
MIGRATION_COUNT=$(find database/migrations -name "*.php" | wc -l)
echo "  ✓ Found $MIGRATION_COUNT migrations"

echo ""
echo "[5/5] Checking API controllers..."
CONTROLLERS=(
    "app/Http/Controllers/Api/AuthController.php"
    "app/Http/Controllers/Api/StudentController.php"
    "app/Http/Controllers/Api/AssessmentController.php"
    "app/Http/Controllers/Api/AnalyticsController.php"
)

for controller in "${CONTROLLERS[@]}"; do
    if [ -f "$controller" ]; then
        echo "  ✓ $(basename $controller)"
    else
        echo "  ✗ $(basename $controller) MISSING"
    fi
done

echo ""
echo "=== Setup Complete ==="
echo ""
echo "Next steps:"
echo "1. Start Docker Desktop"
echo "2. Run: docker-compose up --build -d"
echo "3. Wait 30 seconds for containers to initialize"
echo "4. Test: curl http://localhost:8000/up"
echo ""
