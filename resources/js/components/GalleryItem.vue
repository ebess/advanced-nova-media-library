<template>
  <div class="gallery-item">
    <slot/>
  </div>
</template>

<script>
export default {
  props: ['image', 'thumbnail', 'removable'],
};
</script>

<style lang="scss">
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
    border-radius: $border-radius;
    background-color: rgb(232, 245, 251, 0.2);

    .gallery-item-info {
      display: flex;
      background-color: rgb(232, 245, 251, 0.2);
      border-radius: $border-radius;
      z-index: 10;
    }
  }
}
</style>
