<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Register for CounselWise
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    For HPCSA registered counsellors only
                </p>
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                            Full Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-xs italic mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="surname" class="block text-gray-700 text-sm font-bold mb-2">
                            Surname
                        </label>
                        <input
                            id="surname"
                            v-model="form.surname"
                            type="text"
                            required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': form.errors.surname }"
                        />
                        <div v-if="form.errors.surname" class="text-red-500 text-xs italic mt-1">
                            {{ form.errors.surname }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="hpcsa_number" class="block text-gray-700 text-sm font-bold mb-2">
                            HPCSA Registration Number
                        </label>
                        <input
                            id="hpcsa_number"
                            v-model="form.hpcsa_number"
                            type="text"
                            required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': form.errors.hpcsa_number }"
                            placeholder="e.g. PS0123456"
                        />
                        <div v-if="form.errors.hpcsa_number" class="text-red-500 text-xs italic mt-1">
                            {{ form.errors.hpcsa_number }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                            Email Address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-xs italic mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <div v-if="form.errors.password" class="text-red-500 text-xs italic mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50 w-full"
                        >
                            {{ form.processing ? 'Creating Account...' : 'Create Account' }}
                        </button>
                    </div>

                    <div class="mt-6 text-center">
                        <Link
                            href="/login"
                            class="text-blue-500 hover:text-blue-800 text-sm"
                        >
                            Already have an account? Sign in here
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    surname: '',
    hpcsa_number: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post('/register')
}
</script>
