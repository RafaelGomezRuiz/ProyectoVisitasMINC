<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 p-4 md:p-6"
  >
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8"
      >
        <div>
          <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">
            Lista de Visitantes
          </h1>

          <p class="text-sm text-gray-500 mt-1">
            Total registrados:
            <span class="font-bold text-blue-600">
              {{ visitorStore.clientesTotal.length }}
            </span>
          </p>
        </div>
      </div>

      <!-- Card -->
      <div
        class="bg-white/90 backdrop-blur rounded-3xl shadow-xl border border-gray-100 overflow-hidden"
      >
        <!-- Tabla -->
        <div class="overflow-x-auto">
          <table class="w-full min-w-[900px]">
            <!-- Head -->
            <thead
              class="bg-gradient-to-r from-gray-100 to-gray-50 border-b border-gray-200"
            >
              <tr>
                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  ID
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Nombre Completo
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Género
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Tipo Documento
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Documento
                </th>

                <th
                  class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                  Última Visita
                </th>
              </tr>
            </thead>

            <!-- Body -->
            <tbody v-if="visitorStore.clientesTotal.length > 0">
              <tr
                v-for="visitor in visitorStore.clientesTotal"
                :key="visitor.id"
                class="border-b border-gray-100 hover:bg-blue-50/40 transition-all duration-200"
              >
                <!-- ID -->
                <td class="px-6 py-5 whitespace-nowrap">
                  <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-bold"
                  >
                    {{ visitor.visitante.id }}
                  </span>
                </td>

                <!-- Nombre -->
                <td class="px-6 py-5">
                  <div class="font-semibold text-gray-800">
                    {{ visitor.visitante.nombres }}
                    {{ visitor.visitante.apellidos }}
                  </div>
                </td>

                <!-- Sexo -->
                <td class="px-6 py-5">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-semibold"
                    :class="
                      visitor.visitante.sexo === 'Masculino'
                        ? 'bg-blue-100 text-blue-700'
                        : 'bg-pink-100 text-pink-700'
                    "
                  >
                    {{ visitor.visitante.sexo }}
                  </span>
                </td>

                <!-- Tipo Documento -->
                <td class="px-6 py-5">
                  <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700"
                  >
                    {{ visitor.visitante.tipo_doc }}
                  </span>
                </td>

                <!-- Documento -->
                <td class="px-6 py-5 text-gray-700 font-medium">
                  {{ visitor.visitante.documento_identidad || "No disponible" }}
                </td>

                <!-- Última Visita -->
                <td class="px-6 py-5 text-gray-600">
                  <div class="flex flex-col">
                    <span class="font-medium">
                      {{ new Date(visitor.created_at).toLocaleDateString() }}
                    </span>

                    <span class="text-xs text-gray-400">
                      {{ new Date(visitor.created_at).toLocaleTimeString() }}
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Empty -->
            <tbody v-else>
              <tr>
                <td
                  colspan="6"
                  class="py-16 text-center text-gray-400 font-medium"
                >
                  No hay visitantes registrados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useVisitorStore } from "../stores/visitorStore.js";

const visitorStore = useVisitorStore();

const fetVisitorStore = async () => {
  await visitorStore.fetchClientesTotal();

  // console.log("ddddddddddd", visitorStore.clientesTotal);
};

onMounted(() => {
  fetVisitorStore();
});
</script>
