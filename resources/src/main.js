// import VueMaterial from 'vue-material';
// import App from './App.vue';
// import Settings from './Settings.vue';
import VenueSearch from './components/VenueSearch';
import EventSearch from './components/EventSearch';
import EventForm from './EventForm.vue';
import { t } from './filters/translate';
// import 'vue-material/dist/vue-material.min.css';
// import 'vue-material/dist/theme/default.css';

const VueSimpleMapPlugin = {
  install(Vue) {
    Vue.filter('t', t);
    // Vue.use(VueMaterial);
    // Vue.component('ticketmaster', App);
    // Vue.component('ticketmaster-settings', Settings);
    Vue.component('tm-eventform', EventForm);
    Vue.component('venue-search', VenueSearch);
    Vue.component('event-search', EventSearch);
  }
};

if (typeof window !== 'undefined' && window.Vue) {
  window.Vue.use(VueSimpleMapPlugin);
}
