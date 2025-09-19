<template>
    <div
        class="inline-flex items-center justify-center"
        :class="sizeClasses"
        role="status"
        :aria-label="ariaLabel"
    >
        <svg
            class="animate-spin text-current"
            :class="spinnerSizeClasses"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        <span v-if="text" class="ml-2 text-sm font-medium">{{ text }}</span>
        <span class="sr-only">{{ srText }}</span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    text: {
        type: String,
        default: ''
    },
    ariaLabel: {
        type: String,
        default: 'Loading'
    },
    srText: {
        type: String,
        default: 'Loading, please wait...'
    }
})

const sizeClasses = computed(() => {
    const sizes = {
        xs: 'w-4 h-4',
        sm: 'w-5 h-5',
        md: 'w-6 h-6',
        lg: 'w-8 h-8',
        xl: 'w-10 h-10'
    }
    return sizes[props.size]
})

const spinnerSizeClasses = computed(() => {
    const sizes = {
        xs: 'w-4 h-4',
        sm: 'w-5 h-5',
        md: 'w-6 h-6',
        lg: 'w-8 h-8',
        xl: 'w-10 h-10'
    }
    return sizes[props.size]
})
</script>