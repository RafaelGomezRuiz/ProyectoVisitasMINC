<script setup>
import { ref, watch } from "vue";
import { useVisitorStore } from "../stores/visitorStore";
import { useDataStore } from "../stores/dataStore";
import BaseModal from "./BaseModal.vue";

const emit = defineEmits(["visitor-selected"]);
const visitorStore = useVisitorStore();
const dataStore = useDataStore();

// Modal state for create visitor feedback
const modalState = ref({
  loading: false,
  success: false,
  error: false,
  errorTitle: "",
  errorMessage: "",
  requiredFields: false,
});

// Búsqueda
const documentType = ref(null);
const documentNumber = ref("");
const isSearching = ref(false);
const searchError = ref("");
const selected = ref(false);
const selectedVisitor = ref(null);
const showResultsModal = ref(false);
const showRegisterButton = ref(false);
const searchValidationErrors = ref({
  documentType: false,
  documentNumber: false,
});

// Modal de crear visitante
const isModalOpen = ref(false);
const visitorForm = ref([
  {
    tipo_doc: null,
    documento_identidad: "",
    nombres: "",
    apellidos: "",
    edad: null,
    correo: "",
    sexo: null,
    pais_origen_id: null,
    tipo_visitante_id: null,
  },
]);

///Agregar un visitante
const addVisitorForm = () => {
  visitorForm.value.push({
    tipo_doc: documentType.value,
    documento_identidad: documentType.value === "otro" ? null : "",
    nombres: "",
    apellidos: "",
    edad: null,
    correo: "",
    sexo: null,
    pais_origen_id: null,
    tipo_visitante_id: null,
  });

  console.log(visitorForm.value);
};

//Eliminar un visitante
const removerVisitorForm = (index) => {
  visitorForm.value.splice(index, 1);
};

const visitorFormErrors = ref({});

// Watchers para limpiar errores cuando se llenan los campos
watch(documentType, (newVal) => {
  if (newVal !== null) {
    searchValidationErrors.value.documentType = false;
  }
});

watch(documentNumber, (newVal) => {
  if (newVal && newVal.trim() !== "") {
    searchValidationErrors.value.documentNumber = false;
  }
});

watch(
  () => visitorForm.value[0].nombres,
  (newVal) => {
    const clean = newVal?.trim();

    if (clean) {
      visitorFormErrors.value.nombres = null;

      // SOLO si realmente quieres resetear otros campos
      visitorForm.value[0].apellidos = "";
      visitorForm.value[0].edad = null;
      visitorForm.value[0].sexo = null;
      visitorForm.value[0].pais_origen_id = null;
      visitorForm.value[0].tipo_visitante_id = null;
    }
  },
);

const performSearch = async () => {
  searchError.value = "";
  showRegisterButton.value = false;

  // Validar campos requeridos
  searchValidationErrors.value.documentType = !documentType.value;
  // Si tipo_doc es 'otro', no se requiere número de documento
  if (documentType.value === "otro") {
    searchValidationErrors.value.documentNumber = false;
    documentNumber.value = "0"; // Establecer 0 para menores sin documento
  } else {
    searchValidationErrors.value.documentNumber =
      !documentNumber.value || documentNumber.value.trim() === "";
  }

  if (
    searchValidationErrors.value.documentType ||
    searchValidationErrors.value.documentNumber
  ) {
    modalState.value.requiredFields = true;
    return;
  }

  if (!validateDocument()) {
    searchError.value = "Formato de pasaporte inválido (Ej: A1234567)";
    return;
  }

  isSearching.value = true;
  try {
    const searchQuery =
      documentType.value === "otro" ? "0" : documentNumber.value;
    await visitorStore.searchVisitors(searchQuery);
    console.log("visitante ", visitorStore.searchResults);
    if (visitorStore.searchResults.length === 0) {
      // Abrir directamente el modal de registro
      openCreateModal();
    } else if (visitorStore.searchResults.length === 1) {
      // Seleccionar automáticamente si hay un solo resultado
      selectVisitor(visitorStore.searchResults[0]);
    } else {
      // Mostrar modal para seleccionar entre múltiples resultados
      showResultsModal.value = true;
    }
  } catch (error) {
    console.error("Error en búsqueda:", error);
    searchError.value = "Error al buscar el visitante";
  } finally {
    isSearching.value = false;
  }
};

const selectVisitor = (visitor) => {
  selected.value = true;
  selectedVisitor.value = visitor;
  showResultsModal.value = false;
  isModalOpen.value = false; // Asegura cerrar cualquier modal abierto
  visitorStore.clearSearchResults();
  emit("visitor-selected", visitor);
};

const resetSearch = () => {
  selected.value = false;
  selectedVisitor.value = null;
  documentType.value = null;
  documentNumber.value = "";
  searchError.value = "";
  showRegisterButton.value = false;
  visitorStore.clearSearchResults();
};

const openCreateModal = () => {
  visitorForm.value = [
    {
      tipo_doc: documentType.value,
      documento_identidad:
        documentType.value === "otro" ? null : documentNumber.value,
      nombres: "",
      apellidos: "",
      edad: null,
      correo: "",
      sexo: null,
      pais_origen_id: null,
      tipo_visitante_id: null,
    },
  ];
  visitorFormErrors.value = {};
  isModalOpen.value = true;
};

const validateEmail = (email) => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
};

const handleCreateVisitor = async () => {
  const visitor = visitorForm.value[0];

  const errors = {};

  if (!visitor.nombres) errors.nombres = "El nombre es obligatorio.";
  if (!visitor.apellidos) errors.apellidos = "El apellido es obligatorio.";
  if (visitor.edad === null || visitor.edad === "") {
    errors.edad = "La edad es obligatoria.";
  }

  if (!visitor.sexo || visitor.sexo === null)
    errors.sexo = "El sexo es obligatorio.";

  if (!visitor.pais_origen_id)
    errors.pais_origen_id = "El país de origen es obligatorio.";
  if (!visitor.tipo_visitante_id)
    errors.tipo_visitante_id = "El tipo de visitante es obligatorio.";

  // 👇 ESTO TE FALTA
  visitorFormErrors.value = errors;

  // ❗ si hay errores, detén el proceso
  if (Object.keys(errors).length > 0) {
    return;
  }

  // // Close the create modal immediately and show loading modal
  // isModalOpen.value = false;
  // modalState.value.loading = true;

  try {
    const result = await visitorStore.createVisitor(visitorForm.value);
    modalState.value.loading = false;

    console.log("ARRAY COMPLETO");
    console.log(JSON.stringify(visitorForm.value, null, 2));
    if (result.success) {
      // Show success modal and emit selection so parent proceeds
      modalState.value.success = true;
      // Si result.data es un array, toma el primer elemento
      const visitorToSelect = Array.isArray(result.data)
        ? result.data[0]
        : result.data;
      selectVisitor(visitorToSelect);
    } else {
      modalState.value.loading = false;

      visitorFormErrors.value = {};

      isModalOpen.value = true;

      modalState.value.error = true;
      modalState.value.errorTitle = "Error";
      modalState.value.errorMessage = "Ocurrió un error al crear el visitante.";
      // Validation errors from API — reopen the create form so user can fix
      visitorFormErrors.value = result.errors || {};
      isModalOpen.value = true;
      modalState.value.error = true;
      modalState.value.errorTitle = "Error al crear visitante";
      modalState.value.errorMessage =
        "Por favor revisa los campos e intenta nuevamente.";
    }
  } catch (error) {
    modalState.value.loading = false;

    visitorFormErrors.value = {};
    // Reopen form so user can retry

    // console.log("ERROR COMPLETO");
    // // console.log(err);

    // console.log("RESPONSE");

    // console.log("DATA");
    // console.log(err.response?.data);

    // console.log("ERRORES");
    // console.log(err.response?.data?.errores);

    isModalOpen.value = true;
    modalState.value.error = true;
    modalState.value.errorTitle = "Error";
    modalState.value.errorMessage = "Ocurrió un error al crear el visitante.";
  }
};

////Manejo de inputs////
const handleDocumentInput = (e) => {
  let value = e.target.value;

  if (documentType.value === "pasaporte") {
    // Solo letras y números
    value = value.replace(/[^A-Za-z0-9]/g, "");

    if (value.length > 0) {
      // Primera posición: letra obligatoria
      const first = value.charAt(0).replace(/[^A-Za-z]/g, "");

      // resto: solo números (máx 9)
      const rest = value
        .slice(1)
        .replace(/[^0-9]/g, "")
        .slice(0, 10);

      value = first + rest;
    }

    // 🔥 máximo 10 caracteres total (1 letra + 9 números)
    value = value.slice(0, 10);
  } else if (documentType.value === "cedula") {
    // Solo números, máx 11 dígitos
    value = value.replace(/[^0-9]/g, "").slice(0, 11);
  }

  documentNumber.value = value;
};

// Handler de input para visitantes del formulario (adicionales)
const handleVisitorDocumentInput = (e, visitor) => {
  let value = e.target.value;

  if (visitor.tipo_doc === "pasaporte") {
    // Solo letras y números, máx 9 caracteres
    value = value.replace(/[^A-Za-z0-9]/g, "");

    // Fuerza primera letra, resto solo números
    if (value.length > 0) {
      const first = value.charAt(0).replace(/[^A-Za-z]/g, "");
      const rest = value.slice(1).replace(/[^0-9]/g, "");
      value = first + rest;
    }
    value = value.slice(0, 9);
  } else if (visitor.tipo_doc === "cedula") {
    // Solo números, máx 11 dígitos
    value = value.replace(/[^0-9]/g, "").slice(0, 11);
  }

  visitor.documento_identidad = value;
};

const validateDocument = () => {
  const value = documentNumber.value.trim();

  if (documentType.value === "pasaporte") {
    return /^[A-Za-z][0-9]+$/.test(value);
  }

  if (documentType.value === "cedula") {
    return /^[0-9]+$/.test(value);
  }

  return true;
};
</script>

<template>
  <div class="bg-white p-6 rounded-xl shadow-lg">
    <!-- BaseModals: loading / success / error / required fields -->
    <BaseModal
      :model-value="modalState.loading"
      title="Guardando Visitante"
      icon="mdi-loading"
      icon-color="primary"
    />

    <BaseModal
      :model-value="modalState.success"
      title="¡Visitante Creado!"
      message="El visitante ha sido creado correctamente."
      icon="mdi-check-circle"
      icon-color="success"
      :show-close="true"
      @update:model-value="(v) => (modalState.success = v)"
    />

    <BaseModal
      :model-value="modalState.error"
      :title="modalState.errorTitle"
      :message="modalState.errorMessage"
      icon="mdi-alert-circle"
      icon-color="error"
      :show-close="true"
      @update:model-value="(v) => (modalState.error = v)"
    />

    <BaseModal
      :model-value="modalState.requiredFields"
      title="Campos Requeridos"
      message="Por favor completa los campos obligatorios: Tipo de Documento y Número de Documento."
      icon="mdi-alert-circle"
      icon-color="warning"
      :show-close="true"
      @update:model-value="(v) => (modalState.requiredFields = v)"
    />

    <!-- Búsqueda de Visitante -->
    <div class="space-y-6">
      <div class="border-b border-gray-200 pb-3">
        <h3
          class="text-2xl font-bold text-gray-800 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
        >
          Buscar o Registrar Visitante
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          Ingrese los datos del documento para buscar un visitante existente
        </p>
      </div>

      <!-- Fila de búsqueda: Tipo de Documento, Número, Botón Buscar -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Tipo de Documento <span class="text-red-500">*</span>
          </label>
          <select
            v-model="documentType"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm"
            :class="{
              'border-2 border-red-500 bg-red-50':
                searchValidationErrors.documentType,
            }"
            :disabled="isSearching"
          >
            <option :value="null" disabled>-- Seleccione --</option>
            <option value="cedula">Cédula</option>
            <option value="pasaporte">Pasaporte</option>
            <option value="otro">Otro / Menor</option>
          </select>
          <p
            v-if="searchValidationErrors.documentType"
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
            Es requerido
          </p>
          <div v-else class="h-6"></div>
        </div>

        <div v-if="documentType !== 'otro'">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Número de Documento <span class="text-red-500">*</span>
          </label>
          <input
            v-model="documentNumber"
            type="text"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm"
            :class="{
              'border-2 border-red-500 bg-red-50':
                searchValidationErrors.documentNumber &&
                documentType !== 'otro',
            }"
            :placeholder="
              documentType === 'pasaporte' ? 'Ej: A1234567' : 'Ej: 0102030405'
            "
            @input="handleDocumentInput"
            :disabled="
              isSearching || documentType === 'otro' || documentType === null
            "
          />
          <p
            v-if="documentType === null"
            class="text-amber-600 text-xs mt-1 flex items-center gap-1"
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
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            Selecciona primero el tipo de documento
          </p>
          <p
            v-else-if="
              searchValidationErrors.documentNumber && documentType !== 'otro'
            "
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
            Es requerido
          </p>
          <p
            v-else-if="documentType === 'otro'"
            class="text-green-600 text-xs mt-1 flex items-center gap-1"
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
            No es necesario para menores
          </p>
          <div v-else class="h-6"></div>
        </div>

        <div class="flex items-end -translate-y-6">
          <button
            @click="performSearch"
            :disabled="isSearching"
            class="w-full px-7 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98] h-11"
          >
            <span
              v-if="!isSearching"
              class="flex items-center justify-center gap-2"
            >
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
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
              Buscar
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg
                class="animate-spin h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                ></path>
              </svg>
              Buscando...
            </span>
          </button>
        </div>
      </div>

      <!-- Mensajes de estado -->
      <div
        v-if="searchError"
        class="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl"
      >
        <p class="text-red-600 text-sm flex items-center gap-2">
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
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          {{ searchError }}
        </p>
      </div>

      <!-- Modal de selección de resultados -->
      <div
        v-if="showResultsModal && visitorStore.searchResults.length > 1"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50 p-4"
      >
        <div
          class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 transform transition-all"
        >
          <div class="flex items-center gap-3 mb-4">
            <div
              class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center"
            >
              <svg
                class="w-6 h-6 text-blue-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
            </div>
            <h4 class="text-xl font-bold text-gray-800">
              Seleccionar Visitante
            </h4>
          </div>
          <p class="text-gray-600 mb-4">
            Se encontraron
            <span class="font-semibold text-blue-600">{{
              visitorStore.searchResults.length
            }}</span>
            resultados. Por favor selecciona uno:
          </p>
          <div class="space-y-2 max-h-64 overflow-y-auto mb-4">
            <button
              v-for="visitor in visitorStore.searchResults"
              :key="visitor.id"
              @click="selectVisitor(visitor)"
              class="w-full text-left p-4 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 group"
            >
              <p class="font-semibold text-gray-800 group-hover:text-blue-700">
                {{ visitor.nombres }} {{ visitor.apellidos }}
              </p>
              <p class="text-sm text-gray-500 mt-1">
                📄 {{ visitor.documento_identidad }}
              </p>
            </button>
          </div>
          <button
            @click="showResultsModal = false"
            class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all duration-200"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal para crear visitante -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50 p-4"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all duration-300"
      >
        <!-- Header -->
        <div
          class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 via-white to-purple-50"
        >
          <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center shadow-md"
            >
              <svg
                class="w-5 h-5 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                />
              </svg>
            </div>
            Registrar Nuevo Visitante
          </h3>
        </div>

        <!-- Add Visitor Button -->
        <div class="px-6 pt-4 pb-3 bg-gray-50/80 border-b border-gray-100">
          <button
            @click="addVisitorForm"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-blue-700 bg-blue-100 rounded-xl hover:bg-blue-200 hover:shadow-md transition-all duration-200 group"
          >
            <svg
              class="w-4 h-4 group-hover:rotate-90 transition-transform duration-200"
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
            Agregar otro visitante
          </button>
        </div>

        <!-- Form -->
        <form
          @submit.prevent="handleCreateVisitor"
          class="flex-1 overflow-y-auto px-6 py-5 space-y-6"
        >
          <div
            v-for="(visitor, index) in visitorForm"
            :key="index"
            class="relative"
          >
            <!-- Remove button for additional visitors -->
            <button
              v-if="index > 0"
              type="button"
              @click="removerVisitorForm(index)"
              class="absolute -top-3 -right-2 z-10 p-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-full hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-md hover:shadow-lg hover:scale-110"
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
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>

            <!-- Visitor Card -->
            <div
              class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden"
            >
              <!-- Visitor Header -->
              <div
                class="bg-gradient-to-r from-gray-50 to-white px-5 py-3 border-b border-gray-200"
              >
                <h2
                  class="text-lg font-semibold text-gray-700 flex items-center gap-2"
                >
                  <span
                    class="w-7 h-7 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-sm"
                  >
                    {{ index + 1 }}
                  </span>
                  Visitante {{ index + 1 }}
                </h2>
              </div>

              <!-- Visitor Content -->
              <div class="p-5 space-y-5">
                <!-- Document Section (only for index > 0) -->
                <div
                  v-if="index > 0"
                  class="bg-gradient-to-r from-blue-50/50 to-indigo-50/30 rounded-xl p-5 border border-blue-100"
                >
                  <h3
                    class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2"
                  >
                    <div
                      class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center"
                    >
                      <svg
                        class="w-3.5 h-3.5 text-blue-600"
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
                    </div>
                    Datos del Documento
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        Tipo de Documento <span class="text-red-500">*</span>
                      </label>
                      <select
                        v-model="visitor.tipo_doc"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            searchValidationErrors.documentType,
                        }"
                        :disabled="isSearching"
                      >
                        <option :value="null" disabled>-- Seleccione --</option>
                        <option value="cedula">Cédula</option>
                        <option value="pasaporte">Pasaporte</option>
                        <option value="otro">Otro / Menor</option>
                      </select>
                      <p
                        v-if="searchValidationErrors.documentType"
                        class="text-red-500 text-xs mt-1"
                      >
                        Es requerido
                      </p>
                      <div v-else class="h-4"></div>
                    </div>

                    <div v-if="visitor.tipo_doc !== 'otro'">
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        Número de Documento <span class="text-red-500">*</span>
                      </label>
                      <input
                        :value="visitor.documento_identidad"
                        @input="handleVisitorDocumentInput($event, visitor)"
                        maxlength="10"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            visitorFormErrors?.documento_identidad,
                        }"
                        :placeholder="
                          visitor.tipo_doc === 'pasaporte'
                            ? 'Ej: A1234567'
                            : 'Ej: 0102030405'
                        "
                        :disabled="
                          visitor.tipo_doc === null ||
                          visitor.tipo_doc === 'otro'
                        "
                      />
                      <p
                        v-if="visitor.tipo_doc === null"
                        class="text-amber-600 text-xs mt-1"
                      >
                        Selecciona primero el tipo de documento
                      </p>
                      <p
                        v-else-if="visitor.tipo_doc === 'otro'"
                        class="text-green-600 text-xs mt-1"
                      >
                        ✓ No es necesario para menores
                      </p>
                      <p
                        v-else-if="visitor.tipo_doc === 'pasaporte'"
                        class="text-gray-500 text-xs mt-1"
                      >
                        Formato: 1 letra + números (Ej: A1234567)
                      </p>
                      <div v-else class="h-4"></div>
                    </div>

                    <div>
                      <button
                        @click="performSearch"
                        :disabled="isSearching"
                        class="w-full px-7 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98] h-11"
                      >
                        <span
                          v-if="!isSearching"
                          class="flex items-center justify-center gap-2"
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
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                          </svg>
                          Buscar
                        </span>
                        <span
                          v-else
                          class="flex items-center justify-center gap-2"
                        >
                          <svg
                            class="animate-spin h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                          >
                            <circle
                              class="opacity-25"
                              cx="12"
                              cy="12"
                              r="10"
                              stroke="currentColor"
                              stroke-width="4"
                            ></circle>
                            <path
                              class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                            ></path>
                          </svg>
                          Buscando...
                        </span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Personal Information -->
                <div class="space-y-4">
                  <h3
                    class="text-sm font-semibold text-gray-700 flex items-center gap-2"
                  >
                    <div
                      class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center"
                    >
                      <svg
                        class="w-3.5 h-3.5 text-green-600"
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
                    </div>
                    Información Personal
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        Nombres <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model="visitor.nombres"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            visitorFormErrors?.nombres,
                        }"
                        placeholder="Ej: Juan Carlos"
                      />
                      <p
                        v-if="visitorFormErrors?.nombres"
                        class="text-red-500 text-xs mt-1"
                      >
                        {{ visitorFormErrors?.nombres }}
                      </p>
                    </div>
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        Apellidos <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model="visitor.apellidos"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            visitorFormErrors?.apellidos,
                        }"
                        placeholder="Ej: Pérez Gómez"
                      />
                      <p
                        v-if="visitorFormErrors?.apellidos"
                        class="text-red-500 text-xs mt-1"
                      >
                        {{ visitorFormErrors?.apellidos }}
                      </p>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        Edad <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model.number="visitor.edad"
                        type="number"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            visitorFormErrors?.edad,
                        }"
                        placeholder="Ej: 25"
                        min="0"
                        max="150"
                      />
                      <p
                        v-if="visitorFormErrors?.edad"
                        class="text-red-500 text-xs mt-1"
                      >
                        {{ visitorFormErrors?.edad }}
                      </p>
                    </div>
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        Sexo <span class="text-red-500">*</span>
                      </label>
                      <select
                        v-model="visitor.sexo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            visitorFormErrors?.sexo,
                        }"
                      >
                        <option :value="null" disabled>-- Seleccione --</option>
                        <option>Masculino</option>
                        <option>Femenino</option>
                      </select>
                      <p
                        v-if="visitorFormErrors?.sexo"
                        class="text-red-500 text-xs mt-1"
                      >
                        {{ visitorFormErrors?.sexo }}
                      </p>
                    </div>
                  </div>

                  <div>
                    <label
                      class="block text-xs font-medium text-gray-600 mb-1.5"
                    >
                      Correo Electrónico
                    </label>
                    <input
                      v-model="visitor.correo"
                      type="email"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                      :class="{
                        'border-2 border-red-500 bg-red-50':
                          visitorFormErrors?.correo,
                      }"
                      placeholder="ejemplo@correo.com"
                    />
                    <p
                      v-if="visitorFormErrors?.correo"
                      class="text-red-500 text-xs mt-1"
                    >
                      {{ visitorFormErrors?.correo }}
                    </p>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        País de Origen <span class="text-red-500">*</span>
                      </label>
                      <select
                        v-model="visitor.pais_origen_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            visitorFormErrors?.pais_origen_id,
                        }"
                      >
                        <option :value="null" disabled>
                          -- Seleccione un país --
                        </option>
                        <option
                          v-for="pais in dataStore.paises"
                          :key="pais.id"
                          :value="pais.id"
                        >
                          {{ pais.nombre }}
                        </option>
                      </select>
                      <p
                        v-if="visitorFormErrors?.pais_origen_id"
                        class="text-red-500 text-xs mt-1"
                      >
                        {{ visitorFormErrors?.pais_origen_id }}
                      </p>
                    </div>
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-600 mb-1.5"
                      >
                        Tipo de Visitante <span class="text-red-500">*</span>
                      </label>
                      <select
                        v-model="visitor.tipo_visitante_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                        :class="{
                          'border-2 border-red-500 bg-red-50':
                            visitorFormErrors?.tipo_visitante_id,
                        }"
                      >
                        <option :value="null" disabled>
                          -- Seleccione un tipo --
                        </option>
                        <option
                          v-for="tipo in dataStore.tiposVisitante"
                          :key="tipo.id"
                          :value="tipo.id"
                        >
                          {{ tipo.nombre }}
                        </option>
                      </select>
                      <p
                        v-if="visitorFormErrors?.tipo_visitante_id"
                        class="text-red-500 text-xs mt-1"
                      >
                        {{ visitorFormErrors?.tipo_visitante_id }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Buttons -->
          <div
            class="flex justify-end gap-3 pt-5 pb-2 border-t border-gray-200"
          >
            <button
              type="button"
              @click="isModalOpen = false"
              class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 hover:shadow-md transition-all duration-200"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98]"
            >
              Guardar Visitante
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<style scoped>
.input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  outline: none;
  transition: all 0.2s ease;
}
.input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.input:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
  white-space: nowrap;
}
.btn-primary:hover:not(:disabled) {
  background-color: #1d4ed8;
}
.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #e5e7eb;
  color: #1f2937;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
}
.btn-secondary:hover {
  background-color: #d1d5db;
}
</style>
