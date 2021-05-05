import VuejsClipper from 'vuejs-clipper';
import IndexField from './components/fields/IndexField';
import DetailField from './components/fields/DetailField';
import FormField from './components/fields/FormField';
import VueRx from "vue-rx";

Nova.booting((Vue, router) => {
  Vue.use(VueRx);
  Vue.use(VuejsClipper);

  Vue.component('index-advanced-media-library-field', IndexField);
  Vue.component('detail-advanced-media-library-field', DetailField);
  Vue.component('form-advanced-media-library-field', FormField);
});
