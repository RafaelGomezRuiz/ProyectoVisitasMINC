import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useReservationStore = defineStore('reservation', () => {
    const pendingReservation = ref(null);
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
    
    async function createReservation(reservationData) {
        try {
            console.log("valor en reservationDta ", reservationData);
            const response = await apiClient.post('/admin/reservas', reservationData);
            console.log("valor de la respuesta ", response);
            return { success: true, data: response.data };
        } catch (error) {
            console.log("valor del error ", error);
            return { success: false, errors: error.response?.data?.errors };
        }
    }

    async function updateReservationStatus(id, estado) {
        try {
            await apiClient.put(`/admin/reservas/${id}`, { estado });
            return true;
        } catch (error) {
            console.error("Error al actualizar la reserva:", error);
            return false;
        }
    }
    
    function clearPending() {
        pendingReservation.value = null;
    }

    return { 
        pendingReservation, 
        loading, 
        fetchPendingForVisitor, 
        createReservation,
        updateReservationStatus, 
        clearPending 
    };
});

