<template>
  <panel-item :field="field">
    <gallery slot="value" :value="field.value" :field="field" :multiple="field.multiple"/>
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
