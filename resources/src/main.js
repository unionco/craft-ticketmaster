import VenueSearch from './components/VenueSearch';
import EventSearch from './components/EventSearch';
import FormGroup from './components/FormGroup';
import { t } from './filters/translate';

const VueSimpleMapPlugin = {
  install(Vue) {
    Vue.filter('t', t);
    
    Vue.component('venue-search', VenueSearch);
    Vue.component('event-search', EventSearch);
    Vue.component('form-group', FormGroup);
  }
};

if (typeof window !== 'undefined' && window.Vue) {
  window.Vue.use(VueSimpleMapPlugin);
}
