<script setup>
import { ref, onMounted } from "vue";
import BaseModal from "../components/BaseModal.vue";
import VisitorSearchOrCreate from "../components/VisitorSearchOrCreate.vue";
import { useReservationStore } from "../stores/reservationStore";
import { useVisitStore } from "../stores/visitStore";
import { useDataStore } from "../stores/dataStore";

const reservationStore = useReservationStore();
const visitStore = useVisitStore();
const dataStore = useDataStore();

const currentVisitor = ref(null);
const visitForm = ref({
  visitante_id: null,
  reserva_id: null,
  fecha: "",
  hora_entrada: "",
  motivo: "",
  area_id: null,
  responsable: "",
  no_carnet: "",
  estado: "activa",
});
const visitFormErrors = ref({}); // Para mostrar errores de validación del servidor
const frontendErrors = ref({}); // Para mostrar errores de validación del frontend
const showVisitForm = ref(false);

const modalState = ref({
  loading: false,
  success: false,
  error: false,
  errorTitle: "",
  errorMessage: "",
});

onMounted(() => {
  dataStore.fetchAll(); // Carga todos los datos para los selects
});

const handleVisitorSelected = async (visitorData) => {
  // Manejar tanto objetos como arrays
  let visitor = Array.isArray(visitorData) ? visitorData[0] : visitorData;
  
  // Validar que el objeto tenga los campos necesarios
  if (!visitor || !visitor.id) {
    // console.error("Datos de visitante inválidos:", visitorData);
    return;
  }
  
  // console.log("Visitante seleccionado:", visitor); // Para debug
  
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
    fecha: now.toISOString().split("T")[0],
    hora_entrada: now.toTimeString().split(" ")[0].substring(0, 5),
    motivo: reserva ? reserva.motivo : "",
    area_id: reserva ? reserva.area_id : null,
    responsable: "",
    no_carnet: "",
    estado: "activa",
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
    frontendErrors.value.area_id = "Área a Visitar es obligatoria";
  }

  return Object.keys(frontendErrors.value).length === 0;
};

const handleCreateVisit = async () => {
  // Primero valida el frontend
  if (!validateForm()) {
    modalState.value.error = true;
    modalState.value.errorTitle = "Campos Obligatorios";
    modalState.value.errorMessage =
      "Por favor, completa todos los campos obligatorios (marcados con *)";
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
        await reservationStore.updateReservationStatus(
          visitForm.value.reserva_id,
          "utilizada",
        );
      }
      modalState.value.loading = false; // Cierra modal de carga
      modalState.value.success = true; // Muestra modal de éxito
      // No llamar resetFlow aquí, se llama cuando el usuario cierra el modal de éxito
    } else {
      // Asigna los errores de validación para mostrarlos en el formulario
      modalState.value.loading = false; // Muestra modal de carga

      visitFormErrors.value = result.errors;
      // console.log("errores ", result.errors);
      modalState.value.error = true;
      modalState.value.errorTitle = "Error al Registrar";
      modalState.value.errorMessage =
        "Hubo un problema al registrar la visita. Por favor, revisa los campos.";
    }
  } catch (error) {
    modalState.value.loading = false;
    modalState.value.error = true;
    modalState.value.errorTitle = "Error";
    modalState.value.errorMessage =
      "Ocurrió un error inesperado. Por favor, intenta nuevamente.";
  }
};

const closeSuccessModal = (value) => {
  modalState.value.success = value;
  // Solo resetea el flujo cuando se cierra el modal de éxito
  if (!value) {
    resetFlow();
  }
};

const closeErrorModal = (value) => {
  modalState.value.error = value;
};

const resetFlow = () => {
  currentVisitor.value = null;
  showVisitForm.value = false;
  reservationStore.clearPending();
  visitForm.value = {
    visitante_id: null,
    reserva_id: null,
    fecha: "",
    hora_entrada: "",
    motivo: "",
    area_id: null,
    responsable: "",
    no_carnet: "",
    estado: "activa",
  };
  visitFormErrors.value = {};
  frontendErrors.value = {};
};
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 font-sans"
  >
    <div class="container mx-auto px-4 py-8 max-w-6xl">
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
      <VisitorSearchOrCreate
        @visitor-selected="handleVisitorSelected"
        v-if="!currentVisitor"
      />

      <!-- Paso 2: Mostrar visitante y gestionar visita/reserva -->
      <div v-if="currentVisitor" class="space-y-6">
        <!-- Info del Visitante Seleccionado -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div
              class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
            >
              <div class="text-white">
                <div class="flex items-center gap-2 mb-1">
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                  <span class="text-sm font-medium opacity-90"
                    >Visitante Seleccionado</span
                  >
                </div>
                <h2 class="text-2xl font-bold">
                  {{ currentVisitor.nombres }} {{ currentVisitor.apellidos }}
                </h2>
                <p class="text-blue-100 mt-1 flex items-center gap-2">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                    />
                  </svg>
                  Documento: {{ currentVisitor.documento_identidad }}
                </p>
                <p
                  v-if="currentVisitor.edad"
                  class="text-blue-100 text-sm mt-1"
                >
                  Edad: {{ currentVisitor.edad }} años
                </p>
              </div>
              <button
                @click="resetFlow"
                class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center gap-2 text-sm font-medium"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                Nueva Visita
              </button>
            </div>
          </div>
        </div>

        <!-- Lógica Condicional: ¿Tiene reserva? -->
        <div
          v-if="reservationStore.loading"
          class="bg-white rounded-2xl shadow-lg p-12 flex flex-col items-center justify-center"
        >
          <div class="relative">
            <div
              class="w-16 h-16 border-4 border-gray-200 border-t-blue-600 rounded-full animate-spin"
            ></div>
          </div>
          <p class="mt-4 text-gray-600 font-medium">
            Cargando información de reserva...
          </p>
        </div>

        <!-- Caso A: Reserva encontrada -->
        <div
          v-if="reservationStore.pendingReservation"
          class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl shadow-lg overflow-hidden"
        >
          <div class="p-6">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <div
                  class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center"
                >
                  <svg
                    class="w-6 h-6 text-green-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-xl font-bold text-green-800">
                  Reserva Pendiente Encontrada
                </h3>
                <p class="text-green-700 mt-2">
                  Este visitante tiene una reserva para el
                  <strong class="font-semibold">{{
                    reservationStore.pendingReservation.fecha
                  }}</strong>
                  a las
                  <strong class="font-semibold">{{
                    reservationStore.pendingReservation.hora
                  }}</strong
                  >.
                </p>
                <p class="mt-1 text-green-700">
                  Motivo:
                  {{
                    reservationStore.pendingReservation.motivo ||
                    "No especificado"
                  }}
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                  <button
                    @click="useReservation"
                    class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                    Usar Reserva
                  </button>
                  <button
                    @click="ignoreReservation"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-all duration-200"
                  >
                    Ignorar y registrar visita normal
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Caso B: Formulario de Visita (se muestra si no hay reserva o si se decide ignorar) -->
        <div
          v-if="showVisitForm"
          class="bg-white rounded-2xl shadow-xl overflow-hidden"
        >
          <div
            class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-200"
          >
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center"
              >
                <svg
                  class="w-4 h-4 text-blue-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                  />
                </svg>
              </div>
              Detalles de la Visita
            </h3>
          </div>

          <form @submit.prevent="handleCreateVisit" class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div
                :class="
                  currentVisitor.edad < 18 ? 'md:col-span-1' : 'md:col-span-2'
                "
              >
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Área a Visitar <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="visitForm.area_id"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm"
                  :class="{
                    'border-2 border-red-500 bg-red-50': frontendErrors.area_id,
                  }"
                >
                  <option :value="null" disabled>
                    -- Seleccione un área --
                  </option>
                  <option
                    v-for="area in dataStore.areas"
                    :key="area.id"
                    :value="area.id"
                  >
                    {{ area.nombre }}
                  </option>
                </select>
                <p
                  v-if="frontendErrors.area_id"
                  class="text-red-500 text-sm mt-1 flex items-center gap-1"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  {{ frontendErrors.area_id }}
                </p>
                <p
                  v-if="visitForm.reserva_id"
                  class="text-green-600 text-sm mt-1 flex items-center gap-1"
                >
                  <svg
                    class="w-3 h-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Área seleccionada de la reserva
                </p>
              </div>

              <div v-if="currentVisitor.edad < 18">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Responsable (si es menor de edad)
                </label>
                <input
                  v-model="visitForm.responsable"
                  type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                  placeholder="Nombre del acompañante adulto"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Motivo de la Visita
                <span class="text-gray-400 text-xs font-normal"
                  >(Opcional)</span
                >
              </label>
              <textarea
                v-model="visitForm.motivo"
                rows="3"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none"
                placeholder="Describa el motivo de la visita..."
              ></textarea>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
              <button
                type="submit"
                class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
                Registrar Visita
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animaciones personalizadas */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Estilos mejorados para inputs y botones */
.input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  background-color: white;
}

.input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background-color: white;
}

.input.border-red-500 {
  border-color: #ef4444;
  background-color: #fef2f2;
}

.input:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  font-weight: 600;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.btn-primary:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

/* Animación para elementos que aparecen */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
