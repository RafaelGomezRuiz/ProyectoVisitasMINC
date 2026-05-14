<template>
  <div class="ps-4 bg-gray-50 min-h-screen font-sans space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Crear Nueva Reserva</h1>
    <!-- Paso 1: Buscar o crear un visitante -->
    <VisitorSearchOrCreate
      title="Paso 1: Buscar Visitante para la Reserva"
      @visitor-selected="handleVisitorSelected"
      v-if="!currentVisitor"
    />
    <!-- Paso 2: Mostrar info del visitante y formulario de reserva -->
    <div v-if="currentVisitor" class="space-y-8">
      <!-- Info del Visitante Seleccionado -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm text-gray-500">Reserva para:</p>
            <h2 class="text-2xl font-bold text-blue-800">
              Nombre: {{ currentVisitor.nombres }}
              {{ currentVisitor.apellidos }}
            </h2>
            <p class="text-gray-600">
              Documento de identidad: {{ currentVisitor.documento_identidad }}
            </p>
          </div>
          <button
            @click="resetFlow"
            class="text-sm text-blue-600 hover:underline"
          >
            Seleccionar otro visitante
          </button>
        </div>
      </div>

      <!-- Formulario de Reserva -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
          Paso 2: Detalles de la Reserva
        </h3>
        <form @submit.prevent="handleCreateReservation" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="area" class="block text-gray-700 font-semibold"
                >Área a Visitar <span class="text-red-600">*</span></label
              >
              <select
                id="area"
                v-model="reservationForm.area_id"
                class="input"
                :class="{ 'border-red-500 bg-red-50': formErrors.area_id }"
              >
                <option :value="null" disabled>-- Seleccione un área --</option>
                <option
                  v-for="area in dataStore.areas"
                  :key="area.id"
                  :value="area.id"
                >
                  {{ area.nombre }}
                </option>
              </select>
              <p v-if="formErrors.area_id" class="text-red-500 text-sm mt-1">
                {{ formErrors.area_id[0] }}
              </p>
            </div>
            <div>
              <label for="fecha" class="block text-gray-700 font-semibold"
                >Fecha <span class="text-red-600">*</span></label
              >
              <input
                type="date"
                id="fecha"
                v-model="reservationForm.fecha"
                class="input"
                :class="{ 'border-red-500': formErrors.fecha }"
              />
              <p v-if="formErrors.fecha" class="text-red-500 text-sm mt-1">
                {{ formErrors.fecha[0] }}
              </p>
            </div>
            <div>
              <label for="hora" class="block text-gray-700 font-semibold"
                >Hora <span class="text-red-600">*</span></label
              >
              <input
                type="time"
                id="hora"
                v-model="reservationForm.hora"
                class="input"
                :class="{ 'border-red-500': formErrors.hora }"
              />
              <p v-if="formErrors.hora" class="text-red-500 text-sm mt-1">
                {{ formErrors.hora[0] }}
              </p>
            </div>
          </div>
          <div>
            <label for="motivo" class="block text-gray-700 font-semibold"
              >Motivo de la Reserva (Opcional)</label
            >
            <textarea
              id="motivo"
              v-model="reservationForm.motivo"
              rows="3"
              class="input"
            ></textarea>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="btn-primary">Confirmar Reserva</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Listado de Reservas con pestañas: Vigentes y Expiradas -->
    <div>
      <Card class="bg-white shadow-sm border-0 mb-6">
        <template #title>
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Reservas</h2>
          </div>
        </template>

        <template #content>
          <Toolbar class="mb-4 bg-gray-50 border-gray-200 rounded-lg">
            <template #start>
              <div class="flex items-center gap-2">
                <InputText
                  v-model="reservationsSearch"
                  placeholder="Buscar por visitante, documento o área"
                  @keyup.enter="doReservationsSearch"
                />
                <Button
                  icon="pi pi-search"
                  label="Buscar"
                  class="p-button-secondary"
                  @click="doReservationsSearch"
                />
                <Button
                  icon="pi pi-times"
                  label="Limpiar"
                  class="p-button-text"
                  @click="clearReservationsSearch"
                />
              </div>
            </template>
          </Toolbar>

          <TabView v-if="!reservationStore.loading" class="p-tabview-sm">
            <TabPanel>
              <template #header>
                <div class="flex items-center gap-2">
                  <i class="pi pi-calendar-plus"></i>
                  <span>Reservas Vigentes</span>
                  <Badge :value="activeCount" class="ml-2" severity="info" />
                </div>
              </template>
              <DataTable
                :value="filteredActiveReservations"
                :paginator="true"
                :rows="rows"
                :first="firstActive"
                :totalRecords="filteredActiveReservations.length"
                responsiveLayout="scroll"
                class="p-datatable-sm"
                stripedRows
                @page="onActivePageChange"
              >
                <Column field="id" header="ID" style="width: 80px" sortable />
                <Column field="visitante.nombres" header="Visitante" sortable>
                  <template #body="{ data }">
                    {{ data.visitante.nombres }} {{ data.visitante.apellidos }}
                  </template>
                </Column>
                <Column
                  field="visitante.documento_identidad"
                  header="Documento"
                  sortable
                />
                <Column field="area.nombre" header="Área" sortable />
                <Column field="fecha" header="Fecha" sortable />
                <Column field="hora" header="Hora" sortable />
                <Column field="estado" header="Estado" sortable />
                <template #empty>
                  <div class="text-center py-10">
                    <i class="pi pi-inbox text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">No hay reservas vigentes.</p>
                  </div>
                </template>
              </DataTable>
            </TabPanel>

            <TabPanel>
              <template #header>
                <div class="flex items-center gap-2">
                  <i class="pi pi-calendar-times"></i>
                  <span>Reservas Expiradas</span>
                  <Badge :value="expiredCount" class="ml-2" severity="danger" />
                </div>
              </template>
              <DataTable
                :value="filteredExpiredReservations"
                :paginator="true"
                :rows="rows"
                :first="firstExpired"
                :totalRecords="filteredExpiredReservations.length"
                responsiveLayout="scroll"
                class="p-datatable-sm"
                stripedRows
                @page="onExpiredPageChange"
              >
                <Column field="id" header="ID" style="width: 80px" sortable />
                <Column field="visitante.nombres" header="Visitante" sortable>
                  <template #body="{ data }">
                    {{ data.visitante.nombres }} {{ data.visitante.apellidos }}
                  </template>
                </Column>
                <Column
                  field="visitante.documento_identidad"
                  header="Documento"
                  sortable
                />
                <Column field="area.nombre" header="Área" sortable />
                <Column field="fecha" header="Fecha" sortable />
                <Column field="hora" header="Hora" sortable />
                <Column field="estado" header="Estado" sortable />
                <template #empty>
                  <div class="text-center py-10">
                    <i class="pi pi-inbox text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">No hay reservas expiradas.</p>
                  </div>
                </template>
              </DataTable>
            </TabPanel>
          </TabView>

          <div v-else class="flex justify-center items-center py-10">
            <ProgressSpinner />
          </div>
        </template>
      </Card>
    </div>

    <!-- BaseModal para errores/avisos -->
    <BaseModal
      v-model="modalState.open"
      :title="modalState.title"
      :message="modalState.message"
      :icon="modalState.icon"
      :icon-color="modalState.iconColor"
      :show-close="modalState.showClose"
    />
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from "vue";
import VisitorSearchOrCreate from "../components/VisitorSearchOrCreate.vue";
import BaseModal from "../components/BaseModal.vue";
import { useReservationStore } from "../stores/reservationStore";
import { useDataStore } from "../stores/dataStore";

// PrimeVue components used in the reservations list
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import InputText from "primevue/inputtext";
import ProgressSpinner from "primevue/progressspinner";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Badge from "primevue/badge";

const reservationStore = useReservationStore();
const dataStore = useDataStore();

const currentVisitor = ref(null);
const reservationForm = ref({
  visitante_id: null,
  area_id: null,
  fecha: "",
  hora: "",
  motivo: "",
  estado: "pendiente",
});
const formErrors = ref({});

// Modal centralizado para errores/avisos
const modalState = reactive({
  open: false,
  title: "",
  message: "",
  icon: "mdi-alert-circle-outline",
  iconColor: "red",
  showClose: true,
});

// Fecha mínima: hoy
const minDate = new Date().toISOString().split("T")[0];

onMounted(() => {
  dataStore.fetchAll(); // Carga todos los datos para los selects
  // Cargar reservas iniciales
  reservationStore.fetchReservations(1);
});

// Reservas: búsqueda y paginación
const reservationsSearch = ref("");
const rows = ref(10);
const first = ref(0);
const firstActive = ref(0);
const firstExpired = ref(0);

const doReservationsSearch = () => {
  // Para ahora solo reiniciamos la página; el backend puede filtrar si se expande
  first.value = 0;
  firstActive.value = 0;
  firstExpired.value = 0;
  reservationStore.fetchReservations(1);
};

const clearReservationsSearch = () => {
  reservationsSearch.value = "";
  first.value = 0;
  firstActive.value = 0;
  firstExpired.value = 0;
  reservationStore.fetchReservations(1);
};

const onReservationsPageChange = (evt) => {
  first.value = evt.first;
  rows.value = evt.rows;
  const page = Math.floor(evt.first / evt.rows) + 1;
  reservationStore.fetchReservations(page);
};

// --- Clasificación local: Vigentes vs Expiradas ---
// Consideramos "expirada" cuando la fecha es anterior a hoy.
const isExpired = (res) => {
  if (!res.fecha) return false;
  const [year, month, day] = res.fecha.split("-").map(Number);
  const resDate = new Date(year, month - 1, day);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return resDate < today;
};

const activeReservations = computed(() => {
  return reservationStore.reservations.filter(
    (r) => !isExpired(r) && r.estado !== "cancelada",
  );
});

const expiredReservations = computed(() => {
  return reservationStore.reservations.filter(
    (r) => isExpired(r) && r.estado !== "cancelada",
  );
});

const filterBySearch = (items) => {
  if (!reservationsSearch.value.trim()) return items;
  const q = reservationsSearch.value.toLowerCase();
  return items.filter((r) => {
    const nombres =
      `${r.visitante?.nombres || ""} ${r.visitante?.apellidos || ""}`.toLowerCase();
    const documento = (r.visitante?.documento_identidad || "").toLowerCase();
    const area = (r.area?.nombre || "").toLowerCase();
    return nombres.includes(q) || documento.includes(q) || area.includes(q);
  });
};

const filteredActiveReservations = computed(() =>
  filterBySearch(activeReservations.value),
);
const filteredExpiredReservations = computed(() =>
  filterBySearch(expiredReservations.value),
);

const activeCount = computed(() => filteredActiveReservations.value.length);
const expiredCount = computed(() => filteredExpiredReservations.value.length);

const onActivePageChange = (evt) => {
  firstActive.value = evt.first;
  rows.value = evt.rows;
};

const onExpiredPageChange = (evt) => {
  firstExpired.value = evt.first;
  rows.value = evt.rows;
};

const handleVisitorSelected = (visitor) => {
  currentVisitor.value = visitor;
  prepareReservationForm();
};

const prepareReservationForm = () => {
  // No autopoblar fecha/hora: el usuario debe seleccionarlos.
  reservationForm.value = {
    visitante_id: currentVisitor.value.id,
    area_id: null,
    fecha: "",
    hora: "",
    motivo: "",
    estado: "pendiente",
  };
  formErrors.value = {};
};

const showRequiredFieldsModal = (errors) => {
  // Construir mensaje legible con los errores pasados
  const lines = [];
  if (errors.area_id)
    lines.push(`Área a visitar: ${errors.area_id.join(", ")}`);
  if (errors.fecha) lines.push(`Fecha: ${errors.fecha.join(", ")}`);
  if (errors.hora) lines.push(`Hora: ${errors.hora.join(", ")}`);
  if (lines.length === 0)
    lines.push("Por favor completa los campos obligatorios.");

  modalState.title = "Campos obligatorios";
  modalState.message = lines.join("\n");
  modalState.icon = "mdi-alert-circle-outline";
  modalState.iconColor = "red";
  modalState.showClose = true;
  modalState.open = true;
};

const handleCreateReservation = async () => {
  formErrors.value = {};

  // Validación frontend
  if (!reservationForm.value.area_id) {
    formErrors.value.area_id = ["Área a visitar es obligatoria."];
  }

  if (!reservationForm.value.fecha) {
    formErrors.value.fecha = ["La fecha es obligatoria."];
  } else if (reservationForm.value.fecha < minDate) {
    formErrors.value.fecha = ["La fecha no puede ser anterior a hoy."];
  }

  if (!reservationForm.value.hora) {
    formErrors.value.hora = ["La hora es obligatoria."];
  }

  if (Object.keys(formErrors.value).length > 0) {
    showRequiredFieldsModal(formErrors.value);
    return;
  }

  // Mostrar loading modal
  modalState.title = "Creando reserva...";
  modalState.message = "Por favor espera mientras se crea tu reserva.";
  modalState.icon = "mdi-loading";
  modalState.iconColor = "blue";
  modalState.showClose = false;
  modalState.open = true;

  const result = await reservationStore.createReservation(
    reservationForm.value,
  );
  // console.log("valor del result ", result);
  if (result.success) {
    modalState.title = "Reserva creada";
    modalState.message = "¡Reserva creada con éxito!";
    modalState.icon = "mdi-check-circle-outline";
    modalState.iconColor = "green";
    modalState.showClose = true;
    modalState.open = true;
    resetFlow();
  } else {
    formErrors.value = result.errors || {};
    // Mostrar errores devueltos por la API en el modal
    showRequiredFieldsModal(formErrors.value);
  }
};

const resetFlow = () => {
  currentVisitor.value = null;
  reservationForm.value = {
    visitante_id: null,
    area_id: null,
    fecha: "",
    hora: "",
    motivo: "",
    estado: "pendiente",
  };
  formErrors.value = {};
};

// Watchers: quitar rojo (errores) cuando el campo se vuelve válido
watch(
  () => reservationForm.value.area_id,
  (newVal) => {
    if (!formErrors.value || !formErrors.value.area_id) return;
    if (newVal) {
      delete formErrors.value.area_id;
    }
  },
);

watch(
  () => reservationForm.value.fecha,
  (newVal) => {
    if (!formErrors.value || !formErrors.value.fecha) return;
    if (newVal && newVal >= minDate) {
      delete formErrors.value.fecha;
    }
  },
);

watch(
  () => reservationForm.value.hora,
  (newVal) => {
    if (!formErrors.value || !formErrors.value.hora) return;
    if (newVal) {
      delete formErrors.value.hora;
    }
  },
);
</script>

<style>
/* Reutilizando estilos globales definidos en otros componentes */
.input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px #dbeafe;
}

.btn-primary {
  @apply bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors;
}
</style>
