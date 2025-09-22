<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                            <div class="bg-blue-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-blue-800 mb-2">Total Users</h3>
                                <p class="text-3xl font-bold text-blue-600">0</p>
                            </div>

                            <div class="bg-green-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-green-800 mb-2">Active Sessions</h3>
                                <p class="text-3xl font-bold text-green-600">0</p>
                            </div>

                            <div class="bg-yellow-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Security Events (24h)</h3>
                                <p class="text-3xl font-bold text-yellow-600">0</p>
                            </div>

                            <div class="bg-red-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-red-800 mb-2">Rate Limit Violations</h3>
                                <p class="text-3xl font-bold text-red-600">0</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                                <div class="space-y-3">
                                    <a href="/admin/users" class="block p-3 bg-white rounded border hover:bg-gray-50 transition-colors">
                                        👥 Manage Users
                                    </a>
                                    <a href="/admin/security" class="block p-3 bg-white rounded border hover:bg-gray-50 transition-colors">
                                        🛡️ Security Dashboard
                                    </a>
                                    <a href="/admin/rate-limits" class="block p-3 bg-white rounded border hover:bg-gray-50 transition-colors">
                                        ⚡ Rate Limit Management
                                    </a>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h2 class="text-xl font-semibold mb-4">System Status</h2>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Application Status</span>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">Online</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Security Monitoring</span>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">Active</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Rate Limiting</span>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">Enabled</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-xl font-semibold mb-4">Admin Access</h2>
                            <p class="text-gray-600 mb-4">
                                You have super-admin privileges to manage all aspects of the CounselWise platform.
                            </p>
                            <p class="text-sm text-gray-500">
                                Admin User: {{ $page.props.auth.user.name }} ({{ $page.props.auth.user.email }})
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