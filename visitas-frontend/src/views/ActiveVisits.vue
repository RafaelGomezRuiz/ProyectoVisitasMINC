<template>
  <div class="space-y-10 bg-gray-50 min-h-screen p-4 md:p-8">
    <!-- Modales: loading / success -->
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

    <!-- Paso 1: Buscar o crear visitante -->
    <!-- <VisitorSearchOrCreate @visitor-selected="handleVisitorSelected" /> -->

    <!-- Paso 2: Mostrar visitante y gestionar visita -->
    <div v-if="currentVisitor" class="space-y-8">
      <!-- Info del Visitante Seleccionado -->
      <div
        class="bg-white p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100"
      >
        <div
          class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
        >
          <div>
            <h2 class="text-2xl md:text-3xl font-bold text-blue-700">
              Nombre: {{ currentVisitor.nombres }}
              {{ currentVisitor.apellidos }}
            </h2>
            <p class="text-gray-600 mt-1">
              Documento de identidad:
              <span class="font-medium">
                {{ currentVisitor.documento_identidad }}
              </span>
            </p>
          </div>

          <button
            @click="resetFlow"
            class="self-start md:self-auto px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
          >
            Registrar otra visita
          </button>
        </div>
      </div>

      <!-- Cargando reserva -->
      <div
        v-if="reservationStore.loading"
        class="flex flex-col items-center justify-center py-10 text-gray-600"
      >
        <ProgressSpinner />
        <span class="mt-3 text-sm">Buscando reserva pendiente...</span>
      </div>

      <!-- Caso A: Reserva encontrada -->
      <div
        v-if="reservationStore.pendingReservation"
        class="bg-green-50 border border-green-200 border-l-4 border-l-green-500 p-6 rounded-2xl shadow-sm"
      >
        <h3 class="text-xl font-bold text-green-800">
          Reserva Pendiente Encontrada
        </h3>
        <p class="text-green-700 mt-2">
          Este visitante tiene una reserva para el
          <strong>{{ reservationStore.pendingReservation.fecha }}</strong> a las
          <strong>{{ reservationStore.pendingReservation.hora }}</strong
          >.
        </p>
        <p class="mt-1 text-green-700">
          Motivo:
          {{ reservationStore.pendingReservation.motivo || "No especificado" }}
        </p>

        <div class="mt-5 flex flex-wrap gap-3">
          <button
            @click="useReservation"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
          >
            Usar Reserva y Registrar Visita
          </button>

          <button
            @click="ignoreReservation"
            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
          >
            Ignorar y registrar visita normal
          </button>
        </div>
      </div>

      <!-- Caso B: Formulario de Visita -->
      <div
        v-if="showVisitForm"
        class="bg-white p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100"
      >
        <h3 class="text-xl font-bold text-gray-800 mb-6">
          Detalles de la Visita
        </h3>

        <form @submit.prevent="handleCreateVisit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div
              :class="currentVisitor.edad < 18 ? 'col-span-1' : 'col-span-2'"
            >
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Área a Visitar <span class="text-red-500">*</span>
              </label>

              <select
                v-model="visitForm.area_id"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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

              <p
                v-if="frontendErrors.area_id"
                class="text-red-500 text-sm mt-1"
              >
                {{ frontendErrors.area_id }}
              </p>
            </div>

            <div v-if="currentVisitor.edad < 18">
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Responsable (si es menor de edad)
              </label>

              <input
                v-model="visitForm.responsable"
                type="text"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                placeholder="Nombre del acompañante adulto"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Motivo de la Visita (Opcional)
            </label>

            <textarea
              v-model="visitForm.motivo"
              rows="3"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-md transition"
            >
              Registrar Visita
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- LISTADO DE VISITAS -->
    <Card class="bg-white rounded-2xl shadow-lg border border-gray-100">
      <template #title>
        <div class="p-4 border-b border-gray-100">
          <h2 class="text-xl font-bold text-gray-800">Visitas</h2>
        </div>
      </template>

      <template #content>
        <!-- Toolbar -->
        <Toolbar class="bg-gray-50 rounded-xl mb-5 border border-gray-100">
          <template #start>
            <div class="flex flex-col md:flex-row gap-3 w-full">
              <InputText
                v-model="search"
                placeholder="Buscar visitante, documento o área"
                class="w-full md:w-80"
                @keyup.enter="doSearch"
              />
              <Button label="Buscar" icon="pi pi-search" @click="doSearch" />
              <Button
                label="Limpiar"
                icon="pi pi-times"
                text
                @click="clearSearch"
              />
            </div>
          </template>
        </Toolbar>

        <!-- Tabs -->
        <TabView
          v-if="!visitStore.loading && !modalState.loading"
          class="bg-white"
        >
          <!-- ACTIVAS -->
          <TabPanel>
            <template #header>
              <div class="flex items-center gap-2">
                <i class="pi pi-clock"></i>
                <span>Activas</span>
                <Badge :value="activeCount" />
              </div>
            </template>

            <DataTable
              :value="filteredActiveVisits"
              :paginator="true"
              :rows="rows"
              stripedRows
              class="p-datatable-sm"
            >
              <Column field="id" header="ID" />
              <Column header="Visitante">
                <template #body="{ data }">
                  {{ data.visitante.nombres }} {{ data.visitante.apellidos }}
                </template>
              </Column>
              <Column field="area.nombre" header="Área" />
              <Column field="hora_entrada" header="Entrada">
                <template #body="{ data }">
                  {{ formatTime(data.hora_entrada) }}
                </template>
              </Column>
            </DataTable>
          </TabPanel>

          <!-- FINALIZADAS -->
          <TabPanel>
            <template #header>
              <div class="flex items-center gap-2">
                <i class="pi pi-check"></i>
                <span>Finalizadas</span>
                <Badge :value="finalizedCount" severity="success" />
              </div>
            </template>

            <DataTable :value="filteredFinalizedVisits" class="p-datatable-sm">
              <Column header="Visitante">
                <template #body="{ data }">
                  {{ data.visitante.nombres }} {{ data.visitante.apellidos }}
                </template>
              </Column>
              <Column field="hora_salida" header="Salida">
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
                <span>Vencidas</span>
                <Badge :value="expiredCount" severity="danger" />
              </div>
            </template>

            <DataTable :value="filteredExpiredVisits" class="p-datatable-sm">
              <Column header="Visitante">
                <template #body="{ data }">
                  {{ data.visitante.nombres }} {{ data.visitante.apellidos }}
                </template>
              </Column>
              <Column field="fecha" header="Fecha" />
            </DataTable>
          </TabPanel>
        </TabView>

        <div v-else class="flex justify-center py-10">
          <ProgressSpinner />
        </div>
      </template>
    </Card>
  </div>
</template>
<script setup>
import { onMounted, ref, computed, reactive } from "vue";
// import VisitorSearchOrCreate from "../components/VisitorSearchOrCreate.vue";
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
