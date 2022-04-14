<template>
  <div class="border-40 group px-4 pb-4 mb-4 w-1/6">
    <div class="shadow">
      <div class="overflow-hidden relative w-full" style="padding-top:100%;">
        <img v-if="'__media_urls__' in item && 'indexView' in item.__media_urls__" :src="item.__media_urls__.indexView" class="absolute block h-full inset-0 w-full" style="object-fit: cover" />
        <button type="button" class="absolute top-0 right-0 m-4 shadow bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-900 cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring inline-flex items-center justify-center h-9 px-3 shadow bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-900" @click="$emit('select')">{{__('Select')}}</button>
      </div>
      <div class="p-3 border-l border-r border-b border-40">
        <h4 class="truncate h-4 mb-1" v-if="'name' in item">{{ item.name }}</h4>
        <h5 class="truncate text-80" v-if="'file_name' in item">{{ item.file_name }}</h5>
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
