# Instalación de Vuetify

## Pasos para instalar Vuetify en el proyecto frontend

### 1. Instalar las dependencias

Ejecuta el siguiente comando en la terminal desde la carpeta `visitas-frontend`:

```bash
npm install
```

O si prefieres instalar específicamente Vuetify:

```bash
npm install vuetify@latest @mdi/js vite-plugin-vuetify
```

### 2. Cambios ya realizados

Los siguientes archivos han sido configurados automáticamente:

#### ✅ `package.json`
- Agregadas dependencias: `vuetify`, `@mdi/js`
- Agregada devDependency: `vite-plugin-vuetify`

#### ✅ `vite.config.js`
- Agregado plugin de Vuetify con autoImport habilitado

#### ✅ `src/main.js`
- Importadas las librerías de Vuetify
- Configurada la instancia de Vuetify
- Integrado Vuetify en la aplicación Vue

### 3. Verificar la instalación

Después de ejecutar `npm install`, inicia el servidor de desarrollo:

```bash
npm run dev
```

El proyecto debería funcionar sin errores y los componentes de Vuetify (como los modales) estarán disponibles.

### 4. Usar BaseModal en los componentes

El componente `BaseModal.vue` ya está listo para usar. Solo asegúrate de importarlo en tus vistas:

```vue
<script setup>
import BaseModal from '../components/BaseModal.vue';
</script>

<template>
  <BaseModal 
    :model-value="isOpen" 
    title="Mi Modal"
    icon="mdi-check-circle"
    icon-color="success"
    :show-close="true"
    @update:model-value="isOpen = $event"
  />
</template>
```

## Iconos disponibles

Los iconos están disponibles a través de Material Design Icons (MDI). Algunos ejemplos:

- `mdi-loading` - Icono de carga (animado)
- `mdi-check-circle` - Icono de éxito
- `mdi-alert-circle` - Icono de error/alerta
- `mdi-information` - Icono de información
- `mdi-close` - Icono de cerrar

Puedes encontrar más iconos en: https://materialdesignicons.com/

## Troubleshooting

Si tienes problemas con la instalación:

1. Elimina `node_modules` y `package-lock.json`:
   ```bash
   rm -r node_modules package-lock.json
   ```

2. Reinstala todas las dependencias:
   ```bash
   npm install
   ```

3. Borra la carpeta de caché de Vite:
   ```bash
   rm -r .vite
   ```

4. Reinicia el servidor de desarrollo:
   ```bash
   npm run dev
   ```
