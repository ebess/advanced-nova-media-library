<template>
  <div class="gallery-item">
    <div class="gallery-item-info p-3">
      <a v-if="removable" class="delete" href="#" @click.prevent="$emit('remove')">
        <icon type="delete" view-box="0 0 20 20" width="16" height="16" />
      </a>
      <a v-if="isCustomPropertiesEditable" class="edit" href="#" @click.prevent="$emit('editCustomProperties')">
        <icon type="edit" view-box="0 0 20 20" width="16" height="16" />
      </a>
      <a class="preview" :href="image.full_urls.default" target="_blank">
        <icon type="search" view-box="0 0 20 20" width="30" height="30" />
      </a>
    </div>
    <img :src="src" :alt="image.name" class="gallery-image">
  </div>
</template>

<script>
  export default {
    props: ['image', 'thumbnail', 'removable', 'isCustomPropertiesEditable'],
    computed: {
      src() {
        // Return desired image conversion on view if it exists
        if (this.image.id && this.conversionOnView && this.image.full_urls[this.conversionOnView]) {
          return this.image.full_urls[this.conversionOnView];
        }
        // Return thumnail if conversion exists
        if (this.image.id && this.thumbnail && this.image.full_urls[this.thumbnail]) {
          return this.image.full_urls[this.thumbnail];
        }

        return this.image.full_urls.default;
      },
    },
  };
</script>

<style lang="scss">
  .gallery-item-info {
    display: flex;
    align-items: center;
    justify-content: center;

    .preview {
      color: var(--black);
    }

    .delete {
      position: absolute;
      right: 10px;
      top: 10px;
      color: var(--danger);
    }

    .edit {
      position: absolute;
      right: 30px;
      top: 10px;
      color: var(--info);
    }
  }
</style>
