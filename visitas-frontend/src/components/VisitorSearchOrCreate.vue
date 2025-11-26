<template>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <!-- BaseModals: loading / success / error -->
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
            @update:model-value="(v) => modalState.success = v"
        />

        <BaseModal
            :model-value="modalState.error"
            :title="modalState.errorTitle"
            :message="modalState.errorMessage"
            icon="mdi-alert-circle"
            icon-color="error"
            :show-close="true"
            @update:model-value="(v) => modalState.error = v"
        />
        <!-- Búsqueda de Visitante -->
        <div class="space-y-4">
            <h3 class="text-xl font-bold text-gray-800">Buscar o Registrar Visitante sa</h3>
            
            <!-- Fila de búsqueda: Tipo de Documento, Número, Botón Buscar -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="doc-type" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Documento <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="doc-type"
                        v-model="documentType"
                        class="input"
                        :disabled="isSearching"
                    >
                        <option :value="null" disabled>-- Seleccione --</option>
                        <option value="cedula">Cédula</option>
                        <option value="pasaporte">Pasaporte</option>
                        <option value="otro">Otro / Menor</option>
                    </select>
                </div>

                <div>
                    <label for="doc-number" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Documento <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="doc-number"
                        v-model="documentNumber"
                        type="text"
                        class="input"
                        placeholder="Ej: 0102030405"
                        :disabled="isSearching"
                    />
                </div>

                <button
                    @click="performSearch"
                    :disabled="!documentType || !documentNumber || isSearching"
                    class="btn-primary h-10"
                >
                    <span v-if="!isSearching">Buscar</span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Buscando...
                    </span>
                </button>

                <button
                    v-if="selected"
                    @click="resetSearch"
                    class="btn-secondary h-10"
                >
                    Cambiar
                </button>
            </div>

            <!-- Mensajes de estado -->
            <div v-if="searchError" class="text-red-600 text-sm mt-2">{{ searchError }}</div>
            <div v-if="selected" class="text-green-600 text-sm mt-2">
                ✓ Visitante seleccionado: <strong>{{ selectedVisitor.nombres }} {{ selectedVisitor.apellidos }}</strong>
            </div>

            <!-- Modal de selección de resultados -->
            <div
                v-if="showResultsModal && visitorStore.searchResults.length > 1"
                class="fixed inset-0 bg-black bg-opacity-30 flex justify-center items-center z-50"
            >
                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                    <h4 class="text-lg font-bold mb-4">Seleccionar Visitante</h4>
                    <p class="text-gray-600 mb-4">Se encontraron {{ visitorStore.searchResults.length }} resultados. Por favor selecciona uno:</p>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <button
                            v-for="visitor in visitorStore.searchResults"
                            :key="visitor.id"
                            @click="selectVisitor(visitor)"
                            class="w-full text-left p-3 border border-gray-300 rounded-lg hover:bg-blue-50 transition"
                        >
                            <p class="font-semibold">{{ visitor.nombres }} {{ visitor.apellidos }}</p>
                            <p class="text-sm text-gray-600">{{ visitor.documento_identidad }}</p>
                        </button>
                    </div>
                    <button
                        @click="showResultsModal = false"
                        class="w-full mt-4 btn-secondary"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal para crear visitante -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0  bg-opacity-30 backdrop-blur-sm flex justify-center items-center z-50"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl mx-4">
                <h3 class="text-2xl font-bold mb-4">Registrar Nuevo Visitante</h3>
                <form @submit.prevent="handleCreateVisitor" class="space-y-4">
                    <!-- Tipo de Documento (removido, ya está arriba) -->

                    <!-- Documento y Nombres -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- <div>
                            <label class="block text-gray-700 font-semibold">
                                No. Documento <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="visitorForm.documento_identidad"
                                type="text"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.documento_identidad }"
                            />
                            <p
                                v-if="visitorFormErrors?.documento_identidad"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.documento_identidad }}
                            </p>
                        </div> -->
                        <div>
                            <label class="block text-gray-700 font-semibold">
                                Nombres <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="visitorForm.nombres"
                                type="text"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.nombres }"
                            />
                            <p
                                v-if="visitorFormErrors?.nombres"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.nombres }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold">
                                Apellidos <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="visitorForm.apellidos"
                                type="text"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.apellidos }"
                            />
                            <p
                                v-if="visitorFormErrors?.apellidos"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.apellidos }}
                            </p>
                        </div>
                    </div>

                    <!-- Apellidos y Edad -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div>
                            <label class="block text-gray-700 font-semibold">
                                Edad del Visitante <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="visitorForm.edad"
                                type="number"
                                class="input"
                                placeholder="Ej: 25"
                                :class="{ 'border-red-500': visitorFormErrors?.edad }"
                            />
                            <p
                                v-if="visitorFormErrors?.edad"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.edad }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold">
                                Sexo
                            </label>
                            <select
                                v-model="visitorForm.sexo"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.sexo }"
                            >
                                <option :value="null" disabled>-- Seleccione --</option>
                                <option>Masculino</option>
                                <option>Femenino</option>
                            </select>
                            <p
                                v-if="visitorFormErrors?.sexo"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.sexo }}
                            </p>
                        </div>
                    </div>

                    <!-- Correo y Sexo -->
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold">
                                Correo Electrónico
                            </label>
                            <input
                                v-model="visitorForm.correo"
                                type="email"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.correo }"
                            />
                            <p
                                v-if="visitorFormErrors?.correo"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.correo }}
                            </p>
                        </div>
                    </div>

                    <!-- País y Tipo de Visitante -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold">
                                País de Origen <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="visitorForm.pais_origen_id"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.pais_origen_id }"
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
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.pais_origen_id }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold">
                                Tipo de Visitante <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="visitorForm.tipo_visitante_id"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.tipo_visitante_id }"
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
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.tipo_visitante_id }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="btn-secondary"
                        >
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            Guardar Visitante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useVisitorStore } from '../stores/visitorStore';
import { useDataStore } from '../stores/dataStore';
import BaseModal from './BaseModal.vue';

const emit = defineEmits(['visitor-selected']);
const visitorStore = useVisitorStore();
const dataStore = useDataStore();

// Modal state for create visitor feedback
const modalState = ref({
    loading: false,
    success: false,
    error: false,
    errorTitle: '',
    errorMessage: ''
});

// Búsqueda
const documentType = ref(null);
const documentNumber = ref('');
const isSearching = ref(false);
const searchError = ref('');
const selected = ref(false);
const selectedVisitor = ref(null);
const showResultsModal = ref(false);
const showRegisterButton = ref(false);

// Modal de crear visitante
const isModalOpen = ref(false);
const visitorForm = ref({
    tipo_doc: null,
    documento_identidad: '',
    nombres: '',
    apellidos: '',
    edad: null,
    correo: '',
    sexo: null,
    pais_origen_id: null,
    tipo_visitante_id: null,
});
const visitorFormErrors = ref({});

const performSearch = async () => {
    searchError.value = '';
    showRegisterButton.value = false;

    if (!documentType.value || !documentNumber.value) {
        searchError.value = 'Por favor completa todos los campos';
        return;
    }

    isSearching.value = true;
    try {
        const searchQuery = documentNumber.value;
        await visitorStore.searchVisitors(searchQuery);
        console.log("visitantre ", visitorStore.searchResults);
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
        console.error('Error en búsqueda:', error);
        searchError.value = 'Error al buscar el visitante';
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
    emit('visitor-selected', visitor);
};

const resetSearch = () => {
    selected.value = false;
    selectedVisitor.value = null;
    documentType.value = null;
    documentNumber.value = '';
    searchError.value = '';
    showRegisterButton.value = false;
    visitorStore.clearSearchResults();
};

const openCreateModal = () => {
    visitorForm.value = {
        tipo_doc: documentType.value,
        documento_identidad: documentNumber.value,
        nombres: '',
        apellidos: '',
        edad: null,
        correo: '',
        sexo: null,
        pais_origen_id: null,
        tipo_visitante_id: null,
    };
    visitorFormErrors.value = {};
    isModalOpen.value = true;
};

const validateEmail = (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
};

const handleCreateVisitor = async () => {
    visitorFormErrors.value = {};
    const errors = {};

    if (!visitorForm.value.documento_identidad) errors.documento_identidad = 'El número de documento es obligatorio.';
    if (!visitorForm.value.nombres) errors.nombres = 'El nombre es obligatorio.';
    if (!visitorForm.value.apellidos) errors.apellidos = 'El apellido es obligatorio.';
    if (visitorForm.value.edad === null || visitorForm.value.edad === '') errors.edad = 'La edad es obligatoria.';
    if (!visitorForm.value.pais_origen_id) errors.pais_origen_id = 'Debe seleccionar un país.';
    if (!visitorForm.value.tipo_visitante_id) errors.tipo_visitante_id = 'Debe seleccionar un tipo de visitante.';
    if (visitorForm.value.correo && !validateEmail(visitorForm.value.correo))
        errors.correo = 'El correo electrónico no tiene un formato válido.';

    if (Object.keys(errors).length > 0) {
        visitorFormErrors.value = errors;
        return;
    }

    // Close the create modal immediately and show loading modal
    isModalOpen.value = false;
    modalState.value.loading = true;

    try {
        const result = await visitorStore.createVisitor(visitorForm.value);
        modalState.value.loading = false;

        if (result.success) {
            // Show success modal and emit selection so parent proceeds
            modalState.value.success = true;
            selectVisitor(result.data);
        } else {
            // Validation errors from API — reopen the create form so user can fix
            visitorFormErrors.value = result.errors || {};
            isModalOpen.value = true;
            modalState.value.error = true;
            modalState.value.errorTitle = 'Error al crear visitante';
            modalState.value.errorMessage = 'Por favor revisa los campos e intenta nuevamente.';
        }
    } catch (err) {
        modalState.value.loading = false;
        console.error('Error creando visitante:', err);
        visitorFormErrors.value = {};
        // Reopen form so user can retry
        isModalOpen.value = true;
        modalState.value.error = true;
        modalState.value.errorTitle = 'Error';
        modalState.value.errorMessage = 'Ocurrió un error al crear el visitante.';
    }
};
</script>

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
