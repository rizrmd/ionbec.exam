<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {computed, onMounted, reactive, ref, toRefs} from 'vue'
import {SearchIcon, EyeIcon, DownloadIcon, ChevronDoubleRightIcon} from '@heroicons/vue/outline'
import {Inertia} from "@inertiajs/inertia";
import {Link, useForm} from "@inertiajs/inertia-vue3";
import Pagination from '@/Components/Pagination.vue'
import route from "@/Libs/ziggy";
import moment from "moment";

const props = defineProps({
  logs: {
    type: Object,
  },
  stats: {
    type: Object,
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const {logs, stats, filters: initialFilters} = toRefs(props)

const filters = reactive({
  date_from: initialFilters.value?.date_from || null,
  date_to: initialFilters.value?.date_to || null,
  ip_address: initialFilters.value?.ip_address || null,
  suspicious_only: initialFilters.value?.suspicious_only || false,
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const clearFilters = () => {
  filters.date_from = null
  filters.date_to = null
  filters.ip_address = null
  filters.suspicious_only = false
  Inertia.reload({
    data: {
      page: 1
    },
  })
}

const exportLogs = () => {
  window.location.href = route('admin.exam-logs.export', filters)
}

const isSuspiciousIp = (ipAddress) => {
  // Simple heuristic for suspicious IPs - you can enhance this
  const suspiciousPatterns = [
    /^10\./, // Private network
    /^192\.168\./, // Private network
    /^172\.(1[6-9]|2[0-9]|3[0-1])\./, // Private network
    /^127\./, // Loopback
  ]
  return suspiciousPatterns.some(pattern => pattern.test(ipAddress))
}

const formatDate = (date) => {
  return moment(date).format('MMM D, YYYY HH:mm:ss')
}

const getTabCountColor = (tabCount) => {
  if (tabCount === 1) return 'bg-green-100 text-green-800'
  if (tabCount === 2) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

onMounted(() => {
  // Any initialization if needed
})
</script>

<template>
  <DashboardLayout title="Exam Logs">
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Exam Session Logs</h1>
        <button @click="exportLogs"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
          <DownloadIcon class="h-4 w-4 mr-2"/>
          Export CSV
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                  <div class="w-4 h-4 bg-white rounded-sm"></div>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Logs</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.total_logs?.toLocaleString() || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                  <ChevronDoubleRightIcon class="w-4 h-4 text-white"/>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Multiple Tabs</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.multiple_tab_logs?.toLocaleString() || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                  <div class="w-2 h-2 bg-white rounded-full"></div>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Unique IPs</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.unique_ips?.toLocaleString() || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                  <div class="w-0 h-0 border-l-4 border-r-4 border-b-4 border-transparent border-b-white"></div>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Today's Logs</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.today_logs?.toLocaleString() || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filters</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
              <input type="date"
                     id="date_from"
                     v-model="filters.date_from"
                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
              <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
              <input type="date"
                     id="date_to"
                     v-model="filters.date_to"
                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
              <label for="ip_address" class="block text-sm font-medium text-gray-700">IP Address</label>
              <input type="text"
                     id="ip_address"
                     v-model="filters.ip_address"
                     placeholder="Search IP..."
                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="flex items-end">
              <div class="flex items-center h-5">
                <input id="suspicious_only"
                       type="checkbox"
                       v-model="filters.suspicious_only"
                       true-value="true"
                       false-value="false"
                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
              </div>
              <label for="suspicious_only" class="ml-2 block text-sm text-gray-900">
                Show suspicious only
              </label>
            </div>
          </div>
          <div class="mt-4 flex gap-2">
            <button @click="search"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <SearchIcon class="h-4 w-4 mr-2"/>
              Apply Filters
            </button>
            <button @click="clearFilters"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Session Logs</h3>
        </div>

        <div class="border-t border-gray-200">
          <div v-if="logs.data.length === 0" class="text-center py-12">
            <div class="text-gray-400">
              <SearchIcon class="mx-auto h-12 w-12"/>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No logs found</h3>
            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to find what you're looking for.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Key</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tab Count</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISP</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(log, index) in logs.data" :key="`log-${index}`"
                    :class="log.tab_count > 1 ? 'bg-red-50' : 'hover:bg-gray-50'">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(log.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      {{ log.attempt?.taker?.name || 'Unknown' }}
                    </div>
                    <div v-if="log.attempt_id" class="text-sm text-gray-500">
                      Attempt #{{ log.attempt_id }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                    {{ log.session_key ? log.session_key.substring(0, 20) + '...' : '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="font-mono text-gray-900">{{ log.ip_address || '-' }}</div>
                    <div v-if="log.ip_address && isSuspiciousIp(log.ip_address)" class="text-xs text-red-600">
                      🚨 Suspicious
                    </div>
                    <div v-else-if="log.ip_address" class="text-xs text-green-600">
                      ✅ Normal
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ [log.city, log.country].filter(Boolean).join(', ') || '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getTabCountColor(log.tab_count)]">
                      {{ log.tab_count }} tab{{ log.tab_count > 1 ? 's' : '' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ log.isp ? log.isp.substring(0, 30) + (log.isp.length > 30 ? '...' : '') : '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link :href="`/admin/exam-logs/${log.id}`"
                          class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                      <EyeIcon class="h-4 w-4"/>
                      <span class="ml-1">View</span>
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="logs.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <!-- Mobile pagination -->
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ logs.from || 0 }}</span>
                    to
                    <span class="font-medium">{{ logs.to || 0 }}</span>
                    of
                    <span class="font-medium">{{ logs.total }}</span>
                    results
                  </p>
                </div>
                <div>
                  <Pagination :pagination-data="logs"/>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>