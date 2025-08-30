<template>
  <dashboard-layout title="Create Client">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Create New Client</h1>
        <Link
          :href="route('back-office.clients.index')"
          class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          Back to Clients
        </Link>
      </div>
    </template>

    <template #default>
      <div class="max-w-3xl mx-auto">
        <form @submit.prevent="submit" class="space-y-6">
          <JetValidationErrors class="mb-4"/>
          
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Client Information</h3>
                <p class="mt-1 text-sm text-gray-500">Basic information about the client organization.</p>
              </div>
              
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <div class="col-span-6">
                    <JetLabel for="name" value="Client Name" required/>
                    <JetInput
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="mt-1 block w-full"
                      autofocus
                      @input="generateSlug"/>
                  </div>

                  <div class="col-span-6">
                    <JetLabel for="slug" value="Client Slug" required/>
                    <JetInput
                      id="slug"
                      v-model="form.slug"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="auto-generated-from-name"/>
                    <p class="mt-1 text-sm text-gray-500">Used for URLs and identification. Auto-generated from name.</p>
                  </div>

                  <div class="col-span-6">
                    <JetLabel for="logo" value="Client Logo"/>
                    <input
                      id="logo"
                      type="file"
                      @input="form.logo = $event.target.files[0]"
                      accept="image/*"
                      class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"/>
                    <p class="mt-1 text-sm text-gray-500">Upload a logo for this client (max 2MB, image files only).</p>
                  </div>

                  <div class="col-span-6">
                    <JetLabel value="Domains" required/>
                    <div class="mt-1 space-y-2">
                      <div v-for="(domain, index) in form.domains" :key="index" class="flex items-center space-x-2">
                        <JetInput
                          v-model="form.domains[index]"
                          type="text"
                          class="flex-1"
                          placeholder="example.com"/>
                        <button
                          type="button"
                          @click="removeDomain(index)"
                          class="inline-flex items-center p-1 border border-transparent text-sm leading-4 font-medium rounded text-red-700 hover:text-red-900">
                          <TrashIcon class="h-4 w-4"/>
                        </button>
                      </div>
                      <button
                        type="button"
                        @click="addDomain"
                        class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                        <PlusIcon class="h-4 w-4 mr-1"/>
                        Add Domain
                      </button>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Domains that this client can access the application from.</p>
                  </div>

                  <div class="col-span-6">
                    <div class="flex items-center">
                      <JetCheckbox
                        id="is_active"
                        v-model:checked="form.is_active"/>
                      <JetLabel for="is_active" value="Active" class="ml-2"/>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Whether this client can access the application.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Contact Information</h3>
                <p class="mt-1 text-sm text-gray-500">Primary contact details for this client.</p>
              </div>
              
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <div class="col-span-6">
                    <JetLabel for="primary_contact_email" value="Primary Contact Email" required/>
                    <JetInput
                      id="primary_contact_email"
                      v-model="form.primary_contact_email"
                      type="email"
                      class="mt-1 block w-full"/>
                  </div>

                  <div class="col-span-6">
                    <JetLabel for="primary_contact_phone" value="Primary Contact Phone"/>
                    <JetInput
                      id="primary_contact_phone"
                      v-model="form.primary_contact_phone"
                      type="text"
                      class="mt-1 block w-full"/>
                  </div>

                  <div class="col-span-6">
                    <JetLabel for="notes" value="Notes"/>
                    <textarea
                      id="notes"
                      v-model="form.notes"
                      rows="4"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                      placeholder="Additional notes about this client..."/>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3">
            <Link
              :href="route('back-office.clients.index')"
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50">
              <span v-if="form.processing">Creating...</span>
              <span v-else>Create Client</span>
            </button>
          </div>
        </form>
      </div>
    </template>
  </dashboard-layout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { PlusIcon, TrashIcon } from '@heroicons/vue/outline'
import { useForm, Link } from "@inertiajs/inertia-vue3"
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetCheckbox from '@/Jetstream/Checkbox'

const form = useForm({
  name: '',
  slug: '',
  logo: null,
  domains: [''],
  primary_contact_email: '',
  primary_contact_phone: '',
  notes: '',
  is_active: true,
})

const generateSlug = () => {
  if (!form.name) return
  form.slug = form.name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim()
}

const addDomain = () => {
  form.domains.push('')
}

const removeDomain = (index) => {
  if (form.domains.length > 1) {
    form.domains.splice(index, 1)
  }
}

const submit = () => {
  // Filter out empty domains
  const filteredDomains = form.domains.filter(domain => domain.trim() !== '')
  
  form.transform((data) => ({
    ...data,
    domains: filteredDomains
  })).post(route('back-office.clients.store'))
}
</script>