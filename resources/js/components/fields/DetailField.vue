<template>
  <PanelItem :index="index" :field="field" >
    <template #field>
      <gallery slot="value" :value="field.value" :field="field" :multiple="field.multiple"/>
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
