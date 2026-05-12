<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 p-4 sm:p-6 lg:p-8 font-sans"
  >
    <div class="mx-auto max-w-7xl">
      <!-- Encabezado -->
      <div
        class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8"
      >
        <div>
          <h1
            class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-800 tracking-tight"
          >
            Gestión de Países
          </h1>

          <p class="text-sm sm:text-base text-gray-500 mt-2">
            Administra los países registrados
          </p>
        </div>

        <button
          @click="openCreateModal"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-5 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.02]"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
              clip-rule="evenodd"
            />
          </svg>

          Crear Nuevo
        </button>
      </div>

      <!-- Tabla -->
      <div
        class="bg-white/90 backdrop-blur rounded-3xl shadow-xl border border-gray-100 overflow-hidden"
      >
        <!-- Loading -->
        <div v-if="store.loading" class="p-8 sm:p-10 text-center text-gray-500">
          <svg
            class="animate-spin h-10 w-10 text-blue-600 mx-auto"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>

            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>

          <p class="mt-4 text-sm font-medium">Cargando países...</p>
        </div>

        <!-- Error -->
        <div
          v-else-if="store.error"
          class="m-4 sm:m-6 p-5 rounded-2xl border border-red-200 bg-red-50 text-center"
        >
          <p class="text-red-700 font-bold text-lg">¡Error!</p>

          <p class="text-red-500 mt-1">
            {{ store.error }}
          </p>
        </div>

        <!-- Tabla -->
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[500px] text-left">
            <thead
              class="bg-gradient-to-r from-gray-100 to-gray-50 border-b border-gray-200"
            >
              <tr>
                <th
                  class="py-4 px-4 sm:px-6 text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Nombre
                </th>

                <th
                  class="py-4 px-4 sm:px-6 text-xs font-bold uppercase tracking-wider text-center text-gray-600"
                >
                  Acciones
                </th>
              </tr>
            </thead>

            <tbody v-if="store.countries.length > 0">
              <tr
                v-for="country in store.countries"
                :key="country.id"
                class="border-b border-gray-100 hover:bg-blue-50/40 transition-all duration-200"
              >
                <td
                  class="py-4 sm:py-5 px-4 sm:px-6 font-medium text-gray-800 text-sm sm:text-base"
                >
                  {{ country.nombre }}
                </td>

                <td class="py-4 sm:py-5 px-4 sm:px-6">
                  <div class="flex items-center justify-center gap-2 sm:gap-3">
                    <!-- Edit -->
                    <button
                      @click="openEditModal(country)"
                      class="p-2 sm:p-2.5 rounded-xl bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 sm:h-5 sm:w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"
                        />

                        <path
                          fill-rule="evenodd"
                          d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>

                    <!-- Delete -->
                    <button
                      @click="confirmDelete(country.id)"
                      class="p-2 sm:p-2.5 rounded-xl bg-red-100 text-red-600 hover:bg-red-600 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 sm:h-5 sm:w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Empty -->
            <tbody v-else>
              <tr>
                <td
                  colspan="2"
                  class="py-14 text-center text-gray-400 font-medium text-sm sm:text-base"
                >
                  No se encontraron países.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Paginación -->
      <div
        v-if="!store.loading && store.pagination.total > 10"
        class="mt-8 flex flex-col lg:flex-row items-center justify-between gap-5"
      >
        <p class="text-sm text-gray-600 font-medium text-center lg:text-left">
          Página
          <span class="font-bold text-blue-600">
            {{ store.pagination.current_page }}
          </span>
          de
          <span class="font-bold">
            {{ store.pagination.last_page }}
          </span>
        </p>

        <div class="flex flex-wrap items-center justify-center gap-2">
          <button
            v-for="link in store.pagination.links"
            :key="link.label"
            @click="changePage(link.url)"
            :disabled="!link.url || link.active"
            v-html="link.label"
            class="min-w-[40px] sm:min-w-[45px] px-3 sm:px-4 py-2 text-sm rounded-xl font-medium transition-all duration-200"
            :class="{
              'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md':
                link.active,

              'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:shadow':
                !link.active && link.url,

              'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url,
            }"
          ></button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div
        class="w-full max-w-md sm:max-w-lg bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-300"
      >
        <!-- Header -->
        <div
          class="px-5 sm:px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50"
        >
          <h2 class="text-xl sm:text-2xl font-bold text-gray-800">
            {{ modalTitle }}
          </h2>

          <p class="text-sm text-gray-500 mt-1">
            Complete la información requerida
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSave" class="p-5 sm:p-6 space-y-5">
          <div>
            <label
              for="nombre"
              class="block text-sm font-semibold text-gray-700 mb-2"
            >
              Nombre del País
            </label>

            <input
              type="text"
              id="nombre"
              v-model="form.nombre"
              required
              class="w-full px-4 py-3 rounded-2xl border border-gray-300 bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all duration-200 text-sm sm:text-base"
              :class="{ 'border-red-500 bg-red-50': formErrors.nombre }"
            />

            <p v-if="formErrors.nombre" class="text-red-500 text-sm mt-2">
              {{ formErrors.nombre[0] }}
            </p>
          </div>

          <!-- Buttons -->
          <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
            <button
              type="button"
              @click="closeModal"
              class="w-full sm:w-auto px-5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-200"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="w-full sm:w-auto px-5 py-2.5 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 hover:scale-[1.02]"
            >
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useCountryStore } from "../stores/countryStore";

const store = useCountryStore();

// --- Estado local del componente ---
const isModalOpen = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  nombre: "",
});
const formErrors = ref({});

// --- Ciclo de vida ---
onMounted(() => {
  store.fetchCountries();
});

// --- Propiedades computadas ---
const modalTitle = computed(() => {
  return isEditing.value ? "Editar País" : "Crear Nuevo País";
});

// --- Métodos para el modal ---
const resetForm = () => {
  form.value = {
    id: null,
    nombre: "",
  };
  formErrors.value = {};
  isEditing.value = false;
};

const openCreateModal = () => {
  resetForm();
  isModalOpen.value = true;
};

const openEditModal = (country) => {
  resetForm();
  isEditing.value = true;
  form.value = { ...country }; // Copia los datos del país al formulario
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const handleSave = async () => {
  formErrors.value = {};
  let result;
  if (isEditing.value) {
    result = await store.updateCountry(form.value);
  } else {
    result = await store.createCountry(form.value);
  }

  if (result === true) {
    closeModal();
  } else {
    formErrors.value = result;
  }
};

// --- Métodos para la tabla ---
const confirmDelete = (id) => {
  if (window.confirm("¿Estás seguro de que deseas eliminar este país?")) {
    store.deleteCountry(id);
  }
};

const changePage = (url) => {
  if (!url) return;
  const pageNumber = new URL(url).searchParams.get("page");
  store.fetchCountries(pageNumber);
};
</script>
