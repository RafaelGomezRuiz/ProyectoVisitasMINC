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
                        <h2 class="text-2xl font-bold text-blue-800">{{ currentVisitor.nombres }} {{ currentVisitor.apellidos }}</h2>
                        <p class="text-gray-600">{{ currentVisitor.documento_identidad }}</p>
                    </div>
                    <button @click="resetFlow" class="text-sm text-blue-600 hover:underline">Seleccionar otro visitante</button>
                </div>
            </div>

            <!-- Formulario de Reserva -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Paso 2: Detalles de la Reserva</h3>
                <form @submit.prevent="handleCreateReservation" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="fecha" class="block text-gray-700 font-semibold">Fecha <span class="text-red-600">*</span></label>
                            <input type="date" id="fecha" v-model="reservationForm.fecha" class="input" :class="{'border-red-500': formErrors.fecha}">
                            <p v-if="formErrors.fecha" class="text-red-500 text-sm mt-1">{{ formErrors.fecha[0] }}</p>
                        </div>
                        <div>
                            <label for="hora" class="block text-gray-700 font-semibold">Hora <span class="text-red-600">*</span></label>
                            <input type="time" id="hora" v-model="reservationForm.hora" class="input" :class="{'border-red-500': formErrors.hora}">
                             <p v-if="formErrors.hora" class="text-red-500 text-sm mt-1">{{ formErrors.hora[0] }}</p>
                        </div>
                    </div>
                    <div>
                        <label for="motivo" class="block text-gray-700 font-semibold">Motivo de la Reserva (Opcional)</label>
                        <textarea id="motivo" v-model="reservationForm.motivo" rows="3" class="input"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">Confirmar Reserva</button>
                    </div>
                </form>
            </div>
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
import { ref, reactive, watch } from 'vue';
import VisitorSearchOrCreate from '../components/VisitorSearchOrCreate.vue';
import BaseModal from '../components/BaseModal.vue';
import { useReservationStore } from '../stores/reservationStore';

const reservationStore = useReservationStore();

const currentVisitor = ref(null);
const reservationForm = ref({
    visitante_id: null,
    fecha: '',
    hora: '',
    motivo: '',
    estado: 'pendiente',
});
const formErrors = ref({});

// Modal centralizado para errores/avisos
const modalState = reactive({
    open: false,
    title: '',
    message: '',
    icon: 'mdi-alert-circle-outline',
    iconColor: 'red',
    showClose: true,
});

// Fecha mínima: hoy
const minDate = new Date().toISOString().split('T')[0];

const handleVisitorSelected = (visitor) => {
    currentVisitor.value = visitor;
    prepareReservationForm();
};

const prepareReservationForm = () => {
    // No autopoblar fecha/hora: el usuario debe seleccionarlos.
    reservationForm.value = {
        visitante_id: currentVisitor.value.id,
        fecha: '',
        hora: '',
        motivo: '',
        estado: 'pendiente',
    };
    formErrors.value = {};
};

const showRequiredFieldsModal = (errors) => {
    // Construir mensaje legible con los errores pasados
    const lines = [];
    if (errors.fecha) lines.push(`Fecha: ${errors.fecha.join(', ')}`);
    if (errors.hora) lines.push(`Hora: ${errors.hora.join(', ')}`);
    if (lines.length === 0) lines.push('Por favor completa los campos obligatorios.');

    modalState.title = 'Campos obligatorios';
    modalState.message = lines.join('\n');
    modalState.icon = 'mdi-alert-circle-outline';
    modalState.iconColor = 'red';
    modalState.showClose = true;
    modalState.open = true;
};

const handleCreateReservation = async () => {
    formErrors.value = {};

    // Validación frontend
    if (!reservationForm.value.fecha) {
        formErrors.value.fecha = ['La fecha es obligatoria.'];
    } else if (reservationForm.value.fecha < minDate) {
        formErrors.value.fecha = ['La fecha no puede ser anterior a hoy.'];
    }

    if (!reservationForm.value.hora) {
        formErrors.value.hora = ['La hora es obligatoria.'];
    }

    if (Object.keys(formErrors.value).length > 0) {
        showRequiredFieldsModal(formErrors.value);
        return;
    }

    // Mostrar loading modal
    modalState.title = 'Creando reserva...';
    modalState.message = 'Por favor espera mientras se crea tu reserva.';
    modalState.icon = 'mdi-loading';
    modalState.iconColor = 'blue';
    modalState.showClose = false;
    modalState.open = true;

    const result = await reservationStore.createReservation(reservationForm.value);
    
    if (result.success) {
        modalState.title = 'Reserva creada';
        modalState.message = '¡Reserva creada con éxito!';
        modalState.icon = 'mdi-check-circle-outline';
        modalState.iconColor = 'green';
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
        fecha: '',
        hora: '',
        motivo: '',
        estado: 'pendiente',
    };
    formErrors.value = {};
};

// Watchers: quitar rojo (errores) cuando el campo se vuelve válido
watch(() => reservationForm.value.fecha, (newVal) => {
    if (!formErrors.value || !formErrors.value.fecha) return;
    if (newVal && newVal >= minDate) {
        delete formErrors.value.fecha;
    }
});

watch(() => reservationForm.value.hora, (newVal) => {
    if (!formErrors.value || !formErrors.value.hora) return;
    if (newVal) {
        delete formErrors.value.hora;
    }
});
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
