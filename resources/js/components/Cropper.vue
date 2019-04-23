<template>
  <transition name="fade">
    <modal v-if="image" @modal-close="$emit('close')">
      <card class="text-center clipping-container m-2 bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-4">
          <clipper-basic class="clipper" ref="clipper" bg-color="rgba(0, 0, 0, 0)" :rotate="rotate" :src="imageUrl"/>
        </div>

        <div class="bg-30 px-6 py-3 footer rounded-lg">
          <button type="button" class="btn btn-link text-80 font-normal h-9 px-3" @click="$emit('close')">{{__('Cancel')}}</button>

          <input class="input-range ml-4 mr-4" type="range" min="0" max="360" step="30" v-model="rotate">

          <button type="button" class="btn btn-default btn-primary" @click="onSave">{{__('Update')}}</button>
        </div>
      </card>
    </modal>
  </transition>
</template>

<script>
  import Converter from '../converter';

  export default {
    props: {
      image: Object,
    },
    data: () => ({
      rotate: 0,
    }),
    computed: {
      mime() {
        // if mime type is set on direct on the file it means it is an already existing image
        // in case  taking mime type from file it means the file has been just uploaded
        return this.image.mime_type || this.image.file.type;
      },
      imageUrl() {
        return this.image ? this.image.full_urls.__original__ : null;
      },
    },
    watch: {
      image() {
        this.reset();
      },
    },
    methods: {
      reset() {
        this.rotate = 0;
      },
      onSave() {
        const base64 = this.$refs.clipper.clip().toDataURL(this.mime);
        const file = Converter(base64, this.mime, this.image.file_name);

        let fileData = {
          file,
          full_urls: {
            __original__: base64,
            default: base64,
          },
          name: file.name,
          file_name: file.name,
        };

        this.$emit('crop-completed', fileData);
        this.$emit('close');
      },
    },
  };
</script>

<style lang="scss" scoped>
  .input-range {
    width: 100%;
    max-width: 300px;
  }

  .footer {
    display: flex;
    justify-content: space-between;
  }

  .fade-enter-active, .fade-leave-active {
    transition: opacity .3s;
  }
  .fade-enter, .fade-leave-to {
    opacity: 0;
  }
</style>
