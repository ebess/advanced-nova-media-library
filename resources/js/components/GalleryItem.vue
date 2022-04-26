<template>
    <div class="block h-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
        <slot />
    </div>
</template>

<script>
    export default {
        props: ['image', 'thumbnail', 'removable'],
    }
</script>
