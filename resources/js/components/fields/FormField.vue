<template>
  <component :is="field.fullSize ? 'full-width-field' : 'default-field'" :field="field" full-width-content>
    <template slot="field">
      <div :class="{'px-8 pt-6': field.fullSize}">
        <gallery slot="value" ref="gallery" v-if="hasSetInitialValue"
                 v-model="value" editable custom-properties :field="field" :multiple="field.multiple"
                 :has-error="hasError" :first-error="firstError"/>
        <button type="button" class="form-file-btn btn btn-default btn-primary mt-2" v-on:click="existingMediaOpen = true">{{  openExistingMediaLabel }}</button>
        <existing-media
          :open="existingMediaOpen"
          v-on:close="existingMediaOpen = false"
          v-on:select="(item) => { addExistingItem(item) }"
        ></existing-media>
      </div>
    </template>
  </component>
</template>

<script>
  import { FormField, HandlesValidationErrors } from 'laravel-nova'
  import Gallery from '../Gallery';
  import FullWidthField from '../FullWidthField';
  import ExistingMedia from '../ExistingMedia';
  import objectToFormData from 'object-to-formdata';

  export default {
    mixins: [FormField, HandlesValidationErrors],
    components: {
      Gallery,
      FullWidthField,
      ExistingMedia
    },
    props: ['resourceName', 'resourceId', 'field'],
    data() {
      return {
        hasSetInitialValue: false,
        existingMediaOpen: false
      }
    },
    computed: {
        openExistingMediaLabel () {
        const type = this.field.type === 'media' ? 'Media' : 'File';

        if (this.field.multiple || this.value.length === 0) {
          return this.__(`Add Existing ${type}`);
        }

        return this.__(`Use Existing ${type}`);
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
            formData.append(`__media__[${field}][${index}]`, file.file, file.name);
          } else {
            formData.append(`__media__[${field}][${index}]`, file.id);
          }

          objectToFormData({
            [`__media-custom-properties__[${field}][${index}]`]: this.getImageCustomProperties(file)
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

      addExistingItem(item) {
        if (!this.field.multiple) {
          this.value.splice(0, 1);
        }

        this.value.push(item);
      }
    },
  };
</script>
