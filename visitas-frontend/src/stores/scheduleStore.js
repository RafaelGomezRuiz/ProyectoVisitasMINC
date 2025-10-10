import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '../api/axios';

export const useScheduleStore = defineStore('schedule', () => {
    const schedules = ref([]);
    const loading = ref(false);

    async function fetchSchedulesForLocation(locationId) {
        loading.value = true;
        schedules.value = [];
        try {
            const response = await apiClient.get(`/admin/horarios?localidad_id=${locationId}`);
            schedules.value = response.data.data || response.data;
        } catch (e) {
            console.error('Error fetching schedules:', e);
        } finally {
            loading.value = false;
        }
    }

    async function createSchedule(scheduleData) {
        try {
            await apiClient.post('/admin/horarios', scheduleData);
            await fetchSchedulesForLocation(scheduleData.localidad_id);
            return true;
        } catch (e) {
            return e.response?.data?.errors || { general: ['Error al crear el horario.'] };
        }
    }

    async function updateSchedule(scheduleData) {
        try {
            await apiClient.put(`/admin/horarios/${scheduleData.id}`, scheduleData);
            await fetchSchedulesForLocation(scheduleData.localidad_id);
            return true;
        } catch (e) {
            return e.response?.data?.errors || { general: ['Error al actualizar el horario.'] };
        }
    }
    
    async function deleteSchedule(schedule) {
        try {
            await apiClient.delete(`/admin/horarios/${schedule.id}`);
            await fetchSchedulesForLocation(schedule.localidad_id);
            return true;
        } catch (e) {
            return false;
        }
    }

    return { schedules, loading, fetchSchedulesForLocation, createSchedule, updateSchedule, deleteSchedule };
});
