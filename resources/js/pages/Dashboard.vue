<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-blue-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-blue-800 mb-2">Total Clients</h3>
                                <p class="text-3xl font-bold text-blue-600">0</p>
                            </div>

                            <div class="bg-green-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-green-800 mb-2">Active Sessions</h3>
                                <p class="text-3xl font-bold text-green-600">0</p>
                            </div>

                            <div class="bg-purple-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-purple-800 mb-2">Recommendations</h3>
                                <p class="text-3xl font-bold text-purple-600">0</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-xl font-semibold mb-4">Welcome to CounselWise</h2>
                            <p class="text-gray-600 mb-4">
                                Your platform for AI-powered therapeutic recommendations tailored to your clients' needs.
                            </p>
                            <p class="text-sm text-gray-500">
                                HPCSA Registration: {{ $page.props.auth.user.hpcsa_number || 'Not provided' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>
