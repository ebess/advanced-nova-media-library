<template>
  <gallery-item class="gallery-item-file">
    <div class="gallery-item-info">
      <a class="download mr-2" :href="image.__media_urls__.__original__" target="_blank">
        <Icon name="magnifying-glass"/>
      </a>
      <a v-if="downloadUrl" class="download mr-2" :href="downloadUrl">
        <Icon name="arrow-down-tray"/>
      </a>
      <span class="label">
        {{ image.file_name }}
      </span>
      <a v-if="isCustomPropertiesEditable" class="edit edit--file ml-2 mr-2" href="#" @click.prevent="$emit('edit-custom-properties')">
        <Icon name="pencil"/>
      </a>
      <a v-if="removable" class="delete ml-2" href="#" @click.prevent="$emit('remove')">
        <Icon name="trash"/>
      </a>
    </div>
  </gallery-item>
</template>

<script>
  import { Icon } from 'laravel-nova-ui'
  import GalleryItem from './GalleryItem';

  export default {
    props: ['image', 'removable', 'isCustomPropertiesEditable'],
    components: {
      GalleryItem,
      Icon,
    },
    computed: {
      downloadUrl() {
        return this.image.id ? `/nova-vendor/ebess/advanced-nova-media-library/download/${this.image.id}?uuid=${this.image.uuid}` : null;
      },
    }
  };
</script>

<style lang="scss">
    .gallery .edit.edit--file {
        position: relative;
        top: auto;
        right: auto;
    }

  .gallery-item-file {
    &.gallery-item {
      width: 100%;

      .gallery-item-info {
        display: flex;

        .label {
          flex-grow: 1;
        }

        .download {
          color: rgb(var(--colors-primary-500));
        }

        .delete {
          align-self: flex-end;
          color: rgb(var(--colors-red-500));
        }
      }
    }
  }
</style>
