<script setup>
import { Link } from '@inertiajs/vue3'
import SiteHeader from '@/Components/Layout/SiteHeader.vue'
import SiteFooter from '@/Components/Layout/SiteFooter.vue'
import HeroSection from '@/Components/Landing/HeroSection.vue'
import HowItWorks from '@/Components/Landing/HowItWorks.vue'

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
})
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col">
        <!-- Header -->
        <SiteHeader
            :is-authenticated="!!$page.props.auth.user"
            :user="$page.props.auth.user"
        />

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50">
            <!-- Hero Section -->
            <HeroSection
                :can-register="canRegister"
                :is-authenticated="!!$page.props.auth.user"
            >
                <template #how-it-works>
                    <HowItWorks />
                </template>
            </HeroSection>
        </main>

        <!-- Footer -->
        <SiteFooter />
    </div>
</template>