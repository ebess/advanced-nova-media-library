<template>
  <component :is="field.fullSize ? 'FullWidthField' : 'DefaultField'" :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <div :class="{'px-8 pt-6': field.fullSize}">
        <gallery slot="value" ref="gallery" v-if="hasSetInitialValue"
                 v-model="value" :editable="!field.readonly" :removable="field.removable" custom-properties :field="field" :multiple="field.multiple" :uploads-to-vapor="field.uploadsToVapor"
                 :has-error="hasError" :first-error="firstError"/>

        <div v-if="field.existingMedia">
          <OutlineButton type="button" class="mt-2" @click.prevent="existingMediaOpen = true">
            {{ openExistingMediaLabel }}
          </OutlineButton>
          <existing-media :open="existingMediaOpen" @close="existingMediaOpen = false" @select="addExistingItem"/>
        </div>
        <help-text
          class="error-text mt-2 text-danger"
          v-if="showErrors && hasError"
        >
          {{ firstError }}
        </help-text>
      </div>
    </template>
  </component>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';
import Gallery from '../Gallery';
import FullWidthField from '../FullWidthField';
import ExistingMedia from '../ExistingMedia';
import objectToFormData from 'object-to-formdata';
import get from 'lodash/get';

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
      existingMediaOpen: false,
    }
  },
  computed: {
    openExistingMediaLabel() {
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
          if (file.isVaporUpload) {
            // In case of Vapor upload, do not send the file's binary data over the wire.
            // The file can already be found in the bucket.
            formData.append(`__media__[${field}][${index}][is_vapor_upload]`, true);
            formData.append(`__media__[${field}][${index}][key]`, file.vaporFile.key);
            formData.append(`__media__[${field}][${index}][uuid]`, file.vaporFile.uuid);
            formData.append(`__media__[${field}][${index}][file_name]`, file.vaporFile.filename);
            formData.append(`__media__[${field}][${index}][file_size]`, file.vaporFile.file_size);
            formData.append(`__media__[${field}][${index}][mime_type]`, file.vaporFile.mime_type);
          } else {
            formData.append(`__media__[${field}][${index}]`, file.file, file.name);
          }
        } else {
          formData.append(`__media__[${field}][${index}]`, file.id);
        }

        objectToFormData({
          [`__media-custom-properties__[${field}][${index}]`]: this.getImageCustomProperties(file)
        }, {}, formData);
      });
    },

    getImageCustomProperties(image) {
      return (this.field.customPropertiesFields || []).reduce((properties, {attribute: property}) => {
        properties[property] = get(image, `custom_properties.${property}`);

        // Fixes checkbox problem
        if (properties[property] === true) {
          properties[property] = 1;
        }

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
      // Copy to trigger watcher to recognize differnece between new and old values
      // https://github.com/vuejs/vue/issues/2164
      let copiedArray = this.value.slice(0)

      if (!this.field.multiple) {
        copiedArray.splice(0, 1);
      }

      copiedArray.push(item);
      this.value = copiedArray;
    }
  },
};
</script>
