<template>
  <div class="gallery" :class="{editable}">
    <component :is="draggable ? 'draggable' : 'div'" v-if="images.length > 0" v-model="images"
               class="gallery-list clearfix">
      <component :is="singleComponent" v-for="(image, index) in images" class="mb-3 p-3 mr-3"
                    :key="index" :image="image" :thumbnail="field.thumbnailUrl" :removable="editable"
                    @remove="remove(index)"/>
    </component>

    <span v-else-if="!editable" class="mr-3">&mdash;</span>

    <span v-if="editable" class="form-file">
      <input :id="field.name" :multiple="multiple" ref="file" class="form-file-input" type="file" @change="add"/>
      <label :for="field.name" class="form-file-btn btn btn-default btn-primary" v-text="label"/>
    </span>

    <p v-if="hasError" class="my-2 text-danger">
      {{ firstError }}
    </p>
  </div>
</template>

<script>
  import SingleImage from './SingleImage';
  import SingleFile from './SingleFile';
  import Draggable from 'vuedraggable';

  export default {
    components: {
      Draggable,
    },
    props: {
      hasError: Boolean,
      firstError: String,
      field: Object,
      value: Array,
      editable: Boolean,
      multiple: Boolean,
    },
    data() {
      return {
        images: this.value,
        singleComponent: this.field.type === 'image' ? SingleImage : SingleFile,
      };
    },
    computed: {
      draggable() {
        return this.editable && this.multiple;
      },
      label() {
        const type = this.field.type === 'image' ? 'Image' : 'File';

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
