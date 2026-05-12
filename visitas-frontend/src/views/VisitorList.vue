<template>
  <h1>hola</h1>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Genero</th>
        <th>Documento</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="visitor in visitorStore.clientesTotal" :key="visitor.id">
        <td>{{ visitor.id }}</td>
        <td>{{ visitor.nombres }} {{ visitor.apellidos }}</td>
        <td>{{ visitor.tipo_documento }}</td>

        <td>{{ visitor.documento_identidad }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
import { onMounted } from "vue";
import { useVisitorStore } from "../stores/visitorStore.js";

const visitorStore = useVisitorStore();

const fetVisitorStore = async () => {
  await visitorStore.fetchClientesTotal();

  console.log(visitorStore.clientesTotal);
};

onMounted(() => {
  fetVisitorStore();
});
</script>
