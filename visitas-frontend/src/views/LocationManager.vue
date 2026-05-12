<template>
  <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen font-sans">
    <div class="mx-auto">
      <!-- Encabezado Principal -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestión de Localidades</h1>
        <button
          @click="openLocationModal()"
          class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200 flex items-center"
        >
          <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
              clip-rule="evenodd"
            />
          </svg>
          Nueva Localidad
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Maestra: Lista de Localidades -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-lg">
            <div
              v-if="locationStore.loading"
              class="p-8 text-center text-gray-500"
            >
              Cargando localidades...
            </div>
            <ul v-else class="divide-y divide-gray-200">
              <li
                v-for="location in locationStore.locations"
                :key="location.id"
              >
                <a
                  @click="selectLocation(location)"
                  href="#"
                  class="block p-4 hover:bg-blue-50 transition-colors duration-150"
                  :class="{
                    'bg-blue-100':
                      selectedLocation && selectedLocation.id === location.id,
                  }"
                >
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="font-semibold text-blue-800">
                        {{ location.nombre }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ location.provincia }}, {{ location.municipio }}
                      </p>
                    </div>
                    <svg
                      class="h-5 w-5 text-gray-400"
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
            <div class="flex space-x-1">
              <button
                v-for="link in locationStore.pagination.links"
                :key="link.label"
                @click="changeLocationPage(link.url)"
                :disabled="!link.url || link.active"
                v-html="link.label"
                class="px-3 py-1.5 text-sm rounded-md transition-colors"
                :class="{
                  'bg-blue-600 text-white': link.active,
                  'bg-white hover:bg-gray-200': !link.active,
                }"
              ></button>
            </div>
          </div>
        </div>

        <!-- Columna de Detalle: Horarios y Áreas -->
        <div class="lg:col-span-2">
          <div
            v-if="!selectedLocation"
            class="bg-white rounded-xl shadow-lg p-12 text-center text-gray-500"
          >
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
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
            <h3 class="mt-2 text-lg font-medium text-gray-900">
              Selecciona una localidad
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Elige una localidad de la lista para ver y gestionar sus horarios
              y áreas.
            </p>
          </div>
          <div v-else class="space-y-8">
            <!-- Detalles de la Localidad Seleccionada -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
              <div class="flex justify-between items-start">
                <div>
                  <h2 class="text-2xl font-bold text-gray-800">
                    {{ selectedLocation.nombre }}
                  </h2>
                  <p class="text-gray-600">{{ selectedLocation.direccion }}</p>
                  <p class="mt-2 font-semibold">
                    Costo de Entrada:
                    <span class="text-green-600"
                      >${{ selectedLocation.costo_entrada }}</span
                    >
                  </p>
                </div>
                <div>
                  <button
                    @click="openLocationModal(selectedLocation)"
                    class="text-blue-500 hover:text-blue-700 p-1"
                  >
                    Editar
                  </button>
                  <button
                    @click="confirmDeleteLocation(selectedLocation.id)"
                    class="text-red-500 hover:text-red-700 p-1"
                  >
                    Eliminar
                  </button>
                </div>
              </div>
            </div>

            <!-- Gestión de Horarios -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-700">Horarios</h3>
                <button
                  @click="openScheduleModal()"
                  class="text-sm bg-blue-500 text-white font-semibold py-1 px-3 rounded-md hover:bg-blue-600"
                >
                  Agregar Horario
                </button>
              </div>
              <div v-if="scheduleStore.loading">Cargando horarios...</div>
              <ul v-else class="space-y-2">
                <li
                  v-for="schedule in scheduleStore.schedules"
                  :key="schedule.id"
                  class="flex justify-between items-center p-2 bg-gray-50 rounded-md"
                >
                  <div>
                    <span class="font-semibold text-gray-800">{{
                      getDayName(schedule.dia_semana)
                    }}</span
                    >:
                    <span class="text-gray-600"
                      >{{ schedule.hora_apertura }} -
                      {{ schedule.hora_cierre }}</span
                    >
                  </div>
                  <div class="space-x-2">
                    <button
                      @click="openScheduleModal(schedule)"
                      class="text-sm text-blue-500"
                    >
                      Editar
                    </button>
                    <button
                      @click="confirmDeleteSchedule(schedule)"
                      class="text-sm text-red-500"
                    >
                      Eliminar
                    </button>
                  </div>
                </li>
                <li
                  v-if="scheduleStore.schedules.length === 0"
                  class="text-center text-gray-500 py-4"
                >
                  No hay horarios definidos.
                </li>
              </ul>
            </div>

            <!-- Gestión de Áreas -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-700">Áreas</h3>
                <button
                  @click="openAreaModal()"
                  class="text-sm bg-blue-500 text-white font-semibold py-1 px-3 rounded-md hover:bg-blue-600"
                >
                  Agregar Área
                </button>
              </div>
              <div v-if="areaStore.loading">Cargando áreas...</div>
              <ul v-else class="space-y-2">
                <li
                  v-for="area in areaStore.areas"
                  :key="area.id"
                  class="flex justify-between items-center p-2 bg-gray-50 rounded-md"
                >
                  <span class="text-gray-800">{{ area.nombre }}</span>
                  <div class="space-x-2">
                    <button
                      @click="openAreaModal(area)"
                      class="text-sm text-blue-500"
                    >
                      Editar
                    </button>
                    <button
                      @click="confirmDeleteArea(area)"
                      class="text-sm text-red-500"
                    >
                      Eliminar
                    </button>
                  </div>
                </li>
                <li
                  v-if="areaStore.areas.length === 0"
                  class="text-center text-gray-500 py-4"
                >
                  No hay áreas definidas.
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODALS -->

    <!-- Modal de Localidad -->
    <div
      v-if="isLocationModalOpen"
      class="fixed inset-0 bg-opacity-30 backdrop-blur-sm flex justify-center items-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4">
        <h2 class="text-2xl font-bold mb-4">
          {{ locationForm.id ? "Editar Localidad" : "Nueva Localidad" }}
        </h2>
        <form @submit.prevent="handleLocationSave" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label
                for="loc-nombre"
                class="block text-gray-700 font-semibold mb-1"
                >Nombre</label
              >
              <input
                v-model="locationForm.nombre"
                type="text"
                id="loc-nombre"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.nombre }"
              />
              <p
                v-if="locationFormErrors.nombre"
                class="text-red-500 text-sm mt-1"
              >
                {{ locationFormErrors.nombre[0] }}
              </p>
            </div>
            <div>
              <label
                for="loc-telefono"
                class="block text-gray-700 font-semibold mb-1"
                >Teléfono</label
              >
              <input
                v-model="locationForm.telefono"
                type="text"
                id="loc-telefono"
                class="w-full input"
              />
            </div>
          </div>
          <div>
            <label
              for="loc-direccion"
              class="block text-gray-700 font-semibold mb-1"
              >Dirección</label
            >
            <input
              v-model="locationForm.direccion"
              type="text"
              id="loc-direccion"
              class="w-full input"
              :class="{ 'border-red-500': locationFormErrors.direccion }"
            />
            <p
              v-if="locationFormErrors.direccion"
              class="text-red-500 text-sm mt-1"
            >
              {{ locationFormErrors.direccion[0] }}
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label
                for="loc-provincia"
                class="block text-gray-700 font-semibold mb-1"
                >Provincia</label
              >
              <input
                v-model="locationForm.provincia"
                type="text"
                id="loc-provincia"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.provincia }"
              />
              <p
                v-if="locationFormErrors.provincia"
                class="text-red-500 text-sm mt-1"
              >
                {{ locationFormErrors.provincia[0] }}
              </p>
            </div>
            <div>
              <label
                for="loc-municipio"
                class="block text-gray-700 font-semibold mb-1"
                >Municipio</label
              >
              <input
                v-model="locationForm.municipio"
                type="text"
                id="loc-municipio"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.municipio }"
              />
              <p
                v-if="locationFormErrors.municipio"
                class="text-red-500 text-sm mt-1"
              >
                {{ locationFormErrors.municipio[0] }}
              </p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label
                for="loc-region"
                class="block text-gray-700 font-semibold mb-1"
                >Región</label
              >
              <input
                v-model="locationForm.region"
                type="text"
                id="loc-region"
                class="w-full input"
              />
            </div>
            <div>
              <label
                for="loc-costo"
                class="block text-gray-700 font-semibold mb-1"
                >Costo de Entrada</label
              >
              <input
                v-model="locationForm.costo_entrada"
                type="number"
                step="0.01"
                id="loc-costo"
                class="w-full input"
                :class="{ 'border-red-500': locationFormErrors.costo_entrada }"
              />
              <p
                v-if="locationFormErrors.costo_entrada"
                class="text-red-500 text-sm mt-1"
              >
                {{ locationFormErrors.costo_entrada[0] }}
              </p>
            </div>
          </div>
          <div class="flex justify-end space-x-4 pt-4">
            <button
              type="button"
              @click="isLocationModalOpen = false"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Área -->
    <div
      v-if="isAreaModalOpen"
      class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <h2 class="text-2xl font-bold mb-4">
          {{ areaForm.id ? "Editar Área" : "Nueva Área" }}
        </h2>
        <form @submit.prevent="handleAreaSave">
          <div>
            <label
              for="area-nombre"
              class="block text-gray-700 font-semibold mb-1"
              >Nombre del Área</label
            >
            <input
              v-model="areaForm.nombre"
              type="text"
              id="area-nombre"
              class="w-full input"
              :class="{ 'border-red-500': areaFormErrors.nombre }"
            />
            <p v-if="areaFormErrors.nombre" class="text-red-500 text-sm mt-1">
              {{ areaFormErrors.nombre[0] }}
            </p>
          </div>
          <div class="flex justify-end space-x-4 pt-6">
            <button
              type="button"
              @click="isAreaModalOpen = false"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Horario -->
    <div
      v-if="isScheduleModalOpen"
      class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4">
        <h2 class="text-2xl font-bold mb-4">
          {{ scheduleForm.id ? "Editar Horario" : "Nuevo Horario" }}
        </h2>
        <form @submit.prevent="handleScheduleSave" class="space-y-4">
          <div>
            <label for="sch-dia" class="block text-gray-700 font-semibold mb-1"
              >Día de la Semana</label
            >
            <select
              v-model="scheduleForm.dia_semana"
              id="sch-dia"
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
              class="text-red-500 text-sm mt-1"
            >
              {{ scheduleFormErrors.dia_semana[0] }}
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label
                for="sch-apertura"
                class="block text-gray-700 font-semibold mb-1"
                >Hora de Apertura</label
              >
              <input
                v-model="scheduleForm.hora_apertura"
                type="time"
                id="sch-apertura"
                class="w-full input"
                :class="{ 'border-red-500': scheduleFormErrors.hora_apertura }"
              />
              <p
                v-if="scheduleFormErrors.hora_apertura"
                class="text-red-500 text-sm mt-1"
              >
                {{ scheduleFormErrors.hora_apertura[0] }}
              </p>
            </div>
            <div>
              <label
                for="sch-cierre"
                class="block text-gray-700 font-semibold mb-1"
                >Hora de Cierre</label
              >
              <input
                v-model="scheduleForm.hora_cierre"
                type="time"
                id="sch-cierre"
                class="w-full input"
                :class="{ 'border-red-500': scheduleFormErrors.hora_cierre }"
              />
              <p
                v-if="scheduleFormErrors.hora_cierre"
                class="text-red-500 text-sm mt-1"
              >
                {{ scheduleFormErrors.hora_cierre[0] }}
              </p>
            </div>
          </div>
          <div class="flex justify-end space-x-4 pt-4">
            <button
              type="button"
              @click="isScheduleModalOpen = false"
              class="btn-secondary"
            >
              Cancelar
            </button>
            <button type="submit" class="btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

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

<style>
/* Para una mejor apariencia de los inputs del formulario */
.input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors;
}
.btn-primary {
  @apply bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors;
}
.btn-secondary {
  @apply bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors;
}
</style>
