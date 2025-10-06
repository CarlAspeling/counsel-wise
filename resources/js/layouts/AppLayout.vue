<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col">
        <nav class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/" class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            CounselWise
                        </Link>
                    </div>

                    <div class="flex items-center gap-4" v-if="$page.props.auth.user">
                        <UserAvatar
                            :user="$page.props.auth.user"
                            size="medium"
                            :ring="2"
                        />
                        <Link
                            :href="route('profile.edit')"
                            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 px-3 py-2 rounded-md"
                        >
                            Profile
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md"
                        >
                            Logout
                        </Link>
                    </div>

                    <div class="flex items-center gap-4" v-else>
                        <Link
                            :href="route('login')"
                            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 px-3 py-2 rounded-md"
                        >
                            Login
                        </Link>
                        <Link
                            :href="route('register')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
                        >
                            Register
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import UserAvatar from '@/Components/UserAvatar.vue'
</script>
