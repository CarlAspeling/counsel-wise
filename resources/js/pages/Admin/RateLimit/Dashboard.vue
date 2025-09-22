<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Rate Limit Management</h1>

                        <!-- Stats Overview -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                            <div class="bg-blue-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-blue-800 mb-2">Active Throttles</h3>
                                <p class="text-3xl font-bold text-blue-600">{{ activeThrottles.length }}</p>
                            </div>

                            <div class="bg-yellow-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Recent Violations</h3>
                                <p class="text-3xl font-bold text-yellow-600">{{ recentViolations.length }}</p>
                            </div>

                            <div class="bg-green-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-green-800 mb-2">Total Endpoints</h3>
                                <p class="text-3xl font-bold text-green-600">{{ Object.keys(stats).length }}</p>
                            </div>

                            <div class="bg-purple-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-purple-800 mb-2">Monitoring Status</h3>
                                <p class="text-3xl font-bold text-purple-600">ON</p>
                            </div>
                        </div>

                        <!-- Rate Limit Stats by Endpoint -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-8">
                            <h2 class="text-xl font-semibold mb-4">Rate Limit Configuration</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endpoint</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Attempts</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Decay Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Violations</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(config, endpoint) in stats" :key="endpoint" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ formatEndpointName(endpoint) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ config.max_attempts }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDecayTime(config.decay_seconds) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="config.current_active_violations > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                                      class="px-2 py-1 text-xs font-medium rounded">
                                                    {{ config.current_active_violations }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="text-sm font-medium text-gray-900">{{ config.success_rate }}%</span>
                                                    <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                        <div :class="getSuccessRateClass(config.success_rate)"
                                                             class="h-2 rounded-full"
                                                             :style="`width: ${config.success_rate}%`">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button @click="resetRateLimit(endpoint)"
                                                        class="text-blue-600 hover:text-blue-800 mr-3">
                                                    Reset
                                                </button>
                                                <button class="text-gray-600 hover:text-gray-800">
                                                    Configure
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <!-- Active Throttles -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold">Currently Active Throttles</h2>
                                    <a href="/admin/rate-limits/active" class="text-blue-600 hover:text-blue-800">View All</a>
                                </div>
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div v-for="throttle in activeThrottles.slice(0, 8)" :key="throttle.key" class="bg-white p-3 rounded border">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium">{{ throttle.endpoint }}</p>
                                                <p class="text-sm text-gray-600">{{ throttle.identifier }}</p>
                                                <p class="text-xs text-gray-500">
                                                    Expires: {{ formatDate(throttle.expires_at) }}
                                                </p>
                                            </div>
                                            <button @click="resetThrottle(throttle.key)"
                                                    class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded hover:bg-red-200">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="activeThrottles.length === 0" class="text-center py-4 text-gray-500">
                                        No active throttles
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Violations -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold">Recent Violations</h2>
                                    <a href="/admin/rate-limits/analytics" class="text-blue-600 hover:text-blue-800">View Analytics</a>
                                </div>
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div v-for="violation in recentViolations.slice(0, 8)" :key="violation.id" class="bg-white p-3 rounded border">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium">{{ violation.endpoint }}</p>
                                                <p class="text-sm text-gray-600">{{ violation.ip_address }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ formatDate(violation.occurred_at) }}
                                                </p>
                                            </div>
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">
                                                {{ violation.attempts }} attempts
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="recentViolations.length === 0" class="text-center py-4 text-gray-500">
                                        No recent violations
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <a href="/admin/rate-limits/stats" class="block p-4 bg-white rounded border hover:bg-gray-50 transition-colors">
                                    📊 Detailed Statistics
                                </a>
                                <a href="/admin/rate-limits/analytics" class="block p-4 bg-white rounded border hover:bg-gray-50 transition-colors">
                                    📈 Analytics & Trends
                                </a>
                                <a href="/admin/rate-limits/export" class="block p-4 bg-white rounded border hover:bg-gray-50 transition-colors">
                                    💾 Export Data
                                </a>
                                <button @click="resetAllRateLimits" class="block w-full p-4 bg-white rounded border hover:bg-gray-50 transition-colors text-left">
                                    🔄 Reset All Limits
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    stats: Object,
    activeThrottles: Array,
    recentViolations: Array,
    refreshInterval: Number
})

const formatEndpointName = (endpoint) => {
    return endpoint.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const formatDecayTime = (seconds) => {
    const minutes = Math.floor(seconds / 60)
    if (minutes >= 60) {
        const hours = Math.floor(minutes / 60)
        return `${hours}h ${minutes % 60}m`
    }
    return `${minutes}m`
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}

const getSuccessRateClass = (rate) => {
    if (rate >= 90) return 'bg-green-500'
    if (rate >= 70) return 'bg-yellow-500'
    return 'bg-red-500'
}

const resetRateLimit = (endpoint) => {
    if (confirm(`Reset rate limits for ${formatEndpointName(endpoint)}?`)) {
        router.post('/admin/rate-limits/reset', {
            endpoint: endpoint
        }, {
            preserveState: true,
            onSuccess: () => {
                // Refresh the page data
                router.reload({ only: ['stats', 'activeThrottles', 'recentViolations'] })
            }
        })
    }
}

const resetThrottle = (key) => {
    if (confirm('Reset this specific throttle?')) {
        router.post('/admin/rate-limits/reset', {
            key: key
        }, {
            preserveState: true,
            onSuccess: () => {
                router.reload({ only: ['activeThrottles'] })
            }
        })
    }
}

const resetAllRateLimits = () => {
    if (confirm('Reset ALL rate limits? This will clear all current throttles.')) {
        router.post('/admin/rate-limits/reset', {
            all: true
        }, {
            preserveState: true,
            onSuccess: () => {
                router.reload()
            }
        })
    }
}
</script>