import { defineStore } from "pinia";
import { ref } from "vue";
import apiClient from "../api/axios";

export const useVisitStore = defineStore("visit", () => {
  // --- STATE ---
  const visits = ref([]);
  const activeVisits = ref([]);
  const dashboardStats = ref({});
  const pagination = ref({});
  const loading = ref(false);
  const error = ref(null);

  // --- ACTIONS ---

  /**
   * Obtiene el historial paginado de visitas, opcionalmente filtrado por localidad.
   * @param {number} page - El número de página a solicitar.
   * @param {number|null} locationId - El ID de la localidad para filtrar.
   */
  async function fetchVisits(page = 1, locationId = null) {
    loading.value = true;
    error.value = null;
    try {
      let url = `/admin/visitas?page=${page}`;
      if (locationId) {
        url += `&localidad_id=${locationId}`;
      }
      const response = await apiClient.get(url);
      visits.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        links: response.data.links,
        total: response.data.total,
      };
    } catch (e) {
      error.value = "Ocurrió un error al cargar el historial de visitas.";
      console.error(e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Obtiene todas las visitas (activas, finalizadas y vencidas) para clasificarlas en el componente.
   * Opcionalmente filtradas por localidad.
   * @param {number|null} locationId - El ID de la localidad para filtrar.
   */
  async function fetchActiveVisits(locationId = null) {
    loading.value = true;
    error.value = null;
    try {
      // NO filtrar por estado=activa aquí; el componente clasificará localmente
      let url = `/admin/visitas`;
      if (locationId) {
        url += `?localidad_id=${locationId}`;
      }
      const response = await apiClient.get(url);
      // La API devuelve paginado, así que extraemos el array de datos
      activeVisits.value = Array.isArray(response.data)
        ? response.data
        : response.data.data || [];
    } catch (e) {
      error.value = "Ocurrió un error al cargar las visitas.";
      console.error(e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Obtiene los datos para el dashboard, opcionalmente filtrado por localidad.
   * @param {number|null} locationId - El ID de la localidad para filtrar.
   */
  async function fetchDashboardStats(locationId = null) {
    loading.value = true;
    error.value = null;
    try {
      let url = `/admin/visitas/stats`;
      if (locationId) {
        url += `?localidad_id=${locationId}`;
      }
      const response = await apiClient.get(url);
      dashboardStats.value = response.data;
    } catch (e) {
      error.value = "Ocurrió un error al cargar las estadísticas.";
      console.error(e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Crea una nueva visita.
   * @param {object} visitData - Los datos de la nueva visita.
   */
  async function createVisit(visitData) {
    try {
      console.log("valor de visit data ", visitData);
      const response = await apiClient.post("/admin/visitas", visitData);
      return { success: true, data: response.data };
    } catch (error) {
      console.log("vakir dek errir ", error);
      return { success: false, errors: error.response?.data?.errors };
    }
  }

  /**
   * Actualiza una visita. Usado principalmente para registrar la salida.
   * @param {object} visitData - Los datos a actualizar.
   */
  async function updateVisit(visitData) {
    try {
      const response = await apiClient.put(
        `/admin/visitas/${visitData.id}`,
        visitData,
      );
      const index = activeVisits.value.findIndex((v) => v.id === visitData.id);
      if (index !== -1) {
        activeVisits.value.splice(index, 1);
      }
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, errors: error.response?.data?.errors };
    }
  }

  /**
   * Elimina una visita del historial.
   * @param {number} id - El ID de la visita a eliminar.
   */
  async function deleteVisit(id) {
    try {
      await apiClient.delete(`/admin/visitas/${id}`);
      await fetchVisits(pagination.value.current_page || 1);
      return { success: true };
    } catch (error) {
      console.error("Error al eliminar la visita:", error);
      error.value = "No se pudo eliminar la visita.";
      return { success: false };
    }
  }

  return {
    visits,
    activeVisits,
    dashboardStats,
    pagination,
    loading,
    error,
    fetchVisits,
    fetchActiveVisits,
    fetchDashboardStats,
    createVisit,
    updateVisit,
    deleteVisit,
  };
});
