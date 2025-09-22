<script setup>
import { Link, router } from '@inertiajs/vue3'

defineProps({
    showGetStarted: {
        type: Boolean,
        default: true
    },
    isAuthenticated: {
        type: Boolean,
        default: false
    },
    user: {
        type: Object,
        default: null
    },
    authPageType: {
        type: String,
        default: null // 'login' or 'register'
    }
})

const logout = () => {
    router.post(route('logout'))
}
</script>

<template>
    <header class="bg-secondary-900 text-white px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Logo and Tagline -->
            <div class="flex items-center space-x-3">
                <Link href="/" class="nav-brand flex items-center space-x-3">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/>
                    </svg>
                    <div class="flex flex-col">
                        <span>CounselWise</span>
                        <span class="text-primary-200 text-sm italic">Bringing Wisdom to Your Practice</span>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <Link href="#" class="nav-link flex items-center">Pricing</Link>

                <!-- Unauthenticated Navigation -->
                <template v-if="!isAuthenticated">
                    <!-- Login Page Navigation -->
                    <template v-if="authPageType === 'login'">
                        <Link
                            :href="route('register')"
                            class="btn-primary btn-small flex items-center"
                        >
                            Get Started
                        </Link>
                    </template>

                    <!-- Register Page Navigation -->
                    <template v-else-if="authPageType === 'register'">
                        <Link
                            :href="route('login')"
                            class="btn-primary btn-small flex items-center"
                        >
                            Log in
                        </Link>
                    </template>

                    <!-- Default Navigation (for other pages) -->
                    <template v-else>
                        <Link :href="route('login')" class="nav-link flex items-center">Log in</Link>
                        <Link
                            v-if="showGetStarted"
                            :href="route('register')"
                            class="btn-primary btn-small flex items-center"
                        >
                            Get Started
                        </Link>
                    </template>
                </template>

                <!-- Authenticated Navigation -->
                <template v-else>
                    <button
                        @click="logout"
                        class="btn-primary btn-small flex items-center"
                    >
                        Log out
                    </button>
                </template>
            </nav>
        </div>
    </header>
</template>