<template>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <!-- Buscador -->
        <div class="relative">
            <label for="search" class="block text-sm font-medium text-gray-700">
                Ingresar Cédula o Pasaporte
            </label>
            <input
                type="text"
                id="search"
                v-model="searchQuery"
                @focus="showResults = true"
                class="mt-1 block w-full input pr-10"
                placeholder="Escribe para buscar..."
            />
            <div
                v-if="visitorStore.loading"
                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none pt-6"
            >
                <svg
                    class="animate-spin h-5 w-5 text-gray-400"
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
            </div>

            <!-- Resultados -->
            <ul
                v-if="showResults && visitorStore.searchResults.length > 0"
                class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-auto"
            >
                <li
                    v-for="visitor in visitorStore.searchResults"
                    :key="visitor.id"
                    @click="selectVisitor(visitor)"
                    class="px-4 py-2 cursor-pointer hover:bg-blue-50"
                >
                    <p class="font-semibold">
                        {{ visitor.nombres }} {{ visitor.apellidos }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ visitor.documento_identidad }}
                    </p>
                </li>
            </ul>
        </div>

        <!-- Botón para crear -->
        <div
            v-if="searchQuery && !visitorStore.loading && visitorStore.searchResults.length === 0 && !selected"
            class="mt-4 text-center"
        >
            <p class="text-gray-600 mb-2">No se encontró el visitante.</p>
            <button @click="openCreateModal" class="btn-secondary">
                Registrar Nuevo Visitante
            </button>
        </div>

        <!-- Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-lg flex justify-center items-center z-50"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl mx-4">
                <h3 class="text-2xl font-bold mb-4">Registrar Nuevo Visitante</h3>
                <form @submit.prevent="handleCreateVisitor" class="space-y-4">
                    <!-- Tipo de Documento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold">
                                Tipo de Documento <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="visitorForm.tipo_doc"
                                class="input"
                                :class="{ 'border-red-500': visitorFormErrors?.tipo_doc }"
                            >
                                <option :value="null" disabled>-- Seleccione --</option>
                                <option value="cedula">Cédula</option>
                                <option value="pasaporte">Pasaporte</option>
                                <option value="otro">Otro / Menor</option>
                            </select>
                            <p
                                v-if="visitorFormErrors?.tipo_doc"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ visitorFormErrors?.tipo_doc }}
                            </p>
                        </div>
                        <div>
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
                        </div>
                    </div>

                    <!-- Nombres y Apellidos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <!-- Correo y Sexo -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
import { ref, watch } from 'vue';
import { useVisitorStore } from '../stores/visitorStore';
import { useDataStore } from '../stores/dataStore';

const emit = defineEmits(['visitor-selected']);
const visitorStore = useVisitorStore();
const dataStore = useDataStore();

const searchQuery = ref('');
const showResults = ref(false);
const isModalOpen = ref(false);
// initialize the visitor form with all expected fields to avoid undefined accesses
const visitorForm = ref({
    tipo_doc: null,
    documento_identidad: '',
    nombres: '',
    apellidos: '',
    correo: '',
    sexo: null,
    pais_origen_id: null,
    tipo_visitante_id: null,
});
const visitorFormErrors = ref({
    tipo_doc: null,
    documento_identidad: '',
    nombres: '',
    apellidos: '',
    correo: '',
    sexo: null,
    pais_origen_id: null,
    tipo_visitante_id: null,
});
let searchTimeout = null;
const selected = ref(false);

watch(searchQuery, (newVal) => {
    if (selected.value) {
        selected.value = false;
        return;
    }
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        visitorStore.searchVisitors(newVal);
    }, 300);
});

const selectVisitor = (visitor) => {
    emit('visitor-selected', visitor);
    searchQuery.value = `${visitor.nombres} ${visitor.apellidos} (${visitor.documento_identidad})`;
    showResults.value = false;
    selected.value = true;
    visitorStore.clearSearchResults();
};

const openCreateModal = () => {
    Object.assign(visitorForm.value, {
        tipo_doc: 'cedula',
        documento_identidad: searchQuery.value,
        nombres: '',
        apellidos: '',
        correo: '',
        sexo: 'Masculino',
        pais_origen_id: null,
        tipo_visitante_id: null,
    });
    Object.keys(visitorFormErrors.value).forEach(k => visitorFormErrors.value[k] = null);
    isModalOpen.value = true;
};


// Validar formato de correo
const validateEmail = (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
};

// Validación del formulario
const handleCreateVisitor = async () => {
    visitorFormErrors.value = {};
    const errors = {};

    if (!visitorForm.value.tipo_doc) errors.tipo_doc = 'El tipo de documento es obligatorio.';
    if (!visitorForm.value.documento_identidad) errors.documento_identidad = 'El número de documento es obligatorio.';
    if (!visitorForm.value.nombres) errors.nombres = 'El nombre es obligatorio.';
    if (!visitorForm.value.apellidos) errors.apellidos = 'El apellido es obligatorio.';
    if (!visitorForm.value.sexo) errors.sexo = 'El sexo es obligatorio.';
    if (!visitorForm.value.pais_origen_id) errors.pais_origen_id = 'Debe seleccionar un país.';
    if (!visitorForm.value.tipo_visitante_id) errors.tipo_visitante_id = 'Debe seleccionar un tipo de visitante.';
    if (visitorForm.value.correo && !validateEmail(visitorForm.value.correo))
        errors.correo = 'El correo electrónico no tiene un formato válido.';

    if (Object.keys(errors).length > 0) {
        visitorFormErrors.value = errors;
        return;
    }

    console.log("🔵 Antes de llamar createVisitor con:", visitorForm.value);
    const result = await visitorStore.createVisitor(visitorForm.value);
    console.log("🟢 Resultado de createVisitor:", result);
    if (result.success) {
        isModalOpen.value = false;
        selectVisitor(result.data);
    } else {
        visitorFormErrors.value = result.errors;
    }
};
</script>

<style>
.input {
    @apply w-full px-3 py-2 border ring-1 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors;
}
.btn-primary {
    @apply bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors;
}
.btn-secondary {
    @apply bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors;
}
</style>
