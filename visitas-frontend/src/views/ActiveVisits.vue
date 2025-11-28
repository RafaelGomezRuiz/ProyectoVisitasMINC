<template>
  <div class="space-y-8">
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

      <!-- Formulario de Visita -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Detalles de la Visita</h3>
        <form @submit.prevent="handleCreateVisit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div :class="currentVisitor.edad < 18 ? 'col-span-2' : 'col-span-1'">
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
        <TabView v-if="!visitStore.loading" class="p-tabview-sm">
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

      <div v-else class="flex justify-center items-center py-10">
        <ProgressSpinner />
      </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import VisitorSearchOrCreate from '../components/VisitorSearchOrCreate.vue';
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
const confirm = useConfirm();
const toast = useToast();

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
const handleVisitorSelected = (visitor) => {
    currentVisitor.value = visitor;
    prepareVisitForm();
};

const prepareVisitForm = () => {
    const now = new Date();
    visitForm.value = {
        visitante_id: currentVisitor.value.id,
        reserva_id: null,
        fecha: now.toISOString().split('T')[0],
        hora_entrada: now.toTimeString().split(' ')[0].substring(0, 5),
        motivo: '',
        area_id: null,
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
        toast.add({
            severity: 'error',
            summary: 'Campos Obligatorios',
            detail: 'Por favor completa todos los campos obligatorios',
            life: 3000
        });
        return;
    }

    try {
        const result = await visitStore.createVisit(visitForm.value);
        
        if (result.success) {
            toast.add({
                severity: 'success',
                summary: 'Éxito',
                detail: 'La visita ha sido registrada correctamente.',
                life: 3000
            });
            await visitStore.fetchActiveVisits();
            resetFlow();
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error al Registrar',
                detail: 'Hubo un problema al registrar la visita.',
                life: 3000
            });
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Ocurrió un error inesperado.',
            life: 3000
        });
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

        const result = await visitStore.updateVisit({
            id: visit.id,
            estado: 'finalizada',
            hora_salida: hora_salida
        });

        if (result.success) {
            toast.add({
                severity: 'success',
                summary: 'Salida Registrada',
                detail: `${visit.visitante.nombres} ha salido exitosamente.`,
                life: 3000
            });
            await visitStore.fetchActiveVisits();
        } else {
            console.error('Error en respuesta:', result.errors);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Hubo un error al registrar la salida: ' + JSON.stringify(result.errors),
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error al registrar salida:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo procesar la salida: ' + error.message,
            life: 3000
        });
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

        const result = await visitStore.updateVisit({
            id: visit.id,
            estado: 'finalizada',
            hora_salida: hora_salida
        });

        if (result.success) {
            toast.add({
                severity: 'success',
                summary: 'Visita Cerrada',
                detail: `Visita vencida cerrada exitosamente.`,
                life: 3000
            });
            // Recarga las visitas después de actualizar
            await visitStore.fetchActiveVisits();
        } else {
            console.error('Error en respuesta:', result.errors);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Hubo un error al cerrar la visita: ' + JSON.stringify(result.errors),
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error al cerrar visita:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo procesar el cierre de la visita: ' + error.message,
            life: 3000
        });
    }
};
</script>
