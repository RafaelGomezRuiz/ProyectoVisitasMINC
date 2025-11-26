<template>
  <Card class="bg-white shadow-sm border-0">
    <template #title>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Visitas Activas</h2>
      </div>
    </template>

    <template #content>
      <Toolbar class="mb-4 bg-gray-50 border-gray-200 rounded-lg">
        <template #start>
          <div class="flex items-center gap-2">
            <InputText v-model="search" placeholder="Buscar por visitante, documento o área" @keyup.enter="doSearch" />
            <Button icon="pi pi-search" label="Buscar" class="p-button-secondary" @click="doSearch" />
            <Button icon="pi pi-times" label="Limpiar" class="p-button-text" @click="clearSearch" />
          </div>
        </template>
        <template #end>
          <Button label="Nueva Visita" icon="pi pi-plus" class="p-button-primary" @click="openNewVisit" />
        </template>
      </Toolbar>

      <div v-if="visitStore.loading" class="flex justify-center items-center py-10">
        <ProgressSpinner />
      </div>

      <DataTable
        v-else
        :value="filteredVisits"
        :paginator="true"
        :rows="rows"
        :first="first"
        :totalRecords="filteredVisits.length"
        responsiveLayout="scroll"
        class="p-datatable-sm"
        stripedRows
        @page="onPageChange"
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
    </template>
  </Card>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useVisitStore } from '../stores/visitStore';
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

const visitStore = useVisitStore();
const confirm = useConfirm();
const toast = useToast();

// --- Estado ---
const search = ref('');
const rows = ref(10);
const first = ref(0);

onMounted(() => {
    visitStore.fetchActiveVisits();
});

// --- Búsqueda y filtrado ---
const filteredVisits = computed(() => {
    if (!search.value.trim()) {
        return visitStore.activeVisits;
    }

    const query = search.value.toLowerCase();
    return visitStore.activeVisits.filter(visit => {
        const nombres = `${visit.visitante.nombres} ${visit.visitante.apellidos}`.toLowerCase();
        const documento = visit.visitante.documento_identidad.toLowerCase();
        const area = visit.area.nombre.toLowerCase();

        return nombres.includes(query) || documento.includes(query) || area.includes(query);
    });
});

const doSearch = () => {
    first.value = 0;
};

const clearSearch = () => {
    search.value = '';
    first.value = 0;
};

const onPageChange = (evt) => {
    first.value = evt.first;
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
        const hora_salida = now.toTimeString().split(' ')[0].substring(0, 8); // HH:MM:SS

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
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Hubo un error al registrar la salida.',
                life: 3000
            });
        }
    } catch (error) {
        console.error(error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo procesar la salida.',
            life: 3000
        });
    }
};
</script>
