import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useAreaStore = defineStore('area', () => {
    const areas = ref([]);
    const loading = ref(false);

    async function fetchAreasForLocation(locationId) {
        loading.value = true;
        areas.value = [];
        try {
            // Recomiendo adaptar tu API para que soporte este filtro
            const response = await apiClient.get(`/admin/areas?localidad_id=${locationId}`);
            areas.value = response.data.data || response.data; // Flexible por si no está paginado
        } catch (e) {
            // console.error('Error fetching areas:', e);
        } finally {
            loading.value = false;
        }
    }

    async function createArea(areaData) {
        try {
            await apiClient.post('/admin/areas', areaData);
            await fetchAreasForLocation(areaData.localidad_id);
            return true;
        } catch (e) {
            return e.response?.data?.errors || { general: ['Error al crear el área.'] };
        }
    }

    async function updateArea(areaData) {
        try {
            await apiClient.put(`/admin/areas/${areaData.id}`, areaData);
            await fetchAreasForLocation(areaData.localidad_id);
            return true;
        } catch (e) {
            return e.response?.data?.errors || { general: ['Error al actualizar el área.'] };
        }
    }

    async function deleteArea(area) {
        try {
            await apiClient.delete(`/admin/areas/${area.id}`);
            await fetchAreasForLocation(area.localidad_id);
            return true;
        } catch (e) {
            return false;
        }
    }

    return { areas, loading, fetchAreasForLocation, createArea, updateArea, deleteArea };
});
