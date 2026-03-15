# Architecture - api-inspeccion

## Stack

| Layer          | Technology                        |
| -------------- | --------------------------------- |
| Framework      | Laravel 12                        |
| Language       | PHP 8.2+                          |
| Database       | SQLite (default, configurable)    |
| Authentication | Laravel Sanctum (token-based)     |
| Frontend       | Vite + Tailwind CSS 4             |
| Testing        | PestPHP                           |
| Code Style     | Laravel Pint (PSR-12 based)       |

## Folder Structure

```
api-inspeccion/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── AuthController.php
│   │   │       ├── ClientController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── EquipmentController.php
│   │   │       ├── FindingController.php
│   │   │       ├── InspectionController.php
│   │   │       ├── InspectionRequestController.php
│   │   │       ├── InspectionTemplateController.php
│   │   │       ├── ServiceTypeController.php
│   │   │       ├── UserController.php
│   │   │       └── WorkOrderController.php
│   │   └── Resources/
│   │       ├── ClientResource.php
│   │       ├── EquipmentResource.php
│   │       ├── FindingResource.php
│   │       ├── InspectionAnswerResource.php
│   │       ├── InspectionPhotoResource.php
│   │       ├── InspectionRequestResource.php
│   │       ├── InspectionResource.php
│   │       ├── InspectionTemplateResource.php
│   │       ├── ServiceTypeResource.php
│   │       ├── TemplateSectionResource.php
│   │       ├── TemplateQuestionResource.php
│   │       ├── UserResource.php
│   │       ├── WorkOrderItemResource.php
│   │       └── WorkOrderResource.php
│   ├── Models/
│   │   ├── Client.php
│   │   ├── Equipment.php
│   │   ├── Finding.php
│   │   ├── Inspection.php
│   │   ├── InspectionAnswer.php
│   │   ├── InspectionPhoto.php
│   │   ├── InspectionRequest.php
│   │   ├── InspectionTemplate.php
│   │   ├── ServiceType.php
│   │   ├── TemplateQuestion.php
│   │   ├── TemplateSection.php
│   │   ├── User.php
│   │   ├── WorkOrder.php
│   │   └── WorkOrderItem.php
│   └── Traits/
│       └── ApiResponse.php
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_03_15_171555_create_personal_access_tokens_table.php
│   │   ├── 2026_03_15_180001_create_clients_table.php
│   │   ├── 2026_03_15_180002_create_equipment_table.php
│   │   ├── 2026_03_15_180003_create_service_types_table.php
│   │   ├── 2026_03_15_180004_create_inspection_requests_table.php
│   │   ├── 2026_03_15_180005_create_inspection_templates_table.php
│   │   ├── 2026_03_15_180006_create_template_sections_table.php
│   │   ├── 2026_03_15_180007_create_template_questions_table.php
│   │   ├── 2026_03_15_180008_create_work_orders_table.php
│   │   ├── 2026_03_15_180009_create_work_order_items_table.php
│   │   ├── 2026_03_15_180010_create_inspections_table.php
│   │   ├── 2026_03_15_180011_create_inspection_answers_table.php
│   │   ├── 2026_03_15_180012_create_findings_table.php
│   │   └── 2026_03_15_180013_create_inspection_photos_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── InspectionTemplateSeeder.php
├── routes/
│   ├── api.php
│   └── web.php
├── CLAUDE.md
├── project_spec.md
├── architecture.md
└── project_state.md
```

## Database Schema

### users
| Column             | Type      | Notes                        |
| ------------------ | --------- | ---------------------------- |
| id                 | bigint PK | auto-increment               |
| name               | string    |                              |
| email              | string    | unique                       |
| email_verified_at  | timestamp | nullable                     |
| password           | string    | hashed via cast              |
| role               | string    | default: 'inspector'         |
| remember_token     | string    |                              |
| created_at         | timestamp |                              |
| updated_at         | timestamp |                              |

Also creates: `password_reset_tokens` (email PK, token, created_at) and `sessions` (id PK, user_id, ip_address, user_agent, payload, last_activity).

### personal_access_tokens
Standard Sanctum table for API token storage.

### clients
| Column         | Type      | Notes               |
| -------------- | --------- | -------------------- |
| id             | bigint PK |                      |
| name           | string    |                      |
| ruc            | string(20)| nullable (tax ID)    |
| address        | string    | nullable             |
| contact_name   | string    | nullable             |
| contact_email  | string    | nullable             |
| contact_phone  | string    | nullable             |
| is_active      | boolean   | default: true        |
| created_at     | timestamp |                      |
| updated_at     | timestamp |                      |

### equipment
| Column         | Type      | Notes                              |
| -------------- | --------- | ---------------------------------- |
| id             | bigint PK |                                    |
| client_id      | FK        | -> clients, cascadeOnDelete        |
| name           | string    |                                    |
| type           | string    | nullable                           |
| brand          | string    | nullable                           |
| model          | string    | nullable                           |
| year           | string(4) | nullable                           |
| plate          | string    | nullable                           |
| serial_number  | string    | nullable                           |
| internal_code  | string    | nullable                           |
| status         | string    | default: 'active'                  |
| created_at     | timestamp |                                    |
| updated_at     | timestamp |                                    |

### service_types
| Column       | Type      | Notes            |
| ------------ | --------- | ---------------- |
| id           | bigint PK |                  |
| name         | string    |                  |
| description  | text      | nullable         |
| is_active    | boolean   | default: true    |
| created_at   | timestamp |                  |
| updated_at   | timestamp |                  |

### inspection_requests
| Column          | Type      | Notes                              |
| --------------- | --------- | ---------------------------------- |
| id              | bigint PK |                                    |
| request_number  | string    | unique, auto: REQ-YYYYMMDD-XXXX   |
| client_id       | FK        | -> clients, cascadeOnDelete        |
| service_type_id | FK        | -> service_types, cascadeOnDelete  |
| requested_date  | date      |                                    |
| scheduled_date  | date      | nullable                           |
| status          | string    | default: 'pending'                 |
| notes           | text      | nullable                           |
| created_by      | FK        | -> users, nullable, nullOnDelete   |
| created_at      | timestamp |                                    |
| updated_at      | timestamp |                                    |

### inspection_templates
| Column       | Type      | Notes            |
| ------------ | --------- | ---------------- |
| id           | bigint PK |                  |
| name         | string    |                  |
| code         | string    | unique           |
| description  | text      | nullable         |
| vehicle_type | string    | nullable         |
| is_active    | boolean   | default: true    |
| version      | integer   | default: 1       |
| created_at   | timestamp |                  |
| updated_at   | timestamp |                  |

### template_sections
| Column                 | Type      | Notes                                    |
| ---------------------- | --------- | ---------------------------------------- |
| id                     | bigint PK |                                          |
| inspection_template_id | FK        | -> inspection_templates, cascadeOnDelete |
| name                   | string    |                                          |
| order                  | integer   | default: 0                               |
| description            | text      | nullable                                 |
| created_at             | timestamp |                                          |
| updated_at             | timestamp |                                          |

### template_questions
| Column              | Type      | Notes                                  |
| ------------------- | --------- | -------------------------------------- |
| id                  | bigint PK |                                        |
| template_section_id | FK        | -> template_sections, cascadeOnDelete  |
| text                | string    |                                        |
| type                | string    | yes_no, text, number, select, photo    |
| options             | json      | nullable (for select type)             |
| is_required         | boolean   | default: true                          |
| order               | integer   | default: 0                             |
| fail_values         | json      | nullable (values that trigger flagging)|
| created_at          | timestamp |                                        |
| updated_at          | timestamp |                                        |

### work_orders
| Column                | Type      | Notes                                    |
| --------------------- | --------- | ---------------------------------------- |
| id                    | bigint PK |                                          |
| order_number          | string    | unique, auto: OT-YYYYMMDD-XXXX          |
| inspection_request_id | FK        | -> inspection_requests, cascadeOnDelete  |
| inspector_id          | FK        | -> users, nullable, nullOnDelete         |
| scheduled_date        | date      | nullable                                 |
| status                | string    | default: 'pending'                       |
| notes                 | text      | nullable                                 |
| started_at            | timestamp | nullable                                 |
| completed_at          | timestamp | nullable                                 |
| created_at            | timestamp |                                          |
| updated_at            | timestamp |                                          |

### work_order_items
| Column                 | Type      | Notes                                    |
| ---------------------- | --------- | ---------------------------------------- |
| id                     | bigint PK |                                          |
| work_order_id          | FK        | -> work_orders, cascadeOnDelete          |
| equipment_id           | FK        | -> equipment, cascadeOnDelete            |
| inspection_template_id | FK        | -> inspection_templates, cascadeOnDelete |
| status                 | string    | default: 'pending'                       |
| notes                  | text      | nullable                                 |
| created_at             | timestamp |                                          |
| updated_at             | timestamp |                                          |

### inspections
| Column                 | Type      | Notes                                    |
| ---------------------- | --------- | ---------------------------------------- |
| id                     | bigint PK |                                          |
| work_order_item_id     | FK        | -> work_order_items, cascadeOnDelete     |
| inspection_template_id | FK        | -> inspection_templates, cascadeOnDelete |
| equipment_id           | FK        | -> equipment, cascadeOnDelete            |
| inspector_id           | FK        | -> users, cascadeOnDelete                |
| status                 | string    | default: 'draft'                         |
| overall_result         | string    | nullable (approved/conditionally_approved/rejected) |
| observations           | text      | nullable                                 |
| score                  | integer   | nullable (0-100)                         |
| started_at             | timestamp | nullable                                 |
| completed_at           | timestamp | nullable                                 |
| created_at             | timestamp |                                          |
| updated_at             | timestamp |                                          |

### inspection_answers
| Column               | Type         | Notes                                   |
| -------------------- | ------------ | --------------------------------------- |
| id                   | bigint PK    |                                         |
| inspection_id        | FK           | -> inspections, cascadeOnDelete         |
| template_question_id | FK           | -> template_questions, cascadeOnDelete  |
| answer_text          | text         | nullable                                |
| answer_boolean       | boolean      | nullable                                |
| answer_number        | decimal(10,2)| nullable                                |
| answer_json          | json         | nullable                                |
| is_flagged           | boolean      | default: false                          |
| notes                | text         | nullable                                |
| created_at           | timestamp    |                                         |
| updated_at           | timestamp    |                                         |

Unique constraint on: `(inspection_id, template_question_id)`

### findings
| Column               | Type      | Notes                                   |
| -------------------- | --------- | --------------------------------------- |
| id                   | bigint PK |                                         |
| inspection_id        | FK        | -> inspections, cascadeOnDelete         |
| template_question_id | FK        | -> template_questions, nullable, nullOnDelete |
| severity             | string    | default: 'low'                          |
| description          | text      |                                         |
| recommendation       | text      | nullable                                |
| is_resolved          | boolean   | default: false                          |
| resolved_at          | timestamp | nullable                                |
| resolved_by          | FK        | -> users, nullable, nullOnDelete        |
| created_at           | timestamp |                                         |
| updated_at           | timestamp |                                         |

### inspection_photos
| Column               | Type      | Notes                                   |
| -------------------- | --------- | --------------------------------------- |
| id                   | bigint PK |                                         |
| inspection_id        | FK        | -> inspections, cascadeOnDelete         |
| template_question_id | FK        | -> template_questions, nullable, nullOnDelete |
| finding_id           | FK        | -> findings, nullable, nullOnDelete     |
| photo_path           | string    |                                         |
| caption              | string    | nullable                                |
| created_at           | timestamp |                                         |
| updated_at           | timestamp |                                         |

### Framework tables (also present)
- **cache** (key, value, expiration)
- **cache_locks** (key, owner, expiration)
- **jobs** (id, queue, payload, attempts, ...)
- **job_batches** (id, name, total_jobs, ...)
- **failed_jobs** (id, uuid, connection, queue, payload, exception, failed_at)

## Entity Relationships

```
Client --< Equipment
Client --< InspectionRequest
ServiceType --< InspectionRequest
User (created_by) --< InspectionRequest
InspectionRequest --< WorkOrder
User (inspector) --< WorkOrder
WorkOrder --< WorkOrderItem
Equipment --< WorkOrderItem
InspectionTemplate --< WorkOrderItem
WorkOrderItem --1 Inspection
InspectionTemplate --< Inspection
Equipment --< Inspection
User (inspector) --< Inspection
Inspection --< InspectionAnswer
TemplateQuestion --< InspectionAnswer
Inspection --< InspectionPhoto
TemplateQuestion --< InspectionPhoto
Finding --< InspectionPhoto
Inspection --< Finding
TemplateQuestion --< Finding
User (resolved_by) --< Finding
InspectionTemplate --< TemplateSection
TemplateSection --< TemplateQuestion
```

## Complete API Endpoint List

All routes are prefixed with `/api`. Protected routes require `Authorization: Bearer {token}` header.

### Authentication (Public)
| Method | Path             | Controller@Method          |
| ------ | ---------------- | -------------------------- |
| POST   | /v1/login        | AuthController@login       |

### Authentication (Protected)
| Method | Path             | Controller@Method          |
| ------ | ---------------- | -------------------------- |
| POST   | /v1/logout       | AuthController@logout      |
| GET    | /v1/me           | AuthController@me          |

### Users
| Method | Path             | Controller@Method          |
| ------ | ---------------- | -------------------------- |
| GET    | /v1/users        | UserController@index       |
| POST   | /v1/users        | UserController@store       |

### Dashboard
| Method | Path                  | Controller@Method          |
| ------ | --------------------- | -------------------------- |
| GET    | /v1/dashboard/stats   | DashboardController@stats  |

### Clients (apiResource)
| Method | Path                  | Controller@Method          |
| ------ | --------------------- | -------------------------- |
| GET    | /v1/clients           | ClientController@index     |
| POST   | /v1/clients           | ClientController@store     |
| GET    | /v1/clients/{id}      | ClientController@show      |
| PUT    | /v1/clients/{id}      | ClientController@update    |
| DELETE | /v1/clients/{id}      | ClientController@destroy   |

### Equipment (apiResource)
| Method | Path                  | Controller@Method             |
| ------ | --------------------- | ----------------------------- |
| GET    | /v1/equipment         | EquipmentController@index     |
| POST   | /v1/equipment         | EquipmentController@store     |
| GET    | /v1/equipment/{id}    | EquipmentController@show      |
| PUT    | /v1/equipment/{id}    | EquipmentController@update    |
| DELETE | /v1/equipment/{id}    | EquipmentController@destroy   |

### Service Types (apiResource)
| Method | Path                      | Controller@Method                |
| ------ | ------------------------- | -------------------------------- |
| GET    | /v1/service-types         | ServiceTypeController@index      |
| POST   | /v1/service-types         | ServiceTypeController@store      |
| GET    | /v1/service-types/{id}    | ServiceTypeController@show       |
| PUT    | /v1/service-types/{id}    | ServiceTypeController@update     |
| DELETE | /v1/service-types/{id}    | ServiceTypeController@destroy    |

### Inspection Requests (apiResource)
| Method | Path                              | Controller@Method                      |
| ------ | --------------------------------- | -------------------------------------- |
| GET    | /v1/inspection-requests           | InspectionRequestController@index      |
| POST   | /v1/inspection-requests           | InspectionRequestController@store      |
| GET    | /v1/inspection-requests/{id}      | InspectionRequestController@show       |
| PUT    | /v1/inspection-requests/{id}      | InspectionRequestController@update     |
| DELETE | /v1/inspection-requests/{id}      | InspectionRequestController@destroy    |

### Inspection Templates (apiResource + custom)
| Method | Path                                           | Controller@Method                        |
| ------ | ---------------------------------------------- | ---------------------------------------- |
| GET    | /v1/inspection-templates                       | InspectionTemplateController@index       |
| POST   | /v1/inspection-templates                       | InspectionTemplateController@store       |
| GET    | /v1/inspection-templates/{id}                  | InspectionTemplateController@show        |
| PUT    | /v1/inspection-templates/{id}                  | InspectionTemplateController@update      |
| DELETE | /v1/inspection-templates/{id}                  | InspectionTemplateController@destroy     |
| POST   | /v1/inspection-templates/{id}/duplicate        | InspectionTemplateController@duplicate   |

### Work Orders (apiResource + custom)
| Method | Path                                | Controller@Method               |
| ------ | ----------------------------------- | ------------------------------- |
| GET    | /v1/work-orders                     | WorkOrderController@index       |
| POST   | /v1/work-orders                     | WorkOrderController@store       |
| GET    | /v1/work-orders/{id}                | WorkOrderController@show        |
| PUT    | /v1/work-orders/{id}                | WorkOrderController@update      |
| DELETE | /v1/work-orders/{id}                | WorkOrderController@destroy     |
| POST   | /v1/work-orders/{id}/start          | WorkOrderController@start       |
| POST   | /v1/work-orders/{id}/complete       | WorkOrderController@complete    |
| GET    | /v1/work-orders/{id}/items          | WorkOrderController@items       |

### Inspections (partial apiResource + custom)
| Method | Path                                    | Controller@Method                  |
| ------ | --------------------------------------- | ---------------------------------- |
| GET    | /v1/inspections                         | InspectionController@index         |
| POST   | /v1/inspections                         | InspectionController@store         |
| GET    | /v1/inspections/{id}                    | InspectionController@show          |
| POST   | /v1/inspections/{id}/answers            | InspectionController@saveAnswers   |
| POST   | /v1/inspections/{id}/submit             | InspectionController@submit        |
| POST   | /v1/inspections/{id}/photos             | InspectionController@uploadPhotos  |
| POST   | /v1/inspections/{id}/findings           | InspectionController@createFinding |

### Findings (apiResource)
| Method | Path                      | Controller@Method             |
| ------ | ------------------------- | ----------------------------- |
| GET    | /v1/findings              | FindingController@index       |
| POST   | /v1/findings              | FindingController@store       |
| GET    | /v1/findings/{id}         | FindingController@show        |
| PUT    | /v1/findings/{id}         | FindingController@update      |
| DELETE | /v1/findings/{id}         | FindingController@destroy     |

**Total: 52 routes** (including PATCH variants generated by apiResource alongside PUT)

## Authentication

- Uses **Laravel Sanctum** with token-based authentication.
- `POST /api/v1/login` accepts `email` + `password`, returns a `token` and `user` object.
- All other routes require `Authorization: Bearer {token}` header (via `auth:sanctum` middleware).
- `POST /api/v1/logout` deletes the current access token.
- `GET /api/v1/me` returns the authenticated user.

## Response Format

All responses use the `ApiResponse` trait with a standardized JSON structure.

### Success response
```json
{
  "success": true,
  "message": "Success message",
  "data": { ... }
}
```

### Paginated response
```json
{
  "success": true,
  "message": "Success message",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  }
}
```

### Error response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

## API Resources (14 total)

| Resource                    | Key includes                                                   |
| --------------------------- | -------------------------------------------------------------- |
| UserResource                | id, name, email, role, timestamps                              |
| ClientResource              | id, name, ruc, address, contact_*, is_active, equipment_count  |
| EquipmentResource           | id, client (nested), name, type, brand, model, year, plate, serial_number, internal_code, status |
| ServiceTypeResource         | id, name, description, is_active                               |
| InspectionRequestResource   | id, request_number, client, service_type, dates, status, notes, creator, work_orders_count |
| InspectionTemplateResource  | id, name, code, description, vehicle_type, is_active, version, sections (nested) |
| TemplateSectionResource     | id, name, order, description, questions (nested)               |
| TemplateQuestionResource    | id, text, type, options, is_required, order, fail_values       |
| WorkOrderResource           | id, order_number, inspection_request, inspector, dates, status, notes, items |
| WorkOrderItemResource       | id, equipment, template, status, notes                         |
| InspectionResource          | id, template, equipment, inspector, work_order_item, status, overall_result, score, observations, answers, photos, findings, timestamps |
| InspectionAnswerResource    | id, template_question_id, answer_*, is_flagged, notes          |
| InspectionPhotoResource     | id, photo_path, photo_url, caption, question_id, finding_id   |
| FindingResource             | id, inspection_id, question, severity, description, recommendation, is_resolved, resolved_at, resolver, photos |

## Seeder Data

The `DatabaseSeeder` creates:

1. **Admin user**: name="Admin", email=admin@americanadvisor.com, password=password, role=admin
2. **Inspector user**: name="Inspector Demo", email=inspector@americanadvisor.com, password=password, role=inspector
3. **Inspection Template** (via InspectionTemplateSeeder):
   - Name: "Inspeccion Camioneta 4x4 - Mineria"
   - Code: INSP-4X4-MIN
   - Vehicle type: camioneta_4x4
   - **10 sections, 63 questions total**:
     - Documentacion del Vehiculo (6 questions)
     - Estado Exterior (6 questions, includes 1 select)
     - Neumaticos y Suspension (7 questions)
     - Sistema de Iluminacion (9 questions)
     - Motor y Mecanica (7 questions, includes 1 number, 1 reverse-flagged)
     - Sistema de Frenos (4 questions)
     - Interior del Vehiculo (6 questions, includes 1 select)
     - Equipamiento de Seguridad (8 questions)
     - Equipamiento Minero Especifico (7 questions)
     - Observaciones Generales (3 questions: text, photo, select)

## Query Filters

Most list endpoints support these query parameters:
- `per_page` (default: 15) -- pagination size
- `search` -- text search (varies by entity)
- Entity-specific filters (e.g., `client_id`, `status`, `inspector_id`, `is_active`, `vehicle_type`, `severity`, `is_resolved`)
