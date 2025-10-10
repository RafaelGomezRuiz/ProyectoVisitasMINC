import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/authStore';

// Vistas
import DashboardView from '../views/DashboardView.vue';
import LoginView from '../views/LoginView.vue';

export const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: DashboardView,
    meta: {
      label: 'Inicio',
      icon: 'pi pi-chart-line',
      description: 'Vista general y resultados en vivo',
      requiresAuth: true
    }
  },
  {
    path: '/localidades',
    name: 'Localidades',
    component: () => import('../views/LocationManager.vue'),
    meta: {
      label: 'Localidades',
      icon: 'pi pi-id-card',
      description: 'Gestionar Localidades del MINC',
      requiresAuth: true
    }
  },
  {
    path: '/reservas',
    name: 'Reservas',
    component: () => import('../views/ReservationManager.vue'),
    meta: {
      label: 'Gestión de Reservas',
      icon: 'pi pi-calendar-plus',
      description: 'Reservas de visitas',
      requiresAuth: true
    }
  },
  {
    path: '/tipos-visitante',
    name: 'TiposVisitante',
    component: () => import('../views/VisitorTypeManager.vue'),
    meta: {
      label: 'Gestión de Tipo de Visitante',
      icon: 'pi pi-check-square',
      description: 'Gestionar tipos de visitante',
      requiresAuth: true
    }
  },
  {
  path: '/visitas',
  name: 'Visitas',
  component: () => import('../views/VisitManager.vue'),
    meta: { requiresAuth: true, 
      label: 'Gestión de Visitas', 
      icon: 'pi pi-user-minus' ,
      description: 'Gestión de Visitas',}
   },
     {
  path: '/visitas-activas',
  name: 'VisitasActivas',
  component: () => import('../views/ActiveVisits.vue'),
    meta: { requiresAuth: true, 
      label: 'Visitas Activas', 
      icon: 'pi pi-user-minus' ,
      description: 'Gestión de Visitas',}
   },
  {
    path: '/paises',
    name: 'Paises',
    component: () => import('../views/CountryManager.vue'),
    meta: {
      label: 'Gestión de Paises',
      icon: 'pi pi-chart-pie',
      description: 'Gestionar países',
      requiresAuth: true
    }
  },
  {
    path: '/usuarios',
    name: 'Usuarios',
    component: () => import('../views/UsuariosView.vue'),
    meta: {
      label: 'Gestión de Usuarios',
      icon: 'pi pi-users',
      description: 'Administrar usuarios y supervisores',
      requiresAuth: true
    }
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

// Guardia global (hidrata desde localStorage por si el store aún no lo hizo)
router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  // Hidrata token/usuario si el store arranca vacío
  if (!auth.token) {
    const t = localStorage.getItem('token');
    const u = localStorage.getItem('user');
    if (t) auth.token = t;
    if (u) auth.user = JSON.parse(u);
  }

  const needsAuth = to.meta.requiresAuth === true;

  if (needsAuth && !auth.isAuthenticated) {
    next({ name: 'Login', query: { redirect: to.fullPath } });
  } else if (to.name === 'Login' && auth.isAuthenticated) {
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

export default router;
