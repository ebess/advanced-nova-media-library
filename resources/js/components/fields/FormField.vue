<template>
  <component :is="field.fullSize ? 'FullWidthField' : 'DefaultField'" :field="field" :errors="errors"
             :show-help-text="showHelpText">
    <template #field>
      <div :class="{'px-8 pt-6': field.fullSize}">
        <a
          v-if="field.translatable"
          class="inline-block font-bold cursor-pointer mr-2 animate-text-color select-none"
          :class="{ 'text-60': localeKey !== currentLocale, 'text-primary': localeKey === currentLocale }"
          :key="`a-${localeKey}`"
          v-for="(locale, localeKey) in field.locales"
          @click="changeTab(localeKey)"
        >
          {{ locale }}
        </a>
        <div class="flex flex-row items-center">
          <gallery slot="value" ref="gallery" v-if="hasSetInitialValue"
                   :value="displayValue" @input="handleChange" :editable="!field.readonly" :removable="field.removable"
                   custom-properties :field="field" :multiple="field.multiple" :uploads-to-vapor="field.uploadsToVapor"
                   :has-error="hasError" :first-error="firstError"/>
        </div>

        <div v-if="field.existingMedia">
          <OutlineButton type="button" class="mt-2" @click.prevent="existingMediaOpen = true">
            {{ openExistingMediaLabel }}
          </OutlineButton>
          <existing-media :open="existingMediaOpen" @close="existingMediaOpen = false" @select="addExistingItem"/>
        </div>
        <help-text
          class="error-text mt-2 text-danger"
          v-if="hasError"
        >
          {{ firstError }}
        </help-text>
      </div>
    </template>
  </component>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'
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
      existingMediaOpen: false,
      locales: Object.keys(this.field?.locales || {}),
      currentLocale: null,
    }
  },
  mounted() {
    this.currentLocale = this.locales[0] || null;
    Nova.$on('localeChanged', locale => {
      if (this.currentLocale !== locale) {
        this.changeTab(locale, true);
      }
    });
  },
  computed: {
    openExistingMediaLabel() {
      const type = this.field.type === 'media' ? 'Media' : 'File';

      if (this.field.multiple || this.value.length === 0) {
        return this.__(`Add Existing ${type}`);
      }

      return this.__(`Use Existing ${type}`);
    },
    displayValue() {
      return this.field.translatable ?
        this.value.filter(i => _.get(i, 'custom_properties.locale') === this.currentLocale)
        : this.value
    },
  },
  methods: {
    changeTab(locale, dontEmit) {
      if (this.currentLocale !== locale) {
        if (!dontEmit) {
          Nova.$emit('localeChanged', locale);
        }

        this.currentLocale = locale;
      }
    },
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      let value = this.field.value || [];

      if (!this.field.multiple && !this.field.translatable) {
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
      let customProperties = (this.field.customPropertiesFields || []).map(p => p.attribute)
      if(this.field.translatable) {
        customProperties.push('locale')
      }

      return customProperties.reduce((properties, property) => {
        properties[property] = _.get(image, `custom_properties.${property}`);

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
      if(this.displayValue === value) {
        return
      }
      if(! this.field.translatable) {
        this.value = value
        return
      }

      this.value = this.value.filter(i => _.get(i, 'custom_properties.locale') !== this.currentLocale)
        .concat(value.map(i => _.set(i, 'custom_properties.locale', this.currentLocale)))
    },

    addExistingItem(item) {
      let copiedArray = this.value.slice(0)

      if (!this.field.multiple && !this.field.translatable) {
        copiedArray.splice(0, 1);
      }

      copiedArray.push(_.set(item, 'custom_properties.locale', this.currentLocale));
      this.value = copiedArray
    }
  },
};
</script>
