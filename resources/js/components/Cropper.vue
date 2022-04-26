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
          <OutlineButton v-if="!cropAnyway" type="button"  @click="onCancel">{{__('Cancel')}}</OutlineButton>

          <button v-if="!cropAnyway" type="button" class="btn btn-link text-80 font-normal h-9 px-3" @click.prevent="rotate(-90)" :title="__('Rotate -90')">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="fill-current"><path d="M17.026 22.957c10.957-11.421-2.326-20.865-10.384-13.309l2.464 2.352h-9.106v-8.947l2.232 2.229c14.794-13.203 31.51 7.051 14.794 17.675z"/></svg>
          </button>
          <button v-if="!cropAnyway" type="button" class="btn btn-link text-80 font-normal h-9 px-3" @click.prevent="rotate(+90)" :title="__('Rotate +90')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="fill-current"><path d="M6.974 22.957c-10.957-11.421 2.326-20.865 10.384-13.309l-2.464 2.352h9.106v-8.947l-2.232 2.229c-14.794-13.203-31.51 7.051-14.794 17.675z"/></svg>
          </button>

          <DefaultButton type="button" class="btn btn-default btn-primary" @click="onSave" ref="updateButton">{{__('Update')}}</DefaultButton>
        </div>
      </card>
    </Modal>
</template>

<script>
  import Converter from '../converter';
  import { Cropper } from 'vue-advanced-cropper'
  import 'vue-advanced-cropper/dist/style.css';

  export default {
    components: {
      Cropper
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
