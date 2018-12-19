<template>
  <gallery-item class="gallery-item-file">
    <div class="gallery-item-info">
      <a class="download mr-2" :href="image.full_urls.default" target="_blank">
        <icon type="search" view-box="0 0 20 20" width="16" height="16" />
      </a>
      <span class="label">
        {{ image.file_name }}
      </span>
      <a v-if="removable" class="delete ml-2" href="#" @click.prevent="$emit('remove')">
        <icon type="delete" view-box="0 0 20 20" width="16" height="16" />
      </a>
    </div>
  </gallery-item>
</template>

<script>
  import GalleryItem from './GalleryItem';

  export default {
    props: ['image', 'removable'],
    components: {
      GalleryItem,
    },
    computed() {
      console.log(this.image);
    }
  };
</script>

<style lang="scss">
  .gallery-item-file {
    &.gallery-item {
      width: 100%;

      .gallery-item-info {
        display: flex;

        .label {
          flex-grow: 1;
        }

        .download {
          color: var(--primary-dark);
        }

        .delete {
          align-self: flex-end;
          color: var(--danger);
        }
      }
    }
  }
</style>
