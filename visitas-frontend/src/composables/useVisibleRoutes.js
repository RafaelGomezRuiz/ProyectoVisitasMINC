import { computed } from 'vue';
import { useAuthStore } from '../stores/authStore';
import { routes } from '../router/index';

/**
 * Composable para filtrar rutas visibles según los roles del usuario
 */
export function useVisibleRoutes() {
  const auth = useAuthStore();

  const visibleRoutes = computed(() => {
    if (!auth.isAuthenticated || !auth.user) {
      return [];
    }

    return routes
      .filter(route => {
        // Excluir rutas que no requieren auth (Login)
        if (!route.meta?.requiresAuth) {
          return false;
        }

        // Si la ruta no especifica roles, mostrarla
        if (!route.meta?.roles || route.meta.roles.length === 0) {
          return true;
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        return auth.hasAnyRole(route.meta.roles);
      })
      .map(route => ({
        label: route.meta?.label || route.name,
        icon: route.meta?.icon || 'pi pi-folder',
        description: route.meta?.description || '',
        path: route.path,
        name: route.name,
      }));
  });

  return {
    visibleRoutes,
  };
}
