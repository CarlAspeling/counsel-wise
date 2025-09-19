<template>
    <div v-if="hasErrors || hasFieldError" class="space-y-2">
        <!-- Field-specific error -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 transform translate-y-1"
            enter-to-class="opacity-100 transform translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 transform translate-y-0"
            leave-to-class="opacity-0 transform translate-y-1"
        >
            <div
                v-if="hasFieldError"
                class="text-red-600 text-sm font-medium flex items-start"
                role="alert"
                :aria-live="live ? 'polite' : 'off'"
                :id="errorId"
            >
                <svg
                    class="h-4 w-4 text-red-500 mt-0.5 mr-1 flex-shrink-0"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zM10 15a1 1 0 100-2 1 1 0 000 2z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>{{ fieldError }}</span>
            </div>
        </Transition>

        <!-- Progressive error disclosure -->
        <div v-if="showProgressiveErrors && progressiveErrors.length > 0" class="space-y-1">
            <div class="text-xs text-gray-600 font-medium">{{ progressiveErrorsLabel }}:</div>
            <TransitionGroup
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 transform translate-x-2"
                enter-to-class="opacity-100 transform translate-x-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 transform translate-x-0"
                leave-to-class="opacity-0 transform translate-x-2"
                tag="div"
                class="space-y-1"
            >
                <div
                    v-for="(error, index) in progressiveErrors"
                    :key="`error-${index}`"
                    class="text-xs flex items-center"
                    :class="error.met ? 'text-green-600' : 'text-gray-500'"
                >
                    <svg
                        class="h-3 w-3 mr-1 flex-shrink-0"
                        :class="error.met ? 'text-green-500' : 'text-gray-400'"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            v-if="error.met"
                            fill-rule="evenodd"
                            d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                            clip-rule="evenodd"
                        />
                        <circle v-else cx="10" cy="10" r="2"/>
                    </svg>
                    <span>{{ error.message }}</span>
                </div>
            </TransitionGroup>
        </div>

        <!-- Help text -->
        <div v-if="helpText" class="text-xs text-gray-500 mt-1">
            {{ helpText }}
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    errors: {
        type: [String, Array, Object],
        default: null
    },
    fieldName: {
        type: String,
        default: ''
    },
    fieldError: {
        type: String,
        default: ''
    },
    progressiveErrors: {
        type: Array,
        default: () => []
    },
    showProgressiveErrors: {
        type: Boolean,
        default: false
    },
    progressiveErrorsLabel: {
        type: String,
        default: 'Requirements'
    },
    helpText: {
        type: String,
        default: ''
    },
    live: {
        type: Boolean,
        default: true
    },
    errorId: {
        type: String,
        default: () => `error-${Math.random().toString(36).substr(2, 9)}`
    }
})

const hasErrors = computed(() => {
    if (!props.errors) return false

    if (typeof props.errors === 'string') return props.errors.length > 0
    if (Array.isArray(props.errors)) return props.errors.length > 0
    if (typeof props.errors === 'object') return Object.keys(props.errors).length > 0

    return false
})

const hasFieldError = computed(() => {
    return props.fieldError && props.fieldError.length > 0
})
</script>