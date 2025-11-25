import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useVisitorStore = defineStore('visitor', () => {
    const searchResults = ref([]);
    const loading = ref(false);

    async function searchVisitors(documento) {
        if (!documento || documento.length < 3) {
            searchResults.value = [];
            return;
        }
        loading.value = true;
        try {
            const response = await apiClient.get(`/admin/visitantes/buscar?documento_identidad=${documento}`);
            
            // **Corrección:** Nos aseguramos de que la respuesta sea un array 
            // y filtramos cualquier valor que no sea un objeto válido (como null).
            // Esto hace que el store sea más robusto y previene errores de renderizado.
            if (Array.isArray(response.data)) {
                searchResults.value = response.data.filter(item => typeof item === 'object' && item !== null);
            } else {
                // Si la respuesta no es un array, evitamos el error reseteando el estado.
                searchResults.value = [];
            }
        } catch (error) {
            console.error("Error buscando visitantes:", error);
            searchResults.value = [];
        } finally {
            loading.value = false;
        }
    }

    async function createVisitor(visitorData) {
        try {
            const response = await apiClient.post('/admin/visitantes', visitorData);
            return { success: true, data: response.data };
        } catch (error) {
            console.log("error ", error);
            return { success: false, errors: error.response?.data?.errors };
        }
    }
    
    function clearSearchResults() {
        searchResults.value = [];
    }

    return { searchResults, loading, searchVisitors, createVisitor, clearSearchResults };
});

