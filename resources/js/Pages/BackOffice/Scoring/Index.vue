<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {watch, ref, onMounted, reactive, toRefs, computed} from 'vue'
import {SearchIcon, ExternalLinkIcon, ClockIcon, ExclamationCircleIcon} from '@heroicons/vue/outline'
import {Inertia} from "@inertiajs/inertia";
import {useForm, Link} from "@inertiajs/inertia-vue3";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import moment from "moment";
import SimpleDateRangePicker from "@/Components/SimpleDateRangePicker";
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

const filters = reactive({
  name: null,
  date: null
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
  if (urlParams.modal_create) openModalForm()
})

onMounted(() => {
  const urlParams = urlParser()
  filters.name = urlParams.name
  filters.date = urlParams.date || null
})
</script>

<template>
  <dashboard-layout title="Scoring of Deliveries">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Scoring of Deliveries</h1>
        <div>

        </div>
      </div>
    </template>
    <template #default>
      <div class="flex flex-row gap-4">
        <input id="search-name" v-model="filters.name" autocomplete="name"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search a Name"
               type="text">
        <div class="flex-1">
          <SimpleDateRangePicker v-model="filters.date" placeholder="Pick a date range" position-x="bottom" position-y="right"/>
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
            <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                <tr>
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6" scope="col">DELIVERY NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-right" scope="col">SCHEDULE</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">DURATION</span>
                  </th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">STATUS</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(delivery, index) in payload.data" :key="`delivery-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap pl-4 pr-3 py-4 text-sm text-gray-500 sm:pl-6">
                    <span>
                      <span v-if="delivery.status === 'Scheduled' && delivery.automatic_start" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-red-800 bg-red-200 mr-2">Strict</span>
                      {{ delivery.name ?? '-' }}
                    </span>
                    <div class="mt-2">
                      <span v-if="delivery.takers_count === 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-600 mr-2">
                          Group not set
                      </span>
                      <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-600 mr-2">
                          <span class="font-semibold">{{ delivery.takers_count }}</span>&nbsp;&nbsp;Takers
                      </span>

                      <span v-if="delivery.questions_count === 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-600 mr-2">
                          Exam not set
                      </span>
                      <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-600 mr-2">
                          <span class="font-semibold">{{ delivery.questions_count }}</span>&nbsp;&nbsp;Questions
                      </span>

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
                    <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusColors(delivery.status).bg, statusColors(delivery.status).text]">
                      {{ delivery.status }}
                    </span>
                  </td>
                  <td class="relative whitespace-nowrap px-3 py-4 text-right text-sm font-medium sm:pr-6">
                    <Link :href="route('back-office.scoring.detail', {delivery_hash: delivery.hash, name: 'back-office.scoring.index', params: 'page', values: payload.current_page})" class="text-blue-600 hover:text-blue-900">
                      <ExternalLinkIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
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
</template>
