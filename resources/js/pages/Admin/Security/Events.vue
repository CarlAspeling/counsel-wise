<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Security Events</h1>

                        <!-- Filters -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h2 class="text-lg font-semibold mb-4">Filters</h2>
                            <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                                    <select v-model="filters.event_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">All Types</option>
                                        <option v-for="type in eventTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                                    <select v-model="filters.severity" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">All Severities</option>
                                        <option v-for="severity in severityLevels" :key="severity" :value="severity">
                                            {{ severity.charAt(0).toUpperCase() + severity.slice(1) }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                    <input v-model="filters.date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                    <input v-model="filters.date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                                    <input v-model="filters.ip_address" type="text" placeholder="Search IP..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-3 lg:col-span-5 flex gap-2">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                        Apply Filters
                                    </button>
                                    <button type="button" @click="clearFilters" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition-colors">
                                        Clear
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Events Table -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User/Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP & Location</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="event in events.data" :key="event.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ formatEventType(event.event_type) }}</p>
                                                    <p class="text-sm text-gray-500">{{ event.description }}</p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div v-if="event.user">
                                                    <p class="text-sm font-medium text-gray-900">{{ event.user.name }}</p>
                                                    <p class="text-sm text-gray-500">{{ event.user.email }}</p>
                                                </div>
                                                <div v-else-if="event.email">
                                                    <p class="text-sm text-gray-500">{{ event.email }}</p>
                                                </div>
                                                <div v-else>
                                                    <p class="text-sm text-gray-400">Unknown</p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ event.ip_address }}</p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ event.city && event.country ? `${event.city}, ${event.country}` : (event.country || 'Unknown') }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="getSeverityClass(event.severity)" class="px-2 py-1 text-xs font-medium rounded">
                                                    {{ event.severity }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(event.occurred_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div v-if="events.links && events.links.length > 3" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <a v-if="events.prev_page_url" :href="events.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </a>
                                    <a v-if="events.next_page_url" :href="events.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </a>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing {{ events.from }} to {{ events.to }} of {{ events.total }} results
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            <a v-for="link in events.links" :key="link.label"
                                               :href="link.url"
                                               :class="[
                                                   'relative inline-flex items-center px-2 py-2 border text-sm font-medium',
                                                   link.active
                                                       ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                       : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                   !link.url ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50'
                                               ]"
                                               v-html="link.label">
                                            </a>
                                        </nav>
                                    </div>
                                </div>
                            </div>

                            <div v-if="events.data.length === 0" class="text-center py-8 text-gray-500">
                                No security events found matching your criteria.
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
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    events: Object,
    filters: Object,
    eventTypes: Array,
    severityLevels: Array
})

const filters = ref({ ...props.filters })

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

const applyFilters = () => {
    router.get('/admin/security/events', filters.value, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    filters.value = {
        event_type: '',
        severity: '',
        date_from: '',
        date_to: '',
        ip_address: ''
    }
    applyFilters()
}
</script>