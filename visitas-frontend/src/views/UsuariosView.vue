<script setup>
import { ref, onMounted, computed } from 'vue';
import apiClient from '../api/axios';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

// PrimeVue
import Card from 'primevue/card';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import ProgressSpinner from 'primevue/progressspinner';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';

const toast = useToast();
const confirm = useConfirm();

// --- Estado ---
const usuarios = ref([]);
const isLoading = ref(true);
const rolesLoading = ref(true);
const localidadesLoading = ref(true);

const search = ref('');
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
  name: '',
  email: '',
  rol: 'supervisor',
  localidad_id: null,
  roles: [],
});

const showPwdModal = ref(false);
const pwdForm = ref({
  id: null,
  password: '',
  password_confirmation: '',
});

const rolOptions = [
  { label: 'Administrador', value: 'admin' },
  { label: 'Supervisor', value: 'supervisor' },
];

// --- Helpers ---
const roleSeverity = (rol) => (rol === 'admin' ? 'primary' : 'info');

// Computed para obtener nombres de roles
const getRoleNames = (roleIds) => {
  if (!roleIds || roleIds.length === 0) return 'Sin roles';
  return roleIds.map(r => r.nombre || r).join(', ');
};

// Computed para obtener nombre de localidad
const getLocalidadName = (localidadId) => {
  if (!localidadId) return 'Sin asignar';
  const localidad = allLocalidades.value.find(l => l.id === localidadId);
  return localidad ? localidad.nombre : 'Desconocida';
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
    const { data } = await apiClient.get('/admin/roles');
    allRoles.value = data;
  } catch (e) {
    console.error(e);
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los roles.', life: 3000 });
  } finally {
    rolesLoading.value = false;
  }
}

async function fetchLocalidades() {
  localidadesLoading.value = true;
  try {
    const { data } = await apiClient.get('/admin/localidades');
    const { items } = parseLaravelIndexPayload(data);
    allLocalidades.value = items;
  } catch (e) {
    console.error(e);
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar las localidades.', life: 3000 });
  } finally {
    localidadesLoading.value = false;
  }
}

async function fetchUsuarios() {
  isLoading.value = true;
  try {
    const { data } = await apiClient.get('/admin/usuarios', {
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
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los usuarios.', life: 3000 });
  } finally {
    isLoading.value = false;
  }
}

async function saveUsuario() {
  try {
    // Validaciones
    if (!userForm.value.name || !userForm.value.email) {
      toast.add({ severity: 'warn', summary: 'Validación', detail: 'Nombre y email son obligatorios.', life: 2500 });
      return;
    }
    
    if (!userForm.value.localidad_id) {
      toast.add({ severity: 'warn', summary: 'Validación', detail: 'Debes asignar una localidad.', life: 2500 });
      return;
    }

    if (!userForm.value.roles || userForm.value.roles.length === 0) {
      toast.add({ severity: 'warn', summary: 'Validación', detail: 'Debes asignar al menos un rol.', life: 2500 });
      return;
    }

    const payload = {
      name: userForm.value.name,
      email: userForm.value.email,
      rol: userForm.value.rol,
      localidad_id: userForm.value.localidad_id,
      roles: userForm.value.roles.map(r => r.nombre || r),
    };

    if (isEditing.value) {
      await apiClient.put(`/admin/usuarios/${userForm.value.id}`, payload);
      toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Usuario actualizado correctamente.', life: 2500 });
    } else {
      const pwd = prompt('Asigne una contraseña inicial (mínimo 8 caracteres):');
      if (!pwd || pwd.length < 8) {
        toast.add({ severity: 'warn', summary: 'Cancelado', detail: 'No se creó el usuario (contraseña inválida).', life: 2500 });
        return;
      }
      await apiClient.post('/admin/usuarios', {
        ...payload,
        password: pwd,
        password_confirmation: pwd,
      });
      toast.add({ severity: 'success', summary: 'Creado', detail: 'Usuario creado correctamente.', life: 2500 });
    }

    showUserModal.value = false;
    await fetchUsuarios();
  } catch (e) {
    const msg =
      e?.response?.data?.errors
        ? Object.values(e.response.data.errors).flat().join(' ')
        : e?.response?.data?.message || 'Error al guardar el usuario.';
    toast.add({ severity: 'error', summary: 'Error de validación', detail: msg, life: 5000 });
  }
}

function openNewUser() {
  isEditing.value = false;
  userForm.value = {
    id: null,
    name: '',
    email: '',
    rol: 'supervisor',
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
    password: '',
    password_confirmation: '',
  };
  showPwdModal.value = true;
}

async function changePassword() {
  try {
    if (!pwdForm.value.password || pwdForm.value.password.length < 8) {
      toast.add({ severity: 'warn', summary: 'Inválido', detail: 'La contraseña debe tener al menos 8 caracteres.', life: 2500 });
      return;
    }
    if (pwdForm.value.password !== pwdForm.value.password_confirmation) {
      toast.add({ severity: 'warn', summary: 'Inválido', detail: 'Las contraseñas no coinciden.', life: 2500 });
      return;
    }
    await apiClient.post(`/admin/usuarios/${pwdForm.value.id}/password`, {
      password: pwdForm.value.password,
      password_confirmation: pwdForm.value.password_confirmation,
    });
    toast.add({ severity: 'success', summary: 'Listo', detail: 'Contraseña actualizada.', life: 2500 });
    showPwdModal.value = false;
  } catch (e) {
    const msg =
      e?.response?.data?.errors
        ? Object.values(e.response.data.errors).flat().join(' ')
        : e?.response?.data?.message || 'No se pudo actualizar la contraseña.';
    toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
  }
}

function confirmDelete(u) {
  confirm.require({
    message: `¿Eliminar al usuario "${u.name}"?`,
    header: 'Confirmación',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    acceptLabel: 'Sí, eliminar',
    rejectLabel: 'Cancelar',
    accept: async () => {
      try {
        await apiClient.delete(`/admin/usuarios/${u.id}`);
        toast.add({ severity: 'warn', summary: 'Eliminado', detail: 'Usuario eliminado.', life: 2500 });
        if (usuarios.value.length === 1 && page.value > 1) {
          page.value -= 1;
          first.value = (page.value - 1) * rows.value;
        }
        await fetchUsuarios();
      } catch (e) {
        const msg = e?.response?.data?.message || 'No se pudo eliminar el usuario.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 4000 });
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
  search.value = '';
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
  <div class="p-4 max-w-7xl mx-auto">
    <Card class="bg-white shadow-sm border-0">
      <template #title>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-900">Gestión de Usuarios</h2>
        </div>
      </template>

      <template #content>
        <Toolbar class="mb-4 bg-gray-50 border-gray-200 rounded-lg">
          <template #start>
            <div class="flex items-center gap-2">
              <InputText v-model="search" placeholder="Buscar por nombre, email o rol" @keyup.enter="doSearch" />
              <Button icon="pi pi-search" label="Buscar" class="p-button-secondary" @click="doSearch" />
              <Button icon="pi pi-times" label="Limpiar" class="p-button-text" @click="clearSearch" />
            </div>
          </template>
          <template #end>
            <Button label="Nuevo Usuario" icon="pi pi-user-plus" class="p-button-primary" @click="openNewUser" />
          </template>
        </Toolbar>

        <div v-if="isLoading" class="flex justify-center items-center py-10">
          <ProgressSpinner />
        </div>

        <DataTable
          v-else
          :value="usuarios"
          :paginator="true"
          :rows="rows"
          :first="first"
          :totalRecords="totalRecords"
          lazy
          @page="onPageChange"
          responsiveLayout="scroll"
          class="p-datatable-sm"
          stripedRows
        >
          <Column field="id" header="ID" style="width: 80px" sortable />
          <Column field="name" header="Nombre" sortable />
          <Column field="email" header="Email" sortable />
          <!-- <Column field="rol" header="Rol (Heredado)" style="width: 120px" sortable>
            <template #body="{ data }">
              <Tag :value="data.rol" :severity="roleSeverity(data.rol)" rounded />
            </template>
          </Column> -->
          <Column header="Roles" style="width: 180px">
            <template #body="{ data }">
              <div class="text-sm">
                <Tag
                  v-for="role in data.roles"
                  :key="role.id"
                  :value="role.nombre"
                  class="mr-1 mb-1"
                />
                <span v-if="!data.roles || data.roles.length === 0" class="text-gray-500">Sin roles</span>
              </div>
            </template>
          </Column>
          <Column header="Localidad" style="width: 150px">
            <template #body="{ data }">
              <span>{{ getLocalidadName(data.localidad_id) }}</span>
            </template>
          </Column>
          <Column header="Acciones" style="width: 260px">
            <template #body="{ data }">
              <div class="flex gap-2">
                <Button icon="pi pi-pencil" class="p-button-text p-button-sm" v-tooltip.top="'Editar'"
                        @click="openEditUser(data)" />
                <Button icon="pi pi-key" class="p-button-text p-button-sm p-button-secondary" v-tooltip.top="'Cambiar contraseña'"
                        @click="openPwdModal(data)" />
                <Button icon="pi pi-trash" class="p-button-text p-button-sm p-button-danger" v-tooltip.top="'Eliminar'"
                        @click="confirmDelete(data)" />
              </div>
            </template>
          </Column>

          <template #empty>
            <div class="text-center py-10">
              <i class="pi pi-users text-4xl text-gray-300 mb-2"></i>
              <p class="text-gray-500">No hay usuarios registrados.</p>
            </div>
          </template>
        </DataTable>
      </template>
    </Card>

    <!-- Modal Crear/Editar Usuario -->
    <Dialog v-model:visible="showUserModal" :header="isEditing ? 'Editar Usuario' : 'Nuevo Usuario'" modal class="w-full max-w-lg">
      <div class="p-fluid space-y-4 pt-2">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nombre <span class="text-red-500">*</span>
          </label>
          <InputText v-model.trim="userForm.name" class="w-full" placeholder="Juan Pérez" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Email <span class="text-red-500">*</span>
          </label>
          <InputText v-model.trim="userForm.email" type="email" class="w-full" placeholder="juan@example.com" />
        </div>

        <!-- <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Rol (Heredado)
          </label>
          <Dropdown v-model="userForm.rol" :options="rolOptions" optionLabel="label" optionValue="value" placeholder="Selecciona un rol" class="w-full" />
        </div> -->

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Localidad <span class="text-red-500">*</span>
          </label>
          <div v-if="localidadesLoading" class="flex items-center justify-center py-3">
            <ProgressSpinner style="width: 30px; height: 30px" strokeWidth="3" />
          </div>
          <Dropdown
            v-else
            v-model="userForm.localidad_id"
            :options="allLocalidades"
            optionLabel="nombre"
            optionValue="id"
            placeholder="Selecciona una localidad"
            class="w-full"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Roles <span class="text-red-500">*</span>
          </label>
          <div v-if="rolesLoading" class="flex items-center justify-center py-3">
            <ProgressSpinner style="width: 30px; height: 30px" strokeWidth="3" />
          </div>
          <MultiSelect
            v-else
            v-model="userForm.roles"
            :options="allRoles"
            optionLabel="nombre"
            placeholder="Selecciona uno o más roles"
            class="w-full"
          />
        </div>
      </div>

      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" class="p-button-text" @click="showUserModal = false" />
        <Button label="Guardar" icon="pi pi-check" class="p-button-primary" @click="saveUsuario" />
      </template>
    </Dialog>

    <!-- Modal Cambiar Contraseña -->
    <Dialog v-model:visible="showPwdModal" header="Cambiar Contraseña" modal class="w-full max-w-md">
      <div class="p-fluid space-y-4 pt-2">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nueva contraseña <span class="text-red-500">*</span>
          </label>
          <InputText v-model.trim="pwdForm.password" type="password" class="w-full" autocomplete="new-password" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Confirmar contraseña <span class="text-red-500">*</span>
          </label>
          <InputText v-model.trim="pwdForm.password_confirmation" type="password" class="w-full" autocomplete="new-password" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" class="p-button-text" @click="showPwdModal = false" />
        <Button label="Actualizar" icon="pi pi-check" class="p-button-primary" @click="changePassword" />
      </template>
    </Dialog>
  </div>
</template>
