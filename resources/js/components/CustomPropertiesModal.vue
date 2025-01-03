<template>
    <Modal
      :show="true"
      maxWidth="2xl"
      @modal-close="handleClose"
          :classWhitelist="[
            'flatpickr-current-month',
            'flatpickr-next-month',
            'flatpickr-prev-month',
            'flatpickr-weekday',
            'flatpickr-weekdays',
            'flatpickr-calendar',
            ]"
     >
        <card class="overflow-hidden">
            <form class="rounded-lg shadow-lg overflow-hidden w-action-fields"
                @submit.prevent="handleUpdate"
                autocomplete="off"
            >
                <div v-for="field in fields" :key="field.attribute" class="action">
                    <component :is="'form-' + field.component" :field="field"/>
                </div>

                <div class="bg-30 px-6 py-3 flex">
                    <div class="flex items-center ml-auto">
                        <Button
                          @click.prevent="handleClose"
                          variant="link"
                          :label="__('Cancel')"
                        />
                        <Button
                          :label="__('Update')"
                          variant="solid"
                          @click.prevent="handleUpdate"
                        />
                    </div>
                </div>
            </form>
        </card>
    </Modal>
</template>

<script>
  import { Button } from 'laravel-nova-ui'

  export default {
    components: {
      Button,
    },
    props: {
        fields: {
            type: Array,
            required: true,
        }
    },

    methods: {
        handleClose () {
            this.$emit('close')
        },

        handleUpdate () {
            let formData = new FormData()

            this.fields.forEach(field => field.fill(formData))

            this.$emit('update', formData)
        }
    }
  }
</script>
