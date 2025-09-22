<template>
    <div class="min-h-screen bg-white flex flex-col">
        <!-- Header -->
        <SiteHeader
            :is-authenticated="!!$page.props.auth.user"
            :user="$page.props.auth.user"
            auth-page-type="login"
        />

        <!-- Main Content -->
        <main class="flex-1 py-12 px-6">
            <div class="max-w-md mx-auto">
                <!-- Form Title -->
                <h1 class="text-4xl lg:text-5xl font-bold text-center text-brand mb-8">
                    Login
                </h1>

                <!-- Success/Error Notifications -->
                <div class="space-y-4 mb-8" role="region" aria-label="Notifications">
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
                    class="bg-white rounded-lg shadow-lg p-8 space-y-6"
                    novalidate
                    aria-label="Sign in form"
                >
                    <!-- Email Field -->
                    <div>
                        <label
                            for="email"
                            class="form-label"
                        >
                            <EnvelopeIcon class="h-4 w-4 text-brand mr-2" />
                            Email
                        </label>
                        <input
                            id="email"
                            ref="emailInput"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="Type your email"
                            :disabled="form.processing"
                            class="form-input"
                            :class="[
                                form.errors.email ? 'form-input-error' : '',
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
                    <div>
                        <label
                            for="password"
                            class="form-label"
                        >
                            <LockClosedIcon class="h-4 w-4 text-brand mr-2" />
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Type your password"
                            :disabled="form.processing"
                            class="form-input"
                            :class="[
                                form.errors.password ? 'form-input-error' : '',
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

                    <!-- Remember Me and Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                :disabled="form.processing"
                                class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded transition-colors duration-200"
                                :class="form.processing ? 'cursor-not-allowed' : 'cursor-pointer'"
                            />
                            <span class="ml-2 text-sm text-gray-700">Remember me</span>
                        </label>

                        <Link
                            :href="route('password.request')"
                            class="text-sm text-brand hover:text-primary-700 focus-ring rounded transition-colors duration-200"
                            :tabindex="form.processing ? -1 : 0"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing || !isFormValid"
                            class="w-full btn-primary btn-large"
                            :aria-busy="form.processing"
                        >
                            <span v-if="!form.processing">LOGIN</span>
                            <span v-else class="flex items-center justify-center">
                                <LoadingSpinner size="sm" class="mr-2" />
                                <span>Signing in...</span>
                            </span>
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="mt-6 text-center">
                        <p class="text-gray-600">
                            Don't have an account?
                            <Link
                                :href="route('register')"
                                class="text-brand hover:text-primary-700 font-medium focus-ring rounded transition-colors duration-200"
                                :tabindex="form.processing ? -1 : 0"
                            >
                                Register here
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <SiteFooter />
    </div>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { EnvelopeIcon, LockClosedIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/Components/Auth/LoadingSpinner.vue'
import ErrorAlert from '@/Components/Auth/ErrorAlert.vue'
import SuccessAlert from '@/Components/Auth/SuccessAlert.vue'
import FormValidation from '@/Components/Auth/FormValidation.vue'
import SiteHeader from '@/Components/Layout/SiteHeader.vue'
import SiteFooter from '@/Components/Layout/SiteFooter.vue'

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
