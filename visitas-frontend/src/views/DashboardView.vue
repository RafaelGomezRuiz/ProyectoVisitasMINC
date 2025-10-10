<template>
    <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-6xl mx-auto">
             <!-- Encabezado -->
            <div class="flex items-center space-x-4 mb-6">
                <div class="p-3 bg-blue-100 rounded-full">
                     <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Estadísticas de Visitas</h1>
            </div>

            <div v-if="visitStore.loading" class="text-center p-8">Cargando estadísticas...</div>
            <div v-else-if="visitStore.error" class="text-center p-8 text-red-500">{{ visitStore.error }}</div>
            <!-- Contenedor de Tarjetas de Estadísticas -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Tarjeta: Total Histórico -->
                <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
                    <div class="p-3 bg-indigo-100 rounded-full">
                         <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m-9-5.747h18" /></svg>
                    </div>
                    <div>
                        <h3 class="text-gray-500 font-semibold">Total Histórico</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ visitStore.dashboardStats.totalVisits }}</p>
                    </div>
                </div>

                <!-- Tarjeta: Visitas Activas -->
                <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
                     <div class="p-3 bg-green-100 rounded-full">
                         <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-1a6 6 0 00-1.781-4.121M12 12a4 4 0 110-8 4 4 0 010 8z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-gray-500 font-semibold">Visitas Activas Ahora</h3>
                        <p class="text-3xl font-bold text-green-600">{{ visitStore.dashboardStats.activeVisits }}</p>
                    </div>
                </div>

                <!-- Tarjeta: Visitas Hoy -->
                <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
                     <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-gray-500 font-semibold">Visitas de Hoy</h3>
                        <p class="text-3xl font-bold text-yellow-600">{{ visitStore.dashboardStats.visitsToday }}</p>
                    </div>
                </div>

                <!-- Tarjeta: Área Más Popular -->
                <div class="bg-white p-6 rounded-xl shadow-lg md:col-span-2 lg:col-span-3 flex items-center space-x-4">
                     <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-gray-500 font-semibold">Área Más Popular</h3>
                        <p class="text-2xl font-bold text-purple-600">{{ visitStore.dashboardStats.mostVisitedArea }}</p>
                        <p class="text-sm text-gray-500">{{ visitStore.dashboardStats.mostVisitedAreaCount }} visitas en total</p>
                    </div>
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
    visitStore.fetchDashboardStats();
});
</script>
