# Guía de Implementación: Sistema de Roles y Segmentación por Localidad

## Resumen de Cambios

Se han implementado los siguientes cambios:

### Backend (Laravel)
1. **Modelo Role** - Sistema de roles many-to-many (Administrador, Supervisor, AgenteDeVisitas)
2. **Migraciones**:
   - Tabla `roles`
   - Tabla pivot `role_user`
   - Agregar `user_id` a `visitas`
   - Agregar `user_id` a `reservas`
3. **Relaciones en Modelos**:
   - `User` → roles, visitas, reservas
   - `Visita` → usuario creador
   - `Reserva` → usuario creador
4. **Filtrado de Datos**:
   - Controllers filtran visitas/reservas por localidad del usuario autenticado
   - AgenteDeVisitas solo ve sus propios registros
5. **Seeders** para inicializar roles

### Frontend (Vue)
1. **authStore** - Helpers para verificar roles (`hasRole`, `hasAnyRole`)
2. **Router** - Meta roles en rutas para control de acceso
3. **Composable `useVisibleRoutes`** - Filtrar menú según roles

## Instrucciones de Implementación

### Paso 1: Aplicar Migraciones en Backend

```bash
cd api-visitas

# Ejecutar todas las migraciones
php artisan migrate

# Ejecutar seeders (importante para crear los roles)
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AssignDefaultRolesSeeder
```

### Paso 2: Instalar Dependencias Frontend (si es necesario)

```bash
cd ../visitas-frontend

# Asegurar que las dependencias están actualizadas
npm install

# Ejecutar el servidor de desarrollo
npm run dev
```

## Estructura de Roles

### Roles Disponibles:
- **Administrador**: Acceso completo a todo el sistema
- **Supervisor**: Gestionar localidades y datos generales
- **AgenteDeVisitas**: Registrar visitas y reservas de su localidad

### Rutas y Permisos:

| Ruta | Administrador | Supervisor | AgenteDeVisitas |
|------|---------------|------------|-----------------|
| Dashboard | ✅ | ✅ | ✅ |
| Gestión de Visitas | ✅ | ✅ | ✅ |
| Visitas Activas | ✅ | ✅ | ✅ |
| Gestión de Reservas | ✅ | ✅ | ✅ |
| Localidades | ✅ | ✅ | ❌ |
| Tipos de Visitante | ✅ | ✅ | ❌ |
| Gestión de Países | ✅ | ✅ | ❌ |
| Gestión de Usuarios | ✅ | ❌ | ❌ |

## Filtrado de Datos por Localidad

- **Administrador y Supervisor**: Ven todos los datos
- **AgenteDeVisitas**: Solo ve visitas y reservas creadas por él o asociadas a su localidad

### Asignación Automática:
- Al crear una visita/reserva, se asigna automáticamente el `user_id` del usuario autenticado
- El filtrado en el backend verifica la `localidad_id` del usuario autenticado

## Cambios en Modelos y Bases de Datos

### Nuevas Columnas:
- `visitas.user_id` - ID del usuario que creó la visita
- `reservas.user_id` - ID del usuario que creó la reserva

### Nuevas Relaciones:
- `User` ↔ `Role` (many-to-many)
- `User` ← `Visita` (one-to-many)
- `User` ← `Reserva` (one-to-many)

## Testing Recomendado

1. **Login con diferentes roles**:
   ```
   Admin: email@admin.com
   Supervisor: supervisor@domain.com
   AgenteDeVisitas: agente@domain.com
   ```

2. **Verificar filtrado**:
   - Crear visitas/reservas con diferentes usuarios
   - Verificar que AgenteDeVisitas solo ve sus registros

3. **Verificar menú**:
   - Confirmar que el menú oculta rutas según roles

4. **Verificar guardias de ruta**:
   - Intentar acceder a rutas no permitidas directamente (ej: /usuarios)

## Notas Importantes

- Los seeders crean los roles automáticamente
- Los usuarios existentes con `rol='admin'` se asignan al rol "Administrador"
- Los usuarios existentes con `rol='supervisor'` se asignan al rol "Supervisor"
- Los nuevos usuarios deben crearse con roles asignados en la UI

## Archivos Modificados/Creados

### Backend:
- `app/Models/Role.php` (nuevo)
- `app/Models/User.php` (relaciones añadidas)
- `app/Models/Visita.php` (relaciones, fillable)
- `app/Models/Reserva.php` (relaciones, fillable)
- `app/Http/Controllers/Api/VisitaController.php` (filtrado)
- `app/Http/Controllers/Api/ReservaController.php` (filtrado)
- `app/Http/Controllers/Api/Admin/UserController.php` (roles management)
- `app/Http/Controllers/AuthController.php` (login con roles)
- `database/migrations/2025_11_26_000004_create_roles_table.php` (nuevo)
- `database/migrations/2025_11_26_000005_create_role_user_pivot_table.php` (nuevo)
- `database/migrations/2025_11_26_000006_add_user_id_to_visitas_table.php` (nuevo)
- `database/migrations/2025_11_26_000007_add_user_id_to_reservas_table.php` (nuevo)
- `database/seeders/RoleSeeder.php` (nuevo)
- `database/seeders/AssignDefaultRolesSeeder.php` (nuevo)

### Frontend:
- `src/stores/authStore.js` (helpers de roles)
- `src/router/index.js` (meta roles en rutas)
- `src/composables/useVisibleRoutes.js` (nuevo)

## Troubleshooting

### Los roles no aparecen en el login
1. Verificar que `php artisan migrate` se ejecutó exitosamente
2. Verificar que `php artisan db:seed --class=RoleSeeder` se ejecutó
3. Verificar que `AuthController.php` está actualizado

### El menú muestra todas las rutas
1. Verificar que `authStore` está importando las funciones `hasRole` y `hasAnyRole`
2. Verificar que los roles están siendo guardados en localStorage
3. Usar las DevTools para inspeccionar `auth.user.roles`

### Las visitas/reservas no se filtran por localidad
1. Verificar que `user_id` está siendo asignado en los controllers
2. Verificar que el usuario autenticado tiene `localidad_id` asignado
3. Verificar que el rol del usuario es "AgenteDeVisitas"
