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

                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input
                                        id="phone_number"
                                        v-model="profileForm.phone_number"
                                        type="tel"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': profileForm.errors.phone_number }"
                                        placeholder="+27 XX XXX XXXX"
                                    />
                                    <div v-if="profileForm.errors.phone_number" class="text-red-500 text-xs mt-1">
                                        {{ profileForm.errors.phone_number }}
                                    </div>
                                </div>

                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                    <select
                                        id="gender"
                                        v-model="profileForm.gender"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
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
                                    <label for="language" class="block text-sm font-medium text-gray-700">Language</label>
                                    <select
                                        id="language"
                                        v-model="profileForm.language"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
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
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                                <div>
                                    <label for="region" class="block text-sm font-medium text-gray-700">Region</label>
                                    <select
                                        id="region"
                                        v-model="profileForm.region"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
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
    phone_number: props.auth.user.phone_number,
    gender: props.auth.user.gender,
    language: props.auth.user.language,
    region: props.auth.user.region,
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
    })
}

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}
</script>
