# API Reference

Base URL: `http://localhost:8000/api`

All endpoints require `Content-Type: application/json` header except login.

## Authentication Endpoints

### POST /auth/login

Login and receive API token.

**Request:**
```json
{
  "email": "guru@skillcheck.test",
  "password": "password"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "guru@skillcheck.test",
      "role": "guru",
      "created_at": "2024-01-01T00:00:00Z"
    },
    "token": "1|2pNkLm4bQ9vZxXwYaB5cD6eF7gH8jK9lM0nOpQrStU"
  }
}
```

**Errors:**
- 422: Invalid email or password
- 400: Missing required fields

---

### POST /auth/logout

Revoke current API token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

**Errors:**
- 401: Unauthorized (missing/invalid token)

---

## Student Management Endpoints

### GET /students

Retrieve guru's class roster.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- None

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Students retrieved",
  "data": [
    {
      "id": 1,
      "nis": "001",
      "name": "Ahmad Rizki",
      "class_room_id": 1,
      "guru_id": 1,
      "class_room": {
        "id": 1,
        "code": "TKA1",
        "name": "Teknik Kendaraan Ringan 1"
      },
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

**Errors:**
- 401: Unauthorized
- 403: Not a guru

---

### GET /students/{id}/skills

Retrieve student's skill completion status.

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `id` (integer, required): Student ID

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Student skills retrieved",
  "data": [
    {
      "skill": {
        "id": 1,
        "code": "ENG001",
        "name": "Engine Assembly & Disassembly",
        "skill_area_id": 1,
        "indicators": [
          {
            "id": 1,
            "skill_id": 1,
            "code": "E001",
            "description": "Safely remove engine cover and identify components",
            "order": 1
          }
        ]
      },
      "assessment_id": 5,
      "status": "pending",
      "progress": 50,
      "checked_count": 2,
      "total_count": 4
    }
  ]
}
```

**Status values:**
- `pending`: Assessment not started
- `completed`: All indicators checked (LULUS)

**Errors:**
- 401: Unauthorized
- 403: Student not in guru's class
- 404: Student not found

---

## Assessment Endpoints

### POST /assessments

Submit assessment indicator checks. Auto-completes skill when all indicators checked.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "student_id": 1,
  "skill_id": 1,
  "indicators": [
    {
      "indicator_id": 1,
      "checked": true,
      "notes": "Student demonstrated correct technique"
    },
    {
      "indicator_id": 2,
      "checked": false,
      "notes": "Needs practice on safety procedure"
    }
  ]
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Assessment submitted",
  "data": {
    "id": 5,
    "student_id": 1,
    "skill_id": 1,
    "guru_id": 1,
    "status": "in_progress",
    "completed_at": null,
    "indicators": [
      {
        "id": 11,
        "assessment_id": 5,
        "indicator_id": 1,
        "checked": true,
        "notes": "Student demonstrated correct technique"
      },
      {
        "id": 12,
        "assessment_id": 5,
        "indicator_id": 2,
        "checked": false,
        "notes": "Needs practice on safety procedure"
      }
    ]
  }
}
```

**Status Auto-Transition:**
- When all indicators `checked: true` → status becomes `completed`, `completed_at` timestamp set

**Errors:**
- 401: Unauthorized
- 403: Student not in guru's class
- 422: Validation failed (student/skill not found, invalid indicators)

---

### GET /assessments/{id}

Retrieve assessment form with all indicator details.

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `id` (integer, required): Assessment ID

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Assessment retrieved",
  "data": {
    "id": 5,
    "student_id": 1,
    "skill_id": 1,
    "guru_id": 1,
    "status": "completed",
    "completed_at": "2024-09-25T10:30:00Z",
    "student": {
      "id": 1,
      "nis": "001",
      "name": "Ahmad Rizki"
    },
    "skill": {
      "id": 1,
      "code": "ENG001",
      "name": "Engine Assembly & Disassembly"
    },
    "indicators": [
      {
        "id": 11,
        "assessment_id": 5,
        "indicator_id": 1,
        "checked": true,
        "notes": "Passed",
        "indicator": {
          "code": "E001",
          "description": "Safely remove engine cover and identify components"
        }
      }
    ]
  }
}
```

**Errors:**
- 401: Unauthorized
- 403: Assessment not owned by guru
- 404: Assessment not found

---

### POST /assessments/{id}/reset

Reset assessment for remediation (all indicators unchecked, status → pending).

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `id` (integer, required): Assessment ID

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Assessment reset for remediation",
  "data": {
    "id": 5,
    "student_id": 1,
    "skill_id": 1,
    "guru_id": 1,
    "status": "pending",
    "completed_at": null
  }
}
```

**Effects:**
- All `checked` → `false`
- All `notes` → `null`
- Status → `pending`
- `completed_at` → `null`
- Recorded in `assessment_histories` table

**Errors:**
- 401: Unauthorized
- 403: Assessment not owned by guru
- 404: Assessment not found

---

## Analytics Endpoints

### GET /analytics/heatmap

Class-wide skill completion matrix.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Heatmap matrix retrieved",
  "data": {
    "skills": [
      {
        "id": 1,
        "code": "ENG001",
        "name": "Engine Assembly & Disassembly"
      }
    ],
    "matrix": [
      {
        "student_id": 1,
        "nis": "001",
        "name": "Ahmad Rizki",
        "skills": [
          {
            "skill_id": 1,
            "code": "ENG001",
            "name": "Engine Assembly & Disassembly",
            "score": 100,
            "status": "LULUS"
          },
          {
            "skill_id": 2,
            "code": "ELEC001",
            "name": "Battery & Charging System",
            "score": 50,
            "status": "BELUM_LULUS"
          }
        ]
      }
    ]
  }
}
```

**Score Calculation:**
- Score = (checked_indicators / total_indicators) × 100
- Status: `LULUS` if score = 100, else `BELUM_LULUS`

**Errors:**
- 401: Unauthorized

---

### GET /analytics/gap-analysis

Skill pass rates and learning gaps.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Gap analysis retrieved",
  "data": [
    {
      "skill_id": 1,
      "code": "ENG001",
      "name": "Engine Assembly & Disassembly",
      "total_students": 10,
      "passed_count": 7,
      "failed_count": 2,
      "not_started_count": 1,
      "pass_rate": 70,
      "gap": 30
    }
  ]
}
```

**Metrics:**
- `pass_rate`: Percentage of students with 100% indicators checked
- `gap`: 100 - pass_rate (learning gap percentage)
- `not_started_count`: Students with no assessment yet

**Errors:**
- 401: Unauthorized

---

## Error Response Format

All errors return JSON with:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Field validation error"]
  }
}
```

**Common HTTP Status Codes:**
- 200: Success
- 201: Created
- 400: Bad request
- 401: Unauthorized (missing/invalid token)
- 403: Forbidden (insufficient permissions)
- 404: Not found
- 422: Validation failed
- 500: Server error

---

## Authentication

All protected endpoints require:

```
Authorization: Bearer {token}
```

Token obtained from `/auth/login`. Token expires after inactivity (configurable in Sanctum config). Revoke with `/auth/logout`.

---

## Rate Limiting

Not currently implemented. Add Laravel Throttle middleware if needed:

```php
Route::middleware('throttle:60,1')->group(function () {
    // Protected routes
});
```

---

## CORS

Not currently enabled. To allow frontend requests, add to `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

Then configure `config/cors.php`.
