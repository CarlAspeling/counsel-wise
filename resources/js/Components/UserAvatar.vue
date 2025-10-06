<template>
    <img
        :src="avatarUrl"
        :alt="altText"
        :class="[
            'object-cover',
            sizeClass,
            shapeClass,
            'ring-gray-200 dark:ring-gray-700',
            ringWidth > 0 ? `ring-${ringWidth}` : '',
        ]"
    />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'medium', // 'small', 'medium', 'large', 'xl'
        validator: (value) => ['small', 'medium', 'large', 'xl'].includes(value),
    },
    shape: {
        type: String,
        default: 'circle', // 'circle', 'rounded'
        validator: (value) => ['circle', 'rounded'].includes(value),
    },
    ring: {
        type: Number,
        default: 0,
        validator: (value) => value >= 0 && value <= 8,
    },
})

const sizeClass = computed(() => {
    const sizes = {
        small: 'h-8 w-8',
        medium: 'h-10 w-10',
        large: 'h-16 w-16',
        xl: 'h-24 w-24',
    }
    return sizes[props.size] || sizes.medium
})

const shapeClass = computed(() => {
    return props.shape === 'circle' ? 'rounded-full' : 'rounded-lg'
})

const ringWidth = computed(() => props.ring)

const avatarUrl = computed(() => {
    return props.user.profile_picture_url || ''
})

const altText = computed(() => {
    const userName = props.user.name && props.user.surname
        ? `${props.user.name} ${props.user.surname}`
        : props.user.name || 'User'
    return `${userName}'s profile picture`
})
</script>