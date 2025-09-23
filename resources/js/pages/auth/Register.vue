<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { UserIcon, EnvelopeIcon, IdentificationIcon, UserGroupIcon, LockClosedIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/Components/Auth/LoadingSpinner.vue'
import ErrorAlert from '@/Components/Auth/ErrorAlert.vue'
import SuccessAlert from '@/Components/Auth/SuccessAlert.vue'
import FormValidation from '@/Components/Auth/FormValidation.vue'
import PasswordStrengthIndicator from '@/Components/PasswordStrengthIndicator.vue'
import SiteHeader from '@/Components/Layout/SiteHeader.vue'
import SiteFooter from '@/Components/Layout/SiteFooter.vue'
import { validatePassword } from '@/utils/passwordValidation.js'

const props = defineProps({
    old: {
        type: Object,
        default: () => ({})
    },
    status: {
        type: String,
        default: ''
    }
})

// Refs
const nameInput = ref(null)

// Form state
const form = useForm({
    name: props.old.name || '',
    surname: props.old.surname || '',
    hpcsa_number: props.old.hpcsa_number || '',
    email: props.old.email || '',
    account_type: props.old.account_type || '',
    password: '',
    password_confirmation: '',
})

// Alert state
const showSuccessAlert = ref(false)
const showErrorAlert = ref(true)

// Page data
const page = usePage()

// Password validation state
const passwordValidation = computed(() => validatePassword(form.password))
const isPasswordValid = computed(() => passwordValidation.value.isValid)

// Password confirmation validation
const passwordsMatch = computed(() => {
    if (!form.password_confirmation) return true // Don't show error until they start typing
    return form.password === form.password_confirmation
})

// Computed properties
const successMessage = computed(() => {
    return props.status || page.props.flash?.success || ''
})

// Separate field validation errors from system errors
const fieldErrors = computed(() => {
    const { throttle, ...errors } = form.errors
    return errors
})

const systemErrors = computed(() => {
    return form.errors.throttle ? { throttle: form.errors.throttle } : {}
})

const hasFieldErrors = computed(() => {
    return Object.keys(fieldErrors.value).length > 0
})

const hasSystemErrors = computed(() => {
    return Object.keys(systemErrors.value).length > 0
})

const hasFormErrors = computed(() => {
    return hasFieldErrors.value || hasSystemErrors.value
})

const isFormValid = computed(() => {
    return form.name.length > 0 &&
           form.surname.length > 0 &&
           form.hpcsa_number.length > 0 &&
           form.email.length > 0 &&
           form.account_type.length > 0 &&
           isPasswordValid.value &&
           passwordsMatch.value
})

// Form submission validation
const canSubmit = computed(() => {
    return isFormValid.value && !form.processing
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
    form.post(route('register.store'), {
        onSuccess: () => {
            // Success is handled by redirect
        },
        onError: (errors) => {
            showErrorAlert.value = true
            // Focus on first field with error
            nextTick(() => {
                if (errors.name && nameInput.value) {
                    nameInput.value.focus()
                } else if (errors.surname) {
                    document.getElementById('surname')?.focus()
                } else if (errors.hpcsa_number) {
                    document.getElementById('hpcsa_number')?.focus()
                } else if (errors.email) {
                    document.getElementById('email')?.focus()
                } else if (errors.account_type) {
                    document.getElementById('account_type')?.focus()
                } else if (errors.password) {
                    document.getElementById('password')?.focus()
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
    // Focus on name input when component mounts
    if (nameInput.value) {
        nameInput.value.focus()
    }

    // Show success message if present
    if (successMessage.value) {
        showSuccessAlert.value = true
    }
})
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col">
        <!-- Header -->
        <SiteHeader
            :is-authenticated="!!$page.props.auth.user"
            :user="$page.props.auth.user"
            auth-page-type="register"
        />

        <!-- Main Content -->
        <main class="flex-1 py-12 px-6">
            <div class="max-w-2xl mx-auto">
                <!-- Form Title -->
                <h1 class="text-4xl lg:text-5xl font-bold text-center text-brand mb-8">
                    Create Your Account
                </h1>

                <!-- Success/Error Notifications -->
                <div class="space-y-4 mb-8" role="region" aria-label="Notifications">
                    <SuccessAlert
                        v-if="successMessage"
                        :show="showSuccessAlert"
                        :message="successMessage"
                        @dismiss="showSuccessAlert = false"
                    />

                    <!-- System-level errors (throttling, etc.) -->
                    <ErrorAlert
                        v-if="hasSystemErrors"
                        :show="showErrorAlert"
                        title="Unable to Process Request"
                        :message="systemErrors"
                        @dismiss="showErrorAlert = false"
                    />

                    <!-- Field validation errors -->
                    <ErrorAlert
                        v-if="hasFieldErrors && !hasSystemErrors"
                        :show="showErrorAlert"
                        title="Please correct the following errors:"
                        :message="fieldErrors"
                        @dismiss="showErrorAlert = false"
                    />
                </div>

                <!-- Registration Form -->
                <form
                    @submit.prevent="submit"
                    class="bg-white rounded-lg shadow-lg p-8 space-y-6"
                    novalidate
                    aria-label="Registration form"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name Field -->
                        <div>
                            <label
                                for="name"
                                class="form-label"
                            >
                                <UserIcon class="h-4 w-4 text-brand mr-2" />
                                Name
                            </label>
                            <input
                                id="name"
                                ref="nameInput"
                                v-model="form.name"
                                type="text"
                                required
                                autocomplete="given-name"
                                placeholder="Type your name"
                                :disabled="form.processing"
                                class="form-input"
                                :class="[
                                    form.errors.name ? 'form-input-error' : '',
                                    form.processing ? 'bg-gray-50 cursor-not-allowed' : ''
                                ]"
                                :aria-invalid="!!form.errors.name"
                                :aria-describedby="form.errors.name ? 'name-error' : undefined"
                            />
                            <FormValidation
                                :field-error="form.errors.name"
                                error-id="name-error"
                            />
                        </div>

                        <!-- Surname Field -->
                        <div>
                            <label
                                for="surname"
                                class="form-label"
                            >
                                <UserIcon class="h-4 w-4 text-brand mr-2" />
                                Surname
                            </label>
                            <input
                                id="surname"
                                v-model="form.surname"
                                type="text"
                                required
                                autocomplete="family-name"
                                placeholder="Type your surname"
                                :disabled="form.processing"
                                class="form-input"
                                :class="[
                                    form.errors.surname ? 'form-input-error' : '',
                                    form.processing ? 'bg-gray-50 cursor-not-allowed' : ''
                                ]"
                                :aria-invalid="!!form.errors.surname"
                                :aria-describedby="form.errors.surname ? 'surname-error' : undefined"
                            />
                            <FormValidation
                                :field-error="form.errors.surname"
                                error-id="surname-error"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Email Field -->
                        <div>
                            <label
                                for="email"
                                class="form-label"
                            >
                                <EnvelopeIcon class="h-4 w-4 text-brand mr-2" />
                                Email Address
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="username"
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

                        <!-- HPCSA Number Field -->
                        <div>
                            <label
                                for="hpcsa_number"
                                class="form-label"
                            >
                                <IdentificationIcon class="h-4 w-4 text-brand mr-2" />
                                HPCSA Registration Number
                            </label>
                            <input
                                id="hpcsa_number"
                                v-model="form.hpcsa_number"
                                type="text"
                                required
                                placeholder="Type your HPCSA reg. number"
                                :disabled="form.processing"
                                class="form-input"
                                :class="[
                                    form.errors.hpcsa_number ? 'form-input-error' : '',
                                    form.processing ? 'bg-gray-50 cursor-not-allowed' : ''
                                ]"
                                :aria-invalid="!!form.errors.hpcsa_number"
                                :aria-describedby="form.errors.hpcsa_number ? 'hpcsa-error' : undefined"
                            />
                            <FormValidation
                                :field-error="form.errors.hpcsa_number"
                                error-id="hpcsa-error"
                            />
                        </div>
                    </div>

                    <!-- Account Type Field -->
                    <div class="mb-6">
                        <label
                            for="account_type"
                            class="form-label"
                        >
                            <UserGroupIcon class="h-4 w-4 text-brand mr-2" />
                            Account Type
                        </label>
                        <select
                            id="account_type"
                            v-model="form.account_type"
                            required
                            :disabled="form.processing"
                            class="form-input bg-white"
                            :class="[
                                form.errors.account_type ? 'form-input-error' : '',
                                form.processing ? 'bg-gray-50 cursor-not-allowed' : ''
                            ]"
                            :aria-invalid="!!form.errors.account_type"
                            :aria-describedby="form.errors.account_type ? 'account-type-error' : undefined"
                        >
                            <option value="">Select account type</option>
                            <option value="counsellor_free">Counsellor (Free)</option>
                            <option value="counsellor_paid">Counsellor (Paid)</option>
                            <option value="student_rc">Student (Registered Counsellor)</option>
                        </select>
                        <FormValidation
                            :field-error="form.errors.account_type"
                            error-id="account-type-error"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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
                                autocomplete="new-password"
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

                            <!-- Password Strength Indicator -->
                            <PasswordStrengthIndicator
                                v-if="form.password"
                                :password="form.password"
                                :show-requirements="true"
                                :show-errors="false"
                            />

                            <FormValidation
                                :field-error="form.errors.password"
                                error-id="password-error"
                            />
                        </div>

                        <!-- Password Confirmation Field -->
                        <div>
                            <label
                                for="password_confirmation"
                                class="form-label"
                            >
                                <ShieldCheckIcon class="h-4 w-4 text-brand mr-2" />
                                Confirm Password
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                                :disabled="form.processing"
                                class="form-input"
                                :class="[
                                    (form.errors.password_confirmation || (form.password_confirmation && !passwordsMatch)) ? 'form-input-error' : '',
                                    form.processing ? 'bg-gray-50 cursor-not-allowed' : ''
                                ]"
                                :aria-invalid="!!form.errors.password_confirmation || (form.password_confirmation && !passwordsMatch)"
                                :aria-describedby="form.errors.password_confirmation ? 'password-confirmation-error' : undefined"
                            />

                            <!-- Password Match Validation -->
                            <div v-if="form.password_confirmation && !passwordsMatch" class="mt-2 text-xs text-red-600 flex items-start">
                                <svg class="h-4 w-4 text-red-500 mt-0.5 mr-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zM10 15a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                                <span>Passwords do not match</span>
                            </div>
                            <div v-else-if="form.password_confirmation && passwordsMatch" class="mt-2 text-xs text-green-600 flex items-center">
                                <svg class="h-3 w-3 mr-1 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span>Passwords match ✓</span>
                            </div>

                            <FormValidation
                                :field-error="form.errors.password_confirmation"
                                error-id="password-confirmation-error"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-center pt-6">
                        <button
                            type="submit"
                            :disabled="form.processing || !canSubmit"
                            class="w-full max-w-md btn-primary btn-large"
                            :aria-busy="form.processing"
                        >
                            <span v-if="!form.processing">CREATE ACCOUNT</span>
                            <span v-else class="flex items-center justify-center">
                                <LoadingSpinner size="sm" class="mr-2" />
                                <span>Creating Account...</span>
                            </span>
                        </button>
                    </div>

                    <!-- Submit button help text -->
                    <div v-if="form.password && !isPasswordValid" class="mt-4 text-sm text-gray-500 text-center">
                        Please ensure your password meets all requirements above
                    </div>
                    <div v-else-if="form.password_confirmation && !passwordsMatch" class="mt-4 text-sm text-gray-500 text-center">
                        Please ensure both password fields match
                    </div>

                    <!-- Login Link -->
                    <div class="mt-6 text-center">
                        <p class="text-gray-600">
                            Already have an account?
                            <Link
                                :href="route('login')"
                                class="text-brand hover:text-teal-800 font-medium focus-ring rounded transition-colors duration-200"
                                :tabindex="form.processing ? -1 : 0"
                            >
                                Log in here
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