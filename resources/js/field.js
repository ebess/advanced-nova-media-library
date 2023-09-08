import IndexField from './components/fields/IndexField';
import DetailField from './components/fields/DetailField';
import FormField from './components/fields/FormField';

Nova.booting((app, store) => {
  app.component('index-advanced-media-library-field', IndexField);
  app.component('detail-advanced-media-library-field', DetailField);
  app.component('form-advanced-media-library-field', FormField);
});
