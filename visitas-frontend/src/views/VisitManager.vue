<template>
    <div class="ps-4 bg-gray-50 min-h-screen font-sans space-y-8">
        <!-- Modal de Loading -->
        <BaseModal 
            :model-value="modalState.loading" 
            title="Registrando Visita"
            icon="mdi-loading"
            icon-color="primary"
        />

        <!-- Modal de Éxito -->
        <BaseModal 
            :model-value="modalState.success" 
            title="¡Éxito!"
            message="La visita ha sido registrada correctamente."
            icon="mdi-check-circle"
            icon-color="success"
            :show-close="true"
            @update:model-value="closeSuccessModal"
        />

        <!-- Modal de Error -->
        <BaseModal 
            :model-value="modalState.error" 
            :title="modalState.errorTitle"
            :message="modalState.errorMessage"
            icon="mdi-alert-circle"
            icon-color="error"
            :show-close="true"
            @update:model-value="closeErrorModal"
        />

        <!-- Paso 1: Buscar o crear visitante -->
        <VisitorSearchOrCreate @visitor-selected="handleVisitorSelected" v-if="!currentVisitor" />

        <!-- Paso 2: Mostrar visitante y gestionar visita/reserva -->
        <div v-if="currentVisitor" class="space-y-8">
            <!-- Info del Visitante Seleccionado -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-blue-800">{{ currentVisitor.nombres }} {{ currentVisitor.apellidos }}</h2>
                        <p class="text-gray-600">{{ currentVisitor.documento_identidad }}</p>
                    </div>
                    <button @click="resetFlow" class="text-sm text-blue-600 hover:underline">Buscar otro visitante</button>
                </div>
            </div>

            <!-- Lógica Condicional: ¿Tiene reserva? -->
            <div v-if="reservationStore.loading" class="text-center p-8">Cargando reserva...</div>
            
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
                        <div>
                            <label for="visit-area" class="block text-gray-700 font-semibold">
                                Área a Visitar <span class="text-red-600">*</span>
                            </label>
                            <select id="visit-area" v-model="visitForm.area_id" class="input" :class="{'border-red-500 bg-red-50': frontendErrors.area_id}">
                                <option :value="null" disabled>-- Seleccione un área --</option>
                                <option v-for="area in dataStore.areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                            </select>
                            <p v-if="frontendErrors.area_id" class="text-red-500 text-sm mt-1">{{ frontendErrors.area_id }}</p>
                            <p v-else-if="visitFormErrors.area_id" class="text-red-500 text-sm mt-1">{{ visitFormErrors.area_id[0] }}</p>
                        </div>
                        <div>
                            <label for="visit-edad" class="block text-gray-700 font-semibold">
                                Edad del Visitante <span class="text-red-600">*</span>
                            </label>
                            <input id="visit-edad" v-model.number="visitForm.edad" type="number" class="input" placeholder="Ej: 25" :class="{'border-red-500 bg-red-50': frontendErrors.edad}">
                            <p v-if="frontendErrors.edad" class="text-red-500 text-sm mt-1">{{ frontendErrors.edad }}</p>
                            <p v-else-if="visitFormErrors.edad" class="text-red-500 text-sm mt-1">{{ visitFormErrors.edad[0] }}</p>
                        </div>
                    </div>
                    <div>
                        <label for="visit-responsable" class="block text-gray-700 font-semibold">Responsable (si es menor de edad)</label>
                        <input id="visit-responsable" v-model="visitForm.responsable" type="text" class="input" placeholder="Nombre del acompañante adulto">
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
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import BaseModal from '../components/BaseModal.vue';
import VisitorSearchOrCreate from '../components/VisitorSearchOrCreate.vue';
import { useReservationStore } from '../stores/reservationStore';
import { useVisitStore } from '../stores/visitStore';
import { useDataStore } from '../stores/dataStore';

const reservationStore = useReservationStore();
const visitStore = useVisitStore();
const dataStore = useDataStore();

const currentVisitor = ref(null);
const visitForm = ref({});
const visitFormErrors = ref({}); // Para mostrar errores de validación del servidor
const frontendErrors = ref({}); // Para mostrar errores de validación del frontend
const showVisitForm = ref(false);

const modalState = ref({
    loading: false,
    success: false,
    error: false,
    errorTitle: '',
    errorMessage: ''
});

onMounted(() => {
    dataStore.fetchAll(); // Carga todos los datos para los selects
});

const handleVisitorSelected = async (visitor) => {
    currentVisitor.value = visitor;
    showVisitForm.value = false; // Oculta el form mientras busca reserva
    await reservationStore.fetchPendingForVisitor(visitor.id);
    if (!reservationStore.pendingReservation) {
        // Si no hay reserva, muestra el form de visita normal
        prepareVisitForm();
        showVisitForm.value = true;
    }
};

const prepareVisitForm = (reserva = null) => {
    const now = new Date();
    visitForm.value = {
        visitante_id: currentVisitor.value.id,
        reserva_id: reserva ? reserva.id : null,
        fecha: now.toISOString().split('T')[0],
        hora_entrada: now.toTimeString().split(' ')[0].substring(0, 5),
        motivo: reserva ? reserva.motivo : '',
        area_id: null,
        edad: null, // Se llenará con el input
        responsable: '',
        no_carnet: '',
        estado: 'activa',
    };
    visitFormErrors.value = {}; // Limpia errores anteriores
    frontendErrors.value = {}; // Limpia errores del frontend
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

// Validación en el frontend
const validateForm = () => {
    frontendErrors.value = {};
    
    if (!visitForm.value.area_id) {
        frontendErrors.value.area_id = 'Área a Visitar es obligatoria';
    }
    
    if (!visitForm.value.edad && visitForm.value.edad !== 0) {
        frontendErrors.value.edad = 'Edad del Visitante es obligatoria';
    }
    
    return Object.keys(frontendErrors.value).length === 0;
};

const handleCreateVisit = async () => {
    // Primero valida el frontend
    if (!validateForm()) {
        modalState.value.error = true;
        modalState.value.errorTitle = 'Campos Obligatorios';
        modalState.value.errorMessage = 'Por favor, completa todos los campos obligatorios (marcados con *)';
        return;
    }

    visitFormErrors.value = {}; // Limpia errores anteriores
    modalState.value.loading = true; // Muestra modal de carga
    
    try {
        const result = await visitStore.createVisit(visitForm.value);
        modalState.value.loading = false;
        
        if (result.success) {
            // Si la visita usó una reserva, actualiza el estado de la reserva
            if (visitForm.value.reserva_id) {
                await reservationStore.updateReservationStatus(visitForm.value.reserva_id, 'utilizada');
            }
            modalState.value.success = true; // Muestra modal de éxito
            resetFlow();
        } else {
            // Asigna los errores de validación para mostrarlos en el formulario
            visitFormErrors.value = result.errors;
            modalState.value.error = true;
            modalState.value.errorTitle = 'Error al Registrar';
            modalState.value.errorMessage = 'Hubo un problema al registrar la visita. Por favor, revisa los campos.';
        }
    } catch (error) {
        modalState.value.loading = false;
        modalState.value.error = true;
        modalState.value.errorTitle = 'Error';
        modalState.value.errorMessage = 'Ocurrió un error inesperado. Por favor, intenta nuevamente.';
    }
};

const closeSuccessModal = (value) => {
    modalState.value.success = value;
};

const closeErrorModal = (value) => {
    modalState.value.error = value;
};

const resetFlow = () => {
    currentVisitor.value = null;
    showVisitForm.value = false;
    reservationStore.clearPending();
    visitForm.value = {};
    visitFormErrors.value = {};
    frontendErrors.value = {};
};
</script>

<style scoped>
/* Estilos globales para inputs y botones */
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

.input.border-red-500 {
    border-color: #ef4444;
    background-color: #fee2e2;
}

.btn-primary {
    background-color: #2563eb;
    color: white;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    border: none;
}

.btn-primary:hover {
    background-color: #1d4ed8;
}
</style>

