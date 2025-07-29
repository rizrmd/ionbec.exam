<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {computed, onMounted, reactive, ref, toRefs} from 'vue'
import {PencilAltIcon, PlusIcon, SearchIcon} from '@heroicons/vue/outline'
import {Inertia} from "@inertiajs/inertia";
import {Link, useForm} from "@inertiajs/inertia-vue3";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetBanner from '@/Jetstream/Banner'
import JetDialogModal from '@/Jetstream/DialogModal'
import Select from "@/Components/Select";
import moment from "moment";
import SimpleDateRangePicker from "@/Components/SimpleDateRangePicker";
import Datepicker from "@/Components/Datepicker";
import route from "@/Libs/ziggy";

const props = defineProps({
  payload: {
    type: Object,
  },
  tests: {
    type: Object,
  },
  groups: {
    type: Object,
  },
})

const {payload, tests, groups} = toRefs(props)

const filters = reactive({
  name: null,
  date: null
})

const statusColors = (status) => {
  const map = {
    'Scheduled': {
      bg: 'bg-purple-100',
      text: 'text-purple-800'
    },
    'On Progress': {
      bg: 'bg-blue-100',
      text: 'text-blue-800'
    },
    'Not Started': {
      bg: 'bg-red-100',
      text: 'text-red-800'
    },
    'Scoring': {
      bg: 'bg-yellow-100',
      text: 'text-yellow-800'
    },
    'Finished': {
      bg: 'bg-green-100',
      text: 'text-green-800'
    }
  }
  return map[status] ?? {
    bg: 'bg-gray-100',
    text: 'text-gray-800'
  }
}

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const form = useForm({
  exam: null,
  exam_hash: null,
  group: null,
  group_hash: null,
  name: null,
  display_name: null,
  scheduled_at: null,
  duration: null,
  automatic_start: true,
})

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  if (urlParams.modal_create && Object.keys(urlParams).length === 1) openModalForm()
})

const isModalFormOpen = ref(false)
const isSameAsNameChecked = ref(false)

const openModalForm = () => {
  isModalFormOpen.value = true
  form.exam = null
  form.group = null
  form.name = null
  form.scheduled_at = null
  form.duration = null
  form.automatic_start = true
}

const checkSameAsName = (e) => {
  isSameAsNameChecked.value = e.target.checked
  if (e.target.checked) {
    form.display_name = form.name
  }
}

const closeModalForm = () => {
  form.clearErrors()
  isModalFormOpen.value = false
}

const createAction = () => {
  if (form.exam.is_interview) {
    form.duration = 0;
  }
  form.exam_hash = (form.exam) ? form.exam.value : null
  form.group_hash = (form.group) ? form.group.value : null
  form.post(route('back-office.delivery.store'), {
    onSuccess: closeModalForm
  })
}

const selOpTest = computed(() => {
  let data = [];
  tests.value.forEach((exam) => {
    data.push({
      text: exam.name,
      value: exam.hash,
      is_interview: exam.is_interview
    })
  })
  return data;
});

const selOpGroup = computed(() => {
  let data = [];
  groups.value.forEach((group) => {
    data.push({
      text: group.name,
      value: group.hash
    })
  })
  return data;
});

onMounted(() => {
  const urlParams = urlParser()
  filters.name = urlParams.name
  filters.date = urlParams.date || null
})
</script>

<template>
  <dashboard-layout title="Manage Deliveries">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Manage Deliveries</h1>
        <button
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="openModalForm">
          <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Delivery
        </button>
      </div>
    </template>
    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex flex-row gap-4">
        <input id="search-name" v-model="filters.name" autocomplete="name"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search name"
               type="text">
        <div class="flex-1">
          <SimpleDateRangePicker v-model="filters.date" placeholder="Pick a date range" position-x="bottom"
                                 position-y="right"/>
        </div>
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
            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                <tr>
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6" scope="col">
                    DELIVERY NAME
                  </th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-right" scope="col">
                    SCHEDULE
                  </th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">DURATION</span>
                  </th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">STATUS</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Action</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(delivery, index) in payload.data" :key="`delivery-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap pl-4 pr-3 py-4 text-sm text-gray-500 sm:pl-6">
                    <span>
                      <span v-if="delivery.status === 'Scheduled' && (!delivery.is_interview && delivery.automatic_start)"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-red-800 bg-red-200 mr-2">Strict to schedule</span>
                      {{ delivery.name ?? '-' }}
                    </span>
                    <div class="mt-2">
                      <span v-if="delivery.takers_count === 0"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-600 mr-2">
                          Group not set
                      </span>
                      <span v-else
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-600 mr-2">
                          <span class="font-semibold">{{ delivery.takers_count }}</span>&nbsp;&nbsp;Takers
                      </span>

                      <span v-if="delivery.questions_count === 0"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-600 mr-2">
                          Exam not set
                      </span>
                      <span v-else
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-600 mr-2">
                          <span class="font-semibold">{{ delivery.questions_count }}</span>&nbsp;&nbsp;Questions
                      </span
                      >
                      <span v-if="delivery.is_interview"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-600 mr-2">
                          Interview
                      </span>
                    </div>
                  </td>

                  <td v-if="delivery.is_interview"
                      class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                    {{ moment(delivery.scheduled_at).format('D MMM YYYY') }}
                  </td>
                  <td v-else-if="!delivery.automatic_start"
                      class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                    {{ moment(delivery.scheduled_at).format('D MMM YYYY / H:mm') }}
                  </td>
                  <td v-else class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right text-gray-500">
                    {{ moment(delivery.scheduled_at).format('D MMM YYYY / H:mm') }}
                  </td>

                  <td v-if="delivery.is_interview" class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"></td>
                  <td v-else class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ delivery.duration }} min</td>

                  <td class="whitespace-nowrap px-3 py-4">
                    <!-- Scheduled, On Progress, Scoring, Finished -->
                    <span
                      :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusColors(delivery.status).bg, statusColors(delivery.status).text]">
                      {{ delivery.status }}
                    </span>
                  </td>
                  <td class="relative whitespace-nowrap px-3 py-4 text-right text-sm font-medium sm:pr-6">
                    <Link
                      :href="route('back-office.delivery.scoring', {delivery_hash: delivery.hash, name: 'back-office.delivery.index', params: 'page', values: payload.current_page})"
                      class="text-blue-600 hover:text-blue-900">
                      <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                    </Link>
                  </td>
                </tr>
                <tr v-if="(filters.name !== null || filters.date !== null) && payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Empty, please create Test.</div>
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
        <h4>Create Delivery</h4>
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
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="display_name">
            Display Name <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="display_name" v-model="form.display_name" autofocus
                   :class="[
                     'max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md',
                     isSameAsNameChecked ? 'bg-gray-100' : ''
                   ]"
                   type="text" :readonly="isSameAsNameChecked"/>
            <div class="relative flex items-start mt-1">
              <div class="flex h-6 items-center">
                <input @change="checkSameAsName" id="same-as-name" aria-describedby="same-as-name-description"
                       name="same-as-name" type="checkbox"
                       class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
              </div>
              <div class="ml-3 text-sm leading-6">
                <label for="same-as-name" class="text-gray-900">Same as name.</label>
              </div>
            </div>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="test">
            Test <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <div class="max-w-lg block w-full sm:max-w-xs">
              <Select id="test" :options="selOpTest" title="Select assigned Test" v-model="form.exam"
                      search-placeholder="Search Test"></Select>
            </div>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="group">
            Group <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <div class="max-w-lg block w-full sm:max-w-xs">
              <Select id="group" :options="selOpGroup" title="Select assigned Group" v-model="form.group"
                      search-placeholder="Search Group"/>
            </div>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            Schedule At
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <div class="max-w-lg block w-full sm:max-w-xs">
              <Datepicker v-model="form.scheduled_at" placeholder="Pick a date" :enable-time="true"
                          position-y="bottom"/>
            </div>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start" v-if="!form.exam?.is_interview">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="duration">
            Duration
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="duration" v-model="form.duration" autofocus
                   class="w-36 shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="number"/>
            <span class="ml-2 text-gray-500">minute</span>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start" v-if="!form.exam?.is_interview">
          <label class="block text-sm font-medium text-gray-700">
            -
          </label>
          <div class="mt-0.5 sm:col-span-2 flex items-center gap-2">
            <input id="automatic-start" type="checkbox"
                   class="form-checkbox rounded text-primary-500 focus:ring-primary-500" checked
                   v-model="form.automatic_start">
            <label class="flex flex-col justify-start" for="automatic-start">
              <span>Strict to schedule</span>
              <span class="text-xs">(a waiting page will be shown if checked)</span>
            </label>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="closeModalForm">
          Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="submit"
          :disabled="form.processing"
          @click="createAction">
          Save
        </button>
      </div>
    </template>
  </JetDialogModal>
</template>
