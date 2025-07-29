<template>
  <dashboard-layout title="Manage User">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Users &amp; Access</h1>
        <button
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="openModalForm">
          <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          User
        </button>
      </div>
    </template>

    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex flex-row gap-4">
        <input id="search-name-or-email" v-model="filters.query" autocomplete="name-or-email"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search Name or Email"
               type="text">

        <select id="role" v-model="filters.role"
                class="flex-1 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                name="role">
          <option :value="null">All</option>
          <option v-for="(role, index) in roles" :key="`role-option-${index}`" :value="role.slug">{{ role.name }}</option>
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
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(user, index) in payload.data" :key="`user-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ user.name }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <pre>{{ user.email }}</pre>
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5 cursor-pointer text-blue-600" @click="edit(user.hash)"/>
                  </td>
                </tr>
                <tr v-if="(filters.query !== null || filters.role !== null) && payload.data.length === 0">
                  <td colspan="4">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="4">
                    <div class="text-center w-full my-4">Empty, please create User.</div>
                  </td>
                </tr>
                </tbody>
              </table>
              <pagination :pagination-data="payload"/>
            </div>
          </div>
        </div>
      </div>
    </template>
  </dashboard-layout>

  <JetDialogModal :show="isModalFormOpen" @close="closeModalForm" items="center">
    <template #title>
      <div class="flex justify-between">
        <h4>{{ modalFormTitle }}</h4>
        <button
          class="inline-flex justify-center px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          type="button"
          :disabled="form.processing"
          v-if="isEdit"
          @click="deleteAction">
          Delete
        </button>
      </div>
    </template>

    <template #content>
      <JetValidationErrors class="my-4"/>
      <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="name">
            Name <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="name" v-model="form.name" autofocus
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   name="name"
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="username">
            Username <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="username" v-model="form.username" autocomplete="off"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="email">
            Email <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="email" v-model="form.email"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start" v-if="!isEdit">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="password">
            <span>Password <small class="text-red-500">*</small></span>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="email" v-model="form.password"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="password"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            Role <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <label class="flex items-center" v-for="(role, index) in roles">
              <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="role.slug" v-model="form.roles">
              <span class="ml-2">{{ role.name }}</span>
            </label>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            Gender <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <label class="inline-flex items-center">
              <input type="radio" class="form-radio" value="male" v-model="form.gender">
              <span class="ml-2">Male</span>
            </label>
            <label class="inline-flex items-center ml-6">
              <input type="radio" class="form-radio" value="female" v-model="form.gender">
              <span class="ml-2">Female</span>
            </label>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-between w-full">
        <div>
          <button
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            type="button"
            v-if="isEdit"
            @click="openModalPassword">
            Change Password
          </button>
        </div>
        <div>
          <button
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            type="button"
            @click="closeModalForm">Cancel
          </button>
          <button
            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            type="submit"
            :disabled="form.processing"
            @click="performUpdateOrCreate">
            Save
          </button>
        </div>
      </div>
    </template>
  </JetDialogModal>

  <JetDialogModal :show="isModalPassOpen" @close="closeModalPass" items="center">
    <template #title>
      <div class="flex justify-between">
        <h4>Change Password</h4>
      </div>
    </template>

    <template #content>
      <JetValidationErrors class="my-4"/>
      <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="new_password">
            <span>New Password <small class="text-red-500">*</small></span>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="new_password" v-model="formPass.password"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="password"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="password_confirmation">
            <span>Confirm Password <small class="text-red-500">*</small></span>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="password_confirmation" v-model="formPass.password_confirmation"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="password"/>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="closeModalPass">Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="submit"
          :disabled="formPass.processing"
          @click="performChangePassword">
          Submit
        </button>
      </div>
    </template>
  </JetDialogModal>

  <JetConfirmationModal :show="isDeleting" items="center">
    <template #title>
      Delete User {{ deletingItem?.name }}
    </template>

    <template #content>
      User cannot be recovered after deletion.
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="cancelDeleteAction">Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          type="submit"
          :disabled="form.processing"
          @click="confirmDeleteAction">
          Yes, Delete User
        </button>
      </div>
    </template>
  </JetConfirmationModal>

</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {PlusIcon, SearchIcon, PencilAltIcon} from '@heroicons/vue/outline'
import {computed, reactive, ref, toRaw, toRefs, onMounted} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import {useForm} from "@inertiajs/inertia-vue3";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetBanner from '@/Jetstream/Banner'

const props = defineProps({
  roles: {
    type: Array,
  },
  count: {
    type: Number,
  },
  payload: {
    type: Object,
  },
})

const {payload, roles} = toRefs(props)

const filters = reactive({
  query: null,
  role: null,
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const form = useForm({
  name: null,
  username: null,
  email: null,
  password: null,
  gender: null,
  roles: [],
})

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  filters.role = urlParams.role || null
})

const isModalFormOpen = ref(false)

const editingItemHash = ref(null)
const isEdit = computed(() => editingItemHash.value !== null)
const editingItem = computed(() => toRaw(payload.value.data.find(item => item.hash === editingItemHash.value)))

const modalFormTitle = computed(() => isEdit.value ? `Edit User: ${editingItem.value?.name}` : 'Create User')

const openModalForm = () => {
  isModalFormOpen.value = true

  if (isEdit.value) {
    let roles = [];
    editingItem.value.roles.forEach((role) => roles.push(role.slug));

    form.name = toRaw(editingItem.value.name)
    form.username = toRaw(editingItem.value.username)
    form.email = toRaw(editingItem.value.email)
    form.gender = toRaw(editingItem.value.gender)
    form.roles = toRaw(roles)
    form.isDirty = false
  } else {
    form.name = null
    form.username = null
    form.email = null
    form.gender = null
    form.roles = []
    form.isDirty = false
  }
}

const edit = (itemHash) => {
  editingItemHash.value = toRaw(itemHash)
  openModalForm()
}

const closeModalForm = () => {
  form.clearErrors()
  isModalFormOpen.value = false
  editingItemHash.value = null
}

const performUpdateOrCreate = () => {
  if (isEdit.value) {
    form.put(route('back-office.user.update', {user_hash: editingItem.value.hash}), {
      onSuccess: closeModalForm
    })
  } else {
    form.post(route('back-office.user.store'), {
      onSuccess: closeModalForm
    })
  }
}

const isModalPassOpen = ref(false)
const formPass = useForm({
  password: null,
  password_confirmation: null,
})
const openModalPassword = () => {
  formPass.clearErrors()
  isModalPassOpen.value = true
  formPass.password = null;
  formPass.password_confirmation = null;
};
const closeModalPass = () => {
  formPass.clearErrors()
  isModalPassOpen.value = false;
}

const performChangePassword = () => {
  formPass.post(route('back-office.user.change-password', {user_hash: editingItem.value.hash}), {
    onSuccess: closeModalPass,
  })
}

const deletingItemHash = ref(null)
const isDeleting = computed(() => deletingItemHash.value !== null)
const deletingItem = computed(() => toRaw(payload.value.data.find(item => item.hash === deletingItemHash.value)))

const deleteAction = () => {
  deletingItemHash.value = toRaw(editingItemHash.value)
  closeModalForm()
}

const cancelDeleteAction = () => {
  deletingItemHash.value = null
}

const confirmDeleteAction = () => {
  form.delete(route('back-office.user.destroy', {user_hash: deletingItem.value?.hash}), {
    onSuccess: () => deletingItemHash.value = null
  })
}
</script>

<style scoped>

</style>
