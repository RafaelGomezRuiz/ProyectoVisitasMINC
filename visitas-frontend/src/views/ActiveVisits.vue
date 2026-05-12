<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 p-4 md:p-6"
  >
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Modales -->
      <BaseModal
        :model-value="modalState.loading"
        title="Procesando"
        icon="mdi-loading"
        icon-color="primary"
      />

      <BaseModal
        :model-value="modalState.success"
        :title="modalState.title"
        :message="modalState.message"
        icon="mdi-check-circle"
        icon-color="success"
        :show-close="true"
        @update:model-value="onModalSuccessChange"
      />

      <!-- Paso 1 -->
      <VisitorSearchOrCreate @visitor-selected="handleVisitorSelected" />

      <!-- Paso 2 -->
      <div v-if="currentVisitor" class="space-y-8">
        <!-- Card visitante -->
        <div
          class="bg-white/90 backdrop-blur rounded-3xl shadow-xl border border-gray-100 overflow-hidden"
        >
          <div
            class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 p-6 md:p-8"
          >
            <div>
              <h2
                class="text-2xl md:text-3xl font-extrabold text-blue-700 tracking-tight"
              >
                {{ currentVisitor.nombres }}
                {{ currentVisitor.apellidos }}
              </h2>

              <p class="text-gray-500 mt-2">
                Documento:
                <span class="font-semibold text-gray-700">
                  {{ currentVisitor.documento_identidad }}
                </span>
              </p>
            </div>

            <button
              @click="resetFlow"
              class="px-5 py-3 rounded-2xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold transition-all duration-200"
            >
              Registrar otra visita
            </button>
          </div>
        </div>

        <!-- Loading -->
        <div
          v-if="reservationStore.loading"
          class="bg-white rounded-3xl shadow-xl border border-gray-100 py-20 flex flex-col items-center justify-center"
        >
          <div
            class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"
          ></div>

          <span class="mt-4 text-sm text-gray-500">
            Buscando reserva pendiente...
          </span>
        </div>

        <!-- Reserva -->
        <div
          v-if="reservationStore.pendingReservation"
          class="bg-green-50 border border-green-200 border-l-[6px] border-l-green-500 rounded-3xl shadow-lg p-6 md:p-8"
        >
          <h3 class="text-2xl font-bold text-green-800">
            Reserva Pendiente Encontrada
          </h3>

          <p class="text-green-700 mt-3">
            Este visitante tiene una reserva para el
            <strong>{{ reservationStore.pendingReservation.fecha }}</strong>
            a las
            <strong>{{ reservationStore.pendingReservation.hora }}</strong>
          </p>

          <p class="mt-2 text-green-700">
            Motivo:
            {{
              reservationStore.pendingReservation.motivo || "No especificado"
            }}
          </p>

          <div class="mt-6 flex flex-wrap gap-3">
            <button
              @click="useReservation"
              class="px-5 py-3 rounded-2xl bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold shadow-md hover:shadow-xl transition-all duration-300"
            >
              Usar Reserva y Registrar Visita
            </button>

            <button
              @click="ignoreReservation"
              class="px-5 py-3 rounded-2xl bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 font-semibold transition-all duration-200"
            >
              Ignorar y registrar visita normal
            </button>
          </div>
        </div>

        <!-- Formulario -->
        <div
          v-if="showVisitForm"
          class="bg-white/90 backdrop-blur rounded-3xl shadow-xl border border-gray-100 overflow-hidden"
        >
          <!-- Header -->
          <div
            class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50"
          >
            <h2 class="text-2xl font-bold text-gray-800">
              Detalles de la Visita
            </h2>

            <p class="text-sm text-gray-500 mt-1">
              Complete la información requerida
            </p>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleCreateVisit" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <!-- Área -->
              <div
                :class="
                  currentVisitor.edad < 18 ? 'col-span-1' : 'md:col-span-2'
                "
              >
                <label
                  for="visit-area"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                >
                  Área a Visitar
                  <span class="text-red-500">*</span>
                </label>

                <select
                  id="visit-area"
                  v-model="visitForm.area_id"
                  class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200"
                  :class="{
                    'border-red-500 bg-red-50': frontendErrors.area_id,
                  }"
                >
                  <option :value="null" disabled>
                    -- Seleccione un área --
                  </option>

                  <option
                    v-for="area in dataStore.areas"
                    :key="area.id"
                    :value="area.id"
                  >
                    {{ area.nombre }}
                  </option>
                </select>

                <p
                  v-if="frontendErrors.area_id"
                  class="text-red-500 text-sm mt-2"
                >
                  {{ frontendErrors.area_id }}
                </p>
              </div>

              <!-- Responsable -->
              <div v-if="currentVisitor.edad < 18">
                <label
                  for="visit-responsable"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                >
                  Responsable
                </label>

                <input
                  id="visit-responsable"
                  v-model="visitForm.responsable"
                  type="text"
                  placeholder="Nombre del acompañante adulto"
                  class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200"
                />
              </div>
            </div>

            <!-- Motivo -->
            <div>
              <label
                for="visit-motivo"
                class="block text-sm font-semibold text-gray-700 mb-2"
              >
                Motivo de la Visita
              </label>

              <textarea
                id="visit-motivo"
                v-model="visitForm.motivo"
                rows="3"
                class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200"
              ></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-end pt-4 border-t border-gray-100">
              <button
                type="submit"
                class="px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.02]"
              >
                Registrar Visita
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- TABLA -->
      <div
        class="bg-white/90 backdrop-blur rounded-3xl shadow-xl border border-gray-100 overflow-hidden"
      >
        <!-- Header -->
        <div
          class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-6 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white"
        >
          <div>
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">
              Gestión de Visitas
            </h2>

            <p class="text-sm text-gray-500 mt-1">
              Administra las visitas registradas en el sistema
            </p>
          </div>

          <!-- Stats -->
          <div class="flex flex-wrap gap-3">
            <div
              class="px-4 py-3 rounded-2xl bg-blue-50 border border-blue-100 min-w-[120px]"
            >
              <p class="text-xs text-blue-500 font-semibold uppercase">
                Activas
              </p>

              <p class="text-2xl font-extrabold text-blue-700">
                {{ activeCount }}
              </p>
            </div>

            <div
              class="px-4 py-3 rounded-2xl bg-green-50 border border-green-100 min-w-[120px]"
            >
              <p class="text-xs text-green-500 font-semibold uppercase">
                Finalizadas
              </p>

              <p class="text-2xl font-extrabold text-green-700">
                {{ finalizedCount }}
              </p>
            </div>

            <div
              class="px-4 py-3 rounded-2xl bg-red-50 border border-red-100 min-w-[120px]"
            >
              <p class="text-xs text-red-500 font-semibold uppercase">
                Vencidas
              </p>

              <p class="text-2xl font-extrabold text-red-700">
                {{ expiredCount }}
              </p>
            </div>
          </div>
        </div>

        <!-- Toolbar -->
        <div
          class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white"
        >
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <!-- Search -->
            <div class="relative w-full sm:w-96">
              <InputText
                v-model="search"
                placeholder="Buscar visitante, documento o área..."
                class="w-full !pl-11 !pr-4 !py-3 !rounded-2xl !border !border-gray-300 focus:!ring-4 focus:!ring-blue-100 focus:!border-blue-500"
                @keyup.enter="doSearch"
              />

              <i
                class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
              ></i>
            </div>

            <!-- Buscar -->
            <button
              @click="doSearch"
              class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-all duration-200 shadow-md hover:shadow-lg"
            >
              Buscar
            </button>

            <!-- Limpiar -->
            <button
              @click="clearSearch"
              class="px-5 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-200"
            >
              Limpiar
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="p-5">
          <TabView
            v-if="!visitStore.loading && !modalState.loading"
            class="custom-tabs"
          >
            <!-- ACTIVAS -->
            <TabPanel>
              <template #header>
                <div class="flex items-center gap-2">
                  <i class="pi pi-clock"></i>

                  <span>Visitas Activas</span>

                  <span
                    class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                  >
                    {{ activeCount }}
                  </span>
                </div>
              </template>

              <DataTable
                :value="filteredActiveVisits"
                :paginator="true"
                :rows="rows"
                :first="firstActive"
                :totalRecords="filteredActiveVisits.length"
                responsiveLayout="scroll"
                stripedRows
                paginatorTemplate="PrevPageLink PageLinks NextPageLink"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords}"
                class="modern-table"
                @page="onActivePageChange"
              >
                <Column field="id" header="ID" sortable>
                  <template #body="{ data }">
                    <span
                      class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-bold"
                    >
                      {{ data.id }}
                    </span>
                  </template>
                </Column>

                <Column header="Visitante" sortable>
                  <template #body="{ data }">
                    <div>
                      <div class="font-semibold text-gray-800">
                        {{ data.visitante.nombres }}
                        {{ data.visitante.apellidos }}
                      </div>

                      <div class="text-sm text-gray-500">
                        {{ data.visitante.documento_identidad }}
                      </div>
                    </div>
                  </template>
                </Column>

                <Column field="area.nombre" header="Área" sortable>
                  <template #body="{ data }">
                    <span
                      class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700"
                    >
                      {{ data.area.nombre }}
                    </span>
                  </template>
                </Column>

                <Column field="hora_entrada" header="Hora Entrada" sortable>
                  <template #body="{ data }">
                    {{ formatTime(data.hora_entrada) }}
                  </template>
                </Column>

                <Column header="Acción">
                  <template #body="{ data }">
                    <Button
                      icon="pi pi-sign-out"
                      label="Dar Salida"
                      class="p-button-success p-button-sm rounded-xl"
                      @click="confirmCheckOut(data)"
                    />
                  </template>
                </Column>

                <template #empty>
                  <div class="text-center py-12">
                    <i class="pi pi-inbox text-5xl text-gray-300"></i>

                    <p class="mt-3 text-gray-500">No hay visitas activas.</p>
                  </div>
                </template>
              </DataTable>
            </TabPanel>

            <!-- FINALIZADAS -->
            <TabPanel>
              <template #header>
                <div class="flex items-center gap-2">
                  <i class="pi pi-check"></i>

                  <span>Visitas Finalizadas</span>

                  <span
                    class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                  >
                    {{ finalizedCount }}
                  </span>
                </div>
              </template>

              <DataTable
                :value="filteredFinalizedVisits"
                :paginator="true"
                :rows="rows"
                :first="firstFinalized"
                :totalRecords="filteredFinalizedVisits.length"
                responsiveLayout="scroll"
                stripedRows
                paginatorTemplate="PrevPageLink PageLinks NextPageLink"
                class="modern-table"
                @page="onFinalizedPageChange"
              >
                <Column field="id" header="ID" sortable />

                <Column header="Visitante">
                  <template #body="{ data }">
                    {{ data.visitante.nombres }}
                    {{ data.visitante.apellidos }}
                  </template>
                </Column>

                <Column field="area.nombre" header="Área" />

                <Column field="hora_entrada" header="Hora Entrada">
                  <template #body="{ data }">
                    {{ formatTime(data.hora_entrada) }}
                  </template>
                </Column>

                <Column field="hora_salida" header="Hora Salida">
                  <template #body="{ data }">
                    {{ formatTime(data.hora_salida) }}
                  </template>
                </Column>
              </DataTable>
            </TabPanel>

            <!-- VENCIDAS -->
            <TabPanel>
              <template #header>
                <div class="flex items-center gap-2">
                  <i class="pi pi-times-circle"></i>

                  <span>Visitas Vencidas</span>

                  <span
                    class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"
                  >
                    {{ expiredCount }}
                  </span>
                </div>
              </template>

              <DataTable
                :value="filteredExpiredVisits"
                :paginator="true"
                :rows="rows"
                :first="firstExpired"
                :totalRecords="filteredExpiredVisits.length"
                responsiveLayout="scroll"
                stripedRows
                paginatorTemplate="PrevPageLink PageLinks NextPageLink"
                class="modern-table"
                @page="onExpiredPageChange"
              >
                <Column field="id" header="ID" sortable />

                <Column header="Visitante">
                  <template #body="{ data }">
                    {{ data.visitante.nombres }}
                    {{ data.visitante.apellidos }}
                  </template>
                </Column>

                <Column field="area.nombre" header="Área" />

                <Column field="fecha" header="Fecha" />

                <Column field="hora_entrada" header="Hora Entrada">
                  <template #body="{ data }">
                    {{ formatTime(data.hora_entrada) }}
                  </template>
                </Column>

                <Column header="Acción">
                  <template #body="{ data }">
                    <Button
                      icon="pi pi-times"
                      label="Cerrar"
                      class="p-button-warning p-button-sm rounded-xl"
                      @click="confirmClose(data)"
                    />
                  </template>
                </Column>
              </DataTable>
            </TabPanel>
          </TabView>

          <!-- Loading -->
          <div v-else class="py-20 flex justify-center items-center">
            <div
              class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed, reactive } from "vue";
import VisitorSearchOrCreate from "../components/VisitorSearchOrCreate.vue";
import BaseModal from "../components/BaseModal.vue";
import { useReservationStore } from "../stores/reservationStore";
import { useVisitStore } from "../stores/visitStore";
import { useDataStore } from "../stores/dataStore";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

// PrimeVue
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

const visitStore = useVisitStore();
const dataStore = useDataStore();
const reservationStore = useReservationStore();
const confirm = useConfirm();
const toast = useToast();

// Modal centralizado para loading / success
const modalState = reactive({
  loading: false,
  success: false,
  title: "",
  message: "",
  // action: 'create' | 'checkout' | 'close' | 'validation' | null
  action: null,
});

const onModalSuccessChange = (val) => {
  // val === true -> modal opened; val === false -> modal closed
  modalState.success = val;
  if (!val && modalState.action) {
    // modal was closed after a success/error. Handle post-close actions for successes.
    const act = modalState.action;
    // clear action before performing to avoid reentrancy
    modalState.action = null;

    if (act === "create" || act === "checkout" || act === "close") {
      // After closing the success modal: refresh list and hide visitor form
      visitStore.fetchActiveVisits();
      // hide visitor form / reset selection
      resetFlow();
    }
    // for 'validation' or errors we don't auto-reset or refetch
  }
};

// --- Estado del Visitante y Formulario ---
const currentVisitor = ref(null);
const visitForm = ref({
  visitante_id: null,
  reserva_id: null,
  fecha: "",
  hora_entrada: "",
  motivo: "",
  area_id: null,
  responsable: "",
  no_carnet: "",
  estado: "activa",
});
const frontendErrors = ref({});
const showVisitForm = ref(false);

// --- Estado de la tabla ---
const search = ref("");
const rows = ref(10);
const firstActive = ref(0);
const firstFinalized = ref(0);
const firstExpired = ref(0);

onMounted(() => {
  visitStore.fetchActiveVisits();
  dataStore.fetchAll();
});

// --- Handlers para el visitante seleccionado ---
const handleVisitorSelected = async (visitor) => {
  currentVisitor.value = visitor;
  showVisitForm.value = false; // hide while checking for reservation
  await reservationStore.fetchPendingForVisitor(visitor.id);
  if (!reservationStore.pendingReservation) {
    // no reservation: prepare and show form
    prepareVisitForm();
    showVisitForm.value = true;
  }
  // if there is a pending reservation, the template will show the reservation block
};

const useReservation = () => {
  prepareVisitForm(reservationStore.pendingReservation);
  showVisitForm.value = true;
};

const ignoreReservation = () => {
  reservationStore.clearPending();
  prepareVisitForm();
  showVisitForm.value = true;
};

const prepareVisitForm = (reserva = null) => {
  const now = new Date();
  visitForm.value = {
    visitante_id: currentVisitor.value.id,
    reserva_id: reserva ? reserva.id : null,
    fecha: now.toISOString().split("T")[0],
    hora_entrada: now.toTimeString().split(" ")[0].substring(0, 5),
    motivo: reserva ? reserva.motivo : "",
    area_id: reserva ? reserva.area_id : null,
    responsable: "",
    no_carnet: "",
    estado: "activa",
  };
  frontendErrors.value = {};
};

const validateForm = () => {
  frontendErrors.value = {};

  if (!visitForm.value.area_id) {
    frontendErrors.value.area_id = "Área a Visitar es obligatoria";
  }

  return Object.keys(frontendErrors.value).length === 0;
};

const handleCreateVisit = async () => {
  if (!validateForm()) {
    // Build validation message
    const messages =
      Object.values(frontendErrors.value).flat().join("\n") ||
      "Por favor completa los campos requeridos.";
    modalState.title = "Campos Obligatorios";
    modalState.message = messages;
    modalState.action = "validation";
    modalState.success = true;
    return;
  }

  try {
    modalState.loading = true;
    const result = await visitStore.createVisit(visitForm.value);
    modalState.loading = false;

    if (result.success) {
      modalState.title = "Visita registrada";
      modalState.message = "La visita ha sido registrada correctamente.";
      modalState.action = "create";
      modalState.success = true; // will be handled on close
    } else {
      modalState.title = "Error al registrar";
      modalState.message = "Hubo un problema al registrar la visita.";
      modalState.action = null;
      modalState.success = true;
    }
  } catch (error) {
    modalState.loading = false;
    modalState.title = "Error";
    modalState.message = "Ocurrió un error inesperado.";
    modalState.action = null;
    modalState.success = true;
  }
};

const resetFlow = () => {
  currentVisitor.value = null;
  visitForm.value = {
    visitante_id: null,
    reserva_id: null,
    fecha: "",
    hora_entrada: "",
    motivo: "",
    area_id: null,
    responsable: "",
    no_carnet: "",
    estado: "activa",
  };
  frontendErrors.value = {};
};

// --- Helpers ---
const isExpired = (visit) => {
  if (!visit.fecha) return false;

  // Forzar que JS interprete la fecha como local
  const [year, month, day] = visit.fecha.split("-").map(Number);
  const visitDate = new Date(year, month - 1, day); // ← Esto sí es local

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  return visitDate < today;
};

// --- Clasificación de visitas ---
const activeVisits = computed(() => {
  return visitStore.activeVisits.filter(
    (v) => v.estado === "activa" && !isExpired(v),
  );
});

const finalizedVisits = computed(() => {
  return visitStore.activeVisits.filter((v) => v.estado === "finalizada");
});

const expiredVisits = computed(() => {
  return visitStore.activeVisits.filter(
    (v) => isExpired(v) && v.estado !== "finalizada",
  );
});

// --- Contadores ---
const activeCount = computed(() => filteredActiveVisits.value.length);
const finalizedCount = computed(() => filteredFinalizedVisits.value.length);
const expiredCount = computed(() => filteredExpiredVisits.value.length);

// --- Búsqueda y filtrado ---
const filterBySearch = (visits) => {
  if (!search.value.trim()) {
    return visits;
  }

  const query = search.value.toLowerCase();
  return visits.filter((visit) => {
    const nombres =
      `${visit.visitante.nombres} ${visit.visitante.apellidos}`.toLowerCase();
    const documento = visit.visitante.documento_identidad.toLowerCase();
    const area = visit.area.nombre.toLowerCase();

    return (
      nombres.includes(query) ||
      documento.includes(query) ||
      area.includes(query)
    );
  });
};

const filteredActiveVisits = computed(() => filterBySearch(activeVisits.value));
const filteredFinalizedVisits = computed(() =>
  filterBySearch(finalizedVisits.value),
);
const filteredExpiredVisits = computed(() =>
  filterBySearch(expiredVisits.value),
);

const doSearch = () => {
  firstActive.value = 0;
  firstFinalized.value = 0;
  firstExpired.value = 0;
};

const clearSearch = () => {
  search.value = "";
  firstActive.value = 0;
  firstFinalized.value = 0;
  firstExpired.value = 0;
};

const onActivePageChange = (evt) => {
  firstActive.value = evt.first;
  rows.value = evt.rows;
};

const onFinalizedPageChange = (evt) => {
  firstFinalized.value = evt.first;
  rows.value = evt.rows;
};

const onExpiredPageChange = (evt) => {
  firstExpired.value = evt.first;
  rows.value = evt.rows;
};

const openNewVisit = () => {
  toast.add({
    severity: "info",
    summary: "Nueva Visita",
    detail: "Navegando a registro de visita...",
    life: 2000,
  });
  // Aquí puedes navegar a la vista de crear visita
  // router.push('/visit-manager');
};

const formatTime = (time) => {
  if (!time) return "";

  // Crear fecha ficticia para aprovechar toLocaleString
  const date = new Date(`1970-01-01T${time}`);

  return date.toLocaleTimeString("es-DO", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
};

const confirmCheckOut = (visit) => {
  confirm.require({
    message: `¿Confirmas la salida de ${visit.visitante.nombres} ${visit.visitante.apellidos}?`,
    header: "Confirmar Salida",
    icon: "pi pi-exclamation-triangle",
    acceptClass: "p-button-success",
    acceptLabel: "Sí, dar salida",
    rejectLabel: "Cancelar",
    accept: async () => {
      await checkOut(visit);
    },
  });
};

const checkOut = async (visit) => {
  try {
    const now = new Date();
    const hora_salida = now.toTimeString().split(" ")[0].substring(0, 5); // HH:MM

    console.log("Dando salida a visita:", {
      id: visit.id,
      estado: "finalizada",
      hora_salida,
    });

    modalState.loading = true;
    const result = await visitStore.updateVisit({
      id: visit.id,
      estado: "finalizada",
      hora_salida: hora_salida,
    });
    modalState.loading = false;

    if (result.success) {
      modalState.title = "Salida registrada";
      modalState.message = `${visit.visitante.nombres} ha salido exitosamente.`;
      modalState.action = "checkout";
      modalState.success = true; // actualización y reset se harán al cerrar
    } else {
      console.error("Error en respuesta:", result.errors);
      modalState.title = "Error";
      modalState.message =
        "Hubo un error al registrar la salida: " +
        JSON.stringify(result.errors);
      modalState.action = null;
      modalState.success = true;
    }
  } catch (error) {
    console.error("Error al registrar salida:", error);
    modalState.loading = false;
    modalState.title = "Error";
    modalState.message = "No se pudo procesar la salida: " + error.message;
    modalState.action = null;
    modalState.success = true;
  }
};

const confirmClose = (visit) => {
  confirm.require({
    message: `¿Deseas cerrar la visita vencida de ${visit.visitante.nombres} ${visit.visitante.apellidos}?`,
    header: "Cerrar Visita Vencida",
    icon: "pi pi-exclamation-triangle",
    acceptClass: "p-button-warning",
    acceptLabel: "Sí, cerrar",
    rejectLabel: "Cancelar",
    accept: async () => {
      await closeExpiredVisit(visit);
    },
  });
};

const closeExpiredVisit = async (visit) => {
  try {
    const now = new Date();
    const hora_salida = now.toTimeString().split(" ")[0].substring(0, 5); // HH:MM

    console.log("Cerrando visita vencida:", {
      id: visit.id,
      estado: "finalizada",
      hora_salida,
    });

    modalState.loading = true;
    const result = await visitStore.updateVisit({
      id: visit.id,
      estado: "finalizada",
      hora_salida: hora_salida,
    });
    modalState.loading = false;

    if (result.success) {
      modalState.title = "Visita cerrada";
      modalState.message = `Visita vencida cerrada exitosamente.`;
      modalState.action = "close";
      modalState.success = true;
      // actualización will be triggered after modal close
    } else {
      console.error("Error en respuesta:", result.errors);
      modalState.title = "Error";
      modalState.message =
        "Hubo un error al cerrar la visita: " + JSON.stringify(result.errors);
      modalState.action = null;
      modalState.success = true;
    }
  } catch (error) {
    console.error("Error al cerrar visita:", error);
    modalState.loading = false;
    modalState.title = "Error";
    modalState.message =
      "No se pudo procesar el cierre de la visita: " + error.message;
    modalState.action = null;
    modalState.success = true;
  }
};
</script>
