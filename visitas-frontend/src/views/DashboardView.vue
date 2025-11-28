<template>
    <div class="p-4 sm:p-6 lg:p-4 bg-gradient-to-b from-slate-50 to-slate-100 min-h-screen font-sans">
        <div class="mx-auto max-w-7xl">
            <!-- Encabezado Profesional -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-gray-900">Panel de Control</h1>
                            <p class="text-sm text-gray-500 mt-1">Estadísticas e Información en Tiempo Real</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Loading & Error States -->
            <div v-if="visitStore.loading || reservationStore.loading" class="flex justify-center items-center p-12">
                <div class="text-center">
                    <div class="inline-block relative w-20 h-20 mb-4">
                        <div class="absolute border-4 border-gray-200 rounded-full w-20 h-20"></div>
                        <div class="absolute border-4 border-blue-500 rounded-full w-20 h-20 border-t-blue-600 animate-spin"></div>
                    </div>
                    <p class="text-gray-600 font-medium">Cargando datos...</p>
                </div>
            </div>

            <div v-else-if="visitStore.error || reservationStore.error" class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v-6m0 6H9m3 0h3M9 5h6a2 2 0 012 2v12a2 2 0 01-2 2H9a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-red-700">Error al cargar datos</p>
                        <p class="text-sm text-red-600">{{ visitStore.error || reservationStore.error }}</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div v-else class="space-y-6">
                <!-- KPI Cards - Fila 1 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Histórico -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Histórico</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ visitStore.dashboardStats.totalVisits ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m-9-5.747h18" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Visitas Activas -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Visitas Activas</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ visitStore.dashboardStats.activeVisits ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Visitas Hoy -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 p-6 border-l-4 border-amber-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Visitas de Hoy</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ visitStore.dashboardStats.visitsToday ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-amber-100 rounded-lg">
                                <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Reservas Totales -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Reservas Totales</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ reservationStore.reservations?.length ?? 0 }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos - Fila 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Gráfico de Reservas: Vigentes vs Expiradas -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Reservas: Vigentes vs Expiradas</h3>
                            <p class="text-sm text-gray-500 mt-1">Análisis del estado de reservas pendientes</p>
                        </div>
                        <div class="relative h-72">
                            <canvas id="reservasChart"></canvas>
                        </div>
                    </div>

                    <!-- Gráfico de Visitas por Estado -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Visitas por Estado</h3>
                            <p class="text-sm text-gray-500 mt-1">Distribución de estados de visitas</p>
                        </div>
                        <div class="relative h-72">
                            <canvas id="visitasChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráficos - Fila 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Gráfico de Horarios más Concurridos -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Horarios Más Concurridos</h3>
                            <p class="text-sm text-gray-500 mt-1">Visitas registradas por hora del día</p>
                        </div>
                        <div class="relative h-72">
                            <canvas id="horasChart"></canvas>
                        </div>
                    </div>

                    <!-- Gráfico de Áreas Más Visitadas -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Áreas Más Visitadas</h3>
                            <p class="text-sm text-gray-500 mt-1">Top 10 de áreas con mayor visitas</p>
                        </div>
                        <div class="relative h-72">
                            <canvas id="areasChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Área Más Popular - Destaque -->
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg shadow-sm p-6">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-purple-500 rounded-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium">Área Más Popular</h3>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ visitStore.dashboardStats.mostVisitedArea ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ visitStore.dashboardStats.mostVisitedAreaCount ?? 0 }} visitas en total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch, nextTick, onBeforeUnmount } from 'vue';
import { useVisitStore } from '../stores/visitStore';
import { useReservationStore } from '../stores/reservationStore';
import {
    Chart,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

Chart.register(
    BarController, BarElement,
    LineController, LineElement, PointElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale,
    Title, Tooltip, Legend, Filler
);

const visitStore = useVisitStore();
const reservationStore = useReservationStore();

// Referencias a instancias de Chart
const chartInstances = {
    reservas: null,
    visitas: null,
    horas: null,
    areas: null
};

// Función para obtener contexto de canvas
const getCanvasContext = (id) => document.getElementById(id)?.getContext('2d');

// =============== GRÁFICO 1: RESERVAS (Vigentes vs Expiradas) ===============
const createOrUpdateReservasChart = (vigentes, expiradas) => {
    const ctx = getCanvasContext('reservasChart');
    if (!ctx) return;

    const plainData = JSON.parse(JSON.stringify({
        labels: ['Vigentes', 'Expiradas'],
        datasets: [{
            label: 'Cantidad',
            data: [vigentes, expiradas],
            backgroundColor: ['#10b981', '#ef4444'],
            borderColor: ['#059669', '#dc2626'],
            borderWidth: 2
        }]
    }));

    const plainOptions = JSON.parse(JSON.stringify({
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 12 }, padding: 20 }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleFont: { size: 14 },
                bodyFont: { size: 12 },
                padding: 12,
                displayColors: true
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { font: { size: 11 } } }
        }
    }));

    if (chartInstances.reservas) {
        chartInstances.reservas.data = plainData;
        chartInstances.reservas.options = plainOptions;
        chartInstances.reservas.update('none');
    } else {
        chartInstances.reservas = new Chart(ctx, {
            type: 'bar',
            data: plainData,
            options: plainOptions
        });
    }
};

// =============== GRÁFICO 2: VISITAS (Activas, Finalizadas, Vencidas) ===============
const createOrUpdateVisitasChart = (activas, finalizadas, vencidas) => {
    const ctx = getCanvasContext('visitasChart');
    if (!ctx) return;

    const plainData = JSON.parse(JSON.stringify({
        labels: ['Activas', 'Finalizadas', 'Vencidas'],
        datasets: [{
            label: 'Cantidad',
            data: [activas, finalizadas, vencidas],
            backgroundColor: ['#3b82f6', '#10b981', '#f97316'],
            borderColor: ['#1d4ed8', '#059669', '#ea580c'],
            borderWidth: 2
        }]
    }));

    const plainOptions = JSON.parse(JSON.stringify({
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 12 }, padding: 20 }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleFont: { size: 14 },
                bodyFont: { size: 12 },
                padding: 12
            }
        }
    }));

    if (chartInstances.visitas) {
        chartInstances.visitas.data = plainData;
        chartInstances.visitas.options = plainOptions;
        chartInstances.visitas.update('none');
    } else {
        chartInstances.visitas = new Chart(ctx, {
            type: 'doughnut',
            data: plainData,
            options: plainOptions
        });
    }
};

// =============== GRÁFICO 3: HORARIOS (Horas más concurridas) ===============
const createOrUpdateHorasChart = (labels, data) => {
    const ctx = getCanvasContext('horasChart');
    if (!ctx) return;

    const plainData = JSON.parse(JSON.stringify({
        labels,
        datasets: [{
            label: 'Visitas',
            data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    }));

    const plainOptions = JSON.parse(JSON.stringify({
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 12 }, padding: 20 }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleFont: { size: 14 },
                bodyFont: { size: 12 },
                padding: 12,
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { font: { size: 11 } },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                ticks: { font: { size: 10 } },
                grid: { display: false }
            }
        }
    }));

    if (chartInstances.horas) {
        chartInstances.horas.data = plainData;
        chartInstances.horas.options = plainOptions;
        chartInstances.horas.update('none');
    } else {
        chartInstances.horas = new Chart(ctx, {
            type: 'line',
            data: plainData,
            options: plainOptions
        });
    }
};

// =============== GRÁFICO 4: ÁREAS (Más visitadas) ===============
const createOrUpdateAreasChart = (labels, data) => {
    const ctx = getCanvasContext('areasChart');
    if (!ctx) return;

    const plainData = JSON.parse(JSON.stringify({
        labels,
        datasets: [{
            label: 'Visitas',
            data,
            backgroundColor: [
                '#8b5cf6', '#3b82f6', '#06b6d4', '#10b981',
                '#f59e0b', '#ef4444', '#ec4899', '#14b8a6',
                '#f97316', '#6366f1'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    }));

    const plainOptions = JSON.parse(JSON.stringify({
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleFont: { size: 14 },
                bodyFont: { size: 12 },
                padding: 12
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { font: { size: 11 } },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            y: {
                ticks: { font: { size: 11 } }
            }
        }
    }));

    if (chartInstances.areas) {
        chartInstances.areas.data = plainData;
        chartInstances.areas.options = plainOptions;
        chartInstances.areas.update('none');
    } else {
        chartInstances.areas = new Chart(ctx, {
            type: 'bar',
            data: plainData,
            options: plainOptions
        });
    }
};

// =============== DERIVACIÓN DE DATOS DESDE STORES ===============
const computeChartsData = () => {
    // --- RESERVAS ---
    const reservations = JSON.parse(JSON.stringify(reservationStore.reservations || []));
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let vigentes = 0, expiradas = 0;
    reservations.forEach(r => {
        if (!r.fecha) return;
        const rDate = new Date(r.fecha);
        rDate.setHours(0, 0, 0, 0);
        if (r.estado === 'cancelada' || r.estado === 'rechazada') return;
        if (rDate < today) expiradas++;
        else vigentes++;
    });
    createOrUpdateReservasChart(vigentes, expiradas);

    // --- VISITAS ---
    const visits = JSON.parse(JSON.stringify(visitStore.activeVisits || []));
    const activas = visits.filter(v => v.estado === 'activa' && !isVisitExpired(v)).length;
    const finalizadas = visits.filter(v => v.estado === 'finalizada').length;
    const vencidas = visits.filter(v => isVisitExpired(v) && v.estado !== 'finalizada').length;
    createOrUpdateVisitasChart(activas, finalizadas, vencidas);

    // --- HORARIOS ---
    const hourCounts = {};
    visits.forEach(v => {
        if (!v.hora_entrada) return;
        const h = v.hora_entrada.substring(0, 2);
        hourCounts[h] = (hourCounts[h] || 0) + 1;
    });
    const hourLabels = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0') + ':00');
    const hourData = hourLabels.map(lbl => hourCounts[lbl.substring(0, 2)] || 0);
    createOrUpdateHorasChart(hourLabels, hourData);

    // --- ÁREAS ---
    const areaCountsMap = {};
    visits.forEach(v => {
        const areaName = v.area?.nombre || 'Sin área';
        areaCountsMap[areaName] = (areaCountsMap[areaName] || 0) + 1;
    });
    const areaEntries = Object.entries(areaCountsMap)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 10);
    const areaLabels = areaEntries.map(e => e[0]);
    const areaData = areaEntries.map(e => e[1]);
    createOrUpdateAreasChart(areaLabels, areaData);
};

const isVisitExpired = (visit) => {
    if (!visit.fecha) return false;
    const [y, m, d] = visit.fecha.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return date < today;
};

// =============== CICLO DE VIDA ===============
onMounted(async () => {
    // Cargar datos desde stores
    await Promise.all([
        visitStore.fetchDashboardStats(),
        visitStore.fetchActiveVisits(),
        reservationStore.fetchReservations(1)
    ]);

    // Esperar a que DOM esté listo y renderizar gráficos
    await nextTick();
    computeChartsData();

});

// Observar cambios en stores y actualizar gráficos
watch(
    [() => visitStore.activeVisits, () => reservationStore.reservations],
    () => {
        computeChartsData();
    },
    { deep: true }
);

// Limpiar gráficos al desmontar
onBeforeUnmount(() => {
    Object.values(chartInstances).forEach(chart => {
        if (chart) chart.destroy();
    });
});
</script>
