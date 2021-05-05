<template>
  <field-wrapper>
    <div class="py-6">
      <div class="px-8">
        <form-label :for="field.attribute" :class="{
                      'mb-2': field.helpText && showHelpText
                  }">
          {{ fieldLabel }}&nbsp;<span
            v-if="field.required"
            class="text-danger text-sm"
            >{{ __('*') }}</span
          >
        </form-label>

        <help-text :show-help-text="showHelpText">
          {{ field.helpText }}
        </help-text>
      </div>

      <slot name="field"/>
    </div>
  </field-wrapper>
</template>

<script>
  // todo: extend from `default-field` somehow
  export default {
    props: {
      field: { type: Object, required: true },
      fieldName: { type: String },
      showHelpText: { type: Boolean, default: true },
    },

    computed: {
      fieldLabel() {
        // If the field name is purposefully an empty string, then
        // let's show it as such
        if (this.fieldName === '') {
          return ''
        }

        return this.fieldName || this.field.singularLabel || this.field.name
      },
    },
  };
</script>
