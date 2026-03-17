# Frontend Reference — Panel Admin (Next.js en Vercel)

Última actualización: 2026-03-15

## URL de Deploy

Preview branch: `https://americanadvisor-inspec-git-50720b-gonzalogcaminos2010s-projects.vercel.app/`

## Stack Frontend

- **Framework:** Next.js (App Router)
- **UI:** Tailwind CSS, diseño limpio con sidebar lateral fijo
- **Hosting:** Vercel
- **Auth:** Login con token Sanctum, sesión persistida en el cliente

## Layout General

- **Sidebar izquierdo** fijo (~190px) con logo "American Advisor" (círculo azul "AA")
- **Área principal** con contenido a la derecha
- **Footer del sidebar:** Nombre de usuario + email + botón "Cerrar sesión"
- **Colores:** Fondo blanco/gris claro, sidebar blanco, acentos azules, badges verdes (Activo) y rojos (delete)

## Navegación (Sidebar)

El sidebar está dividido en 3 secciones:

### GESTIÓN
| Ítem | Ruta | Descripción |
|------|------|-------------|
| Dashboard | `/dashboard` | Panel de control principal |
| Clientes | `/clients` | CRUD de empresas mineras |
| Equipos | `/equipment` | CRUD de vehículos/maquinaria |

### OPERACIONES
| Ítem | Ruta | Descripción |
|------|------|-------------|
| Solicitudes | `/inspection-requests` | Solicitudes de inspección |
| Órdenes de Trabajo | `/work-orders` | Órdenes asignadas a inspectores |

### INSPECCIONES
| Ítem | Ruta | Descripción |
|------|------|-------------|
| Plantillas | `/templates` | Plantillas de inspección configurables |
| Inspecciones | `/inspections` | Listado y detalle de inspecciones |
| Hallazgos | `/findings` | Hallazgos/findings reportados |

## Pantallas Documentadas

### 1. Dashboard (`/dashboard`)
- **Header:** "PANEL DE CONTROL" + "Bienvenido, {nombre}" + "Última actualización: ahora" con botón refresh
- **4 cards de estadísticas:** Clientes registrados, Equipos registrados, Inspecciones (X este mes), Órdenes Pend. (X por revisar)
- **Acciones Rápidas:** 4 botones — Nueva Solicitud, Nueva Orden, Nueva Plantilla, Ver Inspecciones
- **Inspecciones Recientes:** Lista simple con nombre equipo + status (in_progress, submitted, etc.)
- **Resumen Operativo:** 3 métricas con iconos — Órdenes Pendientes, Por Revisar, Inspecciones Este Mes
- **Nota:** El stat "Por Revisar" corresponde a `pending_reviews` del API (inspecciones con status `submitted`)

### 2. Clientes (`/clients`)
- **Header:** "Clientes" + botón "Nuevo Cliente" (azul, esquina derecha)
- **Buscador:** Input "Buscar clientes..."
- **Tabla con columnas:** CÓDIGO, NOMBRE, CUIT/NIT, EMAIL, TELÉFONO, CONTACTO, ESTADO, ACCIONES
- **Estado:** Badge verde "Activo"
- **Acciones por fila:** Ver (botón amarillo con ojo), Editar (icono lápiz), Eliminar (icono papelera rojo)
- **Dato de ejemplo:** Minera Los Andes S.A., CUIT 20512345678, contacto Juan Perez

### 3. Equipos (`/equipment`)
- **Header:** "Equipos" + subtítulo "Gestiona los equipos registrados" + botón "Nuevo Equipo"
- **Filtros:** Buscador + dropdown "Filtrar por cliente"
- **Tabla con columnas:** CÓDIGO, NOMBRE, CLIENTE, MODELO, N° SERIE, ESTADO, ACCIONES
- **Acciones por fila:** Editar (lápiz), Eliminar (papelera)
- **Sin botón "Ver"** — a diferencia de Clientes
- **Datos de ejemplo:** Toyota Hilux ABC-123, Ford Ranger XYZ-456 (ambos de Minera Los Andes S.A.)

### 4. Solicitudes de Inspección (`/inspection-requests`)
- **Header:** "Solicitudes de Inspección" + subtítulo + botón "Nueva Solicitud"
- **Filtros:** Buscador por número + dropdown "Todos los estados" + dropdown "Todos los clientes"
- **Tabla con columnas:** N° SOLICITUD, CLIENTE, SERVICIO, TIPO, FECHA, ESTADO, PRIORIDAD, ACCIONES
- **Estado:** texto "pending"
- **Prioridad:** Badge naranja "Media"
- **Acciones por fila:** Ver (amarillo), Editar (lápiz), Eliminar (papelera)
- **Números auto-generados:** REQ-YYYYMMDD-XXXX (ej: REQ-20260315-0001)

### 5. Órdenes de Trabajo (`/work-orders`)
- **Header:** "Ordenes de Trabajo" + botón "Nueva Orden"
- **Filtros:** Buscador + dropdown "Todos los estados" + dropdown "Todos los inspectores"
- **Tabla con columnas:** N° ORDEN, SOLICITUD, EQUIPOS, INSPECTOR, FECHA PROGRAMADA, ESTADO, PRIORIDAD, ACCIONES
- **Acciones por fila:** Ver (botón amarillo con ojo)
- **Sin editar/eliminar** desde el listado
- **Números auto-generados:** OT-YYYYMMDD-XXXX

### 5b. Detalle de Orden (`/work-orders/{id}`)
- **Header:** "Orden OT-20260315-0001" + subtítulo "REQ-20260315-0001" + badge status arriba derecha
- **4 cards de info:** Equipos (cantidad + lista), Inspector (nombre), Fecha Programada, Cliente
- **Sección NOTAS:** Texto libre
- **Botón:** "Volver a Ordenes"
- **Observación:** No muestra los items individuales (work_order_items) ni permite gestionar inspecciones desde aquí. Falta funcionalidad.

### 6. Plantillas de Inspección (`/templates`)
- **Header:** "Plantillas de Inspección" + botón "Nueva Plantilla"
- **Buscador:** Input "Buscar plantillas..."
- **Tabla con columnas:** NOMBRE, CÓDIGO, CATEGORÍA, VERSIÓN, ESTADO, SECCIONES, ACCIONES
- **Estado:** Badge verde "Activo"
- **Secciones:** Muestra "0" — probablemente bug, debería mostrar 10 (la plantilla tiene 10 secciones con 63 preguntas)
- **Acciones por fila:** Ver (ojo), Editar (lápiz), Duplicar (icono copiar), Desactivar (toggle)

### 7. Inspecciones (`/inspections`)
- **Header:** "Inspecciones"
- **Filtros:** Buscador + dropdown "Todos los estados"
- **Tabla con columnas:** #, ORDEN, EQUIPO, PLANTILLA, INSPECTOR, ESTADO, RESULTADO, FECHA, ACCIONES
- **Estado:** Texto (submitted, in_progress) — sin badges de color
- **Resultado:** Texto "conditionally_approved" o "-"
- **Acciones:** Icono de ojo (ver detalle)
- **No hay botón "Nueva Inspección"** — las inspecciones se crean desde work_order_items (vía API)

### 7b. Detalle de Inspección (`/inspections/{id}`)
- **Header:** "Inspeccion #{id}" + subtítulo con nombre plantilla + badges de status y resultado arriba derecha
- **4 cards de info:** Inspector (nombre), Orden de Trabajo (OT #id), Inicio (fecha+hora), Finalización (- si no completada)
- **Botones:** "Volver" (gris) + "Generar Reporte" (rojo/coral)
- **Observaciones importantes:**
  - NO muestra las respuestas de la inspección (secciones/preguntas/answers)
  - NO muestra fotos ni hallazgos
  - NO tiene botones de Aprobar/Devolver para el supervisor
  - NO muestra información de aprobación (approved_by, supervisor_notes)
  - La vista de detalle es muy básica — solo metadata

### 8. Hallazgos (`/findings`)
- **Header:** "Hallazgos"
- **Filtros:** Buscador + dropdown "Todas las severidades" + dropdown "Todos los estados"
- **Tabla con columnas:** DESCRIPCIÓN, INSPECCIÓN, SEVERIDAD, ESTADO, RECOMENDACIÓN, RESUELTO, ACCIONES
- **Estado:** Badge naranja "Abierto"
- **Severidad:** Texto "high"
- **Resuelto:** "No"
- **Acciones:** Editar (lápiz), Abrir/Ver (icono externo)

## Lo que NO existe aún en el frontend

### Panel del Inspector
No hay un panel diferenciado para el rol inspector. Actualmente todo se ve como admin. Se necesita:
- Vista de "Mis Órdenes de Trabajo" asignadas
- Flujo de inspección en campo: abrir OT → seleccionar item → responder preguntas sección por sección → tomar fotos → registrar hallazgos → submit
- Vista simplificada sin acceso a gestión (clientes, equipos, plantillas)

### Panel del Supervisor
No hay panel supervisor. Se necesita:
- Bandeja de inspecciones pendientes de revisión (status: submitted)
- Vista detallada de inspección con todas las respuestas, fotos y hallazgos
- Botones Aprobar / Devolver con formulario de notas
- Dashboard con métricas de aprobación

### Funcionalidades faltantes en pantallas existentes
1. **Detalle de Inspección** — No muestra respuestas, fotos ni hallazgos. Solo metadata básica.
2. **Detalle de Orden** — No muestra los work_order_items individuales ni permite iniciar inspecciones.
3. **Plantillas** — El contador de secciones muestra "0" (posible bug en el front).
4. **Inspecciones listado** — Los estados no tienen badges de color (submitted, in_progress son texto plano).
5. **Generación de Reporte PDF** — El botón "Generar Reporte" existe pero no hay backend de PDF aún.

### App Flutter (Inspector Móvil)
No existe aún. Será la herramienta principal del inspector en campo:
- Login con credenciales de inspector
- Ver órdenes de trabajo asignadas
- Ejecutar inspecciones pregunta por pregunta
- Capturar fotos con la cámara del dispositivo
- Registrar hallazgos con severidad
- Submit de inspección
- Modo offline (deseable)

## Patrones de UI Observados

### Consistencias
- Todas las listas usan tablas con headers en mayúsculas y gris
- Buscadores siempre arriba a la izquierda
- Botón de crear siempre arriba a la derecha (azul con texto blanco)
- Filtros con dropdowns a la derecha del buscador
- Badge "Activo" siempre verde, "Abierto" siempre naranja
- Acciones con iconos: ojo=ver, lápiz=editar, papelera=eliminar

### Inconsistencias detectadas
- Algunos listados tienen botón "Ver" como botón amarillo (Clientes, Solicitudes), otros solo icono de ojo (Inspecciones)
- Equipos no tiene botón Ver, solo Editar y Eliminar
- Los status de inspección se muestran como texto plano sin color
- El detalle de inspección es mucho más básico que el de orden de trabajo

## Endpoints API que el Frontend Consume

Basado en las pantallas, el frontend usa al menos:
- `GET /api/v1/dashboard/stats` — Dashboard
- `GET/POST /api/v1/clients` — Clientes CRUD
- `GET/POST /api/v1/equipment` — Equipos CRUD
- `GET/POST /api/v1/inspection-requests` — Solicitudes CRUD
- `GET/POST /api/v1/work-orders` — Órdenes CRUD
- `GET /api/v1/work-orders/{id}` — Detalle orden
- `GET /api/v1/inspection-templates` — Plantillas
- `GET /api/v1/inspections` — Listado inspecciones
- `GET /api/v1/inspections/{id}` — Detalle inspección
- `GET /api/v1/findings` — Hallazgos

### Endpoints NO consumidos aún por el frontend
- `POST /api/v1/inspections/{id}/answers` — Guardar respuestas
- `POST /api/v1/inspections/{id}/submit` — Enviar inspección
- `POST /api/v1/inspections/{id}/approve` — Aprobar (supervisor)
- `POST /api/v1/inspections/{id}/return` — Devolver (supervisor)
- `POST /api/v1/inspections/{id}/photos` — Subir fotos
- `POST /api/v1/inspections/{id}/findings` — Crear hallazgo desde inspección
- `POST /api/v1/inspection-templates/{id}/duplicate` — Duplicar plantilla (botón existe pero no verificado)
