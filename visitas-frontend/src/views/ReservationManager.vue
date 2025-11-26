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
                            <label for="fecha" class="block text-gray-700 font-semibold">Fecha</label>
                            <input type="date" id="fecha" v-model="reservationForm.fecha" class="input" :class="{'border-red-500': formErrors.fecha}">
                            <p v-if="formErrors.fecha" class="text-red-500 text-sm mt-1">{{ formErrors.fecha[0] }}</p>
                        </div>
                        <div>
                            <label for="hora" class="block text-gray-700 font-semibold">Hora</label>
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
    </div>
</template>

<script setup>
import { ref } from 'vue';
import VisitorSearchOrCreate from '../components/VisitorSearchOrCreate.vue';
import { useReservationStore } from '../stores/reservationStore';

const reservationStore = useReservationStore();

const currentVisitor = ref(null);
const reservationForm = ref({});
const formErrors = ref({});

const handleVisitorSelected = (visitor) => {
    currentVisitor.value = visitor;
    prepareReservationForm();
};

const prepareReservationForm = () => {
    const today = new Date();
    // Prevenir seleccionar fechas pasadas
    const minDate = today.toISOString().split('T')[0];
    
    // Formatear hora actual a HH:MM
    const defaultTime = today.toTimeString().split(' ')[0].substring(0, 5);

    reservationForm.value = {
        visitante_id: currentVisitor.value.id,
        fecha: minDate,
        hora: defaultTime,
        motivo: '',
        estado: 'pendiente', // Estado por defecto al crear
    };
    formErrors.value = {};
};

const handleCreateReservation = async () => {
    formErrors.value = {};
    const result = await reservationStore.createReservation(reservationForm.value);
    
    if (result.success) {
        alert('¡Reserva creada con éxito!');
        resetFlow();
    } else {
        formErrors.value = result.errors;
        alert('Error al crear la reserva. Por favor, revisa los campos.');
    }
};

const resetFlow = () => {
    currentVisitor.value = null;
    reservationForm.value = {};
    formErrors.value = {};
};
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
