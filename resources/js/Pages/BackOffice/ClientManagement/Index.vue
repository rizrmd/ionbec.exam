<template>
  <dashboard-layout title="Manage Clients">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Client Management</h1>
        <Link
          :href="route('back-office.clients.create')"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Create New Client
        </Link>
      </div>
    </template>

    <template #default>
      <JetBanner v-if="$page.props.flash?.success" type="success" :message="$page.props.flash.success" class="mb-4"/>
      <JetBanner v-if="$page.props.flash?.error" type="error" :message="$page.props.flash.error" class="mb-4"/>
      
      <div class="flex flex-row gap-4">
        <input 
          id="search-name" 
          v-model="filters.search" 
          autocomplete="client-name"
          class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
          name="search"
          placeholder="Search name, slug, or email"
          type="text">

        <select 
          id="status" 
          v-model="filters.status"
          class="flex-1 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
          name="status">
          <option :value="null">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        
        <button
          class="flex-none inline-flex justify-center items-center px-4 py-1.5 mt-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          type="button"
          @click="search">
          <SearchIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Search
        </button>
      </div>
      
      <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                <tr>
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-20" scope="col">NO</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">LOGO</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">SLUG</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">DOMAINS</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">STATUS</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">STATS</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(client, index) in clients.data" :key="`client-${client.id}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ clients.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4">
                    <img v-if="client.logo_url" :src="client.logo_url" :alt="client.name + ' logo'" class="h-10 w-auto">
                    <span v-else class="text-sm text-gray-400">No logo</span>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">
                    {{ client.name }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ client.slug }}
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500">
                    <div class="flex flex-wrap gap-1">
                      <span 
                        v-for="domain in client.domains" 
                        :key="domain"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                        {{ domain }}
                      </span>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <button
                      @click="toggleStatus(client)"
                      :class="[
                        client.is_active 
                          ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                          : 'bg-red-100 text-red-800 hover:bg-red-200',
                        'inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium cursor-pointer'
                      ]">
                      {{ client.is_active ? 'Active' : 'Inactive' }}
                    </button>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <div class="text-xs">
                      <div>{{ client.users_count }} users</div>
                      <div>{{ client.exams_count }} exams</div>
                      <div>{{ client.takers_count }} takers</div>
                    </div>
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <div class="flex justify-end space-x-2">
                      <button
                        @click="goToUsers(client.id)"
                        class="text-green-600 hover:text-green-900"
                        title="Manage users">
                        <UserGroupIcon class="h-5 w-5" aria-hidden="true"/>
                      </button>
                      <button
                        @click="openCloneModal(client)"
                        class="text-purple-600 hover:text-purple-900"
                        title="Clone client">
                        <DocumentDuplicateIcon class="h-5 w-5" aria-hidden="true"/>
                      </button>
                      <Link
                        :href="route('back-office.clients.edit', client.id)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Edit client">
                        <PencilAltIcon class="h-5 w-5" aria-hidden="true"/>
                      </Link>
                    </div>
                  </td>
                </tr>
                <tr v-if="(filters.search || filters.status) && clients.data.length === 0">
                  <td colspan="8">
                    <div class="text-center w-full my-4">No clients found matching your filters.</div>
                  </td>
                </tr>
                <tr v-else-if="clients.data.length === 0">
                  <td colspan="8">
                    <div class="text-center w-full my-4">No clients yet. Create your first client.</div>
                  </td>
                </tr>
                </tbody>
              </table>
              <pagination :pagination-data="clients"/>
            </div>
          </div>
        </div>
      </div>
    </template>
  </dashboard-layout>

  <JetConfirmationModal :show="isDeleting" items="center">
    <template #title>
      Delete Client {{ deletingClient?.name }}
    </template>

    <template #content>
      This action cannot be undone. All data associated with this client will be permanently deleted.
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="cancelDelete">Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          type="submit"
          :disabled="deleteForm.processing"
          @click="performDelete">
          Yes, Delete Client
        </button>
      </div>
    </template>
  </JetConfirmationModal>

  <!-- Clone Client Modal -->
  <CloneClientModal 
    :show="showCloneModal" 
    :client="cloningClient || {}"
    @close="closeCloneModal"
    @cloned="handleClientCloned"
  />
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { PlusIcon, SearchIcon, PencilAltIcon, UserGroupIcon, DocumentDuplicateIcon } from '@heroicons/vue/outline'
import { computed, reactive, ref, onMounted } from 'vue'
import { Inertia } from "@inertiajs/inertia"
import { Link, useForm } from "@inertiajs/inertia-vue3"
import Pagination from '@/Components/Pagination.vue'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetBanner from '@/Jetstream/Banner'
import CloneClientModal from '@/Components/CloneClientModal.vue'
import urlParser from "@/Libs/urlParser"

const props = defineProps({
  clients: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filters = reactive({
  search: props.filters.search || null,
  status: props.filters.status || null,
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const deleteForm = useForm({})
const deletingClient = ref(null)
const isDeleting = computed(() => deletingClient.value !== null)

// Clone modal state
const showCloneModal = ref(false)
const cloningClient = ref(null)

const confirmDelete = (client) => {
  deletingClient.value = client
}

const cancelDelete = () => {
  deletingClient.value = null
}

const performDelete = () => {
  deleteForm.delete(route('back-office.clients.destroy', deletingClient.value.id), {
    onSuccess: () => {
      deletingClient.value = null
    }
  })
}

const toggleStatus = (client) => {
  const toggleForm = useForm({})
  toggleForm.patch(route('back-office.clients.toggle-status', client.id), {
    onSuccess: () => {
      // The page will be refreshed automatically by Inertia
    }
  })
}

const goToUsers = (clientId) => {
  Inertia.visit(route('back-office.users.index', { client_id: clientId }))
}

// Clone modal functions
const openCloneModal = (client) => {
  cloningClient.value = client
  showCloneModal.value = true
}

const closeCloneModal = () => {
  showCloneModal.value = false
  cloningClient.value = null
}

const handleClientCloned = () => {
  // Refresh the page to show the new client
  Inertia.reload()
}

onMounted(() => {
  const urlParams = urlParser()
  filters.search = urlParams.search
  filters.status = urlParams.status || null
})
</script>