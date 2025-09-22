<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    canRegister: {
        type: Boolean,
        default: false
    },
    isAuthenticated: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <div class="bg-gray-50 py-6 lg:py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Left Content -->
                <div class="lg:pr-8">
                    <h1 class="text-4xl lg:text-5xl xl:text-6xl font-bold text-gray-900 leading-tight">
                        <span class="block">Therapeutic Recommendations</span>
                        <span class="block">Tailored to <em class="italic">YOUR</em> Client</span>
                    </h1>

                    <p class="mt-6 text-lg lg:text-xl text-gray-600 leading-relaxed">
                        Move beyond generic approaches. Get evidence-based recommendations specific to each client's unique needs.
                    </p>

                    <div class="mt-8">
                        <Link
                            v-if="canRegister && !isAuthenticated"
                            :href="route('register')"
                            class="inline-block bg-teal-500 hover:bg-teal-600 text-white font-semibold px-8 py-4 rounded-full text-lg transition-colors duration-200"
                        >
                            Get Started
                        </Link>
                        <Link
                            v-else-if="isAuthenticated"
                            :href="route('dashboard')"
                            class="inline-block bg-teal-500 hover:bg-teal-600 text-white font-semibold px-8 py-4 rounded-full text-lg transition-colors duration-200"
                        >
                            Go to Dashboard
                        </Link>
                    </div>
                </div>

                <!-- Right Content - How It Works Component Placeholder -->
                <div>
                    <slot name="how-it-works" />
                </div>
            </div>
        </div>
    </div>
</template>