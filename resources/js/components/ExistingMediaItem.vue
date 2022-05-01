<template>
  <div
    class="rounded-lg border border-gray-200 dark:border-gray-700 relative cursor-pointer group"
    @click.prevent="$emit('select')"
  >
    <div class="overflow-hidden flex-grow relative">
      <img
        v-if="'__media_urls__' in item && 'indexView' in item.__media_urls__"
        :src="item.__media_urls__.indexView"
        class="block w-full"
        style="height: 200px; object-fit: contain"
      />

      <div class="absolute top-0 right-0 mt-3 mr-3 hidden group-hover:block">
        <DefaultButton type="button">
          {{ __('Select') }}
        </DefaultButton>
      </div>
    </div>

    <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
      <div
        class="truncate mb-1 font-bold"
        v-if="'name' in item"
        :title="item.name"
      >
        {{ item.name }}
      </div>
      <div
        class="truncate text-gray-600 text-xs"
        v-if="'file_name' in item"
        :title="item.file_name"
      >
        {{ item.file_name }}
      </div>
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

        type: Object,
      },
    },
  }
</script>


<style>
  .group:hover .group-hover\:block {
    display: block;
  }

  @media(max-width: 768px) {
    .group .group-hover\:block {
      display: block;
    }
  }
</style>
