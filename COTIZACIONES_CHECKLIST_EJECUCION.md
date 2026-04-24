# ✅ CHECKLIST EJECUCIÓN - Sistema de Cotizaciones

## 🚀 FASE 1: Preparación BD (5 min)

### Base de Datos

- [ ] Seleccionar tipo BD (MySQL/PostgreSQL/SQLite/Docker)
- [ ] Configurar .env con credenciales

    ```env
    DB_CONNECTION=mysql
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=mendieta_cotizaciones
    DB_USERNAME=root
    DB_PASSWORD=secure_password
    ```

- [ ] Verificar conectividad
    ```bash
    php artisan tinker
    >>> DB::connection()->getPDO()  # Si devuelve PDO: ✅
    ```

### Migraciones

- [ ] Ejecutar migraciones pendientes

    ```bash
    php artisan migrate:status  # Ver estado
    php artisan migrate         # Ejecutar todas
    ```

- [ ] Verificar tablas creadas
    ```bash
    php artisan tinker
    >>> DB::table('quotes')->exists()          # true?
    >>> DB::table('quote_items')->exists()     # true?
    >>> DB::table('company_settings')->exists() # true?
    ```

---

## 🔧 FASE 2: Configuración Aplicación (5 min)

### Crear CompanySetting para Empresa Test

```bash
php artisan tinker

# Crear settings para empresa 1
CompanySetting::firstOrCreate(
  ['company_id' => 1],
  [
    'company_name' => 'Mi Empresa SA',
    'ruc' => '12345678901',
    'address' => 'Calle Test 123, Lima',
    'primary_color' => '#1a6b57',
    'secondary_color' => '#e5f5f1',
    'bank_accounts' => json_encode([
      ['banco' => 'BCP', 'cuenta' => '191-2345678-9-10', 'cci' => '002001234567890123456'],
      ['banco' => 'Interbank', 'cuenta' => '123-456789-01', 'cci' => '003700123456789012345']
    ]),
    'quote_footer' => 'Gracias por su negocio',
    'quote_thanks_message' => '¡Apreciamos su confianza!'
  ]
);

# Verificar
CompanySetting::where('company_id', 1)->first()
```

### Instalar DOMPDF (Opcional, para PDF)

```bash
composer require barryvdh/laravel-dompdf

# O usar snappy
composer require barryvdh/laravel-snappy

# Publicar assets
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

---

## 🧪 FASE 3: Testing Manual (30 min)

### Acceso Admin

#### 1. Crear Cotización

```
✓ Login como admin
✓ Ir a Menu > Cotizaciones > Nueva
✓ Llenar formulario:
  - Cliente: [Seleccionar cliente existente]
  - Moneda: PEN
  - Fecha emisión: Hoy
  - Fecha vencimiento: +30 días
  - IGV: 18%

✓ Agregar items (3 ejemplos):
  [1] Descrip: "Desarrollo Web"      Cant: 1    Precio: 5000
  [2] Descrip: "Hosting Anual"        Cant: 1    Precio: 1200
  [3] Descrip: "Soporte 3 meses"      Cant: 3    Precio: 200

✓ Verificar cálculos:
  Subtotal: 6400
  IGV (18%): 1152
  Total: 7552

✓ Guardar cotización
```

#### 2. Ver Cotización (Show)

```
✓ Ver listado (index)
✓ Hacer click en cotización creada
✓ Verificar:
  - Número (debe ser auto-generado)
  - Versión: 1
  - Estado: Borrador
  - Items listados correctamente
  - Totales coinciden
  - Panel lateral con resumen
```

#### 3. Editar Cotización

```
✓ Click en botón Editar
✓ Modificar items:
  - Cambiar cantidad de item 1 a 2
  - Eliminar item 3
  - Agregar nuevo: "Consultoría" 500x2

✓ Verificar recálculos automáticos
✓ Guardar cambios
✓ Volver a show para confirmar
```

#### 4. Enviar Cotización

```
✓ En show, hacer click "Enviar"
✓ Verificar:
  - Estado cambió a "Enviada"
  - Aparece fecha sent_at
  - Se genera link compartible
  - Copy button funciona
```

#### 5. Compartir con Cliente (Público)

```
✓ Copiar link del cotización enviada
✓ Abrir en navegador incógnito
✓ Verificar:
  - NO pide login
  - Se ve diseño profesional
  - Colores de CompanySetting aplicados
  - Logo de empresa visible
  - Todos los datos correctos
  - Botones: Aceptar / Rechazar visible
```

#### 6. Cliente Acepta Cotización

```
✓ En vista pública, click "Aceptar"
✓ Confirmación: "¿Aceptar cotización?"
✓ Verificar:
  - Mensaje "✓ Cotización Aceptada"
  - Mostrar fecha/hora de aceptación
  - Botones de acción desaparecen
```

#### 7. Admin ve Cambio de Estado

```
✓ Refrescar admin (show)
✓ Verificar:
  - Estado: "Aceptada"
  - Fecha accepted_at visible
  - Aparece botón "Convertir a Factura"
```

#### 8. Convertir a Factura

```
✓ Click en "Convertir a Factura"
✓ Verificar:
  - Se crea nueva factura
  - Datos copiados correctamente (cliente, items, montos)
  - Estado de cotización: "Aceptada"
  - campo invoice_id asignado
  - URL redirige a factura
```

### Casos Edge

#### A. Rechazo de Cotización

```
✓ Crear nueva cotización
✓ Enviar
✓ Abrir link público
✓ Click "Rechazar"
✓ En admin, ver estado: "Rechazada"
✓ Botón "Convertir a Factura" desaparecido
```

#### B. Versionamiento

```
✓ Crear cotización v1
✓ Enviar a cliente (link generado)
✓ Cliente rechaza
✓ En admin, crear versión v2 (debe existir botón)
✓ Cambiar items
✓ Enviar v2 con nuevo link
✓ Verificar: versión=2 en BD
```

#### C. Descarga PDF

```
✓ En show, click "PDF"
✓ Verificar:
  - Descarga PDF
  - Contenido igual a vista pública
  - Diseño legible
  - Números alineados a derecha
```

#### D. Acceso Denegado

```
✓ Login como usuario con rol "cliente"
✓ Ir a /facturador/cotizaciones
✓ Debe mostrar 403 Unauthorized
✓ O redirigir sin acceso
```

---

## 🔐 FASE 4: Validaciones (10 min)

### Datos Correctos

```bash
php artisan tinker

# Verificar Quote creada
Quote::with(['items', 'client', 'company'])->first()

# Verificar Items relacionados
Quote::first()->items

# Verificar numeros_cotizacion únicos por company
Quote::where('company_id', 1)->pluck('numero_cotizacion')

# Verificar share_token es UUID
Quote::where('estado', 'sent')->pluck('share_token')

# Verificar timestamps
Quote::latest()->first([
  'id', 'estado', 'sent_at', 'accepted_at',
  'rejected_at', 'created_at'
])
```

### Integridad Relaciones

```bash
# Quote → Client
Quote::first()->client  # No null si tiene client_id

# Quote → Company
Quote::first()->company # No null

# Quote → User
Quote::first()->user    # Usuario que creó

# Quote → Items
Quote::first()->items->count()  # > 0 si tiene items

# Quote → Invoice (si fue aceptada y convertida)
Quote::where('invoice_id', '!=', null)->first()->invoice
```

### Cálculos Correctos

```bash
# Crear Quote de prueba
$quote = Quote::find(1);

# Verificar montos
$subtotal = $quote->items->sum('monto_total');
$igv = $subtotal * ($quote->porcentaje_igv / 100);
$total = $subtotal + $igv;

# Deben coincidir con BD
$quote->monto_total_gravado == $subtotal
$quote->monto_total_igv == $igv
$quote->monto_total == $total
```

---

## 🐛 FASE 5: Debugging (Si hay problemas)

### Logs

```bash
# Ver últimos errores
tail -100 storage/logs/laravel.log

# En tiempo real
tail -f storage/logs/laravel.log

# Errores específicos de migraciones
php artisan migrate --step  # Una por una
```

### Database Checks

```bash
# Usar artisan
php artisan db:seed --class=QuoteSeeder  # Si existe

# O tinker
php artisan tinker
>>> Schema::hasTable('quotes')          # true?
>>> Schema::hasTable('quote_items')     # true?
>>> Schema::getColumns('quotes')        # Ver campos

# Verificar constraints
>>> DB::select("SHOW CREATE TABLE quotes")
```

### Requests/Responses

```bash
# Ver requests entrantes en logs
config('logging.channels.single.level') => 'debug'

# O usar Laravel Debugbar (si está instalado)
composer require barryvdh/laravel-debugbar --dev

# En routes/web.php (dev):
if (config('app.debug')) {
    DB::listen(fn($q) => \Log::debug($q->sql, $q->bindings));
}
```

---

## ✅ CHECKLIST FINAL

### Migraciones

- [ ] Tablas creadas sin errores
- [ ] Campos correctos (tipos, constraints, índices)
- [ ] Foreign keys vinculados
- [ ] Datos existentes respetados

### Modelos

- [ ] Quote model booteable
- [ ] Relations funcionan
- [ ] Accessors/Mutators correctos
- [ ] Soft deletes si aplica

### Controllers

- [ ] 200 OK en listados
- [ ] 201 Created en store
- [ ] 404 Not Found en inexistentes
- [ ] 403 Forbidden sin permiso
- [ ] 422 Unprocessable Entity en validación

### Views

- [ ] HTML renderiza sin errores
- [ ] Bootstrap carga correctamente
- [ ] Forms submit sin JS errors
- [ ] Links funcionan

### Seguridad

- [ ] Public routes sin auth funciona
- [ ] Admin routes requiere login
- [ ] Tokens no expuestos en URLs
- [ ] XSS prevention (escaping)
- [ ] CSRF protection en forms

---

## 📋 Comandos Útiles

```bash
# Migraciones
php artisan migrate:rollback --step=1  # Revertir última
php artisan migrate:refresh --seed     # Reset completo
php artisan migrate:status             # Ver estado

# Testing
php artisan test                       # Todas
php artisan test tests/Feature/        # Solo Feature
php artisan test --filter=QuoteTest    # Específico

# Tinker (REPL)
php artisan tinker

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Generate key (si es necesario)
php artisan key:generate

# Crear seeder de ejemplo
php artisan make:seeder QuoteSeeder
```

---

## 🎯 Criterios de Éxito

✅ **100% cuando:**

1. Todas las migraciones ejecutadas sin errores
2. Cotización crear → mostrar → editar → enviar → cliente acepta → convertir a factura
3. PDF descarga correctamente
4. Link público funciona sin autenticación
5. Todos los cálculos coinciden
6. Colores y logo de empresa aplican dinámicamente
7. No hay errores 500 en logs
8. Test suite pasa 100%

---

**Documento:** Ejecución & Validación
**Estado:** 🟢 Listo para ejecutar
**Tiempo estimado:** 1 hora completa
