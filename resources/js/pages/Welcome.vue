<template>
    <div class="min-h-screen bg-gray-100">
        <div class="relative bg-white shadow">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex justify-between items-center py-6 md:justify-start md:space-x-10">
                    <div class="flex justify-start lg:w-0 lg:flex-1">
                        <span class="text-2xl font-bold text-blue-600">CounselWise</span>
                    </div>

                    <div class="items-center justify-end md:flex md:flex-1 lg:w-0" v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="ml-8 whitespace-nowrap text-base font-medium text-gray-500 hover:text-gray-900"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="whitespace-nowrap text-base font-medium text-gray-500 hover:text-gray-900"
                            >
                                Sign in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700"
                            >
                                Sign up
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <main>
            <div class="relative">
                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gray-100" />
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="relative shadow-xl sm:rounded-2xl sm:overflow-hidden">
                        <div class="absolute inset-0">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-blue-600 mix-blend-multiply" />
                        </div>
                        <div class="relative px-6 py-16 sm:py-24 lg:py-32 lg:px-8">
                            <h1 class="text-center text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                                <span class="block text-white">Therapeutic Recommendations</span>
                                <span class="block text-blue-200">for HPCSA Counsellors</span>
                            </h1>
                            <p class="mt-6 max-w-lg mx-auto text-center text-xl text-blue-100 sm:max-w-3xl">
                                AI-powered therapeutic recommendations tailored to your clients' distinct needs.
                                Built specifically for HPCSA registered counsellors.
                            </p>
                            <div class="mt-10 max-w-sm mx-auto sm:max-w-none sm:flex sm:justify-center">
                                <div class="space-y-4 sm:space-y-0 sm:space-x-4 sm:flex">
                                    <Link
                                        v-if="canRegister && !$page.props.auth.user"
                                        :href="route('register')"
                                        class="flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-blue-700 bg-white hover:bg-blue-50 sm:px-8"
                                    >
                                        Get Started
                                    </Link>
                                    <Link
                                        v-if="$page.props.auth.user"
                                        :href="route('dashboard')"
                                        class="flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-blue-700 bg-white hover:bg-blue-50 sm:px-8"
                                    >
                                        Go to Dashboard
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features section -->
            <div class="py-16 bg-gray-50 overflow-hidden lg:py-24">
                <div class="mx-auto max-w-xl px-6 lg:max-w-7xl lg:px-8">
                    <div class="mx-auto max-w-4xl lg:mx-0">
                        <h2 class="text-3xl font-extrabold text-gray-900">
                            Professional therapeutic tools for modern counsellors
                        </h2>
                    </div>
                    <dl class="mt-12 space-y-10 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:gap-y-12 lg:grid-cols-3 lg:gap-x-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                    <!-- Icon -->
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    HPCSA Compliant
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    Built with South African professional standards and ethical guidelines in mind.
                                </dd>
                            </div>
                        </div>

                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    AI-Powered Insights
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    Get evidence-based therapeutic recommendations tailored to each client's unique needs.
                                </dd>
                            </div>
                        </div>

                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    Secure & Confidential
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    Client data protection and confidentiality at the highest standards.
                                </dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
})
</script>
