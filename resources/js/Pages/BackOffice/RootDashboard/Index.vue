<template>
  <dashboard-layout title="System Overview">
    <template #default>
      <!-- Summary Cards -->
      <div class="mb-8 max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-5 gap-4">
        <card-counter 
          title="Total Clients" 
          :icon="OfficeBuildingIcon" 
          :count="stats.total_clients"
          color="blue-600"
          @click="navigateToClients"/>
        
        <card-counter 
          title="Active Clients" 
          :icon="CheckCircleIcon" 
          :count="stats.active_clients"
          color="green-600"/>
        
        <card-counter 
          title="Total Users" 
          :icon="UserGroupIcon" 
          :count="formatNumber(stats.total_users)"
          color="purple-600"/>
        
        <card-counter 
          title="Total Exams" 
          :icon="ClipboardListIcon" 
          :count="formatNumber(stats.total_exams)"
          color="indigo-600"/>
        
        <card-counter 
          title="Total Attempts" 
          :icon="TrendingUpIcon" 
          :count="formatNumber(stats.total_attempts)"
          color="orange-600"/>
      </div>

      <!-- Growth Indicators -->
      <div class="mb-8 max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Monthly Growth</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.monthly_growth }}%</p>
            </div>
            <div :class="[stats.monthly_growth >= 0 ? 'text-green-500' : 'text-red-500']">
              <TrendingUpIcon v-if="stats.monthly_growth >= 0" class="h-8 w-8"/>
              <TrendingDownIcon v-else class="h-8 w-8"/>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Active Rate</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.active_rate }}%</p>
            </div>
            <div class="text-blue-500">
              <ChartBarIcon class="h-8 w-8"/>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Avg Users/Client</p>
              <p class="text-2xl font-semibold text-gray-900">{{ Math.round(stats.avg_users_per_client) }}</p>
            </div>
            <div class="text-purple-500">
              <UserIcon class="h-8 w-8"/>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Avg Exams/Client</p>
              <p class="text-2xl font-semibold text-gray-900">{{ Math.round(stats.avg_exams_per_client) }}</p>
            </div>
            <div class="text-indigo-500">
              <AcademicCapIcon class="h-8 w-8"/>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Client Status Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Client Distribution</h3>
          <VueApexCharts 
            type="pie" 
            height="300" 
            :options="clientDistributionOptions" 
            :series="clientDistributionSeries"/>
        </div>

        <!-- Top Clients by Users -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Top Clients by Users</h3>
          <bar-chart 
            :series="topClientsByUsers.map(c => c.users_count)"
            :labels="topClientsByUsers.map(c => c.name)"/>
        </div>
      </div>

      <!-- Additional Charts Row -->
      <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Clients by Exams -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Top Clients by Exams</h3>
          <bar-chart 
            :series="topClientsByExams.map(c => c.exams_count)"
            :labels="topClientsByExams.map(c => c.name)"/>
        </div>

        <!-- Top Clients by Attempts -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Top Clients by Attempts</h3>
          <bar-chart 
            :series="topClientsByAttempts.map(c => c.attempts_count)"
            :labels="topClientsByAttempts.map(c => c.name)"/>
        </div>
      </div>

      <!-- Recent Activity Timeline -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent System Activity</h3>
          <p class="text-sm text-gray-500">Latest actions across all clients</p>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Users -->
            <div v-if="recentActivity.users.length > 0">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Users</h4>
              <div class="space-y-3">
                <div v-for="user in recentActivity.users.slice(0, 5)" :key="`user-${user.id}`" 
                     class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50">
                  <div class="flex-shrink-0">
                    <UserIcon class="h-6 w-6 text-gray-400"/>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                    <p class="text-xs text-gray-500">{{ user.client?.name || 'System' }}</p>
                  </div>
                  <div class="text-xs text-gray-400">
                    {{ moment(user.created_at).fromNow() }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Exams -->
            <div v-if="recentActivity.exams.length > 0">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Exams</h4>
              <div class="space-y-3">
                <div v-for="exam in recentActivity.exams.slice(0, 5)" :key="`exam-${exam.id}`" 
                     class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50">
                  <div class="flex-shrink-0">
                    <ClipboardListIcon class="h-6 w-6 text-gray-400"/>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ exam.name }}</p>
                    <p class="text-xs text-gray-500">{{ exam.client?.name || 'System' }}</p>
                  </div>
                  <div class="text-xs text-gray-400">
                    {{ moment(exam.created_at).fromNow() }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Attempts -->
            <div v-if="recentActivity.attempts.length > 0">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Attempts</h4>
              <div class="space-y-3">
                <div v-for="attempt in recentActivity.attempts.slice(0, 5)" :key="`attempt-${attempt.id}`" 
                     class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50">
                  <div class="flex-shrink-0">
                    <PlayIcon class="h-6 w-6 text-gray-400"/>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ attempt.exam?.name || 'Unknown' }}</p>
                    <p class="text-xs text-gray-500">{{ attempt.taker?.name || 'Anonymous' }} - {{ attempt.client?.name || 'System' }}</p>
                  </div>
                  <div class="text-xs text-gray-400">
                    {{ moment(attempt.created_at).fromNow() }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions for Root Admin -->
      <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            :href="route('back-office.clients.create')"
            class="inline-flex items-center justify-center px-4 py-3 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <PlusIcon class="-ml-1 mr-2 h-4 w-4"/>
            New Client
          </Link>
          
          <Link
            :href="route('back-office.clients.index')"
            class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <OfficeBuildingIcon class="-ml-1 mr-2 h-4 w-4"/>
            Manage Clients
          </Link>
          
          <button class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <RefreshIcon class="-ml-1 mr-2 h-4 w-4"/>
            Refresh Data
          </button>
          
          <button class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <DownloadIcon class="-ml-1 mr-2 h-4 w-4"/>
            Export Report
          </button>
        </div>
      </div>
    </template>
  </dashboard-layout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import CardCounter from '@/Components/CardCounter'
import BarChart from '@/Components/BarChart'
import VueApexCharts from "vue3-apexcharts"
import { 
  OfficeBuildingIcon, 
  CheckCircleIcon, 
  UserGroupIcon, 
  ClipboardListIcon, 
  TrendingUpIcon,
  TrendingDownIcon,
  ChartBarIcon,
  UserIcon,
  AcademicCapIcon,
  PlayIcon,
  PlusIcon,
  RefreshIcon,
  DownloadIcon
} from '@heroicons/vue/outline'
import { Link } from "@inertiajs/inertia-vue3"
import { Inertia } from "@inertiajs/inertia"
import { computed } from 'vue'
import moment from "moment"

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
  topClientsByUsers: {
    type: Array,
    default: () => []
  },
  topClientsByExams: {
    type: Array,
    default: () => []
  },
  topClientsByAttempts: {
    type: Array,
    default: () => []
  },
  topClientsByActivity: {
    type: Array,
    default: () => []
  },
  recentActivity: {
    type: Object,
    default: () => ({
      users: [],
      exams: [],
      attempts: []
    })
  }
})

// Chart configurations
const clientDistributionSeries = computed(() => [
  props.stats.active_clients,
  props.stats.total_clients - props.stats.active_clients
])

const clientDistributionOptions = {
  chart: {
    type: 'pie',
    height: 300
  },
  labels: ['Active Clients', 'Inactive Clients'],
  colors: ['#10B981', '#EF4444'],
  legend: {
    position: 'bottom'
  },
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 200
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
}

// Utility functions
const formatNumber = (number) => {
  return new Intl.NumberFormat().format(number)
}

const navigateToClients = () => {
  Inertia.visit(route('back-office.clients.index'))
}
</script>