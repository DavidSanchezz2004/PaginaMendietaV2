# 📡 Testing API - Cotizaciones

## Configuración Inicial

### Headers Requeridos

```
Content-Type: application/json
Accept: application/json
X-CSRF-Token: {token_from_form}
```

### Autenticación

- **Admin endpoints:** Requieren session/sanctum token
- **Public endpoints:** Solo share_token en URL

---

## 🧪 Ejemplos de Testing

### 1️⃣ CREAR COTIZACIÓN (POST)

**Endpoint:** `POST /facturador/cotizaciones`

**Headers:**

```
Content-Type: application/json
X-CSRF-Token: {csrf_token}
```

**Payload:**

```json
{
  "client_id": 1,
  "fecha_emision": "2024-01-15",
  "fecha_vencimiento": "2024-02-15",
  "codigo_moneda": "PEN",
  "porcentaje_igv": 18,
  "observacion": "Cotización válida por 30 días",
  "items_json": "[
    {
      \"descripcion\": \"Desarrollo Web\",
      \"cantidad\": 1,
      \"monto_valor_unitario\": 5000,
      \"codigo_unidad_medida\": \"UND\",
      \"monto_valor_total\": 5000,
      \"codigo_indicador_afecto\": \"10\"
    },
    {
      \"descripcion\": \"Hosting Anual\",
      \"cantidad\": 1,
      \"monto_valor_unitario\": 1200,
      \"codigo_unidad_medida\": \"UND\",
      \"monto_valor_total\": 1200,
      \"codigo_indicador_afecto\": \"10\"
    }
  ]"
}
```

**Respuesta (201 Created):**

```json
{
    "id": 5,
    "numero_cotizacion": "COT-2024-00005",
    "codigo_interno": "COT-2024-00005-v1",
    "estado": "draft",
    "version": 1,
    "share_token": null,
    "invoice_id": null,
    "company_id": 1,
    "user_id": 1,
    "client_id": 1,
    "fecha_emision": "2024-01-15",
    "fecha_vencimiento": "2024-02-15",
    "codigo_moneda": "PEN",
    "porcentaje_igv": 18,
    "monto_total_gravado": 6200,
    "monto_total_igv": 1116,
    "monto_total_descuento": 0,
    "monto_total": 7316,
    "observacion": "Cotización válida por 30 días",
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
}
```

### 2️⃣ VER LISTADO (GET)

**Endpoint:** `GET /facturador/cotizaciones?estado=draft&search=cliente`

**Parámetros:**

```
estado: draft|sent|accepted|rejected  (opcional)
search: {string}                      (búsqueda en numero/cliente)
page: {int}                           (paginación)
```

**Respuesta (200 OK):**

```json
{
    "data": [
        {
            "id": 5,
            "numero_cotizacion": "COT-2024-00005",
            "estado": "draft",
            "version": 1,
            "cliente": "Empresa XYZ",
            "moneda": "PEN",
            "total": 7316,
            "created_at": "2024-01-15T10:30:00Z"
        }
    ],
    "links": {
        "first": "...",
        "last": "...",
        "next": "...",
        "prev": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

### 3️⃣ VER DETALLE (GET)

**Endpoint:** `GET /facturador/cotizaciones/5`

**Respuesta (200 OK):**

```json
{
    "id": 5,
    "numero_cotizacion": "COT-2024-00005",
    "estado": "draft",
    "client": {
        "id": 1,
        "nombre_cliente": "Empresa XYZ",
        "numero_documento": "20123456789"
    },
    "items": [
        {
            "id": 10,
            "descripcion": "Desarrollo Web",
            "cantidad": 1,
            "monto_valor_unitario": 5000,
            "monto_total": 5000
        },
        {
            "id": 11,
            "descripcion": "Hosting Anual",
            "cantidad": 1,
            "monto_valor_unitario": 1200,
            "monto_total": 1200
        }
    ],
    "totals": {
        "subtotal": 6200,
        "igv": 1116,
        "total": 7316
    },
    "share_url": null
}
```

### 4️⃣ EDITAR COTIZACIÓN (PUT)

**Endpoint:** `PUT /facturador/cotizaciones/5`

**Solo si estado = "draft"**

**Payload:**

```json
{
    "fecha_vencimiento": "2024-02-20",
    "porcentaje_igv": 18,
    "items_json": "[...]" // items actualizados
}
```

**Respuesta (200 OK):**

```json
{
    "success": true,
    "message": "Cotización actualizada",
    "quote": {
        /* Quote actualizado */
    }
}
```

### 5️⃣ ELIMINAR COTIZACIÓN (DELETE)

**Endpoint:** `DELETE /facturador/cotizaciones/5`

**Solo si estado = "draft"**

**Respuesta (200 OK):**

```json
{
    "success": true,
    "message": "Cotización eliminada"
}
```

**Error (403 Forbidden):**

```json
{
    "success": false,
    "message": "Solo se pueden eliminar cotizaciones en borrador"
}
```

### 6️⃣ ENVIAR COTIZACIÓN (POST)

**Endpoint:** `POST /facturador/cotizaciones/5/send`

**Payload:** (vacío)

```json
{}
```

**Respuesta (200 OK):**

```json
{
    "success": true,
    "message": "Cotización enviada",
    "share_url": "https://tudominio.com/quotes/550e8400-e29b-41d4-a716-446655440000",
    "share_token": "550e8400-e29b-41d4-a716-446655440000"
}
```

### 7️⃣ VER COTIZACIÓN PÚBLICA (GET - SIN AUTH)

**Endpoint:** `GET /quotes/550e8400-e29b-41d4-a716-446655440000`

**Headers:** Ninguno requerido

**Respuesta (200 OK):**

```html
<!DOCTYPE html>
<html>
    <head>
        <title>Cotización #COT-2024-00005</title>
    </head>
    <body>
        <!-- HTML renderizado con datos de cotización -->
        <h1>Cotización #COT-2024-00005</h1>
        <button>Aceptar</button>
        <button>Rechazar</button>
    </body>
</html>
```

**Error (410 Gone) - Si token expirado:**

```json
{
    "message": "Esta cotización ya no está disponible"
}
```

### 8️⃣ CLIENTE ACEPTA (POST - SIN AUTH)

**Endpoint:** `POST /quotes/550e8400-e29b-41d4-a716-446655440000/accept`

**Payload:** (vacío)

```json
{}
```

**Respuesta (200 OK):**

```json
{
    "success": true,
    "message": "Cotización aceptada",
    "estado": "accepted",
    "accepted_at": "2024-01-15T10:45:00Z"
}
```

### 9️⃣ CLIENTE RECHAZA (POST - SIN AUTH)

**Endpoint:** `POST /quotes/550e8400-e29b-41d4-a716-446655440000/reject`

**Payload:** (vacío)

```json
{}
```

**Respuesta (200 OK):**

```json
{
    "success": true,
    "message": "Cotización rechazada",
    "estado": "rejected",
    "rejected_at": "2024-01-15T10:45:00Z"
}
```

### 🔟 CONVERTIR A FACTURA (POST)

**Endpoint:** `POST /facturador/cotizaciones/5/to-invoice`

**Solo si estado = "accepted" e invoice_id = null**

**Payload:** (opcional - override datos)

```json
{
    "descripcion_adicional": "Facturación automática desde cotización"
}
```

**Respuesta (200 OK):**

```json
{
    "success": true,
    "message": "Cotización convertida a factura",
    "invoice": {
        "id": 42,
        "numero_comprobante": "F001-00042",
        "monto_total": 7316
    },
    "redirect_url": "/facturador/invoices/42"
}
```

### 1️⃣1️⃣ DESCARGAR PDF (GET)

**Endpoint:** `GET /facturador/cotizaciones/5/pdf`

**Respuesta:** Descarga archivo PDF (`application/pdf`)

---

## 🧪 Testing con CURL

### Create

```bash
curl -X POST http://localhost:8000/facturador/cotizaciones \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: $CSRF_TOKEN" \
  -H "Cookie: XSRF-TOKEN=$XSRF; laravel_session=$SESSION" \
  -d '{
    "client_id": 1,
    "fecha_emision": "2024-01-15",
    "fecha_vencimiento": "2024-02-15",
    "codigo_moneda": "PEN",
    "porcentaje_igv": 18,
    "items_json": "[{\"descripcion\":\"Servicio\",\"cantidad\":1,\"monto_valor_unitario\":1000,\"codigo_unidad_medida\":\"UND\",\"monto_valor_total\":1000,\"codigo_indicador_afecto\":\"10\"}]"
  }'
```

### List

```bash
curl -X GET "http://localhost:8000/facturador/cotizaciones?estado=draft" \
  -H "Cookie: XSRF-TOKEN=$XSRF; laravel_session=$SESSION"
```

### Show

```bash
curl -X GET http://localhost:8000/facturador/cotizaciones/5 \
  -H "Cookie: XSRF-TOKEN=$XSRF; laravel_session=$SESSION"
```

### Send

```bash
curl -X POST http://localhost:8000/facturador/cotizaciones/5/send \
  -H "X-CSRF-Token: $CSRF_TOKEN" \
  -H "Cookie: XSRF-TOKEN=$XSRF; laravel_session=$SESSION"
```

### Public View (Sin auth)

```bash
curl -X GET http://localhost:8000/quotes/550e8400-e29b-41d4-a716-446655440000
```

### Accept (Sin auth)

```bash
curl -X POST http://localhost:8000/quotes/550e8400-e29b-41d4-a716-446655440000/accept \
  -H "Content-Type: application/json"
```

---

## 🔍 Postman Collection

```json
{
    "info": {
        "name": "Cotizaciones API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Crear Cotización",
            "request": {
                "method": "POST",
                "header": [
                    { "key": "Content-Type", "value": "application/json" },
                    { "key": "X-CSRF-Token", "value": "{{csrf_token}}" }
                ],
                "url": {
                    "raw": "{{base_url}}/facturador/cotizaciones",
                    "host": ["{{base_url}}"],
                    "path": ["facturador", "cotizaciones"]
                },
                "body": {
                    "mode": "raw",
                    "raw": "..."
                }
            }
        }
    ]
}
```

---

## ⚠️ Códigos de Error

| Código  | Significado          | Solución                   |
| ------- | -------------------- | -------------------------- |
| **200** | OK                   | ✅ Éxito                   |
| **201** | Created              | ✅ Recurso creado          |
| **400** | Bad Request          | Revisar JSON syntax        |
| **401** | Unauthorized         | Login requerido            |
| **403** | Forbidden            | Sin permisos (policy)      |
| **404** | Not Found            | Recurso no existe          |
| **410** | Gone                 | Token compartible expirado |
| **422** | Unprocessable Entity | Validación fallida         |
| **500** | Server Error         | Ver logs (`storage/logs/`) |

---

## 🎯 Testing Workflow

```
1. Login (obtener session/token)
   ↓
2. Crear cotización (status 201)
   ↓
3. Ver listado (status 200)
   ↓
4. Obtener detalle (status 200)
   ↓
5. Editar (status 200, solo draft)
   ↓
6. Enviar (status 200, genera share_token)
   ↓
7. Ver pública (status 200, sin auth)
   ↓
8. Cliente acepta (status 200)
   ↓
9. Convertir a factura (status 200, invoice_id asignado)
   ↓
10. Verificar factura creada
```

---

**Documento:** API Testing & Examples
**Última actualización:** 2024
**Compatibilidad:** Laravel 10/11+
