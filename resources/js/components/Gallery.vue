<template>
  <div class="gallery" :class="{editable}" @mouseover="mouseOver = true" @mouseout="mouseOver = false">
    <cropper
      v-if="field.type === 'media' && editable"
      :image="cropImage"
      :must-crop="field.mustCrop"
      @close="onCloseCroppedImage"
      @crop-completed="onCroppedImage"
      :configs="field.croppingConfigs"
    />

    <draggable v-if="images.length > 0" v-model="images" class="gallery-list clearfix">
      <template #item="{element, index}">
        <div style="float:left; margin-right: 1em;">
          <component :is="singleComponent" class="mb-3 p-3 mr-3"
                     :key="index" :image="element" :field="field" :editable="editable" :removable="removable || editable" @remove="remove(index)"
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
        </div>
      </template>
    </draggable>

    <span v-else-if="!editable" class="mr-3">&mdash;</span>

    <br style="clear: both" />

    <span v-if="editable" class="">
      <input :id="`__media__${field.attribute}`" :multiple="multiple" ref="file" class="form-file-input" type="file" :disabled="uploading" @change="add"/>
      <label :for="`__media__${field.attribute}`" class="">
        <DefaultButton type="button" @click.prevent="focusFileInput">
          <template v-if="uploading">{{ __('Uploading') }} ({{ uploadProgress }}%)</template>
          <template v-else>{{ label }}</template>
        </DefaultButton>
      </label>
    </span>

    <help-text v-if="field.type !== 'media'" :show-span="showHelpText" class="mt-2">
      {{ field.helpText }}
    </help-text>

    <p v-if="hasError" class="my-2 text-danger">
      {{ firstError }}
    </p>
  </div>
</template>


<script>
import Vapor from "laravel-vapor";
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
    modelValue: Array,
    editable: Boolean,
    removable: Boolean,
    multiple: Boolean,
    uploadsToVapor: Boolean,
    customProperties: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      mouseOver: false,
      cropImageQueue: [],
      images: this.modelValue || [],
      customPropertiesImageIndex: null,
      singleComponent: this.field.type === 'media' ? SingleMedia : SingleFile,
      uploading: false,
      uploadProgress: 0
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
    modelValue(value) {
      this.images = value || [];
    },
    images(value, old) {
      this.queueNewImages(value, old)
      this.$emit('update:modelValue', this.images);
    },
    value(value, old) {
      this.queueNewImages(value, old)
      this.images = value;
    },
  },
  methods: {
    focusFileInput() {
      this.$refs.file.click();
    },
    remove(index) {
      this.images = this.images.filter((value, i) => i !== index);
    },

    onCroppedImage(image) {
      if (this.uploadsToVapor) {
        image.isVaporUpload = true;
        this.uploadToVapor(image.file).then((imageProperties) => {
          image.vaporFile = imageProperties;
        });
      }
      let index = this.images.indexOf(this.cropImage);
      this.images[index] = Object.assign(image, {custom_properties: this.cropImage.custom_properties});
    },

    add() {
      Array.from(this.$refs.file.files).forEach(file => {
        const blobFile = new Blob([file], {type: file.type});
        blobFile.lastModifiedDate = new Date();
        blobFile.name = file.name;
        this.readFile(blobFile);
      });

      // reset file input so if you upload the same image sequentially
      this.$refs.file.value = null;
    },
    readFile(file) {
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
          ...(this.mustCrop && {mustCrop: true}),
        };

        if (!this.validateFile(fileData.file)) {
          return;
        }

        if (this.uploadsToVapor) {
          // This flag signals to FormField that this is an uploaded file.
          fileData.isVaporUpload = true;
          this.uploadToVapor(file).then((imageProperties) => {
            fileData.vaporFile = imageProperties;
          });
        }

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
    },
    retrieveImageFromClipboardAsBlob(pasteEvent, callback) {
      if (pasteEvent.clipboardData == false) {
        if (typeof (callback) == "function") {
          callback(undefined);
        }
      }
      var items = pasteEvent.clipboardData.items
      if (items == undefined) {
        if (typeof (callback) == "function") {
          callback(undefined)
        }
      }
      for (var i = 0; i < items.length; i++) {
        if (items[i].type.indexOf("image") == -1) continue;
        var blob = items[i].getAsFile()

        if (typeof (callback) == "function") {
          callback(blob)
        }
      }
    },
    validateFile(file) {
      return this.validateFileSize(file) && this.validateFileType(file);
    },
    validateFileSize(file) {
      if (this.field.maxFileSize && ((file.size / 1024) > this.field.maxFileSize)) {
        this.$toasted.error(this.__(
          'Maximum file size is :amount MB',
          {amount: String(this.field.maxFileSize / 1024)}
        ));
        return false;
      }
      return true;
    },
    validateFileType(file) {
      if (!Array.isArray(this.field.allowedFileTypes)) {
        return true;
      }

      for (const type of this.field.allowedFileTypes) {
        if (file.type.startsWith(type)) {
          return true;
        }
      }

      this.$toasted.error(this.__(
        'File type must be: :types',
        {types: this.field.allowedFileTypes.join(' / ')}
      ));
      return false;
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
    },

    /**
     * Start the upload process to Vapor.
     */
    uploadToVapor(file) {
      this.uploading = true;
      this.$emit('file-upload-started');
      return Vapor.store(file, {
        progress: progress => {
          this.uploadProgress = Math.round(progress * 100);
        }
      }).then(response => {
        this.uploading = false;
        this.uploadProgress = 0;
        this.$emit('file-upload-finished');
        return {
          key: response.key,
          uuid: response.uuid,
          filename: file.name,
          mime_type: response.headers['Content-Type'],
          file_size: file.size,
        };
      });
    }
  },
  mounted: function () {
    this.$nextTick(() => {
      window.addEventListener("paste", (e) => {
        if (!this.mouseOver) {
          return;
        }
        this.retrieveImageFromClipboardAsBlob(e, (imageBlob) => {
          if (imageBlob) {
            this.readFile(imageBlob)
          }
        })
      }, false)
    })
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
