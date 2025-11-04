<template>
  <dashboard-layout title="Create User">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">
          Create New User{{ currentClient ? ` for ${currentClient.name}` : '' }}
        </h1>
        <div class="flex space-x-3">
          <Link
            :href="getBackUrl()"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            Back to Users
          </Link>
        </div>
      </div>
    </template>

    <template #default>
      <div class="max-w-3xl mx-auto">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <JetValidationErrors class="mb-4"/>
          
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">User Information</h3>
                <p class="mt-1 text-sm text-gray-500">Basic information for the new user account.</p>
              </div>
              
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <div class="col-span-6">
                    <JetLabel for="name" value="Full Name" required/>
                    <JetInput
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="mt-1 block w-full"
                      autofocus/>
                    <JetInputError :message="form.errors.name" class="mt-2"/>
                  </div>

                  <div class="col-span-6">
                    <JetLabel for="username" value="Username" required/>
                    <JetInput
                      id="username"
                      v-model="form.username"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="Username will auto-generate from email if left empty"/>
                    <JetInputError :message="form.errors.username" class="mt-2"/>
                  </div>

                  <div class="col-span-6">
                    <JetLabel for="email" value="Email Address" required/>
                    <JetInput
                      id="email"
                      v-model="form.email"
                      type="email"
                      class="mt-1 block w-full"
                      @input="autoGenerateUsername"/>
                    <JetInputError :message="form.errors.email" class="mt-2"/>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Password</h3>
                <p class="mt-1 text-sm text-gray-500">Set the initial password for this user.</p>
              </div>
              
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <div class="col-span-6">
                    <JetLabel for="password" value="Password" required/>
                    <JetInput
                      id="password"
                      v-model="form.password"
                      type="password"
                      class="mt-1 block w-full"/>
                    <JetInputError :message="form.errors.password" class="mt-2"/>
                  </div>

                  <div class="col-span-6">
                    <JetLabel for="password_confirmation" value="Confirm Password" required/>
                    <JetInput
                      id="password_confirmation"
                      v-model="form.password_confirmation"
                      type="password"
                      class="mt-1 block w-full"/>
                    <JetInputError :message="form.errors.password_confirmation" class="mt-2"/>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Roles & Permissions</h3>
                <p class="mt-1 text-sm text-gray-500">Assign roles to define user permissions.</p>
              </div>
              
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="space-y-3">
                  <div v-for="role in roles" :key="role.id" class="relative flex items-start">
                    <div class="flex items-center h-5">
                      <input
                        :id="`role-${role.id}`"
                        type="checkbox"
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        :value="role.id"
                        v-model="form.role_ids"/>
                    </div>
                    <div class="ml-3 text-sm">
                      <label :for="`role-${role.id}`" class="font-medium text-gray-700 cursor-pointer">
                        {{ role.name }}
                      </label>
                      <p v-if="role.description" class="text-gray-500">{{ role.description }}</p>
                    </div>
                  </div>
                  <JetInputError :message="form.errors.role_ids" class="mt-2"/>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3">
            <Link
              :href="getBackUrl()"
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
              Cancel
            </Link>
            <JetButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing">
              Create User
            </JetButton>
          </div>
        </form>
      </div>
    </template>
  </dashboard-layout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Link, useForm } from "@inertiajs/inertia-vue3"
import JetButton from '@/Jetstream/Button'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetLabel from '@/Jetstream/Label'
import JetValidationErrors from '@/Jetstream/ValidationErrors'

const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  },
  currentClient: {
    type: Object,
    default: null
  }
})

const form = useForm({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_ids: [],
})

const autoGenerateUsername = () => {
  // Auto-generate username from email if username is empty
  if (!form.username && form.email) {
    const emailParts = form.email.split('@')
    if (emailParts.length > 0) {
      form.username = emailParts[0]
    }
  }
}

const handleSubmit = () => {
  const urlParams = new URLSearchParams(window.location.search)
  const clientId = urlParams.get('client_id')

  // Generate username if still empty
  if (!form.username && form.email) {
    autoGenerateUsername()
  }

  // Build the route with client_id as query parameter
  const routeParams = clientId ? { client_id: clientId } : {}

  form.post(route('back-office.users.store', routeParams))
}

const getBackUrl = () => {
  const urlParams = new URLSearchParams(window.location.search)
  const clientId = urlParams.get('client_id')
  if (clientId) {
    return route('back-office.users.index', { client_id: clientId })
  }
  return route('back-office.users.index')
}
</script>