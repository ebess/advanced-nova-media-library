<template>
    <GalleryItem
        class="flex items-center"
        :style="{
            cursor: (editable ? 'grab' : 'default'),
            userSelect: 'none',
        }"
    >
        <span class="text-bold px-3">
            {{ image.file_name }}
        </span>

        <div class="flex ml-auto">
            <a
                class="h-10 w-10 cursor-pointer hover:opacity-50 border-l border-gray-200 dark:border-gray-700 px-2 inline-flex items-center justify-center"
                :href="image.__media_urls__.__original__"
                target="_blank"
            >
                <Icon type="external-link" width="16" height="16" />
            </a>

            <a
                v-if="downloadUrl"
                class="h-10 w-10 cursor-pointer hover:opacity-50 border-l border-gray-200 dark:border-gray-700 px-2 inline-flex items-center justify-center"
                :href="downloadUrl"
            >
                <Icon type="download" width="16" height="16" />
            </a>

            <div
                v-if="isCustomPropertiesEditable"
                class="h-10 w-10 cursor-pointer hover:opacity-50 border-l border-gray-200 dark:border-gray-700 px-2 inline-flex items-center justify-center"
                @click.prevent="$emit('edit-custom-properties')"
            >
                <Icon type="pencil" width="16" height="16" />
            </div>

            <div
                v-if="removable"
                class="h-10 w-10 cursor-pointer hover:opacity-50 border-l border-gray-200 dark:border-gray-700 px-2 inline-flex items-center justify-center"
                @click.prevent="$emit('remove')"
            >
                <Icon type="trash" class="text-red-500" width="16" height="16" />
            </div>
        </div>
    </GalleryItem>
</template>

<script>
    import GalleryItem from './GalleryItem';

    export default {
        props: ['image', 'removable', 'editable', 'isCustomPropertiesEditable'],

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
