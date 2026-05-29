<template>
    <Modal :show="image" @modal-close="onCancel" class="modal-cropper">
      <card class="text-center clipping-container max-w-view bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-4">
          <Cropper
            v-if="image"
            ref="clipper"
            :stencil-props="configs || {}"
            :src="imageUrl"
          />
        </div>
        <div class="bg-30 px-6 py-3 footer rounded-lg">
          <Button
            v-if="!cropAnyway"
            @click.prevent="onCancel"
            variant="link"
            :label="__('Cancel')"
          />

          <Button v-if="!cropAnyway"
            @click.prevent="rotate(-90)"
            variant="action"
            :title="__('Rotate -90')"
            icon="arrow-uturn-left"
          />
          <Button v-if="!cropAnyway"
            @click.prevent="rotate(+90)"
            variant="action"
            :title="__('Rotate +90')"
            icon="arrow-uturn-right"
          />

          <Button
            @click.prevent="onSave"
            variant="solid"
            :label="__('Update')"
          />
        </div>
      </card>
    </Modal>
</template>

<script>
  import Converter from '../converter';
  import { Cropper } from 'vue-advanced-cropper'
  import 'vue-advanced-cropper/dist/style.css';
  import { Button} from "laravel-nova-ui";

  export default {
    components: {
      Cropper,
      Button,
    },
    props: {
      image: Object,
      configs: {
        type: Object,
        default: () => ({}),
      },
      mustCrop: {
        type: Boolean,
        default: false
      }
    },
    data: () => ({
      rotationHistory: 0,
    }),
    computed: {
      mime() {
        // if mime type is set on direct on the file it means it is an already existing image
        // in case  taking mime type from file it means the file has been just uploaded
        return this.image.mime_type || this.image.file.type;
      },
      imageUrl() {
        return this.image ? this.image.__media_urls__.__original__ : null;
      },
      cropAnyway() {
        return (this.image.mustCrop === true) && this.mustCrop;
      },
    },
    watch: {
      image: function (newValue) {
        if (newValue) {
          this.$nextTick(() => {
            this.$refs.updateButton.focus();
          })
        }
        this.reset();
      },
    },
    methods: {
      reset() {
        if(this.$refs.clipper && this.image) {
          this.$refs.clipper.rotate(-this.rotationHistory);
        }
        this.rotationHistory = 0;
      },
      rotate(angle) {
        this.$refs.clipper.rotate(angle);
        this.rotationHistory += angle;
      },
      onSave() {
        const { canvas } = this.$refs.clipper.getResult();
        const base64 = canvas.toDataURL(this.mime);
        const file = Converter(base64, this.mime, this.image.file_name);

        let fileData = {
          file,
          __media_urls__: {
            __original__: base64,
            default: base64,
          },
          name: file.name,
          file_name: file.name,
        };

        this.$emit('crop-completed', fileData);
        this.$emit('close');
      },
      onCancel() {
        if (this.cropAnyway) {
          this.onSave();
        } else {
          this.$emit('close');
        }
      },
    },
  };
</script>

<style lang="scss" scoped>
  .footer {
    display: flex;
    justify-content: space-between;
  }

  .modal-cropper {
    z-index: 400;
  }

  .max-w-view {
    max-width: calc(100vw - 6.5rem);
  }

  @media (min-aspect-ratio: 4/3) {
    .max-w-view {
      max-width: 60vw;
    }
  }

  .fade-enter-active, .fade-leave-active {
    transition: opacity .3s;
  }
  .fade-enter, .fade-leave-to {
    opacity: 0;
  }
</style>
