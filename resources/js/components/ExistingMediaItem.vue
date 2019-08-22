<template>
  <div class="border-40 group px-4 pb-4 mb-4 w-1/4">
    <div class="shadow">
      <button class="overflow-hidden relative w-full" style="padding-top:100%;">
        <template v-if="'__media_urls__' in item && 'indexView' in item.__media_urls__">
          <img :src="item.__media_urls__.indexView" class="absolute block h-full pin-t pin-l w-full" style="object-fit: cover" />
        </template>
        <button type="button" class="absolute form-file-btn btn btn-default btn-primary pin-t pin-r mr-4 mt-4" v-on:click="$emit('select')">Select</button>
      </div>
      <div class="p-3 border-l border-r border-b border-40">
        <template v-if="'name' in item">
          <h4 class="truncate h-4 mb-1">{{ item.name }}</h4>
        </template>
        <template v-if="'file_name' in item">
          <h5 class="truncate text-80">{{ item.file_name }}</h5>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      item: {
        default: function () { return {}; },
        type: Object
      }
    }
  }
</script>
