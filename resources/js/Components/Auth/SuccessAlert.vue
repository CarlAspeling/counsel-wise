<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 transform scale-95"
        enter-to-class="opacity-100 transform scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 transform scale-100"
        leave-to-class="opacity-0 transform scale-95"
    >
        <div
            v-if="show"
            class="mb-4 p-4 rounded-md border-l-4 border-green-400 bg-green-50"
            role="alert"
            aria-live="polite"
            aria-atomic="true"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg
                        class="h-5 w-5 text-green-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.73 10.42a.75.75 0 00-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 v-if="title" class="text-sm font-medium text-green-800">
                        {{ title }}
                    </h3>
                    <div class="text-sm text-green-700" :class="{ 'mt-2': title }">
                        {{ message }}
                    </div>
                </div>
                <div v-if="dismissible" class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button
                            type="button"
                            @click="dismiss"
                            class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50"
                            :aria-label="dismissAriaLabel"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: true
    },
    title: {
        type: String,
        default: ''
    },
    message: {
        type: String,
        required: true
    },
    dismissible: {
        type: Boolean,
        default: true
    },
    autoDismiss: {
        type: [Boolean, Number],
        default: 3000
    },
    dismissAriaLabel: {
        type: String,
        default: 'Dismiss success message'
    }
})

const emit = defineEmits(['dismiss'])

const show = ref(props.show)

watch(() => props.show, (newValue) => {
    show.value = newValue
})

let dismissTimer = null

const dismiss = () => {
    show.value = false
    emit('dismiss')
}

const setupAutoDismiss = () => {
    if (props.autoDismiss && show.value) {
        const delay = typeof props.autoDismiss === 'number' ? props.autoDismiss : 3000
        dismissTimer = setTimeout(dismiss, delay)
    }
}

watch(show, (newValue) => {
    if (newValue) {
        setupAutoDismiss()
    } else if (dismissTimer) {
        clearTimeout(dismissTimer)
        dismissTimer = null
    }
})

// Setup auto dismiss on mount if needed
if (props.autoDismiss && show.value) {
    setupAutoDismiss()
}
</script>