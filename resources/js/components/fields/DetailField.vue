<template>
  <PanelItem :index="index" :field="field" >
    <template #value>
      <gallery :modelValue="field.value" :field="field" :multiple="field.multiple"/>
    </template>
  </PanelItem>
</template>

<script>
  import Gallery from '../Gallery';

  export default {
    components: {
      Gallery,
    },
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
  };
</script>
