import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useDataStore = defineStore('data', () => {
    const paises = ref([]);
    const tiposVisitante = ref([]);
    const areas = ref([]);

    async function fetchAll() {
        try {
            // Hacemos las llamadas en paralelo para más eficiencia
            const [paisesRes, tiposRes, areasRes] = await Promise.all([
                apiClient.get('/admin/paises?all=true'), // Asume que tu API puede devolver todos los registros
                apiClient.get('/admin/tipos-visitante?all=true'),
                apiClient.get('/admin/areas?all=true'),
            ]);
            paises.value = paisesRes.data.data || paisesRes.data;
            tiposVisitante.value = tiposRes.data.data || tiposRes.data;
            areas.value = areasRes.data.data || areasRes.data;
        } catch (error) {
            console.error("Error cargando datos maestros:", error);
        }
    }

    return { paises, tiposVisitante, areas, fetchAll };
});
