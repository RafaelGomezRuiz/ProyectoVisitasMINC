<template>
    <div class="ps-4 bg-gray-50 min-h-screen font-sans space-y-8">
        <!-- <h1 class="text-3xl font-bold text-gray-800">Registro de Visitas</h1> -->

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
                            <label for="visit-area" class="block text-gray-700 font-semibold">Área a Visitar</label>
                            <select id="visit-area" v-model="visitForm.area_id" class="input" :class="{'border-red-500': visitFormErrors.area_id}">
                                <option :value="null" disabled>-- Seleccione un área --</option>
                                <option v-for="area in dataStore.areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                            </select>
                            <p v-if="visitFormErrors.area_id" class="text-red-500 text-sm mt-1">{{ visitFormErrors.area_id[0] }}</p>
                        </div>
                        <div>
                            <label for="visit-edad" class="block text-gray-700 font-semibold">Edad del Visitante</label>
                            <input id="visit-edad" v-model.number="visitForm.edad" type="number" class="input" placeholder="Ej: 25" :class="{'border-red-500': visitFormErrors.edad}">
                            <p v-if="visitFormErrors.edad" class="text-red-500 text-sm mt-1">{{ visitFormErrors.edad[0] }}</p>
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
import VisitorSearchOrCreate from '../components/VisitorSearchOrCreate.vue';
import { useReservationStore } from '../stores/reservationStore';
import { useVisitStore } from '../stores/visitStore';
import { useDataStore } from '../stores/dataStore';

const reservationStore = useReservationStore();
const visitStore = useVisitStore();
const dataStore = useDataStore();

const currentVisitor = ref(null);
const visitForm = ref({});
const visitFormErrors = ref({}); // Para mostrar errores de validación
const showVisitForm = ref(false);

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

const handleCreateVisit = async () => {
    visitFormErrors.value = {}; // Limpia errores antes de enviar
    const result = await visitStore.createVisit(visitForm.value);
    if (result.success) {
        // Si la visita usó una reserva, actualiza el estado de la reserva
        if (visitForm.value.reserva_id) {
            await reservationStore.updateReservationStatus(visitForm.value.reserva_id, 'utilizada');
        }
        alert('Visita registrada con éxito!');
        resetFlow();
    } else {
        // Asigna los errores de validación para mostrarlos en el formulario
        visitFormErrors.value = result.errors;
        alert('Error al registrar la visita. Por favor, revisa los campos.');
    }
};

const resetFlow = () => {
    currentVisitor.value = null;
    showVisitForm.value = false;
    reservationStore.clearPending();
    visitForm.value = {};
    visitFormErrors.value = {};
};
</script>

<style>
/* Estilos globales para inputs y botones */
.input {
    @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors;
}
.btn-primary {
    @apply bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors;
}
</style>

