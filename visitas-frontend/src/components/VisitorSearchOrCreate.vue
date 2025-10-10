<template>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ title }}</h2>
        
        <!-- Buscador -->
        <div class="relative">
            <label for="search" class="block text-sm font-medium text-gray-700">Buscar por Cédula o Pasaporte</label>
            <input type="text" id="search" v-model="searchQuery" @focus="showResults = true"
                   class="mt-1 block w-full input pr-10" placeholder="Escribe para buscar...">
            <div v-if="visitorStore.loading" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none pt-6">
                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
            <!-- Resultados de Búsqueda -->
            <ul v-if="showResults && visitorStore.searchResults.length > 0" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-auto">
                <li v-for="visitor in visitorStore.searchResults" :key="visitor.id" @click="selectVisitor(visitor)"
                    class="px-4 py-2 cursor-pointer hover:bg-blue-50">
                    <p class="font-semibold">{{ visitor.nombres }} {{ visitor.apellidos }}</p>
                    <p class="text-sm text-gray-600">{{ visitor.documento_identidad }}</p>
                </li>
            </ul>
        </div>
        
        <!-- Botón para crear si no hay resultados -->
        <div v-if="searchQuery && !visitorStore.loading && visitorStore.searchResults.length === 0 && !selected" class="mt-4 text-center">
            <p class="text-gray-600 mb-2">No se encontró el visitante.</p>
            <button @click="openCreateModal" class="btn-secondary">Registrar Nuevo Visitante</button>
        </div>

        <!-- Modal de Creación -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl mx-4">
                <h3 class="text-2xl font-bold mb-4">Registrar Nuevo Visitante</h3>
                <form @submit.prevent="handleCreateVisitor" class="space-y-4">
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold">Tipo de Documento</label>
                            <select v-model="form.tipo_doc" class="input">
                                <option value="cedula">Cédula</option>
                                <option value="pasaporte">Pasaporte</option>
                                <option value="otro">Otro / Menor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold">No. Documento</label>
                            <input v-model="form.documento_identidad" type="text" class="input" :class="{'border-red-500': formErrors.documento_identidad}">
                            <p v-if="formErrors.documento_identidad" class="text-red-500 text-sm mt-1">{{ formErrors.documento_identidad[0] }}</p>
                        </div>
                    </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold">Nombres</label>
                            <input v-model="form.nombres" type="text" class="input" :class="{'border-red-500': formErrors.nombres}">
                             <p v-if="formErrors.nombres" class="text-red-500 text-sm mt-1">{{ formErrors.nombres[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold">Apellidos</label>
                            <input v-model="form.apellidos" type="text" class="input" :class="{'border-red-500': formErrors.apellidos}">
                            <p v-if="formErrors.apellidos" class="text-red-500 text-sm mt-1">{{ formErrors.apellidos[0] }}</p>
                        </div>
                    </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold">Correo Electrónico</label>
                            <input v-model="form.correo" type="email" class="input" :class="{'border-red-500': formErrors.correo}">
                            <p v-if="formErrors.correo" class="text-red-500 text-sm mt-1">{{ formErrors.correo[0] }}</p>
                        </div>
                         <div>
                            <label class="block text-gray-700 font-semibold">Sexo</label>
                             <select v-model="form.sexo" class="input">
                                <option>Masculino</option>
                                <option>Femenino</option>
                                <option>Otro</option>
                            </select>
                        </div>
                    </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold">País de Origen</label>
                            <select v-model="form.pais_origen_id" class="input" :class="{'border-red-500': formErrors.pais_origen_id}">
                                <option :value="null" disabled>-- Seleccione un país --</option>
                                <option v-for="pais in dataStore.paises" :key="pais.id" :value="pais.id">{{ pais.nombre }}</option>
                            </select>
                             <p v-if="formErrors.pais_origen_id" class="text-red-500 text-sm mt-1">{{ formErrors.pais_origen_id[0] }}</p>
                        </div>
                         <div>
                            <label class="block text-gray-700 font-semibold">Tipo de Visitante</label>
                             <select v-model="form.tipo_visitante_id" class="input" :class="{'border-red-500': formErrors.tipo_visitante_id}">
                                <option :value="null" disabled>-- Seleccione un tipo --</option>
                                <option v-for="tipo in dataStore.tiposVisitante" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
                            </select>
                             <p v-if="formErrors.tipo_visitante_id" class="text-red-500 text-sm mt-1">{{ formErrors.tipo_visitante_id[0] }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" @click="isModalOpen = false" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary">Guardar Visitante</button>
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

const props = defineProps({
    title: {
        type: String,
        default: 'Buscar o Registrar Visitante'
    }
});
const emit = defineEmits(['visitor-selected']);

const visitorStore = useVisitorStore();
const dataStore = useDataStore();

const searchQuery = ref('');
const showResults = ref(false);
const isModalOpen = ref(false);
const form = ref({});
const formErrors = ref({});
let searchTimeout = null;
const selected = ref(false); // Flag para saber si ya se seleccionó un visitante

watch(searchQuery, (newVal) => {
    if (selected.value) {
        selected.value = false; // Permite una nueva búsqueda si se borra el campo
        return;
    }
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        visitorStore.searchVisitors(newVal);
    }, 300); // Debounce de 300ms
});

const selectVisitor = (visitor) => {
    emit('visitor-selected', visitor);
    searchQuery.value = `${visitor.nombres} ${visitor.apellidos} (${visitor.documento_identidad})`;
    showResults.value = false;
    selected.value = true; // Marca que ya se seleccionó
    visitorStore.clearSearchResults();
};

const openCreateModal = () => {
    form.value = {
        tipo_doc: 'cedula',
        documento_identidad: searchQuery.value, // Pre-llena con la búsqueda
        nombres: '',
        apellidos: '',
        correo: '',
        sexo: 'Masculino',
        pais_origen_id: null,
        tipo_visitante_id: null,
    };
    formErrors.value = {};
    isModalOpen.value = true;
};

const handleCreateVisitor = async () => {
    formErrors.value = {}; // Limpia errores previos
    const result = await visitorStore.createVisitor(form.value);
    if (result.success) {
        isModalOpen.value = false;
        selectVisitor(result.data); // Emite el nuevo visitante
    } else {
        formErrors.value = result.errors;
    }
};
</script>

<style>
/* Estilos globales para botones e inputs si aún no los tienes */
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

