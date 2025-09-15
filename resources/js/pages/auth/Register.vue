<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordStrengthIndicator from '@/Components/PasswordStrengthIndicator.vue';
import { validatePassword } from '@/utils/passwordValidation.js';

const props = defineProps({
    old: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    name: props.old.name || '',
    surname: props.old.surname || '',
    hpcsa_number: props.old.hpcsa_number || '',
    email: props.old.email || '',
    account_type: props.old.account_type || '',
    password: '',
    password_confirmation: '',
});

// Password validation state
const passwordValidation = computed(() => validatePassword(form.password));
const isPasswordValid = computed(() => passwordValidation.value.isValid);

// Password confirmation validation
const passwordsMatch = computed(() => {
    if (!form.password_confirmation) return true; // Don't show error until they start typing
    return form.password === form.password_confirmation;
});

// Form submission validation
const canSubmit = computed(() => {
    return isPasswordValid.value && passwordsMatch.value && !form.processing;
});

const submit = () => {
    // Client-side validation before submission
    if (!isPasswordValid.value) {
        return;
    }
    
    if (!passwordsMatch.value) {
        return;
    }
    
    form.post(route('register.store'), {
        onFinish: () => {
            // Reset password fields on completion
            form.reset('password', 'password_confirmation');
        }
    });
};
</script>

<template>
    <GuestLayout>
        <div class="mb-4">
            <h2 class="text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                Register for CounselWise
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                For HPCSA registered counsellors only
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="given-name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="surname" value="Surname" />

                <TextInput
                    id="surname"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.surname"
                    required
                    autocomplete="family-name"
                />

                <InputError class="mt-2" :message="form.errors.surname" />
            </div>

            <div>
                <InputLabel for="hpcsa_number" value="HPCSA Registration Number" />

                <TextInput
                    id="hpcsa_number"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.hpcsa_number"
                    required
                    placeholder="e.g. PS0123456"
                />

                <InputError class="mt-2" :message="form.errors.hpcsa_number" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="account_type" value="Account Type" />

                <select
                    id="account_type"
                    v-model="form.account_type"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-blue-600 dark:focus:ring-blue-600"
                >
                    <option value="">Select Account Type</option>
                    <option value="counsellor_free">Counsellor (Free)</option>
                    <option value="counsellor_paid">Counsellor (Paid)</option>
                    <option value="student_rc">Student (Registered Counsellor)</option>
                </select>

                <InputError class="mt-2" :message="form.errors.account_type" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <!-- Password Strength Indicator -->
                <PasswordStrengthIndicator 
                    v-if="form.password"
                    :password="form.password"
                    :show-requirements="true"
                    :show-errors="false"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <!-- Password Match Validation -->
                <div v-if="form.password_confirmation && !passwordsMatch" class="mt-2 text-xs text-red-600 dark:text-red-400">
                    Passwords do not match
                </div>
                <div v-else-if="form.password_confirmation && passwordsMatch" class="mt-2 text-xs text-green-600 dark:text-green-400">
                    Passwords match ✓
                </div>

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div>
                <PrimaryButton 
                    class="w-full justify-center" 
                    :class="{ 'opacity-25': form.processing || !canSubmit }" 
                    :disabled="form.processing || !canSubmit"
                >
                    {{ form.processing ? 'Creating Account...' : 'Create Account' }}
                </PrimaryButton>
                
                <!-- Submit button help text -->
                <div v-if="form.password && !isPasswordValid" class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                    Please ensure your password meets all requirements above
                </div>
                <div v-else-if="form.password_confirmation && !passwordsMatch" class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                    Please ensure both password fields match
                </div>
            </div>

            <div class="text-center">
                <Link
                    :href="route('login')"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200"
                >
                    Already have an account? Sign in here
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>