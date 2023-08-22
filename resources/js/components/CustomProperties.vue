<template>
  <transition name="fade">
    <CustomPropertiesModal
      :fields="filledFields"
      @close="handleClose"
      @update="handleUpdate"
    />
  </transition>
</template>

<script>
import CustomPropertiesModal from './CustomPropertiesModal'
import tap from 'lodash/tap'
import get from 'lodash/get'
import set from 'lodash/set'
import clone from 'lodash/clone'

export default {
  props: {
    modelValue: {
      type: Object,
      required: true,
    },
    fields: {
      type: Array,
      required: true,
    },
  },

  components: {
    CustomPropertiesModal,
  },

  data() {
    return {
      image: clone(this.modelValue),
    }
  },

  computed: {
    filledFields() {
      return JSON.parse(JSON.stringify(this.fields)).map(field => tap(field, field => {
        field.value = this.getProperty(field.attribute)
      }))
    }
  },

  methods: {
    handleClose() {
      this.$emit('close')
    },

    handleUpdate(formData) {
      for (let [property, value] of formData.entries()) {
        this.setProperty(property, value)
      }

      this.$emit('update:modelValue', this.image)

      this.handleClose()
    },

    getProperty(property) {
      return get(this.image, `custom_properties.${property}`)
    },

    setProperty(property, value) {
      set(this.image, `custom_properties.${property}`, value)
    },
  }
}
</script>

