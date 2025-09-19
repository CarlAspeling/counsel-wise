<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import LoadingSpinner from '@/Components/Auth/LoadingSpinner.vue'
import ErrorAlert from '@/Components/Auth/ErrorAlert.vue'
import SuccessAlert from '@/Components/Auth/SuccessAlert.vue'

const props = defineProps({
    status: {
        type: String,
        default: ''
    }
})

// Form state
const form = useForm({})

// Alert state
const showSuccessAlert = ref(false)
const showErrorAlert = ref(true)

// Page data
const page = usePage()

// Computed properties
const verificationLinkSent = computed(() => props.status === 'verification-link-sent')

const successMessage = computed(() => {
    if (verificationLinkSent.value) {
        return 'A new verification link has been sent to your email address.'
    }
    return props.status || page.props.flash?.success || ''
})

const hasFormErrors = computed(() => {
    return Object.keys(form.errors).length > 0
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
const submit = () => {
    // Reset alerts
    showErrorAlert.value = false
    showSuccessAlert.value = false

    // Clear previous errors
    form.clearErrors()

    // Submit form
    form.post(route('verification.send'), {
        onSuccess: () => {
            // Success will be handled by status update
        },
        onError: (errors) => {
            showErrorAlert.value = true
        },
        onFinish: () => {
            // Form is no longer processing
        }
    })
}

// Lifecycle
onMounted(() => {
    // Show success message if present
    if (successMessage.value) {
        showSuccessAlert.value = true
    }
})
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div>
                <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Verify Your Email
                </h1>
                <p class="mt-2 text-center text-sm text-gray-600">
                    We've sent you a verification link
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
                    title="Please try again:"
                    :message="form.errors"
                    @dismiss="showErrorAlert = false"
                />
            </div>

            <!-- Email Verification Content -->
            <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8">
                <!-- Info Text -->
                <div class="mb-6 text-sm text-gray-600 text-center">
                    Thanks for signing up! Before getting started, could you verify your email address by clicking on the
                    link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                </div>

                <!-- Actions -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Resend Button -->
                    <div class="flex items-center justify-center">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full relative bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:cursor-not-allowed"
                            :aria-busy="form.processing"
                        >
                            <span v-if="!form.processing">Resend Verification Email</span>
                            <span v-else class="flex items-center justify-center">
                                <LoadingSpinner size="sm" class="mr-2" />
                                <span>Sending Email...</span>
                            </span>
                        </button>
                    </div>

                    <!-- Logout Link -->
                    <div class="mt-6 text-center">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="text-blue-500 hover:text-blue-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded transition-colors duration-200"
                            :tabindex="form.processing ? -1 : 0"
                        >
                            Log Out
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>