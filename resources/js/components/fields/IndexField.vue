<template>
  <div v-if="field.type === 'image'">
    <img v-if="field.thumbnailUrl" :src="field.thumbnailUrl" style="object-fit: cover;" class="rounded-full w-8 h-8" />
    <span v-else>&mdash;</span>
  </div>
  <div v-else>
    <span v-if="field.multiple">
      {{ field.value.map(({ file_name }) => file_name).join(', ') }}
    </span>
    <span v-else>{{ field.value[0].file_name }}</span>
  </div>
</template>

<script>
  export default {
    props: ['resourceName', 'field'],
  };
</script>
