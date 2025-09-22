<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Suspicious Activity Analysis</h1>

                        <div class="space-y-8">
                            <!-- Suspicious IPs -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h2 class="text-xl font-semibold mb-4">Suspicious IP Addresses</h2>
                                <div v-if="suspiciousIPs.length > 0" class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Threat Level</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patterns</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="ip in suspiciousIPs" :key="ip.ip_address" class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ ip.ip_address }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ ip.location }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ formatDate(ip.last_activity) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="getThreatLevelClass(ip.threat_level)" class="px-2 py-1 text-xs font-medium rounded">
                                                        {{ ip.threat_level }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span v-if="ip.should_block" class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">
                                                        Should Block
                                                    </span>
                                                    <span v-else class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                                        Monitoring
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm">
                                                        <div v-for="pattern in ip.patterns.filter(p => p.is_suspicious)" :key="pattern.type" class="mb-1">
                                                            <span class="font-medium">{{ formatPatternType(pattern.type) }}:</span>
                                                            <span :class="getSeverityClass(pattern.severity)" class="ml-2 px-1 py-0.5 text-xs rounded">
                                                                {{ pattern.severity }}
                                                            </span>
                                                        </div>
                                                        <div v-if="ip.patterns.filter(p => p.is_suspicious).length === 0" class="text-gray-400">
                                                            No suspicious patterns
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="text-center py-8 text-gray-500">
                                    No suspicious IP addresses detected in the last 24 hours.
                                </div>
                            </div>

                            <!-- Suspicious Users -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h2 class="text-xl font-semibold mb-4">Suspicious User Activity</h2>
                                <div v-if="suspiciousUsers.length > 0" class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Security Score</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Threat Level</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patterns</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="user in suspiciousUsers" :key="user.user.id" class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ user.user.name }}</p>
                                                        <p class="text-sm text-gray-500">{{ user.user.email }}</p>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ formatDate(user.last_activity) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <span class="text-sm font-medium text-gray-900">{{ user.security_score }}/100</span>
                                                        <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                            <div :class="getScoreBarClass(user.security_score)"
                                                                 class="h-2 rounded-full"
                                                                 :style="`width: ${user.security_score}%`">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="getThreatLevelClass(user.threat_level)" class="px-2 py-1 text-xs font-medium rounded">
                                                        {{ user.threat_level }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm">
                                                        <div v-for="pattern in user.patterns.filter(p => p.is_suspicious)" :key="pattern.type" class="mb-1">
                                                            <span class="font-medium">{{ formatPatternType(pattern.type) }}:</span>
                                                            <span :class="getSeverityClass(pattern.severity)" class="ml-2 px-1 py-0.5 text-xs rounded">
                                                                {{ pattern.severity }}
                                                            </span>
                                                        </div>
                                                        <div v-if="user.patterns.filter(p => p.is_suspicious).length === 0" class="text-gray-400">
                                                            No suspicious patterns
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="text-center py-8 text-gray-500">
                                    No suspicious user activity detected in the last 24 hours.
                                </div>
                            </div>

                            <!-- Blocked IPs -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h2 class="text-xl font-semibold mb-4">Currently Blocked IPs</h2>
                                <div v-if="blockedIPs.length > 0" class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Threat Level</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="ip in blockedIPs" :key="ip.ip_address" class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ ip.ip_address }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ ip.location }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ formatDate(ip.last_activity) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="getThreatLevelClass(ip.threat_level)" class="px-2 py-1 text-xs font-medium rounded">
                                                        {{ ip.threat_level }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <button class="text-blue-600 hover:text-blue-800 mr-3">Review</button>
                                                    <button class="text-red-600 hover:text-red-800">Unblock</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="text-center py-8 text-gray-500">
                                    No IPs are currently blocked.
                                </div>
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
    suspiciousIPs: Array,
    suspiciousUsers: Array,
    blockedIPs: Array
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}

const formatPatternType = (type) => {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const getThreatLevelClass = (level) => {
    const classes = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-orange-100 text-orange-800',
        'critical': 'bg-red-100 text-red-800'
    }
    return classes[level] || 'bg-gray-100 text-gray-800'
}

const getSeverityClass = (severity) => {
    const classes = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-red-100 text-red-800'
    }
    return classes[severity] || 'bg-gray-100 text-gray-800'
}

const getScoreBarClass = (score) => {
    if (score >= 70) return 'bg-red-500'
    if (score >= 40) return 'bg-yellow-500'
    return 'bg-green-500'
}
</script>