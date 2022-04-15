<template>
  <panel-item :field="field">
    <template #value>
      <gallery :value="field.value" :field="field" :multiple="field.multiple"/>
    </template>
  </panel-item>
</template>

<script>
  import Gallery from '../Gallery';

  export default {
    components: {
      Gallery,
    },
    props: ['resource', 'resourceName', 'resourceId', 'field'],
  };
</script>
