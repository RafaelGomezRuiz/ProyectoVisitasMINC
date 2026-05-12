<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter, RouterLink } from "vue-router"; // 👈 añade useRouter
import { useAuthStore } from "./stores/authStore";
import { routes } from "./router";

// PrimeVue
import Button from "primevue/button";
import Toast from "primevue/toast";
import ConfirmDialog from "primevue/confirmdialog";

const sidebarOpen = ref(false);
const route = useRoute();
const router = useRouter(); // 👈 instancia del router
const authStore = useAuthStore();

// Filtrar menú según roles del usuario
const menuItems = computed(() => {
  return routes
    .map((r) => ({
      path: r.path,
      label: r.meta?.label,
      icon: r.meta?.icon,
      description: r.meta?.description,
      roles: r.meta?.roles || [],
      requiresAuth: r.meta?.requiresAuth,
    }))
    .filter((r) => {
      // Solo mostrar si tiene label y está autenticado
      if (!r.label || !r.requiresAuth) return false;

      // Si no tiene roles especificados, mostrar a todos
      if (r.roles.length === 0) return true;

      // Si tiene roles especificados, verificar que el usuario tenga al menos uno
      return authStore.hasAnyRole(r.roles);
    });
});

const handleLogout = async () => {
  // Aquí sí queremos avisar al servidor (server:true por defecto)
  await authStore.logout();
  await router.replace({ name: "Login" });
};
</script>

<template>
  <!-- Si el usuario está autenticado, muestra el panel de administración -->
  <div
    v-if="authStore.isAuthenticated"
    class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100"
  >
    <!-- Sidebar Mejorado -->
    <div
      class="fixed inset-y-0 left-0 z-50 w-72 lg:w-80 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-2xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
      :class="{
        '-translate-x-full': !sidebarOpen,
        'translate-x-0': sidebarOpen,
      }"
    >
      <!-- Header del Sidebar con efecto de vidrio -->
      <div class="relative overflow-hidden">
        <div
          class="absolute top-0 right-0 w-40 h-40 bg-blue-500/10 rounded-full -mr-20 -mt-20"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/10 rounded-full -ml-16 -mb-16"
        ></div>

        <div class="relative p-5 border-b border-slate-700/50">
          <div class="flex items-center space-x-3">
            <div class="relative">
              <div
                class="absolute inset-0 bg-blue-500 rounded-lg blur-md opacity-50"
              ></div>
              <div
                class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg"
              >
                <i class="pi pi-shield text-white text-xl"></i>
              </div>
            </div>
            <div>
              <h2 class="text-lg font-bold text-white">Panel Admin</h2>
              <p class="text-xs text-slate-400">Sistema de Gestión</p>
            </div>
          </div>
          <button
            @click="sidebarOpen = false"
            class="absolute top-5 right-5 lg:hidden text-slate-400 hover:text-white transition-colors hover:rotate-90 duration-200"
          >
            <i class="pi pi-times text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Navegación Mejorada -->
      <nav
        class="flex-1 p-4 space-y-1 overflow-y-auto max-h-[calc(100vh-200px)] custom-scroll"
      >
        <RouterLink
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          @click="sidebarOpen = false"
          class="flex items-center p-3 rounded-xl cursor-pointer transition-all duration-200 group relative overflow-hidden"
          :class="
            route.path === item.path
              ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg transform scale-102'
              : 'text-slate-300 hover:bg-slate-800/70 hover:text-white hover:translate-x-1'
          "
        >
          <!-- Efecto de brillo en hover -->
          <div
            v-if="route.path !== item.path"
            class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          ></div>

          <div class="relative flex items-center flex-1">
            <div
              :class="
                route.path === item.path
                  ? 'text-white'
                  : 'text-slate-400 group-hover:text-blue-400'
              "
            >
              <i :class="`pi ${item.icon} text-lg`"></i>
            </div>
            <div class="ml-3 flex-1">
              <div class="font-medium text-sm">{{ item.label }}</div>
              <div class="text-xs opacity-75">{{ item.description }}</div>
            </div>
            <i
              v-if="route.path === item.path"
              class="pi pi-chevron-right text-xs opacity-75 animate-pulse-slow"
            ></i>
          </div>
        </RouterLink>
      </nav>

      <!-- Perfil de Usuario Mejorado -->
      <div
        class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-slate-900 to-transparent"
      >
        <div class="relative">
          <div
            class="absolute -top-4 left-4 right-4 h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"
          ></div>

          <div
            class="p-3 bg-slate-800/80 backdrop-blur-sm rounded-xl border border-slate-700/50 hover:border-slate-600 transition-all duration-300"
          >
            <div class="flex items-center space-x-3">
              <div class="relative">
                <div
                  class="absolute inset-0 bg-green-500 rounded-full blur-sm opacity-50 animate-pulse"
                ></div>
                <div
                  class="relative w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center shadow-lg"
                >
                  <i class="pi pi-user text-white text-sm"></i>
                </div>
                <div
                  class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-400 rounded-full border-2 border-slate-800 shadow-sm"
                ></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">
                  {{ authStore.user?.name || "Administrador" }}
                </p>
                <p class="text-xs text-slate-400 truncate">
                  {{ authStore.user?.email || "admin@example.com" }}
                </p>
              </div>
              <Button
                @click="handleLogout"
                icon="pi pi-sign-out"
                class="p-button-text p-button-danger !p-2 hover:!bg-red-500/10"
                v-tooltip.top="'Cerrar Sesión'"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Componentes Globales Mejorados -->
    <Toast
      position="top-center"
      :breakpoints="{ '960px': { width: '90%', right: '5%', left: '5%' } }"
    />
    <ConfirmDialog
      :style="{ width: '450px' }"
      :breakpoints="{ '960px': { width: '90vw' } }"
    />

    <!-- Overlay para móvil -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-all duration-300 animate-fade-in"
    ></div>

    <!-- Contenido Principal -->
    <div class="flex-1 lg:ml-0 flex flex-col min-h-screen">
      <!-- Header Mejorado -->
      <header
        class="bg-white/90 backdrop-blur-md shadow-lg border-b border-gray-200/50 sticky top-0 z-30"
      >
        <div class="px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <!-- Botón de menú móvil -->
              <button
                @click="sidebarOpen = true"
                class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-all duration-200"
              >
                <i class="pi pi-bars text-xl"></i>
              </button>

              <!-- Título y descripción desktop -->
              <div class="hidden sm:block">
                <div class="flex items-center space-x-3">
                  <div
                    class="w-1 h-8 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"
                  ></div>
                  <div>
                    <h1
                      class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent"
                    >
                      {{ route.meta.label || "Inicio" }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                      {{
                        route.meta.description || "Panel de control principal"
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Título móvil -->
              <div class="sm:hidden">
                <h1 class="text-xl font-bold text-gray-800">
                  {{ route.meta.label || "Inicio" }}
                </h1>
              </div>
            </div>

            <!-- Panel de estado y notificaciones -->
            <div class="flex items-center space-x-3">
              <!-- Indicador de estado -->
              <div
                class="hidden md:flex items-center space-x-2 px-3 py-2 bg-green-50 rounded-full shadow-sm"
              >
                <div class="relative">
                  <div
                    class="w-2 h-2 bg-green-500 rounded-full animate-pulse"
                  ></div>
                  <div
                    class="absolute inset-0 w-2 h-2 bg-green-500 rounded-full animate-ping opacity-75"
                  ></div>
                </div>
                <span class="text-xs text-green-700 font-medium">En línea</span>
              </div>

              <!-- Icono de notificaciones -->
              <div class="relative">
                <button
                  class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all duration-200 relative"
                >
                  <i class="pi pi-bell text-gray-600 text-sm"></i>
                  <span
                    class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"
                  ></span>
                </button>
              </div>

              <!-- Avatar móvil -->
              <div class="flex md:hidden items-center space-x-2">
                <div
                  class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-md"
                >
                  <i class="pi pi-user text-white text-xs"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Breadcrumb -->
        <div
          class="hidden sm:block px-4 sm:px-6 lg:px-8 py-2 bg-gray-50/80 border-t border-gray-100"
        >
          <div class="flex items-center space-x-2 text-xs">
            <i class="pi pi-home text-gray-400"></i>
            <span class="text-gray-500">Panel</span>
            <i class="pi pi-chevron-right text-gray-400 text-xs"></i>
            <span class="text-gray-700 font-medium">{{
              route.meta.label || "Inicio"
            }}</span>
            <i
              v-if="route.meta.subtitle"
              class="pi pi-chevron-right text-gray-400 text-xs"
            ></i>
            <span v-if="route.meta.subtitle" class="text-gray-500">{{
              route.meta.subtitle
            }}</span>
          </div>
        </div>
      </header>

      <!-- Main Content con animación -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-x-hidden">
        <transition
          name="fade-slide"
          mode="out-in"
          @before-enter="beforeEnter"
          @enter="enter"
          @leave="leave"
        >
          <router-view :key="$route.fullPath" />
        </transition>
      </main>

      <!-- Footer Mejorado -->
      <footer
        class="bg-white/80 backdrop-blur-md border-t border-gray-200 px-4 sm:px-6 lg:px-8 py-4 mt-auto"
      >
        <div
          class="flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 gap-3"
        >
          <p>
            &copy; {{ new Date().getFullYear() }} Sistema de Gestión. Todos los
            derechos reservados.
          </p>
          <div class="flex space-x-6">
            <a
              href="#"
              class="hover:text-gray-700 transition-colors hover:underline"
              >Términos</a
            >
            <a
              href="#"
              class="hover:text-gray-700 transition-colors hover:underline"
              >Privacidad</a
            >
            <a
              href="#"
              class="hover:text-gray-700 transition-colors hover:underline"
              >Soporte</a
            >
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- Si no está autenticado -->
  <div v-else class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <router-view />
  </div>
</template>

<style scoped>
.transition-all {
  transition: all 0.3s ease;
}
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
