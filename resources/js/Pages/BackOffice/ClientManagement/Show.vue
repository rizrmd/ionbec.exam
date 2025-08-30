<template>
  <dashboard-layout :title="`Client: ${client.name}`">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">{{ client.name }}</h1>
          <p class="text-sm text-gray-500 mt-1">{{ client.slug }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="toggleStatus"
            :class="[
              client.is_active 
                ? 'bg-red-600 hover:bg-red-700 text-white' 
                : 'bg-green-600 hover:bg-green-700 text-white',
              'inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2'
            ]"
            :disabled="toggleForm.processing">
            {{ client.is_active ? 'Deactivate' : 'Activate' }}
          </button>
          <Link
            :href="route('back-office.clients.edit', client.id)"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <PencilAltIcon class="-ml-1 mr-2 h-4 w-4"/>
            Edit Client
          </Link>
          <Link
            :href="route('back-office.clients.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            Back to Clients
          </Link>
        </div>
      </div>
    </template>

    <template #default>
      <JetBanner v-if="$page.props.flash.success" type="success" :message="$page.props.flash.success" class="mb-4"/>
      <JetBanner v-if="$page.props.flash.error" type="error" :message="$page.props.flash.error" class="mb-4"/>

      <div class="space-y-6">
        <!-- Client Information -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Client Information</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Basic details and configuration.</p>
          </div>
          <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ client.name }}</dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Slug</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ client.slug }}</dd>
              </div>
              <div v-if="client.logo_url" class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Logo</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <img :src="client.logo_url" :alt="client.name + ' logo'" class="h-20 w-auto">
                </dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                  <span 
                    :class="[
                      client.is_active 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800',
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                    ]">
                    {{ client.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Domains</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <div class="flex flex-wrap gap-2">
                    <span 
                      v-for="domain in client.domains" 
                      :key="domain"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800">
                      {{ domain }}
                    </span>
                  </div>
                </dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Primary Contact Email</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <a :href="`mailto:${client.primary_contact_email}`" class="text-primary-600 hover:text-primary-900">
                    {{ client.primary_contact_email }}
                  </a>
                </dd>
              </div>
              <div v-if="client.primary_contact_phone" class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Primary Contact Phone</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ client.primary_contact_phone }}</dd>
              </div>
              <div v-if="client.notes" class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <div class="whitespace-pre-wrap">{{ client.notes }}</div>
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Statistics</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Usage and activity metrics for this client.</p>
          </div>
          <div class="border-t border-gray-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 p-6">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_users }}</div>
                <div class="text-sm text-gray-500">Total Users</div>
                <div class="text-xs text-gray-400 mt-1">{{ stats.active_users }} verified</div>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_exams }}</div>
                <div class="text-sm text-gray-500">Total Exams</div>
                <div class="text-xs text-gray-400 mt-1">{{ stats.published_exams }} published</div>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_takers }}</div>
                <div class="text-sm text-gray-500">Total Takers</div>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_attempts }}</div>
                <div class="text-sm text-gray-500">Total Attempts</div>
                <div class="text-xs text-gray-400 mt-1">{{ stats.completed_attempts }} completed</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Data -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Related Data</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Quick access to related information.</p>
          </div>
          <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="text-center border rounded-lg p-4 hover:bg-gray-50 cursor-pointer">
                <div class="text-lg font-semibold text-gray-900">{{ stats.total_users }}</div>
                <div class="text-sm text-gray-500">Users</div>
                <div class="text-xs text-primary-600 mt-2">View Users →</div>
              </div>
              
              <div class="text-center border rounded-lg p-4 hover:bg-gray-50 cursor-pointer">
                <div class="text-lg font-semibold text-gray-900">{{ stats.total_exams }}</div>
                <div class="text-sm text-gray-500">Exams</div>
                <div class="text-xs text-primary-600 mt-2">View Exams →</div>
              </div>
              
              <div class="text-center border rounded-lg p-4 hover:bg-gray-50 cursor-pointer">
                <div class="text-lg font-semibold text-gray-900">{{ stats.total_takers }}</div>
                <div class="text-sm text-gray-500">Takers</div>
                <div class="text-xs text-primary-600 mt-2">View Takers →</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Timestamps -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Timestamps</h3>
          </div>
          <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  {{ formatDate(client.created_at) }}
                </dd>
              </div>
              <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  {{ formatDate(client.updated_at) }}
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </template>
  </dashboard-layout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { PencilAltIcon } from '@heroicons/vue/outline'
import { Link, useForm } from "@inertiajs/inertia-vue3"
import JetBanner from '@/Jetstream/Banner'

const props = defineProps({
  client: {
    type: Object,
    required: true,
  },
  stats: {
    type: Object,
    required: true,
  }
})

const toggleForm = useForm({})

const toggleStatus = () => {
  toggleForm.patch(route('back-office.clients.toggle-status', props.client.id), {
    preserveScroll: true,
  })
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>