<template>
  <v-dialog
    :model-value="modelValue"
    :max-width="maxWidth"
    persistent
    transition="dialog-transition"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card class="confirm-dialog" rounded="lg">
      <v-card-text class="text-center pa-8">
        <div class="icon-wrapper mb-4">
            <template v-if="icon === 'mdi-loading'">
                <v-progress-circular indeterminate :size="48" :color="iconColor" />
            </template>

            <v-icon
                v-else
                :size="iconSize"
                :color="iconColor"
                class="modal-icon"
            >
                {{ icon }}
            </v-icon>
        </div>

        <h2 v-if="title" class="text-h5 font-weight-600 mb-3" :class="titleClass">{{ title }}</h2>
        <p v-if="message" class="text-body-1 mb-4 text-grey-darken-2">{{ message }}</p>
        <slot />
        <div v-if="showClose" class="dialog-actions d-flex gap-3 justify-center">
          <v-btn color="primary" variant="tonal" size="large" @click="close">Cerrar</v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { defineEmits } from 'vue'

const { modelValue, title, message, icon, iconColor, iconSize, showClose, titleClass, maxWidth } = defineProps({
  modelValue: Boolean,
  title: String,
  message: String,
  icon: String,
  iconColor: String,
  iconSize: { type: [String, Number], default: 72 },
  showClose: { type: Boolean, default: false },
  titleClass: { type: String, default: '' },
  maxWidth: { type: [String, Number], default: 480 }
});

const emit = defineEmits(['update:modelValue'])

function close() {
  emit('update:modelValue', false)
}
</script>

<style scoped>
.icon-wrapper {
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}
.modal-icon {
  filter: drop-shadow(0 4px 8px rgba(63, 81, 181, 0.18));
}
.dialog-actions {
  display: flex;
  gap: 1rem;
  margin-top: 0.5rem;
}
.confirm-dialog {
  overflow: hidden;
  animation: fadeInScale 0.5s cubic-bezier(.4,0,.2,1);
}
@keyframes fadeInScale {
  0% { opacity: 0; transform: scale(0.95) translateY(20px);}
  100% { opacity: 1; transform: scale(1) translateY(0);}
}
</style>
