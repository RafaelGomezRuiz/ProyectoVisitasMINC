<template>
  <div class="space-y-8">
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
    <VisitorSearchOrCreate @visitor-selected="handleVisitorSelected" />

    <!-- Paso 2: Mostrar visitante y gestionar visita -->
    <div v-if="currentVisitor" class="space-y-8">
      <!-- Info del Visitante Seleccionado -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-bold text-blue-800">Nombre: {{ currentVisitor.nombres }} {{ currentVisitor.apellidos }}</h2>
            <p class="text-gray-600">Documento de identidad: {{ currentVisitor.documento_identidad }}</p>
          </div>
          <button @click="resetFlow" class="text-sm text-blue-600 hover:underline">Registrar otra visita</button>
        </div>
      </div>

      <!-- Cargando reserva -->
      <div v-if="reservationStore.loading" class="text-center p-8 flex flex-col items-center justify-center">
        <ProgressSpinner />
        <span class="mt-2 text-gray-600">Buscando reserva pendiente...</span>
      </div>

      <!-- Caso A: Reserva encontrada -->
      <div v-if="reservationStore.pendingReservation" class="bg-green-50 border-l-4 border-green-500 p-6 rounded-r-lg shadow-lg">
        <h3 class="text-xl font-bold text-green-800">Reserva Pendiente Encontrada</h3>
        <p class="text-green-700 mt-2">
          Este visitante tiene una reserva para el <strong>{{ reservationStore.pendingReservation.fecha }}</strong> a las <strong>{{ reservationStore.pendingReservation.hora }}</strong>.
        </p>
        <p class="mt-1 text-green-700">Motivo: {{ reservationStore.pendingReservation.motivo || 'No especificado' }}</p>
        <div class="mt-4">
          <button @click="useReservation" class="btn-primary">Usar Reserva y Registrar Visita</button>
          <button @click="ignoreReservation" class="ml-4 text-sm text-gray-600 hover:underline">Ignorar y registrar visita normal</button>
        </div>
      </div>

      <!-- Caso B: Formulario de Visita (se muestra si no hay reserva o si se decide ignorar) -->
      <div v-if="showVisitForm" class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Detalles de la Visita</h3>
        <form @submit.prevent="handleCreateVisit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div :class="currentVisitor.edad < 18 ? 'col-span-1' : 'col-span-2'">
              <label for="visit-area" class="block text-gray-700 font-semibold">Área a Visitar <span class="text-red-600">*</span> </label>

              <select
                id="visit-area"
                v-model="visitForm.area_id"
                class="input"
                :class="{'border-red-500 bg-red-50': frontendErrors.area_id}"
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

              <p v-if="frontendErrors.area_id" class="text-red-500 text-sm mt-1">{{ frontendErrors.area_id }} </p>
            </div>

            <div v-if="currentVisitor.edad < 18">
              <label for="visit-responsable" class="block text-gray-700 font-semibold">Responsable (si es menor de edad)</label>
              <input id="visit-responsable" v-model="visitForm.responsable" type="text" class="input" placeholder="Nombre del acompañante adulto">
            </div>
          </div>
          <div>
            <label for="visit-motivo" class="block text-gray-700 font-semibold">Motivo de la Visita (Opcional)</label>
            <textarea id="visit-motivo" v-model="visitForm.motivo" rows="2" class="input"></textarea>
          </div>

          <div class="flex justify-end pt-4">
            <button type="submit" class="btn-primary">Registrar Visita</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Listado de Visitas Activas con pestañas: Activas, Finalizadas, Vencidas -->
    <Card class="bg-white shadow-sm border-0">
      <template #title>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-900">Visitas</h2>
        </div>
      </template>

      <template #content>
        <!-- Toolbar de búsqueda global -->
        <Toolbar class="mb-4 bg-gray-50 border-gray-200 rounded-lg">
          <template #start>
            <div class="flex items-center gap-2">
              <InputText v-model="search" placeholder="Buscar por visitante, documento o área" @keyup.enter="doSearch" />
              <Button icon="pi pi-search" label="Buscar" class="p-button-secondary" @click="doSearch" />
              <Button icon="pi pi-times" label="Limpiar" class="p-button-text" @click="clearSearch" />
            </div>
          </template>
        </Toolbar>

        <!-- Tabs con tres secciones -->
        <TabView v-if="!visitStore.loading && !modalState.loading" class="p-tabview-sm">
        <!-- Tab 1: Visitas Activas -->
        <TabPanel>
          <template #header>
            <div class="flex items-center gap-2">
              <i class="pi pi-clock"></i>
              <span>Visitas Activas</span>
              <Badge :value="activeCount" severity="info" class="ml-2" />
            </div>
          </template>
          <DataTable
            :value="filteredActiveVisits"
            :paginator="true"
            :rows="rows"
            :first="firstActive"
            :totalRecords="filteredActiveVisits.length"
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
            <Column field="visitante.documento_identidad" header="Documento" sortable />
            <Column field="area.nombre" header="Área" sortable />
            <Column field="hora_entrada" header="Hora de Entrada" sortable>
              <template #body="{ data }">
                {{ formatTime(data.hora_entrada) }}
              </template>
            </Column>
            <Column header="Acción" style="width: 160px">
              <template #body="{ data }">
                <Button 
                  icon="pi pi-sign-out" 
                  label="Dar Salida"
                  class="p-button-success p-button-sm"
                  @click="confirmCheckOut(data)"
                />
              </template>
            </Column>
            <template #empty>
              <div class="text-center py-10">
                <i class="pi pi-inbox text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">No hay visitas activas en este momento.</p>
              </div>
            </template>
          </DataTable>
        </TabPanel>

        <!-- Tab 2: Visitas Finalizadas -->
        <TabPanel>
          <template #header>
            <div class="flex items-center gap-2">
              <i class="pi pi-check"></i>
              <span>Visitas Finalizadas</span>
              <Badge :value="finalizedCount" severity="success" class="ml-2" />
            </div>
          </template>
          <DataTable
            :value="filteredFinalizedVisits"
            :paginator="true"
            :rows="rows"
            :first="firstFinalized"
            :totalRecords="filteredFinalizedVisits.length"
            responsiveLayout="scroll"
            class="p-datatable-sm"
            stripedRows
            @page="onFinalizedPageChange"
          >
            <Column field="id" header="ID" style="width: 80px" sortable />
            <Column field="visitante.nombres" header="Visitante" sortable>
              <template #body="{ data }">
                {{ data.visitante.nombres }} {{ data.visitante.apellidos }}
              </template>
            </Column>
            <Column field="visitante.documento_identidad" header="Documento" sortable />
            <Column field="area.nombre" header="Área" sortable />
            <Column field="hora_entrada" header="Hora de Entrada" sortable>
              <template #body="{ data }">
                {{ formatTime(data.hora_entrada) }}
              </template>
            </Column>
            <Column field="hora_salida" header="Hora de Salida" sortable>
              <template #body="{ data }">
                {{ formatTime(data.hora_salida) }}
              </template>
            </Column>
            <template #empty>
              <div class="text-center py-10">
                <i class="pi pi-inbox text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">No hay visitas finalizadas.</p>
              </div>
            </template>
          </DataTable>
        </TabPanel>

        <!-- Tab 3: Visitas Vencidas -->
        <TabPanel>
          <template #header>
            <div class="flex items-center gap-2">
              <i class="pi pi-times-circle"></i>
              <span>Visitas Vencidas</span>
              <Badge :value="expiredCount" severity="danger" class="ml-2" />
            </div>
          </template>
          <DataTable
            :value="filteredExpiredVisits"
            :paginator="true"
            :rows="rows"
            :first="firstExpired"
            :totalRecords="filteredExpiredVisits.length"
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
            <Column field="visitante.documento_identidad" header="Documento" sortable />
            <Column field="area.nombre" header="Área" sortable />
            <Column field="fecha" header="Fecha" sortable />
            <Column field="hora_entrada" header="Hora de Entrada" sortable>
              <template #body="{ data }">
                {{ formatTime(data.hora_entrada) }}
              </template>
            </Column>
            <Column header="Acción" style="width: 160px">
              <template #body="{ data }">
                <Button 
                  icon="pi pi-times" 
                  label="Cerrar"
                  class="p-button-warning p-button-sm"
                  @click="confirmClose(data)"
                />
              </template>
            </Column>
            <template #empty>
              <div class="text-center py-10">
                <i class="pi pi-inbox text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">No hay visitas vencidas.</p>
              </div>
            </template>
          </DataTable>
        </TabPanel>
      </TabView>

      <div v-else-if="visitStore.loading" class="flex justify-center items-center py-10">
        <ProgressSpinner />
      </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { onMounted, ref, computed, reactive } from 'vue';
import VisitorSearchOrCreate from '../components/VisitorSearchOrCreate.vue';
import BaseModal from '../components/BaseModal.vue';
import { useReservationStore } from '../stores/reservationStore';
import { useVisitStore } from '../stores/visitStore';
import { useDataStore } from '../stores/dataStore';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

// PrimeVue
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Badge from 'primevue/badge';

const visitStore = useVisitStore();
const dataStore = useDataStore();
const reservationStore = useReservationStore();
const confirm = useConfirm();
const toast = useToast();

// Modal centralizado para loading / success
const modalState = reactive({
  loading: false,
  success: false,
  title: '',
  message: '',
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

    if (act === 'create' || act === 'checkout' || act === 'close') {
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
    fecha: '',
    hora_entrada: '',
    motivo: '',
    area_id: null,
    responsable: '',
    no_carnet: '',
    estado: 'activa',
});
const frontendErrors = ref({});
const showVisitForm = ref(false);

// --- Estado de la tabla ---
const search = ref('');
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
    fecha: now.toISOString().split('T')[0],
    hora_entrada: now.toTimeString().split(' ')[0].substring(0, 5),
    motivo: reserva ? reserva.motivo : '',
    area_id: reserva ? reserva.area_id : null,
    responsable: '',
    no_carnet: '',
    estado: 'activa',
  };
  frontendErrors.value = {};
};

const validateForm = () => {
    frontendErrors.value = {};
    
    if (!visitForm.value.area_id) {
        frontendErrors.value.area_id = 'Área a Visitar es obligatoria';
    }
    
    return Object.keys(frontendErrors.value).length === 0;
};

const handleCreateVisit = async () => {
    if (!validateForm()) {
        // Build validation message
        const messages = Object.values(frontendErrors.value).flat().join('\n') || 'Por favor completa los campos requeridos.';
        modalState.title = 'Campos Obligatorios';
        modalState.message = messages;
        modalState.action = 'validation';
        modalState.success = true;
        return;
    }

    try {
      modalState.loading = true;
      const result = await visitStore.createVisit(visitForm.value);
      modalState.loading = false;
        
      if (result.success) {
        modalState.title = 'Visita registrada';
        modalState.message = 'La visita ha sido registrada correctamente.';
        modalState.action = 'create';
        modalState.success = true; // will be handled on close
      } else {
        modalState.title = 'Error al registrar';
        modalState.message = 'Hubo un problema al registrar la visita.';
        modalState.action = null;
        modalState.success = true;
      }
    } catch (error) {
      modalState.loading = false;
      modalState.title = 'Error';
      modalState.message = 'Ocurrió un error inesperado.';
      modalState.action = null;
      modalState.success = true;
    }
};

const resetFlow = () => {
    currentVisitor.value = null;
    visitForm.value = {
        visitante_id: null,
        reserva_id: null,
        fecha: '',
        hora_entrada: '',
        motivo: '',
        area_id: null,
        responsable: '',
        no_carnet: '',
        estado: 'activa',
    };
    frontendErrors.value = {};
};

// --- Helpers ---
const isExpired = (visit) => {
    if (!visit.fecha) return false;

    // Forzar que JS interprete la fecha como local
    const [year, month, day] = visit.fecha.split('-').map(Number);
    const visitDate = new Date(year, month - 1, day); // ← Esto sí es local

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return visitDate < today;
};



// --- Clasificación de visitas ---
const activeVisits = computed(() => {
    return visitStore.activeVisits.filter(v => v.estado === 'activa' && !isExpired(v));
});

const finalizedVisits = computed(() => {
    return visitStore.activeVisits.filter(v => v.estado === 'finalizada');
});

const expiredVisits = computed(() => {
    return visitStore.activeVisits.filter(v => isExpired(v) && v.estado !== 'finalizada');
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
    return visits.filter(visit => {
        const nombres = `${visit.visitante.nombres} ${visit.visitante.apellidos}`.toLowerCase();
        const documento = visit.visitante.documento_identidad.toLowerCase();
        const area = visit.area.nombre.toLowerCase();

        return nombres.includes(query) || documento.includes(query) || area.includes(query);
    });
};

const filteredActiveVisits = computed(() => filterBySearch(activeVisits.value));
const filteredFinalizedVisits = computed(() => filterBySearch(finalizedVisits.value));
const filteredExpiredVisits = computed(() => filterBySearch(expiredVisits.value));

const doSearch = () => {
    firstActive.value = 0;
    firstFinalized.value = 0;
    firstExpired.value = 0;
};

const clearSearch = () => {
    search.value = '';
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
        severity: 'info',
        summary: 'Nueva Visita',
        detail: 'Navegando a registro de visita...',
        life: 2000
    });
    // Aquí puedes navegar a la vista de crear visita
    // router.push('/visit-manager');
};

const formatTime = (time) => {
    if (!time) return '';

    // Crear fecha ficticia para aprovechar toLocaleString
    const date = new Date(`1970-01-01T${time}`);

    return date.toLocaleTimeString('es-DO', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
};

const confirmCheckOut = (visit) => {
    confirm.require({
        message: `¿Confirmas la salida de ${visit.visitante.nombres} ${visit.visitante.apellidos}?`,
        header: 'Confirmar Salida',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-success',
        acceptLabel: 'Sí, dar salida',
        rejectLabel: 'Cancelar',
        accept: async () => {
            await checkOut(visit);
        },
    });
};

const checkOut = async (visit) => {
    try {
        const now = new Date();
        const hora_salida = now.toTimeString().split(' ')[0].substring(0, 5); // HH:MM

        console.log('Dando salida a visita:', { id: visit.id, estado: 'finalizada', hora_salida });

        modalState.loading = true;
        const result = await visitStore.updateVisit({
          id: visit.id,
          estado: 'finalizada',
          hora_salida: hora_salida
        });
        modalState.loading = false;

        if (result.success) {
          modalState.title = 'Salida registrada';
          modalState.message = `${visit.visitante.nombres} ha salido exitosamente.`;
          modalState.action = 'checkout';
          modalState.success = true; // actualización y reset se harán al cerrar
        } else {
          console.error('Error en respuesta:', result.errors);
          modalState.title = 'Error';
          modalState.message = 'Hubo un error al registrar la salida: ' + JSON.stringify(result.errors);
          modalState.action = null;
          modalState.success = true;
        }
    } catch (error) {
        console.error('Error al registrar salida:', error);
        modalState.loading = false;
        modalState.title = 'Error';
        modalState.message = 'No se pudo procesar la salida: ' + error.message;
        modalState.action = null;
        modalState.success = true;
    }
};

const confirmClose = (visit) => {
    confirm.require({
        message: `¿Deseas cerrar la visita vencida de ${visit.visitante.nombres} ${visit.visitante.apellidos}?`,
        header: 'Cerrar Visita Vencida',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-warning',
        acceptLabel: 'Sí, cerrar',
        rejectLabel: 'Cancelar',
        accept: async () => {
            await closeExpiredVisit(visit);
        },
    });
};

const closeExpiredVisit = async (visit) => {
    try {
        const now = new Date();
        const hora_salida = now.toTimeString().split(' ')[0].substring(0, 5); // HH:MM

        console.log('Cerrando visita vencida:', { id: visit.id, estado: 'finalizada', hora_salida });

        modalState.loading = true;
        const result = await visitStore.updateVisit({
          id: visit.id,
          estado: 'finalizada',
          hora_salida: hora_salida
        });
        modalState.loading = false;

        if (result.success) {
          modalState.title = 'Visita cerrada';
          modalState.message = `Visita vencida cerrada exitosamente.`;
          modalState.action = 'close';
          modalState.success = true;
          // actualización will be triggered after modal close
        } else {
          console.error('Error en respuesta:', result.errors);
          modalState.title = 'Error';
          modalState.message = 'Hubo un error al cerrar la visita: ' + JSON.stringify(result.errors);
          modalState.action = null;
          modalState.success = true;
        }
    } catch (error) {
        console.error('Error al cerrar visita:', error);
        modalState.loading = false;
        modalState.title = 'Error';
        modalState.message = 'No se pudo procesar el cierre de la visita: ' + error.message;
        modalState.action = null;
        modalState.success = true;
    }
};
</script>
