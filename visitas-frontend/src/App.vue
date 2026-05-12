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
  <div v-if="authStore.isAuthenticated" class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div
      class="fixed inset-y-0 left-0 z-50 w-80 bg-slate-900 shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
      :class="{
        '-translate-x-full': !sidebarOpen,
        'translate-x-0': sidebarOpen,
      }"
    >
      <div
        class="flex items-center justify-between p-5 border-b border-slate-700"
      >
        <div class="flex items-center space-x-3">
          <div
            class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center"
          >
            <i class="pi pi-shield text-white text-xl"></i>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-white">Administrador</h2>
            <p class="text-xs text-slate-400">Panel de Control</p>
          </div>
        </div>
        <button
          @click="sidebarOpen = false"
          class="lg:hidden text-slate-400 hover:text-white"
        >
          <i class="pi pi-times text-xl"></i>
        </button>
      </div>

      <nav class="p-4 space-y-2">
        <RouterLink
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          @click="sidebarOpen = false"
          class="flex items-center p-2 rounded-lg cursor-pointer transition-all duration-200"
          :class="
            route.path === item.path
              ? 'bg-blue-600 text-white shadow-lg'
              : 'text-slate-300 hover:bg-slate-800 hover:text-white'
          "
        >
          <i :class="`pi ${item.icon} text-lg`"></i>
          <div class="ml-3 flex-1">
            <div class="font-medium">{{ item.label }}</div>
            <div class="text-xs opacity-75">{{ item.description }}</div>
          </div>
        </RouterLink>
      </nav>

      <div class="absolute bottom-4 left-4 right-4">
        <div class="p-3 bg-slate-800 rounded-lg">
          <div class="flex items-center space-x-3">
            <div
              class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center"
            >
              <i class="pi pi-user text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white truncate">
                {{ authStore.user?.name }}
              </p>
              <p class="text-xs text-slate-400 truncate">
                {{ authStore.user?.email }}
              </p>
            </div>
            <Button
              @click="handleLogout"
              icon="pi pi-sign-out"
              class="p-button-text p-button-danger"
              v-tooltip.top="'Cerrar Sesión'"
            />
          </div>
        </div>
      </div>
    </div>

    <Toast position="top-center" />
    <ConfirmDialog />

    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    ></div>

    <div class="flex-1 lg:ml-0">
      <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <button
              @click="sidebarOpen = true"
              class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100"
            >
              <i class="pi pi-bars text-xl"></i>
            </button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">
                {{ route.meta.label || "Inicio" }}
              </h1>
              <p class="text-sm text-gray-600">
                {{ route.meta.description || "Panel de control" }}
              </p>
            </div>
          </div>
        </div>
      </header>

      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>

  <!-- Si no está autenticado, el router-view mostrará la página de Login -->
  <div v-else>
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
