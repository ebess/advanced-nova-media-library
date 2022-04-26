<template>
    <GalleryItem
        class="flex items-center px-4 py-3"
        :style="{
            cursor: (editable ? 'grab' : 'default'),
            userSelect: 'none',
        }"
    >
        <a
            class="mr-2 cursor-pointer hover:opacity-50"
            :href="image.__media_urls__.__original__"
            target="_blank"
        >
            <Icon type="search" width="16" height="16" />
        </a>

        <a
            v-if="downloadUrl"
            class="mr-2 cursor-pointer hover:opacity-50"
            :href="downloadUrl"
        >
            <Icon type="download" width="16" height="16" />
        </a>

        <span class="text-bold">
            {{ image.file_name }}
        </span>

        <div class="flex items-center ml-auto">
            <div
                v-if="isCustomPropertiesEditable"
                class="ml-2 cursor-pointer hover:opacity-50"
                @click.prevent="$emit('edit-custom-properties')"
            >
                <Icon type="pencil" width="16" height="16" />
            </div>

            <div
                v-if="removable"
                class="ml-2 cursor-pointer hover:opacity-50"
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
