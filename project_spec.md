# Project Specification - American Advisor Inspecciones

## Project Name

American Advisor Inspecciones (api-inspeccion)

## Purpose

Mining vehicle inspection management system. Enables companies (clients) in the mining sector to request inspections of their fleet vehicles (camionetas 4x4, trucks, heavy equipment). Inspectors perform structured inspections using configurable templates, record answers, upload photos, flag findings, and generate pass/fail results.

## Actors

### Admin
- Manages all entities: clients, equipment, service types, users, inspection templates
- Creates and manages inspection requests and work orders
- Views dashboard statistics
- Can create inspector users

### Inspector
- Assigned to work orders
- Performs inspections: answers template questions, uploads photos, creates findings
- Submits inspections for automatic result calculation

## Core Workflow

```
Client (mining company with vehicles)
  |
  v
InspectionRequest (REQ-YYYYMMDD-XXXX)
  |
  v
WorkOrder (OT-YYYYMMDD-XXXX) -- assigned to an Inspector
  |-- WorkOrderItem (equipment + template pair)
  |-- WorkOrderItem (equipment + template pair)
  |
  v
Inspection (per WorkOrderItem, performed by Inspector)
  |-- InspectionAnswers (one per template question)
  |-- InspectionPhotos (attached to questions or findings)
  |-- Findings (issues discovered, with severity)
  |
  v
Submit --> auto-calculates score + overall_result
```

### Step-by-step:

1. **Admin creates a Client** with contact info and RUC (tax ID).
2. **Admin registers Equipment** (vehicles) belonging to that client, with plate, serial number, brand, model, year.
3. **Admin creates a ServiceType** (e.g., "Inspeccion Pre-Uso", "Inspeccion Anual").
4. **Admin creates an InspectionRequest** for a client + service type. The system auto-generates request number as `REQ-YYYYMMDD-XXXX`.
5. **Admin creates a WorkOrder** from the request, assigning an inspector and listing items (each item = one equipment + one inspection template). The system auto-generates order number as `OT-YYYYMMDD-XXXX`.
6. **Inspector starts the WorkOrder** (status changes to `in_progress`).
7. **Inspector creates an Inspection** for each WorkOrderItem. This sets the item status to `in_progress`.
8. **Inspector answers questions** from the template (yes/no, text, number, select, photo types). Answers are saved with automatic flag detection.
9. **Inspector uploads photos** and/or **creates findings** for issues found.
10. **Inspector submits the inspection** -- the system auto-calculates the result.
11. **Inspector completes the WorkOrder** once all items are completed/skipped.

## Business Rules

### Auto-generated Numbers
- **Inspection Request numbers**: `REQ-YYYYMMDD-XXXX` where XXXX is zero-padded count+1 of all requests.
- **Work Order numbers**: `OT-YYYYMMDD-XXXX` where XXXX is zero-padded count+1 of all work orders.

### Work Order Completion
- A work order can only be marked as `completed` when **all** its items have status `completed` or `skipped`.
- Attempting to complete with pending/in-progress items returns a 422 error.

### Flag Detection (is_flagged)
- Applied during `saveAnswers` for `yes_no` type questions.
- The `answer_boolean` value is cast to a string: `true` becomes `"1"`, `false` becomes `"0"`.
- This string is checked against the question's `fail_values` array (e.g., `["0"]` means "No" is a failure).
- If the string is found in `fail_values`, the answer is flagged (`is_flagged = true`).
- Note: The question "Fugas visibles" (visible leaks) has `fail_values: ["1"]` -- answering "Yes" (true/1) is a failure.

### Overall Result Calculation (on submit)
- Only `yes_no` type answers are considered for scoring.
- Count total yes_no answers and how many are flagged.
- **0 flagged** --> `approved`
- **Flagged <= 30% of total** --> `conditionally_approved`
- **Flagged > 30% of total** --> `rejected`
- **Score** = `round(((total - flagged) / total) * 100)`
- If there are no yes_no answers, result is `approved` with score `100`.

### Template Sync (PUT update)
- When updating an inspection template via PUT:
  - Sections/questions **with an `id`** field --> updated in place.
  - Sections/questions **without an `id`** field --> created as new.
  - Existing sections/questions **not present** in the request --> deleted.
- This is a full sync pattern, not a partial patch.

### Template Duplication
- Creates a deep copy of template + sections + questions.
- New code = `{original_code}-COPY-{timestamp}`.
- Version is incremented by 1.
- Duplicate starts as `is_active = false`.

### Photo Storage
- Photos are stored on the `public` disk at: `storage/public/inspections/{inspection_id}/`
- Max file size: 5120 KB (5 MB) per photo.
- Photos can be associated with a specific template question and/or a finding.

### Statuses
- **InspectionRequest**: `pending` (default), and other string values.
- **WorkOrder**: `pending` (default), `in_progress`, `completed`.
- **WorkOrderItem**: `pending` (default), `in_progress`, `completed`, `skipped`.
- **Inspection**: `draft` (default from migration), `in_progress` (set on create), `completed` (set on submit).
- **Equipment**: `active` (default), and other string values.
- **Finding**: `is_resolved` boolean, with `resolved_at` timestamp and `resolved_by` user.

### Finding Severity
- Free-form string field (e.g., "low", "medium", "high", "critical").
- Default value in migration: `low`.

### Finding Resolution
- When `is_resolved` is set to `true`, `resolved_at` is auto-set to now and `resolved_by` to the current user.
- When `is_resolved` is set back to `false`, both fields are cleared to null.
