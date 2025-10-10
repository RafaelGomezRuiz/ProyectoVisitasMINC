<template>
    <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-4xl mx-auto">
            <!-- Encabezado -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Gestión de Tipos de Visitante</h1>
                <button @click="openCreateModal"
                    class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Crear Nuevo
                </button>
            </div>

            <!-- Tabla de datos -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                 <div v-if="store.loading" class="p-8 text-center text-gray-500">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2">Cargando...</p>
                 </div>
                 <div v-else-if="store.error" class="p-8 text-center text-red-500 bg-red-50 rounded-lg">
                    <p class="font-semibold">¡Error!</p>
                    <p>{{ store.error }}</p>
                 </div>
                 <div v-else>
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm">Nombre</th>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm">Extranjero</th>
                                <th class="py-3 px-6 font-bold text-gray-600 uppercase text-sm text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody v-if="store.visitorTypes.length > 0">
                            <tr v-for="visitorType in store.visitorTypes" :key="visitorType.id"
                                class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-4 px-6">{{ visitorType.nombre }}</td>
                                <td class="py-4 px-6">
                                    <span :class="visitorType.es_extranjero ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                                          class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ visitorType.es_extranjero ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center space-x-2">
                                    <button @click="openEditModal(visitorType)"
                                        class="text-blue-500 hover:text-blue-700 p-1 rounded-full transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                                    </button>
                                    <button @click="confirmDelete(visitorType.id)"
                                        class="text-red-500 hover:text-red-700 p-1 rounded-full transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                           <tr>
                              <td colspan="3" class="text-center py-8 text-gray-500">No se encontraron registros.</td>
                           </tr>
                        </tbody>
                    </table>
                 </div>
            </div>
            
            <!-- Paginación -->
            <div v-if="!store.loading && store.pagination.total > 10" class="mt-6 flex justify-between items-center">
                <p class="text-sm text-gray-600">
                    Página {{ store.pagination.current_page }} de {{ store.pagination.last_page }}
                </p>
                <div class="flex space-x-1">
                    <button v-for="link in store.pagination.links" :key="link.label"
                        @click="changePage(link.url)"
                        :disabled="!link.url || link.active"
                        v-html="link.label"
                        class="px-3 py-1.5 text-sm rounded-md transition-colors"
                        :class="{
                            'bg-blue-600 text-white': link.active,
                            'bg-white text-gray-700 hover:bg-gray-200': !link.active && link.url,
                            'text-gray-400 cursor-not-allowed bg-gray-100': !link.url
                        }">
                    </button>
                </div>
            </div>

        </div>

        <!-- Modal para Crear/Editar -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                <h2 class="text-2xl font-bold mb-4">{{ modalTitle }}</h2>
                <form @submit.prevent="handleSave">
                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 font-semibold mb-2">Nombre</label>
                        <input type="text" id="nombre" v-model="form.nombre"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{'border-red-500': formErrors.nombre}"
                            required>
                        <p v-if="formErrors.nombre" class="text-red-500 text-sm mt-1">{{ formErrors.nombre[0] }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.es_extranjero" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Es Extranjero</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="closeModal"
                            class="bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">Cancelar</button>
                        <button type="submit"
                            class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useVisitorTypeStore } from '../stores/visitorTypeStore';

const store = useVisitorTypeStore();

// --- Estado local del componente ---
const isModalOpen = ref(false);
const isEditing = ref(false);
const form = ref({
    id: null,
    nombre: '',
    es_extranjero: false,
});
const formErrors = ref({});


// --- Ciclo de vida ---
onMounted(() => {
    store.fetchVisitorTypes();
});

// --- Propiedades computadas ---
const modalTitle = computed(() => {
    return isEditing.value ? 'Editar Tipo de Visitante' : 'Crear Nuevo Tipo de Visitante';
});

// --- Métodos para el modal ---
const resetForm = () => {
    form.value = {
        id: null,
        nombre: '',
        es_extranjero: false,
    };
    formErrors.value = {};
    isEditing.value = false;
};

const openCreateModal = () => {
    resetForm();
    isModalOpen.value = true;
};

const openEditModal = (visitorType) => {
    resetForm();
    isEditing.value = true;
    form.value = { ...visitorType, es_extranjero: !!visitorType.es_extranjero }; // Copia los datos y asegura boolean
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleSave = async () => {
    formErrors.value = {};
    let result;
    if (isEditing.value) {
        result = await store.updateVisitorType(form.value);
    } else {
        result = await store.createVisitorType(form.value);
    }

    if (result === true) {
        closeModal();
        // Aquí podrías agregar una notificación de éxito (ej. con vue-toastification)
    } else {
        formErrors.value = result;
    }
};

// --- Métodos para la tabla ---
const confirmDelete = (id) => {
    if (window.confirm('¿Estás seguro de que deseas eliminar este registro?')) {
        store.deleteVisitorType(id);
    }
};

const changePage = (url) => {
    if (!url) return;
    // Extraemos el número de página de la URL para pasarlo a la acción del store
    const pageNumber = new URL(url).searchParams.get('page');
    store.fetchVisitorTypes(pageNumber);
};
</script>

<style scoped>
/* Estilos adicionales si fueran necesarios */
</style>
