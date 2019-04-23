<template>
  <component :is="field.fullSize ? 'full-width-field' : 'default-field'" :field="field">
    <template slot="field">
      <div :class="{'px-8 pt-6': field.fullSize}">
        <gallery slot="value" ref="gallery" v-if="hasSetInitialValue"
                 v-model="value" editable custom-properties :field="field" :multiple="field.multiple"
                 :has-error="hasError" :first-error="firstError"/>
      </div>
    </template>
  </component>
</template>

<script>
  import { FormField, HandlesValidationErrors } from 'laravel-nova'
  import Gallery from '../Gallery';
  import FullWidthField from '../FullWidthField';
  import objectToFormData from 'object-to-formdata';

  export default {
    mixins: [FormField, HandlesValidationErrors],
    components: {
      Gallery,
      FullWidthField,
    },
    props: ['resourceName', 'resourceId', 'field'],
    data() {
      return {
        hasSetInitialValue: false,
      }
    },

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
        this.hasSetInitialValue = true;
      },

      /**
       * Fill the given FormData object with the field's internal value.
       */
      fill(formData) {
        const field = this.field.attribute;
        this.value.forEach((file, index) => {
          const isNewImage = !file.id;

          if (isNewImage) {
            formData.append(`${field}[${index}]`, file.file, file.name);
          } else {
            formData.append(`${field}[${index}]`, file.id);
          }

          objectToFormData({
            [`${field}-custom-properties[${index}]`]: this.getImageCustomProperties(file)
          }, {}, formData);
        });
      },

      getImageCustomProperties(image) {
        return (this.field.customPropertiesFields || []).reduce((properties, { attribute: property }) => {
          properties[property] = _.get(image, `custom_properties.${property}`);

          return properties;
        }, {})
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
