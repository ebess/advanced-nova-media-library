<template>
  <gallery-item class="gallery-item-file">
    <div class="gallery-item-info">
      <a class="download mr-2" :href="image.__media_urls__.__original__" target="_blank">
        <icon type="search" view-box="0 0 20 20" width="16" height="16" />
      </a>
      <a v-if="downloadUrl" class="download mr-2" :href="downloadUrl">
        <icon type="download" view-box="0 0 20 22" width="16" height="16" />
      </a>
      <span class="label">
        {{ image.file_name }}
      </span>
      <a v-if="isCustomPropertiesEditable" class="edit edit--file ml-2" href="#" @click.prevent="$emit('edit-custom-properties')">
        <icon type="pencil" view-box="0 0 20 20" width="16" height="16" />
      </a>
      <a v-if="removable" class="delete ml-2" href="#" @click.prevent="$emit('remove')">
        <icon type="trash" view-box="0 0 20 20" width="16" height="16" />
      </a>
    </div>
  </gallery-item>
</template>

<script>
  import GalleryItem from './GalleryItem';

  export default {
    props: ['image', 'removable', 'isCustomPropertiesEditable'],
    components: {
      GalleryItem,
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
