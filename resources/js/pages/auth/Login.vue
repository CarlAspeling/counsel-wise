<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign in to CounselWise
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    For HPCSA registered counsellors
                </p>
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
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

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="mr-2"
                            />
                            <span class="text-sm text-gray-700">Remember me</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                        >
                            {{ form.processing ? 'Signing in...' : 'Sign In' }}
                        </button>

                        <Link
                            href="/forgot-password"
                            class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <div class="mt-6 text-center">
                        <Link
                            href="/register"
                            class="text-blue-500 hover:text-blue-800 text-sm"
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
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post('/login')
}
</script>
