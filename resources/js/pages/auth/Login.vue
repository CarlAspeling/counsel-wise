<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div>
                <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign in to CounselWise
                </h1>
                <p class="mt-2 text-center text-sm text-gray-600">
                    For HPCSA registered counsellors
                </p>
            </div>

            <!-- Success/Error Notifications -->
            <div class="space-y-4" role="region" aria-label="Notifications">
                <SuccessAlert
                    v-if="successMessage"
                    :show="showSuccessAlert"
                    :message="successMessage"
                    @dismiss="showSuccessAlert = false"
                />

                <ErrorAlert
                    v-if="hasFormErrors"
                    :show="showErrorAlert"
                    title="Please correct the following errors:"
                    :message="form.errors"
                    @dismiss="showErrorAlert = false"
                />
            </div>

            <!-- Login Form -->
            <form
                @submit.prevent="submit"
                class="mt-8 space-y-6"
                novalidate
                aria-label="Sign in form"
            >
                <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8">
                    <!-- Email Field -->
                    <div class="mb-6">
                        <label
                            for="email"
                            class="block text-gray-700 text-sm font-bold mb-2"
                        >
                            Email Address
                            <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input
                            id="email"
                            ref="emailInput"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            :disabled="form.processing"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                            :class="[
                                form.errors.email
                                    ? 'border-red-500 focus:ring-red-500'
                                    : 'border-gray-300',
                                form.processing ? 'bg-gray-50 cursor-not-allowed' : ''
                            ]"
                            :aria-invalid="!!form.errors.email"
                            :aria-describedby="form.errors.email ? 'email-error' : undefined"
                            @blur="validateEmail"
                        />
                        <FormValidation
                            :field-error="form.errors.email"
                            error-id="email-error"
                        />
                    </div>

                    <!-- Password Field -->
                    <div class="mb-6">
                        <label
                            for="password"
                            class="block text-gray-700 text-sm font-bold mb-2"
                        >
                            Password
                            <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            :disabled="form.processing"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                            :class="[
                                form.errors.password
                                    ? 'border-red-500 focus:ring-red-500'
                                    : 'border-gray-300',
                                form.processing ? 'bg-gray-50 cursor-not-allowed' : ''
                            ]"
                            :aria-invalid="!!form.errors.password"
                            :aria-describedby="form.errors.password ? 'password-error' : undefined"
                        />
                        <FormValidation
                            :field-error="form.errors.password"
                            error-id="password-error"
                        />
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                :disabled="form.processing"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors duration-200"
                                :class="form.processing ? 'cursor-not-allowed' : 'cursor-pointer'"
                            />
                            <span class="ml-2 text-sm text-gray-700">Remember me</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between">
                        <button
                            type="submit"
                            :disabled="form.processing || !isFormValid"
                            class="relative bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:cursor-not-allowed"
                            :aria-busy="form.processing"
                        >
                            <span v-if="!form.processing">Sign In</span>
                            <span v-else class="flex items-center">
                                <LoadingSpinner size="sm" class="mr-2" />
                                <span>Signing in...</span>
                            </span>
                        </button>

                        <Link
                            :href="route('password.request')"
                            class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded transition-colors duration-200"
                            :tabindex="form.processing ? -1 : 0"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <!-- Register Link -->
                    <div class="mt-6 text-center">
                        <Link
                            :href="route('register')"
                            class="text-blue-500 hover:text-blue-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded transition-colors duration-200"
                            :tabindex="form.processing ? -1 : 0"
                        >
                            Don't have an account? Register here
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import LoadingSpinner from '@/Components/Auth/LoadingSpinner.vue'
import ErrorAlert from '@/Components/Auth/ErrorAlert.vue'
import SuccessAlert from '@/Components/Auth/SuccessAlert.vue'
import FormValidation from '@/Components/Auth/FormValidation.vue'

// Props from the page
const props = defineProps({
    status: {
        type: String,
        default: ''
    }
})

// Refs
const emailInput = ref(null)

// Form state
const form = useForm({
    email: '',
    password: '',
    remember: false,
})

// Alert state
const showSuccessAlert = ref(false)
const showErrorAlert = ref(true)

// Page data
const page = usePage()

// Computed properties
const successMessage = computed(() => {
    return props.status || page.props.flash?.success || ''
})

const hasFormErrors = computed(() => {
    return Object.keys(form.errors).length > 0
})

const isFormValid = computed(() => {
    return form.email.length > 0 && form.password.length > 0
})

// Watch for success messages
watch(successMessage, (newValue) => {
    if (newValue) {
        showSuccessAlert.value = true
    }
})

// Watch for form errors
watch(() => form.errors, (newErrors) => {
    if (Object.keys(newErrors).length > 0) {
        showErrorAlert.value = true
    }
}, { deep: true })

// Methods
const validateEmail = () => {
    if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        // Basic email validation for immediate feedback
        // Server-side validation will provide the final validation
    }
}

const submit = () => {
    // Reset alerts
    showErrorAlert.value = false
    showSuccessAlert.value = false

    // Clear previous errors
    form.clearErrors()

    // Submit form
    form.post(route('login'), {
        onSuccess: () => {
            // Success is handled by redirect
        },
        onError: (errors) => {
            showErrorAlert.value = true
            // Focus on first field with error
            nextTick(() => {
                if (errors.email && emailInput.value) {
                    emailInput.value.focus()
                }
            })
        },
        onFinish: () => {
            // Form is no longer processing
        }
    })
}

// Lifecycle
onMounted(() => {
    // Focus on email input when component mounts
    if (emailInput.value) {
        emailInput.value.focus()
    }

    // Show success message if present
    if (successMessage.value) {
        showSuccessAlert.value = true
    }
})
</script>
