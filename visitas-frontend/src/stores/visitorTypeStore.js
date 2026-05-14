import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useVisitorTypeStore = defineStore('visitorType', () => {
    // --- STATE ---
    const visitorTypes = ref([]);
    const pagination = ref({});
    const loading = ref(false);
    const error = ref(null);

    // --- ACTIONS ---

    /**
     * Obtiene los tipos de visitante desde la API de forma paginada.
     * @param {number} page - El número de página a solicitar.
     */
    async function fetchVisitorTypes(page = 1) {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.get(`/admin/tipos-visitante?page=${page}`);
            visitorTypes.value = response.data.data;
            // Guardamos los metadatos de paginación para los controles de la UI
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                links: response.data.links,
                total: response.data.total,
            };
        } catch (e) {
            error.value = 'Ocurrió un error al cargar los datos.';
            // console.error(e);
        } finally {
            loading.value = false;
        }
    }

    /**
     * Crea un nuevo tipo de visitante.
     * @param {object} visitorTypeData - Los datos del nuevo tipo.
     */
    async function createVisitorType(visitorTypeData) {
        loading.value = true;
        error.value = null;
        try {
            await apiClient.post('/admin/tipos-visitante', visitorTypeData);
            // Refrescamos la lista para mostrar el nuevo registro
            await fetchVisitorTypes(pagination.value.current_page || 1);
            return true; // Éxito
        } catch (e) {
            error.value = e.response?.data?.message || 'Error al crear el registro.';
            // console.error(e);
            return e.response.data.errors; // Devuelve errores de validación
        } finally {
            loading.value = false;
        }
    }

    /**
     * Actualiza un tipo de visitante existente.
     * @param {object} visitorTypeData - Los datos a actualizar.
     */
    async function updateVisitorType(visitorTypeData) {
        loading.value = true;
        error.value = null;
        try {
            await apiClient.put(`/admin/tipos-visitante/${visitorTypeData.id}`, visitorTypeData);
            await fetchVisitorTypes(pagination.value.current_page || 1);
            return true; // Éxito
        } catch (e) {
            error.value = e.response?.data?.message || 'Error al actualizar.';
            // console.error(e);
            return e.response.data.errors; // Devuelve errores de validación
        } finally {
            loading.value = false;
        }
    }

    /**
     * Elimina un tipo de visitante.
     * @param {number} id - El ID del tipo de visitante a eliminar.
     */
    async function deleteVisitorType(id) {
        loading.value = true;
        error.value = null;
        try {
            await apiClient.delete(`/admin/tipos-visitante/${id}`);
            // Si la página queda vacía después de borrar, vamos a la anterior
            let currentPage = pagination.value.current_page;
            if (visitorTypes.value.length === 1 && currentPage > 1) {
                currentPage--;
            }
            await fetchVisitorTypes(currentPage);
        } catch (e) {
            error.value = 'Error al eliminar el registro.';
            // console.error(e);
        } finally {
            loading.value = false;
        }
    }

    return {
        visitorTypes,
        pagination,
        loading,
        error,
        fetchVisitorTypes,
        createVisitorType,
        updateVisitorType,
        deleteVisitorType,
    };
});
