<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/" class="text-xl font-semibold text-gray-900">
                            CounselWise
                        </Link>
                    </div>

                    <div class="flex items-center space-x-4" v-if="$page.props.auth.user">
                        <span class="text-gray-700">{{ $page.props.auth.user.name }}</span>
                        <Link
                            href="/profile"
                            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md"
                        >
                            Profile
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md"
                        >
                            Logout
                        </Link>
                    </div>

                    <div class="flex items-center space-x-4" v-else>
                        <Link
                            href="/login"
                            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md"
                        >
                            Login
                        </Link>
                        <Link
                            href="/register"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
                        >
                            Register
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
