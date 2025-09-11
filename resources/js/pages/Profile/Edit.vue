<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Profile Information</h2>

                        <form @submit.prevent="updateProfile" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        id="name"
                                        v-model="profileForm.name"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.name }"
                                    />
                                    <div v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="surname" class="block text-sm font-medium text-gray-700">Surname</label>
                                    <input
                                        id="surname"
                                        v-model="profileForm.surname"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.surname }"
                                    />
                                    <div v-if="profileForm.errors.surname" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.surname }}
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input
                                        id="email"
                                        v-model="profileForm.email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.email }"
                                    />
                                    <div v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.email }}
                                    </div>
                                </div>

                                <div>
                                    <label for="hpcsa_number" class="block text-sm font-medium text-gray-700">HPCSA Number</label>
                                    <input
                                        id="hpcsa_number"
                                        v-model="profileForm.hpcsa_number"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.hpcsa_number }"
                                    />
                                    <div v-if="profileForm.errors.hpcsa_number" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.hpcsa_number }}
                                    </div>
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Update Password</h2>

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
                            </div>

                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
                                >
                                    {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                                </button>

                                <div v-if="passwordForm.recentlySuccessful" class="text-green-600 text-sm">
                                    Password updated successfully!
                                </div>
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
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    auth: Object,
})

const profileForm = useForm({
    name: props.auth.user.name,
    surname: props.auth.user.surname,
    email: props.auth.user.email,
    hpcsa_number: props.auth.user.hpcsa_number,
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const updateProfile = () => {
    profileForm.patch('/profile', {
        preserveScroll: true,
    })
}

const updatePassword = () => {
    passwordForm.put('/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}
</script>
