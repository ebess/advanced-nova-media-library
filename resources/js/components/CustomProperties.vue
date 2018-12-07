<template>
    <portal to="modals">
        <transition name="fade">
            <CustomPropertiesModal
                :fields="filledFields"
                @close="handleClose"
                @update="handleUpdate"
            />
        </transition>
    </portal>
</template>

<script>
    import CustomPropertiesModal from './CustomPropertiesModal'

    export default {
        props: {
            value: {
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

        data () {
            return {
                image: _.cloneDeep(this.value),
            }
        },

        computed: {
            filledFields () {
                return _.cloneDeep(this.fields).map(field => _.tap(field, field => {
                    field.value = this.getProperty(field.attribute)
                }))
            }
        },

        methods: {
            handleClose () {
                this.$emit('close')
            },

            handleUpdate (formData) {
                for (let [property, value] of formData.entries()) {
                    this.setProperty(property, value)
                }

                this.$emit('input', this.image)

                this.handleClose()
            },

            getProperty (property) {
                return _.get(this.image, `custom_properties.${property}`)
            },

            setProperty (property, value) {
                _.set(this.image, `custom_properties.${property}`, value)
            },
        }
    }
</script>
