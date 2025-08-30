<template>
  <dashboard-layout title="Manage Users">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">
          User Management{{ currentClient ? ` - ${currentClient.name}` : '' }}
        </h1>
        <Link
          :href="getCreateUrl()"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Create New User
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
          autocomplete="user-name"
          class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
          name="search"
          placeholder="Search name, email, or role"
          type="text">

        <select 
          id="role" 
          v-model="filters.role"
          class="flex-1 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
          name="role">
          <option :value="null">All Roles</option>
          <option value="administrator">Administrator</option>
          <option value="scorer">Scorer</option>
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
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">EMAIL</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">ROLES</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">STATUS</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">CREATED</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(user, index) in (users?.data || [])" :key="`user-${user.id || index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ (users?.from || 1) + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">
                    {{ user.name }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ user.email }}
                  </td>
                  <td class="px-3 py-4 text-sm text-gray-500">
                    <div class="flex flex-wrap gap-1">
                      <span 
                        v-for="role in user.roles" 
                        :key="role.id"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                        {{ role.name }}
                      </span>
                      <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-gray-400">No roles</span>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span
                      :class="[
                        user.email_verified_at 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-yellow-100 text-yellow-800',
                        'inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium'
                      ]">
                      {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ new Date(user.created_at).toLocaleDateString() }}
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <div class="flex justify-end space-x-2">
                      <Link
                        :href="getUserEditUrl(user.id || user.email)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Edit user">
                        <PencilAltIcon class="h-5 w-5" aria-hidden="true"/>
                      </Link>
                      <button
                        @click="confirmDelete(user)"
                        class="text-red-600 hover:text-red-900"
                        title="Delete user">
                        <TrashIcon class="h-5 w-5" aria-hidden="true"/>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="(filters.search || filters.role) && (!users?.data || users.data.length === 0)">
                  <td colspan="7">
                    <div class="text-center w-full my-4">No users found matching your filters.</div>
                  </td>
                </tr>
                <tr v-else-if="!users?.data || users.data.length === 0">
                  <td colspan="7">
                    <div class="text-center w-full my-4">No users yet. Create your first user.</div>
                  </td>
                </tr>
                </tbody>
              </table>
              <pagination v-if="users?.links && users.links.length > 3" :pagination-data="users"/>
            </div>
          </div>
        </div>
      </div>
    </template>
  </dashboard-layout>

  <JetConfirmationModal :show="isDeleting" items="center">
    <template #title>
      Delete User {{ deletingUser?.name }}
    </template>

    <template #content>
      This action cannot be undone. The user will be permanently deleted from the system.
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
          Yes, Delete User
        </button>
      </div>
    </template>
  </JetConfirmationModal>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { PlusIcon, SearchIcon, PencilAltIcon, TrashIcon } from '@heroicons/vue/outline'
import { computed, reactive, ref, onMounted } from 'vue'
import { Inertia } from "@inertiajs/inertia"
import { Link, useForm } from "@inertiajs/inertia-vue3"
import Pagination from '@/Components/Pagination.vue'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetBanner from '@/Jetstream/Banner'
import urlParser from "@/Libs/urlParser"

const props = defineProps({
  users: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  currentClient: {
    type: Object,
    default: null
  }
})

const filters = reactive({
  search: props.filters.search || null,
  role: props.filters.role || null,
})

const search = () => {
  const searchData = {
    page: 1,
    ...filters,
  }
  
  // Ensure we always include client_id if available
  const urlParams = new URLSearchParams(window.location.search)
  const clientId = urlParams.get('client_id')
  if (clientId) {
    searchData.client_id = clientId
  }
  
  Inertia.reload({
    data: searchData,
  })
}

const deleteForm = useForm({})
const deletingUser = ref(null)
const isDeleting = computed(() => deletingUser.value !== null)

const confirmDelete = (user) => {
  if (!user.id) {
    console.warn('Cannot delete user - ID not available')
    return
  }
  deletingUser.value = user
}

const cancelDelete = () => {
  deletingUser.value = null
}

const performDelete = () => {
  if (!deletingUser.value?.id) {
    console.warn('Cannot delete user - ID not available')
    return
  }
  
  const urlParams = new URLSearchParams(window.location.search)
  const clientId = urlParams.get('client_id')
  const routeParams = clientId ? { user: deletingUser.value.id, client_id: clientId } : { user: deletingUser.value.id }
  
  deleteForm.delete(route('back-office.users.destroy', routeParams), {
    onSuccess: () => {
      deletingUser.value = null
    }
  })
}

const getUserEditUrl = (userIdentifier) => {
  if (!userIdentifier) return '#'
  
  // If it's an email, we might need to handle it differently
  // For now, assume we need an ID
  if (typeof userIdentifier === 'string' && userIdentifier.includes('@')) {
    console.warn('User ID not available, using email:', userIdentifier)
    return '#' // Can't route without proper ID
  }
  
  const urlParams = new URLSearchParams(window.location.search)
  const clientId = urlParams.get('client_id')
  if (clientId) {
    return route('back-office.users.edit', { user: userIdentifier, client_id: clientId })
  }
  return route('back-office.users.edit', userIdentifier)
}

const getCreateUrl = () => {
  const urlParams = new URLSearchParams(window.location.search)
  const clientId = urlParams.get('client_id')
  if (clientId) {
    return route('back-office.users.create', { client_id: clientId })
  }
  return route('back-office.users.create')
}

onMounted(() => {
  const urlParams = urlParser()
  filters.search = urlParams.search
  filters.role = urlParams.role || null
})
</script>