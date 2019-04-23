import VuejsClipper from 'vuejs-clipper';

Nova.booting((Vue, router) => {
  Vue.use(VuejsClipper);

  Vue.component('index-advanced-media-library-field', require('./components/fields/IndexField'));
  Vue.component('detail-advanced-media-library-field', require('./components/fields/DetailField'));
  Vue.component('form-advanced-media-library-field', require('./components/fields/FormField'));
});
