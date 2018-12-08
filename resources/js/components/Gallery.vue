<template>
  <div class="gallery" :class="{editable}">

    <component :is="draggable ? 'draggable' : 'div'" v-if="images.length > 0" v-model="images"
               class="gallery-list clearfix">
      <single-image v-for="(image, index) in images" class="mb-3 p-3 mr-3"
                    :key="index" :image="image" :thumbnail="field.thumbnailUrl" :removable="editable"
                    @remove="remove(index)"/>
    </component>
    <span v-else-if="!editable" class="mr-3">&mdash;</span>

    <span v-if="editable" class="form-file">
      <input :id="field.name" :multiple="multiple" ref="file" class="form-file-input" type="file" @change="add"/>
      <label :for="field.name" class="form-file-btn btn btn-default btn-primary">
          {{ label }}
      </label>
    </span>

    <p v-if="hasError" class="my-2 text-danger">
      {{ firstError }}
    </p>
  </div>
</template>

<script>
  import SingleImage from './SingleImage';
  import Draggable from 'vuedraggable';

  export default {
    components: {
      Draggable,
      SingleImage,
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
      };
    },
    computed: {
      draggable() {
        return this.editable && this.multiple;
      },
      label() {
        if (this.multiple) {
          return this.field.type === 'image' ? this.__('Add New Image') : this.__('Add New File');
        }

        return this.field.type === 'image' ? this.__('Replace Image') : this.__('Replace File')
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
  $bg-color: #e8f5fb;
  $item-max-size: 150px;
  $border-radius: 10px;

  .gallery {
    &.editable {
      .gallery-item {
        cursor: grab;
      }
    }

    .gallery-item {
      float: left;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      width: $item-max-size;
      height: $item-max-size;
      border-radius: $border-radius;
      background-color: $bg-color;

      &:hover .gallery-item-info {
        display: flex;
      }

      .gallery-item-info {
        display: none;
        flex-direction: column;
        background-color: transparentize($bg-color, .2);
        border-radius: $border-radius;
        position: absolute;
        z-index: 10;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
      }
    }

    .gallery-image {
      object-fit: contain;
      display: block;
      max-height: 100%;
      border-radius: $border-radius;
    }
  }
</style>
