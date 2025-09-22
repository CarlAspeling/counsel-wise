<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Security Dashboard</h1>

                        <!-- 24 Hour Stats -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Last 24 Hours</h2>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="bg-blue-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Total Events</h3>
                                    <p class="text-3xl font-bold text-blue-600">{{ stats.total_events_24h }}</p>
                                </div>

                                <div class="bg-yellow-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Failed Logins</h3>
                                    <p class="text-3xl font-bold text-yellow-600">{{ stats.failed_logins_24h }}</p>
                                </div>

                                <div class="bg-red-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-red-800 mb-2">Suspicious Events</h3>
                                    <p class="text-3xl font-bold text-red-600">{{ stats.suspicious_events_24h }}</p>
                                </div>

                                <div class="bg-purple-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-purple-800 mb-2">Unique IPs</h3>
                                    <p class="text-3xl font-bold text-purple-600">{{ stats.unique_ips_24h }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- 7 Day Stats -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Last 7 Days</h2>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="bg-blue-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Total Events</h3>
                                    <p class="text-3xl font-bold text-blue-600">{{ stats.total_events_7d }}</p>
                                </div>

                                <div class="bg-yellow-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Failed Logins</h3>
                                    <p class="text-3xl font-bold text-yellow-600">{{ stats.failed_logins_7d }}</p>
                                </div>

                                <div class="bg-red-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-red-800 mb-2">Suspicious Events</h3>
                                    <p class="text-3xl font-bold text-red-600">{{ stats.suspicious_events_7d }}</p>
                                </div>

                                <div class="bg-purple-50 p-6 rounded-lg">
                                    <h3 class="text-lg font-semibold text-purple-800 mb-2">Unique IPs</h3>
                                    <p class="text-3xl font-bold text-purple-600">{{ stats.unique_ips_7d }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <!-- Recent Events -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold">Recent Security Events</h2>
                                    <a href="/admin/security/events" class="text-blue-600 hover:text-blue-800">View All</a>
                                </div>
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div v-for="event in recentEvents.slice(0, 10)" :key="event.id" class="bg-white p-3 rounded border">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium">{{ formatEventType(event.event_type) }}</p>
                                                <p class="text-sm text-gray-600">{{ event.description }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ event.user ? event.user.email : event.email || 'Unknown' }} •
                                                    {{ event.ip_address }} •
                                                    {{ formatDate(event.occurred_at) }}
                                                </p>
                                            </div>
                                            <span :class="getSeverityClass(event.severity)" class="px-2 py-1 text-xs rounded">
                                                {{ event.severity }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="recentEvents.length === 0" class="text-center py-4 text-gray-500">
                                        No recent security events
                                    </div>
                                </div>
                            </div>

                            <!-- Top Threats -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold">Top Threats (7 days)</h2>
                                    <a href="/admin/security/suspicious" class="text-blue-600 hover:text-blue-800">View All</a>
                                </div>
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div v-for="threat in topThreats.slice(0, 8)" :key="threat.ip_address" class="bg-white p-3 rounded border">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium">{{ threat.ip_address }}</p>
                                                <p class="text-sm text-gray-600">{{ threat.location }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium text-red-600">{{ threat.event_count }} events</p>
                                                <span v-if="threat.is_blocked" class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">
                                                    Blocked
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="topThreats.length === 0" class="text-center py-4 text-gray-500">
                                        No threats detected
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <a href="/admin/security/events" class="block p-4 bg-white rounded border hover:bg-gray-50 transition-colors">
                                    📋 View All Security Events
                                </a>
                                <a href="/admin/security/suspicious" class="block p-4 bg-white rounded border hover:bg-gray-50 transition-colors">
                                    🚨 Suspicious Activity Analysis
                                </a>
                                <a href="/admin/rate-limits" class="block p-4 bg-white rounded border hover:bg-gray-50 transition-colors">
                                    ⚡ Rate Limit Management
                                </a>
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

const props = defineProps({
    stats: Object,
    recentEvents: Array,
    topThreats: Array,
    alertsOverTime: Array
})

const formatEventType = (type) => {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}

const getSeverityClass = (severity) => {
    const classes = {
        'info': 'bg-blue-100 text-blue-800',
        'notice': 'bg-green-100 text-green-800',
        'warning': 'bg-yellow-100 text-yellow-800',
        'alert': 'bg-orange-100 text-orange-800',
        'critical': 'bg-red-100 text-red-800'
    }
    return classes[severity] || 'bg-gray-100 text-gray-800'
}
</script>