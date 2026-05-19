# Project State - api-inspeccion

Last updated: 2026-05-19 (Phase 13)

## What Has Been Implemented

### Phase 1: Foundation
- Laravel 12 project initialized with SQLite database
- Sanctum authentication installed and configured
- `ApiResponse` trait for standardized JSON responses (`app/Traits/ApiResponse.php`)
- User model extended with `role` field and `HasApiTokens` trait

### Phase 2: Core Models & Migrations (14 domain tables)
- Client (mining companies)
- Equipment (vehicles belonging to clients, with `metadata` JSON field for dynamic type-specific fields)
- ServiceType (types of inspection services)
- InspectionRequest (requests from clients, auto-numbered REQ-YYYYMMDD-XXXX)
- InspectionTemplate / TemplateSection / TemplateQuestion (configurable inspection forms)
- WorkOrder / WorkOrderItem (work assignments for inspectors, auto-numbered OT-YYYYMMDD-XXXX)
- Inspection / InspectionAnswer / InspectionPhoto / Finding (inspection execution and results)

### Phase 3: API Controllers (11 controllers)
- AuthController: login, logout, me
- UserController: index, store
- DashboardController: stats (counts + recent inspections)
- ClientController: full CRUD
- EquipmentController: full CRUD (accepts `metadata` JSON field)
- ServiceTypeController: full CRUD
- InspectionRequestController: full CRUD
- InspectionTemplateController: full CRUD + duplicate
- WorkOrderController: full CRUD + start, complete, items
- InspectionController: index, show, store, saveAnswers, submit, approve, returnInspection, uploadPhotos, createFinding, sign
- FindingController: full CRUD with resolution tracking

### Phase 4: API Resources (14 resources)
- All models have corresponding API Resource classes for consistent JSON serialization
- Nested eager-loading of relationships in resource responses

### Phase 5: Business Logic
- Auto-generated request/order numbers
- Template sync (PUT) with create/update/delete of nested sections and questions
- Template duplication (deep copy)
- Flag detection on yes_no answers against fail_values
- Overall result calculation on inspection submit (approved / conditionally_approved / rejected)
- Score calculation: ((total - flagged) / total) * 100
- Work order completion guard (all items must be completed/skipped)
- Supervisor approval workflow: submit → submitted (pending review) → approved (completed) or returned (inspector corrects)
- CheckRole middleware for role-based route protection (supervisor, admin)
- Finding resolution tracking (resolved_at, resolved_by auto-set)
- Photo upload to public storage
- Signature collection: inspector, supervisor, and client can sign completed inspections (base64 PNG → stored in `signatures/{inspection_id}/`). `all_signatures_complete` computed field tracks when all 3 are done.

### Phase 6: Seeders
- DatabaseSeeder creates 3 users (admin + supervisor + inspector)
- InspectionTemplateSeeder creates a complete mining 4x4 inspection template with 10 sections, 63 questions
- LenorGruaArticuladaTemplateSeeder creates a LENOR articulated boom crane template with 20 sections, ~100 questions (code: INSP-GRUA-ART)

### Phase 7: Code Quality
- Laravel Pint formatting applied across codebase

### Phase 8: PDF Report Generation
- `barryvdh/laravel-dompdf` v3.1.2 installed
- `InspectionReportController` with two endpoints:
  - `GET /inspections/{id}/report` — generates final PDF (only for submitted/completed inspections)
  - `GET /inspections/{id}/report/preview` — generates preview PDF with watermark (any status)
- Blade template at `resources/views/reports/informe-preliminar.blade.php` (R3 PEAT 01 REV.07 format)
- Includes: client/equipment data, inspection answers by section, findings, observations, supervisor notes, signatures
- Logo placeholder at `public/images/logo-american-advisor.png` (replace with real logo)

### Phase 9: Certificate PDF + QR Verification
- Certificate auto-generated on supervisor approval: `certificate_number` (CERT-YYYYMMDD-XXXX), `certificate_issued_at`, `qr_token` (UUID)
- Migration adds 3 columns to `inspections` table
- `GET /inspections/{id}/certificate` — generates landscape A4 certificate PDF with embedded QR code (auth required, completed only)
- `GET /api/v1/public/inspections/{qrToken}` — public endpoint for certificate verification via QR scan (no auth)
- QR code generated with `chillerlan/php-qrcode` v6 (GD image PNG, base64 embedded in PDF)
- Certificate Blade template at `resources/views/reports/certificado-inspeccion.blade.php` (LENOR-style format)
- `barryvdh/laravel-dompdf` moved from require-dev to require (production dependency)
- LENOR crane template seeder: `LenorGruaArticuladaTemplateSeeder` — 20 sections, ~100 questions for articulated boom crane inspection (code: INSP-GRUA-ART)

### Phase 10: API Documentation (Swagger)
- OpenAPI 3.0.0 specification created at `public/docs/api-docs.json` (109KB)
- Swagger UI served at `http://localhost:8000/docs/index.html` (CDN-based, no package dependency)
- All 54+ endpoints documented with request/response schemas
- 30+ component schemas (model resources, request bodies, enums)
- 11 tag groups matching controller organization
- Try-it-out enabled for interactive API testing
- Bearer token auth configured in Swagger UI

## What Has Been Verified

- All 19 migrations run successfully
- Database seeder runs without errors (3 users + 2 templates: mining 4x4 with 10 sections/63 questions + LENOR crane with 20 sections/~100 questions)
- 59 routes registered (confirmed via `php artisan route:list`)
- Code formatted with Laravel Pint
- Swagger UI accessible at `http://localhost:8000/docs/index.html`

## Default Credentials

| Role       | Email                             | Password |
| ---------- | --------------------------------- | -------- |
| Admin      | admin@americanadvisor.com         | password |
| Supervisor | supervisor@americanadvisor.com    | password |
| Inspector  | inspector@americanadvisor.com     | password |

## What Is NOT Yet Done / Potential Next Steps

### Testing
- **No automated tests written.** PestPHP is installed (`composer run test` works, reports 0 tests). Feature and unit test directories exist but are empty/default.
- Priority tests to write: authentication, inspection workflow (create -> answer -> submit -> result calculation), work order completion guard, template sync CRUD, flag detection logic.

### Validation
- **No FormRequest validation classes.** All validation is inline in controller methods using `$request->validate()`.
- Extracting to FormRequest classes would improve reusability, testability, and separation of concerns.

### Authorization / Access Control
- **Partial role-based access control.** A `CheckRole` middleware exists and is applied to supervisor-only routes (approve, return inspections). However, most endpoints are still unprotected -- any authenticated user can access admin-only actions (user management, client CRUD, template management).
- Needed: apply `role` middleware more broadly to restrict admin-only endpoints from inspectors.

### Notifications
- **No email notifications** implemented. No notification classes, no mail configuration beyond defaults.
- Potential: notify admin when inspection is submitted, notify inspector when assigned to a work order.

### Reporting
- PDF report generation implemented (barryvdh/laravel-dompdf). Two endpoints: final report + preview.
- Potential: add photo thumbnails in PDF, PDF download (vs stream), batch report generation.

### File Management
- **No file cleanup/pruning for photos.** Deleted inspections leave orphaned files on disk.
- The `storage:link` command should be run in production to make public storage accessible.

### CORS
- **CORS uses Laravel defaults.** Not explicitly configured for `*` origins. The default `config/cors.php` may need adjustment for frontend SPA consumption.

### Rate Limiting
- **No rate limiting configured** beyond Laravel defaults. The login endpoint is not rate-limited.

### API Documentation
- Swagger/OpenAPI 3.0.0 spec implemented at `public/docs/api-docs.json`
- Swagger UI at `http://localhost:8000/docs/index.html`
- The spec is manually maintained (not auto-generated). When adding/modifying endpoints, update `api-docs.json` manually.
- Potential improvement: install `dedoc/scramble` for auto-generated docs from code annotations.

### Phase 11: Template Categories CRUD (2026-05-19)
- New `template_categories` table: `id`, `code` (unique slug), `name`, `is_active`, timestamps
- `TemplateCategorySeeder` creates 10 default categories (vehiculo_liviano, camioneta_4x4, perforadora_diamantina, equipo_pesado_mineria, grua_izaje, compresor, generador, instalacion_electrica, instalacion_industrial, otro)
- 4 endpoints under `/template-categories`:
  - `GET` (all authenticated, filter `?active=true`, paginated default 100/page)
  - `POST` (admin only, validates slug format `[a-z0-9_]+`, returns 409 if code exists)
  - `PATCH` (admin only, updates `name`/`is_active`, `code` immutable)
  - `DELETE` (admin only, soft-deletes if `inspection_templates.vehicle_type` references the code, hard-deletes otherwise, returns 204)
- Categories link to templates via `inspection_templates.vehicle_type` (no schema change to that column)

### Phase 12: Reopen Inspection (2026-05-19)
- New endpoint `POST /inspections/{id}/reopen`
- Only the owning inspector (`inspector_id === user.id`) can reopen — supervisor/admin cannot
- Allowed source states: `submitted`, `returned`. Other states → 409
- Effect: `status = in_progress`, `completed_at = null`. Answers/findings/photos preserved.

### Phase 13: AI Photo Analysis — Asistencia IA RI-03 fase 1 (2026-05-19)
- New `ai_analyses` table: `id`, `photo_id` FK, `inspection_id` FK (denorm), `requested_by_user_id` FK, `model`, `prompt_version`, `response_json`, `has_defect`, `severity`, `used_by_user`, `latency_ms`, timestamps. Index on (inspection_id, created_at).
- `POST /api/v1/ai/analyze-photo` — body `{ photo_id }`. Reads photo from disk `public`, base64-encodes, calls Anthropic Messages API with vision (model `claude-sonnet-4-6` by default). System + user prompts in Spanish, requesting strict JSON output: `{ has_defect, title, description, severity, defect_type, observations }`. Retries once on malformed JSON; 502 if both attempts fail.
- `PATCH /api/v1/ai/analyses/{id}/mark-used` — body empty. Only the requester can mark used. Returns 204.
- Service `App\Services\Ai\PhotoAnalysisService` encapsulates the Anthropic call + retry + JSON extraction. Latency measured via `microtime`.
- Config in `config/services.php` → `anthropic.api_key`, `anthropic.photo_analysis_enabled`, `anthropic.model`. Env vars: `ANTHROPIC_API_KEY`, `AI_PHOTO_ANALYSIS_ENABLED`, `AI_PHOTO_ANALYSIS_MODEL`.
- Permission rules: admin/supervisor analyze any photo; inspector only photos of inspections they are assigned to.
- Limits: photo >10MB → 422. Disabled flag or missing API key → 503.
- 12 Pest tests covering all spec cases (in `tests/Feature/AiAnalysisTest.php`).

### Decisions & Investigations Log
- **"Solicitado por" field (2026-03-19):** Frontend had a text input for "Solicitado por" in the inspection request form. Investigation confirmed backend already handles this automatically — `created_by` FK is set to `$request->user()->id` on creation, and `InspectionRequestResource` returns `creator` (UserResource) when the relation is loaded. **No backend changes needed.** Frontend team notified to remove the text field and display the logged-in user's name as read-only.
- **Template categories link field (2026-05-19):** Spec referenced an `inspection_templates.category` column but the actual column is `vehicle_type`. Decision: keep `vehicle_type` as the link field (no migration). Categories' `code` matches `inspection_templates.vehicle_type` string.
- **Status casing (2026-05-19):** Spec used UPPERCASE statuses (`SUBMITTED`, `IN_PROGRESS`, `RETURNED`) but codebase uses lowercase. Mapped accordingly: `submitted`, `in_progress`, `returned`.
- **Reopen permission scope (2026-05-19):** Decided to restrict reopen to the owning inspector only. Admin/supervisor cannot reopen — they should use `return` instead to send back to inspector.

### Other Potential Improvements
- Soft deletes on key models (clients, equipment, inspections)
- Audit logging / activity log
- Inspection template versioning (currently tracked with `version` field but no version history)
- Bulk operations (bulk answer submission is supported, but no bulk status updates)
- Search improvements (full-text search, Algolia/Meilisearch)
- Caching layer for dashboard stats and template data
- Queue jobs for heavy operations (report generation, notifications)
- API versioning strategy (v1 prefix exists but no version negotiation)
