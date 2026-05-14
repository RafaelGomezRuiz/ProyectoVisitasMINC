import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useLocationStore = defineStore('location', () => {
    const locations = ref([]);
    const pagination = ref({});
    const loading = ref(false);
    const error = ref(null);

    async function fetchLocations(page = 1) {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.get(`/admin/localidades?page=${page}`);
            locations.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                links: response.data.links,
                total: response.data.total,
            };
        } catch (e) {
            error.value = 'Ocurrió un error al cargar las localidades.';
            // console.error(e);
        } finally {
            loading.value = false;
        }
    }

    async function createLocation(locationData) {
        try {
            await apiClient.post('/admin/localidades', locationData);
            await fetchLocations(pagination.value.current_page || 1);
            return true;
        } catch (e) {
            return e.response?.data?.errors || { general: ['Error al crear la localidad.'] };
        }
    }

    async function updateLocation(locationData) {
        try {
            await apiClient.put(`/admin/localidades/${locationData.id}`, locationData);
            await fetchLocations(pagination.value.current_page || 1);
            return true;
        } catch (e) {
            return e.response?.data?.errors || { general: ['Error al actualizar la localidad.'] };
        }
    }

    async function deleteLocation(id) {
        try {
            await apiClient.delete(`/admin/localidades/${id}`);
            await fetchLocations(pagination.value.current_page || 1);
            return true;
        } catch (e) {
            error.value = 'Error al eliminar la localidad.';
            return false;
        }
    }

    return { locations, pagination, loading, error, fetchLocations, createLocation, updateLocation, deleteLocation };
});
