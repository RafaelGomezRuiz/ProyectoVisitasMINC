import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useCountryStore = defineStore('country', () => {
    // --- STATE ---
    const countries = ref([]);
    const pagination = ref({});
    const loading = ref(false);
    const error = ref(null);

    // --- ACTIONS ---

    /**
     * Obtiene los países desde la API de forma paginada.
     * @param {number} page - El número de página a solicitar.
     */
    async function fetchCountries(page = 1) {
        loading.value = true;
        error.value = null;
        try {
            // Nota: Laravel puede pluralizar 'pais' a 'paises' en la ruta. Ajusta si es necesario.
            const response = await apiClient.get(`/admin/paises?page=${page}`);
            countries.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                links: response.data.links,
                total: response.data.total,
            };
        } catch (e) {
            error.value = 'Ocurrió un error al cargar los países.';
            // console.error(e);
        } finally {
            loading.value = false;
        }
    }

    /**
     * Crea un nuevo país.
     * @param {object} countryData - Los datos del nuevo país.
     */
    async function createCountry(countryData) {
        loading.value = true;
        error.value = null;
        try {
            await apiClient.post('/admin/paises', countryData);
            await fetchCountries(pagination.value.current_page || 1);
            return true; // Éxito
        } catch (e) {
            error.value = e.response?.data?.message || 'Error al crear el país.';
            // console.error(e);
            return e.response.data.errors; // Devuelve errores de validación
        } finally {
            loading.value = false;
        }
    }

    /**
     * Actualiza un país existente.
     * @param {object} countryData - Los datos a actualizar.
     */
    async function updateCountry(countryData) {
        loading.value = true;
        error.value = null;
        try {
            // La ruta usa el singular 'pai' como variable, pero el endpoint es plural 'paises'
            await apiClient.put(`/admin/paises/${countryData.id}`, countryData);
            await fetchCountries(pagination.value.current_page || 1);
            return true; // Éxito
        } catch (e) {
            error.value = e.response?.data?.message || 'Error al actualizar el país.';
            // console.error(e);
            return e.response.data.errors;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Elimina un país.
     * @param {number} id - El ID del país a eliminar.
     */
    async function deleteCountry(id) {
        loading.value = true;
        error.value = null;
        try {
            await apiClient.delete(`/admin/paises/${id}`);
            let currentPage = pagination.value.current_page;
            if (countries.value.length === 1 && currentPage > 1) {
                currentPage--;
            }
            await fetchCountries(currentPage);
        } catch (e) {
            error.value = 'Error al eliminar el país.';
            // console.error(e);
        } finally {
            loading.value = false;
        }
    }

    return {
        countries,
        pagination,
        loading,
        error,
        fetchCountries,
        createCountry,
        updateCountry,
        deleteCountry,
    };
});
