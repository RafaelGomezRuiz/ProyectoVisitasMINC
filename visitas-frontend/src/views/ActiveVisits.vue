<template>
    <div class="ps-4 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-6xl mx-auto">
            <!-- Encabezado -->
            <div class="flex items-center space-x-4 mb-6">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.125-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.125-1.283.356-1.857m0 0a3.001 3.001 0 015.688 0M12 12a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Visitas Activas ({{ visitStore.activeVisits.length }})</h1>
            </div>

            <!-- Tabla de Visitas Activas -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                 <div v-if="visitStore.loading" class="p-8 text-center text-gray-500">Cargando...</div>
                 <div v-else-if="visitStore.error" class="p-8 text-center text-red-500">{{ visitStore.error }}</div>
                 <div v-else-if="visitStore.activeVisits.length === 0" class="p-8 text-center text-gray-500">No hay visitas activas en este momento.</div>
                 <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm">Visitante</th>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm">Documento</th>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm">Área</th>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm">Hora de Entrada</th>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="visit in visitStore.activeVisits" :key="visit.id" class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium">{{ visit.visitante.nombres }} {{ visit.visitante.apellidos }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ visit.visitante.documento_identidad }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ visit.area.nombre }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ visit.hora_entrada }}</td>
                                <td class="py-4 px-6 text-center">
                                    <button @click="checkOut(visit)"
                                        class="bg-green-500 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-green-600 transition-colors duration-200 flex items-center mx-auto">
                                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                        Dar Salida
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useVisitStore } from '../stores/visitStore';

const visitStore = useVisitStore();

onMounted(() => {
    visitStore.fetchActiveVisits();
});

const checkOut = async (visit) => {
    if (window.confirm(`¿Confirmas la salida de ${visit.visitante.nombres}?`)) {
        const now = new Date();
        const hora_salida = now.toTimeString().split(' ')[0].substring(0, 8); // HH:MM:SS

        const result = await visitStore.updateVisit({
            id: visit.id,
            estado: 'finalizada',
            hora_salida: hora_salida
        });

        if (!result.success) {
            alert('Hubo un error al registrar la salida.');
        }
    }
};
</script>
