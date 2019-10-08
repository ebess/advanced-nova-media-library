<template>
  <div class="gallery" :class="{editable}">
    <cropper v-if="field.type === 'media' && editable" :image="cropImage" :must-crop="field.mustCrop" @close="onCloseCroppedImage" @crop-completed="onCroppedImage" :configs="field.croppingConfigs"/>

    <component :is="draggable ? 'draggable' : 'div'" v-if="images.length > 0" v-model="images"
               class="gallery-list clearfix">

      <component :is="singleComponent" v-for="(image, index) in images" class="mb-3 p-3 mr-3"
                    :key="index" :image="image" :field="field" :editable="editable" :removable="editable" @remove="remove(index)"
                    :is-custom-properties-editable="customProperties && customPropertiesFields.length > 0"
                    @edit-custom-properties="customPropertiesImageIndex = index"
                    @crop-start="cropImageQueue.push($event)"
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
      <input :id="`__media__${field.attribute}`" :multiple="multiple" ref="file" class="form-file-input" type="file" @change="add"/>
      <label :for="`__media__${field.attribute}`" class="form-file-btn btn btn-default btn-primary" v-text="label"/>
    </span>

    <p v-if="hasError" class="my-2 text-danger">
      {{ firstError }}
    </p>
  </div>
</template>

<script>
  import SingleMedia from './SingleMedia';
  import SingleFile from './SingleFile';
  import Cropper from './Cropper';
  import CustomProperties from './CustomProperties';
  import Draggable from 'vuedraggable';

  export default {
    components: {
      Draggable,
      SingleMedia,
      SingleFile,
      CustomProperties,
      Cropper,
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
        cropImageQueue: [],
        images: this.value,
        customPropertiesImageIndex: null,
        singleComponent: this.field.type === 'media' ? SingleMedia : SingleFile,
      };
    },
    computed: {
      cropImage() {
        return this.cropImageQueue.length ? this.cropImageQueue[this.cropImageQueue.length - 1] : null
      },
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

        return this.__(`Upload New ${type}`);
      },
      mustCrop() {
        return ('mustCrop' in this.field && this.field.mustCrop);
      }
    },
    watch: {
      images(value, old) {
        this.queueNewImages(value, old)
        this.$emit('input', this.images);
      },
      value(value, old) {
        this.queueNewImages(value, old)
        this.images = value;
      },
    },
    methods: {
      remove(index) {
        this.images = this.images.filter((value, i) => i !== index);
      },

      onCroppedImage(image) {
        let index = this.images.indexOf(this.cropImage);
        this.images[index] = Object.assign(image, { custom_properties: this.cropImage.custom_properties });
      },

      add() {
        Array.from(this.$refs.file.files).forEach(file => {
          file = new File([file], file.name, {type: file.type});

          let reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = () => {
            const fileData = {
              file: file,
              __media_urls__: {
                __original__: reader.result,
                default: reader.result,
              },
              name: file.name,
              file_name: file.name,
            };

            // Copy to trigger watcher to recognize differnece between new and old values
            // https://github.com/vuejs/vue/issues/2164
            let copiedArray = this.images.slice(0)
            if (this.multiple) {
              copiedArray.push(fileData);
            } else {
              copiedArray = [fileData];
            }
            this.images = copiedArray
          };
        });

        // reset file input so if you upload the same image sequentially
        this.$refs.file.value = null;
      },

      onCloseCroppedImage() {
        this.cropImageQueue.pop()
      },

      /**
       * Compares new and old images and will queue anything that needs cropping (if mustCrop)
       *
       * @param value
       * @param old
       */
      queueNewImages(value, old) {
        let aThis = this
        if (this.mustCrop) {
          // For each of the new values (one)
          // If it's not in the old value (two)
          // And it's not already queued (three)
          let toCrop = value.filter(function (one) {
            return !(old.filter(function (two) {
              return one === two
            }).length) && !(aThis.cropImageQueue.filter(function (three) {
              return one === three
            }).length)
          })

          // Added them to the queue
          for (let i in toCrop) {
            this.cropImageQueue.push(toCrop[i])
          }
        }
      }
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
