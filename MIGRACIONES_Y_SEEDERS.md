# Ejecución de Migraciones y Seeders

## Descripción General

Este documento describe los pasos necesarios para ejecutar las migraciones de base de datos y seeders que implementan el sistema de roles y relaciones de muchos-a-muchos en el proyecto VISITAS.

## Cambios Incluidos

### Migraciones

1. **`create_roles_table`** - Crea tabla de roles con campos `nombre` y `descripcion`
2. **`create_role_user_pivot_table`** - Crea tabla pivote `role_user` para relación muchos-a-muchos
3. **`add_user_id_to_visitas_table`** - Agrega columna `user_id` a tabla `visitas` para rastrear quién creó la visita
4. **`add_user_id_to_reservas_table`** - Agrega columna `user_id` a tabla `reservas` para rastrear quién creó la reserva
5. **`add_area_id_to_reservas_table`** - Agrega columna `area_id` a tabla `reservas` para seleccionar el área a visitar
6. **`add_localidad_id_to_users_table`** - Agrega columna `localidad_id` a tabla `users` (obligatoria para AgenteDeVisitas)

### Seeders

1. **`RoleSeeder`** - Crea tres roles iniciales:
   - `Administrador` - Acceso completo
   - `Supervisor` - Supervisar localidades
   - `AgenteDeVisitas` - Registrar visitas de su localidad

2. **`AssignDefaultRolesSeeder`** - Asigna roles a usuarios existentes basado en el campo `rol` heredado

## Pasos de Ejecución

### 1. Conexión a la Base de Datos

Asegúrate de que el archivo `.env` tenga la configuración correcta:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=visitas_db
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Ejecutar Migraciones

En el directorio `api-visitas/`, ejecuta:

```bash
php artisan migrate
```

Esto ejecutará todas las migraciones pendientes en orden cronológico.

**Salida esperada:**
```
Migrating: 2025_11_26_000000_create_roles_table
Migrated:  2025_11_26_000000_create_roles_table (0.03s)
Migrating: 2025_11_26_000001_create_role_user_pivot_table
Migrated:  2025_11_26_000001_create_role_user_pivot_table (0.02s)
Migrating: 2025_11_26_000002_add_user_id_to_visitas_table
Migrated:  2025_11_26_000002_add_user_id_to_visitas_table (0.01s)
Migrating: 2025_11_26_000003_add_user_id_to_reservas_table
Migrated:  2025_11_26_000003_add_user_id_to_reservas_table (0.01s)
Migrating: 2025_11_26_000004_add_area_id_to_reservas_table
Migrated:  2025_11_26_000004_add_area_id_to_reservas_table (0.01s)
Migrating: 2025_11_26_000005_add_localidad_id_to_users_table
Migrated:  2025_11_26_000005_add_localidad_id_to_users_table (0.02s)
```

### 3. Ejecutar Seeders

En el directorio `api-visitas/`, ejecuta:

```bash
php artisan db:seed --class=RoleSeeder
```

Luego asigna roles a usuarios existentes:

```bash
php artisan db:seed --class=AssignDefaultRolesSeeder
```

**O ejecuta ambos con:**

```bash
php artisan db:seed
```

(Si `DatabaseSeeder.php` incluye ambos)

## Rollback (Deshacer Cambios)

Si necesitas revertir las migraciones:

```bash
php artisan migrate:rollback
```

Para deshacer todas las migraciones:

```bash
php artisan migrate:reset
```

Para reiniciar completamente:

```bash
php artisan migrate:refresh --seed
```

## Verificación

Después de ejecutar las migraciones y seeders, verifica en la base de datos:

### Roles Creados
```sql
SELECT * FROM roles;
```

**Resultado esperado:**
```
| id | nombre          | descripcion                                    |
|----|-----------------|------------------------------------------------|
| 1  | Administrador   | Acceso completo a todo el sistema              |
| 2  | Supervisor      | Supervisar localidades y gestionar datos       |
| 3  | AgenteDeVisitas | Registrar visitas y reservas de su localidad   |
```

### Asignación de Roles
```sql
SELECT user_id, role_id FROM role_user;
```

### Nuevas Columnas en Usuarios
```sql
DESCRIBE users;
```

Busca `localidad_id` - debe tener tipo `unsignedBigInteger` y ser nullable.

### Nuevas Columnas en Visitas
```sql
DESCRIBE visitas;
```

Busca `user_id` - debe tener tipo `unsignedBigInteger`.

### Nuevas Columnas en Reservas
```sql
DESCRIBE reservas;
```

Busca `user_id` y `area_id` - deben tener tipo `unsignedBigInteger`.

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint"

**Causa:** El orden de las migraciones o referencias incorrectas.

**Solución:**
1. Verifica que las tablas referenciadas existan antes de agregar foreign keys
2. Ejecuta `php artisan migrate:reset` para limpiar y vuelve a empezar
3. Verifica la secuencia de migraciones

### Error: "Base table or view not found: 1146 Table 'visitas_db.roles' doesn't exist"

**Causa:** Las migraciones no se ejecutaron completamente.

**Solución:**
```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
```

### Error: "Call to undefined method roles()" en controladores

**Causa:** Falta cargar la relación en el modelo.

**Solución:** Asegúrate de usar `.with('roles')` en queries:
```php
$user = User::with('roles', 'localidad')->find($id);
```

## Configuración en Controladores

Los controladores ya están configurados para:

1. **UserController.php** - Convierte nombres de roles a IDs antes de sincronizar
2. **VisitaController.php** - Filtra visitas por localidad del usuario AgenteDeVisitas
3. **ReservaController.php** - Filtra reservas por localidad del usuario y validar área

## Configuración en Frontend

El frontend ya está configurado para:

1. **UsuariosView.vue** - Carga roles y localidades del API para selects
2. **authStore.js** - Tiene helpers `hasRole()` y `hasAnyRole()`
3. **router/index.js** - Valida roles antes de permitir acceso a rutas

## Próximos Pasos

1. ✅ Ejecutar migraciones y seeders
2. ✅ Probar la creación/edición de usuarios con roles y localidades
3. ✅ Verificar que AgenteDeVisitas solo vea datos de su localidad
4. ⚠️ Integrar `useVisibleRoutes` composable en navbar/sidebar (si corresponde)
5. ⚠️ Realizar pruebas end-to-end con múltiples roles

## Archivos Modificados/Creados

### Backend
- `app/Models/Role.php` (nuevo)
- `app/Models/User.php` (modificado)
- `app/Models/Visita.php` (modificado)
- `app/Models/Reserva.php` (modificado)
- `app/Http/Controllers/Api/Admin/UserController.php` (modificado)
- `app/Http/Controllers/Api/VisitaController.php` (modificado)
- `app/Http/Controllers/Api/ReservaController.php` (modificado)
- `app/Http/Controllers/Api/RoleController.php` (nuevo)
- `routes/api.php` (modificado)
- `database/migrations/` (6 nuevas migraciones)
- `database/seeders/RoleSeeder.php` (nuevo)
- `database/seeders/AssignDefaultRolesSeeder.php` (nuevo)

### Frontend
- `src/stores/authStore.js` (modificado)
- `src/router/index.js` (modificado)
- `src/composables/useVisibleRoutes.js` (nuevo)
- `src/views/UsuariosView.vue` (modificado)

## Soporte

Si tienes problemas durante la ejecución, revisa:
1. El archivo `.env` con credenciales correctas
2. La salida de `php artisan migrate --step` (ejecuta una migración a la vez)
3. Los logs en `storage/logs/laravel.log`
