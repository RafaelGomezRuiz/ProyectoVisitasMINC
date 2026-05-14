import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useReservationStore = defineStore('reservation', () => {
    const pendingReservation = ref(null);
    const reservations = ref([]);
    const pagination = ref({});
    const loading = ref(false);

    async function fetchPendingForVisitor(visitorId) {
        loading.value = true;
        pendingReservation.value = null;
        try {
            const response = await apiClient.get(`/admin/reservas?visitante_id=${visitorId}&estado=pendiente`);
            if (response.data && response.data.id) {
                pendingReservation.value = response.data;
            }
        } catch (error) {
            console.error("Error al buscar reserva pendiente:", error);
        } finally {
            loading.value = false;
        }
    }
    
    /**
     * Obtiene el listado paginado de reservas (con soporte de filtrado por localidad)
     * @param {number} page
     * @param {number|null} locationId
     */
    async function fetchReservations(page = 1, locationId = null) {
        loading.value = true;
        try {
            let url = `/admin/reservas?page=${page}`;
            if (locationId) url += `&localidad_id=${locationId}`;
            const response = await apiClient.get(url);

            // console.log("Respuesta de reservas:", response);
            // La API puede devolver un objeto paginado (Laravel) o directamente un arreglo.
            // Normalizamos ambos casos para que la UI siempre reciba un arreglo en `reservations`.
            const respData = response.data;
            let items = [];
            if (Array.isArray(respData)) {
                items = respData;
                pagination.value = { current_page: 1, last_page: 1, total: items.length };
            } else if (Array.isArray(respData.data)) {
                items = respData.data;
                pagination.value = {
                    current_page: respData.current_page ?? 1,
                    last_page: respData.last_page ?? 1,
                    total: respData.total ?? items.length,
                    links: respData.links ?? [],
                };
            } else if (respData && typeof respData === 'object') {
                // Caso inesperado pero intentamos extraer un arreglo si existe
                items = respData.data || [];
                pagination.value = { current_page: 1, last_page: 1, total: items.length };
            }

            reservations.value = items;
        } catch (error) {
            // console.error('Error al cargar reservas:', error);
        } finally {
            loading.value = false;
        }
    }
    
    async function createReservation(reservationData) {
        try {
            // console.log("valor en reservationDta ", reservationData);
            const response = await apiClient.post('/admin/reservas', reservationData);
            // console.log("valor de la respuesta ", response);
            return { success: true, data: response.data };
        } catch (error) {
            // console.log("valor del error ", error);
            return { success: false, errors: error.response?.data?.errors };
        }
    }

    async function updateReservationStatus(id, estado) {
        try {
            await apiClient.put(`/admin/reservas/${id}`, { estado });
            return true;
        } catch (error) {
            // console.error("Error al actualizar la reserva:", error);
            return false;
        }
    }
    
    function clearPending() {
        pendingReservation.value = null;
    }

    return { 
        pendingReservation, 
        reservations,
        pagination,
        loading, 
        fetchPendingForVisitor, 
        fetchReservations,
        createReservation,
        updateReservationStatus, 
        clearPending 
    };
});

