<template>
  <dashboard-layout title="Manage Candidates">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Manage Candidates</h1>
        <button
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="openModalForm">
          <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Candidate
        </button>
      </div>
    </template>

    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>

      <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
        <card-counter title="Verified" :color="filters.verified !== null && filters.verified ? 'green-600' : 'green-100'" :count="countVerified" @click="() => search(true)"/>
        <card-counter title="Not Verified" :color="filters.verified !== null && !filters.verified ? 'red-600' : 'red-100'" :count="countNonVerified" @click="() => search(false)"/>
      </div>

      <div class="flex flex-row gap-4 mt-8">
        <input id="search-name-or-email" v-model="filters.query" autocomplete="name-or-email"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search Name or Email"
               type="text">
        <input id="search-date" v-model="filters.date"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="date"
               placeholder="Pick a date"
               type="date">
        <select v-model="filters.group"
                class="flex-1 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
          <option :value="null">All</option>
          <option v-for="(group, index) in groups" :key="`role-option-${index}`" :value="group.hash">{{ group.name }}</option>
        </select>
        <button
          class="flex-none inline-flex justify-center items-center px-4 py-1.5 mt-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          type="button"
          @click="() => search(null)">
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
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">REG</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">EMAIL</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">GROUPS</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">CREATED AT</th>
                  <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900" scope="col">VERIFIED</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(taker, index) in payload.data" :key="`taker-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <span v-if="taker.reg !== null && taker.reg.length > 0">{{ taker.reg }}</span>
                    <small v-else class="text-red-400">(empty)</small>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ taker.name }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <pre>{{ taker.email }}</pre>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ taker.groups.length }} Group</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ moment(taker.created_at).format('D MMM YYYY') }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 flex justify-center items-center">
                    <span v-if="taker.is_verified">
                      <BadgeCheckIcon aria-hidden="true" class="-ml-1 h-5 w-5 text-green-600"/>
                    </span>
                    <span v-else>
                      <XCircleIcon aria-hidden="true" class="-ml-1 h-5 w-5 text-red-600"/>
                    </span>
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5 cursor-pointer text-blue-600" @click="edit(taker.hash)"/>
                  </td>
                </tr>
                <tr v-if="(filters.query !== null || filters.role !== null) && payload.data.length === 0">
                  <td colspan="7">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="7">
                    <div class="text-center w-full my-4">Empty, please create Candidate.</div>
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
        <div class="flex gap-2">
          <button
            class="inline-flex justify-center px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            type="button"
            v-if="isEdit && (!!editingItem && !editingItem.is_verified)"
            @click="verifAcc(toRaw(editingItemHash))">
            Verify Account
          </button>
          <button
            class="inline-flex justify-center px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            type="button"
            v-if="isEdit"
            @click="deletingItemHash = toRaw(editingItemHash)">
            Delete
          </button>
        </div>
      </div>
    </template>

    <template #content>
      <JetValidationErrors class="my-4"/>
      <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="email">
            No. Candidate
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="reg" v-model="form.reg"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="text" placeholder="(empty)"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
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
            Password <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="password" v-model="form.password"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="password"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="group">
            Group <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <div class="max-w-lg sm:max-w-xs w-full">
              <MultipleSelect title="Group" :required="true" :options="filteredGroups" pills-size="small" v-model="form.groups" position="top" />
            </div>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-between w-full">
        <div class="flex items-center">
          <button
            class="text-orange-600"
            type="button"
            v-if="isEdit"
            @click="openModalReset">
            Reset Password
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

  <JetDialogModal :show="isModalReset"  @close="closeModalReset" items="center">
    <template #content>
      <div class="-mt-4 text-center">
        <p>New Password will be sent to budi.sans@gmail.com</p>
        <div class="flex justify-center mt-2">
          <button
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-75"
            type="button"
            :disabled="loadingSentEmail"
            @click="actionGenerateReset">
            No, Generate Instead
          </button>
          <button
            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-75"
            type="button"
            :disabled="loadingSentEmail"
            @click="actionSentEmail">
            <span class="mr-2" v-if="loadingSentEmail">
              <LoadingSpin/>
            </span>
            Sent to Email
          </button>
        </div>
      </div>
    </template>
  </JetDialogModal>

  <JetDialogModal :show="isModalNewPass"  @close="closeModalNewPass" items="center">
    <template #content>
      <div class="-mt-4 text-center">
        <p>New password successfully generated!</p>
        <div class="flex justify-center my-2">
          <button v-if="isHttps" class="flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" @click="copyToClipboard(newPassword)">
            <ClipboardCopyIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
            {{ newPassword }}
          </button>
          <code v-else class="bg-gray-200 border border-gray-400 py-2 px-4 rounded-md"> {{ newPassword }} </code>
        </div>
        <p class="text-orange-500 text-sm">This password <strong>only shows once</strong>, copy and send it safely to the account owner.</p>
      </div>
    </template>
  </JetDialogModal>

  <JetConfirmationModal :show="isDeleting" items="center">
    <template #title>
      Delete Candidate {{ deletingItem?.name }}
    </template>

    <template #content>
      Candidate cannot be recovered after deletion.
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="deletingItemHash = null">Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          type="submit"
          @click="confirmDeleteAction">
          Yes, Delete Candidate
        </button>
      </div>
    </template>
  </JetConfirmationModal>

  <JetConfirmationModal :show="modalVerif" items="center">
    <template #title>
      Verification Candidate {{ verifItem?.name }}
    </template>

    <template #content>
      You cannot undo this action.

      <div class="w-full bg-gray-100 rounded-lg" v-if="verifItem.register_data">
        <div class="p-4 my-2 border-b border-gray-300">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            Registration Data
          </h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Some registration data from input participant.
          </p>
        </div>

        <div class="space-y-6 p-4 pt-2">
          <div class="flex gap-2">
            <div class="flex-1">
              <div class="text-sm font-medium text-gray-500">
                Delivery
              </div>
              <div class="mt-1 text-sm text-gray-900">
                {{ verifItem.register_data.delivery.name }}
              </div>
            </div>
            <div class="flex-1">
              <div class="text-sm font-medium text-gray-500">
                Group
              </div>
              <div class="mt-1 text-sm text-gray-900">
                {{ verifItem.register_data.group.name }}
              </div>
            </div>
          </div>

<!--          <div>-->
<!--            <label class="block text-sm font-medium text-gray-700 mb-1" for="taker_code">Participant Code</label>-->
<!--            <input id="taker_code"-->
<!--                   v-model="formVerification.taker_code"-->
<!--                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"-->
<!--                   type="text"/>-->
<!--          </div>-->
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="verifItemHash = null">
          Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          type="submit"
          @click="confirmVerifAction">
          Yes, Verify Taker Account
        </button>
      </div>
    </template>
  </JetConfirmationModal>

</template>

<script setup>
import moment from 'moment'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {PlusIcon, SearchIcon, ClipboardCopyIcon, BadgeCheckIcon, XCircleIcon, PencilAltIcon} from '@heroicons/vue/outline'
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
import MultipleSelect from "@/Components/MultipleSelect";
import LoadingSpin from '@/Components/Icons/LoadingSpin';
import CardCounter from '@/Components/CardCounter.vue'

const props = defineProps({
  groups: {
    type: Array,
  },
  count: {
    type: Number,
  },
  countVerified: {
    type: Number,
  },
  countNonVerified: {
    type: Number,
  },
  payload: {
    type: Object,
  },
})

const {payload, groups} = toRefs(props)

const filters = reactive({
  query: null,
  date: null,
  group: null,
  verified: null,
})

const search = (verified = null) => {
  filters.verified = (verified === filters.verified) ? null : verified;
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const form = useForm({
  name: null,
  email: null,
  reg: null,
  groups: [],
  selects: []
})

const isHttps = computed(() => window.location.protocol === 'https:')

const filteredGroups = computed(() => {
  let newGroups = [];
  groups.value.forEach((group) => {
    newGroups.push({
      text: group.name,
      value: group.hash
    })
  })
  return newGroups;
})

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  filters.group = urlParams.group || null
  filters.date = urlParams.date
  if (urlParams.verified === 'true') {
    filters.verified = true;
  } else if (urlParams.verified === 'false') {
    filters.verified = false;
  } else {
    filters.verified = null;
  }

  if (urlParams.modal_create && Object.keys(urlParams).length === 1) openModalForm()
})

const isModalFormOpen = ref(false)

const editingItemHash = ref(null)
const isEdit = computed(() => editingItemHash.value !== null)
const editingItem = computed(() => toRaw(payload.value.data.find(item => item.hash === editingItemHash.value)))

const modalFormTitle = computed(() => isEdit.value ? `Edit Candidate` : 'Create Candidate')

const openModalForm = () => {
  isModalFormOpen.value = true

  if (isEdit.value) {
    let groups = [];
    editingItem.value.groups.forEach((group) => groups.push(group.hash));

    form.name = toRaw(editingItem.value.name)
    form.email = toRaw(editingItem.value.email)
    form.reg = toRaw(editingItem.value.reg)
    form.groups = editingItem.value.groups.map(i => i['hash'])
    form.isDirty = false
  } else {
    form.name = null
    form.email = null
    form.reg = null
    form.password = null
    form.groups = []
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
    form.put(route('back-office.test-taker.update', {taker_hash: editingItem.value.hash}), {
      onSuccess: closeModalForm
    })
  } else {
    form.post(route('back-office.test-taker.store'), {
      onSuccess: closeModalForm
    })
  }
}

const deletingItemHash = ref(null)
const isDeleting = computed(() => deletingItemHash.value !== null)
const deletingItem = computed(() => toRaw(payload.value.data.find(item => item.hash === deletingItemHash.value)))

const confirmDeleteAction = () => {
  form.delete(route('back-office.test-taker.destroy', {taker_hash: deletingItem.value?.hash}), {
    onSuccess: () => {
      closeModalForm()
      deletingItemHash.value = null
    }
  })
}

const formVerification = useForm({
  register_data_hash: null,
  taker_code: null,
})
const verifItemHash = ref(null)
const modalVerif = computed(() => verifItemHash.value !== null)
const verifItem = computed(() => toRaw(payload.value.data.find(item => item.hash === verifItemHash.value)))

const confirmVerifAction = () => {
  formVerification.post(route('back-office.test-taker.verification', {taker_hash: verifItem.value?.hash}), {
    onSuccess: () => {
      closeModalForm()
      verifItemHash.value = null
    }
  })
}

const verifAcc = (data) => {
  verifItemHash.value = data
  const registerData = verifItem.value.register_data
  if (registerData !== null) {
    formVerification.register_data_hash = registerData.hash
    formVerification.taker_code = registerData.taker_code
  }
}

const isModalReset = ref(false)
const openModalReset = () => isModalReset.value = true;
const closeModalReset = () => isModalReset.value = false;

const isModalNewPass = ref(false)
const openModalNewPass = () => isModalNewPass.value = true;
const closeModalNewPass = () => isModalNewPass.value = false;
const newPassword = ref(null);
const copyToClipboard = (text) => navigator.clipboard.writeText(text).then(() => {
  console.log('Text copied.')
}, (err) => {
  console.log('Couldn\'t copy text: ', err)
});

const actionGenerateReset = () => {
  closeModalReset()
  axios.post(route('back-office.test-taker.generate-password'), {
    hash: editingItemHash.value
  })
    .then((response) => {
      newPassword.value = response.data.password
      openModalNewPass()
    })
    .catch((error) => console.log(error));
}

const loadingSentEmail = ref(false);
const actionSentEmail = () => {
  loadingSentEmail.value = true;
  axios.post(route('back-office.test-taker.sent-password'), {
    hash: editingItemHash.value
  })
    .then((response) => closeModalReset())
    .catch((error) => console.log(error))
    .finally(() => loadingSentEmail.value = false);
}
</script>

<style scoped>

</style>
