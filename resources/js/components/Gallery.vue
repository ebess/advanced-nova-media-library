<template>
  <div class="gallery" :class="{editable}">
    <component :is="draggable ? 'draggable' : 'div'" v-if="images.length > 0" v-model="images"
               class="gallery-list clearfix">

      <component :is="singleComponent" v-for="(image, index) in images" class="mb-3 p-3 mr-3"
                    :key="index" :image="image" :field="field" :removable="editable" @remove="remove(index)"
                    :is-custom-properties-editable="customProperties && customPropertiesFields.length > 0"
                    @editCustomProperties="customPropertiesImageIndex = index"
                    />

      <CustomProperties
        v-if="customPropertiesImageIndex !== null"
        v-model="images[customPropertiesImageIndex]"
        :fields="customPropertiesFields"
        @close="customPropertiesImageIndex = null"
      />

    </component>

    <span v-else-if="!editable" class="mr-3">&mdash;</span>

    <span v-if="editable" class="form-file">
      <input :id="field.attribute" :multiple="multiple" ref="file" class="form-file-input" type="file" @change="add"/>
      <label :for="field.attribute" class="form-file-btn btn btn-default btn-primary" v-text="label"/>
    </span>

    <p v-if="hasError" class="my-2 text-danger">
      {{ firstError }}
    </p>
  </div>
</template>

<script>
  import SingleMedia from './SingleMedia';
  import SingleFile from './SingleFile';
  import CustomProperties from './CustomProperties';
  import Draggable from 'vuedraggable';

  export default {
    components: {
      Draggable,
      SingleMedia,
      SingleFile,
      CustomProperties,
    },
    props: {
      hasError: Boolean,
      firstError: String,
      field: Object,
      value: Array,
      editable: Boolean,
      multiple: Boolean,
      customProperties: {
        type: Boolean,
        default: false,
      },
    },
    data() {
      return {
        images: this.value,
        customPropertiesImageIndex: null,
        singleComponent: this.field.type === 'media' ? SingleMedia : SingleFile,
      };
    },
    computed: {
      draggable() {
        return this.editable && this.multiple;
      },
      customPropertiesFields() {
        return this.field.customPropertiesFields || [];
      },
      label() {
        const type = this.field.type === 'media' ? 'Media' : 'File';

        if (this.multiple || this.images.length === 0) {
          return this.__(`Add New ${type}`);
        }

        return this.__(`Replace ${type}`);
      }
    },
    watch: {
      images() {
        this.$emit('input', this.images);
      },
      value(value) {
        this.images = value;
      },
    },
    methods: {
      remove(index) {
        this.images = this.images.filter((value, i) => i !== index);
      },

      add() {
        Array.from(this.$refs.file.files).forEach(file => {
          file = new File([file], file.name, {type: file.type});

          let reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = () => {
            const fileData = {
              file: file,
              full_urls: {
                default: reader.result,
              },
              name: file.name,
              file_name: file.name,
            };

            if (this.multiple) {
              this.images.push(fileData);
            } else {
              this.images = [fileData];
            }
          };
        });
      },
    },
  };
</script>

<style lang="scss">
  .gallery {
    &.editable {
      .gallery-item {
        cursor: grab;
      }
    }
  }
</style>
