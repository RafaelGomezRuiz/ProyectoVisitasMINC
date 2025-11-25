import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import './index.css';

// Importaciones de Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

// Importaciones de PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import 'primeicons/primeicons.css';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';

// Crear la instancia de Vuetify
const vuetify = createVuetify({
  components,
  directives,
});

// Crear la instancia de la aplicación
const app = createApp(App);

// Crear la instancia de Pinia
const pinia = createPinia();

// Usar los plugins
app.use(pinia);
app.use(router);
app.use(vuetify);

// Configuración de PrimeVue
app.use(PrimeVue, {
  theme: {
      preset: Aura,
      options: {
        unstyled: false
      }
  }
});

// Registrar los servicios de PrimeVue
app.use(ToastService);
app.use(ConfirmationService);

// Registrar directivas
app.directive('tooltip', Tooltip);

// Montar la aplicación
app.mount('#app');