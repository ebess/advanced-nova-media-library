<template>
    <GalleryItem
        class="flex flex-col"
        :style="{
            cursor: (editable ? 'grab' : 'default'),
            userSelect: 'none',
        }"
    >
        <div class="flex items-center justify-end border-b border-gray-200 dark:border-gray-700">
            <div
                class="h-8 cursor-pointer flex-shrink-0 ico-button inline-flex items-center justify-center px-2 text-sm border-l border-gray-200 dark:border-gray-700 hover:opacity-50"
                @click.prevent="showPreview"
            >
                <Icon type="external-link" width="16" height="16" />
            </div>

            <a
                v-if="downloadUrl"
                class="h-8 cursor-pointer flex-shrink-0 ico-button inline-flex items-center justify-center px-2 text-sm border-l border-gray-200 dark:border-gray-700 hover:opacity-50"
                :href="downloadUrl"
                title="Download"
            >
                <Icon type="download" width="16" height="16" />
            </a>

            <div
                v-if="isCustomPropertiesEditable"
                class="h-8 cursor-pointer flex-shrink-0 ico-button inline-flex items-center justify-center px-2 text-sm border-l border-gray-200 dark:border-gray-700 hover:opacity-50"
                @click.prevent="$emit('edit-custom-properties')"
            >
                <Icon type="pencil" width="16" height="16" />
            </div>

            <div
                v-if="croppable"
                class="h-8 cursor-pointer flex-shrink-0 ico-button inline-flex items-center justify-center px-2 text-sm border-l border-gray-200 dark:border-gray-700 hover:opacity-50"
                @click.prevent="$emit('crop-start', image)"
            >
                <Icon type="scissors" width="16" height="16" />
            </div>

            <div
                v-if="removable"
                class="h-8 cursor-pointer flex-shrink-0 ico-button inline-flex items-center justify-center px-2 text-sm border-l border-gray-200 dark:border-gray-700 hover:opacity-50"
                @click.prevent="$emit('remove')"
            >
                <Icon type="trash" class="text-red-500" width="16" height="16" />
            </div>
        </div>

        <img
            :src="src"
            :alt="image.name"
            ref="image"
            class="flex-grow"
            style="object-fit: contain"
        />

        <div v-if="field.showStatistics" class="flex text-xs flex-col items-center p-2 text-center border-t border-gray-200 dark:border-gray-700">
            <div v-if="size" class="mb-1"><strong>{{ size }}</strong></div>
            <div class="mb-1"><strong>{{ width }}×{{ height }}</strong> px</div>
            <div :class="{ 'mb-1': mimeType }"><strong>{{ aspectRatio }}</strong> (<i>{{ ratio }}</i>)</div>
            <div v-if="mimeType">{{ mimeType }}</div>
        </div>
    </GalleryItem>
</template>

<script>
    import GalleryItem from './GalleryItem';

    export default {
        components: {
            GalleryItem,
        },

        props: ['image', 'field', 'removable', 'editable', 'isCustomPropertiesEditable'],

        data: () => ({
            acceptedMimeTypes: ['image/jpg', 'image/jpeg', 'image/png'],
            src: "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==",
            width: undefined,
            height: undefined,
            aspectRatio: undefined,
            ratio: undefined,
            size: undefined,
        }),

        computed: {
            downloadUrl() {
                return this.image.id ? `/nova-vendor/ebess/advanced-nova-media-library/download/${this.image.id}` : null;
            },

            croppable() {
                return this.editable &&
                    this.field.croppable &&
                    this.acceptedMimeTypes.includes(this.mimeType);
            },

            mimeType() {
                return this.image?.mime_type || this.image?.file?.type || '';
            },
        },

        watch: {
            image: {
                handler: 'getImage',
                immediate: true
            },
        },

        methods: {
            showPreview() {
                const blobUrl = this.image.file ? URL.createObjectURL(this.image.file) : this.image.__media_urls__.preview;
                window.open(blobUrl, '_blank');
            },

            getImage() {
                if (this.editable && this.image.__media_urls__.form) {
                    this.src = this.image.__media_urls__.form;
                } else if (!this.editable && this.image.__media_urls__.detailView) {
                    this.src = this.image.__media_urls__.detailView;
                } else if (this.isVideo(this.image.__media_urls__.__original__)) {
                    //Seconds to seek to, to get thumbnail of video
                    let seconds = 1;  //TODO get this from the field instead of hardcoding it here
                    this.getVideoThumbnail(this.image.__media_urls__.__original__, seconds);
                } else {
                    this.src = this.image.__media_urls__.__original__;
                }

                if (this.field.showStatistics) {
                    setTimeout(this.calculateStatistics);
                }
            },

            getVideoThumbnail(path, secs = 0) {
                const video = document.createElement('video');

                video.onloadedmetadata = () => {
                    video.currentTime = Math.min(Math.max(0, (secs < 0 ? video.duration : 0) + secs), video.duration);
                };

                video.onseeked = (e) => {
                    const canvas = document.createElement('canvas');

                    canvas.height = video.videoHeight;
                    canvas.width = video.videoWidth;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    this.src = canvas.toDataURL();
                };

                video.src = path;
            },

            isVideo(mediaPath) {
                if (mediaPath.startsWith("data:video"))
                    return true;

                // TODO better video detection
                const supportedExtensions = [".mp4", ".ogv", ".webm"];

                return supportedExtensions.some(suffix => mediaPath.endsWith(suffix));
            },

            calculateStatistics() {
                if (this.$refs.image?.complete) {
                    this.width = this.$refs.image.naturalWidth;
                    this.height = this.$refs.image.naturalHeight;
                    this.ratio = Math.round((this.width / this.height) * 100) / 100;

                    const gcd = this.gcd(this.width, this.height);
                    this.aspectRatio = (this.width / gcd) + ':' + (this.height / gcd);

                    if (this.field.showStatistics) {
                        const src = this.$refs.image.currentSrc;

                        if (src.startsWith('data:')) {
                            const base64Length = src.length - (src.indexOf(',') + 1);
                            const padding = (src.charAt(src.length - 2) === '=') ? 2 : ((src.charAt(src.length - 1) === '=') ? 1 : 0);

                            this.size = this.formatBytes(base64Length * 0.75 - padding);
                        } else if (window.performance !== undefined) {
                            const imgResourceTimings = window.performance.getEntriesByName(this.$refs.image.currentSrc);

                            if (imgResourceTimings.length) {
                                const decodedBodySize = imgResourceTimings[0].decodedBodySize;

                                if (decodedBodySize) {
                                    this.size = this.formatBytes(imgResourceTimings[0].decodedBodySize);
                                } else {
                                    this.size = undefined;
                                }
                            } else {
                                this.size = undefined;
                            }
                        }
                    }
                } else {
                    this.$refs.image.onload = this.calculateStatistics;
                }
            },

            gcd(a, b) {
                if (b === 0) {
                    return a;
                }

                return this.gcd(b, a % b);
            },

            formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';

                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

                const i = Math.floor(Math.log(bytes) / Math.log(k));

                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            },
        },
    }
</script>
