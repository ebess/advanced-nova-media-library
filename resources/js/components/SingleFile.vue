<template>
    <gallery-item class="gallery-item-file">
        <div class="gallery-item-info">
            <a
                class="download mr-2"
                :href="image.__media_urls__.__original__"
                target="_blank"
            >
                <Icon type="search" width="16" height="16" />
            </a>

            <a
                v-if="downloadUrl"
                class="download mr-2"
                :href="downloadUrl"
            >
                <Icon type="download" width="16" height="16" />
            </a>

            <span class="label">
                {{ image.file_name }}
            </span>

            <div
                v-if="isCustomPropertiesEditable"
                class="edit edit--file ml-2"
                @click.prevent="$emit('edit-custom-properties')"
            >
                <Icon type="edit" width="16" height="16" />
            </div>

            <div
                v-if="removable"
                class="delete ml-2"
                @click.prevent="$emit('remove')"
            >
                <Icon type="delete" width="16" height="16" />
            </div>
        </div>
    </gallery-item>
</template>

<script>
    import GalleryItem from './GalleryItem';

    export default {
        props: ['image', 'removable', 'isCustomPropertiesEditable'],

        components: {
            GalleryItem,
        },

        computed: {
            downloadUrl() {
                return this.image.id ? `/nova-vendor/ebess/advanced-nova-media-library/download/${this.image.id}` : null;
            },
        },
    }
</script>

<style lang="scss">
    .gallery .edit.edit--file {
        position: relative;
        top: auto;
        right: auto;
    }

    .gallery-item-file {
        &.gallery-item {
            width: 100%;

            .gallery-item-info {
                display: flex;

                .label {
                    flex-grow: 1;
                }

                .download {
                    color: var(--primary-dark);
                }

                .delete {
                    align-self: flex-end;
                    color: var(--danger);
                }
            }
        }
    }
</style>
