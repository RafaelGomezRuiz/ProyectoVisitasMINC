import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';
import { useAuthStore } from './authStore';

export const useDataStore = defineStore('data', () => {
    const paises = ref([]);
    const tiposVisitante = ref([]);
    const areas = ref([]);

    async function fetchAll() {
        try {
            const authStore = useAuthStore();
            
            // Construir parámetros para filtrar áreas por localidad del usuario
            // Solo si el usuario tiene rol AgenteDeVisitas
            let areasUrl = '/admin/areas?all=true';
            if (authStore.user?.localidad_id && authStore.hasRole('AgenteDeVisitas')) {
                areasUrl += `&localidad_id=${authStore.user.localidad_id}`;
            }

            // Hacemos las llamadas en paralelo para más eficiencia
            const [paisesRes, tiposRes, areasRes] = await Promise.all([
                apiClient.get('/admin/paises?all=true'), // Asume que tu API puede devolver todos los registros
                apiClient.get('/admin/tipos-visitante?all=true'),
                apiClient.get(areasUrl), // Áreas filtradas por localidad_id si es AgenteDeVisitas
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
