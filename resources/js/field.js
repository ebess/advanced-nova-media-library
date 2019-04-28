import VuejsClipper from 'vuejs-clipper';

Nova.booting((Vue, router) => {
  Vue.use(VuejsClipper);

  Vue.component('index-advanced-media-library-field', require('./components/fields/IndexField').default);
  Vue.component('detail-advanced-media-library-field', require('./components/fields/DetailField').default);
  Vue.component('form-advanced-media-library-field', require('./components/fields/FormField').default);
});
