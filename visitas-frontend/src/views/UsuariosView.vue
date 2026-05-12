<script setup>
import { ref, onMounted, computed } from "vue";
import apiClient from "../api/axios";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";

// PrimeVue
import Card from "primevue/card";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";
import ProgressSpinner from "primevue/progressspinner";
import Dropdown from "primevue/dropdown";
import MultiSelect from "primevue/multiselect";

const toast = useToast();
const confirm = useConfirm();

// --- Estado ---
const usuarios = ref([]);
const isLoading = ref(true);
const rolesLoading = ref(true);
const localidadesLoading = ref(true);

const search = ref("");
const rows = ref(10);
const totalRecords = ref(0);
const page = ref(1);
const first = ref(0);

// Datos para selects
const allRoles = ref([]);
const allLocalidades = ref([]);

const showUserModal = ref(false);
const isEditing = ref(false);
const userForm = ref({
  id: null,
  name: "",
  email: "",
  rol: "supervisor",
  localidad_id: null,
  roles: [],
});

const showPwdModal = ref(false);
const pwdForm = ref({
  id: null,
  password: "",
  password_confirmation: "",
});

const rolOptions = [
  { label: "Administrador", value: "admin" },
  { label: "Supervisor", value: "supervisor" },
];

// --- Helpers ---
const roleSeverity = (rol) => (rol === "admin" ? "primary" : "info");

// Computed para obtener nombres de roles
const getRoleNames = (roleIds) => {
  if (!roleIds || roleIds.length === 0) return "Sin roles";
  return roleIds.map((r) => r.nombre || r).join(", ");
};

// Computed para obtener nombre de localidad
const getLocalidadName = (localidadId) => {
  if (!localidadId) return "Sin asignar";
  const localidad = allLocalidades.value.find((l) => l.id === localidadId);
  return localidad ? localidad.nombre : "Desconocida";
};

function parseLaravelIndexPayload(data) {
  if (Array.isArray(data)) {
    return { items: data, total: data.length };
  }
  if (data && Array.isArray(data.data)) {
    return { items: data.data, total: Number(data.total ?? data.data.length) };
  }
  return { items: [], total: 0 };
}

// --- API ---
async function fetchRoles() {
  rolesLoading.value = true;
  try {
    const { data } = await apiClient.get("/admin/roles");
    allRoles.value = data;
  } catch (e) {
    console.error(e);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "No se pudieron cargar los roles.",
      life: 3000,
    });
  } finally {
    rolesLoading.value = false;
  }
}

async function fetchLocalidades() {
  localidadesLoading.value = true;
  try {
    const { data } = await apiClient.get("/admin/localidades");
    const { items } = parseLaravelIndexPayload(data);
    allLocalidades.value = items;
  } catch (e) {
    console.error(e);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "No se pudieron cargar las localidades.",
      life: 3000,
    });
  } finally {
    localidadesLoading.value = false;
  }
}

async function fetchUsuarios() {
  isLoading.value = true;
  try {
    const { data } = await apiClient.get("/admin/usuarios", {
      params: {
        per_page: rows.value,
        page: page.value,
        search: search.value || undefined,
      },
    });
    const { items, total } = parseLaravelIndexPayload(data);
    usuarios.value = items;
    totalRecords.value = total;
  } catch (e) {
    console.error(e);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "No se pudieron cargar los usuarios.",
      life: 3000,
    });
  } finally {
    isLoading.value = false;
  }
}

async function saveUsuario() {
  try {
    // Validaciones
    if (!userForm.value.name || !userForm.value.email) {
      toast.add({
        severity: "warn",
        summary: "Validación",
        detail: "Nombre y email son obligatorios.",
        life: 2500,
      });
      return;
    }

    if (!userForm.value.localidad_id) {
      toast.add({
        severity: "warn",
        summary: "Validación",
        detail: "Debes asignar una localidad.",
        life: 2500,
      });
      return;
    }

    if (!userForm.value.roles || userForm.value.roles.length === 0) {
      toast.add({
        severity: "warn",
        summary: "Validación",
        detail: "Debes asignar al menos un rol.",
        life: 2500,
      });
      return;
    }

    const payload = {
      name: userForm.value.name,
      email: userForm.value.email,
      rol: userForm.value.rol,
      localidad_id: userForm.value.localidad_id,
      roles: userForm.value.roles.map((r) => r.nombre || r),
    };

    if (isEditing.value) {
      await apiClient.put(`/admin/usuarios/${userForm.value.id}`, payload);
      toast.add({
        severity: "success",
        summary: "Actualizado",
        detail: "Usuario actualizado correctamente.",
        life: 2500,
      });
    } else {
      const pwd = prompt(
        "Asigne una contraseña inicial (mínimo 8 caracteres):",
      );
      if (!pwd || pwd.length < 8) {
        toast.add({
          severity: "warn",
          summary: "Cancelado",
          detail: "No se creó el usuario (contraseña inválida).",
          life: 2500,
        });
        return;
      }
      await apiClient.post("/admin/usuarios", {
        ...payload,
        password: pwd,
        password_confirmation: pwd,
      });
      toast.add({
        severity: "success",
        summary: "Creado",
        detail: "Usuario creado correctamente.",
        life: 2500,
      });
    }

    showUserModal.value = false;
    await fetchUsuarios();
  } catch (e) {
    const msg = e?.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join(" ")
      : e?.response?.data?.message || "Error al guardar el usuario.";
    toast.add({
      severity: "error",
      summary: "Error de validación",
      detail: msg,
      life: 5000,
    });
  }
}

function openNewUser() {
  isEditing.value = false;
  userForm.value = {
    id: null,
    name: "",
    email: "",
    rol: "supervisor",
    localidad_id: null,
    roles: [],
  };
  showUserModal.value = true;
}

function openEditUser(u) {
  isEditing.value = true;
  userForm.value = {
    id: u.id,
    name: u.name,
    email: u.email,
    rol: u.rol,
    localidad_id: u.localidad_id,
    roles: u.roles || [],
  };
  showUserModal.value = true;
}

function openPwdModal(u) {
  pwdForm.value = {
    id: u.id,
    password: "",
    password_confirmation: "",
  };
  showPwdModal.value = true;
}

async function changePassword() {
  try {
    if (!pwdForm.value.password || pwdForm.value.password.length < 8) {
      toast.add({
        severity: "warn",
        summary: "Inválido",
        detail: "La contraseña debe tener al menos 8 caracteres.",
        life: 2500,
      });
      return;
    }
    if (pwdForm.value.password !== pwdForm.value.password_confirmation) {
      toast.add({
        severity: "warn",
        summary: "Inválido",
        detail: "Las contraseñas no coinciden.",
        life: 2500,
      });
      return;
    }
    await apiClient.post(`/admin/usuarios/${pwdForm.value.id}/password`, {
      password: pwdForm.value.password,
      password_confirmation: pwdForm.value.password_confirmation,
    });
    toast.add({
      severity: "success",
      summary: "Listo",
      detail: "Contraseña actualizada.",
      life: 2500,
    });
    showPwdModal.value = false;
  } catch (e) {
    const msg = e?.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join(" ")
      : e?.response?.data?.message || "No se pudo actualizar la contraseña.";
    toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
  }
}

function confirmDelete(u) {
  confirm.require({
    message: `¿Eliminar al usuario "${u.name}"?`,
    header: "Confirmación",
    icon: "pi pi-exclamation-triangle",
    acceptClass: "p-button-danger",
    acceptLabel: "Sí, eliminar",
    rejectLabel: "Cancelar",
    accept: async () => {
      try {
        await apiClient.delete(`/admin/usuarios/${u.id}`);
        toast.add({
          severity: "warn",
          summary: "Eliminado",
          detail: "Usuario eliminado.",
          life: 2500,
        });
        if (usuarios.value.length === 1 && page.value > 1) {
          page.value -= 1;
          first.value = (page.value - 1) * rows.value;
        }
        await fetchUsuarios();
      } catch (e) {
        const msg =
          e?.response?.data?.message || "No se pudo eliminar el usuario.";
        toast.add({
          severity: "error",
          summary: "Error",
          detail: msg,
          life: 4000,
        });
      }
    },
  });
}

function onPageChange(evt) {
  first.value = evt.first;
  rows.value = evt.rows;
  page.value = Math.floor(evt.first / evt.rows) + 1;
  fetchUsuarios();
}

function doSearch() {
  page.value = 1;
  first.value = 0;
  fetchUsuarios();
}

function clearSearch() {
  search.value = "";
  page.value = 1;
  first.value = 0;
  fetchUsuarios();
}

onMounted(() => {
  fetchRoles();
  fetchLocalidades();
  fetchUsuarios();
});
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 p-4 md:p-6"
  >
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8"
      >
        <div>
          <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">
            Gestión de Usuarios
          </h1>

          <p class="text-sm text-gray-500 mt-1">
            Administra los usuarios registrados en el sistema
          </p>
        </div>

        <button
          @click="openNewUser"
          class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-5 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.02]"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
            />
          </svg>

          Nuevo Usuario
        </button>
      </div>

      <!-- Card -->
      <div
        class="bg-white/90 backdrop-blur rounded-3xl shadow-xl border border-gray-100 overflow-hidden"
      >
        <!-- Toolbar -->
        <div
          class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white"
        >
          <!-- Search -->
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <div class="relative w-full sm:w-80">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar por nombre, email o rol..."
                class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200"
                @keyup.enter="doSearch"
              />

              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="absolute left-4 top-3.5 h-5 w-5 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>

            <button
              @click="doSearch"
              class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-all duration-200 shadow-md hover:shadow-lg"
            >
              Buscar
            </button>

            <button
              @click="clearSearch"
              class="px-5 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-200"
            >
              Limpiar
            </button>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="py-20 flex justify-center">
          <div
            class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"
          ></div>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[1000px]">
            <thead
              class="bg-gradient-to-r from-gray-100 to-gray-50 border-b border-gray-200"
            >
              <tr>
                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  ID
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Nombre
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Email
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Roles
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Localidad
                </th>

                <th
                  class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Acciones
                </th>
              </tr>
            </thead>

            <tbody v-if="usuarios.length > 0">
              <tr
                v-for="user in usuarios"
                :key="user.id"
                class="border-b border-gray-100 hover:bg-blue-50/40 transition-all duration-200"
              >
                <!-- ID -->
                <td class="px-6 py-5">
                  <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-bold"
                  >
                    {{ user.id }}
                  </span>
                </td>

                <!-- Nombre -->
                <td class="px-6 py-5">
                  <div class="font-semibold text-gray-800">
                    {{ user.name }}
                  </div>
                </td>

                <!-- Email -->
                <td class="px-6 py-5 text-gray-600">
                  {{ user.email }}
                </td>

                <!-- Roles -->
                <td class="px-6 py-5">
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="role in user.roles"
                      :key="role.id"
                      class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                    >
                      {{ role.nombre }}
                    </span>

                    <span
                      v-if="!user.roles || user.roles.length === 0"
                      class="text-sm text-gray-400"
                    >
                      Sin roles
                    </span>
                  </div>
                </td>

                <!-- Localidad -->
                <td class="px-6 py-5 text-gray-700 font-medium">
                  {{ getLocalidadName(user.localidad_id) }}
                </td>

                <!-- Actions -->
                <td class="px-6 py-5">
                  <div class="flex items-center justify-center gap-3">
                    <!-- Edit -->
                    <button
                      @click="openEditUser(user)"
                      class="p-2 rounded-xl bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"
                        />
                      </svg>
                    </button>

                    <!-- Password -->
                    <button
                      @click="openPwdModal(user)"
                      class="p-2 rounded-xl bg-yellow-100 text-yellow-600 hover:bg-yellow-500 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M18 8a6 6 0 10-12 0v1H5a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2h-1V8zm-8 0a4 4 0 118 0v1h-8V8z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>

                    <!-- Delete -->
                    <button
                      @click="confirmDelete(user)"
                      class="p-2 rounded-xl bg-red-100 text-red-600 hover:bg-red-600 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Empty -->
            <tbody v-else>
              <tr>
                <td
                  colspan="6"
                  class="py-16 text-center text-gray-400 font-medium"
                >
                  No hay usuarios registrados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div
        class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4"
      >
        <p class="text-sm text-gray-600 font-medium">
          Mostrando
          <span class="font-bold text-blue-600">
            {{ usuarios.length }}
          </span>
          usuarios
        </p>

        <div class="flex items-center gap-2">
          <button
            class="px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 transition"
          >
            Anterior
          </button>

          <button class="px-4 py-2 rounded-xl bg-blue-600 text-white shadow-md">
            1
          </button>

          <button
            class="px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 transition"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Crear / Editar -->
    <div
      v-if="showUserModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div
        class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-300"
      >
        <!-- Header -->
        <div
          class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50"
        >
          <h2 class="text-2xl font-bold text-gray-800">
            {{ isEditing ? "Editar Usuario" : "Nuevo Usuario" }}
          </h2>

          <p class="text-sm text-gray-500 mt-1">
            Complete la información requerida
          </p>
        </div>

        <!-- Form -->
        <form
          @submit.prevent="saveUsuario"
          class="p-6 space-y-5 max-h-[80vh] overflow-y-auto"
        >
          <!-- Nombre -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Nombre <span class="text-red-500">*</span>
            </label>

            <input
              v-model="userForm.name"
              type="text"
              placeholder="Juan Pérez"
              class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Email <span class="text-red-500">*</span>
            </label>

            <input
              v-model="userForm.email"
              type="email"
              placeholder="juan@example.com"
              class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200"
            />
          </div>

          <!-- Localidad -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Localidad <span class="text-red-500">*</span>
            </label>

            <select
              v-model="userForm.localidad_id"
              class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200"
            >
              <option :value="null" disabled>-- Seleccione --</option>

              <option
                v-for="localidad in allLocalidades"
                :key="localidad.id"
                :value="localidad.id"
              >
                {{ localidad.nombre }}
              </option>
            </select>
          </div>

          <!-- Roles -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Roles <span class="text-red-500">*</span>
            </label>

            <div class="border border-gray-300 rounded-2xl p-4 bg-gray-50">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label
                  v-for="role in allRoles"
                  :key="role.id"
                  class="flex items-center gap-3 bg-white px-4 py-3 rounded-xl border border-gray-200 hover:border-blue-400 transition cursor-pointer"
                >
                  <input
                    v-model="userForm.roles"
                    :value="role"
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 rounded"
                  />

                  <span class="text-sm font-medium text-gray-700">
                    {{ role.nombre }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button
              type="button"
              @click="showUserModal = false"
              class="px-5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-200"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]"
            >
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Password -->
    <div
      v-if="showPwdModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div
        class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-300"
      >
        <!-- Header -->
        <div
          class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-yellow-50 to-orange-50"
        >
          <h2 class="text-2xl font-bold text-gray-800">Cambiar Contraseña</h2>

          <p class="text-sm text-gray-500 mt-1">
            Actualice la contraseña del usuario
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="changePassword" class="p-6 space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Nueva Contraseña
            </label>

            <input
              v-model="pwdForm.password"
              type="password"
              class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-yellow-100 focus:border-yellow-500 outline-none transition-all duration-200"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Confirmar Contraseña
            </label>

            <input
              v-model="pwdForm.password_confirmation"
              type="password"
              class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-yellow-100 focus:border-yellow-500 outline-none transition-all duration-200"
            />
          </div>

          <!-- Footer -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button
              type="button"
              @click="showPwdModal = false"
              class="px-5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-200"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]"
            >
              Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
