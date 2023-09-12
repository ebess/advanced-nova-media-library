<template>
  <div class="advanced-nova-media-library">
    <PanelItem :index="index" :field="field" >
      <template #value>
        <gallery :modelValue="field.value" :field="field" :multiple="field.multiple"/>
      </template>
    </PanelItem>
  </div>
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
