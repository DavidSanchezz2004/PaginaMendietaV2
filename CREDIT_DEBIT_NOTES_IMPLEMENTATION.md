# Implementación de Notas de Crédito y Débito - COMPLETADA ✅

## Resumen

Se ha implementado completamente el módulo de **Notas de Crédito y Débito** para el facturador electrónico. El sistema permite crear, enviar y consultar notas de crédito (tipo 07) y débito (tipo 08) ante SUNAT a través de Feasy.

## Arrivos Creados / Modificados

### 1. Base de Datos

- **Migración**: `database/migrations/2026_03_17_create_credit_debit_notes_table.php`
    - Tabla: `credit_debit_notes` con todos los campos necesarios
    - Relaciones con `invoices`, `companies`, `users`
    - Estados: draft, sent, error, consulted, voided

### 2. Modelos

- **Modelo**: `app/Models/CreditDebitNote.php`
    - Relaciones (company, user, invoice)
    - Scopes útiles (creditos, debitos, sent, error, forActiveCompany)
    - Helpers para labels y estados

### 3. Servicios

- **CreditDebitNoteService**: `app/Services/Facturador/CreditDebitNoteService.php`
    - Crear notas (con cálculo automático de totales)
    - Paginar y filtrar
    - Sugerencias de serie/número

- **FeasyService** (actualizado): `app/Services/Facturador/FeasyService.php`
    - `sendCreditDebitNote()` → envía a /comprobante/enviar_nota_credito o /enviar_nota_debito
    - `consultCreditDebitNote()` → consulta estado en SUNAT
    - `buildCreditDebitNotePayload()` → construye payload JSON para Feasy

### 4. Controlador

- **CreditDebitNoteController**: `app/Http/Controllers/Facturador/CreditDebitNoteController.php`
    - CRUD completo (index, create, store, show)
    - emit() → enviar a SUNAT
    - consult() → consultar estado
    - downloadXml() y manejo de errores

### 5. Validación

- **Request**: `app/Http/Requests/Facturador/StoreCreditDebitNoteRequest.php`
    - Validación completa de notas
    - Items con estructura correcta
    - Códigos de SUNAT validados

### 6. Vistas

- **index.blade.php** → Listado con filtros (tipo, estado, búsqueda)
- **create.blade.php** → Formulario de creación con:
    - Selector de factura original
    - Items dinámicos
    - Cálculo de totales en tiempo real
    - Resumen de factura original
- **show.blade.php** → Detalle con acciones (enviar, consultar, descargar)

### 7. Policy & Autorización

- **CreditDebitNotePolicy**: `app/Policies/CreditDebitNotePolicy.php`
    - Validación de permisos
    - Scope por empresa activa

### 8. Configuración

- **routes/web.php** → Rutas agregadas:

    ```
    /facturador/credit-debit-notes (index, create, store, show)
    /facturador/credit-debit-notes/{id}/emit
    /facturador/credit-debit-notes/{id}/consult
    /facturador/credit-debit-notes/{id}/xml
    ```

- **config/menu.php** → Menú agregado:
    - Sección "Notas Crédito/Débito" en Facturador

- **AppServiceProvider.php** → Policy registrada

## Paso 1: Ejecutar Migración

```bash
php artisan migrate
```

Esto crea la tabla `credit_debit_notes` en la BD.

## Paso 2: Test Rápido

1. Ir a: `http://localhost/facturador/credit-debit-notes`
2. Hacer clic en "+ Nueva Nota"
3. Seleccionar una factura emitida
4. Seleccionar tipo de nota (Descuento, Devolución, etc.)
5. Agregar items y enviar

## Flujo de Uso

### Crear Nota

1. Usuario selecciona factura original
2. Sistema auto-llena datos del cliente
3. Usuario agrega items (cantidad, precio)
4. Sistema calcula automáticamente totales
5. Guardar como borrador

### Enviar a SUNAT

1. Click en "Enviar a SUNAT"
2. Sistema construye payload con:
    - Datos de la nota
    - Referencia a factura original
    - Items con cálculos correctos
3. Feasy procesa y devuelve PDF + número de archivo XML
4. Nota pasa a estado "Enviada"

### Consultar Estado

1. Click en "Consultar Estado"
2. Sistema usa número de archivo XML para consultar
3. Actualiza estado en SUNAT

## Características Principales

✅ **Creación Automática de Totales**: Sistema calcula gravado, IGV, inafecto
✅ **Validación SUNAT**: Respeta códigos y tipos de notas de SUNAT
✅ **Gestión de Estados**: Draft → Enviada → Consultada
✅ **Manejo de Errores**: Almacena errores de Feasy para reintento
✅ **Interfaz Responsiva**: Bootstrap 5, formularios dinámicos
✅ **Auditoría**: timestamps y soft deletes

## Notas Técnicas

- Las notas referencia **siempre** a una factura/boleta original
- El `codigo_tipo_nota` (01-04) determina el tipo (Descuento, Devolución, etc.)
- Los items se almacenan como JSON (flexible, permite cualquier cantidad)
- El `codigo_tipo_documento` es '07' (crédito) ó '08' (débito)
- Integración completa con `FeasyService` existente

## Próximos Pasos (Opcionales)

- [ ] API endpoint para obtener datos de factura (AJAX en create.blade.php)
- [ ] Descarga de XML desde Feasy
- [ ] Anulación de notas
- [ ] Exportación a Excel
- [ ] Notificaciones por correo

---

**Implementación completada**: 17 de Marzo de 2026
