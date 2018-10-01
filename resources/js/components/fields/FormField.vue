<template>
  <component :is="field.fullSize ? 'full-width-field' : 'default-field'" :field="field">
    <template slot="field">
      <div :class="{'px-8 pt-6': field.fullSize}">
        <gallery slot="value" v-model="value" editable :field="field" :multiple="field.multiple"
                 :has-error="hasError" :first-error="firstError"/>
      </div>
    </template>
  </component>
</template>

<script>
  import { FormField, HandlesValidationErrors } from 'laravel-nova'
  import Gallery from '../Gallery';
  import FullWidthField from '../FullWidthField';

  export default {
    mixins: [FormField, HandlesValidationErrors],
    components: {
      Gallery,
      FullWidthField,
    },
    props: ['resourceName', 'resourceId', 'field'],

    methods: {
      /*
       * Set the initial, internal value for the field.
       */
      setInitialValue() {
        let value = this.field.value || [];

        if (!this.field.multiple) {
          value = value.slice(0, 1);
        }

        this.value = value;
      },

      /**
       * Fill the given FormData object with the field's internal value.
       */
      fill(formData) {
        const field = this.field.attribute;
        this.value.forEach((file, index) => {
          const isNewImage = !file.id;

          formData.append(`${field}[${index}]`, isNewImage ? file.file : file.id);
        });
      },

      /**
       * Update the field's internal value.
       */
      handleChange(value) {
        this.value = value
      },
    },
  };
</script>
