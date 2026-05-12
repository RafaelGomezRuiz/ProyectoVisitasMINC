<script setup>
import { ref, onMounted } from "vue";
import { useLocationStore } from "../stores/locationStore";
import { useAreaStore } from "../stores/areaStore";
import { useScheduleStore } from "../stores/scheduleStore";

// Stores
const locationStore = useLocationStore();
const areaStore = useAreaStore();
const scheduleStore = useScheduleStore();

// State
const selectedLocation = ref(null);
const daysOfWeek = [
  "Lunes",
  "Martes",
  "Miércoles",
  "Jueves",
  "Viernes",
  "Sábado",
  "Domingo",
];

// Modal State: Location
const isLocationModalOpen = ref(false);
const locationForm = ref({});
const locationFormErrors = ref({});

// Modal State: Area
const isAreaModalOpen = ref(false);
const areaForm = ref({});
const areaFormErrors = ref({});

// Modal State: Schedule
const isScheduleModalOpen = ref(false);
const scheduleForm = ref({});
const scheduleFormErrors = ref({});

// Ciclo de vida
onMounted(() => {
  locationStore.fetchLocations();
});

// Métodos Principales
const selectLocation = (location) => {
  selectedLocation.value = location;
  areaStore.fetchAreasForLocation(location.id);
  scheduleStore.fetchSchedulesForLocation(location.id);
};

const changeLocationPage = (url) => {
  if (!url) return;
  const pageNumber = new URL(url).searchParams.get("page");
  locationStore.fetchLocations(pageNumber);
};

// --- Lógica para MODAL DE LOCALIDAD ---
const openLocationModal = (location = null) => {
  locationFormErrors.value = {};
  if (location) {
    locationForm.value = { ...location }; // Editar
  } else {
    locationForm.value = {
      // Crear
      nombre: "",
      direccion: "",
      region: "",
      provincia: "",
      municipio: "",
      telefono: "",
      costo_entrada: 0,
    };
  }
  isLocationModalOpen.value = true;
};

const handleLocationSave = async () => {
  let result;
  if (locationForm.value.id) {
    result = await locationStore.updateLocation(locationForm.value);
  } else {
    result = await locationStore.createLocation(locationForm.value);
  }

  if (result === true) {
    isLocationModalOpen.value = false;
    // Si estábamos editando, actualizamos la selección
    if (
      selectedLocation.value &&
      selectedLocation.value.id === locationForm.value.id
    ) {
      selectLocation(locationForm.value);
    }
  } else {
    locationFormErrors.value = result;
  }
};

// --- Lógica para MODAL DE ÁREA ---
const openAreaModal = (area = null) => {
  areaFormErrors.value = {};
  if (area) {
    areaForm.value = { ...area }; // Editar
  } else {
    areaForm.value = {
      // Crear
      nombre: "",
      localidad_id: selectedLocation.value.id,
    };
  }
  isAreaModalOpen.value = true;
};

const handleAreaSave = async () => {
  let result;
  if (areaForm.value.id) {
    result = await areaStore.updateArea(areaForm.value);
  } else {
    result = await areaStore.createArea(areaForm.value);
  }

  if (result === true) {
    isAreaModalOpen.value = false;
  } else {
    areaFormErrors.value = result;
  }
};

// --- Lógica para MODAL DE HORARIO ---
const openScheduleModal = (schedule = null) => {
  scheduleFormErrors.value = {};
  if (schedule) {
    scheduleForm.value = { ...schedule }; // Editar
  } else {
    scheduleForm.value = {
      // Crear
      dia_semana: "",
      hora_apertura: "08:00",
      hora_cierre: "17:00",
      localidad_id: selectedLocation.value.id,
    };
  }
  isScheduleModalOpen.value = true;
};

const handleScheduleSave = async () => {
  let result;
  if (scheduleForm.value.id) {
    result = await scheduleStore.updateSchedule(scheduleForm.value);
  } else {
    result = await scheduleStore.createSchedule(scheduleForm.value);
  }

  if (result === true) {
    isScheduleModalOpen.value = false;
  } else {
    scheduleFormErrors.value = result;
  }
};

// --- Funciones de borrado ---
const confirmDeleteLocation = (id) => {
  if (
    window.confirm(
      "¿Seguro que quieres eliminar esta localidad y TODOS sus horarios y áreas?",
    )
  ) {
    locationStore.deleteLocation(id).then((success) => {
      if (success) {
        selectedLocation.value = null;
      }
    });
  }
};
const confirmDeleteArea = (area) => {
  if (
    window.confirm(`¿Seguro que quieres eliminar el área "${area.nombre}"?`)
  ) {
    areaStore.deleteArea(area);
  }
};
const confirmDeleteSchedule = (schedule) => {
  if (
    window.confirm(
      `¿Seguro que quieres eliminar el horario del ${getDayName(schedule.dia_semana)}?`,
    )
  ) {
    scheduleStore.deleteSchedule(schedule);
  }
};

// Helper
const getDayName = (dayNumber) => {
  return daysOfWeek[dayNumber - 1] || "Día inválido";
};
</script>

<template>
  <div
    class="p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen font-sans"
  >
    <div class="mx-auto max-w-7xl">
      <!-- Encabezado Principal -->
      <div class="">
        <div>
          <h1
            class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent"
          >
            Gestión de Localidades
          </h1>
          <p class="text-gray-500 mt-1 text-sm">
            Administra tus localidades, horarios y áreas
          </p>
        </div>
        <button
          @click="openLocationModal()"
          class="bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-2.5 px-5 rounded-xl shadow-md hover:shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center gap-2 transform hover:scale-105"
        >
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
              clip-rule="evenodd"
            />
          </svg>
          Nueva Localidad
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Maestra: Lista de Localidades -->
        <div class="lg:col-span-1">
          <div
            class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100"
          >
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
              <h2 class="text-white font-semibold text-lg">Localidades</h2>
              <p class="text-blue-100 text-xs">Selecciona una para gestionar</p>
            </div>
            <div
              v-if="locationStore.loading"
              class="p-8 text-center text-gray-500"
            >
              <div
                class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
              ></div>
              <p class="mt-2">Cargando localidades...</p>
            </div>
            <ul
              v-else
              class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto"
            >
              <li
                v-for="location in locationStore.locations"
                :key="location.id"
              >
                <a
                  @click="selectLocation(location)"
                  href="#"
                  class="block p-4 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 transition-all duration-150 cursor-pointer"
                  :class="{
                    'bg-gradient-to-r from-blue-100 to-blue-50 border-l-4 border-blue-600':
                      selectedLocation && selectedLocation.id === location.id,
                  }"
                >
                  <div class="flex justify-between items-center">
                    <div class="flex-1">
                      <p
                        class="font-semibold text-gray-800 group-hover:text-blue-800"
                      >
                        {{ location.nombre }}
                      </p>
                      <p class="text-sm text-gray-500 mt-0.5">
                        📍 {{ location.provincia }}, {{ location.municipio }}
                      </p>
                      <p class="text-xs text-gray-400 mt-1">
                        💰 ${{ location.costo_entrada }}
                      </p>
                    </div>
                    <svg
                      class="h-5 w-5 text-gray-400 flex-shrink-0"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                </a>
              </li>
            </ul>
          </div>
          <!-- Paginación de Localidades -->
          <div
            v-if="!locationStore.loading && locationStore.pagination.total > 10"
            class="mt-4 flex justify-center"
          >
            <div class="flex flex-wrap gap-1 justify-center">
              <button
                v-for="link in locationStore.pagination.links"
                :key="link.label"
                @click="changeLocationPage(link.url)"
                :disabled="!link.url || link.active"
                v-html="link.label"
                class="px-3 py-1.5 text-sm rounded-lg transition-all duration-200"
                :class="{
                  'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md':
                    link.active,
                  'bg-white hover:bg-gray-100 text-gray-700 border border-gray-300':
                    !link.active && link.url,
                  'opacity-50 cursor-not-allowed': !link.url && !link.active,
                }"
              ></button>
            </div>
          </div>
        </div>

        <!-- Columna de Detalle: Horarios y Áreas -->
        <div class="lg:col-span-2">
          <div
            v-if="!selectedLocation"
            class="bg-white rounded-2xl shadow-xl p-12 text-center text-gray-500 border border-gray-100"
          >
            <div
              class="inline-flex p-4 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full mb-4"
            >
              <svg
                class="h-16 w-16 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            <h3 class="mt-2 text-xl font-semibold text-gray-900">
              Selecciona una localidad
            </h3>
            <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
              Elige una localidad de la lista para ver y gestionar sus horarios
              y áreas.
            </p>
          </div>
          <div v-else class="space-y-6">
            <!-- Detalles de la Localidad Seleccionada -->
            <div
              class="bg-gradient-to-r from-white to-gray-50 p-6 rounded-2xl shadow-xl border border-gray-100"
            >
              <div
                class="flex flex-col sm:flex-row justify-between items-start gap-4"
              >
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <div
                      class="w-1 h-8 bg-gradient-to-b from-blue-600 to-blue-700 rounded-full"
                    ></div>
                    <h2 class="text-2xl font-bold text-gray-800">
                      {{ selectedLocation.nombre }}
                    </h2>
                  </div>
                  <div class="space-y-2 mt-3">
                    <p class="text-gray-600 flex items-start gap-2">
                      <span class="text-gray-400">📍</span>
                      <span>{{ selectedLocation.direccion }}</span>
                    </p>
                    <p class="text-gray-600 flex items-center gap-2">
                      <span class="text-gray-400">📞</span>
                      <span>{{
                        selectedLocation.telefono || "No especificado"
                      }}</span>
                    </p>
                    <p
                      class="mt-2 inline-flex items-center gap-2 bg-green-50 px-3 py-1.5 rounded-lg"
                    >
                      <span class="font-semibold text-green-800"
                        >Costo de Entrada:</span
                      >
                      <span class="text-green-600 font-bold text-lg"
                        >${{ selectedLocation.costo_entrada }}</span
                      >
                    </p>
                  </div>
                </div>
                <div class="flex gap-2">
                  <button
                    @click="openLocationModal(selectedLocation)"
                    class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                    title="Editar"
                  >
                    <svg
                      class="h-5 w-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      ></path>
                    </svg>
                  </button>
                  <button
                    @click="confirmDeleteLocation(selectedLocation.id)"
                    class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-lg transition-all duration-200"
                    title="Eliminar"
                  >
                    <svg
                      class="h-5 w-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      ></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Gestión de Horarios -->
            <div
              class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100"
            >
              <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200"
              >
                <div
                  class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3"
                >
                  <div>
                    <h3
                      class="text-xl font-bold text-gray-800 flex items-center gap-2"
                    >
                      <span class="text-2xl">🕐</span>
                      Horarios de Atención
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                      Define los días y horarios de operación
                    </p>
                  </div>
                  <button
                    @click="openScheduleModal()"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-2 px-4 rounded-xl hover:shadow-md transition-all duration-200 flex items-center gap-2 text-sm"
                  >
                    <svg
                      class="h-4 w-4"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    Agregar Horario
                  </button>
                </div>
              </div>
              <div class="p-6">
                <div v-if="scheduleStore.loading" class="text-center py-8">
                  <div
                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
                  ></div>
                  <p class="mt-2 text-gray-500">Cargando horarios...</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div
                    v-for="schedule in scheduleStore.schedules"
                    :key="schedule.id"
                    class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-white rounded-xl border border-gray-200 hover:shadow-md transition-all duration-200"
                  >
                    <div class="flex-1">
                      <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800 text-lg">{{
                          getDayName(schedule.dia_semana)
                        }}</span>
                      </div>
                      <p class="text-sm text-gray-600 mt-1">
                        🕒 {{ schedule.hora_apertura }} -
                        {{ schedule.hora_cierre }}
                      </p>
                    </div>
                    <div class="flex gap-2">
                      <button
                        @click="openScheduleModal(schedule)"
                        class="text-blue-600 hover:text-blue-800 p-1.5 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Editar"
                      >
                        <svg
                          class="h-4 w-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                          ></path>
                        </svg>
                      </button>
                      <button
                        @click="confirmDeleteSchedule(schedule)"
                        class="text-red-600 hover:text-red-800 p-1.5 hover:bg-red-50 rounded-lg transition-colors"
                        title="Eliminar"
                      >
                        <svg
                          class="h-4 w-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          ></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                <div
                  v-if="
                    scheduleStore.schedules.length === 0 &&
                    !scheduleStore.loading
                  "
                  class="text-center py-8"
                >
                  <div class="text-gray-400 text-5xl mb-3">📅</div>
                  <p class="text-gray-500">No hay horarios definidos</p>
                  <p class="text-sm text-gray-400 mt-1">
                    Haz clic en "Agregar Horario" para comenzar
                  </p>
                </div>
              </div>
            </div>

            <!-- Gestión de Áreas -->
            <div
              class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100"
            >
              <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200"
              >
                <div
                  class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3"
                >
                  <div>
                    <h3
                      class="text-xl font-bold text-gray-800 flex items-center gap-2"
                    >
                      <span class="text-2xl">🗺️</span>
                      Áreas de la Localidad
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                      Administra las diferentes áreas o secciones
                    </p>
                  </div>
                  <button
                    @click="openAreaModal()"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-2 px-4 rounded-xl hover:shadow-md transition-all duration-200 flex items-center gap-2 text-sm"
                  >
                    <svg
                      class="h-4 w-4"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    Agregar Área
                  </button>
                </div>
              </div>
              <div class="p-6">
                <div v-if="areaStore.loading" class="text-center py-8">
                  <div
                    class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
                  ></div>
                  <p class="mt-2 text-gray-500">Cargando áreas...</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div
                    v-for="area in areaStore.areas"
                    :key="area.id"
                    class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-white rounded-xl border border-gray-200 hover:shadow-md transition-all duration-200 group"
                  >
                    <div class="flex items-center gap-3 flex-1">
                      <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center"
                      >
                        <span class="text-xl">🏢</span>
                      </div>
                      <span class="text-gray-800 font-medium">{{
                        area.nombre
                      }}</span>
                    </div>
                    <div
                      class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                    >
                      <button
                        @click="openAreaModal(area)"
                        class="text-blue-600 hover:text-blue-800 p-1.5 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Editar"
                      >
                        <svg
                          class="h-4 w-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                          ></path>
                        </svg>
                      </button>
                      <button
                        @click="confirmDeleteArea(area)"
                        class="text-red-600 hover:text-red-800 p-1.5 hover:bg-red-50 rounded-lg transition-colors"
                        title="Eliminar"
                      >
                        <svg
                          class="h-4 w-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          ></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                <div
                  v-if="areaStore.areas.length === 0 && !areaStore.loading"
                  class="text-center py-8"
                >
                  <div class="text-gray-400 text-5xl mb-3">🗺️</div>
                  <p class="text-gray-500">No hay áreas definidas</p>
                  <p class="text-sm text-gray-400 mt-1">
                    Haz clic en "Agregar Área" para comenzar
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODALS (Manteniendo la misma estructura y funcionalidad) -->
    <!-- Modal de Localidad -->
    <div
      v-if="isLocationModalOpen"
      class="fixed inset-0 bg-black/20 backdrop-blur-sm flex justify-center items-center z-50 p-4"
      @click.self="isLocationModalOpen = false"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-auto transform transition-all"
      >
        <div class="flex justify-between items-center mb-4">
          <h2
            class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent"
          >
            {{ locationForm.id ? "Editar Localidad" : "Nueva Localidad" }}
          </h2>
          <button
            @click="isLocationModalOpen = false"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>
        <form @submit.prevent="handleLocationSave" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Nombre *</label
              >
              <input
                v-model="locationForm.nombre"
                type="text"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.nombre }"
                placeholder="Ej: Centro Comercial Plaza"
              />
              <p
                v-if="locationFormErrors.nombre"
                class="text-red-500 text-xs mt-1"
              >
                {{ locationFormErrors.nombre[0] }}
              </p>
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Teléfono</label
              >
              <input
                v-model="locationForm.telefono"
                type="text"
                class="w-full input"
                placeholder="Ej: 809-555-1234"
              />
            </div>
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-1 text-sm"
              >Dirección *</label
            >
            <input
              v-model="locationForm.direccion"
              type="text"
              class="w-full input"
              :class="{ 'border-red-500': locationFormErrors.direccion }"
              placeholder="Calle Principal #123"
            />
            <p
              v-if="locationFormErrors.direccion"
              class="text-red-500 text-xs mt-1"
            >
              {{ locationFormErrors.direccion[0] }}
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Provincia *</label
              >
              <input
                v-model="locationForm.provincia"
                type="text"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.provincia }"
                placeholder="Ej: Santo Domingo"
              />
              <p
                v-if="locationFormErrors.provincia"
                class="text-red-500 text-xs mt-1"
              >
                {{ locationFormErrors.provincia[0] }}
              </p>
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Municipio *</label
              >
              <input
                v-model="locationForm.municipio"
                type="text"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.municipio }"
                placeholder="Ej: Santo Domingo Este"
              />
              <p
                v-if="locationFormErrors.municipio"
                class="text-red-500 text-xs mt-1"
              >
                {{ locationFormErrors.municipio[0] }}
              </p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Región</label
              >
              <input
                v-model="locationForm.region"
                type="text"
                class="w-full input"
                placeholder="Ej: Región Sur"
              />
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Costo de Entrada *</label
              >
              <input
                v-model="locationForm.costo_entrada"
                type="number"
                step="0.01"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.costo_entrada }"
                placeholder="0.00"
              />
              <p
                v-if="locationFormErrors.costo_entrada"
                class="text-red-500 text-xs mt-1"
              >
                {{ locationFormErrors.costo_entrada[0] }}
              </p>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="isLocationModalOpen = false"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">
              {{ locationForm.id ? "Actualizar" : "Crear" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Área -->
    <div
      v-if="isAreaModalOpen"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex justify-center items-center z-50 p-4"
      @click.self="isAreaModalOpen = false"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-auto transform transition-all"
      >
        <div class="flex justify-between items-center mb-4">
          <h2
            class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent"
          >
            {{ areaForm.id ? "Editar Área" : "Nueva Área" }}
          </h2>
          <button
            @click="isAreaModalOpen = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>
        <form @submit.prevent="handleAreaSave">
          <div>
            <label class="block text-gray-700 font-semibold mb-1 text-sm"
              >Nombre del Área *</label
            >
            <input
              v-model="areaForm.nombre"
              type="text"
              class="w-full input"
              :class="{ 'border-red-500': areaFormErrors.nombre }"
              placeholder="Ej: Zona de Juegos, Restaurante, Estacionamiento"
            />
            <p v-if="areaFormErrors.nombre" class="text-red-500 text-xs mt-1">
              {{ areaFormErrors.nombre[0] }}
            </p>
          </div>
          <div class="flex justify-end gap-3 pt-6">
            <button
              type="button"
              @click="isAreaModalOpen = false"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">
              {{ areaForm.id ? "Actualizar" : "Guardar" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Horario -->
    <div
      v-if="isScheduleModalOpen"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex justify-center items-center z-50 p-4"
      @click.self="isScheduleModalOpen = false"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-auto transform transition-all"
      >
        <div class="flex justify-between items-center mb-4">
          <h2
            class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent"
          >
            {{ scheduleForm.id ? "Editar Horario" : "Nuevo Horario" }}
          </h2>
          <button
            @click="isScheduleModalOpen = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>
        <form @submit.prevent="handleScheduleSave" class="space-y-4">
          <div>
            <label class="block text-gray-700 font-semibold mb-1 text-sm"
              >Día de la Semana *</label
            >
            <select
              v-model="scheduleForm.dia_semana"
              class="w-full input"
              :class="{ 'border-red-500': scheduleFormErrors.dia_semana }"
            >
              <option disabled value="">Seleccione un día</option>
              <option
                v-for="(day, index) in daysOfWeek"
                :key="index + 1"
                :value="index + 1"
              >
                {{ day }}
              </option>
            </select>
            <p
              v-if="scheduleFormErrors.dia_semana"
              class="text-red-500 text-xs mt-1"
            >
              {{ scheduleFormErrors.dia_semana[0] }}
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Hora de Apertura *</label
              >
              <input
                v-model="scheduleForm.hora_apertura"
                type="time"
                class="w-full input"
                :class="{ 'border-red-500': scheduleFormErrors.hora_apertura }"
              />
              <p
                v-if="scheduleFormErrors.hora_apertura"
                class="text-red-500 text-xs mt-1"
              >
                {{ scheduleFormErrors.hora_apertura[0] }}
              </p>
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1 text-sm"
                >Hora de Cierre *</label
              >
              <input
                v-model="scheduleForm.hora_cierre"
                type="time"
                class="w-full input"
                :class="{ 'border-red-500': scheduleFormErrors.hora_cierre }"
              />
              <p
                v-if="scheduleFormErrors.hora_cierre"
                class="text-red-500 text-xs mt-1"
              >
                {{ scheduleFormErrors.hora_cierre[0] }}
              </p>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="isScheduleModalOpen = false"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">
              {{ scheduleForm.id ? "Actualizar" : "Guardar" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-700;
}

.btn-primary {
  @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-2 px-5 rounded-xl hover:shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105;
}

.btn-secondary {
  @apply bg-gray-200 text-gray-700 font-semibold py-2 px-5 rounded-xl hover:bg-gray-300 hover:shadow-md transition-all duration-200;
}

/* Estilos para scroll personalizado */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
</style>
