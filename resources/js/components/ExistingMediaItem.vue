<template>
  <div class="existing-media-item float-left border dark:border-gray-700 group mb-4 mr-4 shadow relative cursor-pointer group hover:scale-105 transition-all"
       style="width: 200px;"
       @click.prevent="$emit('select')">
    <div class="overflow-hidden relative">
      <img v-if="'__media_urls__' in item && 'indexView' in item.__media_urls__" :src="item.__media_urls__.indexView" class="block w-full" style="height: 200px; object-fit: contain;"/>
      <div class="absolute top-0 right-0 mt-3 mr-3 hidden group-hover:block">
        <DefaultButton type="button">{{ __('Select') }}</DefaultButton>
      </div>
    </div>
    <div class="p-3">
      <h4 class="truncate h-4 mb-1 font-bold truncate" v-if="'name' in item" :title="item.name">{{ item.name }}</h4>
      <h5 class="truncate text-gray-600 text-xs truncate" v-if="'file_name' in item" :title="item.file_name">{{ item.file_name }}</h5>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      default: function () {
        return {};
      },
      type: Object
    }
  }
}
</script>

<style lang="scss">
.existing-media-item {

  &.float-left {
    float: left;
  }

  &.group:hover .group-hover\:block {
    display: block;
  }

  &.hover\:scale\-105:hover {
    transform: scale(1.05);
  }

  &.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
  }

}
</style>
