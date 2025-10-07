<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Email Verification Banner -->
                <div v-if="mustVerifyEmail && !auth.user.email_verified_at"
                     class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                Please verify your new email address to continue using all features.
                                <a :href="route('verification.notice')" class="font-medium underline hover:text-yellow-600 dark:hover:text-yellow-100">
                                    Resend verification email
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Email Changed Success Message -->
                <div v-if="status === 'email-changed-verify'"
                     class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 p-4">
                    <p class="text-sm text-blue-700 dark:text-blue-200">
                        Email changed successfully! Please check your new email address for a verification link.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Profile Information</h2>

                        <form @submit.prevent="updateProfile" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <input
                                        id="name"
                                        v-model="profileForm.name"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.name }"
                                    />
                                    <div v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="surname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Surname</label>
                                    <input
                                        id="surname"
                                        v-model="profileForm.surname"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.surname }"
                                    />
                                    <div v-if="profileForm.errors.surname" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.surname }}
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input
                                        id="email"
                                        v-model="profileForm.email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.email }"
                                    />
                                    <div v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.email }}
                                    </div>
                                </div>

                                <div>
                                    <label for="hpcsa_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">HPCSA Number</label>
                                    <input
                                        id="hpcsa_number"
                                        v-model="profileForm.hpcsa_number"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.hpcsa_number }"
                                    />
                                    <div v-if="profileForm.errors.hpcsa_number" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.hpcsa_number }}
                                    </div>
                                </div>

                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                                    <input
                                        id="phone_number"
                                        v-model="profileForm.phone_number"
                                        type="tel"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.phone_number }"
                                        placeholder="+27 XX XXX XXXX"
                                    />
                                    <div v-if="profileForm.errors.phone_number" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.phone_number }}
                                    </div>
                                </div>

                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                                    <select
                                        id="gender"
                                        v-model="profileForm.gender"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.gender }"
                                    >
                                        <option value="">Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="prefer_not_to_say">Prefer not to say</option>
                                    </select>
                                    <div v-if="profileForm.errors.gender" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.gender }}
                                    </div>
                                </div>

                                <div>
                                    <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                                    <select
                                        id="language"
                                        v-model="profileForm.language"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.language }"
                                    >
                                        <option value="">Select language</option>
                                        <option value="english">English</option>
                                        <option value="afrikaans">Afrikaans</option>
                                        <option value="zulu">Zulu</option>
                                        <option value="xhosa">Xhosa</option>
                                        <option value="sotho">Sotho</option>
                                        <option value="tswana">Tswana</option>
                                        <option value="venda">Venda</option>
                                        <option value="tsonga">Tsonga</option>
                                        <option value="ndebele">Ndebele</option>
                                        <option value="swati">Swati</option>
                                        <option value="pedi">Pedi</option>
                                    </select>
                                    <div v-if="profileForm.errors.language" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.language }}
                                    </div>
                                </div>

                                <div>
                                    <label for="region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                                    <select
                                        id="region"
                                        v-model="profileForm.region"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.region }"
                                    >
                                        <option value="">Select region</option>
                                        <option value="western_cape">Western Cape</option>
                                        <option value="gauteng">Gauteng</option>
                                        <option value="kwazulu_natal">KwaZulu-Natal</option>
                                        <option value="eastern_cape">Eastern Cape</option>
                                        <option value="limpopo">Limpopo</option>
                                        <option value="mpumalanga">Mpumalanga</option>
                                        <option value="north_west">North West</option>
                                        <option value="northern_cape">Northern Cape</option>
                                        <option value="free_state">Free State</option>
                                    </select>
                                    <div v-if="profileForm.errors.region" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.region }}
                                    </div>
                                </div>

                                <div>
                                    <label for="theme_preference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Theme Preference</label>
                                    <select
                                        id="theme_preference"
                                        v-model="profileForm.theme_preference"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.theme_preference }"
                                    >
                                        <option value="light">Light</option>
                                        <option value="dark">Dark</option>
                                        <option value="system">System</option>
                                    </select>
                                    <div v-if="profileForm.errors.theme_preference" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.theme_preference }}
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Choose how the interface appears. System follows your device's theme.
                                    </p>
                                </div>

                                <!-- Profile Picture Upload -->
                                <div class="md:col-span-2">
                                    <ProfilePictureUpload
                                        v-model="profileForm.profile_picture"
                                        :profile-picture-url="auth.user.profile_picture_url"
                                        :user-name="`${auth.user.name} ${auth.user.surname}`"
                                        :has-uploaded-picture="hasUploadedPicture"
                                    />
                                    <div v-if="profileForm.errors.profile_picture" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.profile_picture }}
                                    </div>
                                </div>
                            </div>

                            <!-- Password Confirmation (only shown when email is being changed) -->
                            <div v-if="profileForm.email !== auth.user.email" class="md:col-span-2">
                                <label for="profile_password" class="block text-sm font-medium text-gray-700">
                                    Confirm Password
                                    <span class="text-xs text-gray-500">(required to change email)</span>
                                </label>
                                <input
                                    id="profile_password"
                                    v-model="profileForm.password"
                                    type="password"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': profileForm.errors.password }"
                                />
                                <div v-if="profileForm.errors.password" class="text-red-500 text-xs mt-1">
                                    {{ profileForm.errors.password }}
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
                                >
                                    {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                                </button>

                                <div v-if="profileForm.recentlySuccessful" class="text-green-600 text-sm">
                                    Saved successfully!
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Update Password</h2>

                        <form @submit.prevent="updatePassword" class="space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': passwordForm.errors.current_password }"
                                />
                                <div v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">
                                    {{ passwordForm.errors.current_password }}
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input
                                    id="password"
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    :class="{ 'border-red-500': passwordForm.errors.password }"
                                />

                                <!-- Password Strength Indicator -->
                                <PasswordStrengthIndicator
                                    v-if="passwordForm.password"
                                    :password="passwordForm.password"
                                    :show-requirements="true"
                                    :show-errors="false"
                                />

                                <div v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">
                                    {{ passwordForm.errors.password }}
                                </div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                />

                                <!-- Password Match Validation -->
                                <div v-if="passwordForm.password_confirmation && !passwordsMatch" class="mt-2 text-xs text-red-600">
                                    Passwords do not match
                                </div>
                                <div v-else-if="passwordForm.password_confirmation && passwordsMatch" class="mt-2 text-xs text-green-600">
                                    Passwords match ✓
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing || !canUpdatePassword"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
                                    :class="{ 'opacity-50': !canUpdatePassword }"
                                >
                                    {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                                </button>

                                <div v-if="passwordForm.recentlySuccessful" class="text-green-600 text-sm">
                                    Password updated successfully!
                                </div>
                            </div>

                            <!-- Submit button help text -->
                            <div v-if="passwordForm.password && !isPasswordValid" class="text-xs text-gray-500 text-center">
                                Please ensure your password meets all requirements above
                            </div>
                            <div v-else-if="passwordForm.password_confirmation && !passwordsMatch" class="text-xs text-gray-500 text-center">
                                Please ensure both password fields match
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PasswordStrengthIndicator from '@/Components/PasswordStrengthIndicator.vue'
import ProfilePictureUpload from '@/Components/ProfilePictureUpload.vue'
import { validatePassword } from '@/utils/passwordValidation.js'

const props = defineProps({
    auth: Object,
    mustVerifyEmail: Boolean,
    status: String,
})

// Determine if user has uploaded a picture (vs. fallback avatar)
const hasUploadedPicture = computed(() => {
    return props.auth.user.profile_picture_url && !props.auth.user.profile_picture_url.startsWith('data:image/')
})

const profileForm = useForm({
    _method: 'PATCH',
    name: props.auth.user.name,
    surname: props.auth.user.surname,
    email: props.auth.user.email,
    hpcsa_number: props.auth.user.hpcsa_number,
    phone_number: props.auth.user.phone_number,
    gender: props.auth.user.gender,
    language: props.auth.user.language,
    region: props.auth.user.region,
    theme_preference: props.auth.user.theme_preference || 'light',
    password: '', // Add password field for email change confirmation
    profile_picture: null, // Add profile picture field
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

// Password validation state
const passwordValidation = computed(() => validatePassword(passwordForm.password));
const isPasswordValid = computed(() => passwordValidation.value.isValid);

// Password confirmation validation
const passwordsMatch = computed(() => {
    if (!passwordForm.password_confirmation) return true;
    return passwordForm.password === passwordForm.password_confirmation;
});

// Form submission validation for password form
const canUpdatePassword = computed(() => {
    return isPasswordValid.value && passwordsMatch.value && !passwordForm.processing;
});

const updateProfile = () => {
    profileForm.post(route('profile.update'), {
        preserveScroll: true,
    })
}

const updatePassword = () => {
    // Client-side validation before submission
    if (!isPasswordValid.value) {
        return;
    }

    if (!passwordsMatch.value) {
        return;
    }

    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}
</script>
