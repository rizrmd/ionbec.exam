<script setup>
import moment from 'moment'
import AuthoringGroupLayout from '@/Layouts/AuthoringGroupLayout.vue'
import {SearchIcon, XIcon, PencilAltIcon} from '@heroicons/vue/outline'
import {reactive, ref, toRefs, onMounted, computed, toRaw, watch} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import {useForm} from "@inertiajs/inertia-vue3";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
import Select from "@/Components/Select";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import route from "@/Libs/ziggy";

const props = defineProps({
  payload: {
    type: Object,
  },
  takerCount: {
    type: Number,
  },
  deliveryCount: {
    type: Number,
  },
  group: {
    type: Object,
  },
  takers: {
    type: Object,
  }
})

const {payload, group, takers} = toRefs(props)

const filters = reactive({
  query: null
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
})

const formAdd = useForm({
  hash: null,
  taker_code: null,
})

const showAddModal = ref(false)
const selectedTaker = ref(null)
watch(selectedTaker, (taker, prevTaker) => {
    showAddModal.value = taker !== null
    formAdd.taker_code = null
});

const selectedOptions = computed(() => {
  let data = [];
  takers.value.forEach((taker) => {
    data.push({
      text: `(${moment(taker.createdAt).format('DD.MM.YYYY')}) ${taker.name}`,
      value: taker.hash
    })
  })
  return data;
});

const resetAddAction = () => {
  showAddModal.value = false
  selectedTaker.value = null
}

const confirmAddAction = () => {
  formAdd.hash = selectedTaker.value.value;
  formAdd.post(route('back-office.group.add-test-taker', {group_hash: group.value.hash}), {
    onSuccess: () => resetAddAction()
  })
}

const formDelete = useForm({
  hash: null
})

const deletingItemHash = ref(null)
const isDeleting = computed(() => deletingItemHash.value !== null)
const deletingItem = computed(() => toRaw(payload.value.data.find(item => item.hash === deletingItemHash.value)))
const deleteAction = (hash) => deletingItemHash.value = toRaw(hash)
const cancelDeleteAction = () => deletingItemHash.value = null

const confirmDeleteAction = () => {
  formDelete.hash = deletingItemHash;
  formDelete.post(route('back-office.group.remove-test-taker', {group_hash: group.value.hash}), {
    onSuccess: () => deletingItemHash.value = null
  })
}

const modalEdit = ref(false);
const openModalEdit = (hash, takerCode) => {
  formAdd.hash = hash;
  formAdd.taker_code = takerCode;
  modalEdit.value = true;
}

const editAction = () => {
  formAdd.post(route('back-office.group.add-test-taker', {group_hash: group.value.hash}), {
    onSuccess: () => modalEdit.value = false
  })
}
</script>

<template>
  <AuthoringGroupLayout :group="group" :taker-count="takerCount" :delivery-count="deliveryCount">
    <template #widget>
      <Select :options="selectedOptions" title="Add a candidate to this group .." v-model="selectedTaker" search-placeholder="Search Candidate"></Select>
    </template>
    <template #default>
      <JetActionMessage :on="formDelete.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex flex-row gap-4 mt-4">
        <input id="search-name-or-email" v-model="filters.query" autocomplete="name-or-email"
               class="flex-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search Name or Email"
               type="text">
        <button
          class="inline-flex justify-center items-center px-4 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6" scope="col">NO</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">CODE</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">EMAIL</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(taker, index) in payload.data" :key="`taker-${index}`">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ group.code + '-' + taker.groups[0]?.pivot.taker_code }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ taker.name }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ taker.email }}</td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 sm:pr-6 flex items-center justify-end gap-3">
                    <button class="text-primary-500 hover:text-primary-600" @click="openModalEdit(taker.hash, taker.groups[0]?.pivot.taker_code)">
                      <PencilAltIcon aria-hidden="true" class="-ml-1 h-5 w-5"/>
                    </button>
                    <button class="text-red-400 hover:text-red-800" @click="deleteAction(taker.hash)">
                      <XIcon aria-hidden="true" class="-ml-1 h-5 w-5"/>
                    </button>
                  </td>
                </tr>
                <tr v-if="filters.query !== '' && payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Empty, please add Candidate.</div>
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
  </AuthoringGroupLayout>

  <JetDialogModal :show="modalEdit" @close="modalEdit = false" items="center">
    <template #title>
      <div class="flex justify-between">
        <h4>Edit Participant Code</h4>
      </div>
    </template>

    <template #content>
      <JetValidationErrors class="my-4"/>
      <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="taker_code">
            Code <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="taker_code" v-model="formAdd.taker_code"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="text"/>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="modalEdit = false">Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="submit"
          @click="editAction">
          Save
        </button>
      </div>
    </template>
  </JetDialogModal>

  <JetConfirmationModal :show="isDeleting" items="center">
    <template #title>
      Remove a Candidate
    </template>

    <template #content>
      Are you sure to remove <strong>{{ deletingItem?.name }}</strong> from Group?
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
          :disabled="formDelete.processing"
          @click="confirmDeleteAction">
          Yes, remove.
        </button>
      </div>
    </template>
  </JetConfirmationModal>

  <JetConfirmationModal :show="showAddModal" items="center">
    <template #title>
      Add a Candidate
    </template>

    <template #content>
      Are you sure to add <strong>{{ selectedTaker?.text }}</strong> to this Group?

<!--      <div class="mt-3">-->
<!--        <label class="block text-sm font-medium text-gray-700 mb-1" for="taker_code">Participant Code</label>-->
<!--        <input id="taker_code"-->
<!--               v-model="formAdd.taker_code"-->
<!--               class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"-->
<!--               type="text"/>-->
<!--      </div>-->
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="resetAddAction">Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          type="submit"
          :disabled="formAdd.processing"
          @click="confirmAddAction">
          Yes, add candidate.
        </button>
      </div>
    </template>
  </JetConfirmationModal>
</template>

<style scoped>

</style>
